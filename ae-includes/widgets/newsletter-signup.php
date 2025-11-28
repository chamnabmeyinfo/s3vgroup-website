<?php
/**
 * Newsletter Signup Widget
 * Usage: include __DIR__ . '/widgets/newsletter-signup.php';
 */

if (!option('enable_newsletter', '1')) {
    return;
}
?>

<div id="newsletter-signup-widget" class="text-white rounded-lg p-8 shadow-lg fade-in-up">
    <div class="max-w-2xl mx-auto text-center">
        <div class="mb-4">
            <svg class="h-12 w-12 mx-auto mb-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
        </div>
        <h3 class="text-2xl md:text-3xl font-bold mb-2">Stay Updated</h3>
        <p class="text-white/90 mb-6 text-lg">Subscribe to our newsletter for the latest updates, exclusive offers, and industry news.</p>
        
        <form id="newsletter-form" class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
            <input
                type="email"
                name="email"
                id="newsletter-email"
                placeholder="Enter your email"
                required
                class="flex-1 px-4 py-3 rounded-md text-gray-900 focus:outline-none focus:ring-2 focus:ring-white shadow-lg"
            >
            <input
                type="text"
                name="name"
                id="newsletter-name"
                placeholder="Your name (optional)"
                class="sm:w-40 px-4 py-3 rounded-md text-gray-900 focus:outline-none focus:ring-2 focus:ring-white shadow-lg"
            >
            <button
                type="submit"
                class="px-8 py-3 bg-white text-[#0b3a63] rounded-md font-semibold hover:bg-gray-100 transition-all hover:scale-105 transform whitespace-nowrap shadow-lg"
            >
                Subscribe
            </button>
        </form>
        
        <div id="newsletter-message" class="mt-4 text-sm"></div>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('newsletter-form');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = document.getElementById('newsletter-email').value.trim();
        const name = document.getElementById('newsletter-name').value.trim();
        const messageEl = document.getElementById('newsletter-message');
        const submitBtn = form.querySelector('button[type="submit"]');
        
        if (!email || !email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            messageEl.textContent = 'Please enter a valid email address.';
            messageEl.className = 'mt-4 text-sm text-red-300';
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Subscribing...';
        messageEl.textContent = '';
        messageEl.className = 'mt-4 text-sm';

        try {
            const response = await fetch('/api/newsletter/subscribe.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, name }),
            });

            const result = await response.json();

            if (result.status === 'success') {
                messageEl.textContent = '✅ ' + result.message;
                messageEl.className = 'mt-4 text-sm text-green-300';
                form.reset();
                
                if (window.toast) {
                    window.toast.show(result.message, 'success', 3000);
                }
            } else {
                throw new Error(result.message || 'Subscription failed');
            }
        } catch (error) {
            messageEl.textContent = '❌ ' + error.message;
            messageEl.className = 'mt-4 text-sm text-red-300';
            
            if (window.toast) {
                window.toast.show('Error: ' + error.message, 'error', 3000);
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Subscribe';
        }
    });
})();
</script>

