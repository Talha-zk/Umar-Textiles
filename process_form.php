<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Function to read configuration
function getConfig() {
    $configFile = 'config/email_config.json';
    if (!file_exists($configFile)) {
        return ['recipient_email' => 'info@umartextiles.com', 'site_name' => 'Umar Textiles'];
    }
    $config = json_decode(file_get_contents($configFile), true);
    return $config ?: ['recipient_email' => 'info@umartextiles.com', 'site_name' => 'Umar Textiles'];
}

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to send email
function sendEmail($to, $subject, $message, $headers) {
    // Use PHP's mail function
    return mail($to, $subject, $message, $headers);
}

try {
    // Get form data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('No data received');
    }
    
    // Get configuration
    $config = getConfig();
    $recipientEmail = $config['recipient_email'];
    $siteName = $config['site_name'];
    
    // Determine form type and process accordingly
    $formType = isset($input['form_type']) ? $input['form_type'] : 'contact';
    
    if ($formType === 'footer') {
        // Footer form (simple newsletter/subscription)
        $name = isset($input['name']) ? sanitizeInput($input['name']) : '';
        $email = isset($input['email']) ? sanitizeInput($input['email']) : '';
        
        // Validate required fields
        if (empty($name) || empty($email)) {
            throw new Exception('Name and email are required');
        }
        
        if (!isValidEmail($email)) {
            throw new Exception('Please enter a valid email address');
        }
        
        // Prepare email content
        $subject = "New Newsletter Subscription - $siteName";
        $message = "A new user has subscribed to your newsletter:\n\n";
        $message .= "Name: $name\n";
        $message .= "Email: $email\n";
        $message .= "Date: " . date('Y-m-d H:i:s') . "\n";
        
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
    } else {
        // Contact form (full form)
        $name = isset($input['name']) ? sanitizeInput($input['name']) : '';
        $email = isset($input['email']) ? sanitizeInput($input['email']) : '';
        $company = isset($input['company']) ? sanitizeInput($input['company']) : '';
        $phone = isset($input['phone']) ? sanitizeInput($input['phone']) : '';
        $inquiryType = isset($input['inquiry_type']) ? sanitizeInput($input['inquiry_type']) : '';
        $subject = isset($input['subject']) ? sanitizeInput($input['subject']) : '';
        $message = isset($input['message']) ? sanitizeInput($input['message']) : '';
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($message)) {
            throw new Exception('Name, email, and message are required');
        }
        
        if (!isValidEmail($email)) {
            throw new Exception('Please enter a valid email address');
        }
        
        // Prepare email content
        $emailSubject = "New Contact Form Submission - $siteName";
        if (!empty($subject)) {
            $emailSubject .= " - $subject";
        }
        
        $emailMessage = "A new contact form submission has been received:\n\n";
        $emailMessage .= "Name: $name\n";
        $emailMessage .= "Email: $email\n";
        if (!empty($company)) $emailMessage .= "Company: $company\n";
        if (!empty($phone)) $emailMessage .= "Phone: $phone\n";
        if (!empty($inquiryType)) $emailMessage .= "Inquiry Type: $inquiryType\n";
        if (!empty($subject)) $emailMessage .= "Subject: $subject\n";
        $emailMessage .= "Message:\n$message\n\n";
        $emailMessage .= "Date: " . date('Y-m-d H:i:s') . "\n";
        
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        $subject = $emailSubject;
        $message = $emailMessage;
    }
    
    // Send email
    $emailSent = sendEmail($recipientEmail, $subject, $message, $headers);
    
    if ($emailSent) {
        echo json_encode([
            'success' => true, 
            'message' => $formType === 'footer' ? 
                'Thank you for subscribing to our newsletter!' : 
                'Thank you for your message. We will get back to you soon!'
        ]);
    } else {
        throw new Exception('Failed to send email. Please try again later.');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
