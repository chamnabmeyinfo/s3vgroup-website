/**
 * Homepage Builder - Drag and Drop Interface
 */

(function() {
    'use strict';

    const sectionsContainer = document.getElementById('sections-container');
    const addSectionBtn = document.getElementById('add-section-btn');
    const sectionModal = document.getElementById('section-modal');
    const sectionForm = document.getElementById('section-form');
    const modalTitle = document.getElementById('modal-title');
    const cancelBtn = document.getElementById('cancel-section-btn');
    const saveOrderBtn = document.getElementById('save-order-btn');
    const sectionTypeInput = document.getElementById('section-type');
    const sectionIdInput = document.getElementById('section-id');
    const sectionConfigContent = document.getElementById('section-config-content');

    let currentMode = 'create';
    let currentSectionId = null;
    let sortableInstance = null;

    // Initialize Sortable for drag and drop
    if (sectionsContainer && typeof Sortable !== 'undefined') {
        sortableInstance = new Sortable(sectionsContainer, {
            animation: 150,
            ghostClass: 'opacity-50',
            chosenClass: 'border-[#0b3a63] border-2',
            dragClass: 'cursor-grabbing',
            onEnd: function() {
                // Update order visually
                updateSectionOrder();
            }
        });
    }

    // Available section configs
    const sectionConfigs = {
        hero: {
            title: 'Hero Slider',
            fields: [
                { name: 'title', label: 'Title', type: 'text', required: true },
                { name: 'subtitle', label: 'Subtitle', type: 'textarea' },
                { name: 'buttonText', label: 'Button Text', type: 'text' },
                { name: 'buttonLink', label: 'Button Link', type: 'url' },
                { name: 'backgroundImage', label: 'Background Image URL', type: 'url' },
            ]
        },
        categories: {
            title: 'Categories Grid',
            fields: [
                { name: 'title', label: 'Section Title', type: 'text' },
                { name: 'limit', label: 'Number of Categories', type: 'number', default: 12 },
                { name: 'columns', label: 'Columns (2-6)', type: 'number', default: 4, min: 2, max: 6 },
            ]
        },
        products: {
            title: 'Featured Products',
            fields: [
                { name: 'title', label: 'Section Title', type: 'text' },
                { name: 'limit', label: 'Number of Products', type: 'number', default: 6 },
                { name: 'columns', label: 'Columns', type: 'number', default: 3, min: 2, max: 4 },
            ]
        },
        features: {
            title: 'Features Section',
            fields: [
                { name: 'title', label: 'Section Title', type: 'text' },
                { name: 'subtitle', label: 'Subtitle', type: 'textarea' },
                { name: 'items', label: 'Features (JSON array)', type: 'textarea', placeholder: '[{"icon":"🔧","title":"Feature","description":"Description"}]' },
            ]
        },
        testimonials: {
            title: 'Testimonials',
            fields: [
                { name: 'title', label: 'Section Title', type: 'text' },
                { name: 'limit', label: 'Number of Testimonials', type: 'number', default: 6 },
                { name: 'featuredOnly', label: 'Featured Only', type: 'checkbox', default: true },
            ]
        },
        newsletter: {
            title: 'Newsletter Signup',
            fields: [
                { name: 'title', label: 'Title', type: 'text' },
                { name: 'subtitle', label: 'Subtitle', type: 'textarea' },
                { name: 'buttonText', label: 'Button Text', type: 'text', default: 'Subscribe' },
            ]
        },
        cta: {
            title: 'Call to Action',
            fields: [
                { name: 'title', label: 'Title', type: 'text', required: true },
                { name: 'subtitle', label: 'Subtitle', type: 'textarea' },
                { name: 'buttonText', label: 'Button Text', type: 'text', default: 'Get Started' },
                { name: 'buttonLink', label: 'Button Link', type: 'url' },
                { name: 'secondaryButtonText', label: 'Secondary Button Text', type: 'text' },
                { name: 'secondaryButtonLink', label: 'Secondary Button Link', type: 'url' },
                { name: 'background', label: 'Background Color', type: 'color', default: '#0b3a63' },
            ]
        },
        custom: {
            title: 'Custom HTML',
            fields: [
                { name: 'title', label: 'Section Title', type: 'text' },
                { name: 'html', label: 'HTML Content', type: 'textarea', rows: 10, placeholder: '<div>Your custom HTML here</div>' },
                { name: 'css', label: 'Custom CSS', type: 'textarea', rows: 5, placeholder: '/* Your custom CSS here */' },
            ]
        }
    };

    // Add section button click
    addSectionBtn?.addEventListener('click', () => {
        showSectionTypeSelector();
    });

    // Section type buttons
    document.querySelectorAll('.section-type-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.type;
            const label = btn.dataset.label;
            openModal(type, label);
        });
    });

    // Edit section
    document.querySelectorAll('.edit-section-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            try {
                const response = await fetch(`/api/admin/homepage/item.php?id=${encodeURIComponent(id)}`);
                const result = await response.json();
                if (result.status === 'success') {
                    openModal(result.section.section_type, result.section.title || '', result.section);
                } else {
                    showToast('Failed to load section', 'error');
                }
            } catch (error) {
                console.error('Load error:', error);
                showToast('Error loading section: ' + error.message, 'error');
            }
        });
    });

    // Delete section
    document.querySelectorAll('.delete-section-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const id = btn.dataset.id;
            if (confirm('Are you sure you want to delete this section?')) {
                try {
                    const response = await fetch(`/api/admin/homepage/item.php?id=${encodeURIComponent(id)}`, {
                        method: 'DELETE'
                    });
                    const result = await response.json();
                    if (result.status === 'success') {
                        showToast('Section deleted', 'success');
                        location.reload();
                    } else {
                        showToast(result.message || 'Failed to delete', 'error');
                    }
                } catch (error) {
                    console.error('Delete error:', error);
                    showToast('Error deleting section: ' + error.message, 'error');
                }
            }
        });
    });

    // Status toggle
    document.querySelectorAll('.section-status-toggle').forEach(toggle => {
        toggle.addEventListener('change', async (e) => {
            const id = toggle.dataset.id;
            const status = e.target.checked ? 'ACTIVE' : 'INACTIVE';
            try {
                const response = await fetch(`/api/admin/homepage/item.php?id=${encodeURIComponent(id)}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ status })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    showToast(`Section ${status.toLowerCase()}`, 'success');
                }
            } catch (error) {
                showToast('Error updating status', 'error');
                e.target.checked = !e.target.checked; // Revert
            }
        });
    });

    // Save order
    saveOrderBtn?.addEventListener('click', async () => {
        const order = Array.from(document.querySelectorAll('.section-item')).map(item => item.dataset.id);
        try {
            const response = await fetch('/api/admin/homepage/index.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order })
            });
            const result = await response.json();
            if (result.status === 'success') {
                showToast('Order saved successfully!', 'success');
            } else {
                showToast(result.message || 'Failed to save order', 'error');
            }
        } catch (error) {
            showToast('Error saving order', 'error');
        }
    });

    // Modal functions
    function showSectionTypeSelector() {
        // Show modal with section type selection
        sectionModal.classList.remove('hidden');
        modalTitle.textContent = 'Select Section Type';
        sectionConfigContent.innerHTML = `
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                ${Object.entries(sectionConfigs).map(([type, config]) => `
                    <button type="button" 
                            class="select-section-type p-4 border-2 border-gray-200 rounded-lg hover:border-[#0b3a63] hover:shadow-md transition-all text-left"
                            data-type="${type}">
                        <div class="font-semibold text-gray-900">${config.title}</div>
                    </button>
                `).join('')}
            </div>
        `;
        
        document.querySelectorAll('.select-section-type').forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.dataset.type;
                const config = sectionConfigs[type];
                openModal(type, config.title);
            });
        });
    }

    function openModal(type, label, sectionData = null) {
        currentMode = sectionData ? 'edit' : 'create';
        currentSectionId = sectionData?.id || null;
        sectionTypeInput.value = type;
        sectionIdInput.value = currentSectionId || '';
        modalTitle.textContent = currentMode === 'edit' ? `Edit ${label}` : `Add ${label}`;

        const config = sectionConfigs[type] || sectionConfigs.custom;
        sectionConfigContent.innerHTML = `
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Section Title</label>
                <input type="text" name="title" value="${sectionData?.title || ''}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
            </div>
            ${config.fields.map(field => {
                const value = sectionData?.content?.[field.name] || sectionData?.settings?.[field.name] || field.default || '';
                if (field.type === 'textarea') {
                    return `
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">${field.label}</label>
                            <textarea name="${field.name}" rows="${field.rows || 3}" 
                                      placeholder="${field.placeholder || ''}" 
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                      ${field.required ? 'required' : ''}>${value}</textarea>
                        </div>
                    `;
                } else if (field.type === 'checkbox') {
                    return `
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="${field.name}" ${value ? 'checked' : ''} class="mr-2">
                                <span class="text-sm font-medium text-gray-700">${field.label}</span>
                            </label>
                        </div>
                    `;
                } else {
                    return `
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">${field.label}</label>
                            <input type="${field.type}" 
                                   name="${field.name}" 
                                   value="${value}" 
                                   ${field.min ? `min="${field.min}"` : ''} 
                                   ${field.max ? `max="${field.max}"` : ''} 
                                   placeholder="${field.placeholder || ''}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md"
                                   ${field.required ? 'required' : ''}>
                        </div>
                    `;
                }
            }).join('')}
        `;

        sectionModal.classList.remove('hidden');
    }

    // Form submit
    sectionForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(sectionForm);
        const content = {};
        const settings = {};
        
        // Separate content and settings
        for (const [key, value] of formData.entries()) {
            if (key === 'title' || key === 'section-type' || key === 'section-id') continue;
            if (['limit', 'columns', 'featuredOnly'].includes(key)) {
                settings[key] = value;
            } else {
                content[key] = value;
            }
        }

        const payload = {
            section_type: sectionTypeInput.value,
            title: formData.get('title') || null,
            content: content,
            settings: settings,
            status: 'ACTIVE',
            order_index: currentMode === 'create' ? document.querySelectorAll('.section-item').length : null,
        };

        const url = currentMode === 'create' 
            ? '/api/admin/homepage/index.php'
            : `/api/admin/homepage/item.php?id=${encodeURIComponent(currentSectionId)}`;
        const method = currentMode === 'create' ? 'POST' : 'PUT';

        try {
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            
            if (result.status === 'success') {
                showToast(`Section ${currentMode === 'create' ? 'created' : 'updated'} successfully!`, 'success');
                sectionModal.classList.add('hidden');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(result.message || 'Failed to save section', 'error');
            }
        } catch (error) {
            showToast('Error saving section', 'error');
        }
    });

    // Cancel button
    cancelBtn?.addEventListener('click', () => {
        sectionModal.classList.add('hidden');
    });

    // Close modal on backdrop click
    sectionModal?.addEventListener('click', (e) => {
        if (e.target === sectionModal) {
            sectionModal.classList.add('hidden');
        }
    });

    function updateSectionOrder() {
        // Visual update only - actual save happens on button click
        document.querySelectorAll('.section-item').forEach((item, index) => {
            item.dataset.order = index;
        });
    }

    function showToast(message, type = 'success') {
        if (window.toast) {
            window.toast.show(message, type);
        } else {
            alert(message);
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

})();

