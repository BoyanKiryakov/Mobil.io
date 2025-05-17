<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Delivery Details - Mobil.io</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="styles.css" />
  <style>
    body { background: #fff; }
    .delivery-title {
      text-align: center;
      color: #6a0dad;
      font-size: 2rem;
      font-weight: 700;
      margin: 2rem 0 2rem 0;
      font-family: 'Roboto', sans-serif;
    }
    .delivery-section {
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 2px 12px rgba(106,13,173,0.07);
      padding: 2rem 2rem 1.5rem 2rem;
      margin: 0 auto 2rem auto;
      max-width: 800px;
    }
    .delivery-section h5, .delivery-section .section-title {
      color: #6a0dad;
      font-weight: 700;
      margin-bottom: 1.2rem;
      font-size: 1.2rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .delivery-section .form-check-label {
      font-size: 1.1rem;
      color: #222;
      margin-left: 0.5rem;
    }
    .delivery-section .form-check {
      margin-bottom: 0.5rem;
    }
    .customer-info-input {
      margin-bottom: 1rem;
      border-radius: 8px;
      border: none;
      background: #f5f5f5;
      box-shadow: 0 2px 8px rgba(106,13,173,0.06);
      padding: 0.7rem 1rem;
      font-size: 1rem;
      width: 100%;
    }
    .delivery-row {
      display: flex;
      gap: 2rem;
      margin-bottom: 2rem;
      flex-wrap: wrap;
    }
    .delivery-col {
      flex: 1;
      min-width: 320px;
    }
    .order-summary {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 2px 12px rgba(106,13,173,0.07);
      padding: 2rem 2rem 1.5rem 2rem;
      min-width: 320px;
      max-width: 400px;
      height: fit-content;
      margin-top: 0;
    }
    .order-summary-title {
      color: #6a0dad;
      font-weight: 700;
      font-size: 1.2rem;
      margin-bottom: 1.2rem;
    }
    .order-summary-list {
      font-size: 1rem;
      margin-bottom: 1.5rem;
    }
    .order-summary-list li {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.5rem;
    }
    .order-summary-total {
      font-size: 1.3rem;
      font-weight: 700;
      color: #6a0dad;
      text-align: right;
      margin: 1.5rem 0 1rem 0;
    }
    .finish-order-btn {
      width: 100%;
      background: #6a0dad;
      color: #fff;
      border: none;
      border-radius: 0.7rem;
      padding: 0.7rem 0;
      font-size: 1.1rem;
      font-weight: 700;
      margin-top: 1rem;
      transition: background 0.2s;
    }
    .finish-order-btn:hover { background: #8d3fd6; }
    @media (max-width: 900px) {
      .delivery-row { flex-direction: column; }
      .order-summary { min-width: unset; max-width: unset; }
    }
  </style>
</head>
<body>
  <!-- Navigation Bar (identical to index.html) -->
  <nav class="navbar sticky-top">
    <div class="navbar-logo d-flex align-items-center">
      <a href="index.html">
        <img src="images/mobilio-logo.png" alt="Mobil.io Logo" class="navbar-logo-img" />
      </a>
    </div>
    <div class="navbar-search-wrapper">
      <div class="input-group search-bar">
        <span class="input-group-text">
          <i class="bi bi-search"></i>
        </span>
        <input type="text" class="form-control" placeholder="Search..." aria-label="Search" />
      </div>
    </div>
    <div class="navbar-links-wrapper">
      <div class="nav-links">
        <a class="nav-link" href="catalogue.html">Catalogue</a>
        <a class="nav-link" href="compare.html">Compare phones</a>
        <a class="nav-link" href="#">Contact us</a>
      </div>
    </div>
    <div class="action-icons">
      <i id="theme-toggle" class="bi bi-moon-fill"></i>
      <div class="dropdown d-inline">
        <i class="bi bi-person-fill dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="cursor:pointer;"></i>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
          <li><a class="dropdown-item" href="login.html">Login</a></li>
          <li><a class="dropdown-item" href="register.html">Register</a></li>
        </ul>
      </div>
      <a href="cart.html"><i class="bi bi-cart-fill"></i></a>
    </div>
  </nav>

  <div class="delivery-title">Delivery Details</div>

  <!-- Method of Delivery -->
  <div class="delivery-section">
    <div class="section-title"><i class="bi bi-truck"></i> Method of delivery</div>
    <div class="form-check">
      <input class="form-check-input" type="radio" name="deliveryMethod" id="toAddress" checked>
      <label class="form-check-label" for="toAddress">To Address</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="radio" name="deliveryMethod" id="speedyOffice">
      <label class="form-check-label" for="speedyOffice">Speedy Office</label>
    </div>
  </div>

  <!-- Customer Information -->
  <div class="delivery-section">
    <div class="section-title"><i class="bi bi-person"></i> Customer Information</div>
    <input type="text" class="customer-info-input" placeholder="Name" />
    <input type="text" class="customer-info-input" placeholder="Telephone" />
    <input type="text" class="customer-info-input" placeholder="Enter City" />
    <input type="text" class="customer-info-input" placeholder="Address or Company Address" />
  </div>

  <!-- Payment Method -->
  <div class="delivery-section" style="max-width: 800px;">
    <div class="section-title"><i class="bi bi-bag"></i> Payment Method</div>
    <div class="form-check">
      <input class="form-check-input" type="radio" name="paymentMethod" id="cash" checked>
      <label class="form-check-label" for="cash">Cash</label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="radio" name="paymentMethod" id="card">
      <label class="form-check-label" for="card">Card</label>
    </div>
  </div>

  <!-- Order Summary -->
  <div class="order-summary" style="margin: 0 auto 2rem auto; max-width: 800px;">
    <div class="order-summary-title">Order Summary</div>
    <ul class="order-summary-list list-unstyled">
      <li>
        <span>Subtotal:</span>
        <span id="order-subtotal">€ 2 271,99</span>
      </li>
      <li>
        <span>Delivery price :</span>
        <span id="delivery-price">€0</span>
      </li>
      <li>
        <span><b>Total:</b></span>
        <span id="order-total" style="font-weight:700;color:#6a0dad;">€ 2 271,99</span>
      </li>
    </ul>
    <a href="endpage.html" class="finish-order-btn" style="display:block; text-align:center; text-decoration:none;">Finish order</a>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Theme toggle (identical to index.html)
    const themeToggle = document.getElementById('theme-toggle');
    themeToggle.addEventListener('click', () => {
      themeToggle.classList.add('spin');
      themeToggle.addEventListener('animationend', () => themeToggle.classList.remove('spin'), { once: true });
      document.body.classList.toggle('dark-theme');
      themeToggle.classList.toggle('bi-moon-fill');
      themeToggle.classList.toggle('bi-sun-fill');
    });

    const deliveryPriceElem = document.getElementById('delivery-price');
    const subtotalElem = document.getElementById('order-subtotal');
    const totalElem = document.getElementById('order-total');
    const toAddressRadio = document.getElementById('toAddress');
    const speedyOfficeRadio = document.getElementById('speedyOffice');

    // Parse the subtotal from the text (assumes format "€ 2 271,99")
    function parseEuro(str) {
      return parseFloat(str.replace(/[^\d,]/g, '').replace(',', '.'));
    }

    function formatEuro(num) {
      return '€ ' + num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}).replace('.', ',');
    }

    function updateDeliveryPrice() {
      let delivery = 0;
      if (toAddressRadio.checked) {
        delivery = 5.99;
      }
      deliveryPriceElem.textContent = formatEuro(delivery);

      // Calculate and update total
      const subtotal = parseEuro(subtotalElem.textContent);
      const total = subtotal + delivery;
      totalElem.textContent = formatEuro(total);
    }

    toAddressRadio.addEventListener('change', updateDeliveryPrice);
    speedyOfficeRadio.addEventListener('change', updateDeliveryPrice);

    // Initialize on page load
    updateDeliveryPrice();
  </script>
</body>
</html>
