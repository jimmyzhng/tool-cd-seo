<?php
require 'config.php';
require 'db/db-connection.php';


$stmt = $pdo->query("SELECT * FROM WebsiteStatus WHERE status = 'Blocked' AND block_date IS NULL AND note IS NULL");
$blockedWebsites = $stmt->fetchAll();

print("Found " . count($blockedWebsites) . " blocked website rows with no block dates. Finding block dates.." . "\n");

// Prepare SQL statements outside the loop
$stmtWebsite = $pdo->prepare("SELECT url FROM Websites WHERE id = ?");
$stmtBot = $pdo->prepare("SELECT name FROM Bots WHERE id = ?");
$stmtUpdateUnretrievable = $pdo->prepare("UPDATE WebsiteStatus SET note = 'Unretrievable' WHERE website_id = ? AND bot_id = ? AND status = 'Blocked'");
$stmtUpdateBlockDate = $pdo->prepare("UPDATE WebsiteStatus SET block_date = ? WHERE website_id = ? AND bot_id = ? AND status = 'Blocked'");

// Look for previous block dates in db first:
$stmtPreviousBlockDate = $pdo->prepare("SELECT block_date FROM WebsiteStatus WHERE website_id = ? AND bot_id = ? AND block_date IS NOT NULL ORDER BY check_date DESC LIMIT 1");

$pdo->beginTransaction();

// Run crawlerBlockDate to find the block date from Wayback Machine
foreach ($blockedWebsites as $website) {
    $websiteID = $website['website_id'];
    $botID = $website['bot_id'];

    // get url for given website
    $stmtWebsite->execute([$websiteID]);
    $url = $stmtWebsite->fetchColumn();

    // get bot name
    $stmtBot->execute([$botID]);
    $botName = $stmtBot->fetchColumn();

    // get previous block date (if in db)
    $stmtPreviousBlockDate->execute([$websiteID, $botID]);
    $previousBlockDate = $stmtPreviousBlockDate->fetchColumn();
    
    if ($previousBlockDate) {
        $blockDate = $previousBlockDate;
       } else {
            $blockDate = trim(shell_exec("python3 utils/crawlerBlockDate.py $botName $url"));
            print("Block date for " . $url . " for " . $botName . ": " . $blockDate . "\n");
    }

    // Update block_date in db

    if ($blockDate === "Unretrievable") {
        print("Unretrievable block date for: " . $url . " (" . $botName . ")" . ". Adding to database." . "\n");
        $stmtUpdateUnretrievable->execute([$websiteID, $botID]);
        continue;
    }

    if ($blockDate  && strpos($blockDate, '-') !== false) {
        $blockDate .= " 00:00:00";
        $stmtUpdateBlockDate->execute([$blockDate, $websiteID, $botID]);
        print("Inserted block date for:" . $url . " " . "(" . $botName . ")" . "\n");

    }
}

$pdo->commit();