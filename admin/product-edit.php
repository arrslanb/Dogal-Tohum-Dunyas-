<?php
session_start();
// Güvenlik: Admin değilse at
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}
require_once '../config/db.php';

// ID kontrolü
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}
$product_id = $_GET['id'];
$message = "";
$msgType = "";

// 1. KATEGORİLERİ ÇEK (Dropdown için)
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// 2. GÜNCELLEME İŞLEMİ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock']; // İŞTE BU EKSİKTİ, ARTIK VAR!
    $category_id = $_POST['category_id'];
    
    // Resim Yükleme İşlemi
    $imagePath = $_POST['current_image']; // Varsayılan eski resim
    
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        $fileName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        
        // Resim tipi kontrolü (Sadece jpg, png, jpeg, gif)
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
        
        if (in_array(strtolower($fileType), $allowTypes)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                $imagePath = $fileName; // Yeni resim adı
            } else {
                $message = "Resim yüklenirken hata oluştu.";
                $msgType = "error";
            }
        } else {
            $message = "Sadece JPG, JPEG, PNG ve GIF dosyaları yüklenebilir.";
            $msgType = "error";
        }
    }

    // Veritabanını Güncelle
    if (!$message) { // Resim hatası yoksa devam et
        $sql = "UPDATE products SET name=?, description=?, price=?, stock=?, category_id=?, image=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$name, $description, $price, $stock, $category_id, $imagePath, $product_id])) {
            $message = "Ürün başarıyla güncellendi! ✅";
            $msgType = "success";
        } else {
            $message = "Güncelleme başarısız.";
            $msgType = "error";
        }
    }
}

// 3. MEVCUT ÜRÜN BİLGİLERİNİ ÇEK (Kutuları doldurmak için)
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Ürün bulunamadı.");
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Ürün Düzenle | DoğalPanel</title>
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
                <a href="products.php" class="block px-4 py-2 bg-green-800 rounded text-white font-medium">🌱 Tohumlar</a>
                <a href="orders.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">📦 Siparişler</a>
                <a href="categories.php" class="block px-4 py-2 hover:bg-green-700 rounded text-gray-300 hover:text-white transition">📂 Kategoriler</a>
                <a href="logout.php" class="block px-4 py-2 mt-10 hover:bg-red-700 rounded text-red-300 hover:text-white transition">🚪 Çıkış Yap</a>
            </nav>
        </div>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 bg-white shadow flex items-center justify-between px-6">
            <div class="text-xl font-semibold text-gray-800">Ürün Düzenle: <span class="text-nature-green"><?php echo $product['name']; ?></span></div>
            <a href="products.php" class="text-sm text-gray-500 hover:text-gray-800">← Listeye Dön</a>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            
            <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-8">
                
                <?php if($message): ?>
                    <div class="mb-6 p-4 rounded-lg <?php echo $msgType == 'success' ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Ürün Adı</label>
                            <input type="text" name="name" value="<?php echo $product['name']; ?>" required 
                                   class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-nature-green outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori</label>
                            <select name="category_id" class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-nature-green outline-none">
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo $cat['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fiyat (₺)</label>
                            <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required 
                                   class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-nature-green outline-none font-bold text-gray-800">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-nature-green mb-2">Stok Adedi 📦</label>
                            <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required 
                                   class="w-full border-2 border-nature-green p-3 rounded-lg focus:ring-2 focus:ring-green-600 outline-none font-bold bg-green-50 text-gray-800">
                            <p class="text-xs text-gray-500 mt-1">Stok eklendikçe burayı güncelleyin.</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ürün Açıklaması</label>
                        <textarea name="description" rows="4" required class="w-full border border-gray-300 p-3 rounded-lg focus:ring-2 focus:ring-nature-green outline-none"><?php echo $product['description']; ?></textarea>
                    </div>

                    <div class="border-t pt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-4">Ürün Resmi</label>
                        <div class="flex items-center space-x-6">
                            <div class="shrink-0">
                                <img class="h-24 w-24 object-cover rounded-lg border border-gray-200" src="../uploads/<?php echo $product['image']; ?>" alt="Mevcut Resim">
                            </div>
                            <label class="block">
                                <span class="sr-only">Resim Seç</span>
                                <input type="file" name="image" class="block w-full text-sm text-slate-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-full file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-green-50 file:text-nature-green
                                  hover:file:bg-green-100
                                "/>
                            </label>
                        </div>
                        <input type="hidden" name="current_image" value="<?php echo $product['image']; ?>">
                    </div>

                    <div class="flex justify-end gap-4 pt-6 border-t mt-6">
                        <a href="products.php" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-bold hover:bg-gray-300 transition">İptal</a>
                        <button type="submit" class="bg-nature-green text-white px-8 py-3 rounded-lg font-bold hover:bg-nature-dark transition shadow-lg flex items-center gap-2">
                            <span>💾</span> Kaydet ve Güncelle
                        </button>
                    </div>

                </form>
            </div>
        </main>
    </div>
</div>

</body>
</html>