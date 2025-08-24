<?php
session_start();

// Simple authentication (you can enhance this)
$admin_password = "admin123"; // Change this to a secure password

if (isset($_POST['login'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = "Invalid password";
    }
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Handle email update
if (isset($_POST['update_email']) && isset($_SESSION['admin_logged_in'])) {
    $new_email = filter_var($_POST['recipient_email'], FILTER_VALIDATE_EMAIL);
    if ($new_email) {
        $config = [
            'recipient_email' => $new_email,
            'site_name' => 'Umar Textiles'
        ];
        
        $configFile = 'config/email_config.json';
        $result = file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
        
        if ($result !== false) {
            $success_message = "Email address updated successfully!";
        } else {
            $error_message = "Failed to update email address. Please check file permissions.";
        }
    } else {
        $error_message = "Please enter a valid email address.";
    }
}

// Get current configuration
function getConfig() {
    $configFile = 'config/email_config.json';
    if (!file_exists($configFile)) {
        return ['recipient_email' => 'info@umartextiles.com', 'site_name' => 'Umar Textiles'];
    }
    $config = json_decode(file_get_contents($configFile), true);
    return $config ?: ['recipient_email' => 'info@umartextiles.com', 'site_name' => 'Umar Textiles'];
}

$config = getConfig();
$current_email = $config['recipient_email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Umar Textiles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/img/favicon_tiny.ico" />
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }
        .admin-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .admin-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .admin-body {
            padding: 40px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .current-email {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="mb-0">Umar Textiles</h1>
            <p class="mb-0">Admin Panel</p>
        </div>
        
        <div class="admin-body">
            <?php if (!isset($_SESSION['admin_logged_in'])): ?>
                <!-- Login Form -->
                <h3 class="text-center mb-4">Admin Login</h3>
                
                <?php if (isset($login_error)): ?>
                    <div class="alert alert-danger"><?php echo $login_error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="login" class="btn btn-primary">Login</button>
                    </div>
                </form>
                
            <?php else: ?>
                <!-- Admin Dashboard -->
                <div class="position-relative">
                    <form method="POST" class="logout-btn">
                        <button type="submit" name="logout" class="btn btn-outline-secondary btn-sm">Logout</button>
                    </form>
                </div>
                
                <h3 class="text-center mb-4">Email Configuration</h3>
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <div class="current-email mb-4">
                    <strong>Current Recipient Email:</strong><br>
                    <span class="text-primary"><?php echo htmlspecialchars($current_email); ?></span>
                </div>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="recipient_email" class="form-label">New Recipient Email</label>
                        <input type="email" class="form-control" id="recipient_email" name="recipient_email" 
                               value="<?php echo htmlspecialchars($current_email); ?>" required>
                        <div class="form-text">All form submissions will be sent to this email address.</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="update_email" class="btn btn-primary">Update Email</button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <h5>Form Information</h5>
                    <p class="text-muted">
                        This admin panel allows you to change the email address where all form submissions are sent.<br>
                        Changes take effect immediately.
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="admin_products.php" class="btn btn-primary">Manage Products</a>
                        <a href="index.html" class="btn btn-outline-primary">Back to Website</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
