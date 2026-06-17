<?php
require_once 'includes/db.php';
require_once 'includes/auth_helpers.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] === 'banned') {
                $error = "Your account has been banned.";
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['surname'] = $user['surname'] ?? '';
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_picture'] = $user['profile_picture'] ?? null;
                
                if ($user['role'] === 'admin') {
                    header("Location: admin/index.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}

include 'includes/header.php';
?>

<div class="container mx-auto px-6 py-16 flex justify-center min-h-[70vh] items-center">
    <div class="w-full max-w-md">
        <div class="glass-panel p-10 rounded-[var(--radius)]">
            <h3 class="font-ds text-5xl uppercase tracking-normal text-center mb-8 text-white">Login</h3>
            
            <?php if ($error): ?>
                <div class="bg-red-900/30 border border-red-700/50 text-red-200 px-4 py-3 rounded mb-6 font-cgr">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <div>
                    <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Email</label>
                    <input type="email" name="email" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" required>
                </div>
                <div>
                    <label class="block font-am text-xs text-[var(--c2)] uppercase tracking-wider mb-2">Password</label>
                    <input type="password" name="password" class="w-full bg-[var(--c1)] border border-[var(--border-color)] text-white font-cgr rounded-[var(--radius)] px-4 py-3 focus:outline-none focus:border-[var(--c2)] transition-colors" required>
                </div>
                <button type="submit" class="w-full bg-white text-black font-cgr font-bold uppercase tracking-wider rounded-[var(--radius)] px-4 py-4 hover:bg-gray-200 transition-colors mt-4">
                    Login
                </button>
            </form>
            
            <div class="text-center mt-8 pt-6 border-t border-[var(--border-color)]">
                <a href="register.php" class="font-cgr text-[var(--c2)] hover:text-white transition-colors">Don't have an account? Register</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
