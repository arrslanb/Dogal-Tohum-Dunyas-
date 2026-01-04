<?php
require_once 'includes/header.php';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcının favori ürünlerini çek
$stmt = $pdo->prepare("
    SELECT products.*, wishlist.created_at as added_date 
    FROM wishlist 
    JOIN products ON wishlist.product_id = products.id 
    WHERE wishlist.user_id = ? 
    ORDER BY wishlist.id DESC
");
$stmt->execute([$user_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <h1 class="text-3xl font-bold text-gray-800 mb-8 flex items-center gap-3">
            <span class="text-red-500">❤️</span> Favori Listem
        </h1>

        <?php if(count($products) > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach($products as $product): ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition duration-300 group flex flex-col h-full relative">
                    
                    <a href="wishlist-action.php?product_id=<?php echo $product['id']; ?>&action=remove" class="absolute top-3 right-3 bg-white p-2 rounded-full text-gray-400 hover:text-red-500 shadow-md z-10 transition" title="Listeden Kaldır">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </a>

                    <div class="h-48 overflow-hidden relative">
                        <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                            <?php if($product['image']): ?>
                                <img src="uploads/<?php echo $product['image']; ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                            <?php else: ?>
                                <div class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-400">Görsel Yok</div>
                            <?php endif; ?>
                        </a>
                    </div>

                    <div class="p-5 flex-1 flex flex-col">
                        <h3 class="text-lg font-bold text-gray-800 mb-1">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="hover:text-nature-green transition">
                                <?php echo $product['name']; ?>
                            </a>
                        </h3>
                        
                        <div class="mt-auto pt-4 flex items-center justify-between">
                            <span class="text-xl font-bold text-nature-dark"><?php echo number_format($product['price'], 2); ?> ₺</span>
                            
                            <?php if($product['stock'] > 0): ?>
                                <form action="cart-add.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="bg-nature-green text-white px-3 py-2 rounded-lg text-sm font-bold hover:bg-nature-dark transition flex items-center gap-1">
                                        <span>🛒</span> Sepete Ekle
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-xs text-red-500 font-bold bg-red-50 px-2 py-1 rounded">Stokta Yok</span>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20 bg-white rounded-xl shadow-sm">
                <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl">
                    💔
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Listeniz henüz boş.</h2>
                <p class="text-gray-500 mb-8">Beğendiğiniz ürünleri kalp ikonuna tıklayarak buraya ekleyebilirsiniz.</p>
                <a href="products.php" class="bg-nature-green text-white px-8 py-3 rounded-lg font-bold hover:bg-nature-dark transition">
                    Tohumları Keşfet
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>