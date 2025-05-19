<?php
require_once 'includes/db.php';

try {
    // Update stock for all phones
    $stmt = $pdo->prepare("UPDATE phones SET stock = stock + FLOOR(5 + RAND() * 26)");
    $stmt->execute();
    
    // Get updated stock levels
    $stmt = $pdo->query("SELECT p.name, b.name as brand_name, p.stock FROM phones p JOIN brands b ON p.brand_id = b.id ORDER BY b.name, p.name");
    $phones = $stmt->fetchAll();
    
    echo "<h2>Stock Updated Successfully</h2>";
    echo "<h3>Current Stock Levels:</h3>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Brand</th><th>Model</th><th>Stock</th></tr>";
    
    foreach ($phones as $phone) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($phone['brand_name']) . "</td>";
        echo "<td>" . htmlspecialchars($phone['name']) . "</td>";
        echo "<td>" . $phone['stock'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    echo "<p><a href='catalogue.php'>Go to Catalogue</a></p>";
    
} catch (PDOException $e) {
    echo "Error updating stock: " . htmlspecialchars($e->getMessage());
}
?> 