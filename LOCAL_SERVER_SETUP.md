# Local Server Setup Guide

## Quick Diagnosis

First, try accessing the test file to see what's wrong:
- Open your browser and go to: `http://localhost/test_server.php`
- If you see the PHP test page, PHP is working
- If you see a blank page or error, follow the setup instructions below

## Option 1: Using XAMPP (Recommended for Windows)

### Step 1: Download and Install XAMPP
1. Go to https://www.apachefriends.org/download.html
2. Download XAMPP for Windows
3. Install XAMPP (run as administrator if needed)

### Step 2: Start the Server
1. Open XAMPP Control Panel
2. Click "Start" next to Apache
3. Wait for Apache to start (status should turn green)

### Step 3: Place Your Files
1. Copy your entire `Umar-Textiles` folder to: `C:\xampp\htdocs\`
2. Your website will be available at: `http://localhost/Umar-Textiles/`

### Step 4: Test
- Main site: `http://localhost/Umar-Textiles/index.html`
- Admin panel: `http://localhost/Umar-Textiles/admin.php`
- Test forms: `http://localhost/Umar-Textiles/test_form.html`
- Server test: `http://localhost/Umar-Textiles/test_server.php`

## Option 2: Using WAMP

### Step 1: Download and Install WAMP
1. Go to https://www.wampserver.com/en/
2. Download WAMP for Windows
3. Install WAMP

### Step 2: Start the Server
1. Open WAMP (look for the W icon in system tray)
2. Wait for it to turn green
3. Click on the W icon → Apache → Start

### Step 3: Place Your Files
1. Copy your `Umar-Textiles` folder to: `C:\wamp64\www\` (or `C:\wamp\www\`)
2. Access at: `http://localhost/Umar-Textiles/`

## Option 3: Using Built-in PHP Server (Simple)

### Step 1: Open Command Prompt
1. Press `Win + R`, type `cmd`, press Enter
2. Navigate to your project folder:
   ```
   cd "C:\Users\talha.zafar\Desktop\Umar-Textiles"
   ```

### Step 2: Start PHP Server
Run this command:
```
php -S localhost:8000
```

### Step 3: Access Your Site
- Open browser and go to: `http://localhost:8000`
- All pages will work from this URL

## Option 4: Using Visual Studio Code Live Server (HTML Only)

**Note: This will only work for HTML pages, not PHP functionality**

1. Install "Live Server" extension in VS Code
2. Right-click on `index.html`
3. Select "Open with Live Server"

## Troubleshooting

### If you see "Page Not Found":
- Make sure your files are in the correct directory
- Check that Apache/PHP server is running
- Try accessing `http://localhost/test_server.php` first

### If you see a blank page:
- Check your browser's developer console (F12) for errors
- Look at the server error logs
- Make sure PHP is installed and running

### If forms don't work:
- Make sure you're using a server with PHP (not just file:// protocol)
- Check that the `config` folder has write permissions
- Try the test server file to diagnose issues

### Permission Issues:
- Right-click on the `config` folder
- Properties → Security → Edit
- Add "Everyone" with "Full Control" permissions

## Quick Test Commands

Open Command Prompt in your project folder and run:

```bash
# Check if PHP is installed
php --version

# Start PHP server
php -S localhost:8000

# Check if files exist
dir
dir config
```

## Common URLs to Test

Once your server is running, test these URLs:

1. `http://localhost/Umar-Textiles/test_server.php` - Server diagnostics
2. `http://localhost/Umar-Textiles/index.html` - Main website
3. `http://localhost/Umar-Textiles/admin.php` - Admin panel (password: admin123)
4. `http://localhost/Umar-Textiles/test_form.html` - Form testing page

## Need Help?

If you're still having issues:
1. What error message do you see?
2. What local server software are you using?
3. Can you access `http://localhost/test_server.php`?
4. What happens when you try to open the files directly in your browser?

Let me know the results and I'll help you troubleshoot further!
