<?php
require_once '../includes/db.php';
require_once '../includes/auth_helpers.php';

requireAdmin();

// Get counts for dashboard
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$listings_count = $pdo->query("SELECT COUNT(*) FROM listings")->fetchColumn();
$categories_count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - WavOnline</title>
    <!-- Tailwind CSS  -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="text-white antialiased flex flex-col min-h-screen">
<div class="noise-overlay pointer-events-none"></div>

<nav class="border-b border-[var(--border-color)] bg-[var(--c1)] px-6 py-4 flex items-center justify-between sticky top-0 z-50">
    <div class="flex items-center gap-8">
        <a class="font-ds text-2xl tracking-normal uppercase text-[var(--primary-color)] hover:text-white transition-colors" href="index.php">Admin</a>
        <div class="hidden md:flex items-center gap-6 font-am text-xs uppercase tracking-widest">
            <a class="text-[var(--c2)] hover:text-white transition-colors" href="users.php">Users</a>
            <a class="text-[var(--c2)] hover:text-white transition-colors" href="categories.php">Categories</a>
            <a class="text-[var(--c2)] hover:text-white transition-colors" href="listings.php">Listings</a>
        </div>
    </div>
    <div class="flex items-center gap-4 font-am text-xs uppercase tracking-widest">
        <a class="text-[var(--c2)] hover:text-white transition-colors" href="../index.php">Main Site</a>
        <a class="text-red-400 hover:text-red-300 transition-colors" href="../logout.php">Logout</a>
    </div>
</nav>

<div class="container mx-auto px-6 py-12 flex-1">
    <div class="mb-12 border-b border-[var(--border-color)] pb-6">
        <h2 class="font-dh text-4xl uppercase tracking-normal m-0">Dashboard</h2>
        <p class="font-cgr text-[var(--c2)] mt-2">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>.</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="users.php" class="block group">
            <div class="glass-panel p-8 rounded-[var(--radius)] hover:border-[var(--primary-color)] transition-all h-full">
                <h5 class="font-am text-xs text-[var(--c2)] uppercase tracking-widest mb-4 group-hover:text-white transition-colors">Total Users</h5>
                <p class="font-ds text-6xl m-0 text-white"><?php echo $users_count; ?></p>
                <div class="mt-6 pt-4 border-t border-[var(--border-color)] text-[var(--primary-color)] font-am text-xs uppercase tracking-widest group-hover:translate-x-2 transition-transform">
                    Manage Users &rarr;
                </div>
            </div>
        </a>
        <a href="listings.php" class="block group">
            <div class="glass-panel p-8 rounded-[var(--radius)] hover:border-[var(--primary-color)] transition-all h-full">
                <h5 class="font-am text-xs text-[var(--c2)] uppercase tracking-widest mb-4 group-hover:text-white transition-colors">Total Listings</h5>
                <p class="font-ds text-6xl m-0 text-white"><?php echo $listings_count; ?></p>
                <div class="mt-6 pt-4 border-t border-[var(--border-color)] text-[var(--primary-color)] font-am text-xs uppercase tracking-widest group-hover:translate-x-2 transition-transform">
                    Manage Listings &rarr;
                </div>
            </div>
        </a>
        <a href="categories.php" class="block group">
            <div class="glass-panel p-8 rounded-[var(--radius)] hover:border-[var(--primary-color)] transition-all h-full">
                <h5 class="font-am text-xs text-[var(--c2)] uppercase tracking-widest mb-4 group-hover:text-white transition-colors">Categories</h5>
                <p class="font-ds text-6xl m-0 text-white"><?php echo $categories_count; ?></p>
                <div class="mt-6 pt-4 border-t border-[var(--border-color)] text-[var(--primary-color)] font-am text-xs uppercase tracking-widest group-hover:translate-x-2 transition-transform">
                    Manage Categories &rarr;
                </div>
            </div>
        </a>
    </div>
</div>
</body>
</html>