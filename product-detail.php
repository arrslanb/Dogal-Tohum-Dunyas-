<?php 
require_once 'includes/header.php'; 

if (!isset($_GET['id'])) {
    echo "<script>window.location.href='products.php';</script>";
    exit;
}

$product_id = intval($_GET['id']);
$message = "";
$msgType = "";

// --- YORUM KAYDETME İŞLEMİ ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        $message = "Yorum yapmak için giriş yapmalısınız.";
        $msgType = "error";
    } else {
        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name']; 
        $comment = htmlspecialchars(trim($_POST['comment']));
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;

        if (!empty($comment) && $rating > 0 && $rating <= 5) {
            try {
                $stmtComment = $pdo->prepare("INSERT INTO comments (product_id, user_name, comment, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmtComment->execute([$product_id, $user_name, $comment, $rating]);
                $message = "Yorumunuz başarıyla yayınlandı! Teşekkürler.";
                $msgType = "success";
            } catch (PDOException $e) {
                $message = "Bir hata oluştu: " . $e->getMessage();
                $msgType = "error";
            }
        } else {
            $message = "Lütfen puan verin ve yorum yazın.";
            $msgType = "error";
        }
    }
}

// --- ÜRÜN BİLGİLERİNİ ÇEK ---
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) { 
    echo "<div class='text-center py-20 text-2xl font-bold text-gray-600'>Ürün bulunamadı. 🤷‍♂️</div>"; 
    require_once 'includes/footer.php'; 
    exit; 
}

// SEO Değişkenleri (Header.php'de kullanıyorsan çok işe yarar)
$pageTitle = $product['name'] . " Tohumu Satın Al";
$metaDesc = substr(strip_tags($product['description']), 0, 160);

// --- YORUMLARI ÇEK ---
$stmtComments = $pdo->prepare("SELECT * FROM comments WHERE product_id = ? ORDER BY id DESC");
$stmtComments->execute([$product_id]);
$comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

$avgRating = 0;
$totalReviews = count($comments);
if ($totalReviews > 0) {
    $totalStars = 0;
    foreach($comments as $c) $totalStars += $c['rating'];
    $avgRating = round($totalStars / $totalReviews, 1);
}

// --- BENZER ÜRÜNLER ---
$stmtRelated = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? ORDER BY RAND() LIMIT 4");
$stmtRelated->execute([$product['category_id'], $product_id]);
$relatedProducts = $stmtRelated->fetchAll(PDO::FETCH_ASSOC);
?>

<nav class="bg-gray-100 py-3 border-b" aria-label="Breadcrumb">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-sm text-gray-500">
        <a href="index.php" class="hover:text-nature-green">Ana Sayfa</a> 
        <span class="mx-2">/</span>
        <a href="products.php" class="hover:text-nature-green">Tohumlar</a>
        <span class="mx-2">/</span>
        <span class="text-gray-800 font-semibold" aria-current="page"><?php echo $product['name']; ?></span>
    </div>
</nav>

<main class="bg-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            
            <section class="relative group">
                <div class="aspect-w-1 aspect-h-1 rounded-2xl overflow-hidden shadow-lg bg-gray-100 border border-gray-100">
                    <img class="w-full h-[500px] object-cover object-center transform group-hover:scale-105 transition duration-700" 
                         src="uploads/<?php echo $product['image']; ?>" 
                         alt="<?php echo $product['name']; ?> - Doğal Atalık Tohum"
                         title="<?php echo $product['name']; ?>">
                </div>
                <a href="wishlist-action.php?product_id=<?php echo $product['id']; ?>" class="absolute top-6 right-6 bg-white p-3 rounded-full text-red-500 shadow-xl hover:scale-110 transition z-10" title="Favorilere Ekle">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </a>
            </section>

            <article class="flex flex-col justify-center">
                
                <div class="flex justify-between items-center mb-4">
                    <span class="bg-green-100 text-nature-dark text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                        <?php echo $product['category_name'] ? $product['category_name'] : 'Genel'; ?>
                    </span>
                    <div class="flex items-center gap-1 text-yellow-400" title="Müşteri Puanı">
                        <span class="font-bold text-gray-700 text-lg mr-1"><?php echo $avgRating; ?></span>
                        <?php 
                        for($i=1; $i<=5; $i++) {
                            echo $i <= round($avgRating) ? '★' : '<span class="text-gray-300">★</span>';
                        }
                        ?>
                        <span class="text-xs text-gray-400 ml-1">(<?php echo $totalReviews; ?> Değerlendirme)</span>
                    </div>
                </div>

                <h1 class="text-4xl font-extrabold text-gray-900 mb-4"><?php echo $product['name']; ?></h1>
                
                <div class="text-gray-600 text-lg leading-relaxed mb-6">
                    <?php echo $product['description']; ?>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="text-xl">🌱</span> %100 Doğal Tohum
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="text-xl">🇹🇷</span> Yerli Üretim
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="text-xl">⚡</span> Hızlı Kargo
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="text-xl">🛡️</span> Güvenli Ödeme
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-8 mt-auto">
                    <div class="flex items-end gap-4 mb-2">
                        <span class="text-5xl font-bold text-nature-dark"><?php echo number_format($product['price'], 2); ?> <span class="text-2xl font-medium text-gray-500">₺</span></span>
                        
                        <?php if($product['stock'] > 0): ?>
                            <span class="text-green-600 font-bold bg-green-50 px-3 py-1 rounded-lg mb-2">Stokta Var</span>
                        <?php else: ?>
                            <span class="text-red-600 font-bold bg-red-50 px-3 py-1 rounded-lg mb-2">Stok Tükendi</span>
                        <?php endif; ?>
                    </div>

                    <?php if($product['stock'] <= 10 && $product['stock'] > 0): ?>
                        <div class="mb-6 flex items-center gap-2 text-red-600 font-bold bg-red-50 p-3 rounded-xl border border-red-100 animate-pulse w-fit">
                            <span>🔥</span> Acele Edin! Bu üründen sadece <?php echo $product['stock']; ?> adet kaldı.
                        </div>
                    <?php endif; ?>

                    <?php if($product['stock'] > 0): ?>
                        <form action="cart-add.php" method="POST" class="flex gap-4">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            
                            <div class="flex items-center border border-gray-300 rounded-xl w-32">
                                <button type="button" onclick="updateQty(-1)" class="w-10 h-full text-gray-600 hover:bg-gray-100 font-bold text-xl rounded-l-xl" aria-label="Adet Azalt">-</button>
                                <input type="number" id="qtyInput" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="w-full text-center font-bold text-lg outline-none appearance-none bg-transparent h-12" aria-label="Ürün Adedi">
                                <button type="button" onclick="updateQty(1)" class="w-10 h-full text-gray-600 hover:bg-gray-100 font-bold text-xl rounded-r-xl" aria-label="Adet Artır">+</button>
                            </div>

                            <button type="submit" class="flex-1 bg-nature-green text-white py-3 rounded-xl hover:bg-nature-dark transition font-bold text-lg shadow-lg hover:shadow-xl transform hover:-translate-y-1 flex items-center justify-center gap-2">
                                <span>🛒</span> Sepete Ekle
                            </button>
                        </form>
                    <?php else: ?>
                        <button disabled class="w-full bg-gray-200 text-gray-400 py-4 rounded-xl font-bold cursor-not-allowed">
                            Ürün Tükendi :(
                        </button>
                    <?php endif; ?>
                </div>

            </article>
        </div>

        <section class="mt-20 grid grid-cols-1 lg:grid-cols-3 gap-12" id="reviews">
            
            <div class="lg:col-span-1">
                <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200 sticky top-24">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Değerlendirme Yap</h3>
                    
                    <?php if($message): ?>
                        <div class="p-3 rounded mb-4 text-sm font-bold <?php echo $msgType == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if(isset($_SESSION['user_id'])): ?>
                        <form action="" method="POST">
                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Puanınız</label>
                                <div class="flex flex-row-reverse justify-end gap-2 w-fit">
                                    <input type="radio" id="star5" name="rating" value="5" class="peer hidden" required />
                                    <label for="star5" title="5 Yıldız" class="text-gray-300 peer-checked:text-yellow-400 hover:text-yellow-400 text-3xl cursor-pointer transition">★</label>
                                    <input type="radio" id="star4" name="rating" value="4" class="peer hidden" />
                                    <label for="star4" title="4 Yıldız" class="text-gray-300 peer-checked:text-yellow-400 hover:text-yellow-400 text-3xl cursor-pointer transition">★</label>
                                    <input type="radio" id="star3" name="rating" value="3" class="peer hidden" />
                                    <label for="star3" title="3 Yıldız" class="text-gray-300 peer-checked:text-yellow-400 hover:text-yellow-400 text-3xl cursor-pointer transition">★</label>
                                    <input type="radio" id="star2" name="rating" value="2" class="peer hidden" />
                                    <label for="star2" title="2 Yıldız" class="text-gray-300 peer-checked:text-yellow-400 hover:text-yellow-400 text-3xl cursor-pointer transition">★</label>
                                    <input type="radio" id="star1" name="rating" value="1" class="peer hidden" />
                                    <label for="star1" title="1 Yıldız" class="text-gray-300 peer-checked:text-yellow-400 hover:text-yellow-400 text-3xl cursor-pointer transition">★</label>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="comment" class="block text-sm font-bold text-gray-700 mb-2">Yorumunuz</label>
                                <textarea id="comment" name="comment" rows="4" required class="w-full border p-3 rounded-xl focus:ring-2 focus:ring-nature-green outline-none" placeholder="<?php echo $product['name']; ?> hakkındaki düşünceleriniz..."></textarea>
                            </div>
                            
                            <button type="submit" name="submit_review" class="w-full bg-nature-green text-white py-3 rounded-xl font-bold hover:bg-nature-dark transition shadow-lg">
                                Yorumu Gönder 🚀
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="text-center py-6">
                            <span class="text-4xl block mb-2">🔒</span>
                            <p class="text-gray-600 mb-4">Yorum yapmak için giriş yapmalısınız.</p>
                            <a href="login.php" class="inline-block bg-gray-800 text-white px-6 py-2 rounded-lg font-bold hover:bg-gray-700 transition">Giriş Yap</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lg:col-span-2">
                <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                    Müşteri Yorumları <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-sm"><?php echo count($comments); ?></span>
                </h3>

                <?php if(count($comments) > 0): ?>
                    <div class="space-y-6">
                        <?php foreach($comments as $c): ?>
                        <article class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-nature-green text-white rounded-full flex items-center justify-center font-bold text-lg">
                                        <?php echo strtoupper(substr($c['user_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800"><?php echo $c['user_name']; ?></h4>
                                        <time class="text-xs text-gray-400">
                                            <?php echo isset($c['created_at']) ? date("d.m.Y", strtotime($c['created_at'])) : 'Tarih yok'; ?>
                                        </time>
                                    </div>
                                </div>
                                <div class="text-yellow-400 text-lg tracking-wide">
                                    <?php echo str_repeat('★', $c['rating']); ?><?php echo str_repeat('<span class="text-gray-200">★</span>', 5 - $c['rating']); ?>
                                </div>
                            </div>
                            <p class="text-gray-600 leading-relaxed pl-14">
                                "<?php echo $c['comment']; ?>"
                            </p>
                        </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-blue-50 p-8 rounded-xl text-center border border-blue-100">
                        <span class="text-4xl block mb-4">💬</span>
                        <h4 class="text-xl font-bold text-blue-900 mb-2">Henüz yorum yapılmamış.</h4>
                        <p class="text-blue-700">Bu ürünü ilk değerlendiren siz olun!</p>
                    </div>
                <?php endif; ?>
            </div>

        </section>

        <?php if(count($relatedProducts) > 0): ?>
        <section class="mt-20 pt-10 border-t">
            <h2 class="text-3xl font-bold text-gray-800 mb-8">Bunları da Beğenebilirsiniz 🌱</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach($relatedProducts as $related): ?>
                <div class="bg-white rounded-xl shadow hover:shadow-xl transition duration-300 group overflow-hidden">
                    <a href="product-detail.php?id=<?php echo $related['id']; ?>" title="<?php echo $related['name']; ?> detayları">
                        <div class="h-48 overflow-hidden">
                            <img src="uploads/<?php echo $related['image']; ?>" 
                                 class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                                 alt="<?php echo $related['name']; ?> - Doğal Tohum">
                        </div>
                        <div class="p-5">
                            <h3 class="font-bold text-gray-800 truncate mb-2"><?php echo $related['name']; ?></h3>
                            <div class="flex justify-between items-center">
                                <span class="text-nature-green font-bold text-lg"><?php echo number_format($related['price'], 2); ?> ₺</span>
                                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded">İncele</span>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

    </div>
</main>

<script>
    function updateQty(change) {
        var input = document.getElementById('qtyInput');
        var currentVal = parseInt(input.value);
        var maxVal = parseInt(input.getAttribute('max'));
        var newVal = currentVal + change;
        if(newVal >= 1 && newVal <= maxVal) { input.value = newVal; }
    }
</script>

<?php require_once 'includes/footer.php'; ?>