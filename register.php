<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

$error = '';
$success = '';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $terms = isset($_POST['terms']);
    
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!$terms) {
        $error = 'You must agree to the terms and conditions.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare('SELECT id FROM clients WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered. Please use a different email or login.';
            } else {
                // Create new user
                $stmt = $pdo->prepare('
                    INSERT INTO clients (first_name, last_name, email, password_hash)
                    VALUES (?, ?, ?, ?)
                ');
                
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt->execute([$firstName, $lastName, $email, $passwordHash]);
                
                $userId = $pdo->lastInsertId();
                
                // Log the user in
                login([
                    'id' => $userId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email
                ]);
                
                header('Location: index.php');
                exit();
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register - Mobil.io</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div class="register-container">
    <a href="login.php" class="back-arrow"><i class="bi bi-arrow-left"></i></a>
    <div class="register-title">Create your account</div>
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="POST" action="register.php">
      <div class="mb-3">
        <label for="first-name" class="form-label">First name</label>
        <input type="text" class="form-control" id="first-name" name="first_name" required value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
      </div>
      <div class="mb-3">
        <label for="last-name" class="form-label">Last name</label>
        <input type="text" class="form-control" id="last-name" name="last_name" required value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <div class="input-group">
          <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
        </div>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <input type="password" class="form-control" id="password" name="password" placeholder="********************" required>
          <span class="input-group-text password-toggle" style="cursor: pointer;"><i class="bi bi-eye-slash"></i></span>
        </div>
        <small class="form-text text-muted">Password must be at least 8 characters long.</small>
      </div>
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="terms" name="terms" required <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
        <label class="form-check-label terms-label" for="terms">
          I agree to the terms and conditions
        </label>
      </div>
      <button type="submit" class="btn register-btn w-100">Register</button>
    </form>
    <div class="policy-links">
      <a href="#">Terms of Service</a>
      <a href="#">Privacy Policy</a>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.querySelector('.password-toggle').addEventListener('click', function() {
        const passwordInput = document.querySelector('#password');
        const icon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.replace('bi-eye-slash', 'bi-eye');
        } else {
            passwordInput.type = 'password';
            icon.classList.replace('bi-eye', 'bi-eye-slash');
        }
    });
  </script>
</body>
</html>
