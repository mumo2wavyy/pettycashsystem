<?php
// reports.php - Page for financial reports

$root = $_SERVER['DOCUMENT_ROOT'] . '/pettycashsystem';
require_once $root . '/config.php';
require_once $root . '/functions.php';

if (!$pettyCashSystem->isLoggedIn()) {
    PettyCashSystem::redirect('index.php');
}

$pageTitle = "Financial Reports";
include 'header.php';

$stats = $pettyCashSystem->getDashboardStats();
$incomeCategories = $pettyCashSystem->getCategoryTotals('income', 'month');
$expenseCategories = $pettyCashSystem->getCategoryTotals('expense', 'month');
?>

<div class="container">
    <h1>Financial Reports</h1>
    
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Current Balance</div>
            <div class="summary-value balance-positive"><?php echo $pettyCashSystem->formatCurrency($stats['current_balance']); ?></div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Monthly Income</div>
            <div class="summary-value amount-income"><?php echo $pettyCashSystem->formatCurrency($stats['monthly_income']); ?></div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Monthly Expenses</div>
            <div class="summary-value amount-expense"><?php echo $pettyCashSystem->formatCurrency($stats['monthly_expenses']); ?></div>
        </div>
        <div class="summary-card">
            <div class="summary-label">Net Flow</div>
            <div class="summary-value <?php echo ($stats['monthly_income'] - $stats['monthly_expenses']) >= 0 ? 'balance-positive' : 'balance-negative'; ?>">
                <?php echo $pettyCashSystem->formatCurrency($stats['monthly_income'] - $stats['monthly_expenses']); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="report-card">
            <div class="report-header">
                <div class="report-title">Income by Category</div>
            </div>
            <?php if (empty($incomeCategories)): ?>
                <p>No income data available.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalIncome = array_sum(array_column($incomeCategories, 'total'));
                        foreach($incomeCategories as $category): 
                            $percentage = $totalIncome > 0 ? ($category['total'] / $totalIncome) * 100 : 0;
                        ?>
                        <tr>
                            <td><?php echo $category['category']; ?></td>
                            <td class="amount-income"><?php echo $pettyCashSystem->formatCurrency($category['total']); ?></td>
                            <td><?php echo number_format($percentage, 1); ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="report-card">
            <div class="report-header">
                <div class="report-title">Expenses by Category</div>
            </div>
            <?php if (empty($expenseCategories)): ?>
                <p>No expense data available.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalExpenses = array_sum(array_column($expenseCategories, 'total'));
                        foreach($expenseCategories as $category): 
                            $percentage = $totalExpenses > 0 ? ($category['total'] / $totalExpenses) * 100 : 0;
                        ?>
                        <tr>
                            <td><?php echo $category['category']; ?></td>
                            <td class="amount-expense"><?php echo $pettyCashSystem->formatCurrency($category['total']); ?></td>
                            <td><?php echo number_format($percentage, 1); ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <div class="report-card">
        <div class="report-header">
            <div class="report-title">Recent Transactions</div>
        </div>
        <?php 
    $recentTransactions = $pettyCashSystem->getRecentActivity(10);
        if (empty($recentTransactions)): ?>
            <p>No recent transactions.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recentTransactions as $transaction): ?>
                    <tr>
                        <td><?php echo $pettyCashSystem->formatDate($transaction['transaction_date']); ?></td>
                        <td><?php echo $transaction['description']; ?></td>
                        <td><?php echo $transaction['category']; ?></td>
                        <td class="<?php echo $transaction['type'] === 'income' ? 'amount-income' : 'amount-expense'; ?>">
                            <?php echo PettyCashSystem::formatCurrency($transaction['amount']); ?>
                        </td>
                        <td><?php echo $pettyCashSystem->getTransactionTypeBadge($transaction['type']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="text-center mt-3">
        <button onclick="window.print()" class="btn">Print Report</button>
    </div>
</div>

<?php include 'footer.php'; ?>