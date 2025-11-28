/**
 * Pages Management - Enhanced with Visual Builder Integration
 */

(function() {
    'use strict';

    // Add quick design buttons to existing page list
    document.addEventListener('DOMContentLoaded', () => {
        // Enhance page list with better visual builder access
        const pageRows = document.querySelectorAll('tbody tr[data-id]');
        
        pageRows.forEach(row => {
            const pageId = row.dataset.id;
            const statusCell = row.querySelector('td:nth-child(4)');
            
            // Add status indicator with design button
            if (statusCell) {
                const statusBadge = statusCell.querySelector('span');
                if (statusBadge && statusBadge.textContent.trim() === 'DRAFT') {
                    // Add "Design Now" hint for draft pages
                    const hint = document.createElement('div');
                    hint.className = 'text-xs text-gray-500 mt-1';
                    hint.textContent = 'Click Visual Builder to design';
                    // statusCell.appendChild(hint);
                }
            }
        });
    });

    // Enhanced page creation - redirect to builder
    const pageForm = document.getElementById('page-form');
    if (pageForm) {
        const originalSubmit = pageForm.onsubmit;
        
        pageForm.addEventListener('submit', async (e) => {
            // Let the form handle submission first
            // The modal handler will redirect after success
        });
    }

})();

