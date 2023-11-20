<?php
require_once "config.php";
require_once "db/db_connection.php";

header("Access-Control-Allow-Origin: *");


  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Invalid request type.']);
    exit;
}

try {
    $query = "SELECT DISTINCT category 
    FROM Websites 
    ORDER BY CASE WHEN category = 'Other' 
    THEN 1 ELSE 0 END, category ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    echo json_encode(['categories' => $categories]);
} catch (\PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'An error occurred while fetching categories.', 'details' => $e->getMessage()]);
}
?>