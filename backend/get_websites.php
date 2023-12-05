<?php
$config = include 'config.php';
require_once "db/db_connection.php";

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

try {
    $botCategory = $config['bot_category'];

    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $category = isset($_GET['category']) ? $_GET['category'] : '';

    if (!isset($_GET['sitesPerPage'], $_GET['page']) || !is_numeric($_GET['sitesPerPage']) || !is_numeric($_GET['page'])) {
        echo json_encode(['error' => 'Invalid parameters']);
        exit;
    }

    if ($search && !preg_match('/^[a-zA-Z0-9\s]+$/', $search)) {
        echo json_encode(['error' => 'Invalid search parameter']);
        exit;
    }
    
    if ($category && !preg_match('/^[a-zA-Z0-9\s]+$/', $category)) {
        echo json_encode(['error' => 'Invalid category parameter']);
        exit;
    }

    // pagination
    $sitesPerPage = $_GET['sitesPerPage'];
    $page = $_GET['page'];
    $offset = ($page - 1) * $sitesPerPage;

    // sorting
    $sortColumnMap = [
        "url" => "w.url",
        "category" => "w.category",
        "position" => "w.position"
    ];

    $sortByParam = isset($_GET['sortBy']) ? $_GET['sortBy'] : null;
    $sortBy = isset($sortColumnMap[$sortByParam]) ? $sortColumnMap[$sortByParam] : "w.position";

    $allowedOrders = ['asc', 'desc'];
    $sortOrder = isset($_GET['sortOrder']) && in_array($_GET['sortOrder'], $allowedOrders) ? $_GET['sortOrder'] : 'asc';
    // error_log("Received sortOrder: " . $sortOrder);

    // WHERE clause - check date + searching & filtering
    $conditions = ["(ws.check_date = :lastUpdated OR ws.check_date IS NULL)", "b.category = :bot_category"];

    if ($search) {
        $conditions[] = "w.url LIKE :search";
    }
    
    if ($category) {
        $conditions[] = "w.category = :category";
    }

    $whereSQL = "WHERE " . implode(" AND ", $conditions);

// query to find most recent check_date WITH COMPLETE DATA for 1000 rows
// first, count all bots of the given category * 1000 => this indicates a full list
$botCountQuery = "
    SELECT COUNT(*) as botCount
    FROM Bots
    WHERE category = :bot_category
";

$stmtBotCount = $pdo->prepare($botCountQuery);
$stmtBotCount->bindParam(':bot_category', $botCategory, PDO::PARAM_STR);
$stmtBotCount->execute();
$botCountResult = $stmtBotCount->fetch();
$botCount = $botCountResult['botCount'];

$requiredCount = $botCount * 1000;

$lastUpdatedQuery = "
    SELECT check_date as lastUpdated
    FROM (
        SELECT check_date, COUNT(*) as cnt
        FROM WebsiteStatus
        WHERE status IS NOT NULL AND status <> ''
        GROUP BY check_date
        HAVING cnt >= :required_count
        ORDER BY check_date DESC
    ) t
    LIMIT 1
";

    $stmtLastUpdated = $pdo->prepare($lastUpdatedQuery);
    $stmtLastUpdated->bindParam(':required_count', $requiredCount);
    $stmtLastUpdated->execute();
    $lastUpdatedResult = $stmtLastUpdated->fetch();
    $lastUpdated = $lastUpdatedResult['lastUpdated'];

    // FIRST: 
    // query to count all matching records (for pagination)
        $countQuery = "
        SELECT COUNT(DISTINCT w.id) as total 
        FROM Websites w 
        LEFT JOIN WebsiteStatus ws ON w.id = ws.website_id
        LEFT JOIN Bots b ON ws.bot_id = b.id
        $whereSQL
    ";

    $stmtCount = $pdo->prepare($countQuery);
    $stmtCount->bindParam(':lastUpdated', $lastUpdated, PDO::PARAM_STR);
    $stmtCount->bindParam(':bot_category', $botCategory, PDO::PARAM_STR);
    if ($search) {
        // wrap with % wildcard characters to look for anything with the search value
        $searchLike = "%$search%";
        // bind parameter for security
        $stmtCount->bindParam(':search', $searchLike, PDO::PARAM_STR);
    }
    if ($category) {
        $stmtCount->bindParam(':category', $category, PDO::PARAM_STR);
    }

    // print($countQuery);
    $stmtCount->execute();
    $countResult = $stmtCount->fetch();
    $totalCount = $countResult['total'];

    $stmt = null;

    // NEXT:
    // query to find all results
    $query = "
    SELECT 
        w.name as websiteName, w.url, w.category, w.position,
        JSON_OBJECTAGG(
            b.name, 
            JSON_OBJECT('status', ws.status, 'block_date', ws.block_date)
        ) as botsStatuses
    FROM Websites w 
    LEFT JOIN WebsiteStatus ws ON w.id = ws.website_id
    LEFT JOIN Bots b ON ws.bot_id = b.id
    $whereSQL
    GROUP BY w.id
    ORDER BY $sortBy $sortOrder
    LIMIT :sitesPerPage OFFSET :offset
";
    
    $stmtQuery = $pdo->prepare($query);
    $stmtQuery->bindParam(':lastUpdated', $lastUpdated, PDO::PARAM_STR);
    $stmtQuery->bindParam(':bot_category', $botCategory, PDO::PARAM_STR);
    

    if ($search) {
        // wrap with % wildcard characters to look for anything with the search value
        $searchLike = "%$search%";
        // bind parameter for security
        $stmtQuery->bindParam(':search', $searchLike, PDO::PARAM_STR);
    }

    if ($category) {
        $stmtQuery->bindParam(':category', $category, PDO::PARAM_STR);
    }

    $stmtQuery->bindParam(':sitesPerPage', $sitesPerPage, PDO::PARAM_INT);
    $stmtQuery->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmtQuery->execute();
    $results = $stmtQuery->fetchAll();


    echo json_encode(['sites' => $results, 'totalSites' => $totalCount, 'lastUpdated' => $lastUpdated]);

} catch (\PDOException $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'An error occurred while fetching data.', 'details' => $e->getMessage()]);
}
?>