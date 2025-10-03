
<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// For testing - Set dummy session data (REMOVE THIS IN PRODUCTION)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'admin'; // Change to 'user' or 'approver' to test different views
}

// Include the PettyCashSystem class - adjust path as needed
require_once '../functions.php';

// Set page title for header
$pageTitle = 'Petty Cash Transactions';
include 'header.php';

// Initialize filters array
$filters = [];

// Get filters from request if provided
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }
    if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
        $filters['date_from'] = $_GET['date_from'];
    }
    if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
        $filters['date_to'] = $_GET['date_to'];
    }
}

$pettyCashSystem = new PettyCashSystem();

// Check user role and get appropriate transactions
if ($pettyCashSystem->isApprover() || $pettyCashSystem->isAdmin()) {
    $transactions = $pettyCashSystem->getAllTransactions($filters);
    $userType = "Admin/Approver";
} else {
    $transactions = $pettyCashSystem->getUserTransactions($filters);
    $userType = "Regular User";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petty Cash Transactions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .user-info {
            background: #e7f3ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .filters {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .filters form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        .filter-group label {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .filter-group input, .filter-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 8px 16px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .status-badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Petty Cash Transactions</h1>
        
        <div class="user-info">
            <strong>User Type:</strong> <?php echo htmlspecialchars($userType); ?> | 
            <strong>User ID:</strong> <?php echo htmlspecialchars($_SESSION['user_id']); ?> | 
            <strong>Total Transactions:</strong> <?php echo count($transactions); ?>
        </div>

        <!-- Filters Section -->
        <div class="filters">
            <form method="GET">
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select name="status" id="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo isset($filters['status']) && $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo isset($filters['status']) && $filters['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo isset($filters['status']) && $filters['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="date_from">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="<?php echo $filters['date_from'] ?? ''; ?>">
                </div>
                
                <div class="filter-group">
                    <label for="date_to">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="<?php echo $filters['date_to'] ?? ''; ?>">
                </div>
                
                <div class="filter-group">
                    <button type="submit">Apply Filters</button>
                </div>
                
                <div class="filter-group">
                    <button type="button" onclick="window.location.href='transactions.php'">Clear Filters</button>
                </div>
            </form>
        </div>

        <!-- Transactions Table -->
        <?php if (empty($transactions)): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <h3>No transactions found</h3>
                <p>There are no transactions to display with the current filters.</p>
                <?php if ($pettyCashSystem->isAdmin() || $pettyCashSystem->isApprover()): ?>
                    <p><small>Admin/Approver view: Showing all system transactions</small></p>
                <?php else: ?>
                    <p><small>User view: Showing only your transactions</small></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <?php if ($pettyCashSystem->isApprover() || $pettyCashSystem->isAdmin()): ?>
                            <th>User</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($transaction['id'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($transaction['transaction_date'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($transaction['description'] ?? 'No description'); ?></td>
                            <td>$<?php echo number_format($transaction['amount'] ?? 0, 2); ?></td>
                            <td>
                                <?php 
                                $status = $transaction['status'] ?? 'pending';
                                $statusClass = 'status-' . $status;
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                            <?php if ($pettyCashSystem->isApprover() || $pettyCashSystem->isAdmin()): ?>
                                <td><?php echo htmlspecialchars($transaction['user_name'] ?? 'Unknown User'); ?></td>
                            <?php endif; ?>
                            <td>
                                <button onclick="viewTransaction(<?php echo $transaction['id'] ?? '0'; ?>)">View</button>
                                <?php if (($pettyCashSystem->isApprover() || $pettyCashSystem->isAdmin()) && ($transaction['status'] ?? '') === 'pending'): ?>
                                    <button onclick="approveTransaction(<?php echo $transaction['id'] ?? '0'; ?>)" style="background: #28a745;">Approve</button>
                                    <button onclick="rejectTransaction(<?php echo $transaction['id'] ?? '0'; ?>)" style="background: #dc3545;">Reject</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        function viewTransaction(id) {
            alert('View transaction: ' + id);
            // Implement view functionality
        }
        
        function approveTransaction(id) {
            if (confirm('Are you sure you want to approve this transaction?')) {
                alert('Approving transaction: ' + id);
                // Implement approve functionality - you can make an AJAX call here
            }
        }
        
        function rejectTransaction(id) {
            if (confirm('Are you sure you want to reject this transaction?')) {
                alert('Rejecting transaction: ' + id);
                // Implement reject functionality - you can make an AJAX call here
            }
        }
    </script>
</body>
<?php include 'footer.php'; ?>