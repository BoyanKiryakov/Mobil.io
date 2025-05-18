<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password - Mobil.io</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <style>
    body {
      background: #e6e6fa;
      min-height: 100vh;
    }
    .reset-container {
      background: #e6d6fa;
      border-radius: 80px;
      max-width: 520px;
      margin: 40px auto;
      padding: 40px 32px 32px 32px;
      box-shadow: 0 4px 24px rgba(106,13,173,0.10);
    }
    .reset-title {
      font-size: 2.2rem;
      font-weight: 700;
      text-align: center;
      margin-bottom: 0.5rem;
    }
    .reset-desc {
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
    .reset-btn {
      background: #6a0dad;
      color: #fff;
      font-weight: 600;
      border-radius: 2rem;
      padding: 0.7rem 0;
      font-size: 1.1rem;
      margin-top: 1rem;
    }
    .reset-btn:hover {
      background: #8d3fd6;
    }
    .back-arrow {
      color: #6a0dad;
      font-size: 1.5rem;
      text-decoration: none;
      display: inline-block;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="reset-container">
    <a href="login.html" class="back-arrow"><i class="bi bi-arrow-left"></i></a>
    <div class="reset-title">Reset Password</div>
    <div class="reset-desc">Enter your email and new password to reset your account password.</div>
    <form>
      <div class="mb-3">
        <label for="email" class="form-label">Email address</label>
        <div class="input-group">
          <input type="email" class="form-control" id="email" placeholder="email@example.com" required>
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
        </div>
      </div>
      <div class="mb-3">
        <label for="new-password" class="form-label">New Password</label>
        <div class="input-group">
          <input type="password" class="form-control" id="new-password" placeholder="Enter new password" required>
          <span class="input-group-text"><i class="bi bi-eye-slash"></i></span>
        </div>
      </div>
      <div class="mb-3">
        <label for="confirm-password" class="form-label">Confirm New Password</label>
        <div class="input-group">
          <input type="password" class="form-control" id="confirm-password" placeholder="Confirm new password" required>
          <span class="input-group-text"><i class="bi bi-eye-slash"></i></span>
        </div>
      </div>
      <button type="submit" class="btn reset-btn w-100">Reset Password</button>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
