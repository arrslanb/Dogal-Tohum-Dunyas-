<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

// SİLME İŞLEMİ
if (isset($_GET['delete'])) {
    // Önce resmi klasörden sil, sonra veritabanından
    $stmt = $pdo->prepare("SELECT image FROM sliders WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $img = $stmt->fetchColumn();
    if ($img && file_exists("../uploads/$img")) {
        unlink("../uploads/$img");
    }
    
    $del = $pdo->prepare("DELETE FROM sliders WHERE id = ?");
    $del->execute([$_GET['delete']]);
    header("Location: sliders.php"); exit;
}

// EKLEME İŞLEMİ
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $desc = htmlspecialchars($_POST['description']);
    $link = htmlspecialchars($_POST['link']);
    
    // Resim Yükleme
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        
        // Sadece resim dosyalarına izin verelim
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $stmt = $pdo->prepare("INSERT INTO sliders (title, description, image, link) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $desc, $image_name, $link]);
                $message = "Manşet başarıyla eklendi! 🎉";
            } else {
                $message = "Resim yüklenirken hata oluştu.";
            }
        } else {
            $message = "Bu dosya bir resim değil.";
        }
    } else {
        $message = "Lütfen bir resim seçin.";
    }
}

$sliders = $pdo->query("SELECT * FROM sliders ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Slider Yönetimi | DoğalPanel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = { theme: { extend: { colors: { 'nature-green': '#059669', 'nature-dark': '#064e3b' }, fontFamily: { 'sans': ['Poppins', 'sans-serif'] } } } }
    </script>
</head>
<body class="bg-gray-100 font-sans">
<div class="flex h-screen overflow-hidden">
    
    <div class="bg-nature-dark text-white w-64 flex-shrink-0 hidden md:flex flex-col">
        <div class="h-16 flex items-center justify-center border-b border-green-800"><span class="text-2xl font-bold tracking-wider">DoğalPanel</span></div>
        <div class="flex-1 overflow-y-auto py-4">
            <nav class="space-y-2 px-2">
                <a href="index.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">📊 Gösterge Paneli</a>
                <a href="products.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">🌱 Tohumlar</a>
                <a href="orders.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">📦 Siparişler</a>
                <a href="categories.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">📂 Kategoriler</a>
                <a href="coupons.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">🎟️ Kuponlar</a>
                
                <a href="sliders.php" class="block px-4 py-2 bg-green-800 rounded text-white font-medium">🖼️ Slider (Manşet)</a>
                
                <a href="comments.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">💬 Yorumlar</a>
                <a href="blogs.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">✍️ Blog Yazıları</a>
                <a href="messages.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">📩 Mesajlar</a>
                <a href="logout.php" class="block px-4 py-2 mt-10 hover:bg-red-700 rounded text-red-300 transition">🚪 Çıkış Yap</a>
            </nav>
        </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white shadow flex items-center justify-between px-6">
            <div class="text-xl font-semibold text-gray-800">Slider (Manşet) Yönetimi</div>
            <a href="../index.php" target="_blank" class="text-nature-green text-sm">Siteyi Görüntüle →</a>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            
            <?php if($message): ?>
                <div class="mb-6 p-4 bg-green-100 text-green-700 rounded border border-green-400 font-bold"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="bg-white p-6 rounded-lg shadow h-fit">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Yeni Manşet Ekle</h3>
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Başlık (Slogan)</label>
                            <input type="text" name="title" placeholder="Örn: %50 İndirim Başladı!" class="w-full border p-2 rounded focus:ring-2 focus:ring-nature-green outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Açıklama</label>
                            <textarea name="description" rows="2" placeholder="Örn: Tüm sebze tohumlarında geçerli..." class="w-full border p-2 rounded focus:ring-2 focus:ring-nature-green outline-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Görsel (Geniş Resim Seçin)</label>
                            <input type="file" name="image" required class="w-full border p-2 rounded bg-gray-50 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Buton Linki (İsteğe bağlı)</label>
                            <input type="text" name="link" placeholder="products.php" class="w-full border p-2 rounded focus:ring-2 focus:ring-nature-green outline-none">
                        </div>
                        <button type="submit" class="w-full bg-nature-green text-white py-3 rounded font-bold hover:bg-nature-dark transition">
                            Manşeti Kaydet
                        </button>
                    </form>
                </div>

                <div class="col-span-2 grid grid-cols-1 gap-4">
                    <h3 class="text-lg font-bold text-gray-800">Aktif Manşetler</h3>
                    <?php foreach($sliders as $slide): ?>
                    <div class="bg-white p-4 rounded-lg shadow flex flex-col md:flex-row gap-4 items-center group relative overflow-hidden">
                        
                        <div class="w-full md:w-1/3 h-32 rounded-lg overflow-hidden border">
                            <img src="../uploads/<?php echo $slide['image']; ?>" class="w-full h-full object-cover">
                        </div>
                        
                        <div class="flex-1 text-center md:text-left">
                            <h4 class="text-xl font-bold text-gray-800"><?php echo $slide['title']; ?></h4>
                            <p class="text-gray-500 text-sm mt-1"><?php echo $slide['description']; ?></p>
                            <?php if($slide['link']): ?>
                                <span class="inline-block mt-2 text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded">Link: <?php echo $slide['link']; ?></span>
                            <?php endif; ?>
                        </div>

                        <a href="sliders.php?delete=<?php echo $slide['id']; ?>" onclick="return confirm('Bu manşeti silmek istiyor musun?')" class="absolute top-2 right-2 bg-red-500 text-white w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-700 transition shadow">
                            🗑️
                        </a>

                    </div>
                    <?php endforeach; ?>
                    
                    <?php if(empty($sliders)): ?>
                        <div class="p-8 bg-white rounded shadow text-center text-gray-500">
                            Henüz manşet yok. Soldan yeni bir tane ekle! 🖼️
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>
</div>
</body>
</html>