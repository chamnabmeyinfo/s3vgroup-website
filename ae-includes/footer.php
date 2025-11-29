    </main>

    <?php
    // Define helper functions first - before anything else
    if (!function_exists('e')) {
        function e($string) {
            if ($string === null || $string === false) return '';
            return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
        }
    }
    
    // Safe loading with error handling
    try {
        if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
            require_once __DIR__ . '/../bootstrap/app.php';
        }
        if (!function_exists('fullImageUrl')) {
            if (file_exists(__DIR__ . '/functions.php')) {
                require_once __DIR__ . '/functions.php';
            }
        }
        
        // Safe option() calls with fallbacks
        $primaryColor = '#086D3B';
        if (function_exists('option')) {
            try {
                $primaryColor = option('primary_color', '#086D3B');
                if (empty($primaryColor)) $primaryColor = '#086D3B';
            } catch (Exception $e) {
                error_log('Footer: Could not get primary_color: ' . $e->getMessage());
            }
        }
        
        $footerBg = $primaryColor;
        if (function_exists('option')) {
            try {
                $footerBg = option('footer_background', $primaryColor);
                if (empty($footerBg)) $footerBg = $primaryColor;
            } catch (Exception $e) {
                error_log('Footer: Could not get footer_background: ' . $e->getMessage());
            }
        }
        
        $siteName = 'S3V Group';
        if (function_exists('option')) {
            try {
                $siteName = option('site_name', 'S3V Group');
                if (empty($siteName)) $siteName = 'S3V Group';
            } catch (Exception $e) {
                error_log('Footer: Could not get site_name: ' . $e->getMessage());
            }
        }
        
        if (empty($siteName) && isset($siteConfig['name'])) {
            $siteName = $siteConfig['name'];
        }
        
        // Safe helper function for option() calls
        $safeOption = function($key, $default = '') use (&$error) {
            if (!function_exists('option')) return $default;
            try {
                $value = option($key, $default);
                return $value !== null ? $value : $default;
            } catch (Exception $e) {
                if (!isset($error)) $error = [];
                $error[] = "Failed to get option '$key': " . $e->getMessage();
                return $default;
            } catch (Error $e) {
                if (!isset($error)) $error = [];
                $error[] = "Failed to get option '$key': " . $e->getMessage();
                return $default;
            }
        };
        
        $siteLogo = $safeOption('site_logo', '');
        $siteLogoUrl = $siteLogo && function_exists('fullImageUrl') ? fullImageUrl($siteLogo) : '';
        $contactEmail = $safeOption('contact_email', $siteConfig['contact']['email'] ?? '');
        $contactPhone = $safeOption('contact_phone', $siteConfig['contact']['phone'] ?? '');
        $contactAddress = $safeOption('contact_address', $siteConfig['contact']['address'] ?? '');
        $businessHours = $safeOption('business_hours', $siteConfig['contact']['hours'] ?? '');
        $copyright = $safeOption('footer_copyright', '© ' . date('Y') . ' ' . $siteName . '. All rights reserved.');
        $facebookUrl = $safeOption('facebook_url', '');
        $linkedinUrl = $safeOption('linkedin_url', '');
        $twitterUrl = $safeOption('twitter_url', '');
        $youtubeUrl = $safeOption('youtube_url', '');
        
    } catch (Throwable $e) {
        error_log('Footer initialization error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        // Set safe defaults
        $primaryColor = '#086D3B';
        $footerBg = '#086D3B';
        $siteName = 'S3V Group';
        $siteLogo = '';
        $siteLogoUrl = '';
        $contactEmail = '';
        $contactPhone = '';
        $contactAddress = '';
        $businessHours = '';
        $copyright = '© ' . date('Y') . ' ' . $siteName . '. All rights reserved.';
        $facebookUrl = '';
        $linkedinUrl = '';
        $twitterUrl = '';
        $youtubeUrl = '';
    }
    
    // Safe helper function for option() calls (outside try-catch for later use)
    if (!function_exists('safe_option')) {
        function safe_option($key, $default = '') {
            if (!function_exists('option')) return $default;
            try {
                $value = option($key, $default);
                return $value !== null ? $value : $default;
            } catch (Throwable $e) {
                return $default;
            }
        }
    }
    ?>
    <footer class="modern-footer">
        <div class="modern-footer-container">
            <div class="modern-footer-brand">
                <div class="modern-footer-logo">
                    <?php if ($siteLogoUrl): ?>
                        <img src="<?php echo e($siteLogoUrl); ?>" alt="<?php echo e($siteName); ?>" class="modern-footer-logo-image">
                    <?php else: ?>
                        <div class="modern-footer-logo-text">
                            <span class="modern-footer-logo-text-top">GLOBAL</span>
                            <span class="modern-footer-logo-text-bottom">INDUSTRIAL SOLUTIONS</span>
                        </div>
                    <?php endif; ?>
                </div>
                <p class="modern-footer-description">
                    <?php echo e($siteConfig['description'] ?? 'Professional warehouse equipment solutions for your business needs.'); ?>
                </p>
                <?php if ($facebookUrl || $linkedinUrl || $twitterUrl || $youtubeUrl): ?>
                    <div class="modern-footer-social">
                        <?php if ($facebookUrl): ?>
                            <a href="<?php echo e($facebookUrl); ?>" target="_blank" rel="noopener noreferrer" class="modern-footer-social-link">
                                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php if ($linkedinUrl): ?>
                            <a href="<?php echo e($linkedinUrl); ?>" target="_blank" rel="noopener noreferrer" class="modern-footer-social-link">
                                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php if ($twitterUrl): ?>
                            <a href="<?php echo e($twitterUrl); ?>" target="_blank" rel="noopener noreferrer" class="modern-footer-social-link">
                                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
                            </a>
                        <?php endif; ?>
                        <?php if ($youtubeUrl): ?>
                            <a href="<?php echo e($youtubeUrl); ?>" target="_blank" rel="noopener noreferrer" class="modern-footer-social-link">
                                <svg fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <h4 class="modern-footer-title">Quick Links</h4>
                <div class="modern-footer-links">
                    <a href="<?php echo base_url('products.php'); ?>" class="modern-footer-link">
                        <span>Products</span>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="<?php echo base_url('about.php'); ?>" class="modern-footer-link">
                        <span>About Us</span>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="<?php echo base_url('team.php'); ?>" class="modern-footer-link">
                        <span>Our Team</span>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="<?php echo base_url('quote.php'); ?>" class="modern-footer-link">
                        <span>Get Quote</span>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="<?php echo base_url('contact.php'); ?>" class="modern-footer-link">
                        <span>Contact</span>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="modern-footer-title">Contact Info</h4>
                <div class="modern-footer-contact">
                    <?php if ($contactPhone): ?>
                        <div class="modern-footer-contact-item">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span><?php echo e($contactPhone); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($contactEmail): ?>
                        <div class="modern-footer-contact-item">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span><?php echo e($contactEmail); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($contactAddress): ?>
                        <div class="modern-footer-contact-item">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span><?php echo e($contactAddress); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($businessHours): ?>
                        <div class="modern-footer-contact-item">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span><?php echo e($businessHours); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="modern-footer-bottom">
            <?php if ($copyright): ?>
                <div class="modern-footer-copyright">
                    <p><?php echo e($copyright); ?></p>
                </div>
            <?php endif; ?>
            <div class="modern-footer-bottom-links">
                <a href="<?php echo base_url('about.php'); ?>" class="modern-footer-bottom-link">Privacy Policy</a>
                <a href="<?php echo base_url('about.php'); ?>" class="modern-footer-bottom-link">Terms of Service</a>
            </div>
        </div>
    </footer>
    
    <?php include __DIR__ . '/widgets/loading-screen.php'; ?>
    <?php
    // Use AssetVersion for proper cache busting (better than time())
    $assetVersion = class_exists('App\Support\AssetVersion') ? \App\Support\AssetVersion::get() : date('Ymd');
    ?>
    <!-- Loading screen script - NOT deferred, must run immediately -->
    <script src="<?php echo asset('ae-includes/js/loading-screen.js'); ?>?v=<?php echo $assetVersion; ?>"></script>

    <script>
        // Mobile Menu Toggle Function
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const button = document.querySelector('[onclick="toggleMobileMenu()"]');
            if (menu) {
                menu.classList.toggle('hidden');
                
                // Update button icon (hamburger to X)
                if (button) {
                    const svg = button.querySelector('svg');
                    if (svg && !menu.classList.contains('hidden')) {
                        // Change to X icon
                        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />';
                    } else if (svg) {
                        // Change back to hamburger
                        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
                    }
                }
                
                // Prevent body scroll when menu is open
                if (!menu.classList.contains('hidden')) {
                    document.body.classList.add('menu-open');
                    // Store scroll position
                    const scrollY = window.scrollY;
                    document.body.style.position = 'fixed';
                    document.body.style.top = `-${scrollY}px`;
                    document.body.style.width = '100%';
                } else {
                    // Restore scroll position
                    const scrollY = document.body.style.top;
                    document.body.classList.remove('menu-open');
                    document.body.style.position = '';
                    document.body.style.top = '';
                    document.body.style.width = '';
                    if (scrollY) {
                        window.scrollTo(0, parseInt(scrollY || '0') * -1);
                    }
                }
            }
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobile-menu');
            const button = event.target.closest('[onclick="toggleMobileMenu()"]');
            const menuContainer = menu ? menu.closest('header') : null;
            
            if (menu && !menu.contains(event.target) && !button && menuContainer && !menuContainer.contains(event.target)) {
                menu.classList.add('hidden');
                // Restore scroll position
                const scrollY = document.body.style.top;
                document.body.classList.remove('menu-open');
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                if (scrollY) {
                    window.scrollTo(0, parseInt(scrollY || '0') * -1);
                }
                
                // Reset button icon
                const menuButton = document.querySelector('[onclick="toggleMobileMenu()"]');
                if (menuButton) {
                    const svg = menuButton.querySelector('svg');
                    if (svg) {
                        svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
                    }
                }
            }
        });
        
        // Close mobile menu on window resize (if resizing to desktop)
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                const menu = document.getElementById('mobile-menu');
                if (window.innerWidth >= 768 && menu && !menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                    // Restore scroll position
                    const scrollY = document.body.style.top;
                    document.body.classList.remove('menu-open');
                    document.body.style.position = '';
                    document.body.style.top = '';
                    document.body.style.width = '';
                    if (scrollY) {
                        window.scrollTo(0, parseInt(scrollY || '0') * -1);
                    }
                }
            }, 250);
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
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#' && href.length > 1) {
                    const target = document.querySelector(href);
                    if (target) {
                        e.preventDefault();
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        // Close mobile menu if open
                        const menu = document.getElementById('mobile-menu');
                        if (menu && !menu.classList.contains('hidden')) {
                            toggleMobileMenu();
                        }
                    }
                }
            });
        });
    </script>
    
    <?php if ($customJSFooter = option('custom_js_footer', '')): ?>
        <script><?php echo $customJSFooter; ?></script>
    <?php endif; ?>
    
    <!-- Mobile Bottom Navigation - Disabled for production stability -->
    <?php 
    // Completely disabled to prevent any errors
    // Re-enable after confirming production is stable
    ?>
</body>
</html>
