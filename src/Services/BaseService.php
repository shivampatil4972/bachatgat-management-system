<?php
/**
 * Base Service Class
 * All services should extend this for consistency
 */
abstract class BaseService {
    protected $db;
    protected $validator;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Validate data before processing
     */
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            if (!$this->validateField($field, $data[$field] ?? null, $rule)) {
                $errors[$field] = "Validation failed for $field";
            }
        }
        
        return empty($errors) ? null : $errors;
    }
    
    /**
     * Validate individual field
     */
    protected function validateField($field, $value, $rule) {
        if (strpos($rule, 'required') !== false && empty($value)) {
            return false;
        }
        
        if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        if (strpos($rule, 'min:') !== false) {
            preg_match('/min:(\d+)/', $rule, $matches);
            if (strlen($value) < $matches[1]) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Log service action
     */
    protected function log($action, $details = []) {
        error_log("[SERVICE] $action: " . json_encode($details));
    }
    
    /**
     * Handle database errors
     */
    protected function handleDbError($e, $context = []) {
        $this->log('Database Error', array_merge(['error' => $e->getMessage()], $context));
        throw new Exception('Database operation failed');
    }
}
?>
