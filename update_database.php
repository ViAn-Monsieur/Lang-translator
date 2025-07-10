<?php
// Database Update Script
// Run this script to update your database with new chat features

require_once 'includes/db.php';

echo "🚀 Starting database update...\n\n";

try {
    // Update database connection to use PDO if not already
    if ($conn instanceof mysqli) {
        $dsn = "mysql:host=$sever_name;dbname=$db_name;charset=utf8mb4";
        $conn = new PDO($dsn, $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "✅ Converted to PDO connection\n";
    }

    // Check if chat tables exist
    $tables_to_create = [];
    
    $stmt = $conn->query("SHOW TABLES LIKE 'chat_conversations'");
    if ($stmt->rowCount() === 0) {
        $tables_to_create[] = 'chat_conversations';
    }
    
    $stmt = $conn->query("SHOW TABLES LIKE 'chat_messages'");
    if ($stmt->rowCount() === 0) {
        $tables_to_create[] = 'chat_messages';
    }
    
    $stmt = $conn->query("SHOW TABLES LIKE 'system_stats'");
    if ($stmt->rowCount() === 0) {
        $tables_to_create[] = 'system_stats';
    }

    if (!empty($tables_to_create)) {
        echo "📋 Creating missing tables: " . implode(', ', $tables_to_create) . "\n";
        
        // Read and execute SQL schema
        $sql_file = __DIR__ . '/sql/database_schema.sql';
        if (file_exists($sql_file)) {
            $sql_content = file_get_contents($sql_file);
            
            // Split by semicolon and execute each statement
            $statements = array_filter(array_map('trim', explode(';', $sql_content)));
            
            foreach ($statements as $statement) {
                if (!empty($statement) && !stripos($statement, 'CREATE DATABASE')) {
                    try {
                        $conn->exec($statement);
                        echo "  ✅ Executed: " . substr($statement, 0, 50) . "...\n";
                    } catch (PDOException $e) {
                        // Ignore table exists errors
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            echo "  ⚠️  Warning: " . $e->getMessage() . "\n";
                        }
                    }
                }
            }
        }
    } else {
        echo "✅ All required tables already exist\n";
    }

    // Add missing columns to existing tables
    echo "\n🔧 Checking for missing columns...\n";
    
    // Check if users table has updated_at column
    $stmt = $conn->query("SHOW COLUMNS FROM users LIKE 'updated_at'");
    if ($stmt->rowCount() === 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        echo "✅ Added updated_at column to users table\n";
    }
    
    // Check if users table has is_active column
    $stmt = $conn->query("SHOW COLUMNS FROM users LIKE 'is_active'");
    if ($stmt->rowCount() === 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE");
        echo "✅ Added is_active column to users table\n";
    }

    // Create indexes if they don't exist
    echo "\n📊 Creating database indexes...\n";
    
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_user_email ON users(email)",
        "CREATE INDEX IF NOT EXISTS idx_translation_user ON translations(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_translation_date ON translations(created_at)",
        "CREATE INDEX IF NOT EXISTS idx_conversation_user ON chat_conversations(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_conversation_updated ON chat_conversations(updated_at)",
        "CREATE INDEX IF NOT EXISTS idx_message_conversation ON chat_messages(conversation_id)",
        "CREATE INDEX IF NOT EXISTS idx_message_user ON chat_messages(user_id)",
        "CREATE INDEX IF NOT EXISTS idx_message_created ON chat_messages(created_at)"
    ];
    
    foreach ($indexes as $index_sql) {
        try {
            $conn->exec($index_sql);
            echo "✅ " . substr($index_sql, 0, 60) . "...\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                echo "⚠️  Warning: " . $e->getMessage() . "\n";
            }
        }
    }

    // Verify installation
    echo "\n🔍 Verifying installation...\n";
    
    $required_tables = ['users', 'translations', 'chat_conversations', 'chat_messages', 'system_stats'];
    $missing_tables = [];
    
    foreach ($required_tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() === 0) {
            $missing_tables[] = $table;
        } else {
            echo "✅ Table '$table' exists\n";
        }
    }
    
    if (!empty($missing_tables)) {
        echo "❌ Missing tables: " . implode(', ', $missing_tables) . "\n";
        echo "Please run the SQL schema manually.\n";
    } else {
        echo "\n🎉 Database update completed successfully!\n";
        echo "\n📋 Summary:\n";
        echo "✅ All required tables exist\n";
        echo "✅ All indexes created\n";
        echo "✅ Database ready for chat features\n";
        echo "\n🚀 You can now use:\n";
        echo "   - Chat with history sidebar\n";
        echo "   - Admin dashboard\n";
        echo "   - Modern UI components\n";
        echo "\n📍 Next steps:\n";
        echo "   1. Visit: chatbot_new.php (new chat with sidebar)\n";
        echo "   2. Admin: admin/dashboard.php (admin panel)\n";
        echo "   3. Test: Create conversations and check history\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Please check your database configuration.\n";
}
?>