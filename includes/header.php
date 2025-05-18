<?php
require_once __DIR__ . '/session.php';
$currentUser = getCurrentUser();
?>
<!-- Sticky navbar -->
<nav class="navbar sticky-top">
  <div class="navbar-logo d-flex align-items-center">
    <a href="index.php">
      <img src="images/mobilio-logo.png" alt="Mobil.io Logo" class="navbar-logo-img" />
    </a>
  </div>
  <div class="navbar-search-wrapper">
    <div class="input-group search-bar">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input type="text" class="form-control" placeholder="Search..." aria-label="Search" />
    </div>
  </div>
  <div class="navbar-links-wrapper">
    <div class="nav-links">
      <a class="nav-link" href="catalogue.php">Catalogue</a>
      <a class="nav-link" href="compare.php">Compare phones</a>
      <a class="nav-link" href="#">Contact us</a>
    </div>
  </div>
  <div class="action-icons">
    <i id="theme-toggle" class="bi bi-moon-fill"></i>
    <div class="dropdown d-inline">
      <i class="bi bi-person-fill dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="cursor:pointer;"></i>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
        <?php if ($currentUser): ?>
          <li><span class="dropdown-item-text">Welcome, <?php echo htmlspecialchars($currentUser['first_name']); ?>!</span></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="profile.php">View Profile</a></li>
          <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="logout.php">Log out</a></li>
        <?php else: ?>
          <li><a class="dropdown-item" href="login.php">Login</a></li>
          <li><a class="dropdown-item" href="register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </div>
    <a href="cart.php"><i class="bi bi-cart-fill"></i></a>
  </div>
</nav> 