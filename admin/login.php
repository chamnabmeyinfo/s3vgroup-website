<?php
// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

startAdminSession();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === ADMIN_EMAIL && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $email;
        header('Location: /admin/');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /admin/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo $siteConfig['name']; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Admin Login</h1>
            <p class="text-sm text-gray-600 mt-2">Sign in to manage your website content</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-red-800 text-sm"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" id="email" name="email" required
                       class="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-[#0b3a63] focus:outline-none"
                       placeholder="admin@example.com">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" id="password" name="password" required
                       class="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-[#0b3a63] focus:outline-none"
                       placeholder="••••••••">
            </div>
            <button type="submit" class="w-full px-6 py-3 bg-[#0b3a63] text-white rounded-md hover:bg-[#1a5a8a] transition-colors font-semibold">
                Log in
            </button>
        </form>

        <p class="text-center text-xs text-gray-500 mt-6">
            Default: <?php echo ADMIN_EMAIL; ?> / <?php echo ADMIN_PASSWORD; ?>
        </p>
    </div>
</body>
</html>
