<?php
// functions.php - Petty Cash System - With authentication

require_once 'db.php';

// Initialize global $pdo
global $pdo;
$pdo = (new Database())->getPdo();

class PettyCashSystem {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $this->redirect('login.php');
        }
    }
    
    public function requireApprover() {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'approver' && $_SESSION['user_role'] !== 'admin') {
            $this->redirect('dash.php');
        }
    }
    
    public function requireAdmin() {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'admin') {
            $this->redirect('dash.php');
        }
    }
    
    public function redirect($url) {
        header("Location: " . $url);
        exit();
    }
    
    // ... [rest of your existing methods remain the same] ...
    
    public function formatCurrency($amount) {
        return 'KSh ' . number_format($amount, 2);
    }
    
    public function formatDate($date) {
        return date('j M Y', strtotime($date));
    }
    
    public function getTransactionTypeBadge($type) {
        return $type === 'income' ? 
            '<span class="badge badge-income">Income</span>' : 
            '<span class="badge badge-expense">Expense</span>';
    }
    
    public function getStatusBadge($status) {
        switch($status) {
            case 'pending': return '<span class="badge badge-warning">Pending</span>';
            case 'approved': return '<span class="badge badge-success">Approved</span>';
            case 'rejected': return '<span class="badge badge-danger">Rejected</span>';
            default: return '<span class="badge badge-secondary">' . $status . '</span>';
        }
    }
    
    public function createTransaction($data) {
        $sql = "INSERT INTO transactions (user_id, type, category, amount, description, transaction_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $_SESSION['user_id'],
            $data['type'],
            $data['category'],
            $data['amount'],
            $data['description'],
            $data['transaction_date'],
            'pending'
        ];
        
        $result = $this->db->executeQuery($sql, $params);
        return $result ? $this->db->lastInsertId() : false;
    }
    
    public function getCurrentBalance() {
        $sql = "SELECT 
                COALESCE(SUM(CASE WHEN type = 'income' AND status = 'approved' THEN amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN type = 'expense' AND status = 'approved' THEN amount ELSE 0 END), 0) as total_expenses
                FROM transactions";
        
        $result = $this->db->getSingle($sql);
        return $result ? ($result['total_income'] - $result['total_expenses']) : 0;
    }
    
    public function getUserTransactions($limit = null) {
        $sql = "SELECT t.*, u.name as user_name 
                FROM transactions t 
                JOIN users u ON t.user_id = u.id 
                WHERE t.user_id = ? 
                ORDER BY t.transaction_date DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        return $this->db->getMultiple($sql, [$_SESSION['user_id']]);
    }
    
    public function getAllTransactions() {
        $sql = "SELECT t.*, u.name as user_name, u.department 
                FROM transactions t 
                JOIN users u ON t.user_id = u.id 
                ORDER BY t.transaction_date DESC";
        
        return $this->db->getMultiple($sql);
    }
    
    public function getPendingTransactions() {
        $sql = "SELECT t.*, u.name as user_name, u.department 
                FROM transactions t 
                JOIN users u ON t.user_id = u.id 
                WHERE t.status = 'pending' 
                ORDER BY t.transaction_date ASC";
        
        return $this->db->getMultiple($sql);
    }
    
    public function getTransaction($id) {
        $sql = "SELECT t.*, u.name as user_name, u.department 
                FROM transactions t 
                JOIN users u ON t.user_id = u.id 
                WHERE t.id = ?";
        return $this->db->getSingle($sql, [$id]);
    }
    
    public function updateTransactionStatus($transactionId, $status, $notes = '') {
        $sql = "UPDATE transactions 
                SET status = ?, approval_notes = ?, approved_at = NOW(), approved_by = ? 
                WHERE id = ?";
        
        return $this->db->executeQuery($sql, [$status, $notes, $_SESSION['user_id'], $transactionId]);
    }
    
    public function getDashboardStats() {
        $stats = [];
        
        // Current balance
        $stats['current_balance'] = $this->getCurrentBalance();
        
        // Monthly income
        $sql = "SELECT COALESCE(SUM(amount), 0) as total 
                FROM transactions 
                WHERE type = 'income' AND status = 'approved' 
                AND MONTH(transaction_date) = MONTH(CURRENT_DATE())";
        $stats['monthly_income'] = $this->db->getSingle($sql)['total'];
        
        // Monthly expenses
        $sql = "SELECT COALESCE(SUM(amount), 0) as total 
                FROM transactions 
                WHERE type = 'expense' AND status = 'approved' 
                AND MONTH(transaction_date) = MONTH(CURRENT_DATE())";
        $stats['monthly_expenses'] = $this->db->getSingle($sql)['total'];
        
        // Pending approvals
        $sql = "SELECT COUNT(*) as count FROM transactions WHERE status = 'pending'";
        $stats['pending_approvals'] = $this->db->getSingle($sql)['count'];
        
        // User's pending transactions
        $sql = "SELECT COUNT(*) as count FROM transactions WHERE user_id = ? AND status = 'pending'";
        $stats['my_pending'] = $this->db->getSingle($sql, [$_SESSION['user_id']])['count'];
        
        return $stats;
    }
    
    public function getRecentActivity($limit = 10) {
        $sql = "SELECT t.*, u.name as user_name 
                FROM transactions t 
                JOIN users u ON t.user_id = u.id 
                WHERE t.status = 'approved' 
                ORDER BY t.transaction_date DESC 
                LIMIT ?";
        
        return $this->db->getMultiple($sql, [$limit]);
    }
    
    public function getCategoryTotals($type, $period = 'month') {
        $sql = "SELECT category, SUM(amount) as total 
                FROM transactions 
                WHERE type = ? AND status = 'approved'";
        
        $params = [$type];
        
        if ($period === 'month') {
            $sql .= " AND MONTH(transaction_date) = MONTH(CURRENT_DATE()) 
                     AND YEAR(transaction_date) = YEAR(CURRENT_DATE())";
        } elseif ($period === 'year') {
            $sql .= " AND YEAR(transaction_date) = YEAR(CURRENT_DATE())";
        }
        
        $sql .= " GROUP BY category ORDER BY total DESC";
        
        return $this->db->getMultiple($sql, $params);
    }
    
    public function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
    
    public function validateAmount($amount) {
        return is_numeric($amount) && $amount > 0;
    }
    
    public function checkExpenseLimit($amount) {
        return $amount <= MAX_SINGLE_EXPENSE;
    }
    
    public function checkReplenishmentLimit($amount) {
        $currentBalance = $this->getCurrentBalance();
        return ($currentBalance + $amount) <= MAX_PETTY_CASH_AMOUNT;
    }

    public function getUserDetails($userId) {
        $query = "SELECT username, email, department, role, created_at FROM users WHERE id = ?";
        return $this->db->getSingle($query, [$userId]);
    }
    
    public function getPdo() {
        return $this->pdo;
    }
}

// Create instance
$pettyCashSystem = new PettyCashSystem();

function getUserById($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getProfilePicture($filename) {
    if ($filename && file_exists("../uploads/profiles/" . $filename)) {
        return "../uploads/profiles/" . $filename;
    }
    return "./assets/dafault-avatar.jpg"; // Default avatar
}

function handleProfilePictureUpload($user_id) {
    $uploadDir = "../uploads/profiles/";
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $file = $_FILES['profile_picture'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Maximum size is 2MB.'];
    }
    
    // Check file type
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Only JPG, PNG, and GIF files are allowed.'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "profile_" . $user_id . "_" . time() . "." . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Update database
        global $pdo;
        $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        if ($stmt->execute([$filename, $user_id])) {
            return ['success' => true, 'message' => 'Profile picture updated successfully!'];
        }
    }
    
    return ['success' => false, 'message' => 'Failed to upload profile picture.'];
}

function getUserTransactions($user_id, $limit = 10) {
    global $pdo;
    $limit = intval($limit);
    $stmt = $pdo->prepare("
        SELECT * FROM transactions
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT $limit
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countUserTransactions($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

function countUserTransactionsByStatus($user_id, $status) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ? AND status = ?");
    $stmt->execute([$user_id, $status]);
    return $stmt->fetchColumn();
}

function calculateUserTotalAmount($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT SUM(
            CASE 
                WHEN type = 'income' THEN amount 
                WHEN type = 'expense' THEN -amount 
                ELSE 0 
            END
        ) as total 
        FROM transactions 
        WHERE user_id = ? AND status = 'approved'
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return number_format($result['total'] ?? 0, 2);
}
?>