<?php
require_once 'includes/db.php';
require_once 'includes/auth_helpers.php';

requireLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'switch_role') {
        $new_role = $_POST['new_role'] ?? '';
        if (in_array($new_role, ['buyer', 'seller'])) {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            if ($stmt->execute([$new_role, $_SESSION['user_id']])) {
                $_SESSION['role'] = $new_role;
                $success = "Role updated to " . ucfirst($new_role) . " successfully.";
            } else {
                $error = "Failed to update role.";
            }
        }
    } else {
        $name = trim($_POST['name'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name) || empty($surname)) {
            $error = "Name and surname cannot be empty.";
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, surname = ?, phone = ? WHERE id = ?");
            if ($stmt->execute([$name, $surname, $phone, $_SESSION['user_id']])) {
                $_SESSION['name'] = $name;
                $_SESSION['surname'] = $surname;
                $success = "Profile updated successfully.";
            } else {
                $error = "Failed to update profile.";
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

include 'includes/header.php';
?>

<div class="container mx-auto px-6 py-16 flex justify-center min-h-[70vh]">
    <div class="w-full max-w-2xl">
        <div class="glass-panel p-8 md:p-12 rounded-[var(--radius)]">
            <h3 class="font-dh text-4xl uppercase tracking-normal mb-8 text-white border-b border-[var(--border-color)] pb-4">My Profile</h3>
            
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

            <form method="POST" action="" class="space-y-6">
                <div>
                    <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Email</label>
                    <input type="email" class="w-full bg-black/50 border border-[var(--border-color)] text-[var(--c2)] font-cgr rounded-[var(--radius)] px-4 py-3 cursor-not-allowed" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    <small class="block mt-2 font-am text-[10px] text-[var(--c3)] uppercase tracking-widest">Email cannot be changed.</small>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">First Name</label>
                        <input type="text" name="name" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    </div>
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Surname</label>
                        <input type="text" name="surname" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" value="<?php echo htmlspecialchars($user['surname']); ?>" required>
                    </div>
                </div>
                
                <div>
                    <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Phone</label>
                    <input type="text" name="phone" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-[var(--border-color)] mt-8">
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Role</label>
                        <input type="text" class="w-full bg-black/50 border border-[var(--border-color)] text-[var(--c2)] font-cgr rounded-[var(--radius)] px-4 py-3 cursor-not-allowed" value="<?php echo ucfirst(htmlspecialchars($user['role'])); ?>" disabled>
                    </div>
                    <div>
                        <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Member Since</label>
                        <input type="text" class="w-full bg-black/50 border border-[var(--border-color)] text-[var(--c2)] font-cgr rounded-[var(--radius)] px-4 py-3 cursor-not-allowed" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" disabled>
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-[var(--primary-color)] text-black font-cgr font-bold uppercase tracking-wider rounded-[var(--radius)] px-4 py-4 hover:bg-white transition-colors mt-8">
                    Update Profile
                </button>
            </form>

            <?php if (in_array($user['role'], ['buyer', 'seller'])): ?>
            <div class="mt-12 pt-8 border-t border-[var(--border-color)]">
                <h4 class="font-dh text-2xl uppercase tracking-normal mb-4 text-white">Switch Role</h4>
                <p class="font-cgr text-[var(--c2)] mb-6">
                    You are currently a <strong><?php echo ucfirst(htmlspecialchars($user['role'])); ?></strong>. 
                    <?php if ($user['role'] === 'buyer'): ?>
                        Want to start selling your items? Switch to a Seller account.
                    <?php else: ?>
                        Done selling? Switch back to a Buyer account.
                    <?php endif; ?>
                </p>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="switch_role">
                    <input type="hidden" name="new_role" value="<?php echo $user['role'] === 'buyer' ? 'seller' : 'buyer'; ?>">
                    <button type="submit" class="bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr font-bold uppercase tracking-wider rounded-[var(--radius)] px-6 py-3 hover:bg-[var(--c3)] hover:text-white transition-colors">
                        Switch to <?php echo $user['role'] === 'buyer' ? 'Seller' : 'Buyer'; ?>
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
