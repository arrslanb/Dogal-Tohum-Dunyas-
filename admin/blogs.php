<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

// BLOG TABLOSUNU OLUŞTUR (Yoksa)
$pdo->exec("CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// SİLME İŞLEMİ
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: blogs.php"); exit;
}

// EKLEME İŞLEMİ
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = htmlspecialchars($_POST['title']);
    $content = htmlspecialchars($_POST['content']); // Editörden gelen veri
    $image = "";

    // Resim Yükleme
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        $image = time() . "_" . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image);
    }

    if (!empty($title) && !empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO blogs (title, content, image) VALUES (?, ?, ?)");
        $stmt->execute([$title, $content, $image]);
        $message = "Yazı başarıyla yayınlandı! ✍️";
    }
}

$blogs = $pdo->query("SELECT * FROM blogs ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Blog Yönetimi | DoğalPanel</title>
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
                <a href="comments.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">💬 Yorumlar</a>
                <a href="blogs.php" class="block px-4 py-2 bg-green-800 rounded text-white font-medium">✍️ Blog Yazıları</a>
                <a href="messages.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 transition">📩 Mesajlar</a>
                <a href="logout.php" class="block px-4 py-2 mt-10 hover:bg-red-700 rounded text-red-300 transition">🚪 Çıkış Yap</a>
            </nav>
        </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white shadow flex items-center justify-between px-6">
            <div class="text-xl font-semibold text-gray-800">Blog Yönetimi</div>
            <a href="../blog.php" target="_blank" class="text-nature-green text-sm">Blog Sayfasına Git →</a>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            
            <?php if($message): ?>
                <div class="mb-6 p-4 bg-green-100 text-green-700 rounded border border-green-400 font-bold"><?php echo $message; ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="bg-white p-6 rounded-lg shadow h-fit">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Yeni Yazı Ekle</h3>
                    <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Başlık</label>
                            <input type="text" name="title" required class="w-full border p-2 rounded focus:ring-2 focus:ring-nature-green outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Kapak Görseli</label>
                            <input type="file" name="image" class="w-full border p-2 rounded bg-gray-50 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">İçerik</label>
                            <textarea name="content" rows="10" required class="w-full border p-2 rounded focus:ring-2 focus:ring-nature-green outline-none" placeholder="Yazınızı buraya yazın..."></textarea>
                        </div>
                        <button type="submit" class="w-full bg-nature-green text-white py-3 rounded font-bold hover:bg-nature-dark transition">
                            Yayınla 🚀
                        </button>
                    </form>
                </div>

                <div class="col-span-2 space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Yayınlanan Yazılar</h3>
                    <?php foreach($blogs as $blog): ?>
                    <div class="bg-white p-4 rounded-lg shadow flex gap-4 items-start group hover:shadow-md transition">
                        <div class="w-24 h-24 flex-shrink-0 bg-gray-200 rounded overflow-hidden">
                            <?php if($blog['image']): ?>
                                <img src="../uploads/<?php echo $blog['image']; ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs">Görsel Yok</div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-lg font-bold text-gray-800 group-hover:text-nature-green transition"><?php echo $blog['title']; ?></h4>
                            <p class="text-sm text-gray-500 mt-1 line-clamp-2"><?php echo $blog['content']; ?></p>
                            <div class="mt-3 flex justify-between items-center">
                                <span class="text-xs text-gray-400"><?php echo date("d.m.Y", strtotime($blog['created_at'])); ?></span>
                                <a href="blogs.php?delete=<?php echo $blog['id']; ?>" onclick="return confirm('Silmek istiyor musunuz?')" class="text-red-500 text-sm font-bold hover:underline">Sil 🗑️</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if(empty($blogs)): ?>
                        <div class="p-8 bg-white rounded shadow text-center text-gray-500">
                            Henüz hiç yazı yok. İlk yazını soldan ekle! ✍️
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>
</div>
</body>
</html>