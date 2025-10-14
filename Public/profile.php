<?php
session_start();
require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user data
$user = getUserById($user_id);
if (!$user) {
    header('Location: logout.php');
    exit;
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $result = handleProfilePictureUpload($user_id);
    if ($result['success']) {
        $success = $result['message'];
        $user = getUserById($user_id); // Refresh user data
    } else {
        $error = $result['message'];
    }
}

// Get user's transaction history
$transactions = getUserTransactions($user_id, 10); // Last 10 transactions
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Petty Cash System</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .profile-info {
            margin-left: 30px;
            flex: 1;
        }
        
        .profile-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        
        .profile-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .info-group {
            margin-bottom: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #333;
        }
        
        .btn-edit {
            background: #007bff;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-edit:hover {
            background: #0056b3;
        }
        
        .transaction-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .transaction-amount.income {
            color: #28a745;
            font-weight: bold;
        }
        
        .transaction-amount.expense {
            color: #dc3545;
            font-weight: bold;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        
        .upload-form {
            margin-top: 15px;
        }
        
        .contact-admin-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div>
                <img src="<?php echo getProfilePicture($user['profile_picture']); ?>" 
                     alt="Profile Picture" class="profile-picture">
                
                <!-- Profile Picture Upload Form -->
                <form method="POST" enctype="multipart/form-data" class="upload-form">
                    <input type="file" name="profile_picture" accept="image/*" required>
                    <button type="submit" class="btn-edit">Upload Photo</button>
                </form>
            </div>
            
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['name']); ?></h1>
                <p class="role-badge"><?php echo ucfirst($user['role']); ?></p>
                <p><?php echo htmlspecialchars($user['department']); ?> Department</p>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>
        
        <!-- Error/Success Messages -->
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Profile Sections -->
        <div class="profile-sections">
            <!-- Personal Information -->
            <div class="profile-card">
                <h2>Personal Information</h2>
                
                <div class="info-group">
                    <div class="info-label">Full Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['name']); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Email Address</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value">
                        <?php echo $user['phone_number'] ? htmlspecialchars($user['phone_number']) : 'Not provided'; ?>
                    </div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Department</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['department']); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Role</div>
                    <div class="info-value"><?php echo ucfirst($user['role']); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Bio/Description</div>
                    <div class="info-value">
                        <?php echo $user['bio'] ? nl2br(htmlspecialchars($user['bio'])) : 'No bio provided'; ?>
                    </div>
                </div>
                
                <div class="contact-admin-note">
                    📞 <strong>Contact Administrator</strong> to update personal information, change password, or modify account settings.
                </div>
            </div>
            
            <!-- Transaction History -->
            <div class="profile-card">
                <h2>Recent Transactions</h2>
                <p>Your last 10 transactions</p>
                
                <?php if (empty($transactions)): ?>
                    <p>No transactions found.</p>
                <?php else: ?>
                    <div class="transaction-list">
                        <?php foreach ($transactions as $transaction): ?>
                            <div class="transaction-item">
                                <div>
                                    <strong><?php echo htmlspecialchars($transaction['category']); ?></strong>
                                    <br>
                                    <small><?php echo htmlspecialchars($transaction['description']); ?></small>
                                    <br>
                                    <small>Date: <?php echo date('M j, Y', strtotime($transaction['transaction_date'])); ?></small>
                                </div>
                                <div style="text-align: right;">
                                    <div class="transaction-amount <?php echo $transaction['type']; ?>">
                                        <?php echo $transaction['type'] == 'income' ? '+' : '-'; ?>
                                        Ksh <?php echo number_format($transaction['amount'], 2); ?>
                                    </div>
                                    <span class="status-badge status-<?php echo $transaction['status']; ?>">
                                        <?php echo ucfirst($transaction['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="margin-top: 15px; text-align: center;">
                        <a href="transactions.php" class="btn-edit">View All Transactions</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Account Statistics -->
        <div class="profile-card" style="margin-top: 30px;">
            <h2>Account Statistics</h2>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; text-align: center;">
                <div>
                    <h3><?php echo countUserTransactions($user_id); ?></h3>
                    <p>Total Transactions</p>
                </div>
                <div>
                    <h3><?php echo countUserTransactionsByStatus($user_id, 'approved'); ?></h3>
                    <p>Approved</p>
                </div>
                <div>
                    <h3><?php echo countUserTransactionsByStatus($user_id, 'pending'); ?></h3>
                    <p>Pending</p>
                </div>
                <div>
                    <h3><?php echo calculateUserTotalAmount($user_id); ?></h3>
                    <p>Total Amount (Ksh)</p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>