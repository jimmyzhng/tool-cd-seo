<?php
$config = include 'config.php';
require_once "db/db_connection.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$botCategory = $config['bot_category'];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Invalid request type.']);
    exit;
}

try {
    // Get all bot names
    $botNamesQuery = "SELECT name FROM Bots WHERE category = :category";
    $botStmt = $pdo->prepare($botNamesQuery);
    $botStmt->bindParam(':category', $botCategory, PDO::PARAM_STR);
    $botStmt->execute();
    $botNames = $botStmt->fetchAll(PDO::FETCH_COLUMN);

    // dynamic query to create each column
    $dynamicQueryParts = [];
    foreach ($botNames as $botName) {
        $dynamicQueryParts[] = "MAX(CASE WHEN b.name = '$botName' THEN ws.status ELSE NULL END) AS `$botName`";
    }
    $dynamicSelect = implode(", ", $dynamicQueryParts);

    if (empty($dynamicQueryParts)) {
        throw new Exception("No bots found in the specified category. Here is what we have: " .  implode(", ", $botNames));
    }

    $query = "
        SELECT 
            w.position AS Position,
            w.url AS Website,
            w.category AS Category,
            $dynamicSelect
        FROM 
            Websites w
        JOIN 
            WebsiteStatus ws ON w.id = ws.website_id
        JOIN 
            Bots b ON ws.bot_id = b.id
        WHERE 
            ws.check_date = (SELECT MAX(check_date) FROM WebsiteStatus WHERE website_id = w.id)
            AND b.category = :bot_category
        GROUP BY 
            w.id
        ORDER BY 
            w.position;
    ";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':bot_category', $botCategory, PDO::PARAM_STR);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['websites' => $results]);

} catch (\Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'An error occurred while fetching website data.', 'details' => $e->getMessage()]);
}
?>