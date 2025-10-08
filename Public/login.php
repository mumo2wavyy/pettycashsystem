<?php
// login.php - Login and Registration page

require_once '../config.php';
require_once '../db.php';
require_once '../functions.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dash.php");
    exit();
}

$db = new Database();
$error = '';
$success = '';
$active_tab = 'login';

// Handle Login
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (!empty($email) && !empty($password)) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $user = $db->getSingle($sql, [$email]);
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_department'] = $user['department'];
            
            header("Location: dash.php");
            exit();
        } else {
            $error = "Invalid email or password";
            $active_tab = 'login';
        }
    } else {
        $error = "Please enter both email and password";
        $active_tab = 'login';
    }
}

// Handle Registration
if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $department = trim($_POST['department']);
    
    $errors = [];
    
    // Validation
    if (empty($name)) $errors[] = "Full name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (empty($password)) $errors[] = "Password is required";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    if (empty($department)) $errors[] = "Department is required";
    
    // Check if email already exists
    if (empty($errors)) {
        $sql = "SELECT id FROM users WHERE email = ?";
        $existing_user = $db->getSingle($sql, [$email]);
        if ($existing_user) {
            $errors[] = "Email already registered";
        }
    }
    
    if (empty($errors)) {
        // Hash password and create user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password, role, department) VALUES (?, ?, ?, 'user', ?)";
        $result = $db->executeQuery($sql, [$name, $email, $hashed_password, $department]);
        
        if ($result) {
            $success = "Registration successful! You can now login.";
            $active_tab = 'login';
        } else {
            $error = "Registration failed. Please try again.";
            $active_tab = 'register';
        }
    } else {
        $error = implode("<br>", $errors);
        $active_tab = 'register';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petty Cash System - Login</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .tab-container {
            max-width: 500px;
            margin: 50px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .tabs {
            display: flex;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        
        .tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .tab.active {
            background: white;
            border-bottom: 3px solid #1a5276;
            color: #1a5276;
        }
        
        .tab-content {
            padding: 2rem;
        }
        
        .tab-pane {
            display: none;
        }
        
        .tab-pane.active {
            display: block;
        }
        
        .demo-accounts {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        
        .demo-accounts h4 {
            margin: 0 0 0.5rem 0;
            color: #1a5276;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="logo">Petty Cash System</div>
                <ul class="nav-links">
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="tab-container">
            <div class="tabs">
                <button class="tab <?php echo $active_tab === 'login' ? 'active' : ''; ?>" data-tab="login">Login</button>
                <button class="tab <?php echo $active_tab === 'register' ? 'active' : ''; ?>" data-tab="register">Register</button>
            </div>
            
            <div class="tab-content">
                <!-- Login Form -->
                <div class="tab-pane <?php echo $active_tab === 'login' ? 'active' : ''; ?>" id="login-tab">
                    <h2 style="text-align: center; margin-bottom: 1.5rem; color: #1a5276;">Login to Your Account</h2>
                    
                    <?php if ($error && $active_tab === 'login'): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="login" value="1">
                        
                        <div class="form-group">
                            <label for="login-email">Email</label>
                            <input type="email" id="login-email" name="email" class="form-control" required 
                                   value="<?php echo isset($_POST['email']) && $active_tab === 'login' ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="login-password">Password</label>
                            <input type="password" id="login-password" name="password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                        </div>
                    </form>
                    
                    <div class="demo-accounts">
                        <h4>Demo Accounts:</h4>
                        <p><strong>Admin:</strong> admin@company.co.ke / password</p>
                        <p><strong>Approver:</strong> approver@company.co.ke / password</p>
                        <p><strong>User:</strong> user@company.co.ke / password</p>
                    </div>
                </div>
                
                <!-- Registration Form -->
                <div class="tab-pane <?php echo $active_tab === 'register' ? 'active' : ''; ?>" id="register-tab">
                    <h2 style="text-align: center; margin-bottom: 1.5rem; color: #1a5276;">Create New Account</h2>
                    
                    <?php if ($error && $active_tab === 'register'): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="register" value="1">
                        
                        <div class="form-group">
                            <label for="register-name">Full Name</label>
                            <input type="text" id="register-name" name="name" class="form-control" required 
                                   value="<?php echo isset($_POST['name']) && $active_tab === 'register' ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="register-email">Email</label>
                            <input type="email" id="register-email" name="email" class="form-control" required 
                                   value="<?php echo isset($_POST['email']) && $active_tab === 'register' ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="register-department">Department</label>
                            <select id="register-department" name="department" class="form-control" required>
                                <option value="">Select Department</option>
                                <option value="Finance" <?php echo (isset($_POST['department']) && $_POST['department'] === 'Finance') ? 'selected' : ''; ?>>Finance</option>
                                <option value="Sales" <?php echo (isset($_POST['department']) && $_POST['department'] === 'Sales') ? 'selected' : ''; ?>>Sales</option>
                                <option value="Marketing" <?php echo (isset($_POST['department']) && $_POST['department'] === 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                                <option value="IT" <?php echo (isset($_POST['department']) && $_POST['department'] === 'IT') ? 'selected' : ''; ?>>IT</option>
                                <option value="HR" <?php echo (isset($_POST['department']) && $_POST['department'] === 'HR') ? 'selected' : ''; ?>>HR</option>
                                <option value="Operations" <?php echo (isset($_POST['department']) && $_POST['department'] === 'Operations') ? 'selected' : ''; ?>>Operations</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="register-password">Password</label>
                            <input type="password" id="register-password" name="password" class="form-control" required 
                                   minlength="6" placeholder="At least 6 characters">
                        </div>
                        
                        <div class="form-group">
                            <label for="register-confirm-password">Confirm Password</label>
                            <input type="password" id="register-confirm-password" name="confirm_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-success" style="width: 100%;">Register Account</button>
                        </div>
                    </form>
                    
                    <div style="text-align: center; margin-top: 1rem; font-size: 0.9rem; color: #666;">
                        <p>Already have an account? <a href="#" onclick="switchTab('login')">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Petty Cash System. All rights reserved. | Nairobi, Kenya</p>
        </div>
    </footer>

    <script>
        function switchTab(tabName) {
            // Update tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
                if (tab.dataset.tab === tabName) {
                    tab.classList.add('active');
                }
            });
            
            // Update content
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
                if (pane.id === tabName + '-tab') {
                    pane.classList.add('active');
                }
            });
        }
        
        // Add click event to tabs
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                switchTab(this.dataset.tab);
            });
        });
        
        // Password confirmation validation
        const password = document.getElementById('register-password');
        const confirmPassword = document.getElementById('register-confirm-password');
        
        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords do not match");
            } else {
                confirmPassword.setCustomValidity("");
            }
        }
        
        if (password && confirmPassword) {
            password.addEventListener('change', validatePassword);
            confirmPassword.addEventListener('keyup', validatePassword);
        }
    </script>
</body>
</html>