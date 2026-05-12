<?php
/**
 * Authentication Middleware
 * Check if user is authenticated
 */
class AuthMiddleware {
    
    public static function handle() {
        session_start();
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            return Response::json(
                Response::unauthorized('Please login first')
            );
        }
        
        // Check session timeout
        if (isset($_SESSION['LAST_ACTIVITY'])) {
            $timeout = Env::get('SESSION_TIMEOUT', 1800);
            if (time() - $_SESSION['LAST_ACTIVITY'] > $timeout) {
                session_destroy();
                return Response::json(
                    Response::unauthorized('Session expired. Please login again')
                );
            }
        }
        
        // Update last activity
        $_SESSION['LAST_ACTIVITY'] = time();
    }
}

/**
 * Admin Middleware
 * Check if user has admin role
 */
class AdminMiddleware {
    
    public static function handle() {
        AuthMiddleware::handle();
        
        if ($_SESSION['role'] !== 'admin') {
            return Response::json(
                Response::forbidden('Admin access required')
            );
        }
    }
}

/**
 * CORS Middleware
 * Handle Cross-Origin requests
 */
class CorsMiddleware {
    
    public static function handle() {
        header('Access-Control-Allow-Origin: ' . Env::get('APP_URL', '*'));
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Credentials: true');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}

/**
 * Rate Limiting Middleware
 * Prevent API abuse
 */
class RateLimitMiddleware {
    
    public static function handle($limit = 100, $window = 3600) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = "rate_limit:$ip";
        
        // Using file-based rate limiting (can be upgraded to Redis)
        $rateFile = sys_get_temp_dir() . "/$key.json";
        
        if (!file_exists($rateFile)) {
            file_put_contents($rateFile, json_encode(['count' => 1, 'reset_at' => time() + $window]));
            return true;
        }
        
        $data = json_decode(file_get_contents($rateFile), true);
        
        // Check if window expired
        if (time() > $data['reset_at']) {
            file_put_contents($rateFile, json_encode(['count' => 1, 'reset_at' => time() + $window]));
            return true;
        }
        
        // Check if limit exceeded
        if ($data['count'] >= $limit) {
            return Response::json(
                Response::error('Rate limit exceeded', 429)
            );
        }
        
        // Increment counter
        $data['count']++;
        file_put_contents($rateFile, json_encode($data));
        
        return true;
    }
}
?>
