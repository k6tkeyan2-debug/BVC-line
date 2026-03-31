<?php
// Email Configuration
$recipient_email = 'sales-sg@bvcline.com.sg';
$from_email = $email; // Use the email from the form submission
$site_name = 'BVC Lines';

// Logging function
function log_email_activity($message, $data = null) {
    $log_file = __DIR__ . '/email_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message";

    if ($data !== null) {
        $log_entry .= " | Data: " . json_encode($data, JSON_PRETTY_PRINT);
    }

    $log_entry .= "\n" . str_repeat('-', 80) . "\n";

    // Try to write to log file, create if doesn't exist
    $fp = fopen($log_file, 'a');
    if ($fp) {
        fwrite($fp, $log_entry);
        fclose($fp);
    }
}

// Log script start
log_email_activity("Email script started", array(
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
    'remote_ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown'
));

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
    log_email_activity("Form validation failed", array(
        'errors' => $errors,
        'form_data' => array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'country' => $country,
            'message_length' => strlen($message)
        )
    ));

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

// Log email attempt
log_email_activity("Attempting to send email", array(
    'to' => $recipient_email,
    'from' => $from_email,
    'subject' => $subject,
    'headers' => $headers,
    'form_data' => array(
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'country' => $country,
        'message_length' => strlen($message)
    )
));

// Send email
try {
    $mail_sent = mail($recipient_email, $subject, $email_body, $headers);

    // Log result
    if ($mail_sent) {
        log_email_activity("Email sent successfully to recipient", array(
            'recipient' => $recipient_email,
            'subject' => $subject
        ));
    } else {
        log_email_activity("Email sending failed to recipient", array(
            'recipient' => $recipient_email,
            'subject' => $subject,
            'error' => 'mail() function returned false'
        ));
    }
} catch (Exception $e) {
    log_email_activity("Exception occurred while sending email to recipient", array(
        'recipient' => $recipient_email,
        'subject' => $subject,
        'error' => $e->getMessage(),
        'error_code' => $e->getCode()
    ));
    $mail_sent = false;
}

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

    // Log confirmation email attempt
    log_email_activity("Attempting to send confirmation email to user", array(
        'to' => $email,
        'subject' => $user_subject
    ));

    try {
        $confirmation_sent = mail($email, $user_subject, $user_body, $headers);

        if ($confirmation_sent) {
            log_email_activity("Confirmation email sent successfully to user", array(
                'user_email' => $email,
                'subject' => $user_subject
            ));
        } else {
            log_email_activity("Confirmation email failed to user", array(
                'user_email' => $email,
                'subject' => $user_subject,
                'error' => 'mail() function returned false for confirmation'
            ));
        }
    } catch (Exception $e) {
        log_email_activity("Exception occurred while sending confirmation email", array(
            'user_email' => $email,
            'subject' => $user_subject,
            'error' => $e->getMessage(),
            'error_code' => $e->getCode()
        ));
        $confirmation_sent = false;
    }

    http_response_code(200);
    echo json_encode(array(
        'status' => 'success',
        'message' => 'Thank you! Your message has been sent successfully. We will contact you shortly.'
    ));
} else {
    log_email_activity("Email sending completely failed", array(
        'recipient' => $recipient_email,
        'user_email' => $email,
        'error' => 'Both recipient and confirmation emails failed'
    ));

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
