<?php 
// Header dosyasını çağır (Oturum ve DB burada başlar)
require_once 'includes/header.php'; 

// --- VERİTABANI SORGULARI ---
$sliders = [];
$categories = [];
$featuredProducts = [];

try {
    if(isset($pdo)) {
        // 1. Sliderları Çek
        $stmt = $pdo->query("SELECT * FROM sliders WHERE status = 1 ORDER BY id DESC");
        $sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Kategorileri Çek
        $stmtCat = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
        $categories = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

        // 3. Öne Çıkan Ürünleri Çek
        $stmtFeatured = $pdo->query("SELECT * FROM products WHERE stock > 0 ORDER BY RAND() LIMIT 4");
        $featuredProducts = $stmtFeatured->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    // Veritabanı hatası sessizce geçilir
}
?>

<div class="relative bg-white overflow-hidden h-[500px] md:h-[600px] group">
    <?php if(count($sliders) > 0): ?>
        <div id="slider-container" class="w-full h-full relative">
            <?php foreach($sliders as $index => $slide): ?>
                <div class="absolute inset-0 transition-all duration-1000 ease-in-out slider-item <?php echo $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'; ?>" data-index="<?php echo $index; ?>">
                    <div class="absolute inset-0">
                        <img class="w-full h-full object-cover transform scale-100 group-hover:scale-105 transition duration-700" src="uploads/<?php echo $slide['image']; ?>" alt="<?php echo $slide['title']; ?>">
                        <div class="absolute inset-0 bg-black bg-opacity-50 md:bg-opacity-40"></div>
                    </div>
                    
                    <div class="relative max-w-7xl mx-auto h-full flex items-center px-4 sm:px-6 lg:px-8">
                        <div class="w-full md:w-2/3 md:pl-10 text-center md:text-left pt-10 md:pt-0">
                            <?php if($slide['title']): ?>
                            <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl mb-4 md:mb-6 drop-shadow-lg leading-tight">
                                <?php echo $slide['title']; ?>
                            </h1>
                            <?php endif; ?>
                            
                            <?php if($slide['description']): ?>
                            <p class="mt-2 md:mt-4 text-base md:text-xl text-gray-100 max-w-3xl drop-shadow-md font-medium mx-auto md:mx-0">
                                <?php echo $slide['description']; ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if(!empty($slide['link'])): ?>
                                <div class="mt-8 md:mt-10 flex justify-center md:justify-start gap-4">
                                    <a href="<?php echo $slide['link']; ?>" class="inline-flex items-center justify-center px-6 py-3 md:px-8 md:py-4 border border-transparent text-base md:text-lg font-bold rounded-lg text-nature-dark bg-white hover:bg-gray-100 transition shadow-xl transform hover:-translate-y-1">
                                        İncele →
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if(count($sliders) > 1): ?>
                <button onclick="changeSlide(-1)" class="absolute left-2 md:left-4 top-1/2 transform -translate-y-1/2 z-20 bg-black bg-opacity-30 hover:bg-opacity-50 text-white w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full transition text-xl md:text-2xl">❮</button>
                <button onclick="changeSlide(1)" class="absolute right-2 md:right-4 top-1/2 transform -translate-y-1/2 z-20 bg-black bg-opacity-30 hover:bg-opacity-50 text-white w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full transition text-xl md:text-2xl">❯</button>
            <?php endif; ?>
        </div>

        <script>
            let currentSlide = 0;
            const slides = document.querySelectorAll('.slider-item');
            const totalSlides = slides.length;
            let slideInterval;

            function changeSlide(direction) {
                if(totalSlides > 0) {
                    slides[currentSlide].classList.remove('opacity-100', 'z-10');
                    slides[currentSlide].classList.add('opacity-0', 'z-0');
                    currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
                    slides[currentSlide].classList.remove('opacity-0', 'z-0');
                    slides[currentSlide].classList.add('opacity-100', 'z-10');
                    resetTimer();
                }
            }

            function resetTimer() {
                if(totalSlides > 1) {
                    clearInterval(slideInterval);
                    slideInterval = setInterval(() => changeSlide(1), 6000);
                }
            }
            resetTimer();
        </script>
    <?php else: ?>
        <div class="absolute inset-0">
            <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1506413175690-b397c162585e?q=80&w=2070&auto=format&fit=crop" alt="Doğal Tarım Varsayılan">
            <div class="absolute inset-0 bg-gray-900 bg-opacity-50"></div>
        </div>
        <div class="relative max-w-7xl mx-auto h-full flex items-center px-4 sm:px-6 lg:px-8">
            <div class="w-full md:w-2/3 text-center md:text-left">
                <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl mb-4">
                    Topraktan Sofranıza <span class="text-green-400">Doğallık</span>
                </h1>
                <p class="mt-4 text-base md:text-xl text-gray-200 max-w-3xl mx-auto md:mx-0">
                    Genetiği değiştirilmemiş, %100 yerli atalık tohumlarla bahçenizi yeşertin.
                </p>
                <div class="mt-8 flex justify-center md:justify-start gap-4">
                    <a href="products.php" class="inline-flex items-center justify-center px-6 py-3 md:px-8 md:py-4 border border-transparent text-base md:text-lg font-bold rounded-lg text-nature-dark bg-white hover:bg-gray-100 transition shadow">
                        Alışverişe Başla
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="py-12 md:py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-8 text-center">Kategorilere Göz At</h2>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8">
            <?php foreach($categories as $cat): ?>
                <?php 
                    $icon = '🌱'; 
                    if(stripos($cat['name'], 'Sebze') !== false) $icon = '🍅';
                    elseif(stripos($cat['name'], 'Meyve') !== false) $icon = '🍓';
                    elseif(stripos($cat['name'], 'Yeşillik') !== false || stripos($cat['name'], 'Ot') !== false) $icon = '🌿';
                    elseif(stripos($cat['name'], 'Çiçek') !== false) $icon = '🌸';
                ?>
                
                <a href="products.php?category_id=<?php echo $cat['id']; ?>" class="bg-gray-50 p-4 md:p-8 rounded-2xl text-center hover:shadow-xl transition cursor-pointer group border border-gray-100 hover:bg-nature-green hover:border-nature-green">
                    <span class="text-4xl md:text-5xl block mb-2 md:mb-4 group-hover:scale-110 transition transform"><?php echo $icon; ?></span>
                    <span class="font-bold text-base md:text-xl text-gray-800 group-hover:text-white transition"><?php echo $cat['name']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="bg-gray-50 py-12 md:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-800 text-center md:text-left">Öne Çıkan Tohumlar ⭐</h2>
            <a href="products.php" class="text-nature-green font-bold hover:underline flex items-center gap-1">Tümünü Gör <span>→</span></a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="bg-white rounded-2xl shadow-md overflow-hidden hover:shadow-2xl transition duration-300 group flex flex-col h-full border border-gray-100">
                <div class="h-56 md:h-64 overflow-hidden relative">
                    <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                        <img src="uploads/<?php echo $product['image']; ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                    </a>
                    
                    <a href="wishlist-action.php?product_id=<?php echo $product['id']; ?>" class="absolute top-4 right-4 bg-white p-2 rounded-full text-red-500 shadow hover:scale-110 transition z-10" title="Favorilere Ekle">
                        ❤️
                    </a>

                    <form action="cart-add.php" method="POST" class="absolute bottom-4 right-4 z-10 transform translate-y-12 group-hover:translate-y-0 duration-300">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="bg-nature-green p-3 rounded-full text-white shadow-lg hover:bg-nature-dark transition flex items-center justify-center w-12 h-12">
                            🛒
                        </button>
                    </form>
                </div>
                <div class="p-5 md:p-6 flex-1 flex flex-col">
                    <h3 class="text-lg font-bold text-gray-800 mb-2 line-clamp-1">
                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="hover:text-nature-green transition"><?php echo $product['name']; ?></a>
                    </h3>

                    <?php if($product['stock'] <= 10 && $product['stock'] > 0): ?>
                        <div class="mb-2 text-red-600 font-bold text-xs animate-pulse flex items-center gap-1">
                            <span>🔥</span> Tükenmek Üzere! (Son <?php echo $product['stock']; ?> adet)
                        </div>
                    <?php endif; ?>

                    <div class="flex items-center justify-between mt-auto">
                        <span class="text-xl md:text-2xl font-bold text-nature-dark"><?php echo $product['price']; ?> ₺</span>
                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="text-xs text-gray-400 border border-gray-200 px-2 py-1 rounded hover:bg-gray-50">İncele</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="py-16 bg-white border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center gap-12">
            
            <div class="w-full md:w-1/2 relative group">
                <div class="absolute -inset-2 bg-nature-green rounded-2xl opacity-20 group-hover:opacity-40 transition blur-lg"></div>
                <img src="https://images.unsplash.com/photo-1530836369250-ef72a3f5cda8?q=80&w=2070&auto=format&fit=crop" class="relative rounded-2xl shadow-2xl transform transition group-hover:scale-105 duration-500 w-full object-cover h-96" alt="Doğal Tarım ve Filizlenme">
            </div>

            <div class="w-full md:w-1/2 space-y-6">
                <h2 class="text-3xl md:text-4xl font-extrabold text-gray-800">
                    Geleceği <span class="text-nature-green">Yeşertiyoruz</span> 🌱
                </h2>
                <p class="text-gray-600 text-lg leading-relaxed">
                    Doğal Tohum Dünyası olarak, kaybolmaya yüz tutmuş atalık tohumları koruyor ve sofralarınıza taşıyoruz. Genetiği ile oynanmamış, %100 yerli ve doğal tohumlarımızla sürdürülebilir tarımı destekliyoruz.
                </p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="flex items-center gap-3">
                        <span class="bg-green-100 text-nature-dark p-2 rounded-lg">🛡️</span>
                        <span class="font-bold text-gray-700">%100 Doğal ve Yerli</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="bg-green-100 text-nature-dark p-2 rounded-lg">🚛</span>
                        <span class="font-bold text-gray-700">Hızlı ve Güvenli Kargo</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="bg-green-100 text-nature-dark p-2 rounded-lg">🌱</span>
                        <span class="font-bold text-gray-700">Yüksek Çimlenme Oranı</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="bg-green-100 text-nature-dark p-2 rounded-lg">📞</span>
                        <span class="font-bold text-gray-700">Bahçıvanlık Desteği</span>
                    </div>
                </div>

                <div class="pt-4">
                    <a href="about.php" class="inline-block text-nature-green font-bold hover:text-nature-dark transition underline decoration-2 underline-offset-4">
                        Hikayemizi Daha Fazla Oku →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>