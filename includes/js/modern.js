/**
 * Modern Website Features
 * Toast notifications, dark mode, search, mobile menu
 */

(function() {
    'use strict';

    // Wait for DOM to be ready before initializing
    const init = () => {
        // DOM is ready, proceed with initialization
        if (!document.body) {
            setTimeout(init, 10);
            return;
        }

    // ========================================
    // Toast Notification System
    // ========================================
    class ToastNotification {
        constructor() {
            this.container = null;
            this.init();
        }

        init() {
            if (document.getElementById('toast-container')) {
                this.container = document.getElementById('toast-container');
                return;
            }

            // Wait for body to be available
            const initContainer = () => {
                if (!document.body) {
                    setTimeout(initContainer, 10);
                    return;
                }

                this.container = document.createElement('div');
                this.container.id = 'toast-container';
                this.container.className = 'fixed top-4 right-4 z-50 space-y-2 pointer-events-none';
                document.body.appendChild(this.container);
            };

            initContainer();
        }

        show(message, type = 'info', duration = 3000) {
            const toast = document.createElement('div');
            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ',
            };

            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500',
            };

            toast.className = `${colors[type] || colors.info} text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-3 pointer-events-auto transform transition-all duration-300 translate-x-full opacity-0`;
            toast.innerHTML = `
                <span class="text-xl font-bold">${icons[type] || icons.info}</span>
                <span class="flex-1">${message}</span>
                <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200 text-xl font-bold">&times;</button>
            `;

            this.container.appendChild(toast);

            // Trigger animation
            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            });

            // Auto remove
            if (duration > 0) {
                setTimeout(() => {
                    toast.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, duration);
            }

            return toast;
        }
    }

    // Global toast instance
    window.toast = new ToastNotification();

    // ========================================
    // Dark Mode Toggle
    // ========================================
    class DarkMode {
        constructor() {
            this.init();
        }

        init() {
            // Check localStorage
            const saved = localStorage.getItem('darkMode');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (saved === null) {
                // First visit - use system preference
                this.set(prefersDark);
            } else {
                this.set(saved === 'true');
            }

            // Create toggle button
            this.createToggle();

            // Listen for system preference changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (localStorage.getItem('darkMode') === null) {
                    this.set(e.matches);
                }
            });
        }

        set(enabled) {
            if (enabled) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            localStorage.setItem('darkMode', enabled.toString());
        }

        toggle() {
            const isDark = document.documentElement.classList.contains('dark');
            this.set(!isDark);
            window.toast?.show(`Dark mode ${!isDark ? 'enabled' : 'disabled'}`, 'info', 2000);
        }

        createToggle() {
            // Check if toggle already exists
            if (document.getElementById('dark-mode-toggle')) {
                return;
            }

            const toggle = document.createElement('button');
            toggle.id = 'dark-mode-toggle';
            toggle.className = 'fixed bottom-4 right-4 z-40 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-800 rounded-full p-3 shadow-lg hover:shadow-xl transition-all';
            toggle.innerHTML = `
                <svg class="w-6 h-6 sun-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <svg class="w-6 h-6 moon-icon hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            `;
            toggle.onclick = () => this.toggle();

            // Update icon based on current mode
            const updateIcon = () => {
                const isDark = document.documentElement.classList.contains('dark');
                toggle.querySelector('.sun-icon').classList.toggle('hidden', isDark);
                toggle.querySelector('.moon-icon').classList.toggle('hidden', !isDark);
            };

            updateIcon();
            
            // Watch for changes
            const observer = new MutationObserver(updateIcon);
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

            // Wait for body to be available
            const appendToggle = () => {
                if (!document.body) {
                    setTimeout(appendToggle, 10);
                    return;
                }
                document.body.appendChild(toggle);
            };

            appendToggle();
        }
    }

    // Initialize dark mode when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof option === 'function' && option('enable_dark_mode', '1') === '1') {
                new DarkMode();
            }
        });
    } else {
        if (typeof option === 'function' && option('enable_dark_mode', '1') === '1') {
            new DarkMode();
        }
    }

    // ========================================
    // Enhanced Mobile Navigation
    // ========================================
    window.toggleMobileMenu = function() {
        const menu = document.getElementById('mobile-menu');
        const button = document.querySelector('[onclick="toggleMobileMenu()"]');
        
        if (!menu) {
            // Create mobile menu
            const nav = document.querySelector('header nav');
            if (!nav) return;

            const mobileMenu = document.createElement('div');
            mobileMenu.id = 'mobile-menu';
            mobileMenu.className = 'fixed inset-0 z-40 bg-white dark:bg-gray-900 transform translate-x-full transition-transform duration-300 md:hidden';
            mobileMenu.innerHTML = `
                <div class="flex flex-col h-full">
                    <div class="flex items-center justify-between p-4 border-b">
                        <span class="text-xl font-bold">Menu</span>
                        <button onclick="toggleMobileMenu()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <nav class="flex-1 p-4 space-y-4 overflow-y-auto">
                        ${nav.innerHTML}
                    </nav>
                </div>
            `;
            document.body.appendChild(mobileMenu);
            mobileMenu.classList.remove('translate-x-full');
        } else {
            menu.classList.toggle('translate-x-full');
        }
    };

    // ========================================
    // Product Search
    // ========================================
    class ProductSearch {
        constructor() {
            this.resultsContainer = null;
            this.init();
        }

        init() {
            const searchInput = document.getElementById('product-search');
            if (!searchInput) return;

            let debounceTimer;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                const query = e.target.value.trim();

                if (query.length < 2) {
                    this.hideResults();
                    return;
                }

                debounceTimer = setTimeout(() => {
                    this.search(query);
                }, 300);
            });

            // Hide results on click outside
            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && this.resultsContainer && !this.resultsContainer.contains(e.target)) {
                    this.hideResults();
                }
            });
        }

        async search(query) {
            try {
                const response = await fetch(`/api/products/index.php?search=${encodeURIComponent(query)}&limit=5`);
                const result = await response.json();

                if (result.status === 'success' && result.data.products) {
                    this.showResults(result.data.products);
                } else {
                    this.hideResults();
                }
            } catch (error) {
                console.error('Search error:', error);
            }
        }

        showResults(products) {
            if (!this.resultsContainer) {
                this.resultsContainer = document.createElement('div');
                this.resultsContainer.id = 'search-results';
                this.resultsContainer.className = 'absolute top-full left-0 right-0 mt-2 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 max-h-96 overflow-y-auto z-50';
                const searchInput = document.getElementById('product-search');
                searchInput.parentElement.style.position = 'relative';
                searchInput.parentElement.appendChild(this.resultsContainer);
            }

            if (products.length === 0) {
                this.resultsContainer.innerHTML = '<div class="p-4 text-center text-gray-500">No products found</div>';
            } else {
                this.resultsContainer.innerHTML = products.map(product => `
                    <a href="/product.php?slug=${product.slug}" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <div class="flex items-center gap-3">
                            ${product.heroImage ? `<img src="${product.heroImage}" alt="${product.name}" class="w-12 h-12 rounded object-cover">` : ''}
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 dark:text-white">${product.name}</h3>
                                ${product.summary ? `<p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1">${product.summary}</p>` : ''}
                            </div>
                        </div>
                    </a>
                `).join('');
            }

            this.resultsContainer.style.display = 'block';
        }

        hideResults() {
            if (this.resultsContainer) {
                this.resultsContainer.style.display = 'none';
            }
        }
    }

    // Initialize search
    if (typeof option === 'function' && option('enable_search', '1') === '1') {
        document.addEventListener('DOMContentLoaded', () => {
            new ProductSearch();
        });
    }

    // ========================================
    // Smooth Scroll and Animations
    // ========================================
    if (typeof option === 'function' && option('enable_animations', '1') === '1') {
        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px',
        };

        const fadeInObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                    fadeInObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('section, .animate-on-scroll').forEach(el => {
                el.classList.add('opacity-0', 'transition-opacity', 'duration-700');
                fadeInObserver.observe(el);
            });
        });
    }

    // CSS for animations
    const addStyles = () => {
        if (!document.head) {
            setTimeout(addStyles, 10);
            return;
        }

        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-fade-in {
                animation: fadeIn 0.7s ease-out forwards;
            }
            .dark {
                color-scheme: dark;
            }
            .dark body {
                background-color: #111827;
                color: #f9fafb;
            }
            .dark header {
                background-color: #1f2937;
                border-color: #374151;
            }
            .dark footer {
                background-color: #1f2937;
            }
            .dark .bg-white {
                background-color: #1f2937;
            }
            .dark .bg-gray-50 {
                background-color: #111827;
            }
            .dark .text-gray-900 {
                color: #f9fafb;
            }
            .dark .text-gray-700 {
                color: #d1d5db;
            }
            .dark .border-gray-200 {
                border-color: #374151;
            }
        `;
        document.head.appendChild(style);
    };

    addStyles();
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        // DOM already ready
        init();
    }
})();

