<?php
/**
 * admin/submissions.php - View and manage contact submissions
 * Place this in an admin folder for secure access
 */

require_once '../config.php';

session_start();

// Function to verify password (supports both hash and plain text)
function verifyAdminPassword($inputPassword) {
    // Try hashed password first (recommended)
    if (!empty(ADMIN_PASSWORD_HASH)) {
        return password_verify($inputPassword, ADMIN_PASSWORD_HASH);
    }
    // Fallback to plain text (for backward compatibility only)
    return $inputPassword === ADMIN_PASSWORD_PLAIN;
}

// Check authentication
if (!isset($_SESSION['authenticated'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if (verifyAdminPassword($_POST['password'])) {
            $_SESSION['authenticated'] = true;
            $_SESSION['login_time'] = time();
            header('Location: submissions.php');
        } else {
            $error = 'Invalid password';
        }
    }

    if (!isset($_SESSION['authenticated'])) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Admin - Dream Consults</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f0f0f0; }
                .login-box { max-width: 400px; margin: 50px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
                h1 { text-align: center; color: #333; }
                input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
                button { width: 100%; padding: 10px; background: #4caf50; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
                button:hover { background: #2e7d32; }
                .error { color: red; text-align: center; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h1>Admin Panel</h1>
                <form method="POST">
                    <input type="password" name="password" placeholder="Admin Password" required autofocus>
                    <button type="submit">Login</button>
                </form>
                <?php if (isset($error)) echo '<p class="error">' . $error . '</p>'; ?>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: submissions.php');
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $db = new PDO('sqlite:' . DB_PATH);
        $stmt = $db->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        header('Location: submissions.php');
    } catch (Exception $e) {
        $error = 'Failed to delete: ' . $e->getMessage();
    }
}

// Fetch submissions
$submissions = [];
try {
    $db = new PDO('sqlite:' . DB_PATH);
    $result = $db->query("SELECT * FROM contacts ORDER BY submitted_at DESC");
    $submissions = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Submissions - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .logout { background: #d32f2f; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; }
        .logout:hover { background: #b71c1c; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #1e3a8a; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #ddd; }
        tr:hover { background: #f9f9f9; }
        .delete-btn { background: #d32f2f; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; }
        .delete-btn:hover { background: #b71c1c; }
        .message { max-width: 300px; overflow: auto; }
        .submitted-at { font-size: 0.9rem; color: #666; }
        .no-data { text-align: center; padding: 40px; color: #999; }
        .count { background: #4caf50; color: white; padding: 10px 20px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Contact Submissions</h1>
            <a href="?logout=1" class="logout">Logout</a>
        </div>

        <?php if (isset($error)) echo '<p style="color: red;">' . $error . '</p>'; ?>

        <div class="count">Total submissions: <?php echo count($submissions); ?></div>

        <?php if (empty($submissions)): ?>
            <p class="no-data">No contact submissions yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Service</th>
                        <th>Message</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $sub): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($sub['name']); ?></td>
                            <td><a href="mailto:<?php echo htmlspecialchars($sub['email']); ?>"><?php echo htmlspecialchars($sub['email']); ?></a></td>
                            <td><?php echo htmlspecialchars($sub['phone'] ?: '-'); ?></td>
                            <td><?php echo htmlspecialchars($sub['service']); ?></td>
                            <td class="message"><?php echo htmlspecialchars(substr($sub['message'], 0, 100)) . (strlen($sub['message']) > 100 ? '...' : ''); ?></td>
                            <td class="submitted-at"><?php echo date('M d, Y H:i', strtotime($sub['submitted_at'])); ?></td>
                            <td><a href="?delete=<?php echo $sub['id']; ?>" class="delete-btn" onclick="return confirm('Delete this submission?');">Delete</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
