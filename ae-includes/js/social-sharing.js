/**
 * Social Sharing Buttons
 */
(function() {
    'use strict';

    if (!option || option('enable_social_sharing', '1') !== '1') {
        return;
    }

    function shareOnFacebook(url, title) {
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank', 'width=600,height=400');
    }

    function shareOnTwitter(url, title) {
        window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`, '_blank', 'width=600,height=400');
    }

    function shareOnLinkedIn(url, title) {
        window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`, '_blank', 'width=600,height=400');
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            if (window.toast) {
                window.toast.show('Link copied to clipboard!', 'success', 2000);
            } else {
                alert('Link copied to clipboard!');
            }
        }).catch(() => {
            alert('Failed to copy link');
        });
    }

    function createShareButtons() {
        const shareContainer = document.getElementById('social-share-buttons');
        if (!shareContainer) return;

        const url = window.location.href;
        const title = document.title;
        const description = document.querySelector('meta[name="description"]')?.content || '';

        shareContainer.innerHTML = `
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm font-medium text-gray-700 mr-2">Share:</span>
                <button onclick="shareOnFacebook('${url}', '${title}')" class="px-3 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700 transition-colors">
                    Facebook
                </button>
                <button onclick="shareOnTwitter('${url}', '${title}')" class="px-3 py-2 bg-[#1DA1F2] text-white rounded-md text-sm hover:bg-[#1a8cd8] transition-colors">
                    Twitter
                </button>
                <button onclick="shareOnLinkedIn('${url}', '${title}')" class="px-3 py-2 bg-[#0077B5] text-white rounded-md text-sm hover:bg-[#006399] transition-colors">
                    LinkedIn
                </button>
                <button onclick="copyToClipboard('${url}')" class="px-3 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700 transition-colors">
                    Copy Link
                </button>
            </div>
        `;
    }

    // Make functions globally available
    window.shareOnFacebook = shareOnFacebook;
    window.shareOnTwitter = shareOnTwitter;
    window.shareOnLinkedIn = shareOnLinkedIn;
    window.copyToClipboard = copyToClipboard;

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createShareButtons);
    } else {
        createShareButtons();
    }
})();

