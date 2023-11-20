<?php
require_once 'config.php';
require_once APP_ROOT . '/vendor/autoload.php';
require APP_ROOT . '/db/db-connection.php';

$jsonData = file_get_contents(APP_ROOT . '/data/top-websites-categorized.json');
$websitesData = json_decode($jsonData, true);


$stmtInsert = $pdo->prepare("INSERT INTO Websites (name, url, position, category) VALUES (:name, :url, :position, :category)");
$stmtSelect = $pdo->prepare("SELECT COUNT(*) FROM Websites WHERE url = :url");

foreach ($websitesData as $website) {
    $url = $website['domain'];
    $name = explode('.', $url)[0];
    $position = $website['position'];
    $category = $website['category'];

    // check if exists first
    $stmtSelect->execute([':url' => $url]);
    $exists = $stmtSelect->fetchColumn();

    if (!$exists) {
        $stmtInsert->bindParam(':name', $name);
        $stmtInsert->bindParam(':url', $url);
        $stmtInsert->bindParam(':position', $position);
        $stmtInsert->bindParam(':category', $category);

        $stmtInsert->execute();
    }
}

echo "Data has been successfully inserted! \n";
?>