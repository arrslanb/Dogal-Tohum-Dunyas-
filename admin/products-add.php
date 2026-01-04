<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

$message = "";

// Form Gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    // Resim Yükleme İşlemi
    $imageName = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['image']['name'];
        $filetype = $_FILES['image']['type'];
        $filesize = $_FILES['image']['size'];
        
        // Uzantıyı al
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($ext), $allowed)) {
            // Benzersiz isim oluştur (tohum-12345.jpg gibi)
            $newFilename = "tohum-" . time() . "." . $ext;
            $destination = "../uploads/" . $newFilename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $imageName = $newFilename;
            } else {
                $message = "Resim yüklenirken bir hata oluştu.";
            }
        } else {
            $message = "Sadece JPG, PNG ve WEBP formatları kabul edilir.";
        }
    }

    if (empty($message)) {
        $sql = "INSERT INTO products (name, category_id, description, price, image) VALUES (:name, :category_id, :description, :price, :image)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            'name' => $name,
            'category_id' => $category_id,
            'description' => $description,
            'price' => $price,
            'image' => $imageName
        ]);

        if ($result) {
            header("Location: products.php"); // Başarılıysa listeye dön
            exit;
        } else {
            $message = "Veritabanına eklenirken hata oluştu.";
        }
    }
}

// Kategorileri Çek (Select kutusu için)
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Yeni Tohum Ekle</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

<div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-2xl">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-2">🌱 Yeni Tohum Ekle</h2>
    
    <?php if($message): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?php echo $message; ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Tohum Adı</label>
                <input type="text" name="name" class="w-full border p-2 rounded focus:ring-2 focus:ring-green-500" required placeholder="Örn: Ata Domates Tohumu">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Kategori</label>
                <select name="category_id" class="w-full border p-2 rounded focus:ring-2 focus:ring-green-500">
                    <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Fiyat (TL)</label>
            <input type="number" step="0.01" name="price" class="w-full border p-2 rounded focus:ring-2 focus:ring-green-500" required placeholder="0.00">
        </div>

        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Açıklama</label>
            <textarea name="description" rows="4" class="w-full border p-2 rounded focus:ring-2 focus:ring-green-500" required placeholder="Tohum hakkında bilgi..."></textarea>
        </div>

        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">Ürün Görseli</label>
            <input type="file" name="image" class="w-full border p-2 rounded bg-gray-50">
            <p class="text-xs text-gray-500 mt-1">PNG, JPG veya WEBP</p>
        </div>

        <div class="flex justify-end gap-4">
            <a href="products.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">İptal</a>
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 font-bold">Kaydet</button>
        </div>
    </form>
</div>

</body>
</html>