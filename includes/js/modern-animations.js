/**
 * Modern Animation System
 * Advanced scroll-triggered animations, parallax, and micro-interactions
 */

(function() {
    'use strict';

    // Modern Easing Functions
    const easings = {
        easeInOutCubic: 'cubic-bezier(0.16, 1, 0.3, 1)',
        easeOutExpo: 'cubic-bezier(0.16, 1, 0.3, 1)',
        easeInOutBack: 'cubic-bezier(0.68, -0.55, 0.265, 1.55)',
        spring: 'cubic-bezier(0.34, 1.56, 0.64, 1)',
    };

    class ModernAnimations {
        constructor() {
            this.observers = new Map();
            this.parallaxElements = [];
            this.animatedCounters = [];
            this.init();
        }

        init() {
            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                this.initScrollAnimations();
                this.initParallax();
                this.initCounterAnimations();
                this.initMagneticButtons();
                this.initTextReveal();
                this.initHoverEffects();
                this.initImageAnimations();
            }
        }

        // Modern Scroll-Triggered Animations using Intersection Observer
        initScrollAnimations() {
            const observerOptions = {
                root: null,
                rootMargin: '0px 0px -50px 0px',
                threshold: 0.1,
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        const element = entry.target;
                        const animationType = element.dataset.animation || 'fadeInUp';
                        const delay = parseFloat(element.dataset.delay || 0) + (index * 0.1);
                        
                        // Add animation class
                        element.classList.add('animate-' + animationType);
                        element.style.animationDelay = delay + 's';
                        
                        // Mark as animated
                        element.classList.add('animated');
                        
                        // Unobserve after animation
                        setTimeout(() => {
                            observer.unobserve(element);
                        }, 1000);
                    }
                });
            }, observerOptions);

            // Observe all elements with animate-on-scroll
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });

            // Observe elements with specific animation attributes
            document.querySelectorAll('[data-animation]').forEach(el => {
                if (!el.classList.contains('animated')) {
                    observer.observe(el);
                }
            });

            // Stagger children animation
            document.querySelectorAll('.stagger-children').forEach(parent => {
                const children = Array.from(parent.children);
                children.forEach((child, index) => {
                    child.style.animationDelay = (index * 0.1) + 's';
                    observer.observe(child);
                });
            });
        }

        // Parallax Effect
        initParallax() {
            const parallaxElements = document.querySelectorAll('.parallax, .parallax-slow');
            
            if (parallaxElements.length === 0) return;

            let ticking = false;

            const updateParallax = () => {
                const scrollY = window.pageYOffset || window.scrollY;
                
                parallaxElements.forEach(element => {
                    const rect = element.getBoundingClientRect();
                    const speed = element.classList.contains('parallax-slow') ? 0.2 : 0.5;
                    const offset = (window.innerHeight - rect.top) * speed;
                    
                    element.style.transform = `translateY(${offset}px)`;
                });

                ticking = false;
            };

            const onScroll = () => {
                if (!ticking) {
                    window.requestAnimationFrame(updateParallax);
                    ticking = true;
                }
            };

            window.addEventListener('scroll', onScroll, { passive: true });
            this.parallaxElements = parallaxElements;
        }

        // Counter Animation
        initCounterAnimations() {
            const counters = document.querySelectorAll('.counter-animate, [data-count]');
            
            const animateCounter = (element) => {
                const target = parseInt(element.dataset.count || element.textContent.replace(/[^0-9]/g, ''));
                const duration = parseInt(element.dataset.duration || 2000);
                const increment = target / (duration / 16);
                let current = 0;
                
                const updateCounter = () => {
                    current += increment;
                    if (current < target) {
                        element.textContent = Math.floor(current).toLocaleString();
                        requestAnimationFrame(updateCounter);
                    } else {
                        element.textContent = target.toLocaleString();
                        element.classList.add('active');
                    }
                };

                updateCounter();
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                        entry.target.classList.add('counted');
                        animateCounter(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            counters.forEach(counter => {
                if (!counter.classList.contains('counted')) {
                    observer.observe(counter);
                }
            });
        }

        // Magnetic Button Effect
        initMagneticButtons() {
            const magneticElements = document.querySelectorAll('.magnetic, .btn-animate');
            
            magneticElements.forEach(element => {
                element.addEventListener('mousemove', (e) => {
                    const rect = element.getBoundingClientRect();
                    const x = e.clientX - rect.left - rect.width / 2;
                    const y = e.clientY - rect.top - rect.height / 2;
                    
                    const moveX = x * 0.15;
                    const moveY = y * 0.15;
                    
                    element.style.transform = `translate(${moveX}px, ${moveY}px)`;
                });

                element.addEventListener('mouseleave', () => {
                    element.style.transform = 'translate(0, 0)';
                });
            });
        }

        // Text Reveal Animation
        initTextReveal() {
            const textElements = document.querySelectorAll('.text-reveal');
            
            textElements.forEach(element => {
                const text = element.textContent;
                const words = text.split(' ');
                
                element.innerHTML = words.map((word, index) => 
                    `<span style="animation-delay: ${index * 0.1}s">${word}</span>`
                ).join(' ');
            });
        }

        // Enhanced Hover Effects
        initHoverEffects() {
            // Add hover-lift to cards
            document.querySelectorAll('.card, .app-card').forEach(card => {
                card.classList.add('hover-lift');
            });

            // Add hover-zoom to images in cards
            document.querySelectorAll('.card img, .app-card img').forEach(img => {
                const wrapper = img.closest('.card, .app-card');
                if (wrapper) {
                    wrapper.classList.add('hover-zoom');
                    img.style.transition = 'transform 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
                }
            });
        }

        // Image Animations
        initImageAnimations() {
            // Lazy load images with fade-in
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.classList.add('animate-blur-in');
                            img.removeAttribute('data-src');
                        } else {
                            img.classList.add('animate-fade-in-up');
                        }
                        
                        imageObserver.unobserve(img);
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('img[data-src], img:not(.animated)').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    // Smooth Scroll for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#' || !href) return;
            
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                const offsetTop = target.offsetTop - 80;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new ModernAnimations();
        });
    } else {
        new ModernAnimations();
    }

})();

