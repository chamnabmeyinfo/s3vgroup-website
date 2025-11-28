/**
 * Elementor-style Homepage Builder v2
 * Full-featured drag-and-drop visual editor
 */

(function() {
    'use strict';

    let canvasFrame = null;
    let settingsPanel = null;
    let settingsContent = null;
    let settingsTitle = null;
    let saveBtn = null;
    
    let selectedSection = null;
    let sections = []; // Array of { id, element, data }
    let sectionsData = new Map(); // Map of section ID to full data
    let sortableInstance = null;
    let hasChanges = false;

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    async function init() {
        // Get elements after DOM is ready
        canvasFrame = document.getElementById('canvas-frame');
        settingsPanel = document.getElementById('settings-panel');
        settingsContent = document.getElementById('settings-content');
        settingsTitle = document.getElementById('settings-title');
        saveBtn = document.getElementById('save-builder-btn');
        
        if (!canvasFrame) {
            console.error('Canvas frame not found!');
            return;
        }
        
        // Ensure nothing is blocking clicks
        document.body.style.pointerEvents = 'auto';
        const builder = document.querySelector('.homepage-builder-v2');
        if (builder) {
            builder.style.pointerEvents = 'auto';
        }
        
        await loadSections();
        
        // Attach click handlers to all existing sections
        attachClickHandlersToSections();
        
        initDragAndDrop();
        initCanvasInteractions();
        initSettingsTabs();
        initResponsiveControls();
        initSaveButton();
        initElementSearch();
        attachLivePreviewHandlers();
        updateSaveButtonState();
        
        // Clean up any stuck drag states immediately
        setTimeout(() => {
            if (canvasFrame) {
                canvasFrame.classList.remove('drag-over-active');
                canvasFrame.style.backgroundColor = '';
                canvasFrame.style.border = '';
                canvasFrame.style.boxShadow = '';
                canvasFrame.style.animation = '';
            }
            document.querySelectorAll('.dragging-ghost').forEach(el => {
                try {
                    if (el && el.parentNode) el.parentNode.removeChild(el);
                } catch(e) {}
            });
        }, 100);
        
        console.log('Builder initialized - all elements should be clickable');
    }

    // Attach live preview handlers
    function attachLivePreviewHandlers() {
        // Auto-save on changes (debounced)
        let saveTimeout;
        const debouncedSave = () => {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                if (selectedSection) {
                    const sectionData = getSectionData(selectedSection);
                    saveSection(selectedSection.dataset.id, sectionData).catch(console.error);
                }
            }, 1000);
        };

        // Watch for style changes
        const observer = new MutationObserver(debouncedSave);
        document.querySelectorAll('.canvas-section').forEach(section => {
            observer.observe(section, {
                attributes: true,
                attributeFilter: ['style'],
                subtree: true
            });
        });
    }

    // Load sections from DOM and fetch data from server
    async function loadSections() {
        try {
            const pageId = getPageId();
            const url = pageId ? `/api/admin/homepage/index.php?page_id=${encodeURIComponent(pageId)}` : '/api/admin/homepage/index.php';
            const response = await fetch(url);
            const result = await response.json();
            
            if (result.status === 'success') {
                // Store sections data
                sectionsData.clear();
                result.sections.forEach(section => {
                    sectionsData.set(section.id, section);
                });
                
                // Build sections array
                sections = Array.from(document.querySelectorAll('.canvas-section')).map(sectionEl => {
                    const id = sectionEl.dataset.id;
                    const data = sectionsData.get(id) || getSectionDataFromElement(sectionEl);
                    if (!sectionsData.has(id)) {
                        sectionsData.set(id, data);
                    }
                    return {
                        id: id,
                        element: sectionEl,
                        data: data
                    };
                });
                
                hasChanges = false;
            }
        } catch (error) {
            console.error('Error loading sections:', error);
            // Fallback: load from DOM only
            sections = Array.from(document.querySelectorAll('.canvas-section')).map(section => ({
                id: section.dataset.id,
                element: section,
                data: getSectionDataFromElement(section)
            }));
        }
    }
    
    // Get section data from element attributes (fallback)
    function getSectionDataFromElement(sectionElement) {
        const id = sectionElement.dataset.id;
        const existingData = sectionsData.get(id);
        if (existingData) return existingData;
        
        return {
            id: id,
            page_id: getPageId(),
            section_type: sectionElement.dataset.type || 'custom',
            title: sectionElement.dataset.title || '',
            content: JSON.parse(sectionElement.dataset.content || '{}'),
            settings: JSON.parse(sectionElement.dataset.settings || '{}'),
            status: sectionElement.dataset.status || 'ACTIVE',
            order_index: parseInt(sectionElement.dataset.order || '0')
        };
    }

    // Initialize drag and drop from elements panel
    function initDragAndDrop() {
        console.log('Initializing drag and drop...');
        console.log('Canvas frame:', canvasFrame);
        
        if (!canvasFrame) {
            console.error('Canvas frame not initialized for drag and drop');
            return;
        }
        
        const elementItems = document.querySelectorAll('.element-item');
        console.log('Element items found:', elementItems.length);
        
        if (elementItems.length === 0) {
            console.error('No element items found for drag and drop!');
            return;
        }
        
        elementItems.forEach((item) => {
            // Ensure draggable
            item.setAttribute('draggable', 'true');
            item.style.cursor = 'grab';
            item.style.userSelect = 'none';
            
            // Remove old listeners by cloning
            const newItem = item.cloneNode(true);
            item.parentNode.replaceChild(newItem, item);
            
            // Add event listeners to the new item
            newItem.addEventListener('dragstart', function(e) {
                console.log('Drag started for:', this.dataset.type);
                e.dataTransfer.effectAllowed = 'copy';
                
                const dragData = {
                    type: this.dataset.type,
                    defaults: JSON.parse(this.dataset.defaults || '{}')
                };
                
                console.log('Drag data:', dragData);
                
                // Set data in multiple formats for compatibility
                try {
                    e.dataTransfer.setData('application/json', JSON.stringify(dragData));
                } catch (err) {
                    console.warn('Could not set JSON data:', err);
                }
                try {
                    e.dataTransfer.setData('text/plain', this.dataset.type);
                } catch (err) {
                    console.warn('Could not set text data:', err);
                }
                try {
                    e.dataTransfer.setData('text/html', JSON.stringify(dragData));
                } catch (err) {
                    console.warn('Could not set HTML data:', err);
                }
                
                // Create custom drag image
                const dragImage = this.cloneNode(true);
                dragImage.style.position = 'absolute';
                dragImage.style.top = '-1000px';
                dragImage.style.left = '-1000px';
                dragImage.style.width = this.offsetWidth + 'px';
                dragImage.style.height = this.offsetHeight + 'px';
                dragImage.style.opacity = '0.8';
                dragImage.style.transform = 'rotate(5deg)';
                dragImage.style.boxShadow = '0 4px 12px rgba(0,0,0,0.3)';
                dragImage.style.border = '2px dashed #0b3a63';
                dragImage.style.backgroundColor = '#fff';
                dragImage.className += ' dragging-ghost';
                document.body.appendChild(dragImage);
                
                // Set custom drag image
                const rect = this.getBoundingClientRect();
                e.dataTransfer.setDragImage(dragImage, rect.width / 2, rect.height / 2);
                
                // Visual feedback on original element
                this.style.opacity = '0.3';
                this.style.transform = 'scale(0.95)';
                this.style.cursor = 'grabbing';
                this.style.transition = 'all 0.2s';
                
                // Remove drag image after a delay
                setTimeout(() => {
                    if (dragImage.parentNode) {
                        dragImage.parentNode.removeChild(dragImage);
                    }
                }, 0);
            });
            
            newItem.addEventListener('dragend', function(e) {
                console.log('Drag ended');
                this.style.opacity = '1';
                this.style.transform = 'scale(1)';
                this.style.cursor = 'grab';
                
                // Remove any leftover drag images
                document.querySelectorAll('.dragging-ghost').forEach(el => el.remove());
            });
            
            // Add hover state for better visual feedback
            newItem.addEventListener('mouseenter', function() {
                if (!this.classList.contains('dragging')) {
                    this.style.backgroundColor = '#f0f7ff';
                    this.style.transform = 'translateX(4px)';
                }
            });
            
            newItem.addEventListener('mouseleave', function() {
                if (!this.classList.contains('dragging')) {
                    this.style.backgroundColor = '';
                    this.style.transform = '';
                }
            });
        });

        // Make canvas a drop zone - use capture phase to ensure it fires
        let dragOverCount = 0;
        
        canvasFrame.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.dataTransfer.dropEffect = 'copy';
            
            dragOverCount++;
            
            // Visual feedback
            this.style.backgroundColor = '#e0f2fe';
            this.style.border = '3px dashed #0b3a63';
            this.style.boxShadow = '0 0 20px rgba(11, 58, 99, 0.3)';
            this.style.transition = 'all 0.2s';
            
            // Add pulsing animation
            if (!this.classList.contains('drag-over-active')) {
                this.classList.add('drag-over-active');
                this.style.animation = 'pulse-border 1s ease-in-out infinite';
            }
            
            console.log('Dragging over canvas');
        }, true);

        canvasFrame.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            dragOverCount--;
            
            const relatedTarget = e.relatedTarget;
            // Only remove visual feedback if we're actually leaving the canvas
            if (dragOverCount <= 0 || (!this.contains(relatedTarget) && relatedTarget !== this && !this.contains(relatedTarget?.parentElement))) {
                this.style.backgroundColor = '';
                this.style.border = '';
                this.style.boxShadow = '';
                this.style.animation = '';
                this.classList.remove('drag-over-active');
                dragOverCount = 0;
                console.log('Left canvas area');
            }
        }, true);
        
        // Reset on drag end - ensure cleanup happens
        document.addEventListener('dragend', function(e) {
            console.log('Global dragend event');
            if (canvasFrame) {
                canvasFrame.style.backgroundColor = '';
                canvasFrame.style.border = '';
                canvasFrame.style.boxShadow = '';
                canvasFrame.style.animation = '';
                canvasFrame.classList.remove('drag-over-active');
                dragOverCount = 0;
            }
            // Remove any leftover drag ghosts
            document.querySelectorAll('.dragging-ghost').forEach(el => {
                if (el.parentNode) el.parentNode.removeChild(el);
            });
        }, true);
        
        // Also reset on drop
        canvasFrame.addEventListener('drop', function(e) {
            // Cleanup is done in the drop handler, but ensure it's called
            setTimeout(() => {
                this.classList.remove('drag-over-active');
                dragOverCount = 0;
            }, 100);
        });

        canvasFrame.addEventListener('drop', async function(e) {
            console.log('DROP EVENT FIRED!');
            e.preventDefault();
            e.stopPropagation();
            this.style.backgroundColor = '';
            this.style.border = '';
            
            let data = null;
            
            // Try multiple ways to get the data
            try {
                // Try application/json first
                const jsonData = e.dataTransfer.getData('application/json');
                if (jsonData) {
                    data = JSON.parse(jsonData);
                    console.log('Got JSON data:', data);
                }
            } catch (err) {
                console.log('JSON parse failed, trying other methods');
            }
            
            if (!data) {
                try {
                    // Try text/html
                    const htmlData = e.dataTransfer.getData('text/html');
                    if (htmlData) {
                        data = JSON.parse(htmlData);
                        console.log('Got HTML data:', data);
                    }
                } catch (err) {
                    console.log('HTML parse failed');
                }
            }
            
            if (!data) {
                // Fallback to text/plain and find element
                const type = e.dataTransfer.getData('text/plain');
                console.log('Got text type:', type);
                if (type) {
                    const elementItem = document.querySelector(`[data-type="${type}"]`);
                    if (elementItem) {
                        data = {
                            type: type,
                            defaults: JSON.parse(elementItem.dataset.defaults || '{}')
                        };
                        console.log('Reconstructed data from element:', data);
                    }
                }
            }
            
            if (data && data.type) {
                console.log('Creating section with data:', data);
                try {
                    await createNewSection(data.type, data.defaults || {});
                    console.log('Section created successfully!');
                } catch (error) {
                    console.error('Error in createNewSection:', error);
                    alert('Error creating section: ' + error.message);
                }
            } else {
                console.error('No valid data found for drop:', {
                    types: Array.from(e.dataTransfer.types),
                    files: e.dataTransfer.files.length,
                    dataTransfer: e.dataTransfer
                });
                    alert('Could not create section. No data found in drop event.\n\nCheck console for details.');
            }
            
            // Clean up drag state after drop
            setTimeout(cleanupDragState, 100);
        }, true);
        
        console.log('Drag and drop initialized successfully');

        // Make sections sortable (reinitialize after sections are loaded)
        initSortable();
    }
    
    // Initialize SortableJS for section reordering
    function initSortable() {
        if (!canvasFrame || typeof Sortable === 'undefined') {
            console.warn('SortableJS not available or canvas frame not found');
            return;
        }
        
        // Destroy existing instance if any
        if (sortableInstance) {
            sortableInstance.destroy();
            sortableInstance = null;
        }
        
        try {
            sortableInstance = new Sortable(canvasFrame, {
                animation: 150,
                handle: '.drag-handle',
                draggable: '.canvas-section',
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                filter: '.element-item', // Don't allow dragging element items
                preventOnFilter: false,
                onStart: function(evt) {
                    console.log('SortableJS drag started for section reordering');
                },
                onEnd: function(evt) {
                    console.log('SortableJS drag ended');
                    updateSectionOrder();
                }
            });
            console.log('SortableJS initialized successfully');
        } catch (error) {
            console.error('Error initializing Sortable:', error);
        }
    }

    // Get page_id from URL or default to null (homepage)
    function getPageId() {
        const params = new URLSearchParams(window.location.search);
        return params.get('page_id') || null;
    }

    // Create new section (adds to DOM, saved on bulk sync)
    async function createNewSection(type, defaults = {}) {
        console.log('createNewSection called with:', { type, defaults });
        
        if (!canvasFrame) {
            console.error('Canvas frame not available, trying to find it...');
            canvasFrame = document.getElementById('canvas-frame');
            if (!canvasFrame) {
                alert('Canvas frame not found. Please refresh the page.');
                throw new Error('Canvas frame not found');
            }
        }
        
        try {
            const pageId = getPageId();
            const tempId = 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            
            const newSectionData = {
                id: tempId,
                page_id: pageId,
                section_type: type,
                title: defaults.title || '',
                content: defaults,
                settings: defaults.settings || {},
                status: 'ACTIVE',
                order_index: sections.length
            };
            
            console.log('New section data:', newSectionData);
            
            // Store in memory
            sectionsData.set(tempId, newSectionData);
            
            // Remove empty canvas message if present
            const emptyCanvas = canvasFrame.querySelector('.empty-canvas');
            if (emptyCanvas) {
                console.log('Removing empty canvas message');
                emptyCanvas.remove();
            }
            
        // Create element
        const sectionEl = createSectionElement(newSectionData);
        console.log('Created section element:', sectionEl);
        
        if (!sectionEl) {
            throw new Error('Failed to create section element');
        }
        
        canvasFrame.appendChild(sectionEl);
        console.log('Section appended to canvas');
        
        // Mark section as having click handler (will be attached by attachClickHandlersToSections)
        sectionEl.dataset.clickHandlerAttached = 'false';
        
        // Attach click handlers to new section immediately
        attachClickHandlersToSections();
        
        // Update sections array
        sections.push({
            id: tempId,
            element: sectionEl,
            data: newSectionData
        });
        
        // Update order
        updateSectionOrder();
        
        // Reinitialize sortable for new section
        initSortable();
        
        // Select new section
        selectSection(sectionEl);
            
            hasChanges = true;
            updateSaveButtonState();
            
            console.log('Section creation complete successfully');
        } catch (error) {
            console.error('Error in createNewSection:', error);
            console.error('Error stack:', error.stack);
            alert('Error creating section: ' + error.message + '\n\nCheck console for details.');
            throw error;
        }
    }
    
    // Create section DOM element
    function createSectionElement(sectionData) {
        const sectionEl = document.createElement('div');
        sectionEl.className = 'canvas-section';
        sectionEl.dataset.id = sectionData.id;
        sectionEl.dataset.type = sectionData.section_type;
        sectionEl.dataset.order = sectionData.order_index || 0;
        sectionEl.dataset.status = sectionData.status || 'ACTIVE';
        sectionEl.dataset.content = JSON.stringify(sectionData.content || {});
        sectionEl.dataset.settings = JSON.stringify(sectionData.settings || {});
        sectionEl.dataset.title = sectionData.title || '';
        
        // Render preview (simplified - full rendering would match homepage-section-canvas.php)
        sectionEl.innerHTML = `
            <div class="section-toolbar">
                <span class="drag-handle">☰</span>
                <span class="section-type-icon">${getSectionIcon(sectionData.section_type)}</span>
                <span class="section-title">${escapeHtml(sectionData.title || sectionData.section_type)}</span>
                <button type="button" class="section-toolbar-btn edit-section" title="Edit">✏️</button>
                <button type="button" class="section-toolbar-btn duplicate-section" title="Duplicate">📋</button>
                <button type="button" class="section-toolbar-btn delete-section" title="Delete">🗑️</button>
            </div>
            <div class="section-preview-content">
                <div style="padding: 40px 20px; text-align: center; background: #f9f9f9; border: 2px dashed #ddd;">
                    ${getSectionIcon(sectionData.section_type)} ${escapeHtml(sectionData.title || sectionData.section_type)}
                </div>
            </div>
        `;
        
        return sectionEl;
    }
    
    function getSectionIcon(type) {
        const icons = {
            'hero': '🎯', 'heading': '📝', 'text': '📄', 'categories': '📦',
            'products': '🛍️', 'features': '✨', 'testimonials': '💬',
            'newsletter': '📧', 'cta': '🚀', 'spacer': '↕️', 'divider': '➖', 'custom': '⚙️'
        };
        return icons[type] || '📄';
    }

    // Attach click handlers to all sections (for existing and new sections)
    function attachClickHandlersToSections() {
        const canvasFrame = document.getElementById('canvas-frame');
        if (!canvasFrame) return;
        
        const sectionElements = canvasFrame.querySelectorAll('.canvas-section');
        
        sectionElements.forEach(section => {
            // Only attach if not already attached (check for data attribute)
            if (section.dataset.clickHandlerAttached === 'true') {
                return;
            }
            
            // Mark as having handler attached
            section.dataset.clickHandlerAttached = 'true';
            
            // Add click handler directly to section
            section.addEventListener('click', function(e) {
                // Don't interfere with toolbar buttons
                if (e.target.closest('.section-toolbar') || 
                    e.target.closest('.section-toolbar-btn') ||
                    e.target.closest('.drag-handle')) {
                    return;
                }
                e.preventDefault();
                e.stopPropagation();
                selectSection(this);
            }, true); // Use capture phase
            
            // Make sure it's in sections array
            const sectionId = section.dataset.id;
            const existingSection = sections.find(s => s.id === sectionId);
            if (!existingSection) {
                // Add to sections array if not already there
                sections.push({
                    id: sectionId,
                    element: section,
                    data: getSectionDataFromElement(section)
                });
            } else {
                // Update element reference
                existingSection.element = section;
            }
        });
        
        console.log(`✓ Attached click handlers to ${sectionElements.length} sections`);
    }

    // Initialize canvas interactions
    function initCanvasInteractions() {
        // Use delegation on canvas frame for better performance
        const canvasFrame = document.getElementById('canvas-frame');
        if (!canvasFrame) {
            console.error('Canvas frame not found for interactions');
            return;
        }

        // Click to select section - use delegation as backup
        canvasFrame.addEventListener('click', (e) => {
            // Don't select if clicking on toolbar buttons or their children
            if (e.target.closest('.section-toolbar') || 
                e.target.closest('.section-toolbar-btn') ||
                e.target.closest('.drag-handle')) {
                // Let toolbar button handlers process the click
                return;
            }

            // Find the section element (closest parent or the element itself)
            const section = e.target.closest('.canvas-section');
            
            if (section) {
                e.preventDefault();
                e.stopPropagation();
                selectSection(section);
            } else {
                // Click outside section - deselect only if clicking on canvas background
                if (e.target === canvasFrame || e.target.closest('.empty-canvas')) {
                    deselectSection();
                }
            }
        }, true); // Use capture phase to ensure we get the event first

        // Double-click to edit inline
        canvasFrame.addEventListener('dblclick', (e) => {
            const section = e.target.closest('.canvas-section');
            if (section && selectedSection === section && !e.target.closest('.section-toolbar')) {
                openInlineEditor(section);
            }
        });

        // Section toolbar buttons - handle separately
        canvasFrame.addEventListener('click', (e) => {
            const editBtn = e.target.closest('.edit-section');
            const duplicateBtn = e.target.closest('.duplicate-section');
            const deleteBtn = e.target.closest('.delete-section');
            
            if (editBtn) {
                e.preventDefault();
                e.stopPropagation();
                if (selectedSection) {
                    openSettingsPanel(selectedSection);
                }
            } else if (duplicateBtn) {
                e.preventDefault();
                e.stopPropagation();
                if (selectedSection) {
                    duplicateSection(selectedSection);
                }
            } else if (deleteBtn) {
                e.preventDefault();
                e.stopPropagation();
                if (selectedSection) {
                    deleteSection(selectedSection);
                }
            }
        });

        // Also handle clicks outside canvas frame
        document.addEventListener('click', (e) => {
            if (!canvasFrame.contains(e.target) && !e.target.closest('.settings-panel') && !e.target.closest('.elements-panel')) {
                deselectSection();
            }
        });
        
        console.log('Canvas interactions initialized');
    }

    // Select section
    function selectSection(sectionElement) {
        deselectSection();
        selectedSection = sectionElement;
        sectionElement.classList.add('selected');
        openSettingsPanel(sectionElement);
    }

    // Deselect section
    function deselectSection() {
        if (selectedSection) {
            selectedSection.classList.remove('selected');
        }
        selectedSection = null;
        closeSettingsPanel();
    }

    // Open settings panel
    function openSettingsPanel(sectionElement) {
        const sectionData = getSectionData(sectionElement);
        if (settingsTitle) {
            settingsTitle.textContent = `${sectionData.section_type || sectionData.type || 'Section'} Settings`;
        }
        
        // Load section settings
        loadSectionSettings(sectionData);
        
        if (settingsPanel) {
            settingsPanel.style.display = 'block';
        }
    }

    // Close settings panel
    function closeSettingsPanel() {
        settingsContent.innerHTML = `
            <div class="empty-settings">
                <p style="text-align: center; color: #999; padding: 40px 20px;">
                    Select a section to edit its settings
                </p>
            </div>
        `;
    }

    // Load section settings
    function loadSectionSettings(sectionData) {
        const activeTab = document.querySelector('.settings-tab.active')?.dataset.tab || 'content';
        
        let html = '';

        if (activeTab === 'content') {
            html = generateContentSettings(sectionData);
        } else if (activeTab === 'style') {
            html = generateStyleSettings(sectionData);
        } else if (activeTab === 'advanced') {
            html = generateAdvancedSettings(sectionData);
        }

        settingsContent.innerHTML = html;
        attachSettingHandlers(sectionData);
    }

    // Generate content settings
    function generateContentSettings(sectionData) {
        const content = sectionData.content || {};
        const type = sectionData.section_type || sectionData.type;
        
        let html = '';

        switch (type) {
            case 'hero':
                html = `
                    <div class="settings-section">
                        <div class="setting-field">
                            <label class="setting-label">Section Title</label>
                            <input type="text" class="setting-input" data-field="title" value="${escapeHtml(sectionData.title || '')}" placeholder="Hero Section">
                        </div>
                        <div class="setting-field">
                            <label class="setting-label">Title</label>
                            <input type="text" class="setting-input" data-field="content.title" value="${escapeHtml(content.title || '')}" placeholder="Hero Title">
                        </div>
                        <div class="setting-field">
                            <label class="setting-label">Subtitle</label>
                            <textarea class="setting-textarea" data-field="content.subtitle" placeholder="Hero subtitle">${escapeHtml(content.subtitle || '')}</textarea>
                        </div>
                        <div class="setting-field">
                            <label class="setting-label">Button Text</label>
                            <input type="text" class="setting-input" data-field="content.buttonText" value="${escapeHtml(content.buttonText || '')}" placeholder="Get Started">
                        </div>
                        <div class="setting-field">
                            <label class="setting-label">Button Link</label>
                            <input type="url" class="setting-input" data-field="content.buttonLink" value="${escapeHtml(content.buttonLink || '')}" placeholder="/quote.php">
                        </div>
                        <div class="setting-field">
                            <label class="setting-label">Background Image URL</label>
                            <input type="url" class="setting-input" data-field="content.backgroundImage" value="${escapeHtml(content.backgroundImage || '')}" placeholder="https://example.com/image.jpg">
                        </div>
                    </div>
                `;
                break;

            case 'heading':
                html = `
                    <div class="settings-section">
                        <div class="setting-field">
                            <label class="setting-label">Section Title</label>
                            <input type="text" class="setting-input" data-field="title" value="${escapeHtml(sectionData.title || '')}" placeholder="Section Title">
                        </div>
                        <div class="setting-field">
                            <label class="setting-label">Title</label>
                            <input type="text" class="setting-input" data-field="content.title" value="${escapeHtml(content.title || '')}" placeholder="Section Title">
                        </div>
                        <div class="setting-field">
                            <label class="setting-label">Subtitle</label>
                            <textarea class="setting-textarea" data-field="content.subtitle" placeholder="Section subtitle">${escapeHtml(content.subtitle || '')}</textarea>
                        </div>
                    </div>
                `;
                break;

            case 'text':
                html = `
                    <div class="settings-section">
                        <div class="setting-field">
                            <label class="setting-label">Section Title</label>
                            <input type="text" class="setting-input" data-field="title" value="${escapeHtml(sectionData.title || '')}" placeholder="Section Title">
                        </div>
                        <div class="setting-field">
                            <label class="setting-label">Content</label>
                            <textarea class="setting-textarea" data-field="content.content" rows="8" placeholder="<p>Your content here...</p>">${escapeHtml(content.content || '')}</textarea>
                            <div class="setting-help">HTML allowed</div>
                        </div>
                    </div>
                `;
                break;

            case 'spacer':
                html = `
                    <div class="settings-section">
                        <div class="setting-field">
                            <label class="setting-label">Height (px)</label>
                            <input type="number" class="setting-input" data-field="content.height" value="${content.height || 60}" min="0" max="500">
                        </div>
                    </div>
                `;
                break;

            default:
                html = `
                    <div class="settings-section">
                        <div class="setting-field">
                            <label class="setting-label">Section Title</label>
                            <input type="text" class="setting-input" data-field="title" value="${escapeHtml(sectionData.title || '')}">
                        </div>
                        <div class="setting-field">
                            <label class="setting-label">Custom Fields</label>
                            <div id="custom-fields-container"></div>
                            <button type="button" class="add-custom-field-btn" style="margin-top: 10px; padding: 6px 12px; background: #0b3a63; color: white; border: none; border-radius: 3px; cursor: pointer;">
                                + Add Custom Field
                            </button>
                        </div>
                    </div>
                `;
        }

        return html;
    }

    // Generate style settings
    function generateStyleSettings(sectionData) {
        const styles = (sectionData.settings?.styles || {});
        
        return `
            <div class="settings-section">
                <div class="settings-section-title">Layout</div>
                <div class="setting-field">
                    <label class="setting-label">Padding</label>
                    <input type="text" class="setting-input" data-field="settings.styles.padding" value="${escapeHtml(styles.padding || '')}" placeholder="20px or 20px 10px">
                </div>
                <div class="setting-field">
                    <label class="setting-label">Margin</label>
                    <input type="text" class="setting-input" data-field="settings.styles.margin" value="${escapeHtml(styles.margin || '')}" placeholder="0 or 20px 0">
                </div>
            </div>
            
            <div class="settings-section">
                <div class="settings-section-title">Background</div>
                <div class="setting-field">
                    <label class="setting-label">Background Color</label>
                    <div class="color-input-wrapper">
                        <input type="color" class="color-picker" data-field="settings.styles.background_color" value="${styles.background_color || '#ffffff'}">
                        <input type="text" class="color-input setting-input" data-field="settings.styles.background_color" value="${escapeHtml(styles.background_color || '#ffffff')}">
                    </div>
                </div>
                <div class="setting-field">
                    <label class="setting-label">Background Image URL</label>
                    <input type="url" class="setting-input" data-field="settings.styles.background_image" value="${escapeHtml(styles.background_image || '')}">
                </div>
                <div class="setting-field">
                    <label class="setting-label">Background Position</label>
                    <select class="setting-select" data-field="settings.styles.background_position">
                        <option value="center" ${styles.background_position === 'center' ? 'selected' : ''}>Center</option>
                        <option value="top" ${styles.background_position === 'top' ? 'selected' : ''}>Top</option>
                        <option value="bottom" ${styles.background_position === 'bottom' ? 'selected' : ''}>Bottom</option>
                        <option value="left" ${styles.background_position === 'left' ? 'selected' : ''}>Left</option>
                        <option value="right" ${styles.background_position === 'right' ? 'selected' : ''}>Right</option>
                    </select>
                </div>
            </div>
            
            <div class="settings-section">
                <div class="settings-section-title">Typography</div>
                <div class="setting-field">
                    <label class="setting-label">Text Color</label>
                    <div class="color-input-wrapper">
                        <input type="color" class="color-picker" data-field="settings.styles.text_color" value="${styles.text_color || '#333333'}">
                        <input type="text" class="color-input setting-input" data-field="settings.styles.text_color" value="${escapeHtml(styles.text_color || '#333333')}">
                    </div>
                </div>
                <div class="setting-field">
                    <label class="setting-label">Font Size</label>
                    <input type="text" class="setting-input" data-field="settings.styles.font_size" value="${escapeHtml(styles.font_size || '')}" placeholder="16px">
                </div>
                <div class="setting-field">
                    <label class="setting-label">Font Weight</label>
                    <select class="setting-select" data-field="settings.styles.font_weight">
                        <option value="normal" ${styles.font_weight === 'normal' ? 'selected' : ''}>Normal</option>
                        <option value="bold" ${styles.font_weight === 'bold' ? 'selected' : ''}>Bold</option>
                        <option value="300" ${styles.font_weight === '300' ? 'selected' : ''}>Light</option>
                        <option value="600" ${styles.font_weight === '600' ? 'selected' : ''}>Semi-Bold</option>
                    </select>
                </div>
                <div class="setting-field">
                    <label class="setting-label">Text Align</label>
                    <select class="setting-select" data-field="settings.styles.text_align">
                        <option value="left" ${styles.text_align === 'left' ? 'selected' : ''}>Left</option>
                        <option value="center" ${styles.text_align === 'center' ? 'selected' : ''}>Center</option>
                        <option value="right" ${styles.text_align === 'right' ? 'selected' : ''}>Right</option>
                        <option value="justify" ${styles.text_align === 'justify' ? 'selected' : ''}>Justify</option>
                    </select>
                </div>
            </div>
            
            <div class="settings-section">
                <div class="settings-section-title">Borders & Shadows</div>
                <div class="setting-field">
                    <label class="setting-label">Border</label>
                    <input type="text" class="setting-input" data-field="settings.styles.border" value="${escapeHtml(styles.border || '')}" placeholder="1px solid #ddd">
                </div>
                <div class="setting-field">
                    <label class="setting-label">Border Radius</label>
                    <input type="text" class="setting-input" data-field="settings.styles.border_radius" value="${escapeHtml(styles.border_radius || '')}" placeholder="4px">
                </div>
                <div class="setting-field">
                    <label class="setting-label">Box Shadow</label>
                    <input type="text" class="setting-input" data-field="settings.styles.box_shadow" value="${escapeHtml(styles.box_shadow || '')}" placeholder="0 2px 4px rgba(0,0,0,0.1)">
                </div>
            </div>
        `;
    }

    // Generate advanced settings
    function generateAdvancedSettings(sectionData) {
        return `
            <div class="settings-section">
                <div class="settings-section-title">Visibility</div>
                <div class="setting-field">
                    <label class="setting-label">
                        <input type="checkbox" data-field="status" ${sectionData.status === 'ACTIVE' ? 'checked' : ''}>
                        Active (visible on frontend)
                    </label>
                </div>
            </div>
            
            <div class="settings-section">
                <div class="settings-section-title">Custom CSS</div>
                <div class="setting-field">
                    <label class="setting-label">Section CSS Class</label>
                    <input type="text" class="setting-input" data-field="settings.css_class" value="${escapeHtml(sectionData.settings?.css_class || '')}" placeholder="my-custom-class">
                </div>
                <div class="setting-field">
                    <label class="setting-label">Custom CSS</label>
                    <textarea class="setting-textarea" data-field="settings.custom_css" rows="8" placeholder=".my-class { color: red; }">${escapeHtml(sectionData.settings?.custom_css || '')}</textarea>
                </div>
            </div>
            
            <div class="settings-section">
                <div class="settings-section-title">Custom Attributes</div>
                <div class="setting-field">
                    <label class="setting-label">Custom Data Attributes (JSON)</label>
                    <textarea class="setting-textarea" data-field="settings.custom_attributes" rows="4" placeholder='{"data-id": "123"}'>${escapeHtml(JSON.stringify(sectionData.settings?.custom_attributes || {}, null, 2))}</textarea>
                </div>
            </div>
        `;
    }

    // Attach setting handlers
    function attachSettingHandlers(sectionData) {
        // Remove old listeners by cloning the container
        const oldContent = settingsContent.innerHTML;
        settingsContent.innerHTML = oldContent; // This removes all event listeners
        
        const inputs = settingsContent.querySelectorAll('[data-field]');
        
        inputs.forEach(input => {
            // Handle input events with debouncing for text fields
            if (input.type === 'text' || input.type === 'textarea' || input.tagName === 'TEXTAREA') {
                let timeout;
                input.addEventListener('input', (e) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        updateSectionField(e.target.dataset.field, e.target.value);
                    }, 300);
                });
            } else {
                // Immediate update for selects, checkboxes, number inputs, etc.
                input.addEventListener('change', (e) => {
                    const value = input.type === 'checkbox' ? e.target.checked : e.target.value;
                    updateSectionField(e.target.dataset.field, value);
                });
            }
        });

        // Handle checkboxes separately
        const checkboxes = settingsContent.querySelectorAll('input[type="checkbox"][data-field]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                updateSectionField(e.target.dataset.field, e.target.checked);
            });
        });

        // Color picker sync
        const colorPickers = settingsContent.querySelectorAll('.color-picker');
        colorPickers.forEach(picker => {
            picker.addEventListener('input', (e) => { // Use 'input' for instant feedback
                const textInput = picker.nextElementSibling;
                if (textInput && textInput.classList.contains('color-input')) {
                    textInput.value = e.target.value;
                    updateSectionField(picker.dataset.field, e.target.value);
                }
            });
            picker.addEventListener('change', (e) => {
                const textInput = picker.nextElementSibling;
                if (textInput && textInput.classList.contains('color-input')) {
                    textInput.value = e.target.value;
                    updateSectionField(picker.dataset.field, e.target.value);
                }
            });
        });

        // Color text input sync
        const colorInputs = settingsContent.querySelectorAll('.color-input');
        colorInputs.forEach(input => {
            let timeout;
            input.addEventListener('input', (e) => {
                const picker = input.previousElementSibling;
                if (picker && picker.classList.contains('color-picker')) {
                    picker.value = e.target.value;
                }
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    updateSectionField(input.dataset.field, e.target.value);
                }, 300);
            });
        });
    }

    // Update section field - now accepts just fieldPath and value
    function updateSectionField(fieldPath, value) {
        if (!selectedSection) {
            console.warn('No section selected, cannot update field:', fieldPath);
            return;
        }

        const id = selectedSection.dataset.id;
        if (!id) {
            console.error('No section ID found');
            return;
        }

        let currentData = sectionsData.get(id);
        if (!currentData) {
            console.error('Section data not found in memory for ID:', id);
            // Try to get from element dataset as fallback
            currentData = getSectionDataFromElement(selectedSection);
            if (!currentData) {
                console.error('Could not retrieve section data');
                return;
            }
        }
        
        // Clone to avoid mutation
        const updatedData = JSON.parse(JSON.stringify(currentData));
        
        // Ensure settings and content exist
        if (!updatedData.settings) updatedData.settings = {};
        if (!updatedData.content) updatedData.content = {};
        
        // Handle checkbox values (for status field)
        if (fieldPath === 'status') {
            // Value comes as boolean from checkbox
            updatedData.status = value === true || value === 'ACTIVE' || value === 'true' ? 'ACTIVE' : 'INACTIVE';
        } else if (fieldPath === 'title') {
            // Direct field update
            updatedData.title = value;
        } else {
            // Update nested field (e.g., "content.title", "settings.styles.padding")
            const parts = fieldPath.split('.');
            let obj = updatedData;
            
            // Navigate/create nested structure
            for (let i = 0; i < parts.length - 1; i++) {
                if (!obj[parts[i]]) {
                    obj[parts[i]] = {};
                }
                // Ensure we're working with an object, not null/undefined
                if (typeof obj[parts[i]] !== 'object' || obj[parts[i]] === null || Array.isArray(obj[parts[i]])) {
                    obj[parts[i]] = {};
                }
                obj = obj[parts[i]];
            }
            
            // Set the final value (handle number inputs and empty strings)
            const finalKey = parts[parts.length - 1];
            const input = settingsContent.querySelector(`[data-field="${fieldPath}"]`);
            
            if (input) {
                if (input.type === 'number') {
                    obj[finalKey] = value === '' || value === null ? null : parseFloat(value) || 0;
                } else if (input.type === 'checkbox') {
                    obj[finalKey] = value === true || value === 'true';
                } else {
                    obj[finalKey] = value === '' ? null : value;
                }
            } else {
                // Fallback: just set the value
                obj[finalKey] = value === '' ? null : value;
            }
        }
        
        // Update in memory
        sectionsData.set(id, updatedData);
        
        // Update in sections array
        const section = sections.find(s => s.id === id);
        if (section) {
            section.data = updatedData;
        }
        
        // Update element dataset attributes
        if (fieldPath.startsWith('content.')) {
            const content = updatedData.content || {};
            selectedSection.dataset.content = JSON.stringify(content);
        } else if (fieldPath.startsWith('settings.')) {
            const settings = updatedData.settings || {};
            selectedSection.dataset.settings = JSON.stringify(settings);
        } else if (fieldPath === 'title') {
            selectedSection.dataset.title = value || '';
            const titleEl = selectedSection.querySelector('.section-title');
            if (titleEl) {
                titleEl.textContent = value || updatedData.section_type || 'Untitled';
            }
        } else if (fieldPath === 'status') {
            selectedSection.dataset.status = updatedData.status;
        }
        
        // Update visual preview with UPDATED data
        hasChanges = true;
        updateSaveButtonState();
        updateSectionPreview(selectedSection, updatedData);
        
        console.log('Field updated:', fieldPath, '=', value, 'Section ID:', id);
    }

    // Update section preview
    function updateSectionPreview(sectionElement, sectionData) {
        const styles = sectionData.settings?.styles || {};
        
        // Apply inline styles
        let styleString = '';
        if (styles.padding) styleString += `padding: ${styles.padding}; `;
        if (styles.margin) styleString += `margin: ${styles.margin}; `;
        if (styles.background_color) styleString += `background-color: ${styles.background_color}; `;
        if (styles.background_image) styleString += `background-image: url(${styles.background_image}); `;
        if (styles.text_color) styleString += `color: ${styles.text_color}; `;
        if (styles.font_size) styleString += `font-size: ${styles.font_size}; `;
        if (styles.font_weight) styleString += `font-weight: ${styles.font_weight}; `;
        if (styles.text_align) styleString += `text-align: ${styles.text_align}; `;
        if (styles.border) styleString += `border: ${styles.border}; `;
        if (styles.border_radius) styleString += `border-radius: ${styles.border_radius}; `;
        if (styles.box_shadow) styleString += `box-shadow: ${styles.box_shadow}; `;
        
        sectionElement.style.cssText = styleString;
        
        // Update content in preview (simplified - would need full re-render)
        const previewContent = sectionElement.querySelector('.section-preview-content');
        if (previewContent) {
            // Trigger preview update based on section type
            // This is simplified - full implementation would re-render the preview
        }
    }

    // Initialize settings tabs
    function initSettingsTabs() {
        const tabs = document.querySelectorAll('.settings-tab');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                if (selectedSection) {
                    const sectionData = getSectionData(selectedSection);
                    loadSectionSettings(sectionData);
                }
            });
        });
    }

    // Initialize responsive controls
    function initResponsiveControls() {
        const responsiveBtns = document.querySelectorAll('.responsive-btn');
        
        responsiveBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                responsiveBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const device = btn.dataset.device;
                canvasFrame.style.width = device === 'desktop' ? '100%' : device === 'tablet' ? '768px' : '375px';
            });
        });
    }

    // Initialize save button
    function initSaveButton() {
        saveBtn.addEventListener('click', async () => {
            await saveAllSections();
        });
    }

    // Save all sections using bulk sync
    async function saveAllSections() {
        if (!hasChanges) {
            alert('No changes to save');
            return;
        }
        
        saveBtn.classList.add('saving');
        saveBtn.textContent = '💾 Saving...';
        saveBtn.disabled = true;
        
        try {
            const pageId = getPageId();
            
            // Get all sections in current order
            const sectionsToSave = Array.from(document.querySelectorAll('.canvas-section')).map((sectionEl, index) => {
                const id = sectionEl.dataset.id;
                const data = sectionsData.get(id) || getSectionDataFromElement(sectionEl);
                return {
                    ...data,
                    page_id: pageId,
                    order_index: index
                };
            });
            
            // Bulk sync via PUT
            const response = await fetch('/api/admin/homepage/index.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    page_id: pageId,
                    sections: sectionsToSave
                })
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                // Update sections data with server response
                if (result.sections) {
                    sectionsData.clear();
                    result.sections.forEach(section => {
                        sectionsData.set(section.id, section);
                    });
                }
                
                hasChanges = false;
                updateSaveButtonState();
                
                saveBtn.textContent = '💾 Saved!';
                setTimeout(() => {
                    saveBtn.textContent = '💾 Save Changes';
                    saveBtn.classList.remove('saving');
                    saveBtn.disabled = false;
                }, 2000);
                
                // Reload page to reflect any ID changes
                location.reload();
            } else {
                throw new Error(result.message || 'Failed to save sections');
            }
        } catch (error) {
            console.error('Error saving sections:', error);
            alert('Error saving sections: ' + error.message);
            saveBtn.textContent = '💾 Save Changes';
            saveBtn.classList.remove('saving');
            saveBtn.disabled = false;
        }
    }
    
    // Update save button state
    function updateSaveButtonState() {
        if (saveBtn) {
            if (hasChanges) {
                saveBtn.style.opacity = '1';
                saveBtn.style.cursor = 'pointer';
                saveBtn.disabled = false;
            } else {
                saveBtn.style.opacity = '0.6';
                saveBtn.style.cursor = 'not-allowed';
                saveBtn.disabled = true;
            }
        }
    }

    // Get section data from memory or element
    function getSectionData(sectionElement) {
        const id = sectionElement?.dataset?.id || sectionElement?.id;
        if (id && sectionsData.has(id)) {
            return sectionsData.get(id);
        }
        return getSectionDataFromElement(sectionElement);
    }
    
    // Update section data in memory
    function updateSectionData(id, data) {
        const existing = sectionsData.get(id) || {};
        const updated = { ...existing, ...data };
        sectionsData.set(id, updated);
        
        // Update in sections array
        const section = sections.find(s => s.id === id);
        if (section) {
            section.data = updated;
        }
        
        hasChanges = true;
        updateSaveButtonState();
    }

    // Update section order
    function updateSectionOrder() {
        const sectionElements = Array.from(document.querySelectorAll('.canvas-section'));
        sectionElements.forEach((sectionEl, index) => {
            sectionEl.dataset.order = index;
            const id = sectionEl.dataset.id;
            if (sectionsData.has(id)) {
                const data = sectionsData.get(id);
                data.order_index = index;
                sectionsData.set(id, data);
            }
        });
        
        // Update sections array order
        sections = sectionElements.map(el => {
            const id = el.dataset.id;
            return sections.find(s => s.id === id) || {
                id: id,
                element: el,
                data: getSectionData(el)
            };
        });
        
        hasChanges = true;
        updateSaveButtonState();
    }

    // Duplicate section
    async function duplicateSection(sectionElement) {
        const sectionData = getSectionData(sectionElement);
        const defaults = { ...sectionData.content };
        
        await createNewSection(sectionData.type, defaults);
    }

    // Delete section (removes from DOM, deletion happens on save)
    async function deleteSection(sectionElement) {
        if (!confirm('Are you sure you want to delete this section?')) {
            return;
        }
        
        const id = sectionElement.dataset.id;
        
        // Remove from memory (unless it's a temp ID, then just remove from DOM)
        if (!id.startsWith('temp_')) {
            // Mark for deletion by removing from sectionsData
            sectionsData.delete(id);
        }
        
        // Remove from DOM
        sectionElement.remove();
        
        // Remove from sections array
        sections = sections.filter(s => s.id !== id);
        
        // Update order
        updateSectionOrder();
        
        // Deselect
        deselectSection();
        
        hasChanges = true;
        updateSaveButtonState();
    }

    // Utility functions
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function openInlineEditor(sectionElement) {
        // Future: Implement inline editing
        console.log('Inline editor (coming soon)');
    }

})();

