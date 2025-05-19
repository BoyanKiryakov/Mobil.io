<?php
require_once __DIR__ . '/session.php';
$currentUser = getCurrentUser();
?>
<nav class="navbar">
  <div class="navbar-logo">
    <a href="index.php">
      <img src="images/logonew.png" alt="Mobil.io Logo" class="navbar-logo-img" />
    </a>
  </div>
  
  <div class="navbar-search-wrapper">
    <div class="search-bar">
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" class="form-control" placeholder="Search..." aria-label="Search" />
      </div>
    </div>
  </div>

  <div class="navbar-links-wrapper">
    <ul class="nav-links">
      <li><a class="nav-link" href="catalogue.php">Catalogue</a></li>
      <li><a class="nav-link" href="compare.php">Compare phones</a></li>
      <li><a class="nav-link" href="contact.php">Contact us</a></li>
    </ul>
  </div>

  <div class="action-icons">
    <i id="theme-toggle" class="bi bi-moon-fill"></i>
    <div class="dropdown d-inline">
      <i class="bi bi-person-fill dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false"></i>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
        <?php if ($currentUser): ?>
          <li><span class="dropdown-item-text">Welcome, <?php echo htmlspecialchars($currentUser['first_name']); ?>!</span></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="profile.php">View Profile</a></li>
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

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme');
    const themeToggle = document.getElementById('theme-toggle');
    
    if (savedTheme === 'dark') {
      document.body.classList.add('dark-theme');
      themeToggle.classList.remove('bi-moon-fill');
      themeToggle.classList.add('bi-sun-fill');
    }
  });

  // Theme toggle with persistence
  const themeToggle = document.getElementById('theme-toggle');
  themeToggle.addEventListener('click', () => {
    themeToggle.classList.add('spin');
    themeToggle.addEventListener('animationend', () => themeToggle.classList.remove('spin'), { once: true });
    
    document.body.classList.toggle('dark-theme');
    themeToggle.classList.toggle('bi-moon-fill');
    themeToggle.classList.toggle('bi-sun-fill');
    
    // Save theme preference
    const isDarkTheme = document.body.classList.contains('dark-theme');
    localStorage.setItem('theme', isDarkTheme ? 'dark' : 'light');
  });
</script> 