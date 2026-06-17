<?php
require_once '../includes/db.php';
require_once '../includes/auth_helpers.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if (!empty($name)) {
            $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)")->execute([$name, $description]);
        }
    } elseif ($action === 'delete') {
        $cat_id = $_POST['category_id'] ?? 0;
        $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$cat_id]);
    } elseif ($action === 'edit') {
        $cat_id = $_POST['category_id'] ?? 0;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if (!empty($name)) {
            $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?")->execute([$name, $description, $cat_id]);
        }
    }
    header("Location: categories.php");
    exit;
}

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories - Admin</title>
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
            <a class="text-white border-b border-[var(--primary-color)] pb-1" href="categories.php">Categories</a>
            <a class="text-[var(--c2)] hover:text-white transition-colors" href="listings.php">Listings</a>
        </div>
    </div>
    <div class="flex items-center gap-4 font-am text-xs uppercase tracking-widest">
        <a class="text-[var(--c2)] hover:text-white transition-colors" href="../index.php">Main Site</a>
        <a class="text-red-400 hover:text-red-300 transition-colors" href="../logout.php">Logout</a>
    </div>
</nav>

<div class="container mx-auto px-6 py-12 flex-1">
    <div class="mb-8 border-b border-[var(--border-color)] pb-4">
        <h2 class="font-dh text-4xl uppercase tracking-normal m-0">Manage Categories</h2>
    </div>
    
    <div class="glass-panel p-6 rounded-[var(--radius)] mb-8 border border-[var(--border-color)]">
        <h5 class="font-dh text-xl uppercase tracking-normal mb-4 text-[var(--c2)]">Add New Category</h5>
        <form method="POST" class="flex flex-col md:flex-row gap-4">
            <input type="hidden" name="action" value="add">
            <div class="flex-1">
                <input type="text" name="name" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded px-4 py-2 focus:outline-none focus:border-[var(--c2)] transition-colors" placeholder="Category Name" required>
            </div>
            <div class="flex-[2]">
                <input type="text" name="description" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded px-4 py-2 focus:outline-none focus:border-[var(--c2)] transition-colors" placeholder="Description">
            </div>
            <div>
                <button type="submit" class="w-full md:w-auto bg-[var(--primary-color)] text-black font-cgr font-bold uppercase tracking-wider rounded px-6 py-2 hover:bg-white transition-colors">Add Category</button>
            </div>
        </form>
    </div>

    <div class="glass-panel rounded-[var(--radius)] overflow-x-auto border border-[var(--border-color)]">
        <table class="w-full text-left font-cgr">
            <thead class="bg-[var(--c1)] border-b border-[var(--border-color)] font-am text-xs uppercase tracking-widest text-[var(--c2)]">
                <tr>
                    <th class="px-6 py-4 w-16">ID</th>
                    <th class="px-6 py-4 w-1/4">Name</th>
                    <th class="px-6 py-4">Description</th>
                    <th class="px-6 py-4 text-right w-48">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[var(--border-color)]">
                <?php foreach($categories as $cat): ?>
                    <tr class="hover:bg-[var(--c1)] transition-colors">
                        <form method="POST">
                            <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                            <td class="px-6 py-4 font-am text-sm text-[var(--c2)]">#<?php echo $cat['id']; ?></td>
                            <td class="px-6 py-4">
                                <input type="text" name="name" value="<?php echo htmlspecialchars($cat['name']); ?>" class="w-full bg-black/50 border border-[var(--border-color)] text-white font-cgr rounded px-2 py-1 focus:outline-none focus:border-[var(--c2)] transition-colors" required>
                            </td>
                            <td class="px-6 py-4">
                                <input type="text" name="description" value="<?php echo htmlspecialchars($cat['description']); ?>" class="w-full bg-black/50 border border-[var(--border-color)] text-[var(--c2)] font-cgr rounded px-2 py-1 focus:outline-none focus:border-[var(--c2)] transition-colors">
                            </td>
                            <td class="px-6 py-4 text-right font-am text-xs">
                                <div class="inline-flex gap-2 justify-end w-full">
                                    <button type="submit" name="action" value="edit" class="px-3 py-1 border border-green-600/50 text-green-500 hover:bg-green-600/20 rounded transition-colors uppercase">Save</button>
                                    <button type="submit" name="action" value="delete" class="px-3 py-1 border border-red-900/50 text-red-500 hover:bg-red-900/30 hover:text-red-400 rounded transition-colors uppercase" onclick="return confirm('Delete this category? This will delete all listings in it!');">Delete</button>
                                </div>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
