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
    <!-- Add FontAwesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <!-- Logo Section -->
                <div class="logo">
                    <a href="dash.php" style="color: #fff; text-decoration: none;">Petty Cash System</a>
                </div>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                <!-- User Info & Navigation Links -->
                <div class="user-nav">
                    <div class="welcome-message">
                        <span>Welcome, <?php echo $_SESSION['user_name']; ?> </span>
                        <span>(<?php echo ucfirst($_SESSION['user_role']); ?>)</span>
                    </div>
                    <ul class="nav-links">
                        <li><a href="dash.php">Dashboard</a></li>
                        <li><a href="addtransaction.php">Add Transaction</a></li>
                        <li><a href="transactions.php">All Transactions</a></li>
                        <?php if ($_SESSION['user_role'] === 'approver' || $_SESSION['user_role'] === 'admin'): ?>
                            <li><a href="approval_queue.php">Approval Queue</a></li>
                        <?php endif; ?>
                        <li><a href="reports.php">Reports</a></li>
                        <!-- Profile Icon with Link -->
                        <li><a href="profile.php" class="profile-icon"><i class="fas fa-user-circle"></i></a></li>
                        <li><a href="logout.php" class="logout-btn">Logout</a></li>
                    </ul>
                </div>
                <?php else: ?>
                <!-- Navigation Links for Non-logged-in users -->
                <ul class="nav-links">
                    <li><a href="login.php">Login/Register</a></li>
                </ul>
                <?php endif; ?>
            </nav>
        </div>
    </header>
</body>
</html>
