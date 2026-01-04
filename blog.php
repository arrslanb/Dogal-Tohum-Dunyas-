<?php
require_once 'includes/header.php';

// --- TÜRKÇE TARİH İÇİN ÇEVİRİ DİZİSİ ---
$turkish_months = [
    'Jan' => 'OCA', 'Feb' => 'ŞUB', 'Mar' => 'MAR', 'Apr' => 'NİS', 'May' => 'MAY', 'Jun' => 'HAZ',
    'Jul' => 'TEM', 'Aug' => 'AĞU', 'Sep' => 'EYL', 'Oct' => 'EKİ', 'Nov' => 'KAS', 'Dec' => 'ARA'
];

// --- VERİTABANI BAĞLANTISI ---
try {
    // ⚠️ DÜZELTME BURADA: Tablo ismini admin paneliyle aynı yaptım ('blogs')
    $stmt = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC");
    $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Eğer tablo yoksa hata mesajı göster (Ama muhtemelen artık çalışacak)
    echo "<div class='bg-red-100 text-red-700 p-4 text-center font-bold'>Hata: " . $e->getMessage() . "</div>";
    $blogs = []; 
}
?>

<div class="relative bg-nature-dark overflow-hidden h-[40vh]">
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" 
             class="w-full h-full object-cover opacity-40">
    </div>
    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-900 opacity-80"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 h-full flex flex-col justify-center items-center text-center">
        <span class="bg-nature-green text-white px-4 py-1 rounded-full text-xs font-bold tracking-widest uppercase mb-6 shadow-lg transform hover:scale-105 transition cursor-default">
            🌱 Doğal Yaşam Rehberi
        </span>
        <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-4 drop-shadow-2xl">
            Bahçıvanın Günlüğü
        </h1>
        <p class="text-lg md:text-xl text-gray-200 max-w-2xl font-light">
            Toprağa dair ipuçları, ekim rehberleri ve doğal yaşamın sırları burada.
        </p>
    </div>
</div>

<div class="bg-gray-50 min-h-screen py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <?php if(count($blogs) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                <?php foreach($blogs as $blog): ?>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-2xl transition duration-500 transform hover:-translate-y-2 flex flex-col h-full group border border-gray-100">
                    
                    <div class="h-64 overflow-hidden relative">
                        <a href="blog-detail.php?id=<?php echo $blog['id']; ?>">
                            <?php if(!empty($blog['image'])): ?>
                                <img src="uploads/<?php echo $blog['image']; ?>" class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700">
                            <?php else: ?>
                                <div class="w-full h-full bg-gray-200 flex items-center justify-center text-gray-400">
                                    <span class="text-4xl">📷</span>
                                </div>
                            <?php endif; ?>
                        </a>
                        
                        <div class="absolute top-4 left-4 bg-white/95 backdrop-blur-md px-4 py-2 rounded-xl text-center shadow-lg border border-gray-100 group-hover:bg-nature-green group-hover:text-white transition duration-300">
                            <span class="block text-xs font-bold uppercase tracking-wider text-gray-500 group-hover:text-green-100 transition">
                                <?php 
                                    if(isset($blog['created_at'])) {
                                        $en_month = date("M", strtotime($blog['created_at']));
                                        echo isset($turkish_months[$en_month]) ? $turkish_months[$en_month] : $en_month;
                                    }
                                ?>
                            </span>
                            <span class="block text-2xl font-extrabold text-nature-dark group-hover:text-white transition">
                                <?php echo isset($blog['created_at']) ? date("d", strtotime($blog['created_at'])) : ''; ?>
                            </span>
                        </div>
                    </div>

                    <div class="p-8 flex-1 flex flex-col">
                        <div class="mb-4">
                            <span class="text-xs font-bold text-nature-green uppercase tracking-wider bg-green-50 px-3 py-1 rounded-full">Blog Yazısı</span>
                        </div>
                        
                        <h2 class="text-2xl font-bold text-gray-800 mb-3 group-hover:text-nature-green transition line-clamp-2">
                            <a href="blog-detail.php?id=<?php echo $blog['id']; ?>">
                                <?php echo $blog['title']; ?>
                            </a>
                        </h2>
                        
                        <p class="text-gray-600 leading-relaxed mb-6 flex-1 line-clamp-3">
                            <?php 
                                echo mb_substr(strip_tags($blog['content']), 0, 110) . '...';
                            ?>
                        </p>
                        
                        <div class="mt-auto border-t pt-5 flex items-center justify-between">
                            <a href="blog-detail.php?id=<?php echo $blog['id']; ?>" class="text-nature-dark font-bold hover:text-nature-green transition flex items-center gap-2 group-hover:gap-3 text-sm">
                                Devamını Oku 
                                <span>→</span>
                            </a>
                            <span class="text-xs text-gray-400 flex items-center gap-1">
                                🕒 
                                <?php 
                                    $word_count = str_word_count(strip_tags($blog['content']));
                                    echo ceil($word_count / 200) . " dk okuma";
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-24 bg-white rounded-3xl shadow-sm border border-gray-100 max-w-2xl mx-auto">
                <span class="text-7xl animate-bounce block mb-6">📝</span>
                <h3 class="text-2xl font-bold text-gray-800">Henüz Yazı Eklenmemiş</h3>
                <p class="text-gray-500 mt-3 text-lg">
                    Editörlerimiz şu an harika içerikler hazırlıyor. <br>Lütfen daha sonra tekrar kontrol edin.
                </p>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>