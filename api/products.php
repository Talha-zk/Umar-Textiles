<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

function getProducts() {
    $productsFile = '../config/products.json';
    if (!file_exists($productsFile)) {
        return [];
    }
    $products = json_decode(file_get_contents($productsFile), true);
    return $products ?: [];
}

function getCategories() {
    $categoriesFile = '../config/categories.json';
    if (!file_exists($categoriesFile)) {
        return [];
    }
    $categories = json_decode(file_get_contents($categoriesFile), true);
    return $categories ?: [];
}

function getCategoryDisplayName($key) {
    $categories = getCategories();
    foreach ($categories as $category) {
        if ($category['key'] === $key) {
            return $category['display_name'];
        }
    }
    return ucfirst($key);
}

$products = getProducts();
$categories = getCategories();

// Get category display names for frontend
$categoryOptions = [];
foreach ($categories as $category) {
    $categoryOptions[] = [
        'key' => $category['key'],
        'display_name' => $category['display_name']
    ];
}

$response = [
    'products' => $products,
    'categories' => $categoryOptions
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
