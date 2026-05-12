<?php
/**
 * Environment Variable Helper
 * Load and access .env configuration
 */
class Env {
    private static $config = [];
    private static $loaded = false;
    
    /**
     * Load environment variables from .env file
     */
    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }
        
        $envFile = $path ?? dirname(__DIR__) . '/.env';
        
        if (!file_exists($envFile)) {
            // Fallback to .env.example if .env doesn't exist
            $envFile = dirname(__DIR__) . '/.env.example';
        }
        
        if (!file_exists($envFile)) {
            throw new Exception("Environment file not found: $envFile");
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                $value = trim($value, '"\'');
                
                self::$config[$key] = $value;
                putenv("$key=$value");
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Get environment variable
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$config[$key] ?? getenv($key) ?: $default;
    }
    
    /**
     * Check if variable exists
     */
    public static function has($key) {
        if (!self::$loaded) {
            self::load();
        }
        
        return isset(self::$config[$key]) || getenv($key) !== false;
    }
}

// Auto-load .env file
Env::load();
?>
