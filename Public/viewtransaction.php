<?php
// viewtransaction.php - Page to view transaction details

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

// Redirect to login if not authenticated
if (!$pettyCashSystem->isLoggedIn()) {
    PettyCashSystem::redirect('index.php');
}

$pageTitle = "View Transaction";
include 'header.php';

// Get transaction ID
$transactionId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$transaction = PettyCashSystem::getTransaction($transactionId);

if (!$transaction) {
    echo "<div class='container'><div class='alert alert-danger'>Transaction not found.</div></div>";
    include 'footer.php';
    exit;
}

// Check if user has permission to view this transaction
if (!PettyCashSystem::isApprover() && !PettyCashSystem::isAdmin() && $transaction['user_id'] != $_SESSION['user_id']) {
    echo "<div class='container'><div class='alert alert-danger'>You don't have permission to view this transaction.</div></div>";
    include 'footer.php';
    exit;
}
?>

<div class="container">
    <h1>Transaction Details</h1>
    
    <div class="row">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    Transaction #TR-<?php echo str_pad($transaction['id'], 4, '0', STR_PAD_LEFT); ?>
                </div>
                <div>
                    <?php echo PettyCashSystem::getTransactionTypeBadge($transaction['type']); ?>
                    <?php echo PettyCashSystem::getStatusBadge($transaction['status']); ?>
                </div>
            </div>
            
            <div class="transaction-details">
                <div class="detail-row">
                    <div class="detail-label">Submitted By:</div>
                    <div class="detail-value"><?php echo $transaction['user_name']; ?> (<?php echo $transaction['department']; ?>)</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Transaction Date:</div>
                    <div class="detail-value"><?php echo PettyCashSystem::formatDate($transaction['transaction_date']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Category:</div>
                    <div class="detail-value"><?php echo $transaction['category']; ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Amount:</div>
                    <div class="detail-value <?php echo $transaction['type'] === 'income' ? 'amount-income' : 'amount-expense'; ?>">
                        <strong><?php echo PettyCashSystem::formatCurrency($transaction['amount']); ?></strong>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Description:</div>
                    <div class="detail-value"><?php echo nl2br($transaction['description']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Submitted On:</div>
                    <div class="detail-value"><?php echo PettyCashSystem::formatDateTime($transaction['created_at']); ?></div>
                </div>
                
                <?php if ($transaction['status'] !== 'pending'): ?>
                <div class="detail-row">
                    <div class="detail-label">Approved/Rejected By:</div>
                    <div class="detail-value"><?php echo $transaction['approved_by_name'] ?? 'N/A'; ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Approved/Rejected On:</div>
                    <div class="detail-value"><?php echo $transaction['approved_at'] ? PettyCashSystem::formatDateTime($transaction['approved_at']) : 'N/A'; ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Approval Notes:</div>
                    <div class="detail-value"><?php echo $transaction['approval_notes'] ? nl2br($transaction['approval_notes']) : 'No notes provided'; ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="action-buttons" style="margin-top: 1.5rem;">
                <a href="transactions.php" class="btn">Back to Transactions</a>
                <?php if ($transaction['status'] === 'pending' && $transaction['user_id'] == $_SESSION['user_id']): ?>
                    <a href="edittransaction.php?id=<?php echo $transaction['id']; ?>" class="btn btn-warning">Edit Transaction</a>
                <?php endif; ?>
                <?php if (PettyCashSystem::isApprover() || PettyCashSystem::isAdmin()): ?>
                    <a href="approvalqueue.php" class="btn">Back to Approval Queue</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>