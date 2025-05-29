<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$query = trim($_POST['query'] ?? '');

try {
    $collection = $db->emails;
    
    if (empty($query)) {
        $emails = $collection->find([], ['limit' => 50, 'sort' => ['_id' => -1]])->toArray();
    } else {
        $emails = $collection->find([
            'email' => ['$regex' => $query, '$options' => 'i']
        ], ['limit' => 50])->toArray();
    }
    
    $result = [];
    foreach ($emails as $email) {
        $result[] = [
            '_id' => (string)$email['_id'],
            'email' => $email['email']
        ];
    }
    
    echo json_encode(['success' => true, 'emails' => $result]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Search error: ' . $e->getMessage()]);
}
?>