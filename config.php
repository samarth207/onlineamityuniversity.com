<?php
// Set timezone to Indian Standard Time
date_default_timezone_set('Asia/Kolkata');

// Database configuration for Hostinger
// Update these values with your Hostinger database credentials

define('DB_HOST', 'localhost'); // Usually 'localhost' on Hostinger
define('DB_NAME', 'u261758575_onlineamityuni'); // Your database name from Hostinger
define('DB_USER', 'u261758575_onlineamityuni'); // Your database username
define('DB_PASS', 'm3@G$HxmAr?C'); // Your database password

// Fast2SMS API Configuration
define('FAST2SMS_API_KEY', '6wn0ySsBPdpTzVcgrx5E9vlA48IUJkNMuhD2R1XGtiqFjZHWOYSouEDkQj5IT6LVwpb9lBKWmxrFyOM2'); // Replace with your Fast2SMS API key
define('FAST2SMS_SENDER_ID', 'TXTIND'); // Your Fast2SMS Sender ID
define('FAST2SMS_NOTIFICATION_NUMBER', '9260986219'); // Phone number to receive notifications

// Create database connection
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        // Set MySQL session timezone to IST
        $conn->exec("SET time_zone = '+05:30'");
        return $conn;
    } catch(PDOException $e) {
        error_log("Connection failed: " . $e->getMessage());
        return null;
    }
}

// Function to send SMS via Fast2SMS
function sendSMS($phone, $message) {
    $apiKey = FAST2SMS_API_KEY;
    
    // Fast2SMS API endpoint
    $url = 'https://www.fast2sms.com/dev/bulkV2';
    
    // Prepare data for API request
    $data = [
        'authorization' => $apiKey,
        'route' => 'q', // 'q' for quick transactional, 'dlt' for DLT
        'message' => $message,
        'language' => 'english',
        'flash' => 0,
        'numbers' => $phone
    ];
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log the response for debugging
    error_log("Fast2SMS Response: " . $response);
    
    // Return success status
    return $httpCode === 200;
}

// Function to send lead notification SMS
function sendLeadNotification($name, $phone, $email, $course, $formType) {
    $notificationNumber = FAST2SMS_NOTIFICATION_NUMBER;
    
    // Format message
    $message = "New Lead Alert!\n";
    $message .= "Name: $name\n";
    $message .= "Phone: $phone\n";
    $message .= "Email: $email\n";
    $message .= "Course: $course\n";
    $message .= "Type: $formType\n";
    $message .= "Time: " . date('d-M-Y H:i:s');
    
    // Send SMS
    return sendSMS($notificationNumber, $message);
}
?>