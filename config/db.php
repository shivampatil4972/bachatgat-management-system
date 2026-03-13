<?php
/**
 * Database Connection Class
 * Bachat Gat Smart Management System
 * 
 * This class handles PDO database connection using Singleton pattern
 * Ensures only one connection instance throughout the application
 * Implements secure PDO with prepared statements
 * 
 * @author Your Name
 * @version 1.0
 */

class Database {
    
    // Database credentials
    private $host = 'localhost';
    private $db_name = 'bachat_gat_db';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    
    // PDO connection instance
    private $conn = null;
    
    // Singleton instance
    private static $instance = null;
    
    /**
     * Private constructor to prevent direct instantiation
     * Implements Singleton pattern
     */
    private function __construct() {
        // Constructor is private - use getInstance() instead
    }
    
    /**
     * Get singleton instance of Database class
     * @return Database Single instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish PDO database connection
     * @return PDO Database connection object
     */
    public function getConnection() {
        
        // Return existing connection if already established
        if ($this->conn !== null) {
            return $this->conn;
        }
        
        try {
            // DSN (Data Source Name)
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
            
            // PDO options for security and performance
            $options = [
                // Set error mode to exceptions
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                
                // Set default fetch mode to associative array
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                
                // Disable emulated prepared statements for true prepared statements
                PDO::ATTR_EMULATE_PREPARES => false,
                
                // Set persistent connection (optional - better performance)
                PDO::ATTR_PERSISTENT => false,
                
                // Set character set
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
            ];
            
            // Create PDO instance
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch (PDOException $e) {
            // Log error (in production, log to file instead of displaying)
            $this->logError($e->getMessage());
            
            // Display user-friendly error
            die($this->getErrorPage($e->getMessage()));
        }
        
        return $this->conn;
    }
    
    /**
     * Execute a prepared SELECT query
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array|false Result set or false
     */
    public function select($query, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a prepared INSERT query (supports both formats)
     * Format 1: insert("INSERT INTO table (...) VALUES (...)", [...])
     * Format 2: insert('table', ['col' => 'val', ...])
     * @param string $query SQL query or table name
     * @param array $params Parameters to bind or data array
     * @return int|false Last insert ID or false
     */
    public function insert($query, $params = []) {
        // Detect if this is a helper call (table, data) or raw SQL (query, params)
        if (!empty($params) && stripos($query, 'INSERT') === false && stripos($query, 'SELECT') === false) {
            // This looks like insert('table', ['data' => ...]) format
            return $this->insertRecord($query, $params);
        }
        
        // Traditional SQL format
        try {
            $stmt = $this->getConnection()->prepare($query);
            $stmt->execute($params);
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a prepared UPDATE query (supports both formats)
     * Format 1: update("UPDATE table SET ... WHERE ...", [...])
     * Format 2: update('table', $id, ['col' => 'val', ...])
     * @param string $query SQL query or table name
     * @param mixed $params Parameters array, or ID if using helper format
     * @param array $data Data array if using helper format
     * @return int|false Number of affected rows or false
     */
    public function update($query, $params = [], $data = []) {
        // Detect if this is a helper call (table, id, data) or raw SQL
        if (!empty($data) || (is_numeric($params) && stripos($query, 'UPDATE') === false)) {
            // This looks like update('table', $id, ['data' => ...]) format
            return $this->updateById($query, $params, $data);
        }
        
        // Traditional SQL format  
        try {
            $stmt = $this->getConnection()->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a prepared DELETE query
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return int|false Number of affected rows or false
     */
    public function delete($query, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }
    
    /**
     * Execute a custom query (for complex operations)
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return PDOStatement|false Statement object or false
     */
    public function query($query, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }
    
    /**
     * Begin database transaction
     * @return bool Success status
     */
    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }
    
    /**
     * Commit database transaction
     * @return bool Success status
     */
    public function commit() {
        return $this->getConnection()->commit();
    }
    
    /**
     * Rollback database transaction
     * @return bool Success status
     */
    public function rollback() {
        return $this->getConnection()->rollBack();
    }
    
    /**
     * Get single row from database
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return array|false Single row or false
     */
    public function selectOne($query, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($query);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }
    
    /**
     * Get single value from database
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return mixed Single value or false
     */
    public function selectValue($query, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->logError($e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if record exists
     * @param string $query SQL query with placeholders
     * @param array $params Parameters to bind
     * @return bool True if exists, false otherwise
     */
    public function exists($query, $params = []) {
        $result = $this->selectOne($query, $params);
        return $result !== false && !empty($result);
    }
    
    /**
     * Get count of records
     * @param string $table Table name
     * @param string $where WHERE clause (optional)
     * @param array $params Parameters to bind
     * @return int|false Count or false
     */
    public function count($table, $where = '', $params = []) {
        $query = "SELECT COUNT(*) FROM {$table}";
        if (!empty($where)) {
            $query .= " WHERE {$where}";
        }
        return $this->selectValue($query, $params);
    }
    
    /**
     * Find a record by ID
     * @param string $table Table name
     * @param int $id Primary key value
     * @param string $primaryKey Primary key column name (default: table_name_id)
     * @return array|false Record or false
     */
    public function findById($table, $id, $primaryKey = null) {
        if ($primaryKey === null) {
            // Auto-detect primary key: users -> user_id, members -> member_id
            $primaryKey = rtrim($table, 's') . '_id';
        }
        return $this->selectOne(
            "SELECT * FROM {$table} WHERE {$primaryKey} = ?",
            [$id]
        );
    }
    
    /**
     * Update a record by ID (helper method)
     * @param string $table Table name
     * @param int $id Primary key value
     * @param array $data Associative array of column => value
     * @param string $primaryKey Primary key column name (default: table_name_id)
     * @return int|false Number of affected rows or false
     */
    public function updateById($table, $id, $data, $primaryKey = null) {
        if (empty($data)) {
            return false;
        }
        
        if ($primaryKey === null) {
            // Auto-detect primary key: users -> user_id, members -> member_id
            $primaryKey = rtrim($table, 's') . '_id';
        }
        
        // Build SET clause
        $setParts = [];
        $values = [];
        foreach ($data as $column => $value) {
            $setParts[] = "{$column} = ?";
            $values[] = $value;
        }
        $values[] = $id; // Add ID for WHERE clause
        
        $setClause = implode(', ', $setParts);
        $query = "UPDATE {$table} SET {$setClause} WHERE {$primaryKey} = ?";
        
        return $this->update($query, $values);
    }
    
    /**
     * Insert a record (helper method)
     * @param string $table Table name
     * @param array $data Associative array of column => value
     * @return int|false Last insert ID or false
     */
    public function insertRecord($table, $data) {
        if (empty($data)) {
            return false;
        }
        
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($values), '?');
        
        $columnList = implode(', ', $columns);
        $placeholderList = implode(', ', $placeholders);
        
        $query = "INSERT INTO {$table} ({$columnList}) VALUES ({$placeholderList})";
        
        return $this->insert($query, $values);
    }
    
    /**
     * Delete a record by ID (helper method)
     * @param string $table Table name
     * @param int $id Primary key value
     * @param string $primaryKey Primary key column name (default: table_name_id)
     * @return int|false Number of affected rows or false
     */
    public function deleteById($table, $id, $primaryKey = null) {
        if ($primaryKey === null) {
            // Auto-detect primary key: users -> user_id, members -> member_id
            $primaryKey = rtrim($table, 's') . '_id';
        }
        
        $query = "DELETE FROM {$table} WHERE {$primaryKey} = ?";
        return $this->delete($query, [$id]);
    }
    
    /**
     * Test database connection
     * @return bool Connection status
     */
    public function testConnection() {
        try {
            $this->getConnection();
            // Try a simple query
            $this->selectValue("SELECT 1");
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Log database errors
     * @param string $error Error message
     */
    private function logError($error) {
        // In production, log to file
        $logFile = __DIR__ . '/../logs/db_errors.log';
        $logDir = dirname($logFile);
        
        // Create logs directory if it doesn't exist
        if (!file_exists($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        // Log error with timestamp
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] Database Error: {$error}" . PHP_EOL;
        
        // Write to log file
        @file_put_contents($logFile, $logMessage, FILE_APPEND);
        
        // In development, also display error
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            error_log("Database Error: {$error}");
        }
    }
    
    /**
     * Generate error page HTML
     * @param string $errorMessage Error message
     * @return string HTML error page
     */
    private function getErrorPage($errorMessage) {
        // In production, show generic error
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            $displayError = 'Unable to connect to database. Please contact administrator.';
        } else {
            // In development, show detailed error
            $displayError = htmlspecialchars($errorMessage);
        }
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Error</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 600px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .error-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        h1 {
            color: #dc2626;
            margin-bottom: 15px;
            font-size: 28px;
        }
        p {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .error-details {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 8px;
            text-align: left;
            margin-top: 20px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #dc2626;
            overflow-x: auto;
        }
        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>Database Connection Failed</h1>
        <p>We're having trouble connecting to the database. Please check your configuration and try again.</p>
        <div class="error-details">
            {$displayError}
        </div>
        <a href="javascript:location.reload()" class="btn">Try Again</a>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Prevent cloning of instance
     */
    private function __clone() {
        // Singleton - prevent cloning
    }
    
    /**
     * Prevent unserialization of instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Close database connection
     */
    public function closeConnection() {
        $this->conn = null;
    }
}

// Usage example (commented out):
/*
// Get database instance
$db = Database::getInstance();

// Method 1: Using helper methods
$users = $db->select("SELECT * FROM users WHERE role = ?", ['member']);

// Method 2: Using direct PDO
$conn = $db->getConnection();
$stmt = $conn->prepare("SELECT * FROM members WHERE member_id = ?");
$stmt->execute([1]);
$member = $stmt->fetch();

// Insert example
$userId = $db->insert(
    "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)",
    ['John Doe', 'john@example.com', password_hash('password', PASSWORD_DEFAULT), 'member']
);

// Update example
$affected = $db->update(
    "UPDATE members SET total_savings = total_savings + ? WHERE member_id = ?",
    [1000, 1]
);

// Delete example
$deleted = $db->delete(
    "DELETE FROM notifications WHERE is_read = ? AND created_at < ?",
    [1, date('Y-m-d', strtotime('-30 days'))]
);

// Transaction example
try {
    $db->beginTransaction();
    
    // Multiple operations
    $db->insert("INSERT INTO loans (...) VALUES (...)", [...]);
    $db->update("UPDATE members SET ...", [...]);
    
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}
*/
?>
