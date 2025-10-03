<?php
// addtransaction.php - Page for adding new transactions

$root = $_SERVER['DOCUMENT_ROOT'] . '/pettycashsystem';
require_once $root . '/config.php';
require_once $root . '/functions.php';

if (!$pettyCashSystem->isLoggedIn()) {
    PettyCashSystem::redirect('index.php');
}

$pageTitle = "Add Transaction";
include 'header.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = PettyCashSystem::sanitize($_POST['type']);
    $category = PettyCashSystem::sanitize($_POST['category']);
    $amount = floatval($_POST['amount']);
    $description = PettyCashSystem::sanitize($_POST['description']);
    $transaction_date = PettyCashSystem::sanitize($_POST['transaction_date']);
    
    $errors = [];
    
    if (empty($type)) $errors[] = "Transaction type is required";
    if (empty($category)) $errors[] = "Category is required";
    if (!PettyCashSystem::validateAmount($amount)) $errors[] = "Valid amount is required";
    if (empty($description)) $errors[] = "Description is required";
    if (empty($transaction_date)) $errors[] = "Transaction date is required";
    
    if ($type === 'expense' && !PettyCashSystem::checkExpenseLimit($amount)) {
        $errors[] = "Expense amount exceeds single transaction limit of " . PettyCashSystem::formatCurrency(MAX_SINGLE_EXPENSE);
    }
    
    if ($type === 'income' && !PettyCashSystem::checkReplenishmentLimit($amount)) {
        $errors[] = "Replenishment would exceed maximum petty cash balance of " . PettyCashSystem::formatCurrency(MAX_PETTY_CASH_AMOUNT);
    }
    
    if (empty($errors)) {
        $transactionData = [
            'type' => $type,
            'category' => $category,
            'amount' => $amount,
            'description' => $description,
            'transaction_date' => $transaction_date,
            'status' => 'pending'
        ];
        
        $transactionId = PettyCashSystem::createTransaction($transactionData);
        
        if ($transactionId) {
            $_SESSION['success_message'] = "Transaction submitted successfully! Waiting for approval.";
            PettyCashSystem::redirect('index.php');
        } else {
            $errors[] = "Failed to create transaction. Please try again.";
        }
    }
}
?>

<div class="container">
    <h1>Add New Transaction</h1>
    
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
                <label>Transaction Type *</label>
                <div class="transaction-type">
                    <div class="type-btn income <?php echo ($type === 'income') ? 'active' : ''; ?>" data-type="income">
                        💰 Income
                    </div>
                    <div class="type-btn expense <?php echo ($type === 'expense') ? 'active' : ''; ?>" data-type="expense">
                        💸 Expense
                    </div>
                </div>
                <input type="hidden" id="type" name="type" value="<?php echo $type; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="transaction_date">Transaction Date *</label>
                <input type="date" id="transaction_date" name="transaction_date" required 
                       value="<?php echo isset($_POST['transaction_date']) ? $_POST['transaction_date'] : date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label for="category">Category *</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="amount">Amount (KSh) *</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01" required 
                       value="<?php echo isset($_POST['amount']) ? $_POST['amount'] : ''; ?>" 
                       placeholder="0.00">
            </div>
            
            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" rows="4" required placeholder="Describe the purpose of this transaction..."><?php echo isset($_POST['description']) ? $_POST['description'] : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-success">Submit Transaction</button>
                <a href="index.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <div class="card-title">Transaction Limits</div>
        </div>
        <div class="transaction-details">
            <div class="detail-row">
                <div class="detail-label">Maximum Single Expense:</div>
                <div class="detail-value amount-expense"><?php echo PettyCashSystem::formatCurrency(MAX_SINGLE_EXPENSE); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Maximum Petty Cash Balance:</div>
                <div class="detail-value amount-income"><?php echo PettyCashSystem::formatCurrency(MAX_PETTY_CASH_AMOUNT); ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Current Balance:</div>
                <div class="detail-value <?php echo PettyCashSystem::getCurrentBalance() >= 0 ? 'balance-positive' : 'balance-negative'; ?>">
                    <?php echo PettyCashSystem::formatCurrency(PettyCashSystem::getCurrentBalance()); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeButtons = document.querySelectorAll('.type-btn');
    const typeInput = document.getElementById('type');
    const categorySelect = document.getElementById('category');
    
    const categories = {
        income: ['Cash Replenishment', 'Miscellaneous Income', 'Refund', 'Transfer'],
        expense: ['Office Supplies', 'Meals & Entertainment', 'Travel Expenses', 'Utilities', 
                 'Maintenance', 'Postage & Shipping', 'Subscriptions', 'Training', 
                 'Emergency', 'Miscellaneous']
    };
    
    typeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const selectedType = this.getAttribute('data-type');
            
            typeButtons.forEach(btn => {
                btn.classList.remove('active');
                btn.classList.remove('income', 'expense');
            });
            this.classList.add('active');
            this.classList.add(selectedType);
            
            typeInput.value = selectedType;
            updateCategories(selectedType);
        });
    });
    
    function updateCategories(type) {
        categorySelect.innerHTML = '<option value="">Select Category</option>';
        
        if (categories[type]) {
            categories[type].forEach(category => {
                const option = document.createElement('option');
                option.value = category;
                option.textContent = category;
                categorySelect.appendChild(option);
            });
        }
        
        categorySelect.disabled = false;
    }
    
    if (typeInput.value) {
        const activeButton = document.querySelector(`.type-btn[data-type="${typeInput.value}"]`);
        if (activeButton) {
            activeButton.classList.add('active');
            activeButton.classList.add(typeInput.value);
        }
        updateCategories(typeInput.value);
    } else {
        categorySelect.disabled = true;
    }
    
    const amountInput = document.getElementById('amount');
    amountInput.addEventListener('blur', function() {
        if (this.value) {
            this.value = parseFloat(this.value).toFixed(2);
        }
    });
});
</script>

<?php include 'footer.php'; ?>