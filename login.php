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
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT * FROM clients WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                login($user);
                
                if (isset($_POST['remember'])) {
                    // Set a longer session lifetime
                    ini_set('session.cookie_lifetime', 30 * 24 * 60 * 60); // 30 days
                }
                
                header('Location: index.php');
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Log in - Mobil.io</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <style>
    body {
      background: #e6e6fa;
      min-height: 100vh;
    }
    .login-container {
      background: #e6d6fa;
      border-radius: 80px;
      max-width: 520px;
      margin: 40px auto;
      padding: 40px 32px 32px 32px;
      box-shadow: 0 4px 24px rgba(106,13,173,0.10);
    }
    .login-title {
      font-size: 2.5rem;
      font-weight: 700;
      text-align: center;
      margin-bottom: 0.5rem;
    }
    .login-desc {
      text-align: center;
      margin-bottom: 2rem;
      color: #333;
      font-size: 1rem;
    }
    .form-label {
      font-weight: 600;
    }
    .form-control, .input-group-text {
      border-radius: 0.4rem;
    }
    .login-btn {
      background: #6a0dad;
      color: #fff;
      font-weight: 600;
      border-radius: 2rem;
      padding: 0.7rem 0;
      font-size: 1.1rem;
      margin-top: 1rem;
    }
    .login-btn:hover {
      background: #8d3fd6;
    }
    .remember-me {
      color: #222;
      font-size: 1rem;
    }
    .forgot-link {
      float: right;
      font-size: 0.98rem;
      color: #222;
      text-decoration: underline;
      cursor: pointer;
    }
    .signup-link {
      color: #6a0dad;
      font-weight: 600;
      text-decoration: none;
    }
    .signup-link:hover {
      text-decoration: underline;
    }
    .back-arrow {
      color: #6a0dad;
      font-size: 1.5rem;
      text-decoration: none;
      display: inline-block;
      margin-bottom: 10px;
    }
    .divider {
      border-top: 1px solid #d1b3ff;
      margin: 1.5rem 0 1rem 0;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <a href="index.php" class="back-arrow"><i class="bi bi-arrow-left"></i></a>
    <div class="login-title">Log in</div>
    <div class="login-desc">Sign in to explore exclusive phone offers</div>
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <div class="input-group">
          <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com" required>
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
        </div>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <input type="password" class="form-control" id="password" name="password" placeholder="********************" required>
          <span class="input-group-text password-toggle" style="cursor: pointer;"><i class="bi bi-eye-slash"></i></span>
        </div>
      </div>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <input type="checkbox" id="remember" name="remember" class="form-check-input" checked>
          <label for="remember" class="form-check-label remember-me">Remember me</label>
        </div>
        <a href="forgot_password.php" class="forgot-link">Forgot your password?</a>
      </div>
      <button type="submit" class="btn login-btn w-100">Sign in</button>
    </form>
    <div class="divider"></div>
    <div class="text-center mb-2">
      or
    </div>
    <div class="text-center">
      Create a new account <a href="register.php" class="signup-link">Sign up</a>
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
