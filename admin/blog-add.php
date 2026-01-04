<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    
    // Resim Yükleme
    $imageName = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array(strtolower($ext), $allowed)) {
            $imageName = "blog-" . time() . "." . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], "../uploads/" . $imageName);
        }
    }

    if (!empty($title) && !empty($content) && !empty($imageName)) {
        $stmt = $pdo->prepare("INSERT INTO blog (title, content, image) VALUES (?, ?, ?)");
        $stmt->execute([$title, $content, $imageName]);
        header("Location: blogs.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Blog Ekle</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

<div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-2xl">
    <div class="flex justify-between items-center mb-6 border-b pb-2">
        <h2 class="text-2xl font-bold text-gray-800">✍️ Yeni Yazı Ekle</h2>
        <a href="blogs.php" class="text-gray-500 hover:text-gray-700">İptal</a>
    </div>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Başlık</label>
            <input type="text" name="title" class="w-full border p-3 rounded focus:ring-2 focus:ring-green-500" required placeholder="Örn: Domates Ne Zaman Ekilir?">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Görsel</label>
            <input type="file" name="image" class="w-full border p-2 rounded bg-gray-50" required>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">İçerik</label>
            <textarea name="content" rows="10" class="w-full border p-3 rounded focus:ring-2 focus:ring-green-500" required placeholder="Yazınızı buraya yazın..."></textarea>
        </div>

        <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded hover:bg-green-700 transition shadow">
            Yayınla
        </button>
    </form>
</div>

</body>
</html>