/**
 * Modern Hero Slider
 * Beautiful, smooth slider with modern design
 */

(function() {
    'use strict';

    function initModernSlider() {
        const container = document.getElementById('modern-hero-slider');
        if (!container) return;

        const slides = container.querySelectorAll('.modern-slider-slide');
        if (slides.length <= 1) {
            // Only one slide, no need for slider
            slides[0]?.classList.add('active');
            return;
        }

        let currentIndex = 0;
        let autoplayInterval = null;
        const autoplay = typeof option !== 'undefined' ? (option('slider_autoplay', '1') === '1') : true;
        const autoplaySpeed = typeof option !== 'undefined' ? parseInt(option('slider_autoplay_speed', '5000')) : 5000;
        const transition = typeof option !== 'undefined' ? option('slider_transition', 'fade') : 'fade';

        const prevBtn = container.querySelector('.modern-slider-arrow-prev');
        const nextBtn = container.querySelector('.modern-slider-arrow-next');
        const dots = container.querySelectorAll('.modern-slider-dot');

        // Show slide function
        function showSlide(index) {
            currentIndex = index;
            if (currentIndex < 0) currentIndex = slides.length - 1;
            if (currentIndex >= slides.length) currentIndex = 0;

            // Update slides
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (i === currentIndex) {
                    setTimeout(() => {
                        slide.classList.add('active');
                    }, 50);
                }
            });

            // Update dots
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentIndex);
            });

            // Reset autoplay
            if (autoplay) {
                resetAutoplay();
            }
        }

        // Navigation functions
        function nextSlide() {
            showSlide(currentIndex + 1);
        }

        function prevSlide() {
            showSlide(currentIndex - 1);
        }

        function goToSlide(index) {
            showSlide(index);
        }

        // Autoplay functions
        function startAutoplay() {
            if (!autoplay) return;
            stopAutoplay();
            autoplayInterval = setInterval(nextSlide, autoplaySpeed);
        }

        function stopAutoplay() {
            if (autoplayInterval) {
                clearInterval(autoplayInterval);
                autoplayInterval = null;
            }
        }

        function resetAutoplay() {
            stopAutoplay();
            if (autoplay) {
                startAutoplay();
            }
        }

        // Event listeners
        if (nextBtn) {
            nextBtn.addEventListener('click', nextSlide);
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', prevSlide);
        }

        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => goToSlide(index));
        });

        // Pause on hover
        container.addEventListener('mouseenter', stopAutoplay);
        container.addEventListener('mouseleave', () => {
            if (autoplay) {
                startAutoplay();
            }
        });

        // Touch/swipe support
        let touchStartX = 0;
        let touchEndX = 0;

        container.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
            stopAutoplay();
        });

        container.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
            if (autoplay) {
                startAutoplay();
            }
        });

        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;

            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    nextSlide();
                } else {
                    prevSlide();
                }
            }
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (document.activeElement.tagName === 'INPUT') return;
            
            if (e.key === 'ArrowLeft') {
                prevSlide();
            } else if (e.key === 'ArrowRight') {
                nextSlide();
            }
        });

        // Initialize
        showSlide(0);
        if (autoplay) {
            startAutoplay();
        }

        // Expose controls globally
        container.sliderControls = {
            next: nextSlide,
            prev: prevSlide,
            goTo: goToSlide,
            startAutoplay: startAutoplay,
            stopAutoplay: stopAutoplay
        };
    }

    // Make function globally available
    window.initModernSlider = initModernSlider;

    // Auto-initialize if DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initModernSlider);
    } else {
        initModernSlider();
    }
})();

