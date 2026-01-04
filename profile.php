<?php
require_once 'includes/header.php';

// Güvenlik: Giriş yapmayan giremez
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";
$msgType = "";

// --- 1. HESAP SİLME İŞLEMİ ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_account'])) {
    // Önce siparişleri var mı kontrol et (Opsiyonel: Siparişi varsa silme diyebilirsin ama şimdilik siliyoruz)
    // İlişkili verileri temizle (Sepet vb.)
    $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);
    
    // Kullanıcıyı Sil
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$user_id])) {
        // Oturumu Kapat
        session_destroy();
        echo "<script>alert('Hesabınız başarıyla silindi. Tekrar Görüşmek Üzere! 👋'); window.location.href='index.php';</script>";
        exit;
    } else {
        $message = "Hesap silinirken bir hata oluştu.";
        $msgType = "error";
    }
}

// --- 2. BİLGİ GÜNCELLEME İŞLEMİ ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_info'])) {
    $full_name = htmlspecialchars($_POST['full_name']);
    $phone = htmlspecialchars($_POST['phone']);
    
    // Adresi Parçalı Alıp Birleştirme
    $city = htmlspecialchars($_POST['city']);
    $district = htmlspecialchars($_POST['district']);
    $open_address = htmlspecialchars($_POST['open_address']);
    
    // Tam Adres Formatı: "Mahalle Sokak No:1 - Kadıköy / İstanbul"
    $full_address = "$open_address - $district / $city";

    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
    if ($stmt->execute([$full_name, $phone, $full_address, $user_id])) {
        $_SESSION['user_name'] = $full_name;
        $message = "Bilgileriniz başarıyla güncellendi! ✅";
        $msgType = "success";
    } else {
        $message = "Güncelleme sırasında hata oluştu.";
        $msgType = "error";
    }
}

// --- 3. ŞİFRE DEĞİŞTİRME İŞLEMİ ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($current_pass, $user['password'])) {
        if ($new_pass === $confirm_pass) {
            if (strlen($new_pass) >= 6) {
                $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                if ($update->execute([$new_hash, $user_id])) {
                    $message = "Şifreniz başarıyla değiştirildi! 🔐";
                    $msgType = "success";
                }
            } else {
                $message = "Yeni şifre en az 6 karakter olmalı.";
                $msgType = "error";
            }
        } else {
            $message = "Yeni şifreler eşleşmiyor.";
            $msgType = "error";
        }
    } else {
        $message = "Mevcut şifreniz hatalı.";
        $msgType = "error";
    }
}

// GÜNCEL BİLGİLERİ ÇEK
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

// Mevcut adresi parçalama (Varsa)
// Format: "Açık Adres - İlçe / İl" varsayıyoruz.
$dbAddress = $currentUser['address'];
$currentCity = "";
$currentDistrict = "";
$currentOpenAddress = "";

if (!empty($dbAddress)) {
    // Sondaki " / İl" kısmını bulmaya çalış
    $parts = explode(' / ', $dbAddress);
    if (count($parts) > 1) {
        $currentCity = end($parts); // Son parça İl
        
        // Geri kalan kısmı al
        $rest = implode(' / ', array_slice($parts, 0, -1));
        
        // " - " ile ayırıp ilçeyi bul
        $subParts = explode(' - ', $rest);
        if (count($subParts) > 1) {
            $currentDistrict = end($subParts); // Son parça İlçe
            $currentOpenAddress = implode(' - ', array_slice($subParts, 0, -1)); // Geri kalan açık adres
        } else {
            $currentOpenAddress = $rest;
        }
    } else {
        $currentOpenAddress = $dbAddress;
    }
}
?>

<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <h1 class="text-3xl font-bold text-gray-800 mb-8 flex items-center gap-3">
            <span>⚙️</span> Hesap Ayarlarım
        </h1>

        <?php if($message): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $msgType == 'success' ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <div class="bg-white p-8 rounded-xl shadow-md h-fit border border-gray-100">
                <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Kişisel Bilgiler</h2>
                <form action="" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Ad Soyad</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($currentUser['full_name']); ?>" required class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-nature-green outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">E-posta (Değiştirilemez)</label>
                        <input type="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" disabled class="w-full border p-3 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Telefon</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($currentUser['phone']); ?>" placeholder="0555..." class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-nature-green outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">İl</label>
                            <select name="city" id="citySelect" class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-nature-green outline-none bg-white">
                                <option value="">Seçiniz...</option>
                                </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">İlçe</label>
                            <input type="text" name="district" value="<?php echo htmlspecialchars($currentDistrict); ?>" placeholder="Örn: Kadıköy" class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-nature-green outline-none">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-1">Açık Adres (Mahalle, Sokak, No)</label>
                        <textarea name="open_address" rows="3" class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-nature-green outline-none"><?php echo htmlspecialchars($currentOpenAddress); ?></textarea>
                    </div>

                    <button type="submit" name="update_info" class="w-full bg-nature-green text-white py-3 rounded-lg font-bold hover:bg-nature-dark transition mt-2 transform active:scale-95">
                        💾 Bilgileri Güncelle
                    </button>
                </form>
            </div>

            <div class="space-y-8">
                <div class="bg-white p-8 rounded-xl shadow-md h-fit border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 border-b pb-2">Şifre Değiştir</h2>
                    <form action="" method="POST" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Mevcut Şifre</label>
                            <input type="password" name="current_password" required class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-nature-green outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Yeni Şifre</label>
                            <input type="password" name="new_password" required class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-nature-green outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-600 mb-1">Yeni Şifre (Tekrar)</label>
                            <input type="password" name="confirm_password" required class="w-full border p-3 rounded-lg focus:ring-2 focus:ring-nature-green outline-none">
                        </div>
                        <button type="submit" name="update_password" class="w-full bg-gray-800 text-white py-3 rounded-lg font-bold hover:bg-gray-700 transition mt-2 transform active:scale-95">
                            🔒 Şifreyi Güncelle
                        </button>
                    </form>
                </div>

                <div class="bg-red-50 p-6 rounded-xl border border-red-200">
                    <h3 class="text-red-700 font-bold text-lg mb-2">Emin misiniz?</h3>
                    <p class="text-red-600 text-sm mb-4">Hesabınızı silerseniz tüm sipariş geçmişiniz ve kayıtlı bilgileriniz kalıcı olarak silinecektir. Bu işlem geri alınamaz.</p>
                    <form action="" method="POST" onsubmit="return confirm('Hesabınızı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');">
                        <button type="submit" name="delete_account" class="w-full bg-red-600 text-white py-3 rounded-lg font-bold hover:bg-red-700 transition shadow-sm">
                            🗑️ Hesabımı Kalıcı Olarak Sil
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // 81 İlin Listesi (Alfabetik + Öncelikli)
    const cities = [
        "İstanbul", "Ankara", "İzmir", "Bursa", "Antalya", "Adana", "Konya", "Gaziantep", "Şanlıurfa", "Kocaeli",
        "Mersin", "Diyarbakır", "Hatay", "Manisa", "Kayseri", "Samsun", "Balıkesir", "Kahramanmaraş", "Van", "Aydın",
        "Tekirdağ", "Sakarya", "Denizli", "Muğla", "Eskişehir", "Mardin", "Trabzon", "Malatya", "Ordu", "Erzurum",
        "Afyonkarahisar", "Adıyaman", "Sivas", "Batman", "Tokat", "Elazığ", "Zonguldak", "Kütahya", "Osmaniye", "Çanakkale",
        "Şırnak", "Ağrı", "Çorum", "Giresun", "Isparta", "Aksaray", "Yozgat", "Muş", "Düzce", "Uşak",
        "Kırıkkale", "Kars", "Bingöl", "Rize", "Siirt", "Bolu", "Nevşehir", "Yalova", "Hakkari", "Kırklareli",
        "Burdur", "Karaman", "Karabük", "Kırşehir", "Erzincan", "Bilecik", "Sinop", "Iğdır", "Bartın", "Çankırı",
        "Artvin", "Kilis", "Gümüşhane", "Ardahan", "Tunceli", "Bayburt"
    ];
    
    // PHP'den gelen mevcut şehir
    const currentCity = "<?php echo $currentCity; ?>";

    const select = document.getElementById("citySelect");
    
    // Şehirleri Sırala (Alfabetik)
    cities.sort((a, b) => a.localeCompare(b, 'tr'));

    cities.forEach(city => {
        let option = document.createElement("option");
        option.value = city;
        option.text = city;
        if(city === currentCity) {
            option.selected = true;
        }
        select.appendChild(option);
    });
</script>

<?php require_once 'includes/footer.php'; ?>