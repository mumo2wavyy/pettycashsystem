<?php
// approvalqueue.php - Page for approvers to review pending transactions

$root = $_SERVER['DOCUMENT_ROOT'] . '/pettycashsystem';
require_once $root . '/config.php';
require_once $root . '/functions.php';

if (!$pettyCashSystem->isLoggedIn() || (!$pettyCashSystem->isApprover() && !$pettyCashSystem->isAdmin())) {
    $pettyCashSystem->redirect('index.php');
}

$pageTitle = "Approval Queue";
include 'header.php';

$pendingTransactions = PettyCashSystem::getPendingTransactions();
?>

<div class="container">
    <h1>Approval Queue</h1>
    
    <?php if (empty($pendingTransactions)): ?>
        <div class="alert alert-success">
            <p>No pending transactions for approval. Great job!</p>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Pending Transactions (<?php echo count($pendingTransactions); ?>)</div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>User</th>
                        <th>Department</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pendingTransactions as $transaction): ?>
                    <tr>
                        <td><?php echo PettyCashSystem::formatDate($transaction['transaction_date']); ?></td>
                        <td><?php echo $transaction['user_name']; ?></td>
                        <td><?php echo ucfirst($transaction['department']); ?></td>
                        <td><?php echo $transaction['description']; ?></td>
                        <td class="<?php echo $transaction['type'] === 'income' ? 'amount-income' : 'amount-expense'; ?>">
                            <?php echo PettyCashSystem::formatCurrency($transaction['amount']); ?>
                        </td>
                        <td><?php echo $transaction['category']; ?></td>
                        <td><?php echo PettyCashSystem::getTransactionTypeBadge($transaction['type']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="reviewtransaction.php?id=<?php echo $transaction['id']; ?>" class="btn">Review</a>
                                <a href="viewtransaction.php?id=<?php echo $transaction['id']; ?>" class="btn">View</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>