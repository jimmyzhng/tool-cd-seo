<?php
require_once 'config.php';
require APP_ROOT . '/vendor/autoload.php';
require APP_ROOT . '/db/db-connection.php';

// first, obtain google sheets data
function fetchBlockData() {$serviceAccountKeyFilePath = APP_ROOT . '/data/service-acc-key.json';
$spreadsheetId = '1FpAj633_uI5IEL782Vj4o59Kw1yBm-QiLnUrHCTq6jw';
$range = 'block_dates!A1:F1001';  

$client = new Google_Client();
$client->setApplicationName('Google Sheets and PHP');
$client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
$client->setAccessType('offline');
$client->setAuthConfig($serviceAccountKeyFilePath);

$service = new Google_Service_Sheets($client);
$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();

if (empty($values)) {
  print "No data found.\n";
} else {
  // Extract the headers from the first row
  $headers = array_shift($values);
  
  // Convert remaining rows to associative arrays
  $assocData = array_map(function($row) use ($headers) {
    // If the row's column count doesn't match the header count, handle the discrepancy
    if (count($row) !== count($headers)) {
        // Fill missing columns with an empty string
        while (count($row) < count($headers)) {
            $row[] = '';  // use empty string here
        }
        // Optionally handle rows with extra columns:
        $row = array_slice($row, 0, count($headers));
    }

    return array_combine($headers, $row);
}, $values);
  
  return $assocData;
}}

$blockDateData = fetchBlockData();

// Check if data is retrieved
// print_r($blockDateData);

foreach ($blockDateData as $row) {
  // fetch website_id using 'url' column
  $stmt = $pdo->prepare("SELECT id FROM Websites WHERE url = :url");
  $stmt->bindParam(":url", $row["Site"]);
  $stmt->execute();
  $website_id = $stmt->fetchColumn();
  
  // if website_id is found, continue
  if ($website_id) {
      // fetch bot_id and names
      $bots = $pdo->query("SELECT id, name FROM Bots")->fetchAll(PDO::FETCH_KEY_PAIR);
      
      foreach ($bots as $bot_id => $bot_name) {
        if (isset($row[$bot_name]) && $row[$bot_name] !== '' && $row[$bot_name] !== 'Missing') {
            if ($row[$bot_name] === 'Unretrievable') {
                // update remarks in WebsiteStatus table
                $stmt = $pdo->prepare("UPDATE WebsiteStatus SET note = 'Unretrievable' WHERE website_id = :website_id AND bot_id = :bot_id AND status = 'Blocked'");
                $stmt->bindParam(":website_id", $website_id);
                $stmt->bindParam(":bot_id", $bot_id);
                $stmt->execute();
            } else {
                $dateObj = parseDate($row[$bot_name]);
    
                if ($dateObj) {
                    $parsedDate = $dateObj->format('Y-m-d');
                    
                    // update block_date in WebsiteStatus table
                    $stmt = $pdo->prepare("UPDATE WebsiteStatus SET block_date = :block_date WHERE website_id = :website_id AND bot_id = :bot_id AND status = 'Blocked'");
                    $stmt->bindParam(":block_date", $parsedDate);
                    $stmt->bindParam(":website_id", $website_id);
                    $stmt->bindParam(":bot_id", $bot_id);
                    $stmt->execute();
                }
            }
        }
    }
  }
}
echo "Successfully updated database with block date data (" . count($blockDateData) . " rows.)\n" ;


function parseDate($dateStr) {
  // need multiple formats due to the dates in the google sheets varying in format
  $formats = [
      "M j Y",
      "M. j Y",
      "M j",
      "M. j",
      "F j Y",
      "j M Y",
      "j M. Y"
  ];

  foreach ($formats as $format) {
      $date = DateTime::createFromFormat($format, $dateStr);
      if ($date) {
          if (in_array($format, ["M j", "M. j", "j M", "j M."])) {
              $date->setDate(date('Y'), $date->format('n'), $date->format('j'));
          }
          return $date;
      }
  }
  return null;
}