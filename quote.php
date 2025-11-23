<?php
// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/bootstrap/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/site.php';
require_once __DIR__ . '/includes/functions.php';

use App\Database\Connection;
use App\Domain\Catalog\ProductRepository;

$db = getDB();
$productRepo = new ProductRepository($db);
$productSlug = $_GET['product'] ?? null;
$selectedProduct = null;

if ($productSlug) {
    $selectedProduct = $productRepo->findBySlug((string) $productSlug);
}

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
            $_POST = [];
        } else {
            $error = 'Sorry, there was an error submitting your request. Please try again or contact us directly.';
        }
    }
}

$pageTitle = 'Request Quote';
$pageDescription = 'Get a free quote for warehouse and factory equipment';

$primaryColor = option('primary_color', '#0b3a63');
$secondaryColor = option('secondary_color', '#1a5a8a');
$accentColor = option('accent_color', '#fa4f26');

$contactEmail = option('contact_email', $siteConfig['contact']['email'] ?? '');
$contactPhone = option('contact_phone', $siteConfig['contact']['phone'] ?? '');

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="quote-hero">
    <div class="container mx-auto px-4 py-16">
            <div class="max-w-3xl mx-auto text-center animate-on-scroll"
                 data-animation="zoomIn">
                <h1 class="text-5xl md:text-6xl font-bold mb-6 text-reveal">Request a Quote</h1>
            <p class="text-xl md:text-2xl text-gray-200 max-w-2xl mx-auto">
                Get a personalized quote for your warehouse and factory equipment needs. Our team will respond within one business day.
            </p>
        </div>
    </div>
</section>

<!-- Quote Form -->
<section class="section-padding bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto">
            <?php if ($success): ?>
                <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-6 mb-8 animate-fade-in">
                    <div class="flex items-center gap-3">
                        <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-green-800 font-bold text-lg">Thank you! Your quote request has been submitted successfully.</p>
                            <p class="text-green-700 text-sm mt-1">Our team will reach out to you within one business day.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-6 mb-8 animate-fade-in">
                    <div class="flex items-center gap-3">
                        <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-red-800 font-semibold"><?php echo e($error); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="quote-form-card animate-on-scroll hover-glow"
                 data-animation="fadeInUp">
                <?php if ($selectedProduct): ?>
                    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-4 mb-8">
                        <div class="flex items-center gap-3">
                            <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-blue-800 font-semibold">Requesting quote for:</p>
                                <p class="text-blue-700 font-bold text-lg"><?php echo e($selectedProduct['name']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="companyName" class="form-label">Company Name *</label>
                            <input 
                                type="text" 
                                id="companyName" 
                                name="companyName" 
                                required
                                value="<?php echo e($_POST['companyName'] ?? ''); ?>"
                                class="form-input"
                                placeholder="Your Company Name"
                            >
                        </div>
                        <div>
                            <label for="contactName" class="form-label">Contact Name *</label>
                            <input 
                                type="text" 
                                id="contactName" 
                                name="contactName" 
                                required
                                value="<?php echo e($_POST['contactName'] ?? ''); ?>"
                                class="form-input"
                                placeholder="Your Full Name"
                            >
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label for="email" class="form-label">Email Address *</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required
                                value="<?php echo e($_POST['email'] ?? ''); ?>"
                                class="form-input"
                                placeholder="your.email@example.com"
                            >
                        </div>
                        <div>
                            <label for="phone" class="form-label">Phone Number</label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone"
                                value="<?php echo e($_POST['phone'] ?? ''); ?>"
                                class="form-input"
                                placeholder="+855 12 345 678"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="message" class="form-label">Project Details</label>
                        <textarea 
                            id="message" 
                            name="message" 
                            rows="6"
                            class="form-textarea"
                            placeholder="Tell us about your project: equipment needed, quantities, timeline, special requirements, etc."
                        ><?php echo e($_POST['message'] ?? ''); ?></textarea>
                        <p class="text-sm text-gray-500 mt-2">This information helps us provide you with the most accurate quote.</p>
                    </div>

                    <?php if ($productSlug): ?>
                        <input type="hidden" name="product" value="<?php echo e($productSlug); ?>">
                    <?php endif; ?>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <button 
                            type="submit" 
                            class="flex-1 btn-primary text-white font-semibold py-4 text-lg rounded-full shadow-lg hover:shadow-xl transition-all"
                            style="background-color: <?php echo e($primaryColor); ?>;"
                        >
                            Submit Quote Request
                        </button>
                        <a 
                            href="/products.php" 
                            class="px-8 py-4 text-center border-2 rounded-full font-semibold transition-all hover:bg-gray-50"
                            style="border-color: <?php echo e($primaryColor); ?>; color: <?php echo e($primaryColor); ?>;"
                        >
                            Browse Products
                        </a>
                    </div>
                </form>

                <div class="mt-8 pt-8 border-t border-gray-200">
                    <div class="text-center">
                        <p class="text-gray-600 mb-4">Prefer to contact us directly?</p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                            <?php if ($contactPhone): ?>
                                <a href="tel:<?php echo e(str_replace(' ', '', $contactPhone)); ?>" class="flex items-center gap-2 text-lg font-semibold hover:text-primary transition-colors" style="color: <?php echo e($primaryColor); ?>;">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                    <?php echo e($contactPhone); ?>
                                </a>
                            <?php endif; ?>
                            <?php if ($contactEmail): ?>
                                <span class="text-gray-400 hidden sm:inline">•</span>
                                <a href="mailto:<?php echo e($contactEmail); ?>" class="flex items-center gap-2 text-lg font-semibold hover:text-primary transition-colors" style="color: <?php echo e($primaryColor); ?>;">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <?php echo e($contactEmail); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="section-padding bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-5xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl md:text-5xl font-bold mb-4" style="color: <?php echo e($primaryColor); ?>;">
                    Why Request a Quote From Us?
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    We provide personalized service and competitive pricing for all your equipment needs
                </p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center animate-on-scroll float-animation"
                     data-animation="bounceIn">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4 shadow-lg pulse-animation" style="background: linear-gradient(135deg, <?php echo e($primaryColor); ?>, <?php echo e($secondaryColor); ?>);">
                        ⚡
                    </div>
                    <h3 class="text-xl font-bold mb-3" style="color: <?php echo e($primaryColor); ?>;">Fast Response</h3>
                    <p class="text-gray-600">We respond to all quote requests within one business day</p>
                </div>
                <div class="text-center animate-on-scroll">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4 shadow-lg" style="background: linear-gradient(135deg, <?php echo e($secondaryColor); ?>, <?php echo e($accentColor); ?>);">
                        💰
                    </div>
                    <h3 class="text-xl font-bold mb-3" style="color: <?php echo e($primaryColor); ?>;">Competitive Pricing</h3>
                    <p class="text-gray-600">Best prices in the market with flexible payment options</p>
                </div>
                <div class="text-center animate-on-scroll">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center text-white text-2xl font-bold mx-auto mb-4 shadow-lg" style="background: linear-gradient(135deg, <?php echo e($accentColor); ?>, <?php echo e($primaryColor); ?>);">
                        🎯
                    </div>
                    <h3 class="text-xl font-bold mb-3" style="color: <?php echo e($primaryColor); ?>;">Expert Advice</h3>
                    <p class="text-gray-600">Our specialists help you choose the right equipment</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
