<?php
// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/bootstrap/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/site.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Contact Us';
$pageDescription = 'Get in touch with us for warehouse and factory equipment solutions';

$primaryColor = option('primary_color', '#0b3a63');
$secondaryColor = option('secondary_color', '#1a5a8a');
$accentColor = option('accent_color', '#fa4f26');

$contactEmail = option('contact_email', $siteConfig['contact']['email'] ?? '');
$contactPhone = option('contact_phone', $siteConfig['contact']['phone'] ?? '');
$contactAddress = option('contact_address', $siteConfig['contact']['address'] ?? '');
$businessHours = option('business_hours', $siteConfig['contact']['hours'] ?? '');

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="contact-hero">
    <div class="container mx-auto px-4">
            <div class="max-w-3xl mx-auto text-center animate-on-scroll"
                 data-animation="zoomIn">
                <h1 class="text-5xl md:text-6xl font-bold mb-6 text-reveal">Contact Us</h1>
            <p class="text-xl md:text-2xl text-gray-200 max-w-2xl mx-auto">
                We're here to help. Get in touch with our team for expert advice and support.
            </p>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="section-padding bg-white">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Contact Information -->
                <div class="space-y-8 animate-on-scroll">
                    <div>
                        <h2 class="text-3xl md:text-4xl font-bold mb-6" style="color: <?php echo e($primaryColor); ?>;">
                            Get in Touch
                        </h2>
                        <p class="text-lg text-gray-600 mb-8">
                            Reach out to us through any of the following channels. We're always happy to help!
                        </p>
                    </div>

                    <div class="space-y-6">
                        <?php if ($contactPhone): ?>
                            <div class="contact-info-card animate-on-scroll">
                                <div class="contact-icon">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold mb-2" style="color: <?php echo e($primaryColor); ?>;">Phone</h3>
                                <a href="tel:<?php echo e(str_replace(' ', '', $contactPhone)); ?>" class="text-xl font-bold text-gray-900 hover:text-primary transition-colors">
                                    <?php echo e($contactPhone); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if ($contactEmail): ?>
                            <div class="contact-info-card animate-on-scroll">
                                <div class="contact-icon">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold mb-2" style="color: <?php echo e($primaryColor); ?>;">Email</h3>
                                <a href="mailto:<?php echo e($contactEmail); ?>" class="text-xl font-bold text-gray-900 hover:text-primary transition-colors break-all">
                                    <?php echo e($contactEmail); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if ($contactAddress): ?>
                            <div class="contact-info-card animate-on-scroll">
                                <div class="contact-icon">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold mb-2" style="color: <?php echo e($primaryColor); ?>;">Address</h3>
                                <p class="text-lg text-gray-700 leading-relaxed"><?php echo nl2br(e($contactAddress)); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($businessHours): ?>
                            <div class="contact-info-card animate-on-scroll">
                                <div class="contact-icon">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold mb-2" style="color: <?php echo e($primaryColor); ?>;">Business Hours</h3>
                                <p class="text-lg text-gray-700 whitespace-pre-line"><?php echo e($businessHours); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Social Media Links -->
                    <?php
                    $facebookUrl = option('facebook_url', '');
                    $linkedinUrl = option('linkedin_url', '');
                    $twitterUrl = option('twitter_url', '');
                    $youtubeUrl = option('youtube_url', '');
                    ?>
                    <?php if ($facebookUrl || $linkedinUrl || $twitterUrl || $youtubeUrl): ?>
                        <div class="pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-semibold mb-4" style="color: <?php echo e($primaryColor); ?>;">Follow Us</h3>
                            <div class="flex gap-4">
                                <?php if ($facebookUrl): ?>
                                    <a href="<?php echo e($facebookUrl); ?>" target="_blank" rel="noopener noreferrer" class="w-12 h-12 rounded-full bg-gray-100 hover:bg-primary flex items-center justify-center transition-all hover:scale-110 transform" style="--hover-bg: <?php echo e($primaryColor); ?>;">
                                        <svg class="w-6 h-6 text-gray-700 hover:text-white transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                                <?php if ($linkedinUrl): ?>
                                    <a href="<?php echo e($linkedinUrl); ?>" target="_blank" rel="noopener noreferrer" class="w-12 h-12 rounded-full bg-gray-100 hover:bg-primary flex items-center justify-center transition-all hover:scale-110 transform">
                                        <svg class="w-6 h-6 text-gray-700 hover:text-white transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                                <?php if ($twitterUrl): ?>
                                    <a href="<?php echo e($twitterUrl); ?>" target="_blank" rel="noopener noreferrer" class="w-12 h-12 rounded-full bg-gray-100 hover:bg-primary flex items-center justify-center transition-all hover:scale-110 transform">
                                        <svg class="w-6 h-6 text-gray-700 hover:text-white transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                                <?php if ($youtubeUrl): ?>
                                    <a href="<?php echo e($youtubeUrl); ?>" target="_blank" rel="noopener noreferrer" class="w-12 h-12 rounded-full bg-gray-100 hover:bg-primary flex items-center justify-center transition-all hover:scale-110 transform">
                                        <svg class="w-6 h-6 text-gray-700 hover:text-white transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                        </svg>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Contact Form -->
                <div class="animate-on-scroll"
                     data-animation="slideInRight">
                    <div class="contact-form-card hover-glow">
                        <h2 class="text-3xl md:text-4xl font-bold mb-2" style="color: <?php echo e($primaryColor); ?>;">
                            Send Us a Message
                        </h2>
                        <p class="text-gray-600 mb-8">
                            Fill out the form below and we'll get back to you as soon as possible.
                        </p>

                        <form action="/quote.php" method="GET" class="space-y-6">
                            <div>
                                <label for="contact-name" class="form-label">Your Name *</label>
                                <input type="text" id="contact-name" name="contactName" required class="form-input" placeholder="John Doe">
                            </div>

                            <div>
                                <label for="contact-email" class="form-label">Email Address *</label>
                                <input type="email" id="contact-email" name="email" required class="form-input" placeholder="john@example.com">
                            </div>

                            <div>
                                <label for="contact-phone" class="form-label">Phone Number</label>
                                <input type="tel" id="contact-phone" name="phone" class="form-input" placeholder="+855 12 345 678">
                            </div>

                            <div>
                                <label for="contact-company" class="form-label">Company Name</label>
                                <input type="text" id="contact-company" name="companyName" class="form-input" placeholder="Your Company">
                            </div>

                            <div>
                                <label for="contact-message" class="form-label">Message *</label>
                                <textarea id="contact-message" name="message" rows="6" required class="form-textarea" placeholder="Tell us how we can help you..."></textarea>
                            </div>

                            <button type="submit" class="w-full btn-primary text-white font-semibold py-4 text-lg rounded-full shadow-lg hover:shadow-xl transition-all" style="background-color: <?php echo e($primaryColor); ?>;">
                                Send Message
                            </button>
                        </form>

                        <div class="mt-8 pt-8 border-t border-gray-200 text-center">
                            <p class="text-gray-600 mb-4">Or contact us directly:</p>
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
    </div>
</section>

<!-- Map Section (if you have a map) -->
<?php if ($contactAddress): ?>
    <section class="section-padding-sm bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto text-center">
                <h3 class="text-2xl font-bold mb-6" style="color: <?php echo e($primaryColor); ?>;">Visit Our Office</h3>
                <p class="text-lg text-gray-600 mb-8"><?php echo e($contactAddress); ?></p>
                <!-- Add Google Maps embed here if you have the address -->
                <div class="bg-gray-200 rounded-xl h-96 flex items-center justify-center text-gray-500">
                    <div class="text-center">
                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <p>Map integration available</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
