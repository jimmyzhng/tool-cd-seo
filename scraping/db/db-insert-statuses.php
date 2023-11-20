<?php
require '../config.php';
require_once APP_ROOT . '/vendor/autoload.php';
require 'db-connection.php';

$sheet_data = fetch_data_from_google_sheet();

// fetch mapping of website URLs and IDs
$stmt = $pdo->query("SELECT id, url FROM Websites");
$website_to_id = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// fetch mapping of bot names and IDs
$stmt = $pdo->query("SELECT id, name FROM Bots");
$bot_to_id = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// set check date of study being inserted
$check_date = "2023-10-17";

foreach ($sheet_data as $row) {
    $website_url = $row['Site'];
    $website_id = array_search($website_url, $website_to_id);
    print($website_url . "" . $website_id);

  
    foreach (['GPTBot', 'CCBot', 'anthropic-ai', 'Google-Extended'] as $bot_name) {
        $bot_id = array_search($bot_name, $bot_to_id);
        $status = $row[$bot_name];

        // insert data into WebsiteStatus table
        $stmt = $pdo->prepare("INSERT INTO WebsiteStatus (website_id, bot_id, status, check_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$website_id, $bot_id, $status, $check_date]);
    }
}
function fetch_data_from_google_sheet() {
  $client = getClient();
  $service = new Google_Service_Sheets($client);
  
  $spreadsheetId = '1FpAj633_uI5IEL782Vj4o59Kw1yBm-QiLnUrHCTq6jw'; 
  $sheetName = '10/17';
  $range = $sheetName . '!A:F'; 

  $response = $service->spreadsheets_values->get($spreadsheetId, $range);
  $values = $response->getValues();

  // check if sheet is obtained
  if (!empty($values)) {
    // Print the first row (headers)
    echo "Headers:\n";
    foreach ($values[0] as $header) {
        echo $header . "\t";
    }
    echo "\n"; // New line after printing headers
} else {
    echo "No data found in the spreadsheet.\n";
}
  
  $data = [];
  $firstRow = true;

  foreach ($values as $row) {
    if ($firstRow) {
      $firstRow = false;
      continue;
  }
  
      $data[] = [
          'Site' => $row[1],
          'GPTBot' => $row[2],
          'CCBot' => $row[3],
          'anthropic-ai' => $row[4],
          'Google-Extended' => $row[5]
      ];
  }
  // print_r($data);
  return $data;
}

function getClient() {
  $client = new Google_Client();
  $client->setApplicationName('Google Sheets API PHP');
  $client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
  $client->setAuthConfig('oauth_key.json'); 
  $client->setRedirectUri('http://localhost');
  $client->setAccessType('offline');
  $client->setPrompt('select_account consent');

  // load previously authorized token from a file (if exists)
  $tokenPath = 'token.json';
  if (file_exists($tokenPath)) {
      $accessToken = json_decode(file_get_contents($tokenPath), true);
      $client->setAccessToken($accessToken);
  }

  // if no previous token
  if ($client->isAccessTokenExpired()) {
      if ($client->getRefreshToken()) {
          $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
      } else {
          // request authorization from user
          $authUrl = $client->createAuthUrl();
          printf("Open the following link in your browser:\n%s\n", $authUrl);
          print 'Enter verification code: ';
          $authCode = trim(fgets(STDIN));

          // exchange authorization code for an access token
          $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
          $client->setAccessToken($accessToken);

          // save token for future use
          if (!file_exists(dirname($tokenPath))) {
              mkdir(dirname($tokenPath), 0700, true);
          }
          file_put_contents($tokenPath, json_encode($client->getAccessToken()));
      }
  }
  return $client;
}