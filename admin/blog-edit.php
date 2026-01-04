<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') { header("Location: login.php"); exit; }
require_once '../config/db.php';

$title = ""; $content = ""; $image = ""; $id = "";
$pageTitle = "Yeni Yazı Ekle";

// Eğer DÜZENLEME modundaysak verileri çek
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM blog WHERE id = ?");
    $stmt->execute([$id]);
    $blog = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($blog) {
        $title = $blog['title'];
        $content = $blog['content'];
        $image = $blog['image'];
        $pageTitle = "Yazıyı Düzenle";
    }
}

// KAYDETME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newTitle = $_POST['title'];
    $newContent = $_POST['content'];
    $newImage = $image;

    // Resim Yükleme
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetDir . $fileName)) {
            $newImage = $fileName;
        }
    }

    if ($id) {
        // GÜNCELLE
        $sql = "UPDATE blog SET title=?, content=?, image=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$newTitle, $newContent, $newImage, $id]);
    } else {
        // YENİ EKLE
        $sql = "INSERT INTO blog (title, content, image) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$newTitle, $newContent, $newImage]);
    }
    header("Location: blogs.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?> | DoğalPanel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: { colors: { 'nature-green': '#059669', 'nature-dark': '#064e3b' }, fontFamily: { 'sans': ['Poppins', 'sans-serif'] } }
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
                <a href="blogs.php" class="block px-4 py-2 bg-green-800 rounded text-white font-medium">✍️ Blog Yazıları</a>
                <a href="logout.php" class="block px-4 py-2 mt-10 hover:bg-red-700 rounded text-red-300 hover:text-white transition">🚪 Çıkış Yap</a>
            </nav>
        </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white shadow flex items-center justify-between px-6">
            <div class="text-xl font-semibold text-gray-800"><?php echo $pageTitle; ?></div>
            <a href="blogs.php" class="text-sm text-gray-500 hover:text-gray-800">← Vazgeç</a>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Başlık</label>
                        <input type="text" name="title" value="<?php echo $title; ?>" required class="w-full border p-3 rounded focus:ring-2 focus:ring-nature-green outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">İçerik</label>
                        <textarea name="content" rows="10" required class="w-full border p-3 rounded focus:ring-2 focus:ring-nature-green outline-none"><?php echo $content; ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kapak Resmi</label>
                        <?php if($image): ?>
                            <img src="../uploads/<?php echo $image; ?>" class="h-32 mb-4 rounded border">
                        <?php endif; ?>
                        <input type="file" name="image" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-green-50 file:text-nature-green hover:file:bg-green-100">
                    </div>
                    <div class="flex justify-end pt-4">
                        <button type="submit" class="bg-nature-green text-white px-8 py-3 rounded font-bold hover:bg-nature-dark transition shadow-lg">
                            💾 Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>
</body>
</html>