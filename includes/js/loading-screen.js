/**
 * Modern Loading Screen Handler
 * Handles page loading animation and prevents FOUC
 */

(function() {
    'use strict';

    // Check if loading animation is enabled
    const isEnabled = typeof option !== 'undefined' && option('enable_loading_animation', '1') === '1';
    
    if (!isEnabled) {
        return;
    }

    const loadingScreen = document.getElementById('page-loader');
    
    if (!loadingScreen) {
        return;
    }

    // Show loading screen immediately
    loadingScreen.style.opacity = '1';
    loadingScreen.style.display = 'flex';
    loadingScreen.style.visibility = 'visible';
    loadingScreen.classList.add('show');
    
    // Prevent scrolling while loading
    if (document.body) {
        document.body.style.overflow = 'hidden';
        document.body.classList.add('loading');
    }
    
    // If page is already loaded when script runs, wait a bit for smooth transition
    if (document.readyState === 'complete') {
        setTimeout(() => {
            fadeOutLoader();
        }, 500);
    }

    // Handle page load
    window.addEventListener('load', () => {
        // Wait a minimum time for smooth transition
        const minLoadTime = 800; // Minimum display time in ms
        const startTime = performance.now();
        
        const fadeOutAfterMinTime = () => {
            const elapsed = performance.now() - startTime;
            const remaining = Math.max(0, minLoadTime - elapsed);
            
            setTimeout(() => {
                fadeOutLoader();
            }, remaining);
        };

        // If images are still loading, wait for them
        const images = document.querySelectorAll('img');
        let loadedImages = 0;
        const totalImages = images.length;

        if (totalImages === 0) {
            // No images, just wait minimum time
            fadeOutAfterMinTime();
            return;
        }

        images.forEach(img => {
            if (img.complete) {
                loadedImages++;
            } else {
                img.addEventListener('load', () => {
                    loadedImages++;
                    checkAllLoaded();
                });
                img.addEventListener('error', () => {
                    loadedImages++;
                    checkAllLoaded();
                });
            }
        });

        function checkAllLoaded() {
            if (loadedImages >= totalImages) {
                fadeOutAfterMinTime();
            }
        }

        // Check if all images are already loaded
        if (loadedImages >= totalImages) {
            fadeOutAfterMinTime();
        } else {
            // Fallback: fade out after maximum wait time (3 seconds)
            setTimeout(() => {
                fadeOutLoader();
            }, 3000);
        }
    });

    function fadeOutLoader() {
        const loader = document.getElementById('page-loader');
        
        if (!loader || loader.classList.contains('hidden')) {
            return; // Already hidden
        }

        // Fade out animation
        loader.style.opacity = '0';
        loader.style.visibility = 'hidden';
        loader.classList.remove('show');
        loader.classList.add('hidden');

        // Remove from DOM after animation completes
        setTimeout(() => {
            if (loader && loader.parentNode) {
                loader.remove();
            }
            // Restore body scroll
            if (document.body) {
                document.body.style.overflow = '';
                document.body.style.overflowX = 'hidden'; // Prevent horizontal scroll
            }
            
            // Trigger page ready event
            document.dispatchEvent(new CustomEvent('pageloaded'));
            
            // Remove body overflow hidden class if exists
            document.body.classList.remove('loading');
        }, 500);
    }

    // Fallback: Hide loader after 5 seconds max (prevents stuck loading screen)
    setTimeout(() => {
        fadeOutLoader();
    }, 5000);

    // Handle browser back/forward navigation
    window.addEventListener('pageshow', (event) => {
        // If page was loaded from cache, hide loader immediately
        if (event.persisted) {
            fadeOutLoader();
        }
    });

})();

