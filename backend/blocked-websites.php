<?php
include 'db/db_connection.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

  if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Invalid request type']);
    exit;
}

// Category and Limit filters for graph
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 1000;

$category = isset($_GET['category']) ? $_GET['category'] : null;

if ($category !== null && !preg_match('/^[a-zA-Z0-9\s]+$/', $category)) {
  echo json_encode(['error' => 'Invalid category']);
  exit;
}

$whereClause = "ws.status = 'Blocked' AND ws.block_date <= CURDATE()";
if($category !== null) {
    $whereClause .= " AND w.category = :category";
}

$query = " 
SELECT b.name as bot_name, DATE(ws.block_date) as block_date , COUNT(DISTINCT ws.website_id) as blocked_count
FROM WebsiteStatus ws
JOIN Bots b ON ws.bot_id = b.id
JOIN Websites w ON ws.website_id = w.id
WHERE $whereClause AND w.position <= $limit
GROUP BY DATE(ws.block_date), ws.bot_id
ORDER BY DATE(ws.block_date), ws.bot_id
";

$stmt = $pdo->prepare($query);

if($category !== null) {
  $stmt->bindParam(':category', $category);
}
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// echo $data;
echo json_encode($data);