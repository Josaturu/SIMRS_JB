<?php
/**
 * Run SQL Migration - Add Keterangan Fields
 */

require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "=== RUNNING SQL MIGRATION ===\n\n";
    
    // Read SQL file
    $sql_file = __DIR__ . '/sql/FINAL_ADD_ALL_KETERANGAN_FIELDS.sql';
    $sql = file_get_contents($sql_file);
    
    // Split by semicolon and filter
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    $success = 0;
    $skipped = 0;
    
    foreach ($queries as $query) {
        // Skip comments and SELECT queries
        if (empty($query) || 
            strpos($query, '--') === 0 || 
            stripos($query, 'SELECT') === 0 ||
            stripos($query, 'USE') === 0) {
            continue;
        }
        
        try {
            $db->exec($query);
            $success++;
            echo "✅ Executed: " . substr($query, 0, 60) . "...\n";
        } catch (PDOException $e) {
            // Skip if column already exists
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                $skipped++;
                echo "⏭️  Skipped (already exists): " . substr($query, 0, 60) . "...\n";
            } else {
                throw $e;
            }
        }
    }
    
    echo "\n=== MIGRATION SUMMARY ===\n";
    echo "✅ Success: $success queries\n";
    echo "⏭️  Skipped: $skipped queries\n";
    echo "\n✅ Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
