<?php

// Hata raporlamayı aç

error_reporting(E_ALL);

ini_set('display_errors', 1);



ob_start();



if (session_status() == PHP_SESSION_NONE) {

    session_start();

}



// Veritabanı bağlantısı

if (file_exists(__DIR__ . '/../config/db.php')) {

    require_once __DIR__ . '/../config/db.php';

} elseif (file_exists('config/db.php')) {

    require_once 'config/db.php';

}



$cartCount = 0;

$wishlistCount = 0;



if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {

    foreach($_SESSION['cart'] as $qty) {

        $cartCount += (int)$qty;

    }

}



if (isset($_SESSION['user_id']) && isset($pdo)) {

    try {

        $stmtW = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");

        $stmtW->execute([$_SESSION['user_id']]);

        $wishlistCount = $stmtW->fetchColumn();

    } catch (Exception $e) {}

}

?>

<!DOCTYPE html>

<html lang="tr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Doğal Tohum Dünyası</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

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

<body class="bg-gray-50 text-gray-800 font-sans flex flex-col min-h-screen">



<nav class="bg-white shadow-md sticky top-0 z-50">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex justify-between h-20">

            <div class="flex-shrink-0 flex items-center">

                <a href="index.php" class="flex items-center gap-2">

                    <span class="text-3xl md:text-4xl">🌱</span>

                    <span class="font-bold text-xl md:text-2xl text-gray-800 tracking-tight">Doğal<span class="text-nature-green">Tohum</span></span>

                </a>

            </div>



            <div class="hidden md:flex flex-1 items-center justify-center px-8 relative z-50">

                <div class="w-full max-w-lg relative">

                    <form action="products.php" method="GET" class="relative">

                        <input type="text" name="search" id="live_search" autocomplete="off" placeholder="Tohum ara..." class="w-full bg-gray-100 rounded-full py-2 pl-5 pr-12 focus:outline-none focus:ring-2 focus:ring-nature-green">

                        <button type="submit" class="absolute right-3 top-2 text-gray-400">🔍</button>

                    </form>

                    <div id="search_result" class="absolute w-full top-full left-0 mt-2 z-50"></div>

                </div>

            </div>



            <div class="flex items-center space-x-4 md:space-x-6">

                <div class="hidden md:flex space-x-6 text-sm font-medium text-gray-600">

                    <a href="index.php" class="hover:text-nature-green transition">Ana Sayfa</a>

                    <a href="products.php" class="hover:text-nature-green transition">Tohumlar</a>

                    <a href="about.php" class="hover:text-nature-green transition">Hakkımızda</a>

                    <a href="contact.php" class="hover:text-nature-green transition">İletişim</a>

                </div>



                <a href="cart.php" class="relative text-gray-600 hover:text-nature-green">

                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>

                    <?php if($cartCount > 0): ?>

                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full"><?php echo $cartCount; ?></span>

                    <?php endif; ?>

                </a>



                <button id="mobile-menu-btn" class="md:hidden text-gray-600 hover:text-nature-green focus:outline-none ml-2">

                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>

                </button>



                <div class="hidden md:block relative group">

                    <button class="flex items-center text-gray-600 hover:text-nature-green focus:outline-none" onclick="toggleDropdown()">

                        <span class="mr-1 font-medium"><?php echo !empty($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Hesabım'; ?></span>

                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>

                    </button>

                    <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-xl py-2 border border-gray-100 z-50">

                        <?php if(isset($_SESSION['user_id'])): ?>

                            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>

                                <a href="admin/index.php" class="block px-4 py-2 text-sm text-red-600 font-bold hover:bg-red-50">🔧 Yönetim</a>

                                <div class="border-t my-1"></div>

                            <?php endif; ?>

                            <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50">Profilim</a>

                            <a href="my-orders.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50">Siparişlerim</a>

                            <a href="wishlist.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50">Favorilerim</a>

                            <div class="border-t my-1"></div>

                            <a href="logout.php" class="block px-4 py-2 text-sm text-red-500 hover:bg-red-50">Çıkış</a>

                        <?php else: ?>

                            <a href="login.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50">Giriş Yap</a>

                            <a href="register.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-green-50">Kayıt Ol</a>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100 w-full absolute top-20 left-0 z-40 shadow-xl">

        <div class="px-4 py-4 space-y-3">

            <a href="index.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:bg-green-50">Ana Sayfa</a>

            <a href="products.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:bg-green-50">Tohumlar</a>

            <a href="about.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:bg-green-50">Hakkımızda</a>

            <a href="contact.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:bg-green-50">İletişim</a>

           

            <div class="border-t border-gray-100 my-2"></div>

           

            <?php if(isset($_SESSION['user_id'])): ?>

                <div class="px-3 py-2 text-sm text-gray-500 font-bold">Hoşgeldin, <?php echo $_SESSION['user_name']; ?></div>

                <a href="profile.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:bg-green-50">Profilim</a>

                <a href="my-orders.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:bg-green-50">Siparişlerim</a>

                <a href="wishlist.php" class="block px-3 py-2 text-base font-medium text-gray-700 hover:bg-green-50 flex justify-between">

                    <span>❤️ Favorilerim</span>

                    <?php if($wishlistCount > 0): ?><span class="bg-red-100 text-red-600 px-2 rounded-full text-xs font-bold"><?php echo $wishlistCount; ?></span><?php endif; ?>

                </a>

                <a href="logout.php" class="block px-3 py-2 text-base font-medium text-red-500 hover:bg-red-50">Çıkış Yap</a>

            <?php else: ?>

                <a href="login.php" class="block px-3 py-2 text-base font-medium text-nature-green font-bold">Giriş Yap</a>

                <a href="register.php" class="block px-3 py-2 text-base font-medium text-gray-700">Kayıt Ol</a>

            <?php endif; ?>

        </div>

    </div>

</nav>



<script>

    const btn = document.getElementById('mobile-menu-btn');

    const menu = document.getElementById('mobile-menu');

    if(btn && menu) {

        btn.addEventListener('click', () => { menu.classList.toggle('hidden'); });

    }



    function toggleDropdown() {

        const d = document.getElementById('userDropdown');

        if(d) d.classList.toggle('hidden');

    }

   

    window.onclick = function(e) {

        if (!e.target.closest('.group') && !e.target.closest('#mobile-menu-btn')) {

            const d = document.getElementById('userDropdown');

            if(d && !d.classList.contains('hidden')) d.classList.add('hidden');

        }

    }

   

    $(document).ready(function(){

        $("#live_search").keyup(function(){

            var input = $(this).val();

            if(input != ""){

                $.ajax({

                    url: "search-service.php",

                    method: "POST",

                    data: {query: input},

                    success: function(data){ $("#search_result").html(data).show(); }

                });

            } else { $("#search_result").hide(); }

        });

    });

</script>