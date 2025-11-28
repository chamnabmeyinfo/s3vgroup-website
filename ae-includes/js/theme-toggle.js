/**
 * Theme Toggle Functionality
 * Handles dark mode and light mode switching
 */

(function() {
    'use strict';

    // Theme configuration
    const THEME_KEY = 'site-theme';
    const THEMES = {
        LIGHT: 'light',
        DARK: 'dark',
        AUTO: 'auto'
    };

    // Get current theme from localStorage or system preference
    function getCurrentTheme() {
        const savedTheme = localStorage.getItem(THEME_KEY);
        if (savedTheme && Object.values(THEMES).includes(savedTheme)) {
            return savedTheme;
        }
        // Check system preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            return THEMES.DARK;
        }
        return THEMES.LIGHT;
    }

    // Apply theme to document
    function applyTheme(theme) {
        const html = document.documentElement;
        const body = document.body;
        
        // Remove all theme classes
        html.classList.remove('theme-light', 'theme-dark', 'theme-auto');
        body.classList.remove('theme-light', 'theme-dark', 'theme-auto');
        
        // Determine actual theme to apply
        let actualTheme = theme;
        if (theme === THEMES.AUTO) {
            actualTheme = window.matchMedia('(prefers-color-scheme: dark)').matches 
                ? THEMES.DARK 
                : THEMES.LIGHT;
        }
        
        // Apply theme class
        html.classList.add(`theme-${actualTheme}`);
        body.classList.add(`theme-${actualTheme}`);
        html.setAttribute('data-theme', actualTheme);
        
        // Update meta theme-color
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            metaThemeColor.setAttribute('content', actualTheme === THEMES.DARK ? '#111827' : '#ffffff');
        }
        
        // Update toggle button icon
        updateToggleIcon(theme);
    }

    // Update toggle button icon
    function updateToggleIcon(theme) {
        const toggleBtn = document.getElementById('theme-toggle-btn');
        const toggleBtnMobile = document.getElementById('theme-toggle-btn-mobile');
        const buttons = [toggleBtn, toggleBtnMobile].filter(Boolean);
        
        buttons.forEach(btn => {
            const icon = btn.querySelector('svg');
            if (!icon) return;
        
        // Determine which icon to show
        let actualTheme = theme;
        if (theme === THEMES.AUTO) {
            actualTheme = window.matchMedia('(prefers-color-scheme: dark)').matches 
                ? THEMES.DARK 
                : THEMES.LIGHT;
        }
        
        // Update icon based on theme
        if (actualTheme === THEMES.DARK) {
            // Show sun icon (to switch to light)
            icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            `;
            btn.setAttribute('aria-label', 'Switch to light mode');
        } else {
            // Show moon icon (to switch to dark)
            icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            `;
            btn.setAttribute('aria-label', 'Switch to dark mode');
        }
        });
    }

    // Toggle theme
    function toggleTheme() {
        const currentTheme = getCurrentTheme();
        let newTheme;
        
        // Cycle through themes: light -> dark -> auto -> light
        if (currentTheme === THEMES.LIGHT) {
            newTheme = THEMES.DARK;
        } else if (currentTheme === THEMES.DARK) {
            newTheme = THEMES.AUTO;
        } else {
            newTheme = THEMES.LIGHT;
        }
        
        // Save and apply new theme
        localStorage.setItem(THEME_KEY, newTheme);
        applyTheme(newTheme);
        
        // Dispatch custom event for other scripts
        document.dispatchEvent(new CustomEvent('themeChanged', { 
            detail: { theme: newTheme } 
        }));
    }

    // Initialize theme on page load
    function initTheme() {
        const theme = getCurrentTheme();
        applyTheme(theme);
        
        // Listen for system theme changes (if using auto mode)
        if (window.matchMedia) {
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addEventListener('change', (e) => {
                const currentTheme = getCurrentTheme();
                if (currentTheme === THEMES.AUTO) {
                    applyTheme(THEMES.AUTO);
                }
            });
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }

    // Expose toggle function globally
    window.toggleTheme = toggleTheme;
    window.getCurrentTheme = getCurrentTheme;
    window.applyTheme = applyTheme;

})();

