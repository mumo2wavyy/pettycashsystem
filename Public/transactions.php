<?php
// transactions.php - Page to view all transactions

require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// Require login
$pettyCashSystem->requireLogin();

$pageTitle = "All Transactions";
include 'header.php';

// Get filter parameters
$type = isset($_GET['type']) ? $_GET['type'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Get transactions based on user role
if ($_SESSION['user_role'] === 'approver' || $_SESSION['user_role'] === 'admin') {
    $transactions = $pettyCashSystem->getAllTransactions();
} else {
    $transactions = $pettyCashSystem->getUserTransactions();
}
?>

<div class="container">
    <h1>All Transactions</h1>
    
    <div class="filter-section">
        <form method="GET" action="">
            <div class="filter-row">
                <div class="form-group">
                    <label for="type">Type</label>
                    <select id="type" name="type">
                        <option value="">All Types</option>
                        <option value="income" <?php echo $type === 'income' ? 'selected' : ''; ?>>Income</option>
                        <option value="expense" <?php echo $type === 'expense' ? 'selected' : ''; ?>>Expense</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <option value="Office Supplies" <?php echo $category === 'Office Supplies' ? 'selected' : ''; ?>>Office Supplies</option>
                        <option value="Meals & Entertainment" <?php echo $category === 'Meals & Entertainment' ? 'selected' : ''; ?>>Meals & Entertainment</option>
                        <option value="Travel Expenses" <?php echo $category === 'Travel Expenses' ? 'selected' : ''; ?>>Travel Expenses</option>
                        <option value="Utilities" <?php echo $category === 'Utilities' ? 'selected' : ''; ?>>Utilities</option>
                    </select>
                </div>
            </div>
            
            <div class="filter-row">
                <div class="form-group">
                    <label for="start_date">From Date</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                
                <div class="form-group">
                    <label for="end_date">To Date</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                
                <div class="form-group" style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn">Apply Filters</button>
                    <a href="transactions.php" class="btn" style="margin-left: 0.5rem;">Clear</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Transactions (<?php echo count($transactions); ?>)</div>
            <a href="addtransaction.php" class="btn">Add New Transaction</a>
        </div>
        
        <?php if (empty($transactions)): ?>
            <div class="no-data">
                <div class="no-data-icon">📊</div>
                <p>No transactions found.</p>
                <a href="addtransaction.php" class="btn">Add Your First Transaction</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo $pettyCashSystem->formatDate($transaction['transaction_date']); ?></td>
                        <td><?php echo $transaction['description']; ?></td>
                        <td><?php echo $transaction['category']; ?></td>
                        <td class="<?php echo $transaction['type'] === 'income' ? 'amount-income' : 'amount-expense'; ?>">
                            <?php echo $pettyCashSystem->formatCurrency($transaction['amount']); ?>
                        </td>
                        <td><?php echo $pettyCashSystem->getTransactionTypeBadge($transaction['type']); ?></td>
                        <td><?php echo $pettyCashSystem->getStatusBadge($transaction['status']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="viewtransaction.php?id=<?php echo $transaction['id']; ?>" class="btn">View</a>
                                <?php if ($transaction['status'] === 'pending' && $transaction['user_id'] == $_SESSION['user_id']): ?>
                                    <a href="edittransaction.php?id=<?php echo $transaction['id']; ?>" class="btn btn-warning">Edit</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>