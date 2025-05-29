<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = $_POST['id'] ?? '';

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'No ID provided']);
    exit;
}

try {
    $collection = $db->emails;
    $objectId = new MongoDB\BSON\ObjectId($id);
    
    $result = $collection->deleteOne(['_id' => $objectId]);
    
    if ($result->getDeletedCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Email deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Email not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>