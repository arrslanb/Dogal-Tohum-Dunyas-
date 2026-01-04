<?php
require_once 'config/db.php';

// Eğer arama parametresi yoksa dur
if (!isset($_POST['query'])) { exit; }

$search = trim($_POST['query']);
$output = '';

if (!empty($search)) {
    // Ürünleri ara (Maksimum 5 tane getir ki liste uzamasın)
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? AND stock > 0 LIMIT 5");
    $stmt->execute(["%$search%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) > 0) {
        $output .= '<div class="bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden mt-2">';
        
        foreach ($results as $row) {
            $output .= '
            <a href="product-detail.php?id='.$row['id'].'" class="flex items-center gap-4 p-3 hover:bg-green-50 transition border-b last:border-0 cursor-pointer group">
                <div class="w-12 h-12 rounded-lg overflow-hidden border flex-shrink-0">
                    <img src="uploads/'.$row['image'].'" class="w-full h-full object-cover group-hover:scale-110 transition">
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-gray-800 group-hover:text-nature-green">'.$row['name'].'</h4>
                    <span class="text-xs text-nature-dark font-bold">'.$row['price'].' ₺</span>
                </div>
                <div class="text-gray-400 text-xs group-hover:text-nature-green">incele →</div>
            </a>
            ';
        }
        // Tümünü gör butonu
        $output .= '
        <a href="products.php?search='.$search.'" class="block text-center text-xs font-bold text-nature-green bg-gray-50 p-3 hover:bg-green-100 transition">
            Tüm Sonuçları Gör
        </a>
        </div>';
    } else {
        $output .= '
        <div class="bg-white rounded-xl shadow-xl p-4 mt-2 text-center text-gray-500 text-sm border">
            😞 Ürün bulunamadı.
        </div>';
    }
}

echo $output;
?>