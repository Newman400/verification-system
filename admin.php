<?php
session_start();
require_once 'config.php';

$isLoggedIn = $_SESSION['admin_logged_in'] ?? false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $password = $_POST['password'] ?? '';
    if ($password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $isLoggedIn = true;
    } else {
        $loginError = 'Invalid password';
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

if (!$isLoggedIn) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>Admin Login</h1>
            <?php if (isset($loginError)): ?>
                <div class="error-message"><?php echo htmlspecialchars($loginError); ?></div>
            <?php endif; ?>
            <form method="POST" class="login-form">
                <input type="password" name="password" placeholder="Admin Password" required>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
<?php
    exit;
}

try {
    $collection = $db->emails;
    $totalEmails = $collection->countDocuments();
    $recentEmails = $collection->find([], ['limit' => 10, 'sort' => ['_id' => -1]])->toArray();
} catch (Exception $e) {
    $dbError = 'Database connection error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>Admin Dashboard</h1>
            <div class="header-actions">
                <span>Total Emails: <?php echo $totalEmails ?? 0; ?></span>
                <a href="?logout=1" class="logout-btn">Logout</a>
            </div>
        </header>

        <div class="admin-content">
            <div class="admin-sidebar">
                <nav class="admin-nav">
                    <button class="nav-item active" data-section="import">Import Emails</button>
                    <button class="nav-item" data-section="manage">Manage Emails</button>
                    <button class="nav-item" data-section="redirects">Redirect URLs</button>
                    <button class="nav-item" data-section="export">Export Data</button>
                </nav>
            </div>

            <div class="admin-main">
                <section id="import-section" class="admin-section active">
                    <h2>Import Emails</h2>
                    <div class="import-area">
                        <input type="file" id="file-input" accept=".txt,.csv,.json" style="display: none;">
                        <div class="file-drop-zone" id="drop-zone">
                            <div class="drop-zone-content">
                                <div class="drop-icon">📁</div>
                                <p>Drop files here or click to browse</p>
                                <small>Supports .txt, .csv, .json</small>
                            </div>
                        </div>
                        <button id="import-btn" class="import-btn" disabled>Import Emails</button>
                    </div>
                </section>

                <section id="manage-section" class="admin-section">
                    <h2>Manage Emails</h2>
                    <div class="manage-controls">
                        <div class="search-bar">
                            <input type="text" id="search-input" placeholder="Search emails...">
                            <button id="search-btn">Search</button>
                        </div>
                        <div class="add-email-form">
                            <input type="email" id="new-email" placeholder="Add new email">
                            <button id="add-email-btn">Add Email</button>
                        </div>
                    </div>
                    <div class="email-list" id="email-list">
                        <?php if (!empty($recentEmails)): ?>
                            <?php foreach ($recentEmails as $email): ?>
                                <div class="email-item" data-id="<?php echo $email['_id']; ?>">
                                    <span class="email-address"><?php echo htmlspecialchars($email['email']); ?></span>
                                    <button class="delete-btn" onclick="deleteEmail('<?php echo $email['_id']; ?>')">Delete</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <section id="redirects-section" class="admin-section">
                    <h2>Redirect URLs</h2>
                    <div class="redirect-settings">
                        <div class="redirect-item">
                            <label>Gmail (@gmail.com)</label>
                            <input type="url" id="gmail-url" placeholder="https://gmail.com">
                        </div>
                        <div class="redirect-item">
                            <label>Yahoo (@yahoo.com)</label>
                            <input type="url" id="yahoo-url" placeholder="https://yahoo.com">
                        </div>
                        <div class="redirect-item">
                            <label>Outlook (@outlook.com)</label>
                            <input type="url" id="outlook-url" placeholder="https://outlook.com">
                        </div>
                        <div class="redirect-item">
                            <label>Default (Other domains)</label>
                            <input type="url" id="default-url" placeholder="https://example.com">
                        </div>
                        <button id="save-redirects-btn" class="save-btn">Save Redirect Settings</button>
                    </div>
                </section>

                <section id="export-section" class="admin-section">
                    <h2>Export Data</h2>
                    <div class="export-options">
                        <button class="export-btn" onclick="exportData('json')">Export as JSON</button>
                        <button class="export-btn" onclick="exportData('csv')">Export as CSV</button>
                        <button class="export-btn" onclick="exportData('txt')">Export as TXT</button>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>