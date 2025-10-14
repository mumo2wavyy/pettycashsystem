<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';
$pettyCashSystem->requireLogin();
$pageTitle = "Approver Dashboard";
include 'header.php';

if ($_SESSION['user_role'] !== 'approver') {
    header('Location: index.php');
    exit;
}

$pendingTransactions = $pettyCashSystem->getPendingTransactions();
$allTransactions = $pettyCashSystem->getAllTransactions();
?>

<div class="container">
    <h1>Welcome, Approver <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
    <div class="quick-actions">
        <a href="approval_queue.php" class="quick-action-btn">Approval Queue</a>
        <a href="reports.php" class="quick-action-btn">Department Reports</a>
    </div>
    <h2>Pending Transactions</h2>
    <?php if (empty($pendingTransactions)): ?>
        <p>No pending transactions.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($pendingTransactions as $transaction): ?>
                <tr>
                    <td><?php echo $pettyCashSystem->formatDate($transaction['transaction_date']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['user_name']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                    <td><?php echo $pettyCashSystem->formatCurrency($transaction['amount']); ?></td>
                    <td>
                        <a href="reviewtransaction.php?id=<?php echo $transaction['id']; ?>">Approve/Reject</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>