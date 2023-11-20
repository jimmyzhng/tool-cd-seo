<?php
require 'utils/robots-scraper.php';
require "db/db-connection.php";
require_once APP_ROOT . '/vendor/autoload.php';

$botCategory = $config['bot_category'];

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['LOCAL_DB_HOST'];
$dbname = $_ENV['LOCAL_DB_NAME'];
$user = $_ENV['LOCAL_DB_USER'];
$password = $_ENV['LOCAL_DB_PASS'];

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";


use React\EventLoop\Loop;
use React\Http\Browser;

class PromiseQueue {
    private $concurrency;
    private $queue = [];
    private $active = 0;

    public function __construct($concurrency) {
        $this->concurrency = $concurrency;
    }

    public function add(callable $task) {
        $this->queue[] = $task;
        $this->run();
    }

    private function run() {
        while ($this->active < $this->concurrency && !empty($this->queue)) {
            $task = array_shift($this->queue);
            $this->active++;

            $task()->always(function() {
                $this->active--;
                $this->run();
            });
        }
    }
}

const MAX_CONCURRENCY = 10;
$queue = new PromiseQueue(MAX_CONCURRENCY);

$loop = Loop::get();
$browser = new Browser($loop);

// use Websites table
$stmt = $pdo->prepare("SELECT id, name, url, position, category FROM Websites");
$stmt->execute();

// fetch results as associative array
$websites = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo count($websites) . " websites fetched. Checking for bots..." . PHP_EOL;

// use Bots table
$stmt = $pdo->prepare("SELECT id, name FROM Bots  WHERE category = :bot_category");
$stmt->bindParam(':bot_category', $botCategory, PDO::PARAM_STR);
$stmt->execute();
$bots = $stmt->fetchAll();

$promises = [];

foreach ($websites as $website) {
    $url = $website['url'];

    foreach ($bots as $bot) {
        $botName = $bot['name'];

$queue->add(function() use ($url, $botName, $browser, $loop, $pdo, $website, $bot, $dsn, $user, $password, $options) {
    return checkRobotsForUserAgentAsync($url, $botName, $browser, $loop)->then(function ($status) use ($pdo, $website, $bot,  $dsn, $user, $password, $options) {
        // create new PDO per request
        $pdo = new PDO($dsn, $user, $password, $options);
        $stmt = $pdo->prepare("INSERT INTO WebsiteStatus (website_id, bot_id, status, check_date) VALUES (:website_id, :bot_id, :status, :check_date)");
        $stmt->execute([
            'website_id' => $website['id'],
            'bot_id' => $bot['id'],
            'status' => $status,
            'check_date' => date("Y-m-d")
        ]);
        $pdo = null;
    });
});
    };
}

$loop->run();

    echo "All done with the Website Status table!" . PHP_EOL;

    

