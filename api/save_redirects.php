<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

try {
    $redirectMapPath = 'redirect_map.json';
    $result = file_put_contents($redirectMapPath, json_encode($data, JSON_PRETTY_PRINT));
    
    if ($result !== false) {
        echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save settings']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error saving settings: ' . $e->getMessage()]);
}
?>