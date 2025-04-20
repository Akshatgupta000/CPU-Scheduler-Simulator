<?php
require_once __DIR__ . '/../includes/init.php';

try {
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/updates_auth_analytics.sql');
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->exec($statement);
        }
    }
    
    echo "Database updated successfully!\n";
    echo "Tables created:\n";
    echo "- users\n";
    echo "- saved_configurations\n";
    echo "- analytics\n";
    echo "- reports\n";
    echo "\nTest admin user created:\n";
    echo "Email: admin@example.com\n";
    echo "Password: admin123\n";
    
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
    exit(1);
}
?> 