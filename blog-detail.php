<?php
require_once 'includes/header.php';

if (!isset($_GET['id'])) {
    echo "<script>window.location.href='blog.php';</script>";
    exit;
}

$id = intval($_GET['id']); // Güvenlik için intval ekledim

// --- TÜRKÇE TARİH İÇİN ÇEVİRİ DİZİSİ ---
$turkish_months = [
    'Jan' => 'Ocak', 'Feb' => 'Şubat', 'Mar' => 'Mart', 'Apr' => 'Nisan', 'May' => 'Mayıs', 'Jun' => 'Haziran',
    'Jul' => 'Temmuz', 'Aug' => 'Ağustos', 'Sep' => 'Eylül', 'Oct' => 'Ekim', 'Nov' => 'Kasım', 'Dec' => 'Aralık'
];

// --- VERİTABANI BAĞLANTISI (DÜZELTME BURADA) ---
// Artık 'blog' değil 'blogs' tablosuna bakacak.
$stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
$stmt->execute([$id]);
$blog = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$blog) {
    echo "<div class='text-center py-32 bg-gray-50'>";
    echo "<div class='text-6xl mb-4'>🤷‍♂️</div>";
    echo "<h2 class='text-2xl font-bold text-gray-800'>Yazı Bulunamadı</h2>";
    echo "<p class='text-gray-500 mt-2'>Aradığınız içerik silinmiş veya taşınmış olabilir.</p>";
    echo "<a href='blog.php' class='inline-block mt-6 bg-nature-green text-white px-6 py-2 rounded-full font-bold hover:bg-nature-dark transition'>Blog'a Dön</a>";
    echo "</div>";
    require_once 'includes/footer.php';
    exit;
}

// Tarih Formatı
$en_month = date("M", strtotime($blog['created_at']));
$tr_month = isset($turkish_months[$en_month]) ? $turkish_months[$en_month] : $en_month;
$full_date = date("d", strtotime($blog['created_at'])) . ' ' . $tr_month . ' ' . date("Y", strtotime($blog['created_at']));
?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <article class="bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in-up">
            <?php if(!empty($blog['image'])): ?>
                <div class="h-96 w-full relative group">
                    <img src="uploads/<?php echo $blog['image']; ?>" class="w-full h-full object-cover transition duration-700 group-hover:scale-105">
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-90"></div>
                    
                    <div class="absolute bottom-8 left-8 text-white max-w-2xl">
                        <span class="bg-nature-green px-3 py-1 rounded text-xs font-bold uppercase tracking-wider mb-3 inline-block shadow-md">
                            Blog Yazısı
                        </span>
                        <h1 class="text-3xl md:text-5xl font-extrabold leading-tight drop-shadow-lg mb-3">
                            <?php echo $blog['title']; ?>
                        </h1>
                        <div class="flex items-center gap-3 text-sm font-medium text-gray-200">
                            <span class="flex items-center gap-1">📅 <?php echo $full_date; ?></span>
                            <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                            <span>Admin</span>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="p-10 border-b bg-gradient-to-r from-gray-50 to-white">
                    <span class="text-nature-green font-bold tracking-wider text-sm uppercase">Blog Yazısı</span>
                    <h1 class="text-4xl font-extrabold text-gray-900 mt-2 mb-4"><?php echo $blog['title']; ?></h1>
                    <p class="text-gray-500 font-medium">📅 <?php echo $full_date; ?></p>
                </div>
            <?php endif; ?>

            <div class="p-8 md:p-16 text-gray-700 leading-loose text-lg font-light space-y-6">
                <div class="first-letter:text-6xl first-letter:font-bold first-letter:text-nature-green first-letter:mr-3 first-letter:float-left first-letter:leading-none">
                    <?php echo nl2br($blog['content']); ?>
                </div>
            </div>

            <div class="bg-gray-50 p-8 border-t border-gray-100 flex justify-between items-center">
                <a href="blog.php" class="text-gray-600 hover:text-nature-green font-bold flex items-center gap-2 transition transform hover:-translate-x-2">
                    ← Tüm Yazılara Dön
                </a>
                
                <button class="text-gray-400 hover:text-nature-green transition" title="Paylaş">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                </button>
            </div>
        </article>

    </div>
</div>

<style>
    @keyframes fade-in-up {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up { animation: fade-in-up 0.8s ease-out forwards; }
</style>

<?php require_once 'includes/footer.php'; ?>