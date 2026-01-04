<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

// Yorumları Çek (Ürün adıyla birlikte)
$sql = "SELECT comments.*, products.name as product_name 
        FROM comments 
        LEFT JOIN products ON comments.product_id = products.id 
        ORDER BY comments.id DESC";
$stmt = $pdo->query($sql);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yorum Yönetimi</title>
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
                <a href="categories.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">📂 Kategoriler</a>
                <a href="comments.php" class="block px-4 py-2 bg-green-800 rounded text-white font-medium">💬 Yorumlar</a>
                <a href="blogs.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">✍️ Blog Yazıları</a>
                <a href="messages.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">📩 Mesajlar</a>
                <a href="logout.php" class="block px-4 py-2 mt-10 hover:bg-red-700 rounded text-red-300 hover:text-white transition">🚪 Çıkış Yap</a>
            </nav>
        </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white shadow flex items-center justify-between px-6">
            <div class="text-xl font-semibold text-gray-800">Yorum Yönetimi</div>
            <a href="../index.php" target="_blank" class="text-nature-green hover:underline text-sm">Siteyi Görüntüle →</a>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <h2 class="text-2xl font-bold text-gray-700 mb-6">Müşteri Yorumları</h2>

            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">Müşteri</th>
                            <th class="py-3 px-6 text-left">Ürün</th>
                            <th class="py-3 px-6 text-left">Yorum</th>
                            <th class="py-3 px-6 text-center">Puan</th>
                            <th class="py-3 px-6 text-center">Tarih</th>
                            <th class="py-3 px-6 text-center">İşlem</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 text-sm font-light">
                        <?php foreach($comments as $comment): ?>
                        <tr class="border-b border-gray-200 hover:bg-gray-100 transition">
                            <td class="py-3 px-6 text-left font-bold"><?php echo $comment['user_name']; ?></td>
                            <td class="py-3 px-6 text-left text-nature-green"><?php echo $comment['product_name']; ?></td>
                            <td class="py-3 px-6 text-left max-w-xs truncate" title="<?php echo $comment['comment']; ?>">
                                <?php echo substr($comment['comment'], 0, 50) . (strlen($comment['comment']) > 50 ? '...' : ''); ?>
                            </td>
                            <td class="py-3 px-6 text-center text-yellow-500 font-bold">
                                <?php echo $comment['rating']; ?> ★
                            </td>
                            <td class="py-3 px-6 text-center">
                                <?php echo date("d.m.Y", strtotime($comment['created_at'])); ?>
                            </td>
                            <td class="py-3 px-6 text-center">
                                <a href="comment-delete.php?id=<?php echo $comment['id']; ?>" onclick="return confirm('Bu yorumu silmek istediğine emin misin?')" class="bg-red-100 text-red-600 py-1 px-3 rounded-full text-xs font-bold hover:bg-red-200 transition">
                                    Sil 🗑️
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
</body>
</html>