# Umar Textiles - Form System Setup Guide

## Overview
This implementation provides a fully functional form system for your Umar Textiles website with:
- Email form submissions to a configurable Gmail address
- Admin panel to change recipient email without editing code
- File-based configuration (no database required)
- Proper error handling and success messages
- Works with both contact form and footer newsletter form

## Files Created/Modified

### New Files:
1. `config/email_config.json` - Stores the recipient email address
2. `process_form.php` - Handles form submissions and sends emails
3. `admin.php` - Admin panel to manage recipient email
4. `test_form.html` - Test page to verify functionality
5. `FORM_SETUP_README.md` - This setup guide

### Modified Files:
1. `contact.html` - Updated contact form with proper names and IDs
2. `include/footer.html` - Updated footer form with proper names and IDs
3. `assets/js/scripts.js` - Added form handling functionality
4. `assets/css/work.css` - Added form message styles

## Setup Instructions

### 1. Server Requirements
- PHP 7.0 or higher
- PHP mail() function enabled
- Web server (Apache, Nginx, etc.)

### 2. Initial Configuration
1. **Set the initial recipient email:**
   - Open `config/email_config.json`
   - Change `"recipient_email"` to your desired Gmail address
   - Example: `"recipient_email": "your-email@gmail.com"`

2. **Set admin password:**
   - Open `admin.php`
   - Find line: `$admin_password = "admin123";`
   - Change `"admin123"` to a secure password

### 3. File Permissions
Ensure the following directories/files are writable by the web server:
- `config/` directory
- `config/email_config.json` file

### 4. Email Configuration
For reliable email delivery, consider:
- Using a proper SMTP server instead of PHP's mail() function
- Setting up SPF/DKIM records for your domain
- Using a service like SendGrid, Mailgun, or AWS SES

## Usage

### For Website Visitors:
1. **Contact Form** (`contact.html`):
   - Fill out the form with name, email, company, phone, inquiry type, subject, and message
   - Click "Enter" to submit
   - Success/error messages will appear below the form

2. **Footer Newsletter Form** (appears on all pages):
   - Enter name and email
   - Click "ENTER" to subscribe
   - Success/error messages will appear below the form

### For Administrators:
1. **Access Admin Panel:**
   - Navigate to `yourdomain.com/admin.php`
   - Enter the admin password
   - Click "Login"

2. **Change Recipient Email:**
   - View current recipient email
   - Enter new email address
   - Click "Update Email"
   - Changes take effect immediately

## Testing

### 1. Test Forms:
- Visit `yourdomain.com/test_form.html`
- Test both contact and footer forms
- Verify emails are received

### 2. Test Admin Panel:
- Visit `yourdomain.com/admin.php`
- Login with admin password
- Change recipient email
- Verify changes are saved

## Troubleshooting

### Common Issues:

1. **Emails not being sent:**
   - Check PHP mail() function is enabled
   - Verify server can send emails
   - Check spam folder
   - Consider using SMTP instead of mail()

2. **Admin panel not working:**
   - Check file permissions on `config/` directory
   - Verify PHP sessions are enabled
   - Check for PHP errors in server logs

3. **Forms not submitting:**
   - Check browser console for JavaScript errors
   - Verify `process_form.php` is accessible
   - Check server error logs

4. **Configuration not saving:**
   - Check write permissions on `config/email_config.json`
   - Verify PHP has write access to the config directory

### Error Messages:
- "Method not allowed" - Form not submitted via POST
- "No data received" - Form data not properly sent
- "Name and email are required" - Missing required fields
- "Please enter a valid email address" - Invalid email format
- "Failed to send email" - Server email configuration issue

## Security Considerations

1. **Admin Password:**
   - Use a strong, unique password
   - Consider implementing proper authentication
   - Change default password immediately

2. **File Permissions:**
   - Restrict access to `config/` directory
   - Set appropriate file permissions
   - Consider moving config outside web root

3. **Input Validation:**
   - All inputs are sanitized and validated
   - Email addresses are verified
   - XSS protection implemented

## Customization

### Adding New Forms:
1. Create form with proper `name` attributes
2. Add form ID (e.g., `id="newForm"`)
3. Add form type to JavaScript handler
4. Update PHP processing logic if needed

### Styling Messages:
- Modify CSS classes in `assets/css/work.css`
- Customize `.alert-success` and `.alert-danger` styles
- Adjust message positioning and appearance

### Email Templates:
- Modify email content in `process_form.php`
- Customize subject lines and message format
- Add additional fields as needed

## Support

For technical support or questions:
- Check server error logs
- Verify all files are properly uploaded
- Test with the provided test page
- Ensure PHP and web server are properly configured

## File Structure
```
Umar-Textiles/
├── config/
│   └── email_config.json
├── assets/
│   ├── css/
│   │   └── work.css (modified)
│   └── js/
│       └── scripts.js (modified)
├── include/
│   └── footer.html (modified)
├── contact.html (modified)
├── process_form.php (new)
├── admin.php (new)
├── test_form.html (new)
└── FORM_SETUP_README.md (new)
```

## Next Steps
1. Test the system thoroughly
2. Set up proper email delivery (SMTP recommended)
3. Customize email templates as needed
4. Consider adding form analytics
5. Implement additional security measures if needed
