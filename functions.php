<?php
// functions.php - Petty Cash System - With authentication

require_once 'db.php';

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
}

// Create instance
$pettyCashSystem = new PettyCashSystem();
?>