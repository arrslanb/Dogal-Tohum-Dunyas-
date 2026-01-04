<?php
// Türkçe karakter ve JSON ayarları
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *'); // Her yerden erişime izin ver (Hata önleyici)

// Gelen veriyi al
$input = json_decode(file_get_contents('php://input'), true);
$raw_message = isset($input['message']) ? trim($input['message']) : '';

// --- 1. YARDIMCI FONKSİYONLAR ---
function normalizeText($text) {
    $search = ['Ç', 'ç', 'Ğ', 'ğ', 'ı', 'İ', 'Ö', 'ö', 'Ş', 'ş', 'Ü', 'ü'];
    $replace = ['c', 'c', 'g', 'g', 'i', 'i', 'o', 'o', 's', 's', 'u', 'u'];
    $text = str_replace($search, $replace, $text);
    return strtolower($text);
}

function findBestMatch($userText, $intents) {
    $userText = normalizeText($userText);
    $words = explode(' ', $userText); 
    
    $bestIntent = null;
    $highestScore = 0;

    foreach ($intents as $key => $data) {
        $score = 0;
        foreach ($data['keywords'] as $keyword) {
            $keyword = normalizeText($keyword);
            // Kelime eşleşmesi
            if (strpos($userText, $keyword) !== false) {
                $score += 10;
            }
            // Benzerlik (Levenshtein) kontrolü
            foreach ($words as $word) {
                if (strlen($word) > 3) {
                    $lev = levenshtein($word, $keyword);
                    if ($lev <= 2 && $lev < strlen($keyword) / 2) { 
                        $score += 5; 
                    }
                }
            }
        }
        if ($score > $highestScore) {
            $highestScore = $score;
            $bestIntent = $data;
        }
    }
    return ($highestScore >= 5) ? $bestIntent : null;
}

// --- 2. VERİ SETİ ---
$intents = [
    'greeting' => [
        'keywords' => ['merhaba', 'selam', 'slm', 'gunaydin', 'naber', 'nasilsin'],
        'reply' => "Selamlar! 👋 Enerjim yerinde. Senin için ne yapabilirim?",
        'quick_replies' => ["Kargom nerede?", "Tohum öner", "İletişim"]
    ],
    'shipping' => [
        'keywords' => ['kargo', 'siparis', 'nerede', 'takip', 'durum', 'gelmedi'],
        'reply' => "Siparişini kontrol ediyorum... 📦 Kargo takibi için aşağıdaki butona tıkla.",
        'action' => ["text" => "🚚 Sipariş Takibi", "link" => "my-orders.php"],
        'quick_replies' => ["Başka sorum var"]
    ],
    'tomato' => [
        'keywords' => ['domates', 'kirmizi', 'salcalik', 'salkim', 'pembe domates'],
        'reply' => "En lezzetli domates tohumları bizde! 🍅 İşte popüler çeşitler:",
        'action' => ["text" => "🍅 Domatesleri Gör", "link" => "products.php?search=domates"],
        'quick_replies' => ["Biber tohumları", "Fiyatlar nasıl?"]
    ],
    'contact' => [
        'keywords' => ['iletisim', 'telefon', 'adres', 'yeriniz', 'numara', 'mail'],
        'reply' => "Bize her zaman ulaşabilirsin! 📞 İletişim bilgilerimiz:",
        'action' => ["text" => "📞 İletişim Sayfası", "link" => "contact.php"],
        'quick_replies' => ["Adres neresi?"]
    ],
    'thanks' => [
        'keywords' => ['tesekkur', 'sagol', 'eyvallah', 'super'],
        'reply' => "Rica ederim! 😊 Bol hasatlar dilerim!",
        'quick_replies' => ["Ana sayfaya dön"]
    ]
];

// --- 3. CEVAPLAMA ---
$matchedIntent = findBestMatch($raw_message, $intents);

$response = "Bunu tam anlayamadım ama öğreniyorum! 🤖 İstersen menüden seçebilirsin.";
$action = null;
$quick_replies = ["Kargom nerede?", "Domates tohumu", "İletişim"];

if ($matchedIntent) {
    $response = $matchedIntent['reply'];
    $action = isset($matchedIntent['action']) ? $matchedIntent['action'] : null;
    $quick_replies = isset($matchedIntent['quick_replies']) ? $matchedIntent['quick_replies'] : [];
}

echo json_encode([
    "reply" => $response,
    "action" => $action,
    "quick_replies" => $quick_replies
]);
?>