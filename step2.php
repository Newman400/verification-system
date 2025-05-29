<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading Portal</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="loading-box">
            <h1>Initialize Connection</h1>
            <p>Press and hold the button for 5 seconds</p>
            
            <div class="hold-button-container">
                <button id="hold-button" class="hold-button">
                    <div class="button-content">
                        <span class="button-text">Hold to Continue</span>
                        <div class="progress-ring">
                            <svg class="progress-svg" viewBox="0 0 120 120">
                                <circle class="progress-circle-bg" cx="60" cy="60" r="54"/>
                                <circle class="progress-circle" cx="60" cy="60" r="54"/>
                            </svg>
                        </div>
                    </div>
                </button>
            </div>
            
            <div class="status-text" id="status-text">Ready</div>
        </div>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>
