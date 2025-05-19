<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$user = null;
$orders = []; // Placeholder for orders
$message = '';
$messageType = '';

// Fetch user details
try {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // This should not happen if session is valid, but as a safeguard
        unset($_SESSION['user_id']);
        header('Location: login.php');
        exit;
    }

    // Fetch user orders with details of the first product
    $stmt_orders = $pdo->prepare("
        SELECT 
            o.id, o.order_date, o.total, o.status,
            oi.phone_id, -- The specific phone_id of the first item
            p.name AS phone_name,
            b.name AS brand_name
        FROM orders o
        LEFT JOIN (
            -- Subquery to determine the 'first' phone_id for each order
            SELECT order_id, MIN(phone_id) as first_phone_id
            FROM order_items
            GROUP BY order_id
        ) oi_ref ON o.id = oi_ref.order_id
        LEFT JOIN order_items oi ON oi.order_id = oi_ref.order_id AND oi.phone_id = oi_ref.first_phone_id
        LEFT JOIN phones p ON oi.phone_id = p.id
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE o.client_id = ? 
        ORDER BY o.order_date DESC
    ");
    $stmt_orders->execute([$userId]);
    $orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = "Error fetching profile data: " . $e->getMessage();
    $messageType = 'danger';
    // In a production environment, log this error instead of displaying to user
}

// Handle profile update - Placeholder for now
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Sanitize and validate input
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email)) {
        $message = 'All fields marked with * are required.';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
        $messageType = 'danger';
    } else {
        try {
            // Check if email is already taken by another user
            $checkEmailStmt = $pdo->prepare("SELECT id FROM clients WHERE email = ? AND id != ?");
            $checkEmailStmt->execute([$email, $userId]);
            if ($checkEmailStmt->fetch()) {
                $message = 'This email is already registered to another account.';
                $messageType = 'danger';
            } else {
                // Update user in database
                $updateStmt = $pdo->prepare("
                    UPDATE clients 
                    SET first_name = ?, 
                        last_name = ?, 
                        email = ?, 
                        address = ?, 
                        phone_number = ? 
                    WHERE id = ?
                ");
                $updateStmt->execute([$firstName, $lastName, $email, $address, $phoneNumber, $userId]);
                
                $message = 'Profile updated successfully!';
                $messageType = 'success';
                
                // Re-fetch user data to display updated info
                $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $message = "Error updating profile: " . $e->getMessage();
            $messageType = 'danger';
            // In a production environment, log this error instead of displaying to user
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Mobil.io</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5 mb-5">
        <?php if ($user): ?>

            <?php 
            // Display messages from session
            if (isset($_SESSION['profile_message'])) {
                echo '<div class="alert alert-' . ($_SESSION['profile_message_type'] ?? 'info') . ' alert-dismissible fade show" role="alert">';
                echo htmlspecialchars($_SESSION['profile_message']);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
                unset($_SESSION['profile_message']);
                unset($_SESSION['profile_message_type']);
            }
            if ($message): 
            ?>
                <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <h2 class="profile-section-title">Account Information</h2>
            <form method="POST" action="profile.php" class="profile-form">
                <div class="mb-3">
                    <label for="firstName" class="form-label">First Name*</label>
                    <input type="text" class="form-control" id="firstName" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="lastName" class="form-label">Last Name*</label>
                    <input type="text" class="form-control" id="lastName" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email*</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="phoneNumber" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phoneNumber" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
                </div>
                <!-- Add other fields as necessary, e.g., city, postal_code, country -->
                <button type="submit" name="update_profile" class="btn btn-submit mt-4">Save Changes</button>
            </form>

            <hr class="my-5">

            <h2 class="profile-section-title">My Orders</h2>
            <?php if (!empty($orders)): ?>
                <div class="table-responsive">
                    <table class="table table-hover orders-table align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Product</th>
                                <th scope="col">Order Details</th>
                                <th scope="col">Date</th>
                                <th scope="col">Total</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $nonCancellableStatuses = ['shipped', 'completed', 'cancelled'];
                            foreach ($orders as $order): 
                            ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($order['phone_name'])): ?>
                                            <?php echo htmlspecialchars($order['brand_name'].' '.$order['phone_name']); ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td>#<?php echo htmlspecialchars($order['id']); ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['order_date']))); ?></td>
                                    <td>€<?php echo htmlspecialchars(number_format($order['total'], 2)); ?></td>
                                    <td><span class="badge bg-<?php echo strtolower($order['status']) === 'shipped' ? 'success' : (strtolower($order['status']) === 'processing' || strtolower($order['status']) === 'paid' || strtolower($order['status']) === 'pending' ? 'warning' : (strtolower($order['status']) === 'completed' ? 'info' : (strtolower($order['status']) === 'cancelled' ? 'danger' : 'secondary'))); ?>"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span></td>
                                    <td>
                                        <?php if (!in_array(strtolower($order['status']), $nonCancellableStatuses)): ?>
                                            <a href="cancel_order.php?id=<?php echo htmlspecialchars($order['id']); ?>" class="btn btn-submit btn-sm" onclick="return confirm('Are you sure you want to cancel this order?');">Cancel Order</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>You have no orders yet.</p>
            <?php endif; ?>
        <?php else: ?>
            <p>Could not load profile information. Please try logging in again.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Page-specific JavaScript can be added here -->
</body>
</html> 