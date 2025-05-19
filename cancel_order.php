<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

// If user is not logged in, redirect to login page
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$orderId) {
    $_SESSION['profile_message'] = 'Invalid order ID.';
    $_SESSION['profile_message_type'] = 'danger';
    header('Location: profile.php');
    exit;
}

try {
    // Fetch the order to check its current status and ensure it belongs to the user
    $stmt = $pdo->prepare("SELECT status, client_id FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        $_SESSION['profile_message'] = 'Order not found.';
        $_SESSION['profile_message_type'] = 'danger';
        header('Location: profile.php');
        exit;
    }

    if ($order['client_id'] !== $userId) {
        $_SESSION['profile_message'] = 'You are not authorized to cancel this order.';
        $_SESSION['profile_message_type'] = 'danger';
        header('Location: profile.php');
        exit;
    }

    // Define cancellable statuses
    $cancellableStatuses = ['pending', 'paid', 'processing']; 

    if (!in_array(strtolower($order['status']), $cancellableStatuses)) {
        $_SESSION['profile_message'] = 'This order cannot be cancelled as it is already ' . htmlspecialchars($order['status']) . '.';
        $_SESSION['profile_message_type'] = 'warning';
        header('Location: profile.php');
        exit;
    }

    // Proceed with cancellation and stock replenishment
    $pdo->beginTransaction();

    // Fetch order items to return stock
    $stmtItems = $pdo->prepare("SELECT phone_id, quantity FROM order_items WHERE order_id = ?");
    $stmtItems->execute([$orderId]);
    $orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orderItems as $item) {
        $updateStockStmt = $pdo->prepare("UPDATE phones SET stock = stock + ? WHERE id = ?");
        $updateStockStmt->execute([$item['quantity'], $item['phone_id']]);
    }

    // Update the order status to 'cancelled'
    $updateOrderStmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    
    if ($updateOrderStmt->execute([$orderId])) {
        $pdo->commit();
        $_SESSION['profile_message'] = 'Order #' . htmlspecialchars($orderId) . ' has been successfully cancelled and stock updated.';
        $_SESSION['profile_message_type'] = 'success';
    } else {
        $pdo->rollBack();
        $_SESSION['profile_message'] = 'Failed to cancel order. Please try again.';
        $_SESSION['profile_message_type'] = 'danger';
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Log error in a real application
    // error_log("Error cancelling order: " . $e->getMessage());
    $_SESSION['profile_message'] = 'An error occurred while cancelling the order. Please try again.';
    $_SESSION['profile_message_type'] = 'danger';
}

header('Location: profile.php');
exit;
?> 