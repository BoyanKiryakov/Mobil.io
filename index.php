<?php
require_once 'includes/db.php';
require_once 'includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mobil.io</title>

  <!-- Bootstrap CSS -->
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
    rel="stylesheet"
  />

  <!-- Bootstrap Icons -->
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" 
    rel="stylesheet"
  />

  <!-- Roboto font -->
  <link 
    href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" 
    rel="stylesheet"
  />

  <!-- Custom CSS -->
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

  <?php include 'includes/header.php'; ?>

  <!-- Hero section under navbar -->
  <section class="horizontal-divider d-flex">
    <div class="hero-content text-start">
      <div class="hero-text">Discover Your Next Phone</div>
      <button class="btn shop-btn mt-3" onclick="window.location.href='catalogue.php'">Shop Now</button>
    </div>
    <img src="images/homeimg.png" alt="Phone showcase" class="hero-image" />
  </section>

  <!-- Most Popular Brands -->
  <section class="brands-section container py-5">
    <h2 class="section-title">Most Popular Brands</h2>
    <div class="row g-4 mt-3">
      <div class="col-md-4">
        <div class="card brand-card">
          <img src="images/homesamsung.jpg" class="card-img-top" alt="Samsung" />
          <div class="card-body text-center">
            <h5 class="card-title">Samsung</h5>
            <button class="btn view-more mt-2" onclick="window.location.href='catalogue.php?brand=Samsung'">View More</button>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card brand-card">
          <img src="images/homeiphone.jpg" class="card-img-top" alt="iPhone" />
          <div class="card-body text-center">
            <h5 class="card-title">iPhone</h5>
            <button class="btn view-more mt-2" onclick="window.location.href='catalogue.php?brand=iPhone'">View More</button>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card brand-card">
          <img src="images/homemotorola.jpg" class="card-img-top" alt="Motorola" />
          <div class="card-body text-center">
            <h5 class="card-title">Motorola</h5>
            <button class="btn view-more mt-2" onclick="window.location.href='catalogue.php?brand=Motorola'">View More</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Bootstrap JS bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
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