<?php
require_once '../includes/db.php';
require_once '../includes/auth_helpers.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    
    // Prevent admin from banning or deleting themselves
    if ($user_id != $_SESSION['user_id']) {
        if ($action === 'ban') {
            $pdo->prepare("UPDATE users SET status = 'banned' WHERE id = ?")->execute([$user_id]);
        } elseif ($action === 'activate') {
            $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?")->execute([$user_id]);
        } elseif ($action === 'make_admin') {
            $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?")->execute([$user_id]);
        } elseif ($action === 'make_user') {
            $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ?")->execute([$user_id]);
        } elseif ($action === 'delete') {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
        }
    }
    header("Location: users.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin</title>
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
            <a class="text-white border-b border-[var(--primary-color)] pb-1" href="users.php">Users</a>
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
    <div class="mb-8 border-b border-[var(--border-color)] pb-4 flex justify-between items-end">
        <h2 class="font-dh text-4xl uppercase tracking-normal m-0">Manage Users</h2>
    </div>

    <div class="glass-panel rounded-[var(--radius)] overflow-x-auto border border-[var(--border-color)]">
        <table class="w-full text-left font-cgr">
            <thead class="bg-[var(--c1)] border-b border-[var(--border-color)] font-am text-xs uppercase tracking-widest text-[var(--c2)]">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border-color)]">
                <?php foreach($users as $user): ?>
                    <tr class="hover:bg-[var(--c1)] transition-colors">
                        <td class="px-6 py-4 font-am text-sm text-[var(--c2)]">#<?php echo $user['id']; ?></td>
                        <td class="px-6 py-4 text-white"><?php echo htmlspecialchars($user['name']); ?></td>
                        <td class="px-6 py-4 text-[var(--c2)]"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td class="px-6 py-4">
                            <?php if($user['role'] === 'admin'): ?>
                                <span class="px-2 py-1 bg-[var(--primary-color)] text-black font-am text-[10px] uppercase tracking-widest rounded font-bold">Admin</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-[var(--c3)] text-[var(--c2)] border border-[var(--border-color)] font-am text-[10px] uppercase tracking-widest rounded">User</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php if($user['status'] === 'active'): ?>
                                <span class="px-2 py-1 bg-green-900/30 text-green-400 border border-green-700/50 font-am text-[10px] uppercase tracking-widest rounded">Active</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-red-900/30 text-red-400 border border-red-700/50 font-am text-[10px] uppercase tracking-widest rounded">Banned</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right font-am text-xs">
                            <?php if($user['id'] != $_SESSION['user_id']): ?>
                                <form method="POST" class="inline-flex gap-2 justify-end">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    
                                    <?php if($user['status'] === 'active'): ?>
                                        <button type="submit" name="action" value="ban" class="px-3 py-1 border border-yellow-600/50 text-yellow-500 hover:bg-yellow-600/20 rounded transition-colors uppercase">Ban</button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="activate" class="px-3 py-1 border border-green-600/50 text-green-500 hover:bg-green-600/20 rounded transition-colors uppercase">Activate</button>
                                    <?php endif; ?>
                                    
                                    <?php if($user['role'] === 'user'): ?>
                                        <button type="submit" name="action" value="make_admin" class="px-3 py-1 border border-blue-600/50 text-blue-400 hover:bg-blue-600/20 rounded transition-colors uppercase">Make Admin</button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="make_user" class="px-3 py-1 border border-gray-600/50 text-gray-400 hover:bg-gray-600/20 rounded transition-colors uppercase">Make User</button>
                                    <?php endif; ?>

                                    <button type="submit" name="action" value="delete" class="px-3 py-1 border border-red-900/50 text-red-500 hover:bg-red-900/30 hover:text-red-400 rounded transition-colors uppercase" onclick="return confirm('Are you sure you want to delete this user? All their listings will also be deleted.');">Delete</button>
                                </form>
                            <?php else: ?>
                                <span class="text-[var(--c2)] opacity-50 uppercase tracking-widest">[You]</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
