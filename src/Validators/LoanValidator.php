<?php

namespace Validators;

/**
 * LoanValidator
 * Validation rules for loan-related operations
 */
class LoanValidator extends BaseValidator
{
    /**
     * Validate loan creation
     */
    public static function validateCreate($data)
    {
        $errors = [];
        
        // Member ID validation
        if (empty($data['member_id'])) {
            $errors['member_id'] = 'Member is required';
        } elseif (!self::validateNumeric($data['member_id'], 1)) {
            $errors['member_id'] = 'Invalid member ID';
        }
        
        // Loan amount validation
        if (empty($data['loan_amount'])) {
            $errors['loan_amount'] = 'Loan amount is required';
        } elseif (!self::validateNumeric($data['loan_amount'], 1000, 1000000)) {
            $errors['loan_amount'] = 'Loan amount must be between ₹1,000 and ₹10,00,000';
        }
        
        // Tenure validation
        if (empty($data['tenure_months'])) {
            $errors['tenure_months'] = 'Tenure is required';
        } elseif (!self::validateNumeric($data['tenure_months'], 1, 120)) {
            $errors['tenure_months'] = 'Tenure must be between 1-120 months';
        }
        
        // Interest rate validation
        if (isset($data['interest_rate']) && !self::validateNumeric($data['interest_rate'], 0, 50)) {
            $errors['interest_rate'] = 'Interest rate must be between 0-50%';
        }
        
        // Purpose validation
        if (empty($data['purpose'])) {
            $errors['purpose'] = 'Purpose is required';
        } elseif (!self::validateStringLength($data['purpose'], 5, 255)) {
            $errors['purpose'] = 'Purpose must be between 5-255 characters';
        }
        
        return $errors;
    }
    
    /**
     * Validate loan approval
     */
    public static function validateApprove($loanId)
    {
        $errors = [];
        
        if (empty($loanId)) {
            $errors['loan_id'] = 'Loan ID is required';
        } elseif (!self::validateNumeric($loanId, 1)) {
            $errors['loan_id'] = 'Invalid loan ID';
        }
        
        return $errors;
    }
    
    /**
     * Validate installment payment
     */
    public static function validatePayment($data)
    {
        $errors = [];
        
        // Installment ID validation
        if (empty($data['installment_id'])) {
            $errors['installment_id'] = 'Installment ID is required';
        } elseif (!self::validateNumeric($data['installment_id'], 1)) {
            $errors['installment_id'] = 'Invalid installment ID';
        }
        
        // Amount validation
        if (empty($data['amount_paid'])) {
            $errors['amount_paid'] = 'Payment amount is required';
        } elseif (!self::validateNumeric($data['amount_paid'], 0.01)) {
            $errors['amount_paid'] = 'Payment amount must be positive';
        }
        
        return $errors;
    }
    
    /**
     * Validate loan status
     */
    public static function validateStatus($status)
    {
        return self::validateInArray($status, ['pending', 'approved', 'disbursed', 'completed', 'rejected']);
    }
}
