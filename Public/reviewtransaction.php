<?php
// reviewtransaction.php - Page for reviewing and approving transactions

$root = $_SERVER['DOCUMENT_ROOT'] . '/pettycashsystem';
require_once $root . '/config.php';
require_once $root . '/functions.php';

if (!$pettyCashSystem->isLoggedIn() || (!PettyCashSystem::isApprover() && !PettyCashSystem::isAdmin())) {
    PettyCashSystem::redirect('index.php');
}

$pageTitle = "Review Transaction";
include 'header.php';

$transactionId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$transaction = PettyCashSystem::getTransaction($transactionId);

if (!$transaction) {
    echo "<div class='container'><div class='alert alert-danger'>Transaction not found.</div></div>";
    include 'footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = PettyCashSystem::sanitize($_POST['action']);
    $notes = PettyCashSystem::sanitize($_POST['notes']);
    
    if ($action === 'approve' || $action === 'reject') {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $result = PettyCashSystem::updateTransactionStatus($transactionId, $status, $notes);
        
        if ($result) {
            $_SESSION['success_message'] = "Transaction " . $action . "d successfully!";
            PettyCashSystem::redirect('approvalqueue.php');
        } else {
            $error = "Failed to update transaction status.";
        }
    }
}
?>

<div class="container">
    <h1>Review Transaction</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Transaction Details</div>
                <span class="badge badge-<?php echo $transaction['type'] === 'income' ? 'income' : 'expense'; ?>">
                    <?php echo ucfirst($transaction['type']); ?>
                </span>
            </div>
            
            <div class="transaction-details">
                <div class="detail-row">
                    <div class="detail-label">Transaction ID:</div>
                    <div class="detail-value">#TR-<?php echo str_pad($transaction['id'], 4, '0', STR_PAD_LEFT); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Submitted By:</div>
                    <div class="detail-value"><?php echo $transaction['user_name']; ?> (<?php echo $transaction['department']; ?>)</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date:</div>
                    <div class="detail-value"><?php echo PettyCashSystem::formatDate($transaction['transaction_date']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Category:</div>
                    <div class="detail-value"><?php echo $transaction['category']; ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Amount:</div>
                    <div class="detail-value <?php echo $transaction['type'] === 'income' ? 'amount-income' : 'amount-expense'; ?>">
                        <?php echo PettyCashSystem::formatCurrency($transaction['amount']); ?>
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Description:</div>
                    <div class="detail-value"><?php echo $transaction['description']; ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value"><?php echo PettyCashSystem::getStatusBadge($transaction['status']); ?></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Submitted On:</div>
                    <div class="detail-value"><?php echo PettyCashSystem::formatDateTime($transaction['created_at']); ?></div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <div class="card-title">Review & Action</div>
            </div>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="notes">Review Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="4" placeholder="Add any comments or notes for this transaction..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Action</label>
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" name="action" value="approve" class="btn btn-success" style="flex: 1;">
                            ✅ Approve Transaction
                        </button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger" style="flex: 1;">
                            ❌ Reject Transaction
                        </button>
                    </div>
                </div>
                
                <div class="form-group">
                    <a href="approvalqueue.php" class="btn">Back to Queue</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>