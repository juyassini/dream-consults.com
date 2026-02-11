<?php
/**
 * test_setup.php - Diagnostic script to verify your PHP/database setup
 * 
 * Usage: php test_setup.php
 * Or visit: http://localhost:8000/test_setup.php
 */

require_once 'config.php';

echo "==================================\n";
echo "Dream Consults - Setup Diagnosis\n";
echo "==================================\n\n";

// Test 1: PHP Version
echo "1. PHP Version\n";
echo "   → " . phpversion() . "\n";
echo "   ✅ OK\n\n";

// Test 2: SQLite Support
echo "2. SQLite Support\n";
$sqlite_loaded = extension_loaded('pdo_sqlite');
if ($sqlite_loaded) {
    echo "   → PDO SQLite: ✅ Enabled\n\n";
} else {
    echo "   → PDO SQLite: ❌ NOT ENABLED\n";
    echo "   ⚠️  Contact form database won't work!\n\n";
}

// Test 3: Database File
echo "3. Database File\n";
$db_path = DB_PATH;
if (file_exists($db_path)) {
    echo "   → Location: $db_path\n";
    echo "   → Size: " . filesize($db_path) . " bytes\n";
    echo "   ✅ Database exists\n\n";
} else {
    echo "   → Location: $db_path\n";
    echo "   → Status: Will be created on first submission\n";
    echo "   ℹ️  This is normal\n\n";
}

// Test 4: Database Connection
echo "4. Database Connection\n";
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    echo "   ✅ Connection successful\n\n";
    
    // Test 5: Submissions Table
    echo "5. Submissions Table\n";
    $table_exists = false;
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='contacts'");
    $result = $stmt->fetch();
    
    if ($result) {
        $table_exists = true;
        echo "   ✅ Table 'contacts' exists\n";
        
        // Count submissions
        $count_stmt = $pdo->query("SELECT COUNT(*) as cnt FROM contacts");
        if ($count_stmt) {
            $count = $count_stmt->fetch();
            echo "   → Submissions: " . $count['cnt'] . "\n";
        }
    } else {
        echo "   ℹ️  Table 'contacts' will be created on first submission\n";
    }
    echo "\n";
    
} catch (PDOException $e) {
    echo "   ❌ Connection failed: " . $e->getMessage() . "\n\n";
}

// Test 6: File Permissions
echo "6. File Permissions\n";
$project_dir = dirname(__FILE__);
if (is_writable($project_dir)) {
    echo "   → Project folder: ✅ Writable\n";
} else {
    echo "   → Project folder: ❌ NOT writable\n";
    echo "   ⚠️  Database may fail to write!\n";
}
echo "\n";

// Test 7: Configuration
echo "7. Configuration Status\n";
echo "   → Contact Recipient: " . CONTACT_RECIPIENT . "\n";
echo "   → Email Sending: " . (SEND_EMAIL ? "✅ Enabled" : "❌ Disabled") . "\n";
echo "   → SMTP Host: " . (SMTP_HOST ? SMTP_HOST : "(empty)") . "\n";
echo "   → Admin Password Hash: " . (ADMIN_PASSWORD_HASH ? "✅ Set" : "Using plaintext") . "\n";
echo "\n";

// Test 8: contact.php
echo "8. contact.php Handler\n";
if (file_exists(__DIR__ . '/contact.php')) {
    echo "   ✅ contact.php exists\n";
} else {
    echo "   ❌ contact.php NOT found\n";
}
echo "\n";

// Test 9: Admin Panel
echo "9. Admin Panel\n";
if (file_exists(__DIR__ . '/admin/submissions.php')) {
    echo "   ✅ admin/submissions.php exists\n";
    echo "   📍 Access at: http://localhost:8000/admin/submissions.php\n";
} else {
    echo "   ❌ admin/submissions.php NOT found\n";
}
echo "\n";

// Summary
echo "==================================\n";
echo "Summary\n";
echo "==================================\n";
if ($sqlite_loaded && is_writable($project_dir)) {
    echo "✅ Your setup looks good!\n\n";
    echo "Next steps:\n";
    echo "1. Open http://localhost:8000 in your browser\n";
    echo "2. Fill in the contact form\n";
    echo "3. Check http://localhost:8000/admin/submissions.php\n";
} else {
    echo "⚠️  Your setup needs attention!\n\n";
    if (!$sqlite_loaded) {
        echo "• Enable PDO SQLite in your PHP installation\n";
    }
    if (!is_writable($project_dir)) {
        echo "• Make sure the project folder is writable\n";
    }
}
echo "\n";
?>
