<?php
require_once 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid phone ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT phones.*, 
               brands.name as brand_name
        FROM phones 
        JOIN brands ON phones.brand_id = brands.id 
        WHERE phones.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $phone = $stmt->fetch();

    if (!$phone) {
        http_response_code(404);
        echo json_encode(['error' => 'Phone not found']);
        exit;
    }

    // Format the image filename
    $phone['image'] = strtolower(str_replace(' ', '', $phone['name'])) . '.jpg';

    echo json_encode($phone);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
    // Log error in production
} 