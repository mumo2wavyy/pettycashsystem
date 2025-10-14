<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';
$pettyCashSystem->requireLogin();
$pageTitle = "Admin Dashboard";
include 'header.php';

if ($_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Example: User management, audit logs, financial reporting, etc.
?>

<div class="container">
    <h1>Welcome, Admin <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
    <div class="quick-actions">
        <a href="usermanagement.php" class="quick-action-btn">User Management</a>
        <a href="reports.php" class="quick-action-btn">Financial Reports</a>
        <a href="auditlogs.php" class="quick-action-btn">Audit Logs</a>
        <a href="dbmaintenance.php" class="quick-action-btn">Database Maintenance</a>
    </div>
    <h2>System Overview</h2>
    <!-- Add admin-specific stats and controls here -->
</div>
<?php include 'footer.php'; ?>