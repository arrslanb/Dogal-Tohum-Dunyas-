<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        header("Location: categories.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kategori Ekle</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

<div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
    <div class="flex justify-between items-center mb-6 border-b pb-2">
        <h2 class="text-2xl font-bold text-gray-800">📂 Yeni Kategori</h2>
        <a href="categories.php" class="text-gray-500 hover:text-gray-700">İptal</a>
    </div>

    <form action="" method="POST">
        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">Kategori Adı</label>
            <input type="text" name="name" class="w-full border p-3 rounded focus:ring-2 focus:ring-green-500" placeholder="Örn: Baklagiller" required>
        </div>

        <button type="submit" class="w-full bg-nature-green text-white font-bold py-3 rounded hover:bg-green-700 transition shadow">
            Kaydet
        </button>
    </form>
</div>

</body>
</html>