    </main>

    <?php
    require_once __DIR__ . '/../bootstrap/app.php';
    $footerBg = option('footer_background', '#0b3a63');
    $siteName = option('site_name', $siteConfig['name'] ?? 'S3V Group');
    $siteLogo = option('site_logo', '');
    $contactEmail = option('contact_email', $siteConfig['contact']['email'] ?? '');
    $contactPhone = option('contact_phone', $siteConfig['contact']['phone'] ?? '');
    $contactAddress = option('contact_address', $siteConfig['contact']['address'] ?? '');
    $businessHours = option('business_hours', $siteConfig['contact']['hours'] ?? '');
    $copyright = option('footer_copyright', '© ' . date('Y') . ' ' . $siteName . '. All rights reserved.');
    $facebookUrl = option('facebook_url', '');
    $linkedinUrl = option('linkedin_url', '');
    $twitterUrl = option('twitter_url', '');
    $youtubeUrl = option('youtube_url', '');
    ?>
    <footer class="text-white mt-20" style="background-color: <?php echo e($footerBg); ?>;">
        <div class="container mx-auto px-4 py-12">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <?php if ($siteLogo): ?>
                            <img src="<?php echo e($siteLogo); ?>" alt="<?php echo e($siteName); ?>" class="h-8 w-auto">
                        <?php else: ?>
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        <?php endif; ?>
                        <span class="text-xl font-bold"><?php echo e($siteName); ?></span>
                    </div>
                    <p class="text-gray-300 text-sm">
                        <?php echo e($siteConfig['description'] ?? 'Leading supplier of warehouse and factory equipment.'); ?>
                    </p>
                    <?php if ($facebookUrl || $linkedinUrl || $twitterUrl || $youtubeUrl): ?>
                        <div class="flex gap-3 mt-4">
                            <?php if ($facebookUrl): ?>
                                <a href="<?php echo e($facebookUrl); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-white transition-colors">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                </a>
                            <?php endif; ?>
                            <?php if ($linkedinUrl): ?>
                                <a href="<?php echo e($linkedinUrl); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-white transition-colors">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                </a>
                            <?php endif; ?>
                            <?php if ($twitterUrl): ?>
                                <a href="<?php echo e($twitterUrl); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-white transition-colors">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                                </a>
                            <?php endif; ?>
                            <?php if ($youtubeUrl): ?>
                                <a href="<?php echo e($youtubeUrl); ?>" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-white transition-colors">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <h3 class="font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <li><a href="<?php echo base_url('products.php'); ?>" class="hover:text-white transition-colors">Browse Products</a></li>
                        <li><a href="<?php echo base_url('about.php'); ?>" class="hover:text-white transition-colors">About Us</a></li>
                        <li><a href="<?php echo base_url('team.php'); ?>" class="hover:text-white transition-colors">Our Team</a></li>
                        <li><a href="<?php echo base_url('quote.php'); ?>" class="hover:text-white transition-colors">Request Quote</a></li>
                        <li><a href="<?php echo base_url('contact.php'); ?>" class="hover:text-white transition-colors">Contact Us</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold mb-4">Contact</h3>
                    <ul class="space-y-2 text-sm text-gray-300">
                        <?php if ($contactPhone): ?>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <?php echo e($contactPhone); ?>
                            </li>
                        <?php endif; ?>
                        <?php if ($contactEmail): ?>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <a href="mailto:<?php echo e($contactEmail); ?>"><?php echo e($contactEmail); ?></a>
                            </li>
                        <?php endif; ?>
                        <?php if ($contactAddress): ?>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <?php echo e($contactAddress); ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>

                <?php if ($businessHours): ?>
                    <div>
                        <h3 class="font-semibold mb-4">Business Hours</h3>
                        <p class="text-sm text-gray-300"><?php echo e($businessHours); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="border-t border-white/20 mt-8 pt-8">
                <?php if (option('enable_newsletter', '1') === '1'): ?>
                    <div class="mb-8">
                        <?php include __DIR__ . '/widgets/newsletter-signup.php'; ?>
                    </div>
                <?php endif; ?>
                <div class="text-center text-sm text-gray-300">
                    <p><?php echo e($copyright); ?></p>
                </div>
            </div>
        </div>
    </footer>
    
    <?php include __DIR__ . '/widgets/loading-screen.php'; ?>
    <?php
    // Use AssetVersion for proper cache busting (better than time())
    $assetVersion = class_exists('App\Support\AssetVersion') ? \App\Support\AssetVersion::get() : date('Ymd');
    ?>
    <!-- Loading screen script - NOT deferred, must run immediately -->
    <script src="<?php echo asset('includes/js/loading-screen.js'); ?>?v=<?php echo $assetVersion; ?>"></script>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            if (menu) {
                menu.classList.toggle('hidden');
            }
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobile-menu');
            const button = event.target.closest('[onclick="toggleMobileMenu()"]');
            if (menu && !menu.contains(event.target) && !button) {
                menu.classList.add('hidden');
            }
        });
        
        // Header scroll effect
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (header) {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                    header.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)';
                } else {
                    header.classList.remove('scrolled');
                    header.style.boxShadow = '0 1px 2px 0 rgba(0, 0, 0, 0.05)';
                }
            }
        });
    </script>
    
    <?php if ($customJSFooter = option('custom_js_footer', '')): ?>
        <script><?php echo $customJSFooter; ?></script>
    <?php endif; ?>
</body>
</html>
