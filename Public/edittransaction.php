<?php
// edittransaction.php - Page to edit pending transactions

require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// Require login
$pettyCashSystem->requireLogin();

$pageTitle = "Edit Transaction";
include 'header.php';

$transactionId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$transaction = $pettyCashSystem->getTransaction($transactionId);

// Check if transaction exists and user has permission to edit
if (!$transaction) {
    echo "<div class='container'><div class='alert alert-danger'>Transaction not found.</div></div>";
    include 'footer.php';
    exit;
}

if ($transaction['user_id'] != $_SESSION['user_id'] || $transaction['status'] !== 'pending') {
    echo "<div class='container'><div class='alert alert-danger'>You can only edit your own pending transactions.</div></div>";
    include 'footer.php';
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $pettyCashSystem->sanitize($_POST['category']);
    $amount = floatval($_POST['amount']);
    $description = $pettyCashSystem->sanitize($_POST['description']);
    $transaction_date = $pettyCashSystem->sanitize($_POST['transaction_date']);
    
    $errors = [];
    
    if (empty($category)) $errors[] = "Category is required";
    if (!$pettyCashSystem->validateAmount($amount)) $errors[] = "Valid amount is required";
    if (empty($description)) $errors[] = "Description is required";
    if (empty($transaction_date)) $errors[] = "Transaction date is required";
    
    if ($transaction['type'] === 'expense' && !$pettyCashSystem->checkExpenseLimit($amount)) {
        $errors[] = "Expense amount exceeds single transaction limit of " . $pettyCashSystem->formatCurrency(MAX_SINGLE_EXPENSE);
    }
    
    if (empty($errors)) {
        $db = new Database();
        $sql = "UPDATE transactions 
                SET category = ?, amount = ?, description = ?, transaction_date = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $result = $db->executeQuery($sql, [$category, $amount, $description, $transaction_date, $transactionId]);
        
        if ($result) {
            $_SESSION['success_message'] = "Transaction updated successfully!";
            $pettyCashSystem->redirect('viewtransaction.php?id=' . $transactionId);
        } else {
            $errors[] = "Failed to update transaction. Please try again.";
        }
    }
}
?>

<div class="container">
    <h1>Edit Transaction</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <form method="POST" action="">
            <div class="form-group">
                <label>Transaction Type</label>
                <div class="transaction-type">
                    <div class="type-btn <?php echo $transaction['type']; ?> active" style="cursor: not-allowed; opacity: 0.7;">
                        <?php echo $transaction['type'] === 'income' ? '💰 Income' : '💸 Expense'; ?>
                    </div>
                </div>
                <input type="hidden" name="type" value="<?php echo $transaction['type']; ?>">
            </div>
            
            <div class="form-group">
                <label for="transaction_date">Transaction Date *</label>
                <input type="date" id="transaction_date" name="transaction_date" required 
                       value="<?php echo isset($_POST['transaction_date']) ? $_POST['transaction_date'] : $transaction['transaction_date']; ?>">
            </div>
            
            <div class="form-group">
                <label for="category">Category *</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <?php
                    $categories = $transaction['type'] === 'income' ? 
                        ['Cash Replenishment', 'Miscellaneous Income', 'Refund', 'Transfer'] :
                        ['Office Supplies', 'Meals & Entertainment', 'Travel Expenses', 'Utilities', 
                         'Maintenance', 'Postage & Shipping', 'Subscriptions', 'Training', 
                         'Emergency', 'Miscellaneous'];
                    
                    foreach ($categories as $cat): 
                    ?>
                        <option value="<?php echo $cat; ?>" 
                            <?php echo (isset($_POST['category']) && $_POST['category'] === $cat) || $transaction['category'] === $cat ? 'selected' : ''; ?>>
                            <?php echo $cat; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="amount">Amount (KSh) *</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01" required 
                       value="<?php echo isset($_POST['amount']) ? $_POST['amount'] : $transaction['amount']; ?>">
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="4" required placeholder="Describe the purpose of this transaction"><?php echo isset($_POST['description']) ? $_POST['description'] : $transaction['description']; ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-success">Update Transaction</button>
                <a href="viewtransaction.php?id=<?php echo $transaction['id']; ?>" class="btn">Cancel</a>
                <a href="deletetransaction.php?id=<?php echo $transaction['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this transaction?')">Delete</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    amountInput.addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });
});
</script>

<?php include 'footer.php'; ?>