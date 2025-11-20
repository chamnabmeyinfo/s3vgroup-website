<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/site.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = trim($_POST['companyName'] ?? '');
    $contactName = trim($_POST['contactName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $productSlug = $_POST['product'] ?? null;

    if (empty($companyName) || empty($contactName) || empty($email)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $quoteData = [
            'companyName' => $companyName,
            'contactName' => $contactName,
            'email' => $email,
            'phone' => $phone ?: null,
            'message' => $message ?: null,
            'items' => $productSlug ? [['product' => $productSlug]] : null,
        ];

        $quoteId = submitQuote($db, $quoteData);
        
        if ($quoteId) {
            $success = true;
            // Clear form
            $_POST = [];
        } else {
            $error = 'Sorry, there was an error submitting your request. Please try again or contact us directly.';
        }
    }
}

$pageTitle = 'Request Quote';
$pageDescription = 'Get a free quote for forklifts';

include __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold text-[#0b3a63] mb-4">Request a Quote</h1>
            <p class="text-gray-600">Fill out the form below and our team will get back to you within one business day.</p>
        </div>

        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                <p class="text-green-800 font-semibold">Thank you! Your quote request has been submitted successfully.</p>
                <p class="text-green-700 text-sm mt-2">Our team will reach out to you within one business day.</p>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                <p class="text-red-800"><?php echo e($error); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white rounded-lg border border-gray-200 shadow-lg p-6 space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="companyName" class="block text-sm font-medium text-gray-700 mb-2">
                        Company Name *
                    </label>
                    <input type="text" id="companyName" name="companyName" required
                           value="<?php echo e($_POST['companyName'] ?? ''); ?>"
                           class="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-[#0b3a63] focus:outline-none">
                </div>
                <div>
                    <label for="contactName" class="block text-sm font-medium text-gray-700 mb-2">
                        Contact Name *
                    </label>
                    <input type="text" id="contactName" name="contactName" required
                           value="<?php echo e($_POST['contactName'] ?? ''); ?>"
                           class="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-[#0b3a63] focus:outline-none">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email *
                    </label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo e($_POST['email'] ?? ''); ?>"
                           class="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-[#0b3a63] focus:outline-none">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Phone
                    </label>
                    <input type="tel" id="phone" name="phone"
                           value="<?php echo e($_POST['phone'] ?? ''); ?>"
                           class="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-[#0b3a63] focus:outline-none">
                </div>
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                    Project Details
                </label>
                <textarea id="message" name="message" rows="4"
                          class="w-full rounded-md border border-gray-300 px-4 py-2 focus:border-[#0b3a63] focus:outline-none"
                          placeholder="Volumes, locations, target go-live, equipment of interest..."><?php echo e($_POST['message'] ?? ''); ?></textarea>
            </div>

            <?php if (isset($_GET['product'])): ?>
                <input type="hidden" name="product" value="<?php echo e($_GET['product']); ?>">
            <?php endif; ?>

            <button type="submit" class="w-full px-6 py-3 bg-[#0b3a63] text-white rounded-md hover:bg-[#1a5a8a] transition-colors font-semibold">
                Send Request
            </button>
        </form>

        <div class="mt-8 text-center text-gray-600">
            <p>Or contact us directly:</p>
            <p class="mt-2">
                <a href="tel:<?php echo e($siteConfig['contact']['phone']); ?>" class="text-[#0b3a63] hover:underline">
                    <?php echo e($siteConfig['contact']['phone']); ?>
                </a>
                or
                <a href="mailto:<?php echo e($siteConfig['contact']['email']); ?>" class="text-[#0b3a63] hover:underline">
                    <?php echo e($siteConfig['contact']['email']); ?>
                </a>
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
