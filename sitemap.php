<?php
header("Content-Type: application/xml; charset=utf-8");
require_once 'config/db.php';

// Site URL'nizi buraya yazın
$site_url = "https://www.dogaltohumdunyasi.com"; 

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc><?php echo $site_url; ?>/index.php</loc><priority>1.0</priority></url>
    <url><loc><?php echo $site_url; ?>/products.php</loc><priority>0.9</priority></url>
    <url><loc><?php echo $site_url; ?>/about.php</loc><priority>0.7</priority></url>
    <url><loc><?php echo $site_url; ?>/contact.php</loc><priority>0.7</priority></url>

    <?php
    // Dinamik Ürün Linklerini Çek
    $stmt = $pdo->query("SELECT id FROM products WHERE stock > 0");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<url>';
        echo '<loc>' . $site_url . '/product-detail.php?id=' . $row['id'] . '</loc>';
        echo '<priority>0.8</priority>';
        echo '</url>';
    }
    ?>
</urlset>