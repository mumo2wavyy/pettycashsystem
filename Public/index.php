<?php
// index.php - Petty Cash Dashboard - Fixed

require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

$pageTitle = "Petty Cash Dashboard";
include 'header.php';

// Get data using the instance methods
$stats = $pettyCashSystem->getDashboardStats();
$recentActivity = $pettyCashSystem->getRecentActivity(5);
$userTransactions = $pettyCashSystem->getUserTransactions(5);
?>

<div class="container">
    <h1>Petty Cash Dashboard</h1>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="quick-actions">
        <a href="addtransaction.php?type=income" class="quick-action-btn">
            <div style="font-size: 2rem;">💰</div>
            <div>Add Income</div>
        </a>
        <a href="addtransaction.php?type=expense" class="quick-action-btn">
            <div style="font-size: 2rem;">💸</div>
            <div>Add Expense</div>
        </a>
        <a href="transactions.php" class="quick-action-btn">
            <div style="font-size: 2rem;">📊</div>
            <div>View All</div>
        </a>
    </div>
    
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-label">Current Balance</div>
            <div class="stat-value <?php echo $stats['current_balance'] >= 0 ? 'balance-positive' : 'balance-negative'; ?>">
                <?php echo $pettyCashSystem->formatCurrency($stats['current_balance']); ?>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Monthly Income</div>
            <div class="stat-value amount-income"><?php echo $pettyCashSystem->formatCurrency($stats['monthly_income']); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Monthly Expenses</div>
            <div class="stat-value amount-expense"><?php echo $pettyCashSystem->formatCurrency($stats['monthly_expenses']); ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Approvals</div>
            <div class="stat-value"><?php echo $stats['pending_approvals']; ?></div>
        </div>
    </div>
    
    <div class="row">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Recent Activity</div>
                <a href="transactions.php" class="btn">View All</a>
            </div>
            
            <?php if (empty($recentActivity)): ?>
                <p>No recent activity.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentActivity as $transaction): ?>
                        <tr>
                            <td><?php echo $pettyCashSystem->formatDate($transaction['transaction_date']); ?></td>
                            <td><?php echo $transaction['description']; ?></td>
                            <td class="<?php echo $transaction['type'] === 'income' ? 'amount-income' : 'amount-expense'; ?>">
                                <?php echo $pettyCashSystem->formatCurrency($transaction['amount']); ?>
                            </td>
                            <td><?php echo $pettyCashSystem->getTransactionTypeBadge($transaction['type']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <div class="card-header">
                <div class="card-title">My Recent Transactions</div>
                <a href="addtransaction.php" class="btn">Add New</a>
            </div>
            
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($userTransactions as $transaction): ?>
                        <tr>
                            <td><?php echo $pettyCashSystem->formatDate($transaction['transaction_date']); ?></td>
                            <td><?php echo $transaction['description']; ?></td>
                            <td class="<?php echo $transaction['type'] === 'income' ? 'amount-income' : 'amount-expense'; ?>">
                                <?php echo $pettyCashSystem->formatCurrency($transaction['amount']); ?>
                            </td>
                            <td><?php echo $pettyCashSystem->getStatusBadge($transaction['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>