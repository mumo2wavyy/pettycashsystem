
<?php
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// Require login
$pettyCashSystem->requireLogin();

$pageTitle = "My Profile";
include 'header.php';

// Fetch user details from session or database
$userId = $_SESSION['user_id'] ?? null;
$userDetails = [];

if ($userId) {
    $userDetails = $pettyCashSystem->getUserDetails($userId);
}
?>

<div class="container">
    <h1>My Profile</h1>
    <?php if ($userDetails): ?>
        <div class="profile-card">
            <table>
                <tr>
                    <th>Username:</th>
                    <td><?php echo htmlspecialchars($userDetails['username']); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?php echo htmlspecialchars($userDetails['email']); ?></td>
                </tr>
                <tr>
                    <th>Department:</th>
                    <td><?php echo htmlspecialchars($userDetails['department']); ?></td>
                </tr>
                <tr>
                    <th>Role:</th>
                    <td><?php echo ucfirst(htmlspecialchars($userDetails['role'])); ?></td>
                </tr>
                <tr>
                    <th>Member Since:</th>
                    <td><?php echo date('F d, Y', strtotime($userDetails['created_at'])); ?></td>
                </tr>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">User details not found.</div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>