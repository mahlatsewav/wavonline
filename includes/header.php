<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Determine base URL
$base_url = '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WavOnline C2C Marketplace</title>
    <!-- Tailwind CSS -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    
    <!-- Custom CSS -->
    <link href="<?php echo $base_url; ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="text-white antialiased">
<div class="noise-overlay pointer-events-none"></div>

<header class="sticky top-0 z-50 w-full flex flex-col">
    <nav class="border-b border-[var(--border-color)] bg-[var(--c1)] px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-8">
            <a href="<?php echo $base_url; ?>index.php" class="relative flex flex-col items-center justify-center hover:opacity-80 transition-opacity">
                <img src="<?php echo $base_url; ?>public/logo.png" alt="WavOnline Logo" class="h-8 md:h-10 relative z-10 md:-mb-3 drop-shadow-md">
                <span class="hidden md:inline-block font-ds text-2xl tracking-normal uppercase text-white relative z-0">WavOnline</span>
            </a>
            <div class="hidden md:flex items-center gap-6 font-cgr">
                <a class="text-sm text-[var(--c2)] hover:text-white transition-colors" href="<?php echo $base_url; ?>browse.php">Browse Listings</a>
            </div>
        </div>
        
        <div class="hidden md:flex items-center gap-4 font-cgr text-sm">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['seller', 'admin'])): ?>
                    <a class="px-4 py-2 bg-white text-black font-bold rounded-[var(--radius)] hover:bg-gray-200 transition-colors" href="<?php echo $base_url; ?>create_listing.php">+ Sell Item</a>
                <?php endif; ?>
                <div class="relative group">
                    <button class="flex items-center gap-2 text-[var(--c2)] hover:text-white transition-colors">
                        <?php if (!empty($_SESSION['profile_picture'])): ?>
                            <img src="<?php echo $base_url; ?>assets/uploads/profiles/<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" class="w-8 h-8 rounded-full object-cover border border-[var(--border-color)]">
                        <?php else: ?>
                            <div class="w-8 h-8 rounded-full bg-[var(--c3)] border border-[var(--border-color)] flex items-center justify-center text-xs font-bold text-white">
                                <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($_SESSION['name'] . (isset($_SESSION['surname']) ? ' ' . $_SESSION['surname'] : '')); ?>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-[var(--c3)] border border-[var(--border-color)] rounded-[var(--radius)] shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 flex flex-col py-2">
                        <a class="px-4 py-2 hover:bg-[var(--c1)] text-[var(--c2)] hover:text-white transition-colors" href="<?php echo $base_url; ?>profile.php">Profile</a>
                        <a class="px-4 py-2 hover:bg-[var(--c1)] text-[var(--c2)] hover:text-white transition-colors" href="<?php echo $base_url; ?>my_listings.php">My Listings</a>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <div class="h-px bg-[var(--border-color)] my-1"></div>
                            <a class="px-4 py-2 hover:bg-[var(--c1)] text-[var(--c2)] hover:text-white transition-colors" href="<?php echo $base_url; ?>admin/index.php">Admin Dashboard</a>
                        <?php endif; ?>
                        <div class="h-px bg-[var(--border-color)] my-1"></div>
                        <a class="px-4 py-2 hover:bg-[var(--c1)] text-red-400 hover:text-red-300 transition-colors" href="<?php echo $base_url; ?>logout.php">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a class="text-[var(--c2)] hover:text-white transition-colors" href="<?php echo $base_url; ?>login.php">Login</a>
                <a class="px-4 py-2 bg-white text-black font-medium rounded-[var(--radius)] hover:bg-gray-200 transition-colors" href="<?php echo $base_url; ?>register.php">Register</a>
            <?php endif; ?>
        </div>

        <div class="md:hidden flex items-center">
            <button id="mobile-menu-btn" class="text-white hover:text-[var(--c2)] focus:outline-none transition-colors">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>
    </nav>
    
    <div id="mobile-menu" class="hidden md:hidden bg-[var(--c1)] border-b border-[var(--border-color)] px-6 py-4 flex-col gap-4 font-cgr text-sm w-full shadow-xl overflow-y-auto max-h-[calc(100vh-73px)]">
        <a class="block py-2 text-[var(--c2)] hover:text-white transition-colors border-b border-[var(--border-color)]" href="<?php echo $base_url; ?>browse.php">Browse Listings</a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['seller', 'admin'])): ?>
                <a class="block py-2 text-white font-bold" href="<?php echo $base_url; ?>create_listing.php">+ Sell Item</a>
            <?php endif; ?>
            
            <div class="py-2 flex items-center gap-3 text-white border-t border-[var(--border-color)] mt-2 pt-4">
                <?php if (!empty($_SESSION['profile_picture'])): ?>
                    <img src="<?php echo $base_url; ?>assets/uploads/profiles/<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" class="w-10 h-10 rounded-full object-cover border border-[var(--border-color)]">
                <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-[var(--c3)] border border-[var(--border-color)] flex items-center justify-center text-sm font-bold text-white">
                        <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <span class="font-bold text-base"><?php echo htmlspecialchars($_SESSION['name'] . (isset($_SESSION['surname']) ? ' ' . $_SESSION['surname'] : '')); ?></span>
            </div>
            
            <a class="block py-2 pl-2 text-[var(--c2)] hover:text-white transition-colors" href="<?php echo $base_url; ?>profile.php">Profile</a>
            <a class="block py-2 pl-2 text-[var(--c2)] hover:text-white transition-colors" href="<?php echo $base_url; ?>my_listings.php">My Listings</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a class="block py-2 pl-2 text-[var(--c2)] hover:text-white transition-colors" href="<?php echo $base_url; ?>admin/index.php">Admin Dashboard</a>
            <?php endif; ?>
            <a class="block py-2 pl-2 text-red-400 hover:text-red-300 transition-colors" href="<?php echo $base_url; ?>logout.php">Logout</a>
        <?php else: ?>
            <a class="block py-2 text-[var(--c2)] hover:text-white transition-colors" href="<?php echo $base_url; ?>login.php">Login</a>
            <a class="block py-2 text-white font-medium transition-colors" href="<?php echo $base_url; ?>register.php">Register</a>
        <?php endif; ?>
    </div>
</header>

<script>
    document.getElementById('mobile-menu-btn').addEventListener('click', function() {
        var menu = document.getElementById('mobile-menu');
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            menu.classList.add('flex');
        } else {
            menu.classList.add('hidden');
            menu.classList.remove('flex');
        }
    });
</script>

<div class="main-content container mx-auto px-6 py-8 min-h-screen">