<?php 
require_once 'includes/header.php'; 

// --- 1. KATEGORİLERİ ÇEK (Üst menü için) ---
$stmtCat = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
$allCategories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

// --- 2. FİLTRELEME MANTIĞI ---
$where = "WHERE stock > 0";
$params = [];
$pageTitle = "Tüm Tohumlar 🌱"; 
$metaDesc = "En doğal, atalık ve verimli tohum çeşitleri. Sebze, meyve ve çiçek tohumlarında uygun fiyat ve hızlı kargo avantajı."; // Varsayılan Meta Description

// A) Arama Yapıldıysa
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = htmlspecialchars($_GET['search']);
    $where .= " AND name LIKE ?";
    $params[] = "%" . $searchTerm . "%";
    $pageTitle = '"' . $searchTerm . '" Arama Sonuçları';
    $metaDesc = $searchTerm . " için arama sonuçları. En kaliteli " . $searchTerm . " tohumlarını inceleyin.";
}

// B) Kategori Seçildiyse
if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $catId = $_GET['category_id'];
    $where .= " AND category_id = ?";
    $params[] = $catId;
    
    foreach($allCategories as $cat) {
        if($cat['id'] == $catId) {
            $pageTitle = $cat['name'] . " Çeşitleri 🌱";
            $metaDesc = "En taze " . $cat['name'] . " tohumları. Atalık ve yerli üretim " . $cat['name'] . " çeşitlerini uygun fiyata satın alın.";
            break;
        }
    }
}

// SEO İçin Header'a veri gönder (header.php'de bu değişkenleri kullandığını varsayıyorum)
$seoTitle = $pageTitle;
$seoDesc = $metaDesc;

// --- 3. ÜRÜNLERİ ÇEK ---
$sql = "SELECT * FROM products $where ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
            <header>
                <h1 class="text-3xl font-bold text-gray-800"><?php echo $pageTitle; ?></h1>
                <p class="text-gray-600 mt-2">En doğal, en verimli tohum çeşitlerimizle bahçenizi yeşertin.</p>
            </header>
            
            <nav class="flex flex-wrap gap-3" aria-label="Kategori Menüsü">
                <a href="products.php" class="px-3 py-1 rounded-full text-sm font-bold <?php echo !isset($_GET['category_id']) ? 'bg-nature-green text-white' : 'bg-white text-gray-600 hover:text-nature-green'; ?> transition">Tümü</a>
                
                <?php foreach($allCategories as $cat): ?>
                    <a href="products.php?category_id=<?php echo $cat['id']; ?>" class="px-3 py-1 rounded-full text-sm font-bold <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $cat['id']) ? 'bg-nature-green text-white' : 'bg-white text-gray-600 hover:text-nature-green'; ?> transition">
                        <?php echo $cat['name']; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <?php if (count($products) > 0): ?>
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php foreach ($products as $product): ?>
            <article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition duration-300 group flex flex-col h-full border border-gray-100">
                
                <div class="h-48 overflow-hidden relative">
                    <a href="product-detail.php?id=<?php echo $product['id']; ?>" title="<?php echo $product['name']; ?> detaylarını incele">
                        <?php if($product['image']): ?>
                            <img src="uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?> Tohumu - Atalık Yerli Üretim" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                        <?php else: ?>
                            <div class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-400">Görsel Yok</div>
                        <?php endif; ?>
                    </a>
                    
                    <form action="cart-add.php" method="POST" class="absolute bottom-4 right-4 z-10 transform translate-y-12 group-hover:translate-y-0 duration-300">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="bg-white p-3 rounded-full text-nature-green shadow-lg hover:bg-nature-green hover:text-white transition flex items-center justify-center w-12 h-12" title="<?php echo $product['name']; ?> Sepete Ekle">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </button>
                    </form>
                </div>

                <div class="p-5 flex-1 flex flex-col">
                    <h2 class="text-lg font-bold text-gray-800 mb-1">
                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="hover:text-nature-green transition">
                            <?php echo $product['name']; ?>
                        </a>
                    </h2>

                    <?php if($product['stock'] <= 10 && $product['stock'] > 0): ?>
                        <div class="mb-2 text-red-600 font-bold text-[11px] uppercase tracking-tighter animate-pulse flex items-center gap-1">
                            <span>🔥</span> Tükenmek Üzere! (Son <?php echo $product['stock']; ?>)
                        </div>
                    <?php endif; ?>

                    <p class="text-gray-500 text-sm line-clamp-2 mb-4 flex-1">
                        <?php echo $product['description']; ?>
                    </p>
                    
                    <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                        <span class="text-xl font-bold text-nature-dark"><?php echo number_format($product['price'], 2); ?> ₺</span>
                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="text-sm font-medium text-gray-500 hover:text-nature-green" title="<?php echo $product['name']; ?> tohumu özelliklerini gör">İncele →</a>
                    </div>
                </div>

            </article>
            <?php endforeach; ?>
        </section>
        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-xl shadow-sm border border-gray-100">
                <span class="text-6xl block mb-4">🌱</span>
                <h2 class="text-2xl font-bold text-gray-800 mt-4">Bu Kategoride Ürün Bulunamadı</h2>
                <p class="text-gray-500 mt-2">Aradığınız kriterlere uygun tohum henüz eklenmemiş olabilir.</p>
                <a href="products.php" class="mt-6 inline-block bg-nature-green text-white px-6 py-3 rounded-lg font-bold hover:bg-nature-dark transition">Tüm Ürünleri Göster</a>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>