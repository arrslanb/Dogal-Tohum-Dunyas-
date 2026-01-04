<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: messages.php");
    exit;
}
$msg_id = $_GET['id'];

// 1. MESAJI OKUNDU OLARAK İŞARETLE
$update = $pdo->prepare("UPDATE messages SET status = 1 WHERE id = ?");
$update->execute([$msg_id]);

// 2. MESAJI ÇEK
$stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
$stmt->execute([$msg_id]);
$msg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$msg) die("Mesaj bulunamadı.");
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Mesaj Oku | DoğalPanel</title>
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
                <a href="messages.php" class="block px-4 py-2 bg-green-800 rounded text-white font-medium">📩 Mesajlar</a>
                <a href="logout.php" class="block px-4 py-2 mt-10 hover:bg-red-700 rounded text-red-300 hover:text-white transition">🚪 Çıkış Yap</a>
            </nav>
        </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white shadow flex items-center justify-between px-6">
            <div class="text-xl font-semibold text-gray-800">Mesaj Detayı</div>
            <a href="messages.php" class="text-sm text-gray-500 hover:text-gray-800">← Listeye Dön</a>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="max-w-3xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
                
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800"><?php echo $msg['subject']; ?></h2>
                        <p class="text-sm text-gray-500">Gönderen: <span class="font-bold text-gray-700"><?php echo $msg['name']; ?></span> (<a href="mailto:<?php echo $msg['email']; ?>" class="text-blue-500 hover:underline"><?php echo $msg['email']; ?></a>)</p>
                    </div>
                    <div class="text-sm text-gray-400">
                        <?php echo date("d.m.Y H:i", strtotime($msg['created_at'])); ?>
                    </div>
                </div>

                <div class="p-8 text-gray-700 leading-relaxed text-lg min-h-[200px]">
                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                </div>

                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                    <a href="messages.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">Geri Dön</a>
                    <a href="mailto:<?php echo $msg['email']; ?>?subject=RE: <?php echo $msg['subject']; ?>" class="px-4 py-2 bg-nature-green text-white rounded hover:bg-nature-dark transition flex items-center gap-2">
                        <span>✉️</span> Yanıtla
                    </a>
                </div>

            </div>
        </main>
    </div>
</div>

</body>
</html>