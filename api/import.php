<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['txt', 'csv', 'json'];

if (!in_array($extension, $allowedExtensions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file format']);
    exit;
}

try {
    $content = file_get_contents($file['tmp_name']);
    $emails = [];
    
    switch ($extension) {
        case 'txt':
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $email = trim($line);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $email;
                }
            }
            break;
            
        case 'csv':
            $lines = str_getcsv($content, "\n");
            foreach ($lines as $line) {
                $data = str_getcsv($line);
                foreach ($data as $item) {
                    $email = trim($item);
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $emails[] = $email;
                    }
                }
            }
            break;
            
        case 'json':
            $data = json_decode($content, true);
            if (is_array($data)) {
                foreach ($data as $item) {
                    if (is_string($item)) {
                        $email = trim($item);
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $emails[] = $email;
                        }
                    } elseif (is_array($item) && isset($item['email'])) {
                        $email = trim($item['email']);
                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $emails[] = $email;
                        }
                    }
                }
            }
            break;
    }
    
    $emails = array_unique($emails);
    $collection = $db->emails;
    $inserted = 0;
    
    foreach ($emails as $email) {
        $existing = $collection->findOne(['email' => $email]);
        if (!$existing) {
            $collection->insertOne([
                'email' => $email,
                'created_at' => new MongoDB\BSON\UTCDateTime()
            ]);
            $inserted++;
        }
    }
    
    echo json_encode(['success' => true, 'count' => $inserted]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Import failed: ' . $e->getMessage()]);
}
?>