<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

// Initialize current user
$currentUser = getCurrentUser();

// Redirect to login if not authenticated
if (!$currentUser) {
    header('Location: login.php');
    exit;
}

// Get client details
try {
    $stmt = $pdo->prepare("
        SELECT first_name, last_name, email
        FROM clients
        WHERE id = ?
    ");
    $stmt->execute([$currentUser['id']]);
    $clientDetails = $stmt->fetch();
} catch (PDOException $e) {
    // Handle error silently
}

// Get cart items and totals
try {
    $cart_items = [];
    $total = 0;
    $subtotal = 0;
    $shipping = 0;
    
    // Get cart for logged-in user
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id
        FROM carts c
        WHERE c.client_id = ?
    ");
    $stmt->execute([$currentUser['id']]);
    $cart = $stmt->fetch();
    
    if ($cart) {
        // Get cart items with phone details
        $stmt = $pdo->prepare("
            SELECT ci.*, p.*, b.name as brand_name,
                   (p.price * ci.quantity) as item_total
            FROM cart_items ci
            JOIN phones p ON ci.phone_id = p.id
            JOIN brands b ON p.brand_id = b.id
            WHERE ci.cart_id = ?
        ");
        $stmt->execute([$cart['cart_id']]);
        $cart_items = $stmt->fetchAll();
        
        // Calculate totals
        foreach ($cart_items as $item) {
            $subtotal += $item['item_total'];
        }
    }

    // If cart is empty, redirect to cart page
    if (empty($cart_items)) {
        header('Location: cart.php');
        exit;
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $errors = [];
        
        // Validate required fields
        $required_fields = ['name', 'telephone', 'city', 'address'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst($field) . ' is required';
            }
        }
        
        if (empty($errors)) {
            try {
                $pdo->beginTransaction();

                // Check stock availability for all items
                foreach ($cart_items as $item) {
                    $stmt = $pdo->prepare("
                        SELECT stock 
                        FROM phones 
                        WHERE id = ? 
                        FOR UPDATE
                    ");
                    $stmt->execute([$item['id']]);
                    $current_stock = $stmt->fetchColumn();

                    if ($current_stock < $item['quantity']) {
                        throw new Exception("Not enough stock for " . $item['brand_name'] . " " . $item['name']);
                    }
                }
                
                // Create order
                $stmt = $pdo->prepare("
                    INSERT INTO orders (
                        client_id,
                        total,
                        status
                    ) VALUES (?, ?, 'pending')
                ");
                
                // Calculate final total
                $shipping = $_POST['deliveryMethod'] === 'toAddress' ? 5.99 : 0;
                $total = $subtotal + $shipping;
                
                $stmt->execute([
                    $currentUser['id'],
                    $total
                ]);
                
                $order_id = $pdo->lastInsertId();
                
                // Add order items
                $stmt = $pdo->prepare("
                    INSERT INTO order_items (
                        order_id,
                        phone_id,
                        quantity,
                        unit_price
                    ) VALUES (?, ?, ?, ?)
                ");
                
                foreach ($cart_items as $item) {
                    // Add order item
                    $stmt->execute([
                        $order_id,
                        $item['id'],
                        $item['quantity'],
                        $item['price']
                    ]);

                    // Update stock
                    $stmt = $pdo->prepare("
                        UPDATE phones 
                        SET stock = stock - ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$item['quantity'], $item['id']]);
                }
                
                // Clear cart
                $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_id = ?");
                $stmt->execute([$cart['cart_id']]);
                
                $pdo->commit();
                
                // Redirect to success page
                header('Location: endpage.php?id=' . $order_id);
                exit;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = $e->getMessage();
            }
        }
    }
    
} catch (PDOException $e) {
    $error_message = "Error accessing cart data";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Delivery Details - Mobil.io</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <div class="delivery-title">Delivery Details</div>

  <?php if (!empty($errors)): ?>
    <div class="delivery-section">
        <div class="error-message">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <!-- Method of Delivery -->
    <div class="delivery-section">
      <div class="section-title"><i class="bi bi-truck"></i> Method of delivery</div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="deliveryMethod" id="toAddress" value="toAddress" checked>
        <label class="form-check-label" for="toAddress">To Address (€5.99)</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="deliveryMethod" id="speedyOffice" value="speedyOffice">
        <label class="form-check-label" for="speedyOffice">Speedy Office (Free)</label>
      </div>
    </div>

    <!-- Customer Information -->
    <div class="delivery-section">
      <div class="section-title"><i class="bi bi-person"></i> Customer Information</div>
      <input type="text" name="name" class="customer-info-input" placeholder="Name" 
             value="<?php echo htmlspecialchars(
                 $_POST['name'] ?? 
                 ($clientDetails ? $clientDetails['first_name'] . ' ' . $clientDetails['last_name'] : '')
             ); ?>" 
             required />
      <input type="tel" name="telephone" class="customer-info-input" placeholder="Telephone" 
             value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>" 
             required />
      <input type="text" name="city" class="customer-info-input" placeholder="Enter City" 
             value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" 
             required />
      <input type="text" name="address" class="customer-info-input" placeholder="Address or Company Address" 
             value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" 
             required />
      <?php if ($clientDetails): ?>
        <div class="text-muted mt-2">
          <small><i class="bi bi-info-circle"></i> Some fields are pre-filled based on your account information.</small>
        </div>
      <?php endif; ?>
    </div>

    <!-- Payment Method -->
    <div class="delivery-section">
      <div class="section-title"><i class="bi bi-bag"></i> Payment Method</div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="paymentMethod" id="cash" value="cash" checked>
        <label class="form-check-label" for="cash">Cash</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="paymentMethod" id="card" value="card">
        <label class="form-check-label" for="card">Card</label>
      </div>
    </div>

    <!-- Order Summary -->
    <div class="order-summary" style="margin: 0 auto 2rem auto; max-width: 800px;">
      <div class="order-summary-title">Order Summary</div>
      <ul class="order-summary-list list-unstyled">
        <?php foreach ($cart_items as $item): ?>
          <li>
            <span><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['brand_name'] . ' ' . $item['name']); ?></span>
            <span>€<?php echo number_format($item['item_total'], 2); ?></span>
          </li>
        <?php endforeach; ?>
        <li><hr></li>
        <li>
          <span>Subtotal:</span>
          <span id="order-subtotal">€<?php echo number_format($subtotal, 2); ?></span>
        </li>
        <li>
          <span>Delivery price:</span>
          <span id="delivery-price">€0.00</span>
        </li>
        <li>
          <span><b>Total:</b></span>
          <span id="order-total" style="font-weight:700;color:#6a0dad;">€<?php echo number_format($subtotal, 2); ?></span>
        </li>
      </ul>
      <button type="submit" class="finish-order-btn">Place Order</button>
    </div>
  </form>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Theme toggle
    const themeToggle = document.getElementById('theme-toggle');
    themeToggle.addEventListener('click', () => {
      themeToggle.classList.add('spin');
      themeToggle.addEventListener('animationend', () => themeToggle.classList.remove('spin'), { once: true });
      document.body.classList.toggle('dark-theme');
      themeToggle.classList.toggle('bi-moon-fill');
      themeToggle.classList.toggle('bi-sun-fill');
    });

    // Delivery price calculation
    const deliveryPriceElem = document.getElementById('delivery-price');
    const subtotalElem = document.getElementById('order-subtotal');
    const totalElem = document.getElementById('order-total');
    const toAddressRadio = document.getElementById('toAddress');
    const speedyOfficeRadio = document.getElementById('speedyOffice');

    function parseEuro(str) {
      return parseFloat(str.replace(/[€,]/g, '').trim());
    }

    function formatEuro(num) {
      return '€' + num.toFixed(2);
    }

    function updateTotal() {
      const subtotal = parseEuro(subtotalElem.textContent);
      const delivery = toAddressRadio.checked ? 5.99 : 0;
      
      deliveryPriceElem.textContent = formatEuro(delivery);
      totalElem.textContent = formatEuro(subtotal + delivery);
    }

    toAddressRadio.addEventListener('change', updateTotal);
    speedyOfficeRadio.addEventListener('change', updateTotal);

    // Initialize total
    updateTotal();
  </script>
</body>
</html>
