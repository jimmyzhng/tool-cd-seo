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
    $query = "
        SELECT 
            w.position AS Position,
            w.url AS Website,
            w.category AS Category,
            MAX(CASE WHEN b.name = 'GPTBot' THEN ws.status ELSE NULL END) AS GPTBot,
            MAX(CASE WHEN b.name = 'CCBot' THEN ws.status ELSE NULL END) AS CCBot,
            MAX(CASE WHEN b.name = 'Anthropic-AI' THEN ws.status ELSE NULL END) AS 'anthropic-ai',
            MAX(CASE WHEN b.name = 'Google-Extended' THEN ws.status ELSE NULL END) AS 'Google-Extended'
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