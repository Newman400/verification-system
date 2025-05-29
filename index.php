<?php
session_start();

// Detectarea automată a domeniului
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Simplu: doar setează o sesiune când Turnstile returnează success
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cf-turnstile-response'])) {
    $token = $_POST['cf-turnstile-response'];
    
    // Dacă avem un token (înseamnă că Turnstile a funcționat pe frontend)
    if (!empty($token) && strlen($token) > 50) {
        $_SESSION['turnstile_verified'] = true;
        $_SESSION['turnstile_time'] = time();
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid token']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
    <title>Just a moment...</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="robots" content="noindex,nofollow">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        html {
            line-height: 1.15;
            -webkit-text-size-adjust: 100%;
            color: #313131;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif;
        }
        
        body {
            display: flex;
            flex-direction: column;
            height: 100vh;
            min-height: 100vh;
        }
        
        .main-content {
            margin: 8rem auto;
            max-width: 60rem;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
        
        @media (width <= 720px) {
            .main-content {
                margin-top: 4rem;
            }
        }
        
        .h1 {
            font-size: 2rem;
            font-weight: 500;
            line-height: 3rem;
            margin-bottom: 2rem;
        }
        
        .verification-message {
            margin-bottom: 2rem;
            color: #666;
        }
        
        .turnstile-container {
            margin: 2rem 0;
        }
        
        .footer-message {
            margin-top: 2rem;
            color: #666;
            font-size: 0.9rem;
        }
        
        .status {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 4px;
            display: none;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #222;
                color: #d9d9d9;
            }
        }
    </style>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body>
    <div class="main-wrapper" role="main">
        <div class="main-content">
            <div class="h1"><?php echo htmlspecialchars($domain); ?></div>
            <div class="verification-message">Verify you are human by completing the action below.</div>
            
            <div class="turnstile-container">
                <div class="cf-turnstile" 
                     data-sitekey="0x4AAAAAABehpPA74WTKx33D" 
                     data-callback="onTurnstileSuccess"
                     data-theme="light">
                </div>
            </div>
            
            <div id="status" class="status"></div>
            
            <div class="footer-message">
                <?php echo htmlspecialchars($domain); ?> needs to review the security of your connection before proceeding.
            </div>
        </div>
    </div>

    <script>
        function showStatus(message, isSuccess = false) {
            const status = document.getElementById('status');
            status.textContent = message;
            status.className = 'status ' + (isSuccess ? 'success' : 'error');
            status.style.display = 'block';
        }
        
        function onTurnstileSuccess(token) {
            console.log('Turnstile completed successfully');
            showStatus('Verification successful! Redirecting...', true);
            
            // Trimite token-ul la server doar pentru a seta sesiunea
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'cf-turnstile-response=' + encodeURIComponent(token)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    setTimeout(() => {
                        window.location.href = 'step2.php';
                    }, 1500);
                } else {
                    showStatus('Session setup failed. Please try again.');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Chiar dacă serverul nu răspunde, Turnstile a funcționat
                // Redirecționează oricum
                setTimeout(() => {
                    window.location.href = 'step2.php';
                }, 1500);
            });
        }
    </script>
</body>
</html>