/**
 * Professional Hero Slider/Carousel
 * Supports fade, slide, and zoom transitions
 */

(function() {
    'use strict';

    if (typeof option === 'undefined' || option('enable_hero_slider', '1') !== '1') {
        return;
    }

    class HeroSlider {
        constructor(container, options = {}) {
            this.container = container;
            this.slides = container.querySelectorAll('.slider-slide');
            this.currentIndex = 0;
            this.autoplay = options.autoplay !== false;
            this.autoplaySpeed = options.autoplaySpeed || 5000;
            this.transition = options.transition || 'fade';
            this.interval = null;
            
            if (this.slides.length <= 1) return;

            this.init();
        }

        init() {
            // Create navigation dots
            this.createDots();
            
            // Create navigation arrows
            this.createArrows();
            
            // Set initial slide
            this.showSlide(0);
            
            // Start autoplay
            if (this.autoplay) {
                this.startAutoplay();
            }
            
            // Pause on hover
            this.container.addEventListener('mouseenter', () => this.stopAutoplay());
            this.container.addEventListener('mouseleave', () => {
                if (this.autoplay) {
                    this.startAutoplay();
                }
            });

            // Touch/swipe support
            this.initSwipe();
        }

        createDots() {
            const dotsContainer = document.createElement('div');
            dotsContainer.className = 'slider-dots absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-2 z-10';
            
            this.slides.forEach((_, index) => {
                const dot = document.createElement('button');
                dot.className = `slider-dot w-3 h-3 rounded-full transition-all ${index === 0 ? 'bg-white' : 'bg-white/50'}`;
                dot.addEventListener('click', () => this.goToSlide(index));
                dotsContainer.appendChild(dot);
            });
            
            this.container.appendChild(dotsContainer);
            this.dots = dotsContainer.querySelectorAll('.slider-dot');
        }

        createArrows() {
            const prevArrow = document.createElement('button');
            prevArrow.className = 'slider-arrow slider-arrow-prev absolute left-4 top-1/2 transform -translate-y-1/2 z-10 bg-white/80 hover:bg-white text-gray-900 p-3 rounded-full shadow-lg transition-all';
            prevArrow.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>';
            prevArrow.addEventListener('click', () => this.prevSlide());

            const nextArrow = document.createElement('button');
            nextArrow.className = 'slider-arrow slider-arrow-next absolute right-4 top-1/2 transform -translate-y-1/2 z-10 bg-white/80 hover:bg-white text-gray-900 p-3 rounded-full shadow-lg transition-all';
            nextArrow.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';
            nextArrow.addEventListener('click', () => this.nextSlide());

            this.container.appendChild(prevArrow);
            this.container.appendChild(nextArrow);
        }

        showSlide(index) {
            // Update current index
            this.currentIndex = index;
            if (this.currentIndex < 0) this.currentIndex = this.slides.length - 1;
            if (this.currentIndex >= this.slides.length) this.currentIndex = 0;

            // Hide all slides
            this.slides.forEach((slide, i) => {
                slide.classList.remove('active');
                if (this.transition === 'fade') {
                    slide.style.opacity = '0';
                } else if (this.transition === 'slide') {
                    slide.style.transform = `translateX(${(i - this.currentIndex) * 100}%)`;
                } else if (this.transition === 'zoom') {
                    slide.style.transform = 'scale(1.1)';
                    slide.style.opacity = '0';
                }
            });

            // Show current slide
            const currentSlide = this.slides[this.currentIndex];
            currentSlide.classList.add('active');
            
            setTimeout(() => {
                if (this.transition === 'fade') {
                    currentSlide.style.opacity = '1';
                } else if (this.transition === 'slide') {
                    currentSlide.style.transform = 'translateX(0)';
                } else if (this.transition === 'zoom') {
                    currentSlide.style.transform = 'scale(1)';
                    currentSlide.style.opacity = '1';
                }
            }, 50);

            // Update dots
            if (this.dots) {
                this.dots.forEach((dot, i) => {
                    if (i === this.currentIndex) {
                        dot.classList.remove('bg-white/50');
                        dot.classList.add('bg-white');
                    } else {
                        dot.classList.remove('bg-white');
                        dot.classList.add('bg-white/50');
                    }
                });
            }
        }

        nextSlide() {
            this.showSlide(this.currentIndex + 1);
            if (this.autoplay) {
                this.restartAutoplay();
            }
        }

        prevSlide() {
            this.showSlide(this.currentIndex - 1);
            if (this.autoplay) {
                this.restartAutoplay();
            }
        }

        goToSlide(index) {
            this.showSlide(index);
            if (this.autoplay) {
                this.restartAutoplay();
            }
        }

        startAutoplay() {
            this.stopAutoplay();
            this.interval = setInterval(() => {
                this.nextSlide();
            }, this.autoplaySpeed);
        }

        stopAutoplay() {
            if (this.interval) {
                clearInterval(this.interval);
                this.interval = null;
            }
        }

        restartAutoplay() {
            this.stopAutoplay();
            if (this.autoplay) {
                this.startAutoplay();
            }
        }

        initSwipe() {
            let touchStartX = 0;
            let touchEndX = 0;

            this.container.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            });

            this.container.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                this.handleSwipe();
            });

            this.handleSwipe = () => {
                if (touchEndX < touchStartX - 50) {
                    this.nextSlide();
                }
                if (touchEndX > touchStartX + 50) {
                    this.prevSlide();
                }
            };
        }
    }

    // Initialize slider when DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        const sliderContainer = document.getElementById('hero-slider');
        if (sliderContainer) {
            const autoplay = option('slider_autoplay', '1') === '1';
            const autoplaySpeed = parseInt(option('slider_autoplay_speed', '5000'));
            const transition = option('slider_transition', 'fade');
            
            new HeroSlider(sliderContainer, {
                autoplay,
                autoplaySpeed,
                transition,
            });
        }
    });

    // CSS for slider
    if (!document.getElementById('hero-slider-styles')) {
        const style = document.createElement('style');
        style.id = 'hero-slider-styles';
        style.textContent = `
            #hero-slider {
                position: relative;
                overflow: hidden;
                height: 600px;
            }

            .slider-slide {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                transition: opacity 0.8s ease-in-out, transform 0.8s ease-in-out;
            }

            .slider-slide.active {
                position: relative;
            }

            .slider-slide img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .slider-content {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(0, 0, 0, 0.4);
                z-index: 2;
            }

            .slider-arrow {
                opacity: 0;
                transition: opacity 0.3s;
            }

            #hero-slider:hover .slider-arrow {
                opacity: 1;
            }

            @media (max-width: 768px) {
                #hero-slider {
                    height: 400px;
                }
                .slider-arrow {
                    opacity: 1;
                    padding: 0.5rem;
                }
            }
        `;
        document.head.appendChild(style);
    }

})();

