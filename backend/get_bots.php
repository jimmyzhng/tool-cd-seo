<?php
$config = include 'config.php';
require_once "db/db_connection.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$botCategory = $config['bot_category'];

  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Invalid request type. Use GET request.']);
    exit;
  }

  try {
    $botsQuery = "SELECT name FROM Bots WHERE category = :bot_category";

    $stmtBots = $pdo->prepare($botsQuery);
    $stmtBots->bindParam(':bot_category', $botCategory, PDO::PARAM_STR);
    $stmtBots->execute();
    $botNames = $stmtBots->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['botNames' => $botNames]);

} catch (\PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'An error occurred while fetching bot names.', 'details' => $e->getMessage()]);
}
