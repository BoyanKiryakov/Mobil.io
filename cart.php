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

// Get cart items for the current user
try {
    $cart_items = [];
    $total = 0;
    $subtotal = 0;
    $shipping = 0;
    $tax_rate = 0.21; // 21% VAT
    
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
        
        // Calculate shipping (free over €500)
        $shipping = $subtotal >= 500 ? 0 : 15;
        
        // Calculate total with tax and shipping
        $tax = $subtotal * $tax_rate;
        $total = $subtotal + $tax + $shipping;
    }
} catch (PDOException $e) {
    // Log error in production
    $error_message = "Error accessing cart data";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Shopping Cart - Mobil.io</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <div class="cart-container">
    <!-- Cart List -->
    <div class="cart-list">
      <a href="catalogue.php" class="continue-shopping"><i class="bi bi-arrow-left"></i> Continue Shopping</a>
      <h2>Your Shopping Cart</h2>
      <?php if (!empty($cart_items)): ?>
        <?php foreach ($cart_items as $item): ?>
          <div class="cart-item" data-item-id="<?php echo $item['id']; ?>">
            <img src="images/phones/<?php echo strtolower(str_replace(' ', '', $item['name'])); ?>.jpg" 
                 class="cart-item-img" 
                 alt="<?php echo htmlspecialchars($item['brand_name'] . ' ' . $item['name']); ?>"
                 onerror="this.src='images/phone-placeholder.jpg'" />
            <div class="cart-item-details">
              <div class="cart-item-title"><?php echo htmlspecialchars($item['brand_name'] . ' ' . $item['name']); ?></div>
              <div class="cart-item-price">€<?php echo number_format($item['price'], 2); ?></div>
            </div>
            <div class="cart-item-qty">
              Qty
              <select class="form-select form-select-sm" style="width:auto;display:inline-block;margin-left:6px;"
                      onchange="updateQuantity(<?php echo $item['id']; ?>, 'set', this.value)">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <option value="<?php echo $i; ?>" <?php echo $item['quantity'] == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <span class="cart-item-remove" onclick="removeItem(<?php echo $item['id']; ?>)">
              <i class="bi bi-trash"></i> Remove
            </span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="text-center my-5">
          <h3>Your cart is empty</h3>
          <p>Looks like you haven't added any phones to your cart yet.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Cart Summary -->
    <?php if (!empty($cart_items)): ?>
      <div class="cart-summary">
        <div class="cart-summary-title">Your Order</div>
        <ul class="cart-summary-list list-unstyled">
          <?php foreach ($cart_items as $item): ?>
            <li>
              <span><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['brand_name'] . ' ' . $item['name']); ?></span>
              <span>€<?php echo number_format($item['item_total'], 2); ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="cart-summary-promo">
          <input type="text" placeholder="Enter promo code" />
          <button>Apply</button>
        </div>
        <div class="cart-summary-total">€<?php echo number_format($total, 2); ?></div>
        <button class="cart-summary-checkout" onclick="window.location.href='delivery.php'">Checkout</button>
      </div>
    <?php endif; ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function updateQuantity(itemId, action, value = null) {
        let data = {
            action: 'update',
            phone_id: itemId,
            quantity: value
        };

        fetch('cart_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Error updating cart');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating cart');
        });
    }

    function removeItem(itemId) {
        if (confirm('Are you sure you want to remove this item from your cart?')) {
            fetch('cart_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'update',
                    phone_id: itemId,
                    quantity: 0
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error removing item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error removing item');
            });
        }
    }
  </script>
</body>
</html>
