<?php
// Simple test to check if PHP is working
echo "<h1>PHP Server Test</h1>";
echo "<p>If you can see this, PHP is working!</p>";

echo "<h2>Server Information:</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current Directory:</strong> " . getcwd() . "</p>";

echo "<h2>File System Test:</h2>";
$configFile = 'config/email_config.json';
if (file_exists($configFile)) {
    echo "<p style='color: green;'>✓ Config file exists: $configFile</p>";
    if (is_readable($configFile)) {
        echo "<p style='color: green;'>✓ Config file is readable</p>";
        $content = file_get_contents($configFile);
        echo "<p><strong>Config content:</strong> " . htmlspecialchars($content) . "</p>";
    } else {
        echo "<p style='color: red;'>✗ Config file is not readable</p>";
    }
    if (is_writable($configFile)) {
        echo "<p style='color: green;'>✓ Config file is writable</p>";
    } else {
        echo "<p style='color: red;'>✗ Config file is not writable</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Config file does not exist: $configFile</p>";
}

echo "<h2>Directory Permissions:</h2>";
$configDir = 'config';
if (is_dir($configDir)) {
    echo "<p style='color: green;'>✓ Config directory exists</p>";
    if (is_writable($configDir)) {
        echo "<p style='color: green;'>✓ Config directory is writable</p>";
    } else {
        echo "<p style='color: red;'>✗ Config directory is not writable</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Config directory does not exist</p>";
}

echo "<h2>PHP Extensions:</h2>";
$required_extensions = ['json', 'mail'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✓ $ext extension is loaded</p>";
    } else {
        echo "<p style='color: red;'>✗ $ext extension is not loaded</p>";
    }
}

echo "<h2>Quick Links:</h2>";
echo "<p><a href='index.html' target='_blank'>Main Website</a></p>";
echo "<p><a href='admin.php' target='_blank'>Admin Panel</a></p>";
echo "<p><a href='test_form.html' target='_blank'>Test Forms</a></p>";
?>
