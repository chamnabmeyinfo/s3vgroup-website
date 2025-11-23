/**
 * Modern Loading Screen Handler - Optimized for Fast Loading
 * Handles page loading animation without waiting for all images
 * CRITICAL: This script must run immediately, not deferred
 */

(function() {
    'use strict';

    let hasFadedOut = false;
    
    function fadeOutLoader() {
        // Prevent multiple calls
        if (hasFadedOut) {
            return;
        }
        hasFadedOut = true;
        
        const loader = document.getElementById('page-loader');
        
        if (!loader) {
            return; // No loader found
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

    // Check if loading animation is enabled (with fallback if window.option not available)
    function isEnabled() {
        if (typeof window.option === 'function') {
            return window.option('enable_loading_animation', '1') === '1';
        }
        // Default to enabled if option() not available yet
        return true;
    }

    // Get loader element
    const loadingScreen = document.getElementById('page-loader');
    
    if (!loadingScreen) {
        return; // No loader on page
    }

    // Check if disabled
    if (!isEnabled()) {
        // If disabled, hide loader immediately
        loadingScreen.style.display = 'none';
        loadingScreen.remove();
        if (document.body) {
            document.body.style.overflow = '';
            document.body.classList.remove('loading');
        }
        return;
    }

    // Show loading screen immediately (already shown by inline script, but ensure it's visible)
    loadingScreen.style.opacity = '1';
    loadingScreen.style.display = 'flex';
    loadingScreen.style.visibility = 'visible';
    loadingScreen.classList.add('show');
    
    // Prevent scrolling while loading
    if (document.body) {
        document.body.style.overflow = 'hidden';
        document.body.classList.add('loading');
    }

    // STRATEGY 1: If DOM is already ready, hide quickly
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        // Page is already loaded, hide loader after brief delay for smooth transition
        setTimeout(() => {
            fadeOutLoader();
        }, 300);
        return;
    }

    // STRATEGY 2: Wait for DOMContentLoaded (faster than window.load)
    // This fires when HTML is parsed, before images load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            // Hide loader after DOM is ready (don't wait for images)
            setTimeout(() => {
                fadeOutLoader();
            }, 200);
        });
    }

    // STRATEGY 3: Fallback - hide after window.load (but with shorter timeout)
    window.addEventListener('load', () => {
        // If DOM was already ready, loader should be gone
        // Otherwise, hide it now (but don't wait for images)
        if (!hasFadedOut) {
            setTimeout(() => {
                fadeOutLoader();
            }, 100);
        }
    }, { once: true });

    // STRATEGY 4: Safety timeout - ALWAYS hide after max 1.5 seconds (prevents stuck loading)
    // This ensures the page is NEVER stuck on loading screen
    setTimeout(() => {
        if (!hasFadedOut) {
            fadeOutLoader();
        }
    }, 1500); // Reduced to 1.5 seconds for faster loading

    // STRATEGY 5: Handle browser back/forward navigation (cached pages)
    window.addEventListener('pageshow', (event) => {
        // If page was loaded from cache, hide loader immediately
        if (event.persisted && !hasFadedOut) {
            fadeOutLoader();
        }
    }, { once: true });

    // STRATEGY 6: Hide if user starts interacting (they don't want to wait)
    ['click', 'keydown', 'scroll', 'touchstart'].forEach(eventType => {
        document.addEventListener(eventType, () => {
            if (!hasFadedOut) {
                fadeOutLoader();
            }
        }, { once: true, passive: true });
    });

})();

