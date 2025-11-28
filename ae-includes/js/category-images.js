/**
 * Category Images Loader
 * Handles professional image loading with placeholders
 */

(function() {
    'use strict';

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCategoryImages);
    } else {
        initCategoryImages();
    }

    function initCategoryImages() {
        const categoryWrappers = document.querySelectorAll('.category-image-wrapper');
        
        categoryWrappers.forEach(wrapper => {
            const img = wrapper.querySelector('.category-image');
            const placeholder = wrapper.querySelector('.category-icon-placeholder');
            
            if (!img) {
                // No image, hide placeholder after delay
                setTimeout(() => {
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                }, 300);
                return;
            }

            // Check if image is already loaded
            if (img.complete && img.naturalHeight !== 0) {
                handleImageLoaded(img, wrapper, placeholder);
            } else {
                // Wait for image to load
                img.addEventListener('load', () => {
                    handleImageLoaded(img, wrapper, placeholder);
                });

                // Handle image load errors
                img.addEventListener('error', () => {
                    handleImageError(img, wrapper, placeholder);
                });

                // Timeout: if image takes too long, show fallback
                setTimeout(() => {
                    if (!wrapper.classList.contains('image-loaded')) {
                        handleImageError(img, wrapper, placeholder);
                    }
                }, 5000);
            }
        });
    }

    function handleImageLoaded(img, wrapper, placeholder) {
        wrapper.classList.add('image-loaded');
        img.style.opacity = '1';
        
        if (placeholder) {
            placeholder.style.opacity = '0';
            setTimeout(() => {
                placeholder.style.display = 'none';
            }, 300);
        }
    }

    function handleImageError(img, wrapper, placeholder) {
        img.style.display = 'none';
        wrapper.classList.add('image-error');
        
        if (placeholder) {
            // Show fallback icon with initial
            const categoryName = wrapper.closest('.category-item')?.querySelector('h3')?.textContent || '';
            const firstLetter = categoryName.charAt(0).toUpperCase();
            
            if (!wrapper.querySelector('.category-fallback-icon')) {
                placeholder.innerHTML = `
                    <div class="category-fallback-icon w-full h-full flex items-center justify-center">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md category-initial-badge">
                            ${firstLetter}
                        </div>
                    </div>
                `;
            }
            
            placeholder.style.opacity = '1';
            placeholder.style.display = 'flex';
        }
    }

    // Lazy load images using Intersection Observer (if not already using native lazy loading)
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '50px'
        });

        document.querySelectorAll('.category-image[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }

})();

