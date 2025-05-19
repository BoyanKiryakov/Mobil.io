<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

// Get phone ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: catalogue.php');
    exit;
}

try {
    // Get phone details
    $stmt = $pdo->prepare("
        SELECT phones.*, brands.name as brand_name 
        FROM phones 
        JOIN brands ON phones.brand_id = brands.id 
        WHERE phones.id = ?
    ");
    $stmt->execute([$_GET['id']]);
    $phone = $stmt->fetch();

    if (!$phone) {
        header('Location: catalogue.php');
        exit;
    }

    // Get reviews for this phone
    $stmt = $pdo->prepare("
        SELECT reviews.*, clients.first_name, clients.last_name 
        FROM reviews 
        JOIN clients ON reviews.client_id = clients.id 
        WHERE phone_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_GET['id']]);
    $reviews = $stmt->fetchAll();

} catch (PDOException $e) {
    // Log error in production
    header('Location: catalogue.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($phone['brand_name'] . ' ' . $phone['name']); ?> - Mobil.io</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container my-5">
        <h1 class="product-title"><?php echo htmlspecialchars($phone['brand_name'] . ' ' . $phone['name']); ?></h1>

        <div class="row">
            <!-- Image Gallery Column -->
            <div class="col-md-8">
                <div class="d-flex">
                    <div class="thumbnail-container">
                        <?php
                        $base_image = strtolower(str_replace(' ', '', $phone['name']));
                        $images = [
                            $base_image . '.jpg',
                            $base_image . '_2.jpg',
                            $base_image . '_3.jpg',
                            $base_image . '_4.jpg'
                        ];
                        foreach ($images as $index => $image): ?>
                            <img src="images/phones/<?php echo $image; ?>" 
                                 class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 alt="<?php echo htmlspecialchars($phone['name']); ?>"
                                 onerror="this.src='images/phone-placeholder.jpg'"
                                 onclick="updateMainImage(this.src)">
                        <?php endforeach; ?>
                    </div>
                    <div class="main-image-container">
                        <img src="images/phones/<?php echo $images[0]; ?>" 
                             class="main-image" 
                             id="mainImage"
                             alt="<?php echo htmlspecialchars($phone['name']); ?>"
                             onerror="this.src='images/phone-placeholder.jpg'">
                    </div>
                </div>
            </div>

            <!-- Price and Add to Cart Column -->
            <div class="col-md-4">
                <div class="price-box">
                    <div class="price">€<?php echo number_format($phone['price'], 2); ?></div>
                    <button class="btn btn-add-cart" onclick="addToCart(<?php echo $phone['id']; ?>)">
                        Add to Cart
                    </button>
                    <ul class="features-list">
                        <li>
                            <i class="bi bi-shield-check"></i>
                            Cost Protection
                        </li>
                        <li>
                            <i class="bi bi-star"></i>
                            Reviews
                        </li>
                        <li>
                            <i class="bi bi-arrow-return-left"></i>
                            Can be refunded
                        </li>
                        <li>
                            <i class="bi bi-headset"></i>
                            24/7 Contact with us
                        </li>
                        <li>
                            <i class="bi bi-truck"></i>
                            Free Shipping
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Technical Specifications -->
        <h2 class="specs-title">Technical Specifications</h2>
        <table class="specs-table">
            <tr>
                <td><i class="bi bi-phone"></i>Operating System</td>
                <td><?php echo htmlspecialchars($phone['os']); ?></td>
            </tr>
            <tr>
                <td><i class="bi bi-display"></i>Display</td>
                <td><?php echo htmlspecialchars($phone['display']); ?></td>
            </tr>
            <tr>
                <td><i class="bi bi-cpu"></i>Processor</td>
                <td><?php echo htmlspecialchars($phone['processor']); ?></td>
            </tr>
            <?php if ($phone['graphics']): ?>
            <tr>
                <td><i class="bi bi-gpu-card"></i>Graphics</td>
                <td><?php echo htmlspecialchars($phone['graphics']); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td><i class="bi bi-memory"></i>RAM</td>
                <td><?php echo htmlspecialchars($phone['ram']); ?></td>
            </tr>
            <tr>
                <td><i class="bi bi-device-hdd"></i>Storage</td>
                <td><?php echo htmlspecialchars($phone['storage']); ?></td>
            </tr>
            <tr>
                <td><i class="bi bi-camera"></i>Rear Cameras</td>
                <td><?php echo htmlspecialchars($phone['rear_cameras']); ?></td>
            </tr>
            <tr>
                <td><i class="bi bi-camera-video"></i>Front Camera</td>
                <td><?php echo htmlspecialchars($phone['front_camera']); ?></td>
            </tr>
            <tr>
                <td><i class="bi bi-battery"></i>Battery</td>
                <td><?php echo htmlspecialchars($phone['battery']); ?></td>
            </tr>
            <?php if ($phone['connectivity']): ?>
            <tr>
                <td><i class="bi bi-wifi"></i>Connectivity</td>
                <td><?php echo htmlspecialchars($phone['connectivity']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($phone['security']): ?>
            <tr>
                <td><i class="bi bi-shield-lock"></i>Security</td>
                <td><?php echo htmlspecialchars($phone['security']); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($phone['audio']): ?>
            <tr>
                <td><i class="bi bi-music-note-beamed"></i>Audio</td>
                <td><?php echo htmlspecialchars($phone['audio']); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td><i class="bi bi-rulers"></i>Dimensions</td>
                <td><?php echo htmlspecialchars($phone['dimensions']); ?></td>
            </tr>
            <tr>
                <td><i class="bi bi-box-seam"></i>Weight</td>
                <td><?php echo htmlspecialchars($phone['weight']); ?></td>
            </tr>
        </table>

        <!-- Reviews Section -->
        <div class="reviews-section">
            <h2 class="specs-title">Reviews</h2>
            <?php if (empty($reviews)): ?>
                <p>No reviews yet for this product.</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div>
                                <strong><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></strong>
                                <div class="review-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="review-date">
                                <?php echo date('F j, Y', strtotime($review['created_at'])); ?>
                            </div>
                        </div>
                        <div class="review-text">
                            <?php echo htmlspecialchars($review['review_text']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateMainImage(src) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
                if (thumb.src === src) {
                    thumb.classList.add('active');
                }
            });
        }

        function addToCart(phoneId) {
            fetch('cart_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&phone_id=${phoneId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Item added to cart successfully!');
                } else {
                    alert(data.message || 'Error adding item to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding item to cart');
            });
        }
    </script>
</body>
</html> 