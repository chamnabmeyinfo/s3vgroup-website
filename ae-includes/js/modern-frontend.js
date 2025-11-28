/**
 * Modern Frontend JavaScript
 * Handles all interactive features for the modern design
 */

(function() {
    'use strict';

    // ============================================
    // Mobile Menu Toggle
    // ============================================
    function initMobileMenu() {
        const mobileMenuBtn = document.querySelector('.modern-mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (!mobileMenuBtn || !mobileMenu) return;

        function toggleMenu() {
            mobileMenu.classList.toggle('hidden');
            document.body.style.overflow = mobileMenu.classList.contains('hidden') ? '' : 'hidden';
            
            // Update button icon
            const svg = mobileMenuBtn.querySelector('svg');
            if (svg) {
                if (mobileMenu.classList.contains('hidden')) {
                    svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />';
                } else {
                    svg.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />';
                }
            }
        }

        // Toggle on button click
        mobileMenuBtn.addEventListener('click', toggleMenu);

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                if (!mobileMenu.classList.contains('hidden')) {
                    toggleMenu();
                }
            }
        });

        // Close on window resize (if resizing to desktop)
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth >= 1024 && !mobileMenu.classList.contains('hidden')) {
                    toggleMenu();
                }
            }, 250);
        });
    }

    // ============================================
    // Header Scroll Effect
    // ============================================
    function initHeaderScroll() {
        const header = document.getElementById('modern-header');
        if (!header) return;

        let lastScroll = 0;
        window.addEventListener('scroll', function() {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
        });
    }

    // ============================================
    // Search Functionality
    // ============================================
    function initSearch() {
        const searchInputs = document.querySelectorAll('#product-search, #mobile-product-search');
        
        searchInputs.forEach(input => {
            if (!input) return;

            let searchTimeout;
            input.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                if (query.length < 2) {
                    // Hide search results if query is too short
                    return;
                }

                searchTimeout = setTimeout(() => {
                    // Perform search (integrate with your search API)
                    performSearch(query);
                }, 300);
            });

            // Handle Enter key
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const query = this.value.trim();
                    if (query) {
                        window.location.href = `/products.php?search=${encodeURIComponent(query)}`;
                    }
                }
            });
        });
    }

    function performSearch(query) {
        // This would integrate with your search API
        // For now, just log the query
        console.log('Searching for:', query);
    }

    // ============================================
    // Animate on Scroll - Enhanced with Stagger
    // ============================================
    function initScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -80px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    // Add staggered delay based on index
                    const delay = index % 6 * 100;
                    setTimeout(() => {
                        entry.target.classList.add('animated');
                    }, delay);
                    
                    // Unobserve after animation for performance
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe all elements with animation class
        document.querySelectorAll('.modern-animate-on-scroll').forEach((el, index) => {
            // Add data attribute for stagger effect
            el.setAttribute('data-animation-index', index);
            observer.observe(el);
        });
        
                    // Add subtle parallax effect to hero section
        const hero = document.querySelector('.modern-hero');
        if (hero) {
            let ticking = false;
            window.addEventListener('scroll', function() {
                if (!ticking) {
                    window.requestAnimationFrame(function() {
                        const scrolled = window.pageYOffset;
                        if (scrolled < hero.offsetHeight) {
                            const rate = scrolled * 0.3;
                            hero.style.transform = `translateY(${rate}px)`;
                        }
                        ticking = false;
                    });
                    ticking = true;
                }
            });
        }
    }

    // ============================================
    // Smooth Scroll for Anchor Links
    // ============================================
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#' || href.length <= 1) return;

                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    const headerOffset = 80;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });

                    // Close mobile menu if open
                    const mobileMenu = document.getElementById('mobile-menu');
                    if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                        document.body.style.overflow = '';
                    }
                }
            });
        });
    }

    // ============================================
    // Card Hover Effects Enhancement
    // ============================================
    function initCardEffects() {
        const cards = document.querySelectorAll('.modern-card');
        
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    }

    // ============================================
    // Button Ripple Effect
    // ============================================
    function initButtonRipples() {
        const buttons = document.querySelectorAll('.modern-btn, .modern-cta-button, .modern-hero-button-primary, .modern-hero-button-secondary');
        
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
    }

    // ============================================
    // Initialize Everything
    // ============================================
    function init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initMobileMenu();
                initHeaderScroll();
                initSearch();
                initScrollAnimations();
                initSmoothScroll();
                initCardEffects();
                initButtonRipples();
            });
        } else {
            // DOM already loaded
            initMobileMenu();
            initHeaderScroll();
            initSearch();
            initScrollAnimations();
            initSmoothScroll();
            initCardEffects();
            initButtonRipples();
        }
    }

    // Start initialization
    init();

    // Make toggleMobileMenu available globally for inline onclick handlers
    window.toggleMobileMenu = function() {
        const mobileMenuBtn = document.querySelector('.modern-mobile-menu-btn');
        if (mobileMenuBtn) {
            mobileMenuBtn.click();
        }
    };

})();

