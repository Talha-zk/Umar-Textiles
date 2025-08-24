<?php
session_start();

// Simple authentication (you can enhance this)
$admin_password = "codered"; // Change this to a secure password

// Always require login - destroy session on page load if not from login or delete action
if (!isset($_POST['password']) && !isset($_POST['delete_page']) && !isset($_POST['logout'])) {
    session_destroy();
}

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    // Try to authenticate if password is provided
    if (isset($_POST['password'])) {
        if ($_POST['password'] === $admin_password) {
            $_SESSION['admin_logged_in'] = true;
            // Redirect to clear the POST data and prevent form resubmission
            header('Location: admin_delete_pages.php');
            exit;
        } else {
            $login_error = "Invalid password";
        }
    } else {
        // Show login form if not authenticated
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Access - Umar Textiles</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    font-family: 'Arial', sans-serif;
                }
                .admin-container {
                    max-width: 400px;
                    margin: 100px auto;
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
                .btn-primary {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border: none;
                    padding: 12px 30px;
                    border-radius: 25px;
                    font-weight: 600;
                }
            </style>
        </head>
        <body>
            <div class="admin-container">
                <div class="admin-header">
                    <h1 class="mb-0">Admin Access</h1>
                    <p class="mb-0">Page Management</p>
                </div>
                <div class="admin-body">
                    <h3 class="text-center mb-4">Authentication Required</h3>
                    
                    <?php if (isset($login_error)): ?>
                        <div class="alert alert-danger"><?php echo $login_error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="password" class="form-label">Admin Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Access Panel</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="admin.php" class="btn btn-outline-secondary btn-sm">Back to Main Admin</a>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: admin_delete_pages.php');
    exit;
}

// Handle page deletion
if (isset($_POST['delete_page']) && isset($_SESSION['admin_logged_in'])) {
    $page_to_delete = $_POST['page_to_delete'];
    
    // Security: Only allow deletion of HTML files in the root directory
    $allowed_extensions = ['html', 'htm'];
    $file_extension = pathinfo($page_to_delete, PATHINFO_EXTENSION);
    
    if (in_array(strtolower($file_extension), $allowed_extensions) && file_exists($page_to_delete)) {
        if (unlink($page_to_delete)) {
            $success_message = "Page '$page_to_delete' has been successfully deleted.";
        } else {
            $error_message = "Failed to delete '$page_to_delete'. Please check file permissions.";
        }
    } else {
        $error_message = "Invalid file or file does not exist.";
    }
}

// Get list of HTML pages
function getHtmlPages() {
    $pages = [];
    $files = glob('*.html');
    foreach ($files as $file) {
        // Skip admin files and test files, but include all other HTML files
        if (strpos($file, 'admin') === false && strpos($file, 'test') === false) {
            $pages[] = $file;
        }
    }
    
    // Also check for .htm files
    $htm_files = glob('*.htm');
    foreach ($htm_files as $file) {
        if (strpos($file, 'admin') === false && strpos($file, 'test') === false) {
            $pages[] = $file;
        }
    }
    
    // Sort pages alphabetically for better organization
    sort($pages);
    
    return $pages;
}

$html_pages = getHtmlPages();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Management - Umar Textiles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/img/favicon_tiny.ico" />
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }
        .admin-container {
            max-width: 800px;
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
        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
        }
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }
        .page-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #e74c3c;
        }
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="mb-0">Umar Textiles</h1>
            <p class="mb-0">Page Management Panel</p>
        </div>
        
        <div class="admin-body">
            <div class="position-relative">
                <form method="POST" class="logout-btn">
                    <button type="submit" name="logout" class="btn btn-outline-secondary btn-sm">Logout</button>
                </form>
            </div>
            
            <div class="warning-box">
                <h5 class="text-warning">⚠️ Warning</h5>
                <p class="mb-0">
                    <strong>This panel allows you to permanently delete website pages.</strong><br>
                    Deleted pages cannot be recovered. Please be absolutely sure before proceeding.
                </p>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Available Pages (<?php echo count($html_pages); ?>)</h3>
                <button onclick="location.reload()" class="btn btn-outline-primary btn-sm">
                    🔄 Refresh Pages
                </button>
            </div>
            
            <?php if (empty($html_pages)): ?>
                <div class="alert alert-info">
                    No HTML pages found in the root directory.
                </div>
            <?php else: ?>
                <div class="mb-4">
                    <p class="text-muted">
                        Click the delete button next to any page you want to remove from the website.<br>
                        <small>Pages are automatically detected when new files are added to the root directory.</small>
                    </p>
                </div>
                
                <?php foreach ($html_pages as $page): ?>
                    <div class="page-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo htmlspecialchars($page); ?></strong>
                            <br>
                            <small class="text-muted">
                                File size: <?php echo number_format(filesize($page) / 1024, 2); ?> KB | 
                                Last modified: <?php echo date('M j, Y g:i A', filemtime($page)); ?>
                            </small>
                        </div>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($page); ?>? This action cannot be undone.');">
                            <input type="hidden" name="page_to_delete" value="<?php echo htmlspecialchars($page); ?>">
                            <button type="submit" name="delete_page" class="btn btn-danger btn-sm">
                                Delete Page
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <hr class="my-4">
            
            <div class="text-center">
                <div class="d-flex justify-content-center gap-2">
                    <a href="admin.php" class="btn btn-primary">Main Admin Panel</a>
                    <a href="admin_products.php" class="btn btn-outline-primary">Manage Products</a>
                    <a href="index.html" class="btn btn-outline-secondary">Back to Website</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh page list every 30 seconds to detect new pages
        let refreshCountdown = 30;
        const countdownElement = document.createElement('small');
        countdownElement.className = 'text-muted ms-2';
        document.querySelector('.d-flex.justify-content-between').appendChild(countdownElement);
        
        function updateCountdown() {
            countdownElement.textContent = `Auto-refresh in ${refreshCountdown}s`;
            refreshCountdown--;
            
            if (refreshCountdown < 0) {
                location.reload();
            }
        }
        
        // Update countdown every second
        setInterval(updateCountdown, 1000);
        updateCountdown(); // Initial call
    </script>
</body>
</html>
