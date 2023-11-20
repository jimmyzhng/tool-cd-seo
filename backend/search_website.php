<?php

header('Content-Type: application/json');
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Origin: *");

function parseRobotsTxt($content) {
  $lines = explode("\n", $content);
  $currentAgent = '';
  $rules = [];

  foreach ($lines as $line) {
      if (strpos($line, 'User-agent:') === 0) {
        $currentAgent = trim(str_ireplace('User-agent:', '', $line));
          $rules[$currentAgent] = ['allow' => [], 'disallow' => []];
      } elseif (strpos($line, 'Disallow:') === 0) {
          $path = trim(str_replace('Disallow:', '', $line));
          if (!empty($path)) { // Only add to the rules if there's a path specified
              $rules[$currentAgent]['disallow'][] = $path;
          }
      } elseif (strpos($line, 'Allow:') === 0) {
          $path = trim(str_replace('Allow:', '', $line));
          if (!empty($path)) {
              $rules[$currentAgent]['allow'][] = $path;
          }
      }
  }

  return $rules;
}

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    $website = $data['website'];

    // Fetch the robots.txt
    $options = [
      'http' => [
          'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.63 Safari/537.36\r\n"
      ]
  ];
  $context = stream_context_create($options);
  $robotsTxtContent = file_get_contents($website . '/robots.txt', false, $context);

    if ($robotsTxtContent === false) {
        echo json_encode(["success" => false, "message" => "Could not fetch robots.txt"]);
        exit;
    }

    $parsedRules = parseRobotsTxt($robotsTxtContent);
  
    $response = [
      "success" => true,
      "message" => "Data fetched successfully.",
      "data" => [
          "website" => $website,
          "agents" => []
      ]
  ];
  
  foreach ($parsedRules as $agent => $rule) {
      $agentDetails = [
          "userAgent" => $agent,
          "disallowedPaths" => $rule['disallow'],
          "allowedPaths" => $rule['allow']
      ];
  
      if (in_array('/', $rule['disallow'])) {
          $agentDetails["blockageStatus"] = 'Blocked';
      } else if (!empty($rule['disallow'])) {
          $agentDetails["blockageStatus"] = 'Partially Blocked';
      } else {
          $agentDetails["blockageStatus"] = 'Allowed';
      }
  
      $response["data"]["agents"][] = $agentDetails;
  }
  
  echo json_encode($response);
}

