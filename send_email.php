<?php
// Start session
session_start();

// Set header for JSON response
header('Content-Type: application/json');

// Get JSON data from request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// For debugging
error_log("Received data: " . print_r($data, true));

// Validate required fields
if (!isset($data['subject']) || !isset($data['body']) || !isset($data['to'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Process email fields
$to = $data['to'];
$subject = $data['subject'];
$message = $data['body'];
$cc = isset($data['cc']) ? $data['cc'] : '';
$bcc = isset($data['bcc']) ? $data['bcc'] : '';

// Validate main recipient email
if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid recipient email address']);
    exit;
}

// Additional email headers
$headers = 'From: no-reply@yourcompany.com' . "\r\n" .
           'Reply-To: hr@yourcompany.com' . "\r\n";

// Add CC header if provided
if (!empty($cc)) {
    // Handle multiple CC emails separated by commas
    $cc_emails = explode(',', $cc);
    $valid_cc_emails = [];
    
    foreach ($cc_emails as $email) {
        $email = trim($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $valid_cc_emails[] = $email;
        }
    }
    
    if (!empty($valid_cc_emails)) {
        $headers .= 'Cc: ' . implode(',', $valid_cc_emails) . "\r\n";
    }
}

// Add BCC header if provided
if (!empty($bcc)) {
    // Handle multiple BCC emails separated by commas
    $bcc_emails = explode(',', $bcc);
    $valid_bcc_emails = [];
    
    foreach ($bcc_emails as $email) {
        $email = trim($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $valid_bcc_emails[] = $email;
        }
    }
    
    if (!empty($valid_bcc_emails)) {
        $headers .= 'Bcc: ' . implode(',', $valid_bcc_emails) . "\r\n";
    }
}

// Add remaining headers
$headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n" .
            'MIME-Version: 1.0' . "\r\n" .
            'Content-Type: text/html; charset=UTF-8';

// Format message as HTML
$formatted_message = nl2br(htmlspecialchars($message));

// For demonstration purposes, we'll just return success
// In a real application, you would send the actual email
// $mail_sent = mail($to, $subject, $formatted_message, $headers);

// Log the attempt
error_log("Email send attempt to: $to with subject: $subject");
if (!empty($cc)) {
    error_log("CC: $cc");
}
if (!empty($bcc)) {
    error_log("BCC: $bcc");
}

// Return success
echo json_encode(['success' => true]);
?> 