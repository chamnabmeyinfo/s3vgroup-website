/**
 * Professional Animations Library
 * Smooth scroll, reveal animations, parallax, and more
 */

(function() {
    'use strict';

    // ========================================
    // Smooth Scroll
    // ========================================
    if (typeof option !== 'undefined' && option('enable_smooth_scroll', '1') === '1') {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#' || href === '#!') return;
                
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    const offsetTop = target.offsetTop - 80; // Account for fixed header
                    
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    // ========================================
    // Reveal Animations on Scroll
    // ========================================
    function initRevealAnimations() {
        const elements = document.querySelectorAll('.animate-on-scroll, .fade-in-up, .fade-in-left, .fade-in-right, .zoom-in');
        
        if (elements.length === 0) return;

        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px',
        };

        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-revealed');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        elements.forEach(el => {
            revealObserver.observe(el);
        });
    }

    // ========================================
    // Parallax Scrolling
    // ========================================
    function initParallax() {
        if (typeof option === 'undefined' || option('enable_parallax', '0') !== '1') {
            return;
        }

        const parallaxElements = document.querySelectorAll('[data-parallax]');
        
        if (parallaxElements.length === 0) return;

        function updateParallax() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            parallaxElements.forEach(el => {
                const speed = parseFloat(el.dataset.parallax) || 0.5;
                const yPos = -(scrollTop * speed);
                el.style.transform = `translate3d(0, ${yPos}px, 0)`;
            });
        }

        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    updateParallax();
                    ticking = false;
                });
                ticking = true;
            }
        });

        updateParallax();
    }

    // ========================================
    // Loading Animation
    // ========================================
    // NOTE: Loading animation is now handled by loading-screen.js
    // This function is kept for backwards compatibility but does nothing
    function initLoadingAnimation() {
        // Loading screen is handled by dedicated loading-screen.js
        // This prevents conflicts and ensures fast loading
        return;
    }

    // ========================================
    // Counter Animation
    // ========================================
    function animateCounter(element, target, duration = 2000) {
        let current = 0;
        const increment = target / (duration / 16);
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target.toLocaleString();
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current).toLocaleString();
            }
        }, 16);
    }

    function initCounterAnimation() {
        const counters = document.querySelectorAll('[data-counter]');
        
        const observerOptions = {
            threshold: 0.5,
        };

        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = parseInt(entry.target.dataset.counter);
                    if (!isNaN(target)) {
                        animateCounter(entry.target, target);
                        counterObserver.unobserve(entry.target);
                    }
                }
            });
        }, observerOptions);

        counters.forEach(counter => {
            counterObserver.observe(counter);
        });
    }

    // ========================================
    // Progress Bar on Scroll
    // ========================================
    function initScrollProgress() {
        const progressBar = document.createElement('div');
        progressBar.id = 'scroll-progress';
        progressBar.className = 'fixed top-0 left-0 h-1 bg-[#0b3a63] z-50 transition-all duration-150';
        progressBar.style.width = '0%';
        document.body.appendChild(progressBar);

        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const progress = (scrollTop / scrollHeight) * 100;
            progressBar.style.width = progress + '%';
        });
    }

    // ========================================
    // Typing Animation
    // ========================================
    function initTypingAnimation() {
        const elements = document.querySelectorAll('[data-typing]');
        
        elements.forEach(el => {
            const text = el.textContent;
            const speed = parseInt(el.dataset.typingSpeed) || 50;
            el.textContent = '';
            
            let i = 0;
            const observerOptions = {
                threshold: 0.5,
            };

            const typingObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        function type() {
                            if (i < text.length) {
                                el.textContent += text.charAt(i);
                                i++;
                                setTimeout(type, speed);
                            }
                        }
                        type();
                        typingObserver.unobserve(el);
                    }
                });
            }, observerOptions);

            typingObserver.observe(el);
        });
    }

    // ========================================
    // Sticky Header on Scroll
    // ========================================
    function initStickyHeader() {
        const header = document.querySelector('header');
        if (!header) return;

        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 100) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            lastScroll = currentScroll;
        });
    }

    // Initialize all animations
    document.addEventListener('DOMContentLoaded', () => {
        initRevealAnimations();
        initParallax();
        initLoadingAnimation();
        initCounterAnimation();
        initScrollProgress();
        initTypingAnimation();
        initStickyHeader();
    });

    // CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        /* Reveal Animations */
        .animate-on-scroll,
        .fade-in-up,
        .fade-in-left,
        .fade-in-right,
        .zoom-in {
            opacity: 0;
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }

        .fade-in-up {
            transform: translateY(30px);
        }

        .fade-in-left {
            transform: translateX(-30px);
        }

        .fade-in-right {
            transform: translateX(30px);
        }

        .zoom-in {
            transform: scale(0.9);
        }

        .animate-on-scroll.animate-revealed,
        .fade-in-up.animate-revealed,
        .fade-in-left.animate-revealed,
        .fade-in-right.animate-revealed,
        .zoom-in.animate-revealed {
            opacity: 1;
            transform: translate(0, 0) scale(1);
        }

        /* Sticky Header */
        header.scrolled {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Loading Animation */
        #page-loader {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #ffffff;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease-out;
        }

        .loader-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color, #0b3a63);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Scroll Progress Bar */
        #scroll-progress {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Smooth Transitions */
        * {
            scroll-behavior: smooth;
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    `;
    document.head.appendChild(style);

})();

