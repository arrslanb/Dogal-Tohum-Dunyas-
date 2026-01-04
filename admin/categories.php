<?php
session_start();
// Güvenlik
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

$message = "";
$msgType = "";

// 1. KATEGORİ SİLME İŞLEMİ
if (isset($_GET['delete'])) {
    $cat_id = $_GET['delete'];
    // Önce bu kategoride ürün var mı diye bak (Varsa silme)
    $check = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $check->execute([$cat_id]);
    if ($check->fetchColumn() > 0) {
        $message = "Bu kategoride ürünler var! Önce ürünleri başka kategoriye taşıyın veya silin.";
        $msgType = "error";
    } else {
        $del = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $del->execute([$cat_id]);
        $message = "Kategori silindi.";
        $msgType = "success";
    }
}

// 2. KATEGORİ EKLEME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        if ($stmt->execute([$name])) {
            $message = "Kategori eklendi: " . $name;
            $msgType = "success";
        } else {
            $message = "Hata oluştu.";
            $msgType = "error";
        }
    }
}

// 3. LİSTEYİ ÇEK
$categories = $pdo->query("SELECT * FROM categories ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kategoriler | DoğalPanel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'nature-green': '#059669', 'nature-dark': '#064e3b' },
                    fontFamily: { 'sans': ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 font-sans">

<div class="flex h-screen overflow-hidden">
    
    <div class="bg-nature-dark text-white w-64 flex-shrink-0 hidden md:flex flex-col">
        <div class="h-16 flex items-center justify-center border-b border-green-800">
            <span class="text-2xl font-bold tracking-wider">DoğalPanel</span>
        </div>
        <div class="flex-1 overflow-y-auto py-4">
            <nav class="space-y-2 px-2">
                <a href="index.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">📊 Gösterge Paneli</a>
                <a href="products.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">🌱 Tohumlar</a>
                <a href="orders.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">📦 Siparişler</a>
                <a href="categories.php" class="block px-4 py-2 bg-green-800 rounded text-white font-medium">📂 Kategoriler</a>
                <a href="comments.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">💬 Yorumlar</a>
                <a href="blogs.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">✍️ Blog Yazıları</a>
                <a href="messages.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">📩 Mesajlar</a>
                <a href="logout.php" class="block px-4 py-2 mt-10 hover:bg-red-700 rounded text-red-300 hover:text-white transition">🚪 Çıkış Yap</a>
            </nav>
        </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white shadow flex items-center justify-between px-6">
            <div class="text-xl font-semibold text-gray-800">Kategori Yönetimi</div>
            <div class="flex items-center gap-4">
                <a href="../index.php" target="_blank" class="text-nature-green hover:underline text-sm">Siteyi Görüntüle →</a>
                <div class="font-bold text-gray-700">
                    <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Yönetici'; ?>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            
            <?php if($message): ?>
                <div class="mb-6 p-4 rounded-lg <?php echo $msgType == 'success' ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="bg-white p-6 rounded-lg shadow h-fit">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Yeni Kategori Ekle</h3>
                    <form action="" method="POST">
                        <div class="mb-4">
                            <label class="block text-sm text-gray-600 mb-2">Kategori Adı</label>
                            <input type="text" name="name" placeholder="Örn: Meyve Tohumları" required 
                                   class="w-full border p-3 rounded focus:ring-2 focus:ring-nature-green outline-none">
                        </div>
                        <button type="submit" name="add_category" class="w-full bg-nature-green text-white py-2 rounded font-bold hover:bg-nature-dark transition">
                            + Ekle
                        </button>
                    </form>
                </div>

                <div class="col-span-2 bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kategori Adı</th>
                                <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($categories as $cat): ?>
                            <tr>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                    #<?php echo $cat['id']; ?>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm font-bold text-gray-700">
                                    <?php echo $cat['name']; ?>
                                </td>
                                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-right">
                                    <a href="categories.php?delete=<?php echo $cat['id']; ?>" onclick="return confirm('Silmek istediğine emin misin?')" class="text-red-500 hover:text-red-700 font-bold bg-red-50 px-3 py-1 rounded">
                                        Sil 🗑️
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if(empty($categories)) echo "<div class='p-4 text-center text-gray-500'>Henüz kategori yok.</div>"; ?>
                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>