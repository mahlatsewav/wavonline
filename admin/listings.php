<?php
require_once '../includes/db.php';
require_once '../includes/auth_helpers.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $listing_id = $_POST['listing_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM listings WHERE id = ?")->execute([$listing_id]);
    } elseif ($action === 'remove') {
        $pdo->prepare("UPDATE listings SET status = 'removed' WHERE id = ?")->execute([$listing_id]);
    } elseif ($action === 'activate') {
        $pdo->prepare("UPDATE listings SET status = 'active' WHERE id = ?")->execute([$listing_id]);
    }
    header("Location: listings.php");
    exit;
}

$stmt = $pdo->query("SELECT l.*, u.name as seller_name, c.name as category_name 
                     FROM listings l 
                     JOIN users u ON l.user_id = u.id 
                     JOIN categories c ON l.category_id = c.id 
                     ORDER BY l.created_at DESC");
$listings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Listings - Admin</title>
    <!-- Tailwind CSS -->
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
            <a class="text-white border-b border-[var(--primary-color)] pb-1" href="listings.php">Listings</a>
        </div>
    </div>
    <div class="flex items-center gap-4 font-am text-xs uppercase tracking-widest">
        <a class="text-[var(--c2)] hover:text-white transition-colors" href="../index.php">Main Site</a>
        <a class="text-red-400 hover:text-red-300 transition-colors" href="../logout.php">Logout</a>
    </div>
</nav>

<div class="container mx-auto px-6 py-12 flex-1">
    <div class="mb-8 border-b border-[var(--border-color)] pb-4">
        <h2 class="font-dh text-4xl uppercase tracking-normal m-0">Manage Listings</h2>
    </div>
    
    <div class="glass-panel rounded-[var(--radius)] overflow-x-auto border border-[var(--border-color)]">
        <table class="w-full text-left font-cgr">
            <thead class="bg-[var(--c1)] border-b border-[var(--border-color)] font-am text-xs uppercase tracking-widest text-[var(--c2)]">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">Title</th>
                    <th class="px-6 py-4">Seller</th>
                    <th class="px-6 py-4">Category</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border-color)]">
                <?php foreach($listings as $listing): ?>
                    <tr class="hover:bg-[var(--c1)] transition-colors">
                        <td class="px-6 py-4 font-am text-sm text-[var(--c2)]">#<?php echo $listing['id']; ?></td>
                        <td class="px-6 py-4">
                            <a href="../listing.php?id=<?php echo $listing['id']; ?>" target="_blank" class="text-white hover:text-[var(--primary-color)] transition-colors">
                                <?php echo htmlspecialchars($listing['title']); ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 text-[var(--c2)]"><?php echo htmlspecialchars($listing['seller_name']); ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-[var(--c3)] text-[var(--c2)] border border-[var(--border-color)] font-am text-[10px] uppercase tracking-widest rounded">
                                <?php echo htmlspecialchars($listing['category_name']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <?php 
                            if($listing['status'] === 'active') echo '<span class="px-2 py-1 bg-green-900/30 text-green-400 border border-green-700/50 font-am text-[10px] uppercase tracking-widest rounded">Active</span>';
                            elseif($listing['status'] === 'sold') echo '<span class="px-2 py-1 bg-yellow-900/30 text-yellow-400 border border-yellow-700/50 font-am text-[10px] uppercase tracking-widest rounded">Sold</span>';
                            else echo '<span class="px-2 py-1 bg-red-900/30 text-red-400 border border-red-700/50 font-am text-[10px] uppercase tracking-widest rounded">Removed</span>';
                            ?>
                        </td>
                        <td class="px-6 py-4 text-right font-am text-xs">
                            <form method="POST" class="inline-flex gap-2 justify-end">
                                <input type="hidden" name="listing_id" value="<?php echo $listing['id']; ?>">
                                <?php if($listing['status'] !== 'removed'): ?>
                                    <button type="submit" name="action" value="remove" class="px-3 py-1 border border-yellow-600/50 text-yellow-500 hover:bg-yellow-600/20 rounded transition-colors uppercase">Suspend</button>
                                <?php else: ?>
                                    <button type="submit" name="action" value="activate" class="px-3 py-1 border border-green-600/50 text-green-500 hover:bg-green-600/20 rounded transition-colors uppercase">Activate</button>
                                <?php endif; ?>
                                <button type="submit" name="action" value="delete" class="px-3 py-1 border border-red-900/50 text-red-500 hover:bg-red-900/30 hover:text-red-400 rounded transition-colors uppercase" onclick="return confirm('Permanently delete this listing?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
