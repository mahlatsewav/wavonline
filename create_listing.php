<?php
require_once 'includes/db.php';
require_once 'includes/auth_helpers.php';

requireLogin();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['seller', 'admin'])) {
    header("Location: profile.php");
    exit;
}

$error = '';
$success = '';

// Fetch categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? 0;
    $province = trim($_POST['province'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $suburb = trim($_POST['suburb'] ?? '');
    $condition = $_POST['condition'] ?? 'used';

    if (empty($title) || empty($category_id) || empty($description) || empty($price) || empty($province) || empty($city)) {
        $error = "All fields except images and suburb are required.";
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO listings (user_id, category_id, title, description, price, province, city, suburb, condition_state, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
            $stmt->execute([$_SESSION['user_id'], $category_id, $title, $description, $price, $province, $city, $suburb, $condition]);
            $listing_id = $pdo->lastInsertId();

            // Handle image upload
            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'assets/uploads/';
                if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($file_extension, $allowed)) {
                    $new_filename = uniqid('img_') . '.' . $file_extension;
                    $target_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                        $stmt_img = $pdo->prepare("INSERT INTO listing_images (listing_id, image_path) VALUES (?, ?)");
                        $stmt_img->execute([$listing_id, $new_filename]);
                    }
                }
            }
            
            $pdo->commit();
            $success = "Listing created successfully!";
            // Redirect after success message 
            header("Location: my_listings.php?success=created");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to create listing. " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-6 py-12 flex justify-center">
    <div class="w-full max-w-2xl">
        <div class="glass-panel p-8 md:p-12 rounded-[var(--radius)]">
            <h3 class="font-dh text-4xl uppercase tracking-normal mb-8 text-white border-b border-[var(--border-color)] pb-4">Create New Listing</h3>
            
            <?php if ($error): ?>
                <div class="bg-red-900/30 border border-red-700/50 text-red-200 px-4 py-3 rounded mb-6 font-cgr">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Title</label>
                    <input type="text" name="title" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" required>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Category</label>
                        <select name="category_id" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors appearance-none" required>
                            <option value="">Select a category...</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Condition</label>
                        <select name="condition" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors appearance-none" required>
                            <option value="used">Used</option>
                            <option value="new">New</option>
                        </select>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Price (R)</label>
                        <input type="number" step="0.01" min="0" name="price" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" required>
                    </div>
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Province</label>
                        <select name="province" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors appearance-none" required>
                            <option value="">Select a province...</option>
                            <option value="Eastern Cape">Eastern Cape</option>
                            <option value="Free State">Free State</option>
                            <option value="Gauteng">Gauteng</option>
                            <option value="KwaZulu-Natal">KwaZulu-Natal</option>
                            <option value="Limpopo">Limpopo</option>
                            <option value="Mpumalanga">Mpumalanga</option>
                            <option value="Northern Cape">Northern Cape</option>
                            <option value="North West">North West</option>
                            <option value="Western Cape">Western Cape</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">City</label>
                        <input type="text" name="city" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" placeholder="City (Required)" required>
                    </div>
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Suburb/Area (Optional)</label>
                        <input type="text" name="suburb" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" placeholder="Suburb/Area">
                    </div>
                </div>

                <div>
                    <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Description</label>
                    <textarea name="description" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors h-32" required></textarea>
                </div>

                <div>
                    <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Main Image (Optional)</label>
                    <div class="relative w-full bg-[var(--c1)] border border-[var(--border-color)] rounded-[var(--radius)] px-4 py-3 flex items-center">
                        <input type="file" name="image" class="w-full text-white font-cgr file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-white file:text-black hover:file:bg-gray-200" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                    <small class="block mt-2 font-am text-xs text-gray-500">Max size: 2MB. Allowed formats: JPG, PNG, WEBP.</small>
                </div>

                <button type="submit" class="w-full bg-[var(--primary-color)] text-black font-cgr font-bold uppercase tracking-wider rounded-[var(--radius)] px-4 py-4 hover:bg-white transition-colors mt-8">
                    Publish Listing
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
