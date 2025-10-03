<?php
// header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Petty Cash System'; ?></title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="logo">Petty Cash System</div>
                <ul class="nav-links">
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="addtransaction.php">Add Transaction</a></li>
                    <li><a href="transactions.php">All Transactions</a></li>
                    <li><a href="approval_queue.php">Approval Queue</a></li>
                    <li><a href="reports.php">Reports</a></li>
                </ul>
            </nav>
        </div>
    </header>