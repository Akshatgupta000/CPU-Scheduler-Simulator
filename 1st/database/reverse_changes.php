<?php
require_once __DIR__ . '/../includes/init.php';

try {
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/reverse_changes.sql');
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->exec($statement);
        }
    }
    
    echo "Changes reversed successfully!\n";
    echo "Removed tables:\n";
    echo "- reports\n";
    echo "- analytics\n";
    echo "- saved_configurations\n";
    echo "- users\n";
    echo "\nAll associated indexes have been removed.\n";
    
} catch (PDOException $e) {
    echo "Error reversing changes: " . $e->getMessage() . "\n";
    exit(1);
}
?> 