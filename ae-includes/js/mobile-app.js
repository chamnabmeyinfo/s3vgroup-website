/**
 * Mobile App-Like Interactions
 */

(function() {
    'use strict';

    // Check if mobile/tablet
    const isMobile = window.innerWidth <= 1024;
    
    if (!isMobile) {
        return; // Don't initialize on desktop
    }

    // Initialize Mobile App Features
    class MobileApp {
        constructor() {
            this.init();
        }

        init() {
            this.createBottomNav();
            this.initSideMenu();
            this.initSwipeGestures();
            this.initPullToRefresh();
            this.initBottomSheets();
            this.initTouchFeedback();
            this.adjustLayout();
        }

        // Create Bottom Navigation
        createBottomNav() {
            const existingNav = document.querySelector('.bottom-nav');
            if (existingNav) return;

            const bottomNav = document.createElement('nav');
            bottomNav.className = 'bottom-nav mobile-only';
            bottomNav.innerHTML = `
                <a href="/" class="bottom-nav-item ${window.location.pathname === '/' ? 'active' : ''}" data-page="home">
                    <svg class="bottom-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="bottom-nav-label">Home</span>
                </a>
                <a href="/products.php" class="bottom-nav-item ${window.location.pathname.includes('products') ? 'active' : ''}" data-page="products">
                    <svg class="bottom-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span class="bottom-nav-label">Products</span>
                </a>
                <a href="/quote.php" class="bottom-nav-item ${window.location.pathname.includes('quote') ? 'active' : ''}" data-page="quote">
                    <svg class="bottom-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="bottom-nav-label">Quote</span>
                </a>
                <a href="/contact.php" class="bottom-nav-item ${window.location.pathname.includes('contact') ? 'active' : ''}" data-page="contact">
                    <svg class="bottom-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span class="bottom-nav-label">Contact</span>
                </a>
                <a href="/about.php" class="bottom-nav-item ${window.location.pathname.includes('about') ? 'active' : ''}" data-page="about">
                    <svg class="bottom-nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="bottom-nav-label">About</span>
                </a>
            `;

            document.body.appendChild(bottomNav);
            
            // Add app container if not exists
            if (!document.querySelector('.app-container')) {
                const main = document.querySelector('main');
                if (main) {
                    main.classList.add('app-container');
                }
            }
        }

        // Initialize Side Menu
        initSideMenu() {
            // Create side menu button in header
            const header = document.querySelector('header');
            if (header && !header.querySelector('.app-header-button')) {
                const menuButton = document.createElement('button');
                menuButton.className = 'app-header-button mobile-only';
                menuButton.innerHTML = `
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                `;
                menuButton.addEventListener('click', () => this.toggleSideMenu());
                header.insertBefore(menuButton, header.firstChild);
            }

            // Create side menu
            if (!document.querySelector('.side-menu')) {
                const sideMenu = document.createElement('div');
                sideMenu.className = 'side-menu mobile-only';
                sideMenu.innerHTML = `
                    <div class="p-4">
                        <h3 class="font-semibold text-lg mb-4">Menu</h3>
                        <a href="/" class="block py-3 px-4 rounded-lg hover:bg-gray-100 mb-2">Home</a>
                        <a href="/products.php" class="block py-3 px-4 rounded-lg hover:bg-gray-100 mb-2">Products</a>
                        <a href="/about.php" class="block py-3 px-4 rounded-lg hover:bg-gray-100 mb-2">About Us</a>
                        <a href="/team.php" class="block py-3 px-4 rounded-lg hover:bg-gray-100 mb-2">Our Team</a>
                        <a href="/testimonials.php" class="block py-3 px-4 rounded-lg hover:bg-gray-100 mb-2">Testimonials</a>
                        <a href="/quote.php" class="block py-3 px-4 rounded-lg hover:bg-gray-100 mb-2">Request Quote</a>
                        <a href="/contact.php" class="block py-3 px-4 rounded-lg hover:bg-gray-100 mb-2">Contact</a>
                    </div>
                `;
                document.body.appendChild(sideMenu);

                // Create overlay
                const overlay = document.createElement('div');
                overlay.className = 'side-menu-overlay mobile-only';
                overlay.addEventListener('click', () => this.toggleSideMenu());
                document.body.appendChild(overlay);
            }
        }

        toggleSideMenu() {
            const menu = document.querySelector('.side-menu');
            const overlay = document.querySelector('.side-menu-overlay');
            if (menu && overlay) {
                menu.classList.toggle('open');
                overlay.classList.toggle('active');
            }
        }

        // Initialize Swipe Gestures
        initSwipeGestures() {
            const cards = document.querySelectorAll('.app-card, .swipeable-card');
            cards.forEach(card => {
                let startX = 0;
                let currentX = 0;
                let isDragging = false;

                card.addEventListener('touchstart', (e) => {
                    startX = e.touches[0].clientX;
                    isDragging = true;
                });

                card.addEventListener('touchmove', (e) => {
                    if (!isDragging) return;
                    currentX = e.touches[0].clientX - startX;
                    if (Math.abs(currentX) > 10) {
                        e.preventDefault();
                    }
                });

                card.addEventListener('touchend', () => {
                    if (Math.abs(currentX) > 50) {
                        // Handle swipe
                        card.style.transform = `translateX(${currentX > 0 ? '100%' : '-100%'})`;
                        setTimeout(() => {
                            card.style.transform = '';
                        }, 300);
                    }
                    isDragging = false;
                    currentX = 0;
                });
            });
        }

        // Initialize Pull to Refresh
        initPullToRefresh() {
            const container = document.querySelector('.app-container, main');
            if (!container) return;

            let startY = 0;
            let currentY = 0;
            let isPulling = false;

            container.addEventListener('touchstart', (e) => {
                if (window.scrollY === 0) {
                    startY = e.touches[0].clientY;
                    isPulling = true;
                }
            });

            container.addEventListener('touchmove', (e) => {
                if (!isPulling) return;
                currentY = e.touches[0].clientY - startY;
                if (currentY > 0 && currentY < 80) {
                    e.preventDefault();
                }
            });

            container.addEventListener('touchend', () => {
                if (isPulling && currentY > 50) {
                    // Trigger refresh
                    location.reload();
                }
                isPulling = false;
                currentY = 0;
            });
        }

        // Initialize Bottom Sheets
        initBottomSheets() {
            document.addEventListener('click', (e) => {
                if (e.target.matches('[data-bottom-sheet]')) {
                    const sheetId = e.target.getAttribute('data-bottom-sheet');
                    const sheet = document.getElementById(sheetId);
                    if (sheet) {
                        sheet.classList.add('open');
                    }
                }

                if (e.target.matches('.bottom-sheet-close') || e.target.closest('.bottom-sheet-close')) {
                    const sheet = e.target.closest('.bottom-sheet');
                    if (sheet) {
                        sheet.classList.remove('open');
                    }
                }
            });
        }

        // Initialize Touch Feedback
        initTouchFeedback() {
            const touchElements = document.querySelectorAll('.app-card, .app-button, .app-list-item');
            touchElements.forEach(el => {
                el.classList.add('touch-feedback');
            });
        }

        // Adjust Layout for Mobile
        adjustLayout() {
            // Add app header to existing header
            const header = document.querySelector('header');
            if (header) {
                header.classList.add('app-header');
                
                const title = header.querySelector('h1, .text-xl');
                if (title) {
                    title.classList.add('app-header-title');
                }

                // Hide desktop nav on mobile
                const desktopNav = header.querySelector('nav.hidden.md\\:flex');
                if (desktopNav) {
                    desktopNav.classList.add('desktop-only');
                }
            }

            // Convert cards to app style
            const cards = document.querySelectorAll('.rounded-lg, .card');
            cards.forEach(card => {
                if (!card.classList.contains('app-card')) {
                    card.classList.add('app-card');
                }
            });

            // Convert product cards
            const productCards = document.querySelectorAll('[class*="product"]');
            productCards.forEach(card => {
                if (card.querySelector('img')) {
                    card.classList.add('app-product-card');
                    const img = card.querySelector('img');
                    if (img) {
                        img.classList.add('app-product-image');
                    }
                }
            });
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new MobileApp();
        });
    } else {
        new MobileApp();
    }

    // Handle orientation change
    window.addEventListener('orientationchange', () => {
        setTimeout(() => {
            location.reload();
        }, 100);
    });

    // Prevent zoom on double tap
    let lastTouchEnd = 0;
    document.addEventListener('touchend', (e) => {
        const now = Date.now();
        if (now - lastTouchEnd <= 300) {
            e.preventDefault();
        }
        lastTouchEnd = now;
    }, false);

})();

