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
        'keywords' => ['merhaba', 'selam', 'slm', 'gunaydin', 'iyi aksamlar', 'naber', 'nasilsin', 'hey'],
        'reply' => "Selamlar! 👋 Ben senin AI Bahçıvanın. Toprakla uğraşmaktan ellerim biraz kirli ama senin sorularını cevaplamak için klavye başına geçtim! 🌿 Bugün hangi mucizeyi ekiyorsun?",
        'quick_replies' => ["Tohum öner", "İndirim kodu?", "Kargom nerede?"]
    ],
    'emergency_support' => [
        'keywords' => ['sorun', 'sikayet', 'hata', 'bozuk', 'yanlis', 'eksik', 'gelmedi', 'rezalet', 'berbat', 'kotu', 'magdur', 'iade', 'iptal', 'parami', 'destek', 'yardim', 'hey bak'],
        'reply' => "Eyvah eyvah! 🚨 Tansiyon yükselmesin, hemen müdahale ediyorum. Seni doğrudan 'Ana Kumanda Merkezi'ne (WhatsApp) bağlıyorum. Uzman arkadaşlarım seni pamuklara saracak! 🤝",
        'action' => ["text" => "🟢 Hemen Çözüm (WhatsApp)", "link" => "https://wa.me/905XXXXXXXXX"],
        'quick_replies' => ["Siparişlerim", "Hakkımızda"]
    ],
    'shipping' => [
        'keywords' => ['kargo', 'siparis', 'nerede', 'takip', 'durum', 'kac gun', 'ucret', 'bedava', 'yolda mi'],
        'reply' => "Kargon şu an yollarda, belkide bir kuryenin çantasında gün ışığını bekliyor! 📦 500 TL üzeri alışverişlerde kargo bizden. Siparişinin tam konumunu şuradan görebilirsin:",
        'action' => ["text" => "🚚 Sipariş Takibi", "link" => "my-orders.php"],
        'quick_replies' => ["Kargo bedava mı?", "Kurye notu"]
    ],
    'recom_popular' => [
        'keywords' => ['ne ekilir', 'tohum oner', 'tavsiye', 'ne alsam', 'en cok satilan', 'en iyi', 'hangisi', 'favori'],
        'reply' => "Bak şimdi, eğer acemiysen Sırık Fasulye seni üzmez. Ama 'ben bu işin gurmesiyim' dersen Atalık Pembe Domates baş tacımızdır! 🍅 Sizin için seçtiğim şampiyonlar ligine bir bak:",
        'action' => ["text" => "🌱 Şampiyon Tohumlar", "link" => "products.php?filter=popular"],
        'quick_replies' => ["Saksı için tohum", "Kışlık sebzeler"]
    ],
    'discounts' => [
        'keywords' => ['indirim', 'kupon', 'kod', 'ucuz', 'kampanya', 'hediye', 'bedava', 'firsat', 'bele'],
        'reply' => "Sana bir bahçıvan sırrı vereyim mi? 🤫 Ödeme ekranında **KIŞ20** yazarsan fiyatlar bir anda çiçek açar ve %20 düşer! Bu aramızda kalsın...",
        'quick_replies' => ["Ürünleri listele", "Kodu nasıl kullanırım?"]
    ],
    'planting_guide' => [
        'keywords' => ['nasil ekilir', 'ekim', 'dikim', 'toprak', 'sulama', 'nezaman', 'derinlik', 'mesafe', 'can suyu'],
        'reply' => "Altın kural: Tohumu çok derine gömme, boğulmasın; çok yüzeyde bırakma, üşümesin! 📏 Tohumun 2-3 katı derinlik idealdir. Can suyunu da fısfısla ver, şok yaşamasın bebekler!",
        'quick_replies' => ["Sulama rehberi", "Hangi ayda ekilir?"]
    ],
    'pest_disaster' => [
        'keywords' => ['bocek', 'bit', 'hastalik', 'sari yaprak', 'leke', 'ilac', 'dogal ilac', 'kurudu', 'curudu', 'olmadi'],
        'reply' => "Bitkin biraz keyifsiz mi? 🤒 Üzülme, her bahçıvanın başına gelir. Fotoğrafını çekip bana (yani WP ekibine) at, hemen bir reçete yazalım. Kimyasala hayır, doğal çözüme evet!",
        'action' => ["text" => "💬 Bahçıvan Desteği", "link" => "https://wa.me/905XXXXXXXXX"],
        'quick_replies' => ["Arap sabunu tarifi", "İletişim"]
    ],
    'about_us' => [
        'keywords' => ['kimsiniz', 'hakkimizda', 'hikayeniz', 'guvenilir mi', 'neredesiniz', 'atalik nedir'],
        'reply' => "2015'ten beri dededen kalma tohumların peşindeyiz. 👵 GDO'ya savaş açtık, hibrit tohumu kapıdan sokmuyoruz! Biz bir aileyiz, sen de artık bu ailenin bir parçasısın.",
        'action' => ["text" => "📖 Hikayemizi Keşfet", "link" => "about.php"],
        'quick_replies' => ["Tohumlar yerli mi?", "İletişim"]
    ],
    'tomato_king' => [
        'keywords' => ['domates', 'pembe', 'ceri', 'salcalik', 'salkim', 'domatis'],
        'reply' => "Domatesin kralı burada! 🍅 Isırdığında o eski mahalle manavının kokusunu almazsan gel yanıma. Pembe domatesimiz meşhurdur, benden söylemesi!",
        'action' => ["text" => "🍅 Domates Krallığı", "link" => "products.php?search=domates"],
        'quick_replies' => ["Biber tohumu", "Salatalık"]
    ],
    'payment_info' => [
        'keywords' => ['odeme', 'kredi karti', 'havale', 'eft', 'guvenli mi', 'taksit', 'kartla odeme'],
        'reply' => "Cüzdanın bize emanet! 💳 3D Secure ile korunuyorsun. İster kartla öde, ister havale yap. Tek kuralımız: Sevgiyle ekmen!",
        'quick_replies' => ["İletişim", "Kargo ücreti"]
    ],
    'contact_us' => [
        'keywords' => ['iletisim', 'telefon', 'adres', 'yeriniz', 'numara', 'mail', 'whatsapp', 'neredesiniz'],
        'reply' => "Sana bir telefon kadar uzağım (aslında bir tık kadar)! 📞 Hafta içi çayımız hep taze, telefonumuz hep açık. Buyur gel veya yaz:",
        'action' => ["text" => "📞 İletişim Sayfası", "link" => "contact.php"],
        'quick_replies' => ["WhatsApp Yaz", "E-posta gönder"]
    ],
    'thanks' => [
        'keywords' => ['tesekkur', 'sagol', 'eyvallah', 'super', 'adamsin', 'cansin', 'tesekkurler', 'helal'],
        'reply' => "Rica ederim canım benim! 😊 Senin bahçen yeşerdikçe benim devrelerim bayram ediyor. Başka bir emrin olursa fidan diker gibi buradayım!",
        'quick_replies' => ["Yeni soru sor", "Ürünleri gez"]
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