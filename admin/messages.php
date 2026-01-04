<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

// SİLME İŞLEMİ
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: messages.php");
    exit;
}

// MESAJLARI ÇEK (En yeni en üstte)
$stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Mesajlar | DoğalPanel</title>
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
                <a href="comments.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">💬 Yorumlar</a>
                <a href="blogs.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">✍️ Blog Yazıları</a>
                <a href="messages.php" class="block px-4 py-2 bg-green-800 rounded text-white font-medium">📩 Mesajlar</a>
                <a href="logout.php" class="block px-4 py-2 mt-10 hover:bg-red-700 rounded text-red-300 hover:text-white transition">🚪 Çıkış Yap</a>
            </nav>
        </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white shadow flex items-center justify-between px-6">
            <div class="text-xl font-semibold text-gray-800">Gelen Kutusu</div>
            <div class="flex items-center gap-4">
                <a href="../index.php" target="_blank" class="text-nature-green hover:underline text-sm">Siteyi Görüntüle →</a>
                <div class="font-bold text-gray-700">
                    <?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Yönetici'; ?>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Durum</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Gönderen</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Konu</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Tarih</th>
                            <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($messages as $msg): ?>
                        <tr class="<?php echo $msg['status'] == 0 ? 'bg-green-50' : 'bg-white'; ?> border-b border-gray-200 hover:bg-gray-50 transition">
                            
                            <td class="px-5 py-5 text-sm">
                                <?php if($msg['status'] == 0): ?>
                                    <span class="bg-nature-green text-white px-2 py-1 rounded text-xs font-bold">YENİ</span>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">Okundu</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-5 py-5 text-sm">
                                <p class="text-gray-900 whitespace-no-wrap font-bold"><?php echo $msg['name']; ?></p>
                                <p class="text-gray-500 text-xs"><?php echo $msg['email']; ?></p>
                            </td>

                            <td class="px-5 py-5 text-sm">
                                <p class="text-gray-900 whitespace-no-wrap"><?php echo substr($msg['subject'], 0, 30) . '...'; ?></p>
                            </td>

                            <td class="px-5 py-5 text-center text-sm">
                                <p class="text-gray-900 whitespace-no-wrap">
                                    <?php echo date("d.m.Y H:i", strtotime($msg['created_at'])); ?>
                                </p>
                            </td>

                            <td class="px-5 py-5 text-center text-sm">
                                <div class="flex item-center justify-center">
                                    <a href="message-detail.php?id=<?php echo $msg['id']; ?>" class="w-8 h-8 rounded bg-blue-50 text-blue-600 flex items-center justify-center mr-2 hover:bg-blue-100 transition" title="Oku">
                                        👁️
                                    </a>
                                    <a href="messages.php?delete=<?php echo $msg['id']; ?>" onclick="return confirm('Bu mesajı silmek istediğine emin misin?')" class="w-8 h-8 rounded bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100 transition" title="Sil">
                                        🗑️
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if(empty($messages)) echo "<div class='p-6 text-center text-gray-500'>Gelen kutusu boş. 📭</div>"; ?>
            </div>
        </main>
    </div>
</div>
</body>
</html>