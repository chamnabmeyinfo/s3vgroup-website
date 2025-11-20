<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/site.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$db = getDB();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['id'] ?? '';
    $status = $_POST['status'] ?? '';
    
    if ($id && $status) {
        $stmt = $db->prepare("UPDATE quote_requests SET status = ?, updatedAt = NOW() WHERE id = ?");
        $stmt->execute([$status, $id]);
        header('Location: /admin/quotes.php');
        exit;
    }
}

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
                                <form method="POST" class="inline">
                                    <input type="hidden" name="id" value="<?php echo e($quote['id']); ?>">
                                    <select name="status" onchange="this.form.submit()" class="text-xs border border-gray-300 rounded px-2 py-1">
                                        <option value="NEW" <?php echo $quote['status'] === 'NEW' ? 'selected' : ''; ?>>NEW</option>
                                        <option value="IN_PROGRESS" <?php echo $quote['status'] === 'IN_PROGRESS' ? 'selected' : ''; ?>>IN_PROGRESS</option>
                                        <option value="RESOLVED" <?php echo $quote['status'] === 'RESOLVED' ? 'selected' : ''; ?>>RESOLVED</option>
                                        <option value="CLOSED" <?php echo $quote['status'] === 'CLOSED' ? 'selected' : ''; ?>>CLOSED</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?php echo date('M d, Y', strtotime($quote['createdAt'])); ?></td>
                            <td class="px-6 py-4 text-right">
                                <a href="/admin/quotes/view.php?id=<?php echo urlencode($quote['id']); ?>" class="text-sm font-medium text-[#0b3a63] hover:underline">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
