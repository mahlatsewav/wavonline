<?php
require_once 'includes/db.php';
require_once 'includes/auth_helpers.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $role = ($_POST['role'] ?? 'buyer') === 'seller' ? 'seller' : 'buyer';

    if (empty($name) || empty($surname) || empty($email) || empty($password)) {
        $error = "Name, surname, email, and password are required.";
    } elseif ($role === 'seller' && empty($phone)) {
        $error = "Phone number is required for sellers.";
    } elseif ($role === 'seller' && empty($_FILES['profile_picture']['name'])) {
        $error = "Profile picture is required for sellers.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $profile_picture = null;
            
            // Handle profile picture upload
            if (!empty($_FILES['profile_picture']['name']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'assets/uploads/profiles/';
                if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                
                $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($file_extension, $allowed)) {
                    $new_filename = uniqid('profile_') . '.' . $file_extension;
                    $target_path = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {
                        $profile_picture = $new_filename;
                    }
                } else {
                    $error = "Invalid file type for profile picture. Allowed: JPG, PNG, WEBP.";
                }
            }

            if (!$error) {
                $stmt = $pdo->prepare("INSERT INTO users (name, surname, email, password_hash, phone, role, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $surname, $email, $hash, $phone, $role, $profile_picture])) {
                    $success = "Registration successful. You can now login.";
                } else {
                    $error = "Something went wrong. Please try again.";
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-6 py-16 flex justify-center min-h-[70vh] items-center">
    <div class="w-full max-w-md">
        <div class="glass-panel p-10 rounded-[var(--radius)]">
            <h3 class="font-ds text-5xl uppercase tracking-normal text-center mb-8 text-white">Register</h3>
            
            <?php if ($error): ?>
                <div class="bg-red-900/30 border border-red-700/50 text-red-200 px-4 py-3 rounded mb-6 font-cgr">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="bg-green-900/30 border border-green-700/50 text-green-200 px-4 py-3 rounded mb-6 font-cgr">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" class="space-y-5">
                <div>
                    <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Role</label>
                    <select name="role" id="role-select" onchange="toggleSellerFields()" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors appearance-none" required>
                        <option value="buyer">Buyer</option>
                        <option value="seller">Seller</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">First Name</label>
                        <input type="text" name="name" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" required>
                    </div>
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Surname</label>
                        <input type="text" name="surname" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" required>
                    </div>
                </div>
                <div>
                    <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Email</label>
                    <input type="email" name="email" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" required>
                </div>
                <div>
                    <label id="phone-label" class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Phone (Optional)</label>
                    <input type="text" name="phone" id="phone-input" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors">
                </div>
                <div>
                    <label id="profile-picture-label" class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Profile Picture (Optional)</label>
                    <input type="file" name="profile_picture" id="profile-picture-input" class="w-full text-white font-cgr file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-white file:text-black hover:file:bg-gray-200" accept=".jpg,.jpeg,.png,.webp">
                </div>
                <div>
                    <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Password</label>
                    <input type="password" name="password" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" required>
                </div>
                <button type="submit" class="w-full bg-[var(--primary-color)] text-black font-cgr font-bold uppercase tracking-wider rounded-[var(--radius)] px-4 py-4 hover:bg-white transition-colors mt-2">
                    Create Account
                </button>
            </form>
            
            <script>
                function toggleSellerFields() {
                    const role = document.getElementById('role-select').value;
                    const phoneInput = document.getElementById('phone-input');
                    const phoneLabel = document.getElementById('phone-label');
                    const profileInput = document.getElementById('profile-picture-input');
                    const profileLabel = document.getElementById('profile-picture-label');
                    
                    if (role === 'seller') {
                        phoneInput.required = true;
                        phoneLabel.innerHTML = 'Phone <span class="text-red-500">*</span>';
                        profileInput.required = true;
                        profileLabel.innerHTML = 'Profile Picture <span class="text-red-500">*</span>';
                    } else {
                        phoneInput.required = false;
                        phoneLabel.innerHTML = 'Phone (Optional)';
                        profileInput.required = false;
                        profileLabel.innerHTML = 'Profile Picture (Optional)';
                    }
                }
                
                // Initialise on load
                window.addEventListener('DOMContentLoaded', toggleSellerFields);
            </script>
            
            <div class="text-center mt-8 pt-6 border-t border-[var(--border-color)]">
                <a href="login.php" class="font-cgr text-[var(--c2)] hover:text-white transition-colors">Already have an account? Login</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
