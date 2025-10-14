<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';
$pettyCashSystem->requireLogin();
$pageTitle = "User Dashboard";
include 'header.php';


if ($_SESSION['user_role'] !== 'user') {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userTransactions = $pettyCashSystem->getUserTransactions($userId);
?>

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
    <div class="quick-actions">
        <a href="addtransaction.php?type=income" class="quick-action-btn">Add Income</a>
        <a href="addtransaction.php?type=expense" class="quick-action-btn">Add Expense</a>
        <a href="reports.php" class="quick-action-btn">My Reports</a>
    </div>
    <h2>My Transactions</h2>
    <?php if (empty($userTransactions)): ?>
        <p>No transactions found.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($userTransactions as $transaction): ?>
                <tr>
                    <td><?php echo $pettyCashSystem->formatDate($transaction['transaction_date']); ?></td>
                    <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                    <td><?php echo $pettyCashSystem->formatCurrency($transaction['amount']); ?></td>
                    <td><?php echo $pettyCashSystem->getStatusBadge($transaction['status']); ?></td>
                    <td>
                        <?php if ($transaction['status'] === 'pending'): ?>
                            <a href="edittransaction.php?id=<?php echo $transaction['id']; ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>