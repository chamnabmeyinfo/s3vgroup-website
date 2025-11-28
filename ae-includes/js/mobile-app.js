/**
 * Mobile App-Like JavaScript
 * Provides native app experience with smooth interactions, gestures, and animations
 */

(function() {
    'use strict';

    // Add mobile-app class to body on mobile devices
    function initMobileApp() {
        if (window.innerWidth <= 768) {
            document.body.classList.add('mobile-app');
        }
    }

    // Initialize on load and resize
    initMobileApp();
    window.addEventListener('resize', initMobileApp);

    // ============================================
    // MOBILE MENU - App-like Slide In Enhancement
    // ============================================
    
    function initMobileMenu() {
        const mobileMenu = document.getElementById('mobile-menu');
        if (!mobileMenu) return;

        // Create overlay if it doesn't exist
        let overlay = document.querySelector('.mobile-menu-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'mobile-menu-overlay';
            document.body.appendChild(overlay);
        }

        // Enhance existing toggleMobileMenu function
        const originalToggle = window.toggleMobileMenu;
        if (originalToggle) {
            window.toggleMobileMenu = function() {
                originalToggle();
                
                // Add overlay and animations
                if (mobileMenu.classList.contains('hidden')) {
                    overlay.classList.remove('active');
                    document.body.style.overflow = '';
                } else {
                    overlay.classList.add('active');
                    document.body.style.overflow = 'hidden';
                    
                    // Add slide-in animation
                    requestAnimationFrame(() => {
                        mobileMenu.style.transition = 'transform 350ms cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                        mobileMenu.style.transform = 'translateX(0)';
                    });
                }
            };
        }

        // Close menu on overlay click
        overlay.addEventListener('click', () => {
            if (!mobileMenu.classList.contains('hidden') && window.toggleMobileMenu) {
                window.toggleMobileMenu();
            }
        });

        // Close menu on menu link click
        const menuLinks = mobileMenu.querySelectorAll('a');
        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                setTimeout(() => {
                    if (!mobileMenu.classList.contains('hidden') && window.toggleMobileMenu) {
                        window.toggleMobileMenu();
                    }
                }, 100);
            });
        });

        // Swipe to close
        let touchStartX = 0;
        let touchEndX = 0;

        mobileMenu.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        mobileMenu.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        }, { passive: true });

        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (diff > swipeThreshold && !mobileMenu.classList.contains('hidden')) {
                if (window.toggleMobileMenu) {
                    window.toggleMobileMenu();
                }
            }
        }

        // Ensure menu has proper initial state
        mobileMenu.style.transition = 'transform 350ms cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        if (mobileMenu.classList.contains('hidden')) {
            mobileMenu.style.transform = 'translateX(-100%)';
        }
    }

    // ============================================
    // TOUCH FEEDBACK
    // ============================================
    
    function initTouchFeedback() {
        const touchElements = document.querySelectorAll('a, button, .modern-card, .modern-nav-link');
        
        touchElements.forEach(element => {
            element.addEventListener('touchstart', function() {
                this.style.transition = 'transform 100ms, opacity 100ms';
                this.style.opacity = '0.8';
            }, { passive: true });

            element.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.style.opacity = '';
                    this.style.transition = '';
                }, 100);
            }, { passive: true });
        });
    }

    // ============================================
    // HEADER SCROLL EFFECT
    // ============================================
    
    function initHeaderScroll() {
        const header = document.querySelector('.modern-header');
        if (!header) return;

        let lastScroll = 0;
        let ticking = false;

        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    const currentScroll = window.pageYOffset || document.documentElement.scrollTop;

                    if (currentScroll > 100) {
                        header.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
                        header.style.backgroundColor = 'rgba(255, 255, 255, 0.98)';
                    } else {
                        header.style.boxShadow = '';
                        header.style.backgroundColor = '';
                    }

                    // Hide/show header on scroll (optional)
                    if (window.innerWidth <= 768) {
                        if (currentScroll > lastScroll && currentScroll > 200) {
                            header.style.transform = 'translateY(-100%)';
                        } else {
                            header.style.transform = 'translateY(0)';
                        }
                    }

                    lastScroll = currentScroll;
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    }

    // ============================================
    // SMOOTH SCROLLING
    // ============================================
    
    function initSmoothScroll() {
        const links = document.querySelectorAll('a[href^="#"]');
        
        links.forEach(link => {
            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (href === '#' || href === '') return;

                const target = document.querySelector(href);
                if (!target) return;

                e.preventDefault();
                
                const headerHeight = document.querySelector('.modern-header')?.offsetHeight || 56;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            });
        });
    }

    // ============================================
    // PULL TO REFRESH
    // ============================================
    
    function initPullToRefresh() {
        if (window.innerWidth > 768) return; // Only on mobile

        let startY = 0;
        let currentY = 0;
        let pullDistance = 0;
        const threshold = 80;
        const maxPull = 120;

        const refreshIndicator = document.createElement('div');
        refreshIndicator.className = 'pull-to-refresh';
        refreshIndicator.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 4v6h6M23 20v-6h-6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>';
        document.body.insertBefore(refreshIndicator, document.body.firstChild);

        document.addEventListener('touchstart', (e) => {
            if (window.scrollY === 0) {
                startY = e.touches[0].clientY;
            }
        }, { passive: true });

        document.addEventListener('touchmove', (e) => {
            if (startY === 0) return;
            
            currentY = e.touches[0].clientY;
            pullDistance = currentY - startY;

            if (pullDistance > 0 && window.scrollY === 0) {
                e.preventDefault();
                const pullPercent = Math.min(pullDistance / threshold, 1);
                refreshIndicator.style.top = `${pullDistance / 2}px`;
                refreshIndicator.style.opacity = pullPercent;
                
                if (pullDistance >= threshold) {
                    refreshIndicator.classList.add('active');
                } else {
                    refreshIndicator.classList.remove('active');
                }
            }
        }, { passive: false });

        document.addEventListener('touchend', () => {
            if (pullDistance >= threshold) {
                refreshIndicator.classList.add('active');
                window.location.reload();
            } else {
                refreshIndicator.style.opacity = '0';
                refreshIndicator.classList.remove('active');
            }
            startY = 0;
            pullDistance = 0;
        }, { passive: true });
    }

    // ============================================
    // BOTTOM NAVIGATION
    // ============================================
    
    function initBottomNav() {
        if (window.innerWidth > 768) return; // Only on mobile

        const currentPath = window.location.pathname;
        const bottomNav = document.querySelector('.app-bottom-nav');
        
        if (!bottomNav) return;

        // Highlight active item
        const navItems = bottomNav.querySelectorAll('.app-bottom-nav-item');
        navItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && currentPath.includes(href.split('/').pop())) {
                item.classList.add('active');
            }

            // Add tap feedback
            item.addEventListener('touchstart', function() {
                this.style.transform = 'scale(0.9)';
            }, { passive: true });

            item.addEventListener('touchend', function() {
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            }, { passive: true });
        });
    }

    // ============================================
    // LAZY LOADING IMAGES
    // ============================================
    
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.classList.add('fade-in');
                            observer.unobserve(img);
                        }
                    }
                });
            }, {
                rootMargin: '50px'
            });

            const lazyImages = document.querySelectorAll('img[data-src]');
            lazyImages.forEach(img => imageObserver.observe(img));
        }
    }

    // ============================================
    // SWIPE GESTURES FOR CARDS
    // ============================================
    
    function initSwipeGestures() {
        if (window.innerWidth > 768) return;

        const swipeableCards = document.querySelectorAll('.modern-card.swipeable');
        
        swipeableCards.forEach(card => {
            let touchStartX = 0;
            let touchStartY = 0;
            let touchEndX = 0;
            let touchEndY = 0;

            card.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
                touchStartY = e.changedTouches[0].screenY;
            }, { passive: true });

            card.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                touchEndY = e.changedTouches[0].screenY;
                handleCardSwipe(card);
            }, { passive: true });

            function handleCardSwipe(card) {
                const deltaX = touchEndX - touchStartX;
                const deltaY = touchEndY - touchStartY;
                const minSwipeDistance = 50;

                // Horizontal swipe
                if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > minSwipeDistance) {
                    if (deltaX > 0) {
                        // Swipe right - could show actions
                        card.style.transform = 'translateX(80px)';
                        setTimeout(() => {
                            card.style.transform = '';
                        }, 300);
                    } else {
                        // Swipe left - could hide/dismiss
                        card.style.transform = 'translateX(-100%)';
                        setTimeout(() => {
                            card.style.opacity = '0';
                        }, 300);
                    }
                }
            }
        });
    }

    // ============================================
    // SEARCH WITH LIVE RESULTS
    // ============================================
    
    function initMobileSearch() {
        const searchInput = document.querySelector('.modern-search-input');
        if (!searchInput || window.innerWidth > 768) return;

        let searchTimeout;
        const searchResults = document.createElement('div');
        searchResults.className = 'search-results';
        searchResults.style.display = 'none';
        document.body.appendChild(searchResults);

        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();

            if (query.length < 2) {
                searchResults.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });

        searchInput.addEventListener('focus', () => {
            if (searchInput.value.trim().length >= 2) {
                searchResults.style.display = 'block';
            }
        });

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        function performSearch(query) {
            // Placeholder for actual search functionality
            searchResults.innerHTML = `
                <div class="search-result-item">
                    <span>Searching for "${query}"...</span>
                </div>
            `;
            searchResults.style.display = 'block';
        }
    }

    // ============================================
    // VIEWPORT HEIGHT FIX FOR MOBILE
    // ============================================
    
    function fixViewportHeight() {
        const setVH = () => {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        };

        setVH();
        window.addEventListener('resize', setVH);
        window.addEventListener('orientationchange', () => {
            setTimeout(setVH, 100);
        });
    }

    // ============================================
    // PREVENT ZOOM ON DOUBLE TAP
    // ============================================
    
    function preventDoubleTapZoom() {
        if (window.innerWidth > 768) return;

        let lastTap = 0;
        document.addEventListener('touchend', (e) => {
            const currentTime = new Date().getTime();
            const tapLength = currentTime - lastTap;
            
            if (tapLength < 300 && tapLength > 0) {
                e.preventDefault();
            }
            
            lastTap = currentTime;
        }, { passive: false });
    }

    // ============================================
    // INITIALIZE ALL FEATURES
    // ============================================
    
    document.addEventListener('DOMContentLoaded', () => {
        initMobileMenu();
        initTouchFeedback();
        initHeaderScroll();
        initSmoothScroll();
        initPullToRefresh();
        initBottomNav();
        initLazyLoading();
        initSwipeGestures();
        initMobileSearch();
        fixViewportHeight();
        preventDoubleTapZoom();
        
        // Add loading animation removal
        document.body.classList.add('loaded');
    });

    // Handle page transitions
    window.addEventListener('beforeunload', () => {
        document.body.classList.add('page-leaving');
    });

})();
