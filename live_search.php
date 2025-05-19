<?php
require_once 'includes/db.php';

header('Content-Type: application/json');

$results = [];
$searchTerm = $_GET['term'] ?? '';

if (strlen($searchTerm) > 1) { // Only search if term is at least 2 characters
    try {
        $query = "
            SELECT 
                p.id, 
                p.name AS phone_name, 
                b.name AS brand_name,
                p.price,
                CONCAT(LOWER(REPLACE(p.name, ' ', '')), '.jpg') AS image_name_part
            FROM phones p
            JOIN brands b ON p.brand_id = b.id
            WHERE p.name LIKE :term OR b.name LIKE :term
            ORDER BY b.name, p.name
            LIMIT 10;
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute(['term' => "%" . $searchTerm . "%"]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Construct image paths (assuming images/phones/ directory)
        foreach ($results as &$result) {
            // Try to create a more robust image path by also checking brand_name + phone_name
            $specificImage = 'images/phones/' . strtolower(str_replace(' ', '', $result['brand_name'])) . strtolower(str_replace(' ', '', $result['phone_name'])) . '.jpg';
             $genericImage = 'images/phones/' . strtolower(str_replace(' ', '', $result['phone_name'])) . '.jpg';

            // This is a simplified check. In a real scenario, you'd check file_exists on the server.
            // For the client-side, we'll just provide a best guess.
            // The onerror handler on the client will deal with missing images.
            $result['image_url'] = $genericImage; // Default to generic name
        }

    } catch (PDOException $e) {
        // Log error, but return empty results for the client
        error_log("Live search PDOException: " . $e->getMessage());
        $results = ['error' => 'Database error'];
    }
}

echo json_encode($results);
?> 