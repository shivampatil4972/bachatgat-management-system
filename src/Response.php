<?php
/**
 * Standardized Response Handler
 * Ensures consistent API responses across the application
 */
class Response {
    
    /**
     * Return success response
     */
    public static function success($message = 'Success', $data = null, $code = 200) {
        http_response_code($code);
        return [
            'success' => true,
            'code' => $code,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Return error response
     */
    public static function error($message = 'Error', $code = 400, $errors = null) {
        http_response_code($code);
        return [
            'success' => false,
            'code' => $code,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Return validation error response
     */
    public static function validationError($errors, $message = 'Validation failed') {
        http_response_code(422);
        return [
            'success' => false,
            'code' => 422,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Return unauthorized response
     */
    public static function unauthorized($message = 'Unauthorized') {
        http_response_code(401);
        return [
            'success' => false,
            'code' => 401,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Return forbidden response
     */
    public static function forbidden($message = 'Forbidden') {
        http_response_code(403);
        return [
            'success' => false,
            'code' => 403,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Return not found response
     */
    public static function notFound($message = 'Not found') {
        http_response_code(404);
        return [
            'success' => false,
            'code' => 404,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Return server error response (hide details from user)
     */
    public static function serverError($message = 'Server error', $internalError = null) {
        http_response_code(500);
        
        // Log internal error details for debugging
        if ($internalError) {
            error_log('[ERROR] ' . $internalError);
        }
        
        return [
            'success' => false,
            'code' => 500,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Send JSON response and exit
     */
    public static function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
?>
