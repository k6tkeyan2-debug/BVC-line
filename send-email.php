<?php
// Email Configuration
$recipient_email = 'k6tkeyan12@gmail.com';
$from_email = 'k6tkeyan2@gmail.com';
$site_name = 'BVC Lines';

// Get form data
$name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
$phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
$country = isset($_POST['country']) ? sanitize_text_field($_POST['country']) : '';
$message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

// Validation
$errors = array();

if (empty($name)) {
    $errors[] = 'Name is required.';
}

if (empty($email) || !is_email($email)) {
    $errors[] = 'Valid email is required.';
}

if (empty($phone)) {
    $errors[] = 'Phone is required.';
}

if (empty($country)) {
    $errors[] = 'Country is required.';
}

if (empty($message)) {
    $errors[] = 'Message is required.';
}

// If there are validation errors
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Validation failed.',
        'errors' => $errors
    ));
    exit;
}

// Prepare email content
$subject = 'New Contact Form Submission from ' . $name;

$email_body = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { background-color: #f5f5f5; padding: 20px; }
        .content { background-color: #ffffff; padding: 20px; border-radius: 5px; }
        .field { margin-bottom: 15px; }
        .field-label { font-weight: bold; color: #333; }
        .field-value { color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='content'>
            <h2>New Contact Form Submission</h2>
            <hr>
            
            <div class='field'>
                <div class='field-label'>Name:</div>
                <div class='field-value'>" . htmlspecialchars($name) . "</div>
            </div>
            
            <div class='field'>
                <div class='field-label'>Email:</div>
                <div class='field-value'><a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></div>
            </div>
            
            <div class='field'>
                <div class='field-label'>Phone:</div>
                <div class='field-value'>" . htmlspecialchars($phone) . "</div>
            </div>
            
            <div class='field'>
                <div class='field-label'>Country:</div>
                <div class='field-value'>" . htmlspecialchars($country) . "</div>
            </div>
            
            <div class='field'>
                <div class='field-label'>Message:</div>
                <div class='field-value'>" . nl2br(htmlspecialchars($message)) . "</div>
            </div>
            
            <hr>
            <p style='color: #999; font-size: 12px;'>Submitted on: " . date('Y-m-d H:i:s') . "</p>
        </div>
    </div>
</body>
</html>
";

// Prepare headers
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
$headers .= "From: " . $from_email . "\r\n";
$headers .= "Reply-To: " . htmlspecialchars($email) . "\r\n";

// Send email
$mail_sent = mail($recipient_email, $subject, $email_body, $headers);

if ($mail_sent) {
    // Also send confirmation email to the user
    $user_subject = 'We received your inquiry - ' . $site_name;
    $user_body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .container { background-color: #f5f5f5; padding: 20px; }
            .content { background-color: #ffffff; padding: 20px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='content'>
                <h2>Thank you for contacting us!</h2>
                <p>Dear " . htmlspecialchars($name) . ",</p>
                <p>We have received your inquiry and will get back to you as soon as possible.</p>
                <p>Best regards,<br>" . $site_name . " Team</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    mail($email, $user_subject, $user_body, $headers);
    
    http_response_code(200);
    echo json_encode(array(
        'status' => 'success',
        'message' => 'Thank you! Your message has been sent successfully. We will contact you shortly.'
    ));
} else {
    http_response_code(500);
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Failed to send email. Please try again later.'
    ));
}

exit;

// Helper functions
function sanitize_text_field($str) {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

function sanitize_email($email) {
    return filter_var(trim($email), FILTER_VALIDATE_EMAIL);
}

function sanitize_textarea_field($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function is_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
?>
