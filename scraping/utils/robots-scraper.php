<?php
$config = include 'config.php';
require APP_ROOT . '/vendor/autoload.php';

use Psr\Http\Message\ResponseInterface;
use React\Promise\Timer;
function getRandomUserAgent() {
    $userAgents = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', // chrome windows
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36', // chrome mac
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0', // firefox windows
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:89.0) Gecko/20100101 Firefox/89.0', // firefox mac
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', // edge windows
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Edge/91.0.864.59', // edge mac
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.2 Safari/605.1.15', // safari 
        'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1', // safari iphone
        'Mozilla/5.0 (Android 11; Mobile; rv:68.0) Gecko/68.0 Firefox/89.0', // firefox android
        'Mozilla/5.0 (Linux; Android 10; SM-A205U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.164 Mobile Safari/537.36', // chrome android
    ];
    return $userAgents[array_rand($userAgents)];
}

function makeRequestWithRetry($url, $userAgent, $browser, $loop, $maxAttempts = 3, $delayInSeconds = 3) {
    $attempt = 0;
    
    return new \React\Promise\Promise(function ($resolve, $reject) use (&$attempt, $maxAttempts, $delayInSeconds, $url, $userAgent, $browser, $loop) {
        $makeRequest = function() use (&$attempt, $maxAttempts, $delayInSeconds, $url, $userAgent, $browser, $loop, $resolve, $reject, &$makeRequest) {
            $attempt++;
            
            $browser->get($url, [
                'User-Agent' => $userAgent,
                'timeout' => 30
                ])
                ->then(
                    function (ResponseInterface $response) use ($resolve) {
                        $resolve($response);
                    },
                    function (Exception $e) use ($attempt, $maxAttempts, $delayInSeconds, $loop, $reject, &$makeRequest, $url) {
                        echo "HTTP error for $url: " . $e->getMessage() . PHP_EOL;
                        if ($attempt < $maxAttempts) {
                            echo "Retrying... (Attempt $attempt of $maxAttempts)" . PHP_EOL;
                            $loop->addTimer($delayInSeconds, $makeRequest);
                        } else {
                            $reject($e);
                        }
                    }
                );
        };
        
        $makeRequest();
    });
}

function checkRobotsForUserAgentAsync($url, $user_agent_to_check, $browser, $loop) {
    $userAgent = getRandomUserAgent();
    $robots_url = 'http://' . rtrim($url, '/') . '/robots.txt';

    return makeRequestWithRetry($robots_url, $userAgent, $browser, $loop)
        ->then(
            function (ResponseInterface $response) use ($user_agent_to_check) {
                return parseRobotsTxt($response->getBody(), $user_agent_to_check);
            },
            function (Exception $e) use ($url) {
                echo "Failed to fetch the robots.txt file for $url: " . $e->getMessage() . PHP_EOL;
                return 'Missing';
            }
        );
}


function parseRobotsTxt($robots_content, $user_agent_to_check) {
    if (empty($robots_content)) {
        return 'Missing';
    }
    
    $lines = explode("\n", $robots_content);
    $agent_found = false;
    $disallowed_paths = [];

    foreach ($lines as $line) {
        $line = trim($line);

        // stripos for case-insensitivity 
        if (!$agent_found && stripos($line, "User-agent: " . $user_agent_to_check) === 0) {
            $agent_found = true;
        } elseif ($agent_found && stripos($line, "Disallow: ") === 0) {
            $path = trim(str_replace("Disallow:", "", $line));
            if (!empty($path)) {
                $disallowed_paths[] = $path;
            }
        } elseif ($agent_found && stripos($line, "User-agent:") === 0) {
          // stop if another user-agent section begins
            break;
        }
    }

    if (in_array("/", $disallowed_paths)) {
        return 'Blocked';
    } elseif (!empty($disallowed_paths)) {
        // return 'Partially Blocked - Paths: ' . implode(', ', $disallowed_paths);
        return 'Partially Blocked';
    } else {
        return 'Allowed';
    } 

}

// Test
// use React\EventLoop\Loop;
// use React\Http\Browser;

// $loop = Loop::get();
// $browser = new Browser($loop);

// $url = "bloomberg.com";
// $user_agent = "Googlebot";

// checkRobotsForUserAgentAsync($url, $user_agent, $browser)->then(function ($result) use ($url, $user_agent) {
//     echo "Result for $user_agent on $url: $result" . PHP_EOL;
// });

// $loop->run();