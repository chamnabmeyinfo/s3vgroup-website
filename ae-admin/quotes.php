<?php
session_start();
// Load bootstrap FIRST to ensure env() function is available
// Check ae-load.php first, then wp-load.php as fallback
if (file_exists(__DIR__ . '/../ae-load.php')) {
    require_once __DIR__ . '/../ae-load.php';
} else {
    require_once __DIR__ . '/../wp-load.php';
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
// Load functions (check ae-includes first, then wp-includes as fallback)
if (file_exists(__DIR__ . '/../ae-includes/functions.php')) {
    require_once __DIR__ . '/../ae-includes/functions.php';
} else {
    require_once __DIR__ . '/../wp-includes/functions.php';
}

requireAdmin();

$db = getDB();
$quotes = $db->query("SELECT * FROM quote_requests ORDER BY createdAt DESC LIMIT 20")->fetchAll();

$pageTitle = 'Quotes';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-6">
    <div>
        <p class="text-sm uppercase tracking-wide text-gray-500">Pipeline</p>
        <h1 class="text-3xl font-semibold text-[#0b3a63]">Quotes</h1>
        <p class="text-sm text-gray-600">Track inbound requests and support follow-up</p>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-6 py-3 font-medium">Company</th>
                    <th class="px-6 py-3 font-medium">Contact</th>
                    <th class="px-6 py-3 font-medium">Email</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Submitted</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($quotes)): ?>
                    <tr>
                        <td colspan="6" class="p-6 text-sm text-gray-500 text-center">
                            No quote data yet. Requests submitted on the public site will appear here.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($quotes as $quote): ?>
                        <tr>
                            <td class="px-6 py-4 font-semibold"><?php echo e($quote['companyName']); ?></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo e($quote['contactName']); ?></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo e($quote['email']); ?></td>
                            <td class="px-6 py-4">
                                <select data-id="<?php echo e($quote['id']); ?>" class="quote-status-select text-xs border border-gray-300 rounded px-2 py-1 focus:border-[#0b3a63] focus:ring-[#0b3a63]">
                                    <option value="NEW" <?php echo $quote['status'] === 'NEW' ? 'selected' : ''; ?>>NEW</option>
                                    <option value="IN_PROGRESS" <?php echo $quote['status'] === 'IN_PROGRESS' ? 'selected' : ''; ?>>IN_PROGRESS</option>
                                    <option value="RESOLVED" <?php echo $quote['status'] === 'RESOLVED' ? 'selected' : ''; ?>>RESOLVED</option>
                                    <option value="CLOSED" <?php echo $quote['status'] === 'CLOSED' ? 'selected' : ''; ?>>CLOSED</option>
                                </select>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?php echo date('M d, Y', strtotime($quote['createdAt'])); ?></td>
                            <td class="px-6 py-4 text-right">
                                <?php
                                    $quoteData = $quote;
                                    $quoteData['items'] = $quote['items'] ? json_decode($quote['items'], true) ?? [] : [];
                                ?>
                                <div class="flex items-center justify-end gap-3">
                                    <button
                                        type="button"
                                        class="text-sm font-medium text-[#0b3a63] hover:underline quote-view-btn"
                                        data-quote="<?php echo htmlspecialchars(json_encode($quoteData), ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        View
                                    </button>
                                    <button
                                        type="button"
                                        class="text-sm font-medium text-red-600 hover:text-red-800 quote-delete-btn"
                                        data-id="<?php echo htmlspecialchars($quote['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-company="<?php echo htmlspecialchars($quote['companyName'], ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="quote-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
    <div class="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl">
        <div class="flex items-center justify-between border-b border-gray-200 pb-4">
            <div>
                <h2 class="text-xl font-semibold text-[#0b3a63]" id="quote-modal-title">Quote details</h2>
                <p class="text-sm text-gray-500">Customer submission overview</p>
            </div>
            <button type="button" id="quote-modal-close" class="text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <div class="mt-4 space-y-3 text-sm text-gray-700" id="quote-modal-content"></div>
        <div class="mt-6 text-right">
            <button type="button" id="quote-modal-dismiss" class="rounded border border-gray-300 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                Close
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const statusSelects = document.querySelectorAll('.quote-status-select');
    const modal = document.getElementById('quote-modal');
    const modalContent = document.getElementById('quote-modal-content');
    const modalTitle = document.getElementById('quote-modal-title');
    const modalClose = document.getElementById('quote-modal-close');
    const modalDismiss = document.getElementById('quote-modal-dismiss');
    let currentSelect = null;

    async function updateStatus(select) {
        const id = select.dataset.id;
        const status = select.value;

        try {
            const response = await fetch(`/api/admin/quotes/item.php?id=${encodeURIComponent(id)}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ status }),
            });

            const result = await response.json();

            if (!response.ok || result.status !== 'success') {
                throw new Error(result.message || 'Unable to update status.');
            }
        } catch (error) {
            alert(error.message);
            // revert to previous value
            select.value = select.getAttribute('data-prev') || 'NEW';
        } finally {
            select.removeAttribute('disabled');
        }
    }

    statusSelects.forEach((select) => {
        select.setAttribute('data-prev', select.value);
        select.addEventListener('focus', () => select.setAttribute('data-prev', select.value));
        select.addEventListener('change', () => {
            select.setAttribute('disabled', 'disabled');
            updateStatus(select);
        });
    });

    function renderQuoteDetails(data) {
        modalTitle.textContent = data.companyName || 'Quote details';
        const rows = [
            ['Company', data.companyName],
            ['Contact', data.contactName],
            ['Email', data.email],
            ['Phone', data.phone || '—'],
            ['Status', data.status],
            ['Source', data.source || 'Website'],
            ['Message', data.message || '—'],
        ];

        const items = Array.isArray(data.items) ? data.items : [];

        modalContent.innerHTML = rows.map(([label, value]) => `
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-500">${label}</p>
                <p class="font-medium text-gray-900">${value || '—'}</p>
            </div>
        `).join('');

        if (items.length) {
            const list = items.map((item) => `
                <li class="rounded border border-gray-200 px-3 py-2">
                    <p class="font-medium">${item.name || item.id || 'Item'}</p>
                    <p class="text-xs text-gray-500">Qty: ${item.quantity ?? 1}</p>
                    ${item.notes ? `<p class="text-xs text-gray-500 mt-1">${item.notes}</p>` : ''}
                </li>
            `).join('');

            modalContent.innerHTML += `
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500 mb-2">Requested Items</p>
                    <ul class="space-y-2">${list}</ul>
                </div>
            `;
        }
    }

    function showModal(data) {
        renderQuoteDetails(data);
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function hideModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    modalClose.addEventListener('click', hideModal);
    modalDismiss.addEventListener('click', hideModal);

    document.querySelectorAll('.quote-view-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const data = JSON.parse(button.dataset.quote);
            showModal(data);
        });
    });

    // Delete functionality
    document.querySelectorAll('.quote-delete-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const id = button.dataset.id;
            const company = button.dataset.company;

            if (!confirm(`Are you sure you want to delete the quote from "${company}"? This action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch(`/api/admin/quotes/item.php?id=${encodeURIComponent(id)}`, {
                    method: 'DELETE',
                });

                const result = await response.json();

                if (!response.ok || result.status !== 'success') {
                    throw new Error(result.message || 'Unable to delete quote.');
                }

                window.location.reload();
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
