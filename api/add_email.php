<?php
// add_email.php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

try {
    $collection = $db->emails;
    $existing = $collection->findOne(['email' => $email]);
    
    if ($existing) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }
    
    $result = $collection->insertOne([
        'email' => $email,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    
    if ($result->getInsertedCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Email added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add email']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
