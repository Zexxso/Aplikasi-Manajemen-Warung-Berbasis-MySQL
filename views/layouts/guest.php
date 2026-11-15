<?php
/**
 * @var string $slot
 * @var string|null $title
 */
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token()) ?>">

    <title><?= htmlspecialchars($title ?? 'Warungku - Catat Cepat, Cuan Tepat') ?></title>

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        primary: '#4B4EF0',
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-slate-950 text-slate-100 font-sans antialiased selection:bg-[#4B4EF0] selection:text-white">
    <!-- Mobile Screen Wrapper (Max 412px for Android WebView) -->
    <div class="w-full max-w-[412px] min-h-screen mx-auto bg-[#0B0E17] flex flex-col relative shadow-2xl overflow-x-hidden border-x border-slate-900/60">
        <?= $slot ?? '' ?>
    </div>
</body>
</html>
