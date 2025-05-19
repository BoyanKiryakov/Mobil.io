<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

// Initialize current user
$currentUser = getCurrentUser();

// Get order details
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Get order details
    $stmt = $pdo->prepare("
        SELECT o.*, 
               c.first_name, c.last_name, c.email
        FROM orders o
        LEFT JOIN clients c ON o.client_id = c.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        header('Location: index.php');
        exit;
    }

    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.quantity, oi.unit_price,
               p.name as phone_name, b.name as brand_name
        FROM order_items oi
        JOIN phones p ON oi.phone_id = p.id
        JOIN brands b ON p.brand_id = b.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();

} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Thank You - Mobil.io</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <div class="success-container">
    <div class="success-message">
      <i class="bi bi-check-circle-fill success-icon"></i>
      <h1>Thank You for Your Order!</h1>
      <p>Your order has been successfully placed. We'll send you an email confirmation shortly.</p>
    </div>

    <div class="mock-notice">
      <i class="bi bi-info-circle"></i>
      <strong>Demo Notice:</strong> This is a mock e-commerce site for demonstration purposes. No actual transactions have been processed.
    </div>

    <div class="order-details">
      <h2>Order Details</h2>
      <div class="order-info">
        <div class="order-info-section">
          <h3>Order Information</h3>
          <p><strong>Order Number:</strong> #<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></p>
          <p><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
          <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
        </div>
      </div>

      <h3>Ordered Items</h3>
      <table class="order-items">
        <thead>
          <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($order_items as $item): ?>
            <tr>
              <td><?php echo htmlspecialchars($item['brand_name'] . ' ' . $item['phone_name']); ?></td>
              <td><?php echo $item['quantity']; ?></td>
              <td>€<?php echo number_format($item['unit_price'], 2); ?></td>
              <td>€<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div class="order-total">
        <p>Total: <span class="total-amount">€<?php echo number_format($order['total'], 2); ?></span></p>
      </div>
    </div>

    <div class="action-buttons">
      <a href="catalogue.php" class="action-button secondary-button">Continue Shopping</a>
      <?php if ($currentUser): ?>
        <a href="profile.php" class="action-button primary-button">View My Orders</a>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>