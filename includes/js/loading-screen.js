/**
 * Modern Loading Screen Handler - Optimized for Fast Loading
 * Handles page loading animation without waiting for all images
 */

(function() {
    'use strict';

    // Check if loading animation is enabled
    const isEnabled = typeof window.option !== 'undefined' && window.option('enable_loading_animation', '1') === '1';
    
    if (!isEnabled) {
        // If disabled, hide loader immediately
        const loader = document.getElementById('page-loader');
        if (loader) {
            loader.style.display = 'none';
            loader.remove();
        }
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
    
    let hasFadedOut = false;
    
    function fadeOutLoader() {
        // Prevent multiple calls
        if (hasFadedOut) {
            return;
        }
        hasFadedOut = true;
        
        const loader = document.getElementById('page-loader');
        
        if (!loader || loader.classList.contains('hidden')) {
            return; // Already hidden
        }

        // Fade out animation
        loader.style.opacity = '0';
        loader.style.transition = 'opacity 0.3s ease-out';
        
        // Remove from DOM after animation completes
        setTimeout(() => {
            if (loader && loader.parentNode) {
                loader.style.display = 'none';
                loader.remove();
            }
            // Restore body scroll
            if (document.body) {
                document.body.style.overflow = '';
                document.body.style.overflowX = 'hidden';
                document.body.classList.remove('loading');
            }
            
            // Trigger page ready event
            document.dispatchEvent(new CustomEvent('pageloaded'));
        }, 300);
    }

    // OPTIMIZED: Don't wait for all images - just wait for DOM and critical resources
    // This is much faster, especially for 3G networks
    
    // Strategy 1: If DOM is already ready, hide quickly
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        // Page is already loaded, hide loader after brief delay for smooth transition
        setTimeout(() => {
            fadeOutLoader();
        }, 300);
        return;
    }

    // Strategy 2: Wait for DOMContentLoaded (faster than window.load)
    let domReady = false;
    let windowLoaded = false;
    
    document.addEventListener('DOMContentLoaded', () => {
        domReady = true;
        // Hide loader after DOM is ready (don't wait for images)
        setTimeout(() => {
            fadeOutLoader();
        }, 200);
    });

    // Strategy 3: Fallback - hide after window.load (but with shorter timeout)
    window.addEventListener('load', () => {
        windowLoaded = true;
        // If DOM was already ready, loader should be gone
        // Otherwise, hide it now (but don't wait for images)
        if (!hasFadedOut) {
            setTimeout(() => {
                fadeOutLoader();
            }, 100);
        }
    });

    // Strategy 4: Safety timeout - ALWAYS hide after max 2 seconds (prevents stuck loading)
    // This ensures the page is never stuck on loading screen
    setTimeout(() => {
        if (!hasFadedOut) {
            fadeOutLoader();
        }
    }, 2000); // Reduced from 5 seconds to 2 seconds for faster loading

    // Strategy 5: Handle browser back/forward navigation (cached pages)
    window.addEventListener('pageshow', (event) => {
        // If page was loaded from cache, hide loader immediately
        if (event.persisted) {
            fadeOutLoader();
        }
    });

    // Strategy 6: Hide if user starts interacting (they don't want to wait)
    ['click', 'keydown', 'scroll', 'touchstart'].forEach(eventType => {
        document.addEventListener(eventType, () => {
            if (!hasFadedOut) {
                fadeOutLoader();
            }
        }, { once: true });
    });

})();

