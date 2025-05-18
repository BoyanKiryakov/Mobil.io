<?php
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';

$dsn = "mysql:host=$dbHost;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    $pdo->exec("
        CREATE DATABASE IF NOT EXISTS `Mobil_io`
        CHARACTER SET = utf8mb4
        COLLATE = utf8mb4_unicode_ci;
    ");
    $pdo->exec("USE `Mobil_io`;");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS brands (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          name VARCHAR(100) NOT NULL UNIQUE
        ) ENGINE=InnoDB;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS phones (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          brand_id INT UNSIGNED NOT NULL,
          name VARCHAR(150) NOT NULL,
          os VARCHAR(50) NOT NULL,
          price DECIMAL(10,2) NOT NULL,
          stock INT UNSIGNED NOT NULL DEFAULT 0,

          color VARCHAR(50),
          display VARCHAR(100),
          processor VARCHAR(100),
          graphics VARCHAR(100),
          ram VARCHAR(20),
          storage VARCHAR(50),
          rear_cameras VARCHAR(100),
          front_camera VARCHAR(50),
          battery VARCHAR(50),
          connectivity VARCHAR(100),
          security VARCHAR(50),
          audio VARCHAR(100),
          dimensions VARCHAR(50),
          weight VARCHAR(20),

          FOREIGN KEY (brand_id) REFERENCES brands(id)
            ON UPDATE CASCADE ON DELETE RESTRICT
        ) ENGINE=InnoDB;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS clients (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          first_name VARCHAR(50) NOT NULL,
          last_name VARCHAR(50) NOT NULL,
          email VARCHAR(150) NOT NULL UNIQUE,
          password_hash VARCHAR(255) NOT NULL,  -- store PASSWORD_HASH()
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS carts (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          client_id INT UNSIGNED NOT NULL,
          session_id VARCHAR(128) NOT NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          UNIQUE(client_id),
          FOREIGN KEY (client_id) REFERENCES clients(id)
            ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS cart_items (
          cart_id INT UNSIGNED NOT NULL,
          phone_id INT UNSIGNED NOT NULL,
          quantity INT UNSIGNED NOT NULL DEFAULT 1,
          added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (cart_id, phone_id),
          FOREIGN KEY (cart_id) REFERENCES carts(id)
            ON UPDATE CASCADE ON DELETE CASCADE,
          FOREIGN KEY (phone_id) REFERENCES phones(id)
            ON UPDATE CASCADE ON DELETE RESTRICT
        ) ENGINE=InnoDB;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          client_id INT UNSIGNED NOT NULL,
          order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          status ENUM('pending','paid','shipped','completed','cancelled') NOT NULL DEFAULT 'pending',
          total DECIMAL(10,2) NOT NULL,
          FOREIGN KEY (client_id) REFERENCES clients(id)
            ON UPDATE CASCADE ON DELETE RESTRICT
        ) ENGINE=InnoDB;
    ");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS order_items (
          order_id INT UNSIGNED NOT NULL,
          phone_id INT UNSIGNED NOT NULL,
          quantity INT UNSIGNED NOT NULL,
          unit_price DECIMAL(10,2) NOT NULL,
          PRIMARY KEY (order_id, phone_id),
          FOREIGN KEY (order_id) REFERENCES orders(id)
            ON UPDATE CASCADE ON DELETE CASCADE,
          FOREIGN KEY (phone_id) REFERENCES phones(id)
            ON UPDATE CASCADE ON DELETE RESTRICT
        ) ENGINE=InnoDB;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS reviews (
          id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
          client_id INT UNSIGNED NOT NULL,
          phone_id INT UNSIGNED NOT NULL,
          rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
          review_text TEXT,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          FOREIGN KEY (client_id) REFERENCES clients(id)
            ON UPDATE CASCADE ON DELETE CASCADE,
          FOREIGN KEY (phone_id) REFERENCES phones(id)
            ON UPDATE CASCADE ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            status ENUM('new', 'read', 'replied') DEFAULT 'new',
            INDEX (status),
            INDEX (created_at)
        )
    ");

    $pdo->exec(<<<'SQL'
    INSERT INTO brands (id, name) VALUES
      (1,'Motorola'),
      (2,'Xiaomi'),
      (3,'iPhone'),
      (4,'Samsung'),
      (5,'Huawei');
    SQL
    );

    $pdo->exec(<<<'SQL'
    INSERT INTO phones (
      brand_id, name, os, price, stock, color,
      display, processor, graphics, ram, storage,
      rear_cameras, front_camera, battery,
      connectivity, security, audio,
      dimensions, weight
    ) VALUES
      (1,'Moto G24 Power','Android 14',200.00,0,'blue',
       '6.56\" IPS LCD, 90Hz, 720 x 1612 px',
       'MediaTek Helio G85 (12 nm)',NULL,'8 GB','256 GB (expandable via microSD)',
       '50 MP (main, f/1.8, PDAF); 2 MP (macro, f/2.4)',
       '8 MP (Global) / 16 MP (India)','6000 mAh',
       NULL,NULL,NULL,
       '163.5 x 74.5 x 9 mm','197 g'),
    
      (1,'Edge 50 Pro','Android',460.00,0,'black',
       '6.7\" P-OLED, 144Hz, HDR10+, 2000 nits, 1220 x 2712 px',
       'Snapdragon 7 Gen 3',NULL,'12 GB','512 GB (UFS 2.2)',
       '50 MP (main, f/1.4, OIS); 10 MP (tele, 3× OIS); 13 MP (ultrawide)',
       '50 MP (autofocus)','4500 mAh',
       NULL,NULL,NULL,
       '161.2 x 72.4 x 8.2 mm','186 g'),
    
      (1,'G 5G','Android',299.99,0,'blue',
       '6.5\" LCD, 120Hz, 1600 x 720 px',
       'Snapdragon 480+ 5G',NULL,'4 GB','128 GB (expandable via microSD)',
       '48 MP (wide, f/1.7); 2 MP (macro, f/2.4)',
       '8 MP','5000 mAh',
       NULL,NULL,NULL,
       '163.94 x 74.98 x 8.39 mm','189 g'),
    
      (1,'Edge 20','Android',299.00,0,'black',
       '6.7\" OLED, 144Hz, HDR10+, 1080 x 2400 px',
       'Snapdragon 778G (6 nm)',NULL,'8 GB','256 GB (no microSD)',
       '108 MP (main, f/1.9); 8 MP (tele, 3× OIS); 16 MP (ultrawide)',
       '32 MP','4000 mAh',
       NULL,NULL,NULL,
       '163 x 76 x 7 mm','163 g'),
    
      (2,'15 Ultra','Android',918.72,0,'white',
       '6.73\" LTPO AMOLED, 120Hz, Dolby Vision, HDR10+, 1440 x 3200 px',
       'Snapdragon 8 Gen 3 (Elite)',NULL,'12–16 GB','512 GB (also 256 GB & 1 TB)',
       '50 MP (main, 1\" sensor, OIS); 50 MP (ultrawide); 50 MP (tele, 3× OIS); 200 MP (periscope OIS); TOF 3D',
       '32 MP','5410 mAh (Global) / 6000 mAh (China)',
       NULL,NULL,NULL,
       '161.3 x 75.3 x 9.4 mm','226–229 g'),
    
      (2,'14T Pro','Android',899.00,0,'silver',
       '6.67\" AMOLED, 144Hz, Dolby Vision, HDR10+, 1220 x 2712 px',
       'Dimensity 9300+ (4 nm)',NULL,'12 GB','512 GB (UFS 4.0)',
       '50 MP (main, f/1.6, OIS); 50 MP (tele, 2.6× OIS); 12 MP (ultrawide)',
       '32 MP','5000 mAh',
       NULL,NULL,NULL,
       '160.4 x 75.1 x 8.4 mm','209 g'),
    
      (2,'Civi 4 Pro','Android',350.00,0,'bronze',
       '6.55\" AMOLED, 120Hz, Dolby Vision, HDR10+, 1236 x 2750 px',
       'Snapdragon 8s Gen 3 (4 nm)',NULL,'12 GB','256 GB (UFS 4.0)',
       '50 MP (main, f/1.6, OIS); 50 MP (tele, 2× OIS); 12 MP (ultrawide)',
       '32+32 MP dual','4700 mAh',
       NULL,NULL,NULL,
       '157.2 x 72.8 x 7.5 mm','~179 g'),
    
      (2,'Redmi Note 13 Pro','Android',222.22,0,'blue',
       '6.67\" AMOLED, 120Hz, Dolby Vision, HDR10+, 1220 x 2712 px',
       'Snapdragon 7s Gen 2 (4 nm)',NULL,'8 GB','128 GB (UFS 2.2)',
       '200 MP (main, f/1.7, OIS); 8 MP (ultrawide); 2 MP (macro)',
       '16 MP','5100 mAh',
       NULL,NULL,NULL,
       '161.2 x 74.2 x 8 mm','187 g'),
    
      (3,'iPhone 16e','iOS',839.00,0,'black',
       '6.1\" Super Retina XDR OLED, HDR10, Dolby Vision, 1179 x 2556 px',
       'Apple A18 (3 nm)',NULL,'8 GB','256 GB',
       '48 MP (main, f/1.6, OIS); 12 MP (ultrawide)',
       '12 MP + SL 3D','3561 mAh',
       'eSIM + Nano SIM','Face ID','Stereo speakers, Dolby Vision',
       '147.6 x 71.6 x 7.8 mm','170 g'),
    
      (3,'iPhone 16 Pro','iOS',1199.00,0,'bronze',
       '6.3\" LTPO Super Retina XDR OLED, 120Hz, HDR10, Dolby Vision, 1206 x 2622 px',
       'Apple A18 Pro (3 nm)',NULL,'12 GB','512 GB',
       '48 MP (main, f/1.8, OIS); 48 MP (ultrawide); 12 MP (periscope, 5×); TOF LiDAR',
       '12 MP + SL 3D','3582 mAh',
       'eSIM + Nano SIM','Face ID','Stereo speakers, Dolby Vision',
       '149.6 x 71.5 x 8.3 mm','199 g'),
    
      (3,'iPhone 16','iOS',705.00,0,'pink',
       '6.1\" Super Retina XDR OLED, HDR10, Dolby Vision, 1179 x 2556 px',
       'Apple A18',NULL,'8 GB','128 GB',
       '48 MP (main, f/1.6, OIS); 12 MP (ultrawide)',
       '12 MP + SL 3D','3561 mAh',
       'eSIM + Nano SIM','Face ID','Stereo speakers, Dolby Vision',
       '147.6 x 71.6 x 7.8 mm','170 g'),
    
      (3,'iPhone 14','iOS',629.00,0,'blue',
       '6.1\" Super Retina XDR OLED, 1170 x 2532 px',
       'Apple A15 Bionic',NULL,'6 GB','128 GB',
       'Dual 12 MP (main + ultrawide)','12 MP','3279 mAh',
       'eSIM + Nano SIM','Face ID','Stereo speakers',
       '146.7 x 71.5 x 7.8 mm','172 g'),
    
      (3,'iPhone 14 Pro Max','iOS',973.00,0,'black',
       '6.7\" LTPO Super Retina XDR OLED, 120Hz, HDR10, Dolby Vision, 1290 x 2796 px',
       'Apple A16 Bionic (4 nm)',NULL,'6 GB','256 GB',
       '48 MP (main, f/1.8, OIS); 12 MP (tele, 3×); 12 MP (ultrawide); TOF LiDAR',
       '12 MP + SL 3D','4323 mAh',
       'eSIM + Nano SIM','Face ID','Stereo speakers, Dolby Vision',
       '160.7 x 77.6 x 7.9 mm','240 g'),
    
      (3,'iPhone 13 Pro','iOS',778.00,0,'green',
       '6.1\" Super Retina XDR OLED, 120Hz, HDR10, Dolby Vision, 1170 x 2532 px',
       'Apple A15 Bionic',NULL,'6 GB','256 GB',
       '12 MP (main, f/1.5, OIS); 12 MP (tele, 3× OIS); 12 MP (ultrawide); TOF LiDAR',
       '12 MP + SL 3D','3095 mAh',
       'eSIM + Nano SIM','Face ID','Stereo speakers',
       '146.7 x 71.5 x 7.7 mm','204 g'),
    
      (4,'Galaxy S24','Android',999.00,0,'yellow',
       '6.2\" Dynamic LTPO AMOLED 2X, 120Hz, HDR10+, 1080 x 2340 px',
       'Snapdragon 8 Gen 3 / Exynos 2400',NULL,'8 GB','128 GB (UFS 3.1)',
       '50 MP (main, f/1.8, OIS); 10 MP (tele, 3×); 12 MP (ultrawide)',
       '12 MP','4000 mAh',
       NULL,NULL,NULL,
       '147 x 70.6 x 7.6 mm','167 g'),
    
      (4,'Galaxy S24 Ultra','Android',1143.99,0,'purple',
       '6.8\" Dynamic LTPO AMOLED 2X, 120Hz, HDR10+, 1440 x 3120 px',
       'Snapdragon 8 Gen 3',NULL,'12 GB','1024 GB (UFS 4.0)',
       '200 MP (main, f/1.7, OIS); 50 MP (periscope, 5×); 10 MP (tele, 3×); 12 MP (ultrawide)',
       '12 MP','5000 mAh',
       NULL,NULL,NULL,
       '162.3 x 79 x 8.6 mm','232–233 g'),
    
      (4,'Galaxy Z Flip 6','Android',1466.99,0,'grey',
       '6.7\" / 3.4\" Foldable Dynamic LTPO AMOLED 2X, 120Hz, HDR10+, 1080 x 2640 px / 720 x 748 px',
       'Snapdragon 8 Gen 3',NULL,'12 GB','512 GB (UFS 4.0)',
       '50 MP (wide, f/1.8, OIS); 12 MP (ultrawide)',
       '10 MP','4000 mAh',
       NULL,NULL,NULL,
       '165.1 x 71.9 x 6.9 mm (unfolded) / 85.1 x 71.9 x 14.9 mm (folded)','187 g'),
    
      (4,'Galaxy A55 5G','Android',450.00,0,'pink',
       '6.6\" Super AMOLED, 120Hz, HDR10+, 1080 x 2340 px',
       'Exynos 1480 (4 nm)',NULL,'8 GB','256 GB (expandable)',
       '50 MP (main, f/1.8, OIS); 12 MP (ultrawide); 5 MP (macro)',
       '32 MP','5000 mAh',
       NULL,NULL,NULL,
       '161.1 x 77.4 x 8.2 mm','213 g'),
    
      (4,'Galaxy A54 5G','Android',419.00,0,'white',
       '6.4\" Super AMOLED, 120Hz, HDR10+, 1080 x 2340 px',
       'Exynos 1380 (5 nm)',NULL,'6–8 GB','128–256 GB (expandable)',
       '50 MP (main, f/1.8, OIS); 12 MP (ultrawide, f/2.2); 5 MP (macro)',
       '32 MP','5000 mAh',
       NULL,NULL,NULL,
       '158.2 x 76.7 x 8.2 mm','202 g'),
    
      (4,'Galaxy Z Fold5','Android',2050.00,0,'black',
       '7.6\" / 6.2\" Dynamic AMOLED 2X, 120Hz, 1812 x 2176 px / 904 x 2316 px',
       'Snapdragon 8 Gen 2 for Galaxy',NULL,'12 GB','512 GB (UFS 4.0)',
       '50 MP (main, f/1.8, OIS); 10 MP (tele, 3×); 12 MP (ultrawide)',
       '10 MP (cover) / 4 MP (under-display)','4400 mAh',
       NULL,NULL,NULL,
       '154.9 x 129.9 x 6.1 mm (unfolded) / 154.9 x 67.1 x 13.4 mm (folded)','253 g'),
    
      (5,'Nova 13 Pro','HarmonyOS',499.00,0,'black',
       '6.76\" LTPO OLED, 120Hz, HDR, 1224 x 2776 px',
       'Kirin/Snapdragon',NULL,'12 GB','512 GB',
       '50 MP (main, f/1.4–4.0, OIS); 12 MP (tele, 3×, OIS); 8 MP (ultrawide)',
       '60 MP / 8 MP','5000 mAh',
       NULL,NULL,NULL,
       '163.4 x 74.9 x 7.8 mm','209 g'),
    
      (5,'Pura X','HarmonyOS',927.00,0,'black',
       '6.3\" / 3.5\" Foldable LTPO2 OLED, 120Hz, HDR Vivid, 1320 x 2120 px / 980 x 980 px',
       'Kirin 9020',NULL,'16 GB','512 GB',
       '50 MP (main, f/1.6, OIS); 8 MP (tele, 3.5×, OIS); 40 MP (ultrawide)',
       '10.7 MP','4720 mAh',
       NULL,NULL,NULL,
       '143.2 x 91.7 x 7.2 mm / 91.7 x 74.3 x 15.1 mm','193.7–195.9 g'),
    
      (5,'Pura 70 Ultra','HarmonyOS',1075.00,0,'pink',
       '6.8\" LTPO OLED, 120Hz, HDR, 1260 x 2844 px',
       'Kirin 9010 (7 nm)',NULL,'12 GB','512 GB',
       '50 MP (main, f/1.6–4.0, 1\" sensor, OIS); 50 MP (tele, 3.5×, OIS); 40 MP (ultrawide)',
       '13 MP','5200 mAh',
       NULL,NULL,NULL,
       '162.6 x 75.1 x 8.4 mm','226 g'),
    
      (5,'Mate 60 Pro Plus','HarmonyOS',1179.00,0,'white',
       '6.82\" LTPO OLED, 120Hz, HDR, 1260 x 2720 px',
       'Kirin 9000S (7 nm)',NULL,'16 GB','1024 GB (UFS 3.1)',
       '48 MP (main, f/1.4–4.0, OIS); 48 MP (periscope, 3.5×, OIS); 40 MP (ultrawide)',
       '13 MP + TOF 3D','5000 mAh',
       NULL,NULL,NULL,
       '163.7 x 79 x 8.1 mm','225 g');
    SQL
    );
    
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
