<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

header('Content-Type: application/json');

// Initialize current user
$currentUser = getCurrentUser();

if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit;
}

try {
    $action = $_POST['action'];
    
    // Handle logged-in users
    if ($currentUser) {
        // Get or create cart for user
        $stmt = $pdo->prepare("
            SELECT id FROM carts 
            WHERE client_id = ?
        ");
        $stmt->execute([$currentUser['id']]);
        $cart = $stmt->fetch();
        
        if (!$cart) {
            // Create new cart
            $stmt = $pdo->prepare("
                INSERT INTO carts (client_id, session_id) 
                VALUES (?, ?)
            ");
            $stmt->execute([$currentUser['id'], session_id()]);
            $cart_id = $pdo->lastInsertId();
        } else {
            $cart_id = $cart['id'];
        }
        
        switch ($action) {
            case 'add':
                if (!isset($_POST['phone_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Missing phone_id']);
                    exit;
                }
                
                $phone_id = (int)$_POST['phone_id'];
                $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
                
                // Check if item already exists in cart
                $stmt = $pdo->prepare("
                    SELECT quantity FROM cart_items 
                    WHERE cart_id = ? AND phone_id = ?
                ");
                $stmt->execute([$cart_id, $phone_id]);
                $existing_item = $stmt->fetch();
                
                if ($existing_item) {
                    // Update quantity
                    $stmt = $pdo->prepare("
                        UPDATE cart_items 
                        SET quantity = quantity + ? 
                        WHERE cart_id = ? AND phone_id = ?
                    ");
                    $stmt->execute([$quantity, $cart_id, $phone_id]);
                } else {
                    // Add new item
                    $stmt = $pdo->prepare("
                        INSERT INTO cart_items (cart_id, phone_id, quantity) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$cart_id, $phone_id, $quantity]);
                }
                break;
                
            case 'update':
                if (!isset($_POST['phone_id']) || !isset($_POST['quantity'])) {
                    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
                    exit;
                }
                
                $phone_id = (int)$_POST['phone_id'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity <= 0) {
                    // Remove item if quantity is 0 or negative
                    $stmt = $pdo->prepare("
                        DELETE FROM cart_items 
                        WHERE cart_id = ? AND phone_id = ?
                    ");
                    $stmt->execute([$cart_id, $phone_id]);
                } else {
                    // Update quantity directly to the new value
                    $stmt = $pdo->prepare("
                        UPDATE cart_items 
                        SET quantity = ? 
                        WHERE cart_id = ? AND phone_id = ?
                    ");
                    $stmt->execute([$quantity, $cart_id, $phone_id]);
                }
                break;
                
            case 'remove':
                if (!isset($_POST['phone_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Missing phone_id']);
                    exit;
                }
                
                $phone_id = (int)$_POST['phone_id'];
                
                $stmt = $pdo->prepare("
                    DELETE FROM cart_items 
                    WHERE cart_id = ? AND phone_id = ?
                ");
                $stmt->execute([$cart_id, $phone_id]);
                break;
        }
    } else {
        // Handle guest cart using session
        if (!isset($_SESSION['cart_items'])) {
            $_SESSION['cart_items'] = [];
        }
        
        switch ($action) {
            case 'add':
                if (!isset($_POST['phone_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Missing phone_id']);
                    exit;
                }
                
                $phone_id = (int)$_POST['phone_id'];
                $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
                
                if (isset($_SESSION['cart_items'][$phone_id])) {
                    $_SESSION['cart_items'][$phone_id] += $quantity;
                } else {
                    $_SESSION['cart_items'][$phone_id] = $quantity;
                }
                break;
                
            case 'update':
                if (!isset($_POST['phone_id']) || !isset($_POST['quantity'])) {
                    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
                    exit;
                }
                
                $phone_id = (int)$_POST['phone_id'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity <= 0) {
                    unset($_SESSION['cart_items'][$phone_id]);
                } else {
                    $_SESSION['cart_items'][$phone_id] = $quantity;
                }
                break;
                
            case 'remove':
                if (!isset($_POST['phone_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Missing phone_id']);
                    exit;
                }
                
                $phone_id = (int)$_POST['phone_id'];
                unset($_SESSION['cart_items'][$phone_id]);
                break;
        }
    }
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    // Log error in production
    echo json_encode(['success' => false, 'message' => 'Database error']);
} 