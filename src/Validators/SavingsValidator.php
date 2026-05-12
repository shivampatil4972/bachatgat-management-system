<?php

namespace Validators;

/**
 * SavingsValidator
 * Validation rules for savings-related operations
 */
class SavingsValidator extends BaseValidator
{
    /**
     * Validate savings deposit
     */
    public static function validateDeposit($data)
    {
        $errors = [];
        
        // Member ID validation
        if (empty($data['member_id'])) {
            $errors['member_id'] = 'Member is required';
        } elseif (!self::validateNumeric($data['member_id'], 1)) {
            $errors['member_id'] = 'Invalid member ID';
        }
        
        // Amount validation
        if (empty($data['amount'])) {
            $errors['amount'] = 'Amount is required';
        } elseif (!self::validateNumeric($data['amount'], 0.01)) {
            $errors['amount'] = 'Amount must be positive';
        }
        
        // Date validation
        if (!empty($data['deposit_date']) && !self::validateDate($data['deposit_date'])) {
            $errors['deposit_date'] = 'Invalid deposit date format';
        }
        
        return $errors;
    }
    
    /**
     * Validate savings withdrawal
     */
    public static function validateWithdrawal($data)
    {
        $errors = [];
        
        // Member ID validation
        if (empty($data['member_id'])) {
            $errors['member_id'] = 'Member is required';
        } elseif (!self::validateNumeric($data['member_id'], 1)) {
            $errors['member_id'] = 'Invalid member ID';
        }
        
        // Amount validation
        if (empty($data['amount'])) {
            $errors['amount'] = 'Amount is required';
        } elseif (!self::validateNumeric($data['amount'], 0.01)) {
            $errors['amount'] = 'Amount must be positive';
        }
        
        // Date validation
        if (!empty($data['withdrawal_date']) && !self::validateDate($data['withdrawal_date'])) {
            $errors['withdrawal_date'] = 'Invalid withdrawal date format';
        }
        
        return $errors;
    }
    
    /**
     * Validate interest recording
     */
    public static function validateInterest($data)
    {
        $errors = [];
        
        // Member ID validation
        if (empty($data['member_id'])) {
            $errors['member_id'] = 'Member is required';
        } elseif (!self::validateNumeric($data['member_id'], 1)) {
            $errors['member_id'] = 'Invalid member ID';
        }
        
        // Amount validation
        if (!isset($data['interest_amount'])) {
            $errors['interest_amount'] = 'Interest amount is required';
        } elseif (!self::validateNumeric($data['interest_amount'], 0)) {
            $errors['interest_amount'] = 'Interest amount must be non-negative';
        }
        
        return $errors;
    }
    
    /**
     * Calculate interest on savings
     * Formula: Interest = Principal * Rate * Time / 100
     * where Time is in years
     */
    public static function calculateInterest($principal, $rate, $months)
    {
        $years = $months / 12;
        return ($principal * $rate * $years) / 100;
    }
    
    /**
     * Validate minimum withdrawal amount
     */
    public static function validateMinimumWithdrawal($amount, $minimum = 100)
    {
        return $amount >= $minimum;
    }
}
