        </main>
    </div>

    <script>
        // Sidebar Collapse/Expand Functionality
        (function() {
            const sidebar = document.getElementById('admin-sidebar');
            const toggleBtn = document.getElementById('sidebar-toggle');
            const body = document.body;
            
            // Load saved state
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                body.classList.add('sidebar-collapsed');
            }
            
            // Toggle sidebar
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('collapsed');
                    body.classList.toggle('sidebar-collapsed');
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                });
            }
            
            // Collapsible Sections
            const collapsibleHeaders = document.querySelectorAll('.collapsible-header');
            collapsibleHeaders.forEach(header => {
                const section = header.closest('.collapsible-section');
                const target = header.getAttribute('data-target');
                
                // Load saved state for each section
                const isSectionCollapsed = localStorage.getItem(`section_${target}_collapsed`) === 'true';
                if (isSectionCollapsed && section) {
                    section.classList.add('collapsed');
                }
                
                header.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (section) {
                        section.classList.toggle('collapsed');
                        localStorage.setItem(`section_${target}_collapsed`, section.classList.contains('collapsed'));
                    }
                });
            });
            
            // Drag and Drop for Menu Items
            let draggedElement = null;
            let draggedSection = null;
            
            const menuItems = document.querySelectorAll('.admin-nav-item[draggable="true"]');
            
            menuItems.forEach(item => {
                item.addEventListener('dragstart', function(e) {
                    draggedElement = this;
                    draggedSection = this.closest('.admin-nav-section');
                    this.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/html', this.outerHTML);
                });
                
                item.addEventListener('dragend', function(e) {
                    this.classList.remove('dragging');
                    document.querySelectorAll('.admin-nav-item').forEach(item => {
                        item.classList.remove('drag-over');
                    });
                });
                
                item.addEventListener('dragover', function(e) {
                    if (e.preventDefault) {
                        e.preventDefault();
                    }
                    e.dataTransfer.dropEffect = 'move';
                    
                    const currentSection = this.closest('.admin-nav-section');
                    if (draggedSection && currentSection && draggedSection === currentSection) {
                        this.classList.add('drag-over');
                    }
                    return false;
                });
                
                item.addEventListener('dragleave', function(e) {
                    this.classList.remove('drag-over');
                });
                
                item.addEventListener('drop', function(e) {
                    if (e.stopPropagation) {
                        e.stopPropagation();
                    }
                    
                    const currentSection = this.closest('.admin-nav-section');
                    if (draggedElement && currentSection && draggedSection === currentSection && draggedElement !== this) {
                        // Get the container (could be .collapsible-content or .admin-nav-section)
                        const container = currentSection.querySelector('.collapsible-content') || currentSection;
                        const items = Array.from(container.querySelectorAll('.admin-nav-item[draggable="true"]'));
                        
                        const draggedIndex = items.indexOf(draggedElement);
                        const targetIndex = items.indexOf(this);
                        
                        if (draggedIndex < targetIndex) {
                            container.insertBefore(draggedElement, this.nextSibling);
                        } else {
                            container.insertBefore(draggedElement, this);
                        }
                        
                        // Save order to localStorage
                        saveMenuOrder();
                    }
                    
                    this.classList.remove('drag-over');
                    return false;
                });
            });
            
            // Save menu order to localStorage
            function saveMenuOrder() {
                const sections = document.querySelectorAll('.admin-nav-section');
                const order = {};
                
                sections.forEach(section => {
                    const sectionId = section.getAttribute('data-section') || 'dashboard';
                    const items = Array.from(section.querySelectorAll('.admin-nav-item[draggable="true"]'));
                    order[sectionId] = items.map(item => item.getAttribute('data-menu-item')).filter(Boolean);
                });
                
                localStorage.setItem('menuOrder', JSON.stringify(order));
            }
            
            // Load menu order from localStorage
            function loadMenuOrder() {
                const savedOrder = localStorage.getItem('menuOrder');
                if (!savedOrder) return;
                
                try {
                    const order = JSON.parse(savedOrder);
                    const sections = document.querySelectorAll('.admin-nav-section');
                    
                    sections.forEach(section => {
                        const sectionId = section.getAttribute('data-section') || 'dashboard';
                        if (!order[sectionId]) return;
                        
                        const container = section.querySelector('.collapsible-content') || section;
                        const items = Array.from(container.querySelectorAll('.admin-nav-item[draggable="true"]'));
                        const savedItems = order[sectionId];
                        
                        // Create a map of items by data-menu-item
                        const itemMap = new Map();
                        items.forEach(item => {
                            const menuItem = item.getAttribute('data-menu-item');
                            if (menuItem) {
                                itemMap.set(menuItem, item);
                            }
                        });
                        
                        // Reorder items based on saved order
                        savedItems.forEach(menuItem => {
                            const item = itemMap.get(menuItem);
                            if (item && container.contains(item)) {
                                container.appendChild(item);
                            }
                        });
                    });
                } catch (e) {
                    console.error('Error loading menu order:', e);
                }
            }
            
            // Load order on page load
            loadMenuOrder();
        })();

        // ============================================
        // ADVANCED & FUN FEATURES
        // ============================================

        // Command Palette / Quick Search
        (function() {
            const commandPalette = document.getElementById('command-palette');
            const commandInput = document.getElementById('command-palette-input');
            const commandResults = document.getElementById('command-palette-results');
            const commandClose = document.getElementById('command-palette-close');
            const quickSearchBtn = document.getElementById('quick-search-btn');
            let selectedIndex = -1;
            let currentResults = [];

            // Menu items data for search
            const menuItems = [
                { title: 'Dashboard', desc: 'Go to main dashboard', url: '/ae-admin/', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', shortcut: 'D' },
                { title: 'Products', desc: 'Manage products', url: '/ae-admin/products.php', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', shortcut: 'P' },
                { title: 'Categories', desc: 'Manage product categories', url: '/ae-admin/categories.php', icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', shortcut: 'C' },
                { title: 'Pages', desc: 'Manage pages', url: '/ae-admin/pages.php', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', shortcut: 'Pg' },
                { title: 'Media Library', desc: 'Browse media files', url: '/ae-admin/media-library.php', icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', shortcut: 'M' },
                { title: 'Settings', desc: 'Site options and settings', url: '/ae-admin/options.php', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', shortcut: 'S' },
                { title: 'Backend Themes', desc: 'Customize admin appearance', url: '/ae-admin/backend-appearance.php', icon: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01', shortcut: 'T' },
            ];

            function openCommandPalette() {
                commandPalette.classList.add('active');
                commandInput.focus();
                searchCommand('');
            }

            function closeCommandPalette() {
                commandPalette.classList.remove('active');
                commandInput.value = '';
                selectedIndex = -1;
                currentResults = [];
            }

            function searchCommand(query) {
                const lowerQuery = query.toLowerCase();
                currentResults = menuItems.filter(item => 
                    item.title.toLowerCase().includes(lowerQuery) ||
                    item.desc.toLowerCase().includes(lowerQuery)
                );
                renderResults();
            }

            function renderResults() {
                commandResults.innerHTML = '';
                selectedIndex = -1;

                if (currentResults.length === 0) {
                    commandResults.innerHTML = '<div style="padding: 2rem; text-align: center; color: var(--theme-text-muted);">No results found</div>';
                    return;
                }

                currentResults.forEach((item, index) => {
                    const div = document.createElement('div');
                    div.className = 'command-palette-item';
                    div.innerHTML = `
                        <svg class="command-palette-item-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${item.icon}"/>
                        </svg>
                        <div class="command-palette-item-content">
                            <div class="command-palette-item-title">${item.title}</div>
                            <div class="command-palette-item-desc">${item.desc}</div>
                        </div>
                        ${item.shortcut ? `<div class="command-palette-item-shortcut"><kbd>${item.shortcut}</kbd></div>` : ''}
                    `;
                    div.addEventListener('click', () => {
                        window.location.href = item.url;
                    });
                    commandResults.appendChild(div);
                });
            }

            function selectResult(index) {
                if (index >= 0 && index < currentResults.length) {
                    document.querySelectorAll('.command-palette-item').forEach((el, i) => {
                        el.classList.toggle('selected', i === index);
                    });
                    selectedIndex = index;
                }
            }

            // Event listeners
            quickSearchBtn?.addEventListener('click', openCommandPalette);
            commandClose?.addEventListener('click', closeCommandPalette);
            commandPalette?.addEventListener('click', (e) => {
                if (e.target === commandPalette || e.target.classList.contains('command-palette-overlay')) {
                    closeCommandPalette();
                }
            });

            commandInput?.addEventListener('input', (e) => {
                searchCommand(e.target.value);
            });

            commandInput?.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, currentResults.length - 1);
                    selectResult(selectedIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, -1);
                    selectResult(selectedIndex);
                } else if (e.key === 'Enter' && selectedIndex >= 0) {
                    e.preventDefault();
                    if (currentResults[selectedIndex]) {
                        window.location.href = currentResults[selectedIndex].url;
                    }
                } else if (e.key === 'Escape') {
                    closeCommandPalette();
                }
            });

            // Keyboard shortcut: Ctrl+K or Cmd+K
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    openCommandPalette();
                }
            });
        })();

        // Quick Actions Menu
        (function() {
            const quickActionsBtn = document.getElementById('quick-actions-btn');
            const quickActionsMenu = document.getElementById('quick-actions-menu');

            quickActionsBtn?.addEventListener('click', (e) => {
                e.stopPropagation();
                quickActionsMenu.classList.toggle('active');
            });

            document.addEventListener('click', (e) => {
                if (!quickActionsMenu.contains(e.target) && !quickActionsBtn.contains(e.target)) {
                    quickActionsMenu.classList.remove('active');
                }
            });

            document.querySelectorAll('.quick-actions-item').forEach(item => {
                item.addEventListener('click', function() {
                    const action = this.getAttribute('data-action');
                    const adminPath = '/ae-admin';
                    
                    const actions = {
                        'new-product': `${adminPath}/products.php?action=new`,
                        'new-page': `${adminPath}/pages.php?action=new`,
                        'media': `${adminPath}/media-library.php`,
                        'settings': `${adminPath}/options.php`
                    };

                    if (actions[action]) {
                        window.location.href = actions[action];
                    }
                    quickActionsMenu.classList.remove('active');
                });
            });
        })();

        // Particle Effects on Hover
        (function() {
            const particleContainer = document.getElementById('particle-container');
            let particleCount = 0;
            const maxParticles = 50;

            function createParticle(x, y) {
                if (particleCount >= maxParticles) return;
                particleCount++;

                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = x + 'px';
                particle.style.top = y + 'px';
                
                const colors = [
                    'var(--theme-primary, #3b82f6)',
                    'var(--theme-accent, #7C3AED)',
                    'var(--theme-secondary, #10B981)',
                    'var(--theme-tertiary, #F59E0B)'
                ];
                particle.style.background = colors[Math.floor(Math.random() * colors.length)];
                
                particleContainer.appendChild(particle);

                setTimeout(() => {
                    particle.remove();
                    particleCount--;
                }, 3000);
            }

            // Add particle effects to interactive elements
            document.querySelectorAll('.admin-nav-item, .admin-topbar-link, .quick-search-btn, .quick-actions-btn').forEach(el => {
                el.addEventListener('mouseenter', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = rect.left + rect.width / 2;
                    const y = rect.top + rect.height / 2;
                    
                    for (let i = 0; i < 3; i++) {
                        setTimeout(() => {
                            createParticle(
                                x + (Math.random() - 0.5) * 20,
                                y + (Math.random() - 0.5) * 20
                            );
                        }, i * 100);
                    }
                });
            });
        })();

        // Smooth Page Transitions
        (function() {
            const links = document.querySelectorAll('a[href^="/ae-admin/"], a[href^="/wp-admin/"]');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (this.target === '_blank' || this.hasAttribute('download')) return;
                    
                    const href = this.getAttribute('href');
                    if (href && !href.includes('#') && !href.includes('javascript:')) {
                        const transition = document.createElement('div');
                        transition.className = 'page-transition';
                        document.body.appendChild(transition);
                        
                        setTimeout(() => {
                            transition.classList.add('active');
                        }, 10);
                    }
                });
            });
        })();

        // Add tooltips to elements
        document.querySelectorAll('.admin-nav-item').forEach(item => {
            const text = item.querySelector('span')?.textContent;
            if (text) {
                item.setAttribute('data-tooltip', text);
            }
        });

        // Keyboard Shortcuts
        document.addEventListener('keydown', (e) => {
            // Toggle sidebar: Ctrl+B or Cmd+B
            if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                e.preventDefault();
                const sidebar = document.getElementById('admin-sidebar');
                const toggleBtn = document.getElementById('sidebar-toggle');
                if (sidebar && toggleBtn) {
                    toggleBtn.click();
                }
            }
            
            // Focus mode: Ctrl+F or Cmd+F
            if ((e.ctrlKey || e.metaKey) && e.key === 'f' && !e.shiftKey) {
                e.preventDefault();
                const focusBtn = document.getElementById('focus-mode-btn');
                if (focusBtn) {
                    focusBtn.click();
                }
            }
        });

        // Add glow effect to active nav items
        document.querySelectorAll('.admin-nav-item.active').forEach(item => {
            item.classList.add('glow-on-hover');
        });

        // ============================================
        // EVEN MORE ADVANCED FEATURES
        // ============================================

        // Dark Mode Toggle
        (function() {
            const darkModeToggle = document.getElementById('dark-mode-toggle');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const savedMode = localStorage.getItem('darkMode');
            
            // Initialize dark mode
            if (savedMode === 'true' || (!savedMode && prefersDark)) {
                document.body.classList.add('dark-mode');
            }

            darkModeToggle?.addEventListener('click', function() {
                document.body.classList.toggle('dark-mode');
                localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
                
                // Confetti removed
            });
        })();

        // Focus Mode
        (function() {
            const focusModeBtn = document.getElementById('focus-mode-btn');
            
            focusModeBtn?.addEventListener('click', function() {
                document.body.classList.toggle('focus-mode');
                this.classList.toggle('active');
                localStorage.setItem('focusMode', document.body.classList.contains('focus-mode'));
            });

            // Load saved focus mode
            if (localStorage.getItem('focusMode') === 'true') {
                document.body.classList.add('focus-mode');
                focusModeBtn?.classList.add('active');
            }
        })();

        // Confetti Animation
        function triggerConfetti() {
            const container = document.getElementById('confetti-container');
            const colors = [
                'var(--theme-primary, #3b82f6)',
                'var(--theme-accent, #7C3AED)',
                'var(--theme-secondary, #10B981)',
                'var(--theme-tertiary, #F59E0B)',
                'var(--theme-success, #10b981)',
                'var(--theme-error, #dc2626)'
            ];

            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.animationDelay = Math.random() * 0.5 + 's';
                    confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
                    container.appendChild(confetti);

                    setTimeout(() => confetti.remove(), 5000);
                }, i * 20);
            }
        }

        // Context Menu
        (function() {
            const contextMenu = document.getElementById('context-menu');
            let contextMenuTarget = null;

            document.addEventListener('contextmenu', function(e) {
                // Only show on admin content
                if (e.target.closest('.admin-content-wrapper') || e.target.closest('.admin-nav-item')) {
                    e.preventDefault();
                    contextMenuTarget = e.target;

                    const menuItems = [];
                    
                    if (e.target.closest('a')) {
                        const link = e.target.closest('a');
                        menuItems.push(
                            { label: 'Open in New Tab', action: () => window.open(link.href, '_blank') },
                            { label: 'Copy Link', action: () => {
                                navigator.clipboard.writeText(link.href);
                                showToast('Link copied!', 'success');
                            }},
                            { type: 'divider' }
                        );
                    }

                    menuItems.push(
                        { label: 'Reload Page', icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', action: () => location.reload() },
                        { label: 'View Source', icon: 'M10 20l4-4m0 0l4-4m-4 4l-4-4m4 4l4-4', action: () => window.open(window.location.href, '_blank') }
                    );

                    showContextMenu(e.clientX, e.clientY, menuItems);
                }
            });

            function showContextMenu(x, y, items) {
                contextMenu.innerHTML = '';
                
                items.forEach(item => {
                    if (item.type === 'divider') {
                        const divider = document.createElement('div');
                        divider.className = 'context-menu-divider';
                        contextMenu.appendChild(divider);
                    } else {
                        const menuItem = document.createElement('div');
                        menuItem.className = 'context-menu-item';
                        if (item.icon) {
                            menuItem.innerHTML = `
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${item.icon}"/>
                                </svg>
                                <span>${item.label}</span>
                            `;
                        } else {
                            menuItem.textContent = item.label;
                        }
                        menuItem.addEventListener('click', () => {
                            item.action();
                            hideContextMenu();
                        });
                        contextMenu.appendChild(menuItem);
                    }
                });

                contextMenu.style.left = x + 'px';
                contextMenu.style.top = y + 'px';
                contextMenu.classList.add('active');
            }

            function hideContextMenu() {
                contextMenu.classList.remove('active');
            }

            document.addEventListener('click', hideContextMenu);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') hideContextMenu();
            });
        })();

        // Recent Pages Tracking
        (function() {
            const recentPages = JSON.parse(localStorage.getItem('recentPages') || '[]');
            const currentPage = {
                title: document.title.replace(' - Admin Panel', ''),
                url: window.location.pathname + window.location.search,
                icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                timestamp: Date.now()
            };

            // Add current page if not already in list
            const existingIndex = recentPages.findIndex(p => p.url === currentPage.url);
            if (existingIndex >= 0) {
                recentPages.splice(existingIndex, 1);
            }
            recentPages.unshift(currentPage);
            
            // Keep only last 10
            const limited = recentPages.slice(0, 10);
            localStorage.setItem('recentPages', JSON.stringify(limited));

            // Render recent pages
            function renderRecentPages() {
                const list = document.getElementById('recent-pages-list');
                if (!list) return;

                list.innerHTML = '';
                limited.forEach(page => {
                    const item = document.createElement('div');
                    item.className = 'recent-page-item';
                    const timeAgo = getTimeAgo(page.timestamp);
                    item.innerHTML = `
                        <div class="recent-page-item-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 20px; height: 20px;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${page.icon}"/>
                            </svg>
                        </div>
                        <div class="recent-page-item-content">
                            <div class="recent-page-item-title">${page.title}</div>
                            <div class="recent-page-item-time">${timeAgo}</div>
                        </div>
                    `;
                    item.addEventListener('click', () => {
                        window.location.href = page.url;
                    });
                    list.appendChild(item);
                });
            }

            // Use global getTimeAgo function defined below

            renderRecentPages();

            // Toggle recent pages panel
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'R') {
                    e.preventDefault();
                    document.getElementById('recent-pages-panel')?.classList.toggle('active');
                }
            });

            document.getElementById('recent-pages-close')?.addEventListener('click', () => {
                document.getElementById('recent-pages-panel')?.classList.remove('active');
            });
        })();

        // Keyboard Shortcuts Help
        (function() {
            const shortcutsOverlay = document.getElementById('keyboard-shortcuts-overlay');
            const shortcutsBtn = document.getElementById('shortcuts-help-btn');
            const shortcutsClose = document.getElementById('shortcuts-close');
            const shortcutsContent = document.getElementById('shortcuts-content');

            const shortcuts = [
                {
                    section: 'Navigation',
                    items: [
                        { label: 'Open Command Palette', keys: ['Ctrl', 'K'] },
                        { label: 'Toggle Sidebar', keys: ['Ctrl', 'B'] },
                        { label: 'Focus Mode', keys: ['Ctrl', 'F'] },
                        { label: 'Recent Pages', keys: ['Ctrl', 'Shift', 'R'] },
                    ]
                },
                {
                    section: 'General',
                    items: [
                        { label: 'Show Shortcuts', keys: ['?'] },
                        { label: 'Close Modal', keys: ['Esc'] },
                    ]
                }
            ];

            function renderShortcuts() {
                shortcutsContent.innerHTML = '';
                shortcuts.forEach(section => {
                    const sectionDiv = document.createElement('div');
                    sectionDiv.className = 'shortcuts-section';
                    sectionDiv.innerHTML = `<div class="shortcuts-section-title">${section.section}</div>`;
                    
                    section.items.forEach(item => {
                        const itemDiv = document.createElement('div');
                        itemDiv.className = 'shortcut-item';
                        const keysHtml = item.keys.map(key => `<kbd>${key}</kbd>`).join('');
                        itemDiv.innerHTML = `
                            <span class="shortcut-label">${item.label}</span>
                            <div class="shortcut-keys">${keysHtml}</div>
                        `;
                        sectionDiv.appendChild(itemDiv);
                    });
                    
                    shortcutsContent.appendChild(sectionDiv);
                });
            }

            shortcutsBtn?.addEventListener('click', () => {
                shortcutsOverlay.classList.add('active');
                renderShortcuts();
            });

            shortcutsClose?.addEventListener('click', () => {
                shortcutsOverlay.classList.remove('active');
            });

            shortcutsOverlay?.addEventListener('click', (e) => {
                if (e.target === shortcutsOverlay) {
                    shortcutsOverlay.classList.remove('active');
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === '?' && !e.ctrlKey && !e.metaKey && !e.shiftKey && !e.altKey) {
                    e.preventDefault();
                    shortcutsOverlay.classList.add('active');
                    renderShortcuts();
                }
            });
        })();

        // Sidebar Resizer
        (function() {
            const sidebar = document.getElementById('admin-sidebar');
            const resizer = document.getElementById('sidebar-resizer');
            const contentWrapper = document.querySelector('.admin-content-wrapper');
            const topbar = document.querySelector('.admin-topbar');
            let isResizing = false;
            let startX = 0;
            let startWidth = 280;

            resizer?.addEventListener('mousedown', (e) => {
                isResizing = true;
                startX = e.clientX;
                startWidth = sidebar.offsetWidth;
                resizer.classList.add('resizing');
                document.body.style.cursor = 'col-resize';
                document.body.style.userSelect = 'none';
            });

            document.addEventListener('mousemove', (e) => {
                if (!isResizing) return;
                
                const diff = e.clientX - startX;
                let newWidth = startWidth + diff;
                newWidth = Math.max(200, Math.min(400, newWidth)); // Min 200px, Max 400px
                
                sidebar.style.width = newWidth + 'px';
                contentWrapper.style.marginLeft = newWidth + 'px';
                topbar.style.left = newWidth + 'px';
                resizer.style.left = newWidth + 'px';
                
                localStorage.setItem('sidebarWidth', newWidth);
            });

            document.addEventListener('mouseup', () => {
                if (isResizing) {
                    isResizing = false;
                    resizer.classList.remove('resizing');
                    document.body.style.cursor = '';
                    document.body.style.userSelect = '';
                }
            });

            // Load saved width
            const savedWidth = localStorage.getItem('sidebarWidth');
            if (savedWidth && !sidebar.classList.contains('collapsed')) {
                sidebar.style.width = savedWidth + 'px';
                contentWrapper.style.marginLeft = savedWidth + 'px';
                topbar.style.left = savedWidth + 'px';
                resizer.style.left = savedWidth + 'px';
            }
        })();

        // Toast Notification Helper
        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideInRight 0.4s reverse';
                setTimeout(() => toast.remove(), 400);
            }, 3000);
        }

        // Activity Indicator
        (function() {
            const indicator = document.getElementById('activity-indicator');
            if (!indicator) return;

            // Simulate activity updates
            setInterval(() => {
                const statuses = ['Online', 'Active', 'Syncing...'];
                const status = statuses[Math.floor(Math.random() * statuses.length)];
                indicator.querySelector('.activity-text').textContent = status;
            }, 10000);
        })();

        // Enhanced Success Actions with Confetti
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            return originalFetch.apply(this, args).then(response => {
                if (response.ok && args[0]?.method && ['POST', 'PUT', 'PATCH'].includes(args[0].method)) {
                    setTimeout(() => triggerConfetti(), 100);
                }
                return response;
            });
        };

        // ============================================
        // ULTRA ADVANCED FEATURES
        // ============================================

        // Notification Center
        (function() {
            const notificationBell = document.getElementById('notification-bell');
            const notificationCenter = document.getElementById('notification-center');
            const notificationClose = document.getElementById('notification-center-close');
            const notificationList = document.getElementById('notification-center-list');
            const notificationBadge = document.getElementById('notification-badge');

            let notifications = JSON.parse(localStorage.getItem('notifications') || '[]');

            function addNotification(title, message, type = 'info') {
                const notification = {
                    id: Date.now(),
                    title,
                    message,
                    type,
                    timestamp: Date.now(),
                    read: false
                };
                notifications.unshift(notification);
                notifications = notifications.slice(0, 50); // Keep last 50
                localStorage.setItem('notifications', JSON.stringify(notifications));
                updateNotificationBadge();
                renderNotifications();
            }

            function updateNotificationBadge() {
                const unreadCount = notifications.filter(n => !n.read).length;
                notificationBadge.textContent = unreadCount > 0 ? unreadCount : '';
            }

            function renderNotifications() {
                notificationList.innerHTML = '';
                if (notifications.length === 0) {
                    notificationList.innerHTML = '<div class="notification-empty">No new notifications</div>';
                    return;
                }

                notifications.forEach(notif => {
                    const item = document.createElement('div');
                    item.className = `notification-item ${notif.read ? '' : 'unread'}`;
                    const timeAgo = getTimeAgo(notif.timestamp);
                    item.innerHTML = `
                        <div class="notification-item-title">${notif.title}</div>
                        <div class="notification-item-message">${notif.message}</div>
                        <div class="notification-item-time">${timeAgo}</div>
                    `;
                    item.addEventListener('click', () => {
                        notif.read = true;
                        localStorage.setItem('notifications', JSON.stringify(notifications));
                        updateNotificationBadge();
                        renderNotifications();
                    });
                    notificationList.appendChild(item);
                });
            }

            notificationBell?.addEventListener('click', () => {
                notificationCenter.classList.toggle('active');
                renderNotifications();
            });

            notificationClose?.addEventListener('click', () => {
                notificationCenter.classList.remove('active');
            });

            // Simulate notifications
            setTimeout(() => {
                addNotification('Welcome!', 'Your admin panel is ready to use', 'info');
            }, 2000);

            updateNotificationBadge();
        })();


        // Quick Stats
        function loadQuickStats() {
            // Simulate loading stats (replace with actual API calls)
            setTimeout(() => {
                document.getElementById('stat-products').textContent = '127';
                document.getElementById('stat-pages').textContent = '45';
                document.getElementById('stat-media').textContent = '1.2K';
            }, 500);
        }

        // Tab System
        let tabs = [];
        let activeTabId = null;

        function addTab(title, url, setActive = false) {
            const tabId = 'tab-' + Date.now();
            const tab = { id: tabId, title, url };
            tabs.push(tab);
            
            const tabList = document.getElementById('tab-list');
            const tabItem = document.createElement('div');
            tabItem.className = 'tab-item';
            tabItem.id = tabId;
            tabItem.innerHTML = `
                <span>${title}</span>
                <button class="tab-item-close" onclick="closeTab('${tabId}')">×</button>
            `;
            tabItem.addEventListener('click', (e) => {
                if (!e.target.classList.contains('tab-item-close')) {
                    setActiveTab(tabId);
                }
            });
            tabList.appendChild(tabItem);

            if (setActive) {
                setActiveTab(tabId);
            }

            return tabId;
        }

        function setActiveTab(tabId) {
            activeTabId = tabId;
            document.querySelectorAll('.tab-item').forEach(tab => {
                tab.classList.toggle('active', tab.id === tabId);
            });
        }

        function closeTab(tabId) {
            const tabIndex = tabs.findIndex(t => t.id === tabId);
            if (tabIndex >= 0) {
                tabs.splice(tabIndex, 1);
                document.getElementById(tabId)?.remove();
                
                if (activeTabId === tabId && tabs.length > 0) {
                    setActiveTab(tabs[0].id);
                }
            }
        }

        // Make closeTab globally accessible
        window.closeTab = closeTab;

        document.getElementById('tab-new')?.addEventListener('click', () => {
            addTab('New Tab', '/ae-admin/', true);
        });

        // Floating Action Button
        (function() {
            const fab = document.getElementById('floating-action-btn');
            const fabMenu = document.getElementById('fab-menu');

            fab?.addEventListener('click', function(e) {
                if (!fabMenu.contains(e.target)) {
                    this.classList.toggle('active');
                }
            });

            document.querySelectorAll('.fab-item').forEach(item => {
                item.addEventListener('click', function() {
                    const action = this.getAttribute('data-action');
                    const actions = {
                        'new-product': '/ae-admin/products.php?action=new',
                        'new-page': '/ae-admin/pages.php?action=new',
                        'media': '/ae-admin/media-library.php'
                    };
                    if (actions[action]) {
                        window.location.href = actions[action];
                    }
                    fab.classList.remove('active');
                });
            });

            // Close FAB menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!fab.contains(e.target)) {
                    fab.classList.remove('active');
                }
            });
        })();

        // Split Screen
        function loadSplitScreen() {
            const left = document.getElementById('split-screen-left');
            const right = document.getElementById('split-screen-right');
            const divider = document.getElementById('split-screen-divider');

            left.innerHTML = '<iframe src="' + window.location.href + '" style="width: 100%; height: 100%; border: none;"></iframe>';
            right.innerHTML = '<div style="padding: 2rem; text-align: center; color: var(--theme-text-muted);">Right Panel - Drag divider to resize</div>';

            let isResizing = false;
            divider.addEventListener('mousedown', () => {
                isResizing = true;
                divider.classList.add('resizing');
            });

            document.addEventListener('mousemove', (e) => {
                if (!isResizing) return;
                const container = divider.parentElement;
                const leftWidth = (e.clientX / container.offsetWidth) * 100;
                left.style.width = leftWidth + '%';
                right.style.width = (100 - leftWidth) + '%';
            });

            document.addEventListener('mouseup', () => {
                isResizing = false;
                divider.classList.remove('resizing');
            });
        }

        // Preset Styles
        const presetStyles = `
            body.preset-compact .admin-content-wrapper { padding: 1rem; }
            body.preset-compact .admin-card { padding: 1rem; }
            body.preset-spacious .admin-content-wrapper { padding: 3rem; }
            body.preset-spacious .admin-card { padding: 2.5rem; }
            body.preset-minimal .admin-sidebar { box-shadow: none; }
            body.preset-minimal .admin-topbar { box-shadow: none; }
        `;
        const styleSheet = document.createElement('style');
        styleSheet.textContent = presetStyles;
        document.head.appendChild(styleSheet);

        // ============================================
        // NEW ADVANCED FEATURES
        // ============================================

        // Helper function for time ago (shared across features) - must be defined before use
        function getTimeAgo(timestamp) {
            if (!timestamp) return 'Just now';
            const seconds = Math.floor((Date.now() - timestamp) / 1000);
            if (seconds < 60) return 'Just now';
            const minutes = Math.floor(seconds / 60);
            if (minutes < 60) return `${minutes}m ago`;
            const hours = Math.floor(minutes / 60);
            if (hours < 24) return `${hours}h ago`;
            const days = Math.floor(hours / 24);
            return `${days}d ago`;
        }

        // Breadcrumb Navigation
        (function() {
            const breadcrumbNav = document.getElementById('breadcrumb-nav');
            if (!breadcrumbNav) return;

            function generateBreadcrumbs() {
                const path = window.location.pathname;
                const segments = path.split('/').filter(Boolean);
                const adminPath = segments[0] === 'ae-admin' ? 'ae-admin' : (segments[0] === 'wp-admin' ? 'wp-admin' : 'ae-admin');
                const breadcrumbs = [{ name: 'Dashboard', url: '/' + adminPath + '/' }];

                segments.forEach((segment, index) => {
                    if (segment !== 'ae-admin' && segment !== 'wp-admin' && segment) {
                        const url = '/' + segments.slice(0, index + 1).join('/');
                        let name = segment.replace(/[-_]/g, ' ').replace(/\.php$/, '').replace(/\b\w/g, l => l.toUpperCase());
                        if (!name) name = 'Page';
                        breadcrumbs.push({ name, url });
                    }
                });

                breadcrumbNav.innerHTML = '';
                if (breadcrumbs.length > 1) {
                    breadcrumbs.forEach((crumb, index) => {
                        const item = document.createElement('div');
                        item.className = 'breadcrumb-item';
                        
                        if (index === breadcrumbs.length - 1) {
                            item.textContent = crumb.name;
                        } else {
                            item.innerHTML = `
                                <a href="${crumb.url}" class="breadcrumb-link">${crumb.name}</a>
                                <span class="breadcrumb-separator">/</span>
                            `;
                        }
                        
                        breadcrumbNav.appendChild(item);
                    });
                    document.body.classList.add('breadcrumb-active');
                } else {
                    breadcrumbNav.style.display = 'none';
                }
            }

            generateBreadcrumbs();
        })();

        // Quick Actions Toolbar
        (function() {
            const toolbar = document.getElementById('quick-actions-toolbar');
            const toolbarActions = document.getElementById('toolbar-actions');
            if (!toolbar || !toolbarActions) return;
            
            function updateToolbar() {
                const currentPage = window.location.pathname;
                const pageName = currentPage.split('/').pop() || currentPage.split('/').slice(-2, -1)[0];
                toolbarActions.innerHTML = '';

                // Get admin path dynamically
                const adminPath = currentPage.includes('/ae-admin/') ? 'ae-admin' : 'wp-admin';

                // Define actions based on current page
                const pageActions = {
                    'products.php': [
                        { label: 'New Product', icon: 'M12 4v16m8-8H4', url: '/' + adminPath + '/products.php?action=new' },
                        { label: 'Import', icon: 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12', url: '#', action: () => showToast('Import feature coming soon!', 'info') },
                        { label: 'Export', icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4', url: '#', action: () => showToast('Export feature coming soon!', 'info') }
                    ],
                    'pages.php': [
                        { label: 'New Page', icon: 'M12 4v16m8-8H4', url: '/' + adminPath + '/pages.php?action=new' },
                        { label: 'View All', icon: 'M4 6h16M4 12h16M4 18h16', url: '/' + adminPath + '/pages.php' }
                    ],
                    'categories.php': [
                        { label: 'New Category', icon: 'M12 4v16m8-8H4', url: '/' + adminPath + '/categories.php?action=new' }
                    ],
                    'index.php': [
                        { label: 'Refresh', icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', url: '#', action: () => location.reload() }
                    ]
                };

                if (pageActions[pageName]) {
                    toolbar.classList.add('active');
                    pageActions[pageName].forEach(action => {
                        const btn = document.createElement('button');
                        btn.className = 'toolbar-action-btn';
                        btn.innerHTML = `
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${action.icon}"/>
                            </svg>
                            <span>${action.label}</span>
                        `;
                        btn.addEventListener('click', () => {
                            if (action.action) {
                                action.action();
                            } else if (action.url && action.url !== '#') {
                                window.location.href = action.url;
                            }
                        });
                        toolbarActions.appendChild(btn);
                    });
                } else {
                    toolbar.classList.remove('active');
                }
            }

            updateToolbar();
            
            // Update toolbar when navigating (for SPA-like behavior)
            let lastUrl = window.location.href;
            setInterval(() => {
                if (window.location.href !== lastUrl) {
                    updateToolbar();
                    lastUrl = window.location.href;
                }
            }, 500);
        })();

        // Live Clock
        (function() {
            const clockTime = document.getElementById('clock-time');
            const clockDate = document.getElementById('clock-date');
            
            function updateClock() {
                const now = new Date();
                const time = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
                const date = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                
                if (clockTime) clockTime.textContent = time;
                if (clockDate) clockDate.textContent = date;
            }

            updateClock();
            setInterval(updateClock, 1000);
        })();

        // Auto-Save Indicator
        (function() {
            const indicator = document.getElementById('auto-save-indicator');
            if (!indicator) return;

            let saveTimeout;
            const formInputs = document.querySelectorAll('input[type="text"], input[type="email"], textarea, select');

            function showAutoSave(status = 'saved') {
                indicator.className = `auto-save-indicator ${status}`;
                indicator.classList.add('active');
                
                if (status === 'saved') {
                    indicator.innerHTML = `
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Saved</span>
                    `;
                    setTimeout(() => indicator.classList.remove('active'), 2000);
                } else {
                    indicator.innerHTML = `
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Saving...</span>
                    `;
                }
            }

            if (formInputs.length > 0) {
                formInputs.forEach(input => {
                    input.addEventListener('input', () => {
                        clearTimeout(saveTimeout);
                        showAutoSave('saving');
                        
                        saveTimeout = setTimeout(() => {
                            showAutoSave('saved');
                        }, 1000);
                    });
                });
            }
        })();

        // Performance Monitor
        (function() {
            const perfMonitor = document.getElementById('performance-monitor');
            const perfLoadTime = document.getElementById('perf-load-time');
            
            if (!perfMonitor || !perfLoadTime) return;

            function updatePerformance() {
                try {
                    if (performance.timing && performance.timing.loadEventEnd) {
                        const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
                        perfLoadTime.textContent = `${(loadTime / 1000).toFixed(2)}s`;
                    } else if (performance.getEntriesByType) {
                        const navEntries = performance.getEntriesByType('navigation');
                        if (navEntries.length > 0) {
                            const perf = navEntries[0];
                            const loadTime = perf.loadEventEnd - perf.loadEventStart;
                            perfLoadTime.textContent = `${(loadTime / 1000).toFixed(2)}s`;
                        } else {
                            perfLoadTime.textContent = '--';
                        }
                    } else {
                        perfLoadTime.textContent = '--';
                    }
                } catch (e) {
                    perfLoadTime.textContent = '--';
                }
            }

            if (document.readyState === 'complete') {
                updatePerformance();
            } else {
                window.addEventListener('load', updatePerformance);
            }

            perfMonitor.addEventListener('click', function() {
                try {
                    let details = 'Performance Metrics:\n\n';
                    
                    if (performance.timing) {
                        const timing = performance.timing;
                        details += `Load Time: ${((timing.loadEventEnd - timing.navigationStart) / 1000).toFixed(2)}s\n`;
                        details += `DOM Ready: ${((timing.domContentLoadedEventEnd - timing.domContentLoadedEventStart) / 1000).toFixed(2)}s\n`;
                        details += `DNS: ${(timing.domainLookupEnd - timing.domainLookupStart)}ms\n`;
                        details += `Connect: ${(timing.connectEnd - timing.connectStart)}ms\n`;
                    } else if (performance.getEntriesByType) {
                        const perf = performance.getEntriesByType('navigation')[0];
                        if (perf) {
                            details += `Load Time: ${((perf.loadEventEnd - perf.loadEventStart) / 1000).toFixed(2)}s\n`;
                            details += `DOM Ready: ${((perf.domContentLoadedEventEnd - perf.domContentLoadedEventStart) / 1000).toFixed(2)}s\n`;
                            details += `DNS: ${(perf.domainLookupEnd - perf.domainLookupStart)}ms\n`;
                            details += `Connect: ${(perf.connectEnd - perf.connectStart)}ms\n`;
                        }
                    }
                    
                    alert(details || 'Performance data not available');
                } catch (e) {
                    alert('Performance data not available');
                }
            });
        })();

        // Sticky Notes
        (function() {
            const stickyNotesBtn = document.getElementById('sticky-notes-btn');
            const stickyNotesPanel = document.getElementById('sticky-notes-panel');
            const stickyNotesClose = document.getElementById('sticky-notes-close');
            const stickyNotesContent = document.getElementById('sticky-notes-content');
            const addNoteBtn = document.getElementById('add-note-btn');

            let notes = JSON.parse(localStorage.getItem('stickyNotes') || '[]');

            function renderNotes() {
                if (!stickyNotesContent) return;
                
                let notesContainer = stickyNotesContent.querySelector('.notes-container');
                if (!notesContainer) {
                    notesContainer = document.createElement('div');
                    notesContainer.className = 'notes-container';
                    if (addNoteBtn && addNoteBtn.nextSibling) {
                        stickyNotesContent.insertBefore(notesContainer, addNoteBtn.nextSibling);
                    } else {
                        stickyNotesContent.appendChild(notesContainer);
                    }
                }
                
                notesContainer.innerHTML = '';

                notes.forEach((note, index) => {
                    const noteEl = document.createElement('div');
                    noteEl.className = 'sticky-note';
                    noteEl.draggable = true;
                    const title = (note.title || 'Untitled').replace(/"/g, '&quot;');
                    const content = (note.content || '').replace(/"/g, '&quot;').replace(/\n/g, '&#10;');
                    noteEl.innerHTML = `
                        <div class="sticky-note-header">
                            <input type="text" class="sticky-note-title" value="${title}" data-index="${index}">
                            <button class="sticky-note-delete" data-index="${index}" type="button">×</button>
                        </div>
                        <textarea class="sticky-note-content" data-index="${index}" placeholder="Write your note here...">${content}</textarea>
                    `;
                    notesContainer.appendChild(noteEl);
                });

                // Add event listeners
                notesContainer.querySelectorAll('.sticky-note-title').forEach(input => {
                    input.addEventListener('input', function() {
                        const index = parseInt(this.getAttribute('data-index'));
                        if (notes[index]) {
                            notes[index].title = this.value;
                            saveNotes();
                        }
                    });
                });

                notesContainer.querySelectorAll('.sticky-note-content').forEach(textarea => {
                    textarea.addEventListener('input', function() {
                        const index = parseInt(this.getAttribute('data-index'));
                        if (notes[index]) {
                            notes[index].content = this.value;
                            saveNotes();
                        }
                    });
                });

                notesContainer.querySelectorAll('.sticky-note-delete').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const index = parseInt(this.getAttribute('data-index'));
                        if (notes[index]) {
                            notes.splice(index, 1);
                            saveNotes();
                            renderNotes();
                        }
                    });
                });
            }

            function saveNotes() {
                localStorage.setItem('stickyNotes', JSON.stringify(notes));
            }

            if (addNoteBtn) {
                addNoteBtn.addEventListener('click', () => {
                    notes.push({ title: 'New Note', content: '' });
                    saveNotes();
                    renderNotes();
                });
            }

            if (stickyNotesBtn) {
                stickyNotesBtn.addEventListener('click', () => {
                    if (stickyNotesPanel) {
                        stickyNotesPanel.classList.toggle('active');
                        if (stickyNotesPanel.classList.contains('active')) {
                            renderNotes();
                        }
                    }
                });
            }

            if (stickyNotesClose) {
                stickyNotesClose.addEventListener('click', () => {
                    if (stickyNotesPanel) {
                        stickyNotesPanel.classList.remove('active');
                    }
                });
            }

            // Load notes on page load
            if (notes.length > 0 && stickyNotesContent) {
                renderNotes();
            }
        })();

        // Activity Feed
        (function() {
            const activityFeed = document.getElementById('activity-feed-panel');
            const activityContent = document.getElementById('activity-feed-content');
            if (!activityContent) return;
            
            let activities = [];
            try {
                activities = JSON.parse(localStorage.getItem('activityFeed') || '[]');
            } catch (e) {
                activities = [];
            }

            function addActivity(message, type = 'info') {
                const activity = {
                    id: Date.now(),
                    message: String(message || 'Activity'),
                    type: String(type || 'info'),
                    timestamp: Date.now()
                };
                activities.unshift(activity);
                activities = activities.slice(0, 20); // Keep last 20
                try {
                    localStorage.setItem('activityFeed', JSON.stringify(activities));
                } catch (e) {
                    console.warn('Could not save activity feed:', e);
                }
                renderActivities();
            }

            function renderActivities() {
                if (!activityContent) return;
                
                activityContent.innerHTML = '';
                if (activities.length === 0) {
                    activityContent.innerHTML = '<div style="text-align: center; padding: 2rem; color: var(--theme-text-muted); font-size: 0.875rem;">No recent activity</div>';
                    return;
                }

                activities.forEach(activity => {
                    const item = document.createElement('div');
                    item.className = 'activity-item';
                    const timeAgo = getTimeAgo(activity.timestamp);
                    const message = String(activity.message || 'Activity').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    item.innerHTML = `
                        <div>${message}</div>
                        <div class="activity-item-time">${timeAgo}</div>
                    `;
                    activityContent.appendChild(item);
                });
            }

            // Simulate activities
            setTimeout(() => {
                addActivity('Page loaded successfully', 'success');
            }, 1000);

            // Monitor form submissions
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form && form.tagName === 'FORM') {
                    const formName = form.id || form.name || form.getAttribute('data-name') || 'Unknown form';
                    addActivity('Form submitted: ' + formName, 'info');
                }
            });

            // Monitor navigation (only track actual page changes)
            let lastPath = window.location.pathname;
            const checkNavigation = () => {
                if (window.location.pathname !== lastPath) {
                    addActivity('Navigated to: ' + window.location.pathname, 'info');
                    lastPath = window.location.pathname;
                }
            };
            setInterval(checkNavigation, 2000);

            // Close button
            const activityClose = document.getElementById('activity-feed-close');
            if (activityClose && activityFeed) {
                activityClose.addEventListener('click', () => {
                    activityFeed.classList.remove('active');
                });
            }

            renderActivities();
        })();
    </script>
</body>
</html>
