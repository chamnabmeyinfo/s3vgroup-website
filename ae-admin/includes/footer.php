        </main>
    </div>

    <script>
    // macOS-like Admin Scripts
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile sidebar toggle
        const sidebar = document.getElementById('mac-sidebar');
        if (window.innerWidth <= 1024) {
            // Add mobile menu button to top bar
            const topBar = document.querySelector('.mac-top-bar');
            if (topBar && !document.getElementById('mobile-menu-toggle')) {
                const menuBtn = document.createElement('button');
                menuBtn.id = 'mobile-menu-toggle';
                menuBtn.innerHTML = '<svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M3 5h14M3 10h14M3 15h14"/></svg>';
                menuBtn.style.cssText = 'background: none; border: none; color: var(--mac-text); cursor: pointer; padding: 6px 12px; border-radius: 6px; display: inline-flex; align-items: center; transition: background 0.2s;';
                menuBtn.onmouseover = () => menuBtn.style.background = 'rgba(0,0,0,0.05)';
                menuBtn.onmouseout = () => menuBtn.style.background = 'none';
                menuBtn.onclick = () => sidebar.classList.toggle('open');
                topBar.querySelector('.mac-top-bar-left').prepend(menuBtn);
            }
        }

        // Toast notification system
        window.showToast = function(message, type = 'info') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <span style="flex: 1; font-size: 14px;">${message}</span>
                <button onclick="this.parentElement.remove()" style="background: none; border: none; cursor: pointer; opacity: 0.5; padding: 4px; border-radius: 4px; transition: all 0.2s;" onmouseover="this.style.opacity='1'; this.style.background='rgba(0,0,0,0.05)'" onmouseout="this.style.opacity='0.5'; this.style.background='none'">×</button>
            `;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideInRight 0.3s cubic-bezier(0.16, 1, 0.3, 1) reverse';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        };

        // macOS-like admin notices
        const notices = document.querySelectorAll('.mac-notice');
        notices.forEach(notice => {
            const closeBtn = document.createElement('button');
            closeBtn.innerHTML = '×';
            closeBtn.style.cssText = 'float: right; background: none; border: none; font-size: 18px; cursor: pointer; opacity: 0.5; padding: 4px 8px; border-radius: 4px; transition: all 0.2s;';
            closeBtn.onmouseover = () => { closeBtn.style.opacity = '1'; closeBtn.style.background = 'rgba(0,0,0,0.05)'; };
            closeBtn.onmouseout = () => { closeBtn.style.opacity = '0.5'; closeBtn.style.background = 'none'; };
            closeBtn.onclick = () => notice.remove();
            notice.style.position = 'relative';
            notice.appendChild(closeBtn);
        });
    });
    </script>
</body>
</html>
