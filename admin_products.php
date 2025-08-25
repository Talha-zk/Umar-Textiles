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
    header('Location: admin_products.php');
    exit;
}

// Product management functions
function getProducts() {
    $productsFile = 'config/products.json';
    if (!file_exists($productsFile)) {
        return [];
    }
    $products = json_decode(file_get_contents($productsFile), true);
    return $products ?: [];
}

function saveProducts($products) {
    $productsFile = 'config/products.json';
    return file_put_contents($productsFile, json_encode($products, JSON_PRETTY_PRINT));
}

// Category management functions
function getCategories() {
    $categoriesFile = 'config/categories.json';
    if (!file_exists($categoriesFile)) {
        return [];
    }
    $categories = json_decode(file_get_contents($categoriesFile), true);
    return $categories ?: [];
}

function saveCategories($categories) {
    $categoriesFile = 'config/categories.json';
    return file_put_contents($categoriesFile, json_encode($categories, JSON_PRETTY_PRINT));
}

function getCategoryByKey($key) {
    $categories = getCategories();
    foreach ($categories as $category) {
        if ($category['key'] === $key) {
            return $category;
        }
    }
    return null;
}

function getCategoryDisplayName($key) {
    $category = getCategoryByKey($key);
    return $category ? $category['display_name'] : ucfirst($key);
}

// Handle image upload
function handleImageUpload($file) {
    $uploadDir = 'assets/img/products/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create upload directory: " . $uploadDir);
            return false;
        }
    }
    
    // Check if directory is writable
    if (!is_writable($uploadDir)) {
        error_log("Upload directory is not writable: " . $uploadDir);
        return false;
    }
    
    // Check if file was uploaded
    if ($file['error'] !== UPLOAD_ERR_OK) {
        error_log("File upload error: " . $file['error']);
        return false;
    }
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        error_log("Invalid file type: " . $file['type']);
        return false;
    }
    
    // Validate file size (5MB limit)
    if ($file['size'] > 5 * 1024 * 1024) {
        error_log("File too large: " . $file['size']);
        return false;
    }
    
    // Generate unique filename with original name preserved
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
    $filename = $safeName . '_' . uniqid() . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        error_log("File uploaded successfully: " . $filepath);
        return $filepath;
    } else {
        error_log("Failed to move uploaded file to: " . $filepath);
        return false;
    }
}

// Handle product operations
if (isset($_SESSION['admin_logged_in'])) {
    // Add new product
    if (isset($_POST['add_product'])) {
        $products = getProducts();
        $newProduct = [
            'id' => (string)(count($products) + 1),
            'name' => trim($_POST['product_name'] ?? ''),
            'category' => trim($_POST['product_category'] ?? ''),
            'colors' => array_filter(array_map('trim', explode(',', $_POST['product_color'] ?? ''))),
            'image' => 'assets/img/cloth.png' // Default image
        ];
        
        // Handle image upload
        $imageUploadSuccess = true;
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            error_log("Processing image upload for product: " . $newProduct['name']);
            $uploadedImage = handleImageUpload($_FILES['product_image']);
            if ($uploadedImage) {
                $newProduct['image'] = $uploadedImage;
                error_log("Image uploaded successfully: " . $uploadedImage);
            } else {
                $imageUploadSuccess = false;
                $_SESSION['error_message'] = "Failed to upload image. Please check file type and size.";
                error_log("Image upload failed for product: " . $newProduct['name']);
            }
        } else {
            // If no image uploaded, use default image
            $newProduct['image'] = 'assets/img/cloth.png';
        }
        
        if (!empty($newProduct['name']) && !empty($newProduct['category']) && !empty($newProduct['colors']) && $imageUploadSuccess) {
            $products[] = $newProduct;
            if (saveProducts($products)) {
                $_SESSION['success_message'] = "Product added successfully!";
                header('Location: admin_products.php');
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to save product. Please check file permissions.";
                header('Location: admin_products.php');
                exit;
            }
        } elseif (!$imageUploadSuccess) {
            // Don't redirect if image upload failed, let the error message show
        } else {
            $_SESSION['error_message'] = "Please fill in all required fields.";
            header('Location: admin_products.php');
            exit;
        }
    }
    
    // Delete product
    if (isset($_POST['delete_product'])) {
        $products = getProducts();
        $productId = $_POST['product_id'] ?? '';
        
        foreach ($products as $key => $product) {
            if ($product['id'] === $productId) {
                // Delete associated image file if it's not the default
                if ($product['image'] !== 'assets/img/cloth.png' && file_exists($product['image'])) {
                    unlink($product['image']);
                }
                unset($products[$key]);
                break;
            }
        }
        
        $products = array_values($products); // Re-index array
        
        if (saveProducts($products)) {
            $_SESSION['success_message'] = "Product deleted successfully!";
            header('Location: admin_products.php');
            exit;
        } else {
            $_SESSION['error_message'] = "Failed to delete product. Please check file permissions.";
            header('Location: admin_products.php');
            exit;
        }
    }
    
    // Update product
    if (isset($_POST['update_product'])) {
        $products = getProducts();
        $productId = $_POST['product_id'] ?? '';
        
        foreach ($products as $key => $product) {
            if ($product['id'] === $productId) {
                $products[$key]['name'] = trim($_POST['product_name'] ?? '');
                $products[$key]['category'] = trim($_POST['product_category'] ?? '');
                $products[$key]['colors'] = array_filter(array_map('trim', explode(',', $_POST['product_color'] ?? '')));
                
                // Handle image upload for update
                $imageUploadSuccess = true;
                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    error_log("Processing image upload for product update: " . $product['name']);
                    $uploadedImage = handleImageUpload($_FILES['product_image']);
                    if ($uploadedImage) {
                        // Delete old image if it's not the default
                        if ($product['image'] !== 'assets/img/cloth.png' && file_exists($product['image'])) {
                            unlink($product['image']);
                        }
                        $products[$key]['image'] = $uploadedImage;
                        error_log("Image updated successfully: " . $uploadedImage);
                    } else {
                        $imageUploadSuccess = false;
                        $_SESSION['error_message'] = "Failed to upload image. Please check file type and size.";
                        error_log("Image upload failed for product update: " . $product['name']);
                    }
                } else {
                    // Keep existing image if no new image uploaded
                    $products[$key]['image'] = $product['image'];
                }
                break;
            }
        }
        
        if ($imageUploadSuccess && saveProducts($products)) {
            $_SESSION['success_message'] = "Product updated successfully!";
            header('Location: admin_products.php');
            exit;
        } elseif (!$imageUploadSuccess) {
            // Don't redirect if image upload failed, let the error message show
        } else {
            $_SESSION['error_message'] = "Failed to update product. Please check file permissions.";
            header('Location: admin_products.php');
            exit;
        }
    }
    
    // Add new category
    if (isset($_POST['add_category'])) {
        $categories = getCategories();
        $newCategory = [
            'id' => (string)(count($categories) + 1),
            'key' => trim($_POST['category_key'] ?? ''),
            'name' => trim($_POST['category_name'] ?? ''),
            'description' => trim($_POST['category_description'] ?? ''),
            'display_name' => trim($_POST['category_display_name'] ?? ''),
            'created_at' => date('Y-m-d')
        ];
        
        // Check if category key already exists
        $existingCategory = getCategoryByKey($newCategory['key']);
        if ($existingCategory) {
            $_SESSION['error_message'] = "Category key already exists. Please choose a different key.";
            header('Location: admin_products.php');
            exit;
        } elseif (!empty($newCategory['key']) && !empty($newCategory['name'])) {
            $categories[] = $newCategory;
            if (saveCategories($categories)) {
                $_SESSION['success_message'] = "Category '$newCategory[name]' added successfully!";
                header('Location: admin_products.php');
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to save category. Please check file permissions.";
                header('Location: admin_products.php');
                exit;
            }
        } else {
            $_SESSION['error_message'] = "Please fill in all required fields.";
            header('Location: admin_products.php');
            exit;
        }
    }
    
    // Update category
    if (isset($_POST['update_category'])) {
        $categories = getCategories();
        $categoryId = $_POST['category_id'] ?? '';
        
        foreach ($categories as $key => $category) {
            if ($category['id'] === $categoryId) {
                $categories[$key]['key'] = trim($_POST['category_key'] ?? '');
                $categories[$key]['name'] = trim($_POST['category_name'] ?? '');
                $categories[$key]['description'] = trim($_POST['category_description'] ?? '');
                $categories[$key]['display_name'] = trim($_POST['category_display_name'] ?? '');
                break;
            }
        }
        
        if (saveCategories($categories)) {
            $_SESSION['success_message'] = "Category updated successfully!";
            header('Location: admin_products.php');
            exit;
        } else {
            $_SESSION['error_message'] = "Failed to update category. Please check file permissions.";
            header('Location: admin_products.php');
            exit;
        }
    }
    
    // Delete category
    if (isset($_POST['delete_category'])) {
        $categories = getCategories();
        $products = getProducts();
        $categoryId = $_POST['category_id'] ?? '';
        $categoryKey = '';
        
        // Get category key before deletion
        foreach ($categories as $category) {
            if ($category['id'] === $categoryId) {
                $categoryKey = $category['key'];
                break;
            }
        }
        
        // Check if category is used by any products
        $productsUsingCategory = array_filter($products, function($product) use ($categoryKey) {
            return $product['category'] === $categoryKey;
        });
        
        if (!empty($productsUsingCategory)) {
            $_SESSION['error_message'] = "Cannot delete category. It is used by " . count($productsUsingCategory) . " product(s). Please reassign or delete those products first.";
            header('Location: admin_products.php');
            exit;
        } else {
            // Remove category
            foreach ($categories as $key => $category) {
                if ($category['id'] === $categoryId) {
                    unset($categories[$key]);
                    break;
                }
            }
            
            $categories = array_values($categories); // Re-index array
            
            if (saveCategories($categories)) {
                $_SESSION['success_message'] = "Category deleted successfully!";
                header('Location: admin_products.php');
                exit;
            } else {
                $_SESSION['error_message'] = "Failed to delete category. Please check file permissions.";
                header('Location: admin_products.php');
                exit;
            }
        }
    }
}

$products = getProducts();
$categories = getCategories();

// Get session messages and clear them
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;

// Clear session messages
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Umar Textiles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/img/favicon_tiny.ico" />
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }
        .admin-container {
            max-width: 1200px;
            margin: 30px auto;
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
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .product-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .product-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 25px;
        }
        .btn-sm {
            padding: 5px 15px;
            border-radius: 15px;
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 10px;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        .upload-area:hover {
            border-color: #667eea;
            background: #f0f2ff;
        }
        .upload-area.dragover {
            border-color: #667eea;
            background: #e8ecff;
        }
        .category-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .category-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        /* Color Swatches */
        .color-swatches {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        
        .color-swatch {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid #ddd;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .color-swatch:hover {
            transform: scale(1.1);
            border-color: #667eea;
        }
        
        .color-swatch.selected {
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.3);
        }
        
        .color-swatch::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: bold;
            font-size: 12px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .color-swatch.selected::after {
            opacity: 1;
        }
        
        .custom-color-input {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        
        .custom-color-input input[type="color"] {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
        }
        
        .custom-color-input input[type="text"] {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1 class="mb-0">Umar Textiles</h1>
            <p class="mb-0">Product Management</p>
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
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button" role="tab">
                            Manage Products
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#add" type="button" role="tab">
                            Add New Product
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="categories-tab" data-bs-toggle="tab" data-bs-target="#categories" type="button" role="tab">
                            Manage Categories
                        </button>
                    </li>
                </ul>
                
                <!-- Tab Content -->
                <div class="tab-content" id="adminTabsContent">
                    <!-- Products List Tab -->
                    <div class="tab-pane fade show active" id="products" role="tabpanel">
                        <h3 class="mb-4">Product List</h3>
                        
                        <?php if (empty($products)): ?>
                            <div class="text-center py-5">
                                <h5 class="text-muted">No products found</h5>
                                <p class="text-muted">Add your first product using the "Add New Product" tab.</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($products as $product): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="product-card">
                                            <div class="d-flex align-items-start">
                                                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                     class="product-image me-3"
                                                     onerror="this.src='assets/img/cloth.png'">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($product['name']); ?></h6>
                                                    <p class="mb-1"><strong>Category:</strong> <?php echo htmlspecialchars(getCategoryDisplayName($product['category'])); ?></p>
                                                    <p class="mb-2"><strong>Colors:</strong> <?php echo htmlspecialchars(is_array($product['colors']) ? implode(', ', $product['colors']) : $product['color']); ?></p>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-primary" 
                                                                onclick="editProduct('<?php echo $product['id']; ?>', '<?php echo htmlspecialchars(addslashes($product['name'])); ?>', '<?php echo htmlspecialchars($product['category']); ?>', <?php echo json_encode($product['colors']); ?>, '<?php echo htmlspecialchars($product['image']); ?>')">
                                                            Edit
                                                        </button>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                            <button type="submit" name="delete_product" class="btn btn-outline-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Add Product Tab -->
                    <div class="tab-pane fade" id="add" role="tabpanel">
                        <h3 class="mb-4">Add New Product</h3>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="product_name" class="form-label">Product Name *</label>
                                        <input type="text" class="form-control" id="product_name" name="product_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="product_category" class="form-label">Category *</label>
                                        <select class="form-control" id="product_category" name="product_category" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo htmlspecialchars($category['key']); ?>"><?php echo htmlspecialchars($category['display_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="product_color" class="form-label">Colors *</label>
                                        <input type="text" class="form-control" id="product_color" name="product_color" 
                                               placeholder="e.g., White, Blue, Red (separate multiple colors with commas)" required>
                                        <div class="form-text">Enter multiple colors separated by commas</div>
                                        
                                        <!-- Color Swatches -->
                                        <div class="color-swatches" id="colorSwatches">
                                            <div class="color-swatch" data-color="White" style="background-color: #ffffff;"></div>
                                            <div class="color-swatch" data-color="Black" style="background-color: #000000;"></div>
                                            <div class="color-swatch" data-color="Blue" style="background-color: #0066cc;"></div>
                                            <div class="color-swatch" data-color="Red" style="background-color: #cc0000;"></div>
                                            <div class="color-swatch" data-color="Green" style="background-color: #00cc00;"></div>
                                            <div class="color-swatch" data-color="Yellow" style="background-color: #ffff00;"></div>
                                            <div class="color-swatch" data-color="Gray" style="background-color: #808080;"></div>
                                            <div class="color-swatch" data-color="Beige" style="background-color: #f5f5dc;"></div>
                                            <div class="color-swatch" data-color="Pink" style="background-color: #ffc0cb;"></div>
                                            <div class="color-swatch" data-color="Purple" style="background-color: #800080;"></div>
                                            <div class="color-swatch" data-color="Orange" style="background-color: #ffa500;"></div>
                                            <div class="color-swatch" data-color="Brown" style="background-color: #a52a2a;"></div>
                                        </div>
                                        
                                        <!-- Custom Color Input -->
                                        <div class="custom-color-input">
                                            <input type="color" id="customColorPicker" value="#ffffff">
                                            <input type="text" id="customColorName" placeholder="Custom color name">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addCustomColor()">Add Custom</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="product_image" class="form-label">Product Image</label>
                                        <div class="upload-area" id="uploadArea">
                                            <input type="file" class="form-control" id="product_image" name="product_image" 
                                                   accept="image/*" style="display: none;">
                                            <div class="upload-content">
                                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                <p class="mb-2">Click to upload or drag and drop</p>
                                                <p class="text-muted small">PNG, JPG, GIF up to 5MB</p>
                                            </div>
                                        </div>
                                        <div id="imagePreview" style="display: none;">
                                            <img id="previewImg" class="image-preview" alt="Preview">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Categories Tab -->
                    <div class="tab-pane fade" id="categories" role="tabpanel">
                        <h3 class="mb-4">Manage Categories</h3>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Add New Category</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label for="category_key" class="form-label">Category Key *</label>
                                                <input type="text" class="form-control" id="category_key" name="category_key" 
                                                       placeholder="e.g., bedding, kitchen (lowercase, no spaces)" required>
                                                <div class="form-text">This is the internal identifier for the category</div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="category_name" class="form-label">Category Name *</label>
                                                <input type="text" class="form-control" id="category_name" name="category_name" 
                                                       placeholder="e.g., Bedding, Kitchen, etc." required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="category_display_name" class="form-label">Display Name *</label>
                                                <input type="text" class="form-control" id="category_display_name" name="category_display_name" 
                                                       placeholder="e.g., Bedding, Kitchen, etc." required>
                                                <div class="form-text">This is what users will see on the frontend</div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="category_description" class="form-label">Description</label>
                                                <textarea class="form-control" id="category_description" name="category_description" 
                                                          rows="3" placeholder="Brief description of this category"></textarea>
                                            </div>
                                            <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Existing Categories</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($categories)): ?>
                                            <p class="text-muted">No categories found. Add your first category.</p>
                                        <?php else: ?>
                                            <div class="row">
                                                <?php foreach ($categories as $category): ?>
                                                    <div class="col-12">
                                                        <div class="category-card">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div class="flex-grow-1">
                                                                    <h6 class="mb-1"><?php echo htmlspecialchars($category['display_name']); ?></h6>
                                                                    <p class="mb-1"><strong>Key:</strong> <?php echo htmlspecialchars($category['key']); ?></p>
                                                                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($category['name']); ?></p>
                                                                    <?php if (!empty($category['description'])): ?>
                                                                        <p class="mb-2"><strong>Description:</strong> <?php echo htmlspecialchars($category['description']); ?></p>
                                                                    <?php endif; ?>
                                                                    <small class="text-muted">Used by <?php echo count(array_filter($products, function($p) use ($category) { return $p['category'] === $category['key']; })); ?> products</small>
                                                                </div>
                                                                <div class="btn-group btn-group-sm">
                                                                    <button type="button" class="btn btn-outline-primary" 
                                                                            onclick="editCategory('<?php echo $category['id']; ?>', '<?php echo htmlspecialchars($category['key']); ?>', '<?php echo htmlspecialchars($category['name']); ?>', '<?php echo htmlspecialchars($category['display_name']); ?>', '<?php echo htmlspecialchars($category['description']); ?>')">
                                                                        Edit
                                                                    </button>
                                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                                                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                                                        <button type="submit" name="delete_category" class="btn btn-outline-danger">Delete</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Edit Product Modal -->
                <div class="modal fade" id="editProductModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <input type="hidden" id="edit_product_id" name="product_id">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="edit_product_name" class="form-label">Product Name *</label>
                                                <input type="text" class="form-control" id="edit_product_name" name="product_name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="edit_product_category" class="form-label">Category *</label>
                                                <select class="form-control" id="edit_product_category" name="product_category" required>
                                                    <?php foreach ($categories as $category): ?>
                                                        <option value="<?php echo htmlspecialchars($category['key']); ?>"><?php echo htmlspecialchars($category['display_name']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="edit_product_color" class="form-label">Colors *</label>
                                                <input type="text" class="form-control" id="edit_product_color" name="product_color" 
                                                       placeholder="e.g., White, Blue, Red (separate multiple colors with commas)" required>
                                                <div class="form-text">Enter multiple colors separated by commas</div>
                                                
                                                <!-- Color Swatches for Edit -->
                                                <div class="color-swatches" id="editColorSwatches">
                                                    <div class="color-swatch" data-color="White" style="background-color: #ffffff;"></div>
                                                    <div class="color-swatch" data-color="Black" style="background-color: #000000;"></div>
                                                    <div class="color-swatch" data-color="Blue" style="background-color: #0066cc;"></div>
                                                    <div class="color-swatch" data-color="Red" style="background-color: #cc0000;"></div>
                                                    <div class="color-swatch" data-color="Green" style="background-color: #00cc00;"></div>
                                                    <div class="color-swatch" data-color="Yellow" style="background-color: #ffff00;"></div>
                                                    <div class="color-swatch" data-color="Gray" style="background-color: #808080;"></div>
                                                    <div class="color-swatch" data-color="Beige" style="background-color: #f5f5dc;"></div>
                                                    <div class="color-swatch" data-color="Pink" style="background-color: #ffc0cb;"></div>
                                                    <div class="color-swatch" data-color="Purple" style="background-color: #800080;"></div>
                                                    <div class="color-swatch" data-color="Orange" style="background-color: #ffa500;"></div>
                                                    <div class="color-swatch" data-color="Brown" style="background-color: #a52a2a;"></div>
                                                </div>
                                                
                                                <!-- Custom Color Input for Edit -->
                                                <div class="custom-color-input">
                                                    <input type="color" id="editCustomColorPicker" value="#ffffff">
                                                    <input type="text" id="editCustomColorName" placeholder="Custom color name">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addEditCustomColor()">Add Custom</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="edit_product_image" class="form-label">Product Image</label>
                                                <div class="upload-area" id="editUploadArea">
                                                    <input type="file" class="form-control" id="edit_product_image" name="product_image" 
                                                           accept="image/*" style="display: none;">
                                                    <div class="upload-content">
                                                        <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                                        <p class="mb-2">Click to upload or drag and drop</p>
                                                        <p class="text-muted small">PNG, JPG, GIF up to 5MB</p>
                                                    </div>
                                                </div>
                                                <div id="editImagePreview" style="display: none;">
                                                    <img id="editPreviewImg" class="image-preview" alt="Preview">
                                                </div>
                                                <div id="currentImage" class="mt-2">
                                                    <small class="text-muted">Current image:</small><br>
                                                    <img id="currentImagePreview" class="image-preview" alt="Current">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Edit Category Modal -->
                <div class="modal fade" id="editCategoryModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Category</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST">
                                <div class="modal-body">
                                    <input type="hidden" id="edit_category_id" name="category_id">
                                    <div class="mb-3">
                                        <label for="edit_category_key" class="form-label">Category Key *</label>
                                        <input type="text" class="form-control" id="edit_category_key" name="category_key" required>
                                        <div class="form-text">This is the internal identifier for the category</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_category_name" class="form-label">Category Name *</label>
                                        <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_category_display_name" class="form-label">Display Name *</label>
                                        <input type="text" class="form-control" id="edit_category_display_name" name="category_display_name" required>
                                        <div class="form-text">This is what users will see on the frontend</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_category_description" class="form-label">Description</label>
                                        <textarea class="form-control" id="edit_category_description" name="category_description" 
                                                  rows="3" placeholder="Brief description of this category"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="update_category" class="btn btn-primary">Update Category</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <a href="admin.php" class="btn btn-outline-primary me-2">Email Settings</a>
                    <a href="product.html" class="btn btn-outline-primary me-2">View Products Page</a>
                    <a href="index.html" class="btn btn-outline-primary">Back to Website</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
    <script>
        // File upload handling
        function setupFileUpload(uploadAreaId, fileInputId, previewId, previewImgId) {
            const uploadArea = document.getElementById(uploadAreaId);
            const fileInput = document.getElementById(fileInputId);
            const preview = document.getElementById(previewId);
            const previewImg = document.getElementById(previewImgId);
            
            uploadArea.addEventListener('click', () => fileInput.click());
            
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });
            
            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });
            
            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    handleFileSelect(fileInput, preview, previewImg);
                }
            });
            
            fileInput.addEventListener('change', () => {
                handleFileSelect(fileInput, preview, previewImg);
            });
        }
        
        function handleFileSelect(fileInput, preview, previewImg) {
            const file = fileInput.files[0];
            if (file) {
                console.log('File selected for upload:', file.name, 'Size:', file.size, 'Type:', file.type);
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, PNG, or GIF)');
                    fileInput.value = '';
                    return;
                }
                
                // Validate file size (5MB limit)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    fileInput.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                    console.log('File preview loaded successfully');
                };
                reader.onerror = function() {
                    console.error('Error reading file for preview');
                    alert('Error reading file. Please try again.');
                    fileInput.value = '';
                };
                reader.readAsDataURL(file);
            } else {
                console.log('No file selected');
            }
        }
        
        // Setup file uploads
        document.addEventListener('DOMContentLoaded', function() {
            setupFileUpload('uploadArea', 'product_image', 'imagePreview', 'previewImg');
            setupFileUpload('editUploadArea', 'edit_product_image', 'editImagePreview', 'editPreviewImg');
        });
        
        function editProduct(id, name, category, colors, image) {
            try {
                console.log('editProduct called with:', { id, name, category, colors, image });
                
                // Set form values
                const editId = document.getElementById('edit_product_id');
                const editName = document.getElementById('edit_product_name');
                const editCategory = document.getElementById('edit_product_category');
                const editColor = document.getElementById('edit_product_color');
                
                if (!editId || !editName || !editCategory || !editColor) {
                    console.error('Required form elements not found');
                    alert('Error: Form elements not found. Please refresh the page.');
                    return;
                }
                
                editId.value = id;
                editName.value = name;
                editCategory.value = category;
                
                // Handle colors - ensure it's always an array
                let colorArray = [];
                if (Array.isArray(colors)) {
                    colorArray = colors;
                } else if (typeof colors === 'string') {
                    colorArray = colors.split(',').map(c => c.trim()).filter(c => c);
                } else if (colors) {
                    colorArray = [colors.toString()];
                }
                
                const colorValue = colorArray.join(', ');
                editColor.value = colorValue;
                
                // Show current image
                const currentImagePreview = document.getElementById('currentImagePreview');
                if (currentImagePreview) {
                    currentImagePreview.src = image || 'assets/img/cloth.png';
                    currentImagePreview.onerror = function() {
                        this.src = 'assets/img/cloth.png';
                    };
                }
                
                // Select the appropriate color swatches for multiple colors
                const editColorSwatches = document.querySelectorAll('#editColorSwatches .color-swatch');
                editColorSwatches.forEach(s => s.classList.remove('selected'));
                
                // Handle multiple colors
                colorArray.forEach(color => {
                    const matchingSwatch = Array.from(editColorSwatches).find(swatch => 
                        swatch.getAttribute('data-color').toLowerCase() === color.toLowerCase()
                    );
                    
                    if (matchingSwatch) {
                        matchingSwatch.classList.add('selected');
                    }
                });
                
                // Show the modal
                const modalElement = document.getElementById('editProductModal');
                if (!modalElement) {
                    console.error('Edit modal not found');
                    alert('Error: Edit modal not found. Please refresh the page.');
                    return;
                }
                
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
                console.log('Edit modal opened successfully');
                
            } catch (error) {
                console.error('Error in editProduct function:', error);
                alert('Error opening edit form. Please try again. Error: ' + error.message);
            }
        }
        
        function editCategory(id, key, name, displayName, description) {
            document.getElementById('edit_category_id').value = id;
            document.getElementById('edit_category_key').value = key;
            document.getElementById('edit_category_name').value = name;
            document.getElementById('edit_category_display_name').value = displayName;
            document.getElementById('edit_category_description').value = description;
            
            new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
        }
        
        // Color swatch functionality
        function setupColorSwatches() {
            // Setup color swatches for add product form
            const colorSwatches = document.querySelectorAll('#colorSwatches .color-swatch');
            const colorInput = document.getElementById('product_color');
            
            colorSwatches.forEach(swatch => {
                swatch.addEventListener('click', function() {
                    // Toggle selected class for clicked swatch
                    this.classList.toggle('selected');
                    // Update the color input value with all selected colors
                    updateColorInput(colorSwatches, colorInput);
                });
            });
            
            // Setup color swatches for edit product form
            const editColorSwatches = document.querySelectorAll('#editColorSwatches .color-swatch');
            const editColorInput = document.getElementById('edit_product_color');
            
            editColorSwatches.forEach(swatch => {
                swatch.addEventListener('click', function() {
                    // Toggle selected class for clicked swatch
                    this.classList.toggle('selected');
                    // Update the color input value with all selected colors
                    updateColorInput(editColorSwatches, editColorInput);
                });
            });
        }
        
        // Function to update color input with selected colors
        function updateColorInput(swatches, input) {
            const selectedColors = [];
            swatches.forEach(swatch => {
                if (swatch.classList.contains('selected')) {
                    selectedColors.push(swatch.getAttribute('data-color'));
                }
            });
            input.value = selectedColors.join(', ');
        }
        
        // Add custom color functionality
        function addCustomColor() {
            const colorPicker = document.getElementById('customColorPicker');
            const colorName = document.getElementById('customColorName');
            const colorInput = document.getElementById('product_color');
            const colorSwatches = document.getElementById('colorSwatches');
            
            if (colorName.value.trim()) {
                // Create new color swatch
                const newSwatch = document.createElement('div');
                newSwatch.className = 'color-swatch';
                newSwatch.setAttribute('data-color', colorName.value.trim());
                newSwatch.style.backgroundColor = colorPicker.value;
                
                // Add click event
                newSwatch.addEventListener('click', function() {
                    this.classList.toggle('selected');
                    updateColorInput(document.querySelectorAll('#colorSwatches .color-swatch'), colorInput);
                });
                
                colorSwatches.appendChild(newSwatch);
                colorName.value = '';
                colorPicker.value = '#ffffff';
            }
        }
        
        // Add custom color functionality for edit form
        function addEditCustomColor() {
            const colorPicker = document.getElementById('editCustomColorPicker');
            const colorName = document.getElementById('editCustomColorName');
            const colorInput = document.getElementById('edit_product_color');
            const colorSwatches = document.getElementById('editColorSwatches');
            
            if (colorName.value.trim()) {
                // Create new color swatch
                const newSwatch = document.createElement('div');
                newSwatch.className = 'color-swatch';
                newSwatch.setAttribute('data-color', colorName.value.trim());
                newSwatch.style.backgroundColor = colorPicker.value;
                
                // Add click event
                newSwatch.addEventListener('click', function() {
                    this.classList.toggle('selected');
                    updateColorInput(document.querySelectorAll('#editColorSwatches .color-swatch'), colorInput);
                });
                
                colorSwatches.appendChild(newSwatch);
                colorName.value = '';
                colorPicker.value = '#ffffff';
            }
        }
        
        // Initialize color swatches when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin Products page loaded successfully');
            setupFileUpload('uploadArea', 'product_image', 'imagePreview', 'previewImg');
            setupFileUpload('editUploadArea', 'edit_product_image', 'editImagePreview', 'editPreviewImg');
            setupColorSwatches();
            
            // Debug: Check if Bootstrap is loaded
            if (typeof bootstrap !== 'undefined') {
                console.log('Bootstrap is loaded');
            } else {
                console.error('Bootstrap is not loaded');
            }
            
            // Debug: Check if edit modal exists
            const editModal = document.getElementById('editProductModal');
            if (editModal) {
                console.log('Edit modal found');
            } else {
                console.error('Edit modal not found');
            }
            
            // Debug: Check if upload areas exist
            const uploadArea = document.getElementById('uploadArea');
            const editUploadArea = document.getElementById('editUploadArea');
            if (uploadArea) console.log('Upload area found');
            if (editUploadArea) console.log('Edit upload area found');
            
            // Debug: Check if file inputs exist
            const productImageInput = document.getElementById('product_image');
            const editProductImageInput = document.getElementById('edit_product_image');
            if (productImageInput) console.log('Product image input found');
            if (editProductImageInput) console.log('Edit product image input found');
        });
    </script>
</body>
</html>
