<?php
require_once 'includes/db.php';
require_once 'includes/session.php';

// Initialize filter variables
$filters = [];
$params = [];
$where_clauses = [];

// Handle search query
if (!empty($_GET['search'])) {
    $where_clauses[] = "(phones.name LIKE ? OR brands.name LIKE ?)";
    $search_term = "%" . $_GET['search'] . "%";
    $params[] = $search_term;
    $params[] = $search_term;
}

// Handle brand filter
if (!empty($_GET['brand'])) {
    $placeholders = str_repeat('?,', count($_GET['brand']) - 1) . '?';
    $where_clauses[] = "brands.name IN ($placeholders)";
    $params = array_merge($params, $_GET['brand']);
}

// Handle OS filter
if (!empty($_GET['os'])) {
    $os_conditions = [];
    foreach ($_GET['os'] as $os) {
        $os_conditions[] = "phones.os LIKE ?";
        $params[] = "%$os%";
    }
    $where_clauses[] = "(" . implode(" OR ", $os_conditions) . ")";
}

// Handle price range
if (!empty($_GET['min_price'])) {
    $where_clauses[] = "phones.price >= ?";
    $params[] = $_GET['min_price'];
}
if (!empty($_GET['max_price'])) {
    $where_clauses[] = "phones.price <= ?";
    $params[] = $_GET['max_price'];
}

// Handle RAM filter
if (!empty($_GET['ram'])) {
    $ram_conditions = [];
    foreach ($_GET['ram'] as $ram) {
        $ram_conditions[] = "phones.ram LIKE ?";
        $params[] = "%$ram%";
    }
    $where_clauses[] = "(" . implode(" OR ", $ram_conditions) . ")";
}

// Handle storage filter
if (!empty($_GET['storage'])) {
    $storage_conditions = [];
    foreach ($_GET['storage'] as $storage) {
        $storage_conditions[] = "phones.storage LIKE ?";
        $params[] = "%$storage%";
    }
    $where_clauses[] = "(" . implode(" OR ", $storage_conditions) . ")";
}

// Handle color filter
if (!empty($_GET['color'])) {
    $color_conditions = [];
    foreach ($_GET['color'] as $color) {
        $color_conditions[] = "phones.color LIKE ?";
        $params[] = "%$color%";
    }
    $where_clauses[] = "(" . implode(" OR ", $color_conditions) . ")";
}

// Build the SQL query
$sql = "
    SELECT phones.*, brands.name as brand_name 
    FROM phones 
    JOIN brands ON phones.brand_id = brands.id
";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

// Handle sorting
$sort = $_GET['sort'] ?? 'price-low-high';
switch ($sort) {
    case 'price-high-low':
        $sql .= " ORDER BY phones.price DESC";
        break;
    case 'price-low-high':
    default:
        $sql .= " ORDER BY phones.price ASC";
        break;
}

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $phones = $stmt->fetchAll();
} catch (PDOException $e) {
    $phones = [];
    // Log error in production
}

// Get all brands for the filter
try {
    $brands = $pdo->query("SELECT * FROM brands ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    $brands = [];
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

// Function to extract RAM value
function extractRAM($ramString) {
    if (preg_match('/(\d+)\s*GB/i', $ramString, $matches)) {
        return $matches[1] . " GB";
    }
    return $ramString;
}

// Function to extract storage value
function extractStorage($storageString) {
    if (preg_match('/(\d+)\s*GB/i', $storageString, $matches)) {
        return $matches[1] . " GB";
    }
    return $storageString;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mobil.io Catalogue</title>
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
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <?php include 'includes/header.php'; ?>
  <section class="container py-5">
    <!-- Sort dropdown moved outside the main row -->
    <div class="d-flex justify-content-end mb-4">
      <div>
        <label for="sort-by" class="form-label me-2 mb-0">Sort by:</label>
        <select id="sort-by" name="sort" class="form-select d-inline-block w-auto" onchange="document.getElementById('filterForm').submit();">
          <option value="price-low-high" <?php echo ($sort === 'price-low-high') ? 'selected' : ''; ?>>Price: Low to High</option>
          <option value="price-high-low" <?php echo ($sort === 'price-high-low') ? 'selected' : ''; ?>>Price: High to Low</option>
        </select>
      </div>
    </div>

    <div class="row">
      <!-- Filter Sidebar -->
      <aside class="col-md-3">
        <div class="filter-sidebar p-4">
          <h4 class="filter-title-main mb-4">Filter Specifications</h4>
          <form id="filterForm" method="GET" action="catalogue.php">
            <!-- Add a hidden input for the sort value -->
            <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
            
            <div class="filter-section mb-3">
              <div class="filter-label">Operating System</div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="os-android" name="os[]" value="Android" <?php echo (isset($_GET['os']) && in_array('Android', (array)$_GET['os'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="os-android">Android</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="os-ios" name="os[]" value="iOS" <?php echo (isset($_GET['os']) && in_array('iOS', (array)$_GET['os'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="os-ios">iOS</label>
              </div>
            </div>

            <div class="filter-section mb-3">
              <div class="filter-label">Brand</div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="brand-apple" name="brand[]" value="Apple" <?php echo (isset($_GET['brand']) && in_array('Apple', (array)$_GET['brand'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="brand-apple">Apple</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="brand-samsung" name="brand[]" value="Samsung" <?php echo (isset($_GET['brand']) && in_array('Samsung', (array)$_GET['brand'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="brand-samsung">Samsung</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="brand-motorola" name="brand[]" value="Motorola" <?php echo (isset($_GET['brand']) && in_array('Motorola', (array)$_GET['brand'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="brand-motorola">Motorola</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="brand-huawei" name="brand[]" value="Huawei" <?php echo (isset($_GET['brand']) && in_array('Huawei', (array)$_GET['brand'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="brand-huawei">Huawei</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="brand-xiaomi" name="brand[]" value="Xiaomi" <?php echo (isset($_GET['brand']) && in_array('Xiaomi', (array)$_GET['brand'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="brand-xiaomi">Xiaomi</label>
              </div>
            </div>

            <div class="filter-section mb-3">
              <div class="filter-label">Camera</div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="camera-200" name="camera" value="200 MP">
                <label class="form-check-label" for="camera-200">200 MP</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="camera-108" name="camera" value="108 MP">
                <label class="form-check-label" for="camera-108">108 MP</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="camera-64" name="camera" value="64 MP">
                <label class="form-check-label" for="camera-64">64 MP</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="camera-50" name="camera" value="50 MP">
                <label class="form-check-label" for="camera-50">50 MP</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="camera-48" name="camera" value="48 MP">
                <label class="form-check-label" for="camera-48">48 MP</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="camera-32" name="camera" value="32 MP">
                <label class="form-check-label" for="camera-32">32 MP</label>
              </div>
            </div>

            <div class="filter-section mb-3">
              <div class="filter-label">RAM Memory</div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="ram-4" name="ram[]" value="4 GB" <?php echo (isset($_GET['ram']) && in_array('4 GB', (array)$_GET['ram'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="ram-4">4 GB</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="ram-6" name="ram[]" value="6 GB" <?php echo (isset($_GET['ram']) && in_array('6 GB', (array)$_GET['ram'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="ram-6">6 GB</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="ram-8" name="ram[]" value="8 GB" <?php echo (isset($_GET['ram']) && in_array('8 GB', (array)$_GET['ram'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="ram-8">8 GB</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="ram-12" name="ram[]" value="12 GB" <?php echo (isset($_GET['ram']) && in_array('12 GB', (array)$_GET['ram'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="ram-12">12 GB</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="ram-16" name="ram[]" value="16 GB" <?php echo (isset($_GET['ram']) && in_array('16 GB', (array)$_GET['ram'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="ram-16">16 GB</label>
              </div>
            </div>

            <!-- Refresh Rate Filter -->
            <div class="filter-section mb-3">
              <div class="filter-label">Refresh Rate</div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="refresh-60" name="refresh" value="60 Hz">
                <label class="form-check-label" for="refresh-60">60 Hz</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="refresh-90" name="refresh" value="90 Hz">
                <label class="form-check-label" for="refresh-90">90 Hz</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="refresh-120" name="refresh" value="120 Hz">
                <label class="form-check-label" for="refresh-120">120 Hz</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="refresh-144" name="refresh" value="144 Hz">
                <label class="form-check-label" for="refresh-144">144 Hz</label>
              </div>
            </div>

            <!-- Battery Capacity Filter -->
            <div class="filter-section mb-3">
              <div class="filter-label">Battery Capacity</div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="battery-2500" name="battery" value="2500 mAh">
                <label class="form-check-label" for="battery-2500">2500 mAh</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="battery-2501-3000" name="battery" value="2501 mAh - 3000 mAh">
                <label class="form-check-label" for="battery-2501-3000">2501 mAh - 3000 mAh</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="battery-4000" name="battery" value="4000 mAh and higher">
                <label class="form-check-label" for="battery-4000">4000 mAh and higher</label>
              </div>
            </div>

            <!-- Price Range Filter -->
            <div class="filter-section mb-3">
              <div class="filter-label">Price Range</div>
              <div class="price-range-container position-relative mb-2">
                <div class="slider-track"></div>
                <div class="slider-range"></div>
                <input type="range" 
                       class="form-range price-range min-price" 
                       min="0" 
                       max="3000" 
                       step="50" 
                       id="minPriceRange" 
                       value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : '0'; ?>" />
                <input type="range" 
                       class="form-range price-range max-price" 
                       min="0" 
                       max="3000" 
                       step="50" 
                       id="maxPriceRange" 
                       value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '3000'; ?>" />
              </div>
              <div class="d-flex justify-content-between mb-2">
                <span id="priceMinLabel">€<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : '0'; ?></span>
                <span id="priceMaxLabel">€<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : '3000'; ?></span>
              </div>
              <div class="d-flex gap-2">
                <input type="number" 
                       class="form-control form-control-sm" 
                       placeholder="Min Price" 
                       id="minPrice" 
                       name="min_price" 
                       value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>" />
                <input type="number" 
                       class="form-control form-control-sm" 
                       placeholder="Max Price" 
                       id="maxPrice" 
                       name="max_price" 
                       value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>" />
              </div>
            </div>

            <!-- Add custom CSS for dual range slider -->
            <style>
              .price-range-container {
                height: 40px;
                padding-top: 20px;
                position: relative;
              }

              .slider-track {
                position: absolute;
                top: 50%;
                left: 0;
                right: 0;
                height: 4px;
                background: #DEE2E6;
                border-radius: 2px;
                transform: translateY(-50%);
              }

              .slider-range {
                position: absolute;
                top: 50%;
                height: 4px;
                background: #5729F3;
                border-radius: 2px;
                transform: translateY(-50%);
                pointer-events: none;
              }

              .price-range {
                position: absolute;
                pointer-events: none;
                -webkit-appearance: none;
                appearance: none;
                width: 100%;
                height: 100%;
                outline: none;
                top: 0;
                background: none;
              }

              .price-range::-webkit-slider-thumb {
                pointer-events: auto;
                -webkit-appearance: none;
                appearance: none;
                width: 16px;
                height: 16px;
                background: #7749F8;
                border-radius: 50%;
                cursor: pointer;
                border: none;
              }

              .price-range::-moz-range-thumb {
                pointer-events: auto;
                width: 16px;
                height: 16px;
                background: #7749F8;
                border-radius: 50%;
                cursor: pointer;
                border: none;
              }

              /* Hide default track */
              .price-range::-webkit-slider-runnable-track {
                -webkit-appearance: none;
                appearance: none;
                background: transparent;
                border: none;
              }

              .price-range::-moz-range-track {
                appearance: none;
                background: transparent;
                border: none;
              }

              /* Ensure both range inputs are clickable */
              .price-range.min-price {
                z-index: 2;
              }

              .price-range.max-price {
                z-index: 2;
              }
            </style>

            <!-- Memory Filter -->
            <div class="filter-section mb-3">
              <div class="filter-label">Memory</div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="memory-1024" name="storage[]" value="1024 GB" <?php echo (isset($_GET['storage']) && in_array('1024 GB', (array)$_GET['storage'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="memory-1024">1024 GB</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="memory-512" name="storage[]" value="512 GB" <?php echo (isset($_GET['storage']) && in_array('512 GB', (array)$_GET['storage'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="memory-512">512 GB</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="memory-256" name="storage[]" value="256 GB" <?php echo (isset($_GET['storage']) && in_array('256 GB', (array)$_GET['storage'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="memory-256">256 GB</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="memory-128" name="storage[]" value="128 GB" <?php echo (isset($_GET['storage']) && in_array('128 GB', (array)$_GET['storage'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="memory-128">128 GB</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="memory-64" name="storage[]" value="64 GB" <?php echo (isset($_GET['storage']) && in_array('64 GB', (array)$_GET['storage'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="memory-64">64 GB</label>
              </div>
            </div>

            <!-- Screen Size Filter -->
            <div class="filter-section mb-3">
              <div class="filter-label">Screen Size</div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="screen-6-5" name="screen" value="6.01" - 6.5">
                <label class="form-check-label" for="screen-6-5">6.01" - 6.5"</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="screen-6-7" name="screen" value="6.51" - 7">
                <label class="form-check-label" for="screen-6-7">6.51" - 7"</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="screen-7plus" name="screen" value="7.01" and higher">
                <label class="form-check-label" for="screen-7plus">7.01" and higher</label>
              </div>
            </div>

            <!-- Color Filter -->
            <div class="filter-section mb-3">
              <div class="filter-label">Color</div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="color-black" name="color[]" value="Black">
                <label class="form-check-label" for="color-black">Black</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="color-white" name="color[]" value="White">
                <label class="form-check-label" for="color-white">White</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="color-silver" name="color[]" value="Silver">
                <label class="form-check-label" for="color-silver">Silver</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="color-bronze" name="color[]" value="Bronze">
                <label class="form-check-label" for="color-bronze">Bronze</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="color-gold" name="color[]" value="Gold">
                <label class="form-check-label" for="color-gold">Gold</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="color-pink" name="color[]" value="Pink">
                <label class="form-check-label" for="color-pink">Pink</label>
              </div>
              <div class="form-check">
                <input class="form-check-input purple-check" type="checkbox" id="color-blue" name="color[]" value="Blue">
                <label class="form-check-label" for="color-blue">Blue</label>
              </div>
            </div>
            <button class="btn btn-primary w-100 mt-3 apply-filters-btn" id="applyFilters" name="applyFilters">Apply Filters</button>
          </form>
        </div>
      </aside>
      <!-- Phone Cards Section - moved to be next to filters -->
      <div class="col-md-9">
        <div class="row g-4" id="phones-container">
          <?php foreach ($phones as $phone): ?>
          <div class="col-md-4">
            <div class="custom-phone-card">
              <img src="images/phones/<?php echo strtolower(str_replace(' ', '', $phone['name'])); ?>.jpg" class="phone-img" alt="<?php echo htmlspecialchars($phone['brand_name'] . ' ' . $phone['name']); ?>" onerror="this.src='images/phone-placeholder.jpg'"/>
              <div class="phone-info">
                <h5 class="phone-title"><?php echo htmlspecialchars($phone['brand_name'] . ' ' . $phone['name']); ?></h5>
                <div class="phone-price">€<?php echo number_format($phone['price'], 2); ?></div>
                <div class="phone-spec">RAM: <?php echo htmlspecialchars(extractRAM($phone['ram'])); ?></div>
                <div class="phone-spec">Internal Storage: <?php echo htmlspecialchars(extractStorage($phone['storage'])); ?></div>
                <div class="phone-spec">Camera: <?php echo htmlspecialchars(extractMainCameraMp($phone['rear_cameras'])); ?></div>
                <div class="d-flex justify-content-center gap-2 mt-3">
                  <button class="btn btn-add-cart" onclick="addToCart(<?php echo $phone['id']; ?>)">Add to Cart</button>
                  <button class="btn btn-view-product" onclick="viewProduct(<?php echo $phone['id']; ?>)">View Product</button>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($phones)): ?>
          <div class="col-12 text-center">
            <p>No phones found matching your criteria.</p>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
  <!-- Bootstrap JS bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Handle filter form submission
    const filterForm = document.getElementById('filterForm');
    filterForm.addEventListener('submit', (e) => {
        // Don't prevent default - let the form submit naturally
    });

    // Handle sort dropdown changes
    const sortDropdown = document.getElementById('sort-by');
    sortDropdown.addEventListener('change', () => {
        // Add the sort value to the form and submit
        filterForm.submit();
    });

    // Add to cart functionality
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

    // View product functionality
    function viewProduct(phoneId) {
      window.location.href = `product.php?id=${phoneId}`;
    }

    // Dual range slider functionality
    const minPriceRange = document.getElementById('minPriceRange');
    const maxPriceRange = document.getElementById('maxPriceRange');
    const minPriceInput = document.getElementById('minPrice');
    const maxPriceInput = document.getElementById('maxPrice');
    const priceMinLabel = document.getElementById('priceMinLabel');
    const priceMaxLabel = document.getElementById('priceMaxLabel');

    // Function to update price display and range
    function updatePriceDisplay() {
      const minValue = parseInt(minPriceRange.value);
      const maxValue = parseInt(maxPriceRange.value);
      const range = document.querySelector('.slider-range');
      
      // Calculate the position and width of the colored range
      const totalRange = maxPriceRange.max - maxPriceRange.min;
      const leftPosition = ((minValue - maxPriceRange.min) / totalRange) * 100;
      const rightPosition = ((maxValue - maxPriceRange.min) / totalRange) * 100;
      
      // Update the range element style
      range.style.left = leftPosition + '%';
      range.style.width = (rightPosition - leftPosition) + '%';

      // Ensure min doesn't exceed max
      if (minValue > maxValue) {
        if (this === minPriceRange) {
          maxPriceRange.value = minValue;
          maxPriceInput.value = minValue;
          priceMaxLabel.textContent = `€${minValue}`;
        } else {
          minPriceRange.value = maxValue;
          minPriceInput.value = maxValue;
          priceMinLabel.textContent = `€${maxValue}`;
        }
      }

      // Update inputs and labels
      minPriceInput.value = minPriceRange.value;
      maxPriceInput.value = maxPriceRange.value;
      priceMinLabel.textContent = `€${minPriceRange.value}`;
      priceMaxLabel.textContent = `€${maxPriceRange.value}`;
    }

    // Add event listeners for range inputs
    minPriceRange.addEventListener('input', updatePriceDisplay);
    maxPriceRange.addEventListener('input', updatePriceDisplay);

    // Update range sliders when number inputs change
    minPriceInput.addEventListener('input', function() {
      const value = this.value;
      if (value && !isNaN(value)) {
        minPriceRange.value = value;
        updatePriceDisplay.call(minPriceRange);
      }
    });

    maxPriceInput.addEventListener('input', function() {
      const value = this.value;
      if (value && !isNaN(value)) {
        maxPriceRange.value = value;
        updatePriceDisplay.call(maxPriceRange);
      }
    });

    // Initialize price display and range
    updatePriceDisplay();
  </script>
</body>
</html> 