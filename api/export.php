<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(403);
    exit('Unauthorized');
}

$format = $_GET['format'] ?? 'json';
$allowedFormats = ['json', 'csv', 'txt'];

if (!in_array($format, $allowedFormats)) {
    http_response_code(400);
    exit('Invalid format');
}

try {
    $collection = $db->emails;
    $emails = $collection->find()->toArray();
    
    $emailList = [];
    foreach ($emails as $email) {
        $emailList[] = $email['email'];
    }
    
    $filename = 'emails_export_' . date('Y-m-d_H-i-s');
    
    switch ($format) {
        case 'json':
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $filename . '.json"');
            echo json_encode($emailList, JSON_PRETTY_PRINT);
            break;
            
        case 'csv':
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            echo "email\n";
            foreach ($emailList as $email) {
                echo '"' . str_replace('"', '""', $email) . "\"\n";
            }
            break;
            
        case 'txt':
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="' . $filename . '.txt"');
            foreach ($emailList as $email) {
                echo $email . "\n";
            }
            break;
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Export failed: ' . $e->getMessage();
}
?>