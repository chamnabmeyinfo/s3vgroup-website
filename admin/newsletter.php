<?php
session_start();
// Load bootstrap FIRST to ensure env() function is available
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

use App\Database\Connection;
use App\Domain\Content\NewsletterRepository;

$db = getDB();
$repository = new NewsletterRepository($db);
$subscribers = $repository->all();
$activeCount = $repository->count();

$pageTitle = 'Newsletter';
include __DIR__ . '/includes/header.php';
?>

<div class="space-y-8">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Marketing</p>
            <h1 class="text-3xl font-semibold text-[#0b3a63]">Newsletter Subscribers</h1>
            <p class="text-sm text-gray-600">Manage newsletter subscribers and email list</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right">
                <p class="text-xs text-gray-500">Active Subscribers</p>
                <p class="text-2xl font-bold text-[#0b3a63]"><?php echo $activeCount; ?></p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-6 py-3 font-medium">Email</th>
                    <th class="px-6 py-3 font-medium">Name</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Subscribed</th>
                    <th class="px-6 py-3 font-medium">Source</th>
                    <th class="px-6 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($subscribers)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No subscribers yet. Subscribers will appear here once they sign up.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($subscribers as $subscriber): ?>
                        <tr>
                            <td class="px-6 py-4 font-semibold"><?php echo e($subscriber['email']); ?></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo e($subscriber['name'] ?? '—'); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php 
                                    echo $subscriber['status'] === 'ACTIVE' ? 'bg-green-100 text-green-800' : 
                                        ($subscriber['status'] === 'UNSUBSCRIBED' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800'); 
                                ?>">
                                    <?php echo e($subscriber['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?php echo date('M d, Y', strtotime($subscriber['subscribedAt'])); ?></td>
                            <td class="px-6 py-4 text-gray-600"><?php echo e($subscriber['source'] ?? 'website'); ?></td>
                            <td class="px-6 py-4 text-right">
                                <?php if ($subscriber['status'] === 'ACTIVE'): ?>
                                    <button
                                        type="button"
                                        class="text-sm font-medium text-orange-600 hover:text-orange-800 unsubscribe-btn"
                                        data-email="<?php echo htmlspecialchars($subscriber['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                        Unsubscribe
                                    </button>
                                <?php endif; ?>
                                <button
                                    type="button"
                                    class="text-sm font-medium text-red-600 hover:text-red-800 ml-3 delete-btn"
                                    data-id="<?php echo htmlspecialchars($subscriber['id'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-email="<?php echo htmlspecialchars($subscriber['email'], ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function() {
    document.querySelectorAll('.unsubscribe-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const email = button.dataset.email;

            if (!confirm(`Unsubscribe "${email}" from the newsletter?`)) {
                return;
            }

            try {
                const response = await fetch('/api/newsletter/unsubscribe.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email }),
                });

                const result = await response.json();

                if (!response.ok || result.status !== 'success') {
                    throw new Error(result.message || 'Unable to unsubscribe.');
                }

                window.location.reload();
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    });

    document.querySelectorAll('.delete-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const id = button.dataset.id;
            const email = button.dataset.email;

            if (!confirm(`Delete subscriber "${email}"? This action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch(`/api/admin/newsletter/item.php?id=${encodeURIComponent(id)}`, {
                    method: 'DELETE',
                });

                const result = await response.json();

                if (!response.ok || result.status !== 'success') {
                    throw new Error(result.message || 'Unable to delete subscriber.');
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

