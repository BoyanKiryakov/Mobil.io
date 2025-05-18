<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

// Get all phones for the dropdowns
try {
    $stmt = $pdo->prepare("
        SELECT phones.*, brands.name as brand_name 
        FROM phones 
        JOIN brands ON phones.brand_id = brands.id 
        ORDER BY brands.name, phones.name
    ");
    $stmt->execute();
    $phones = $stmt->fetchAll();
} catch (PDOException $e) {
    $phones = [];
    // Log error in production
}

// Function to extract megapixels from camera description
function extractMainCameraMp($cameraDesc) {
    if (preg_match('/(\d+)\s*MP.*main/i', $cameraDesc, $matches)) {
        return $matches[1] . "MP (main)";
    }
    if (preg_match('/(\d+)\s*MP/i', $cameraDesc, $matches)) {
        return $matches[1] . "MP";
    }
    return $cameraDesc;
}

// Function to format specs
function formatSpecs($specs) {
    return nl2br(htmlspecialchars($specs));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Compare Phones - Mobil.io</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link rel="stylesheet" href="styles.css" />
  <style>
    .compare-title {
      text-align: center;
      color: #6a0dad;
      font-size: 2rem;
      font-weight: 700;
      margin: 2rem 0 2rem 0;
      font-family: 'Roboto', sans-serif;
    }
    .compare-container {
      display: flex;
      justify-content: center;
      gap: 2rem;
      margin-bottom: 2rem;
    }
    .compare-card {
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 2px 12px rgba(106,13,173,0.07);
      padding: 2rem 1.5rem;
      width: 400px;
      max-width: 90vw;
    }
    .form-label {
      font-weight: 600;
      margin-bottom: 0.3rem;
    }
    .form-select, .form-control {
      border-radius: 0.5rem;
      margin-bottom: 1rem;
    }
    @media (max-width: 900px) {
      .compare-container {
        flex-direction: column;
        align-items: center;
      }
    }
    .input-group.search-bar {
      align-items: center;
    }
    .input-group-text {
      background: #f5f5f5;
      border-right: none;
      font-weight: 500;
      color: #6a0dad;
      display: flex;
      align-items: center;
      border-top-left-radius: 0.25rem;
      border-bottom-left-radius: 0.25rem;
      border: 1px solid #dee2e6;
      border-right: none;
    }
    .search-bar .form-control {
      border-radius: 0.25rem;
      font-weight: 500;
      background-color: #f5f5f5;
      color: #333;
      border-left: none;
      border: 1px solid #dee2e6;
      border-top-left-radius: 0;
      border-bottom-left-radius: 0;
    }
    .phone-details {
      margin-top: 2rem;
      display: none;
    }
    .phone-details.active {
      display: block;
    }
    .phone-image {
      width: 100%;
      height: auto;
      border-radius: 12px;
      margin-bottom: 1rem;
    }
    .specs-table {
      width: 100%;
      margin-top: 1rem;
    }
    .specs-table td {
      padding: 0.5rem;
      border-bottom: 1px solid #dee2e6;
    }
    .specs-table td:first-child {
      font-weight: 600;
      width: 40%;
    }
  </style>
</head>
<body>
  <?php include 'includes/header.php'; ?>

  <!-- Compare Phones Section -->
  <div class="compare-title">Compare Phones</div>
  <div class="compare-container">
    <!-- Phone 1 -->
    <div class="compare-card">
      <label class="form-label" for="phone1-select">Select Phone</label>
      <select class="form-select" id="phone1-select">
        <option value="">Select a phone...</option>
        <?php foreach ($phones as $phone): ?>
        <option value="<?php echo $phone['id']; ?>">
          <?php echo htmlspecialchars($phone['brand_name'] . ' ' . $phone['name']); ?>
        </option>
        <?php endforeach; ?>
      </select>
      <div class="phone-details" id="phone1-details"></div>
    </div>
    <!-- Phone 2 -->
    <div class="compare-card">
      <label class="form-label" for="phone2-select">Select Phone</label>
      <select class="form-select" id="phone2-select">
        <option value="">Select a phone...</option>
        <?php foreach ($phones as $phone): ?>
        <option value="<?php echo $phone['id']; ?>">
          <?php echo htmlspecialchars($phone['brand_name'] . ' ' . $phone['name']); ?>
        </option>
        <?php endforeach; ?>
      </select>
      <div class="phone-details" id="phone2-details"></div>
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

    // Phone selection handling
    async function loadPhoneDetails(phoneId, targetElement) {
      if (!phoneId) {
        targetElement.innerHTML = '';
        targetElement.classList.remove('active');
        return;
      }

      try {
        const response = await fetch(`get_phone_details.php?id=${phoneId}`);
        const phone = await response.json();

        const html = `
          <img src="images/phones/${phone.image || 'phone-placeholder.jpg'}" 
               class="phone-image" 
               alt="${phone.brand_name} ${phone.name}"
               onerror="this.src='images/phone-placeholder.jpg'">
          <table class="specs-table">
            <tr>
              <td>Brand</td>
              <td>${phone.brand_name}</td>
            </tr>
            <tr>
              <td>Model</td>
              <td>${phone.name}</td>
            </tr>
            <tr>
              <td>OS</td>
              <td>${phone.os}</td>
            </tr>
            <tr>
              <td>Screen Size</td>
              <td>${phone.screen_size}"</td>
            </tr>
            <tr>
              <td>RAM</td>
              <td>${phone.ram}</td>
            </tr>
            <tr>
              <td>Storage</td>
              <td>${phone.storage}</td>
            </tr>
            <tr>
              <td>Battery</td>
              <td>${phone.battery}</td>
            </tr>
            <tr>
              <td>Main Camera</td>
              <td>${phone.rear_cameras}</td>
            </tr>
            <tr>
              <td>Front Camera</td>
              <td>${phone.front_camera}</td>
            </tr>
            <tr>
              <td>Price</td>
              <td>€${phone.price}</td>
            </tr>
          </table>
        `;

        targetElement.innerHTML = html;
        targetElement.classList.add('active');
      } catch (error) {
        console.error('Error loading phone details:', error);
        targetElement.innerHTML = '<p class="text-danger">Error loading phone details</p>';
      }
    }

    // Add event listeners to selects
    document.getElementById('phone1-select').addEventListener('change', function() {
      loadPhoneDetails(this.value, document.getElementById('phone1-details'));
    });

    document.getElementById('phone2-select').addEventListener('change', function() {
      loadPhoneDetails(this.value, document.getElementById('phone2-details'));
    });
  </script>
</body>
</html>
