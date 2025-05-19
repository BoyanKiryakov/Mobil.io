<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

$email = '';
$name = '';

// If user is logged in, get their email and name
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT email, first_name, last_name FROM clients WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user) {
            $email = $user['email'];
            $name = $user['first_name'] . ' ' . $user['last_name'];
        }
    } catch (PDOException $e) {
        // Log error in production
    }
}

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $name = $_POST['name'] ?? '';
    $message_text = $_POST['message'] ?? '';

    // Simple validation
    if (empty($email) || empty($name) || empty($message_text)) {
        $message = 'All fields are required';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address';
        $messageType = 'danger';
    } else {
        // For now, just show success message without storing in database
        $message = 'Your message has been sent successfully! (Demo mode)';
        $messageType = 'success';
        
        // Clear form after successful submission
        if ($messageType === 'success') {
            $message_text = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Mobil.io</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <h1 class="contact-title">Contact Us</h1>
        
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> text-center mb-4">
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="contact-form">
            <form method="POST" action="contact.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($email); ?>" 
                           required>
                </div>
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" 
                           class="form-control" 
                           id="name" 
                           name="name" 
                           value="<?php echo htmlspecialchars($name); ?>" 
                           required>
                </div>
                <div class="mb-4">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control message-box" 
                              id="message" 
                              name="message" 
                              required><?php echo isset($message_text) ? htmlspecialchars($message_text) : ''; ?></textarea>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-submit">Send Message</button>
                </div>
            </form>
        </div>
    </div>

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
    </script>
</body>
</html> 