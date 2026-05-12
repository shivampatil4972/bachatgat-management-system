<?php

namespace Services;

use Exception;
use Response;

/**
 * LoanService
 * Handle all loan-related business logic
 * 
 * Methods:
 * - getAll()           : Get paginated list of loans
 * - getById()          : Get single loan with details
 * - create()           : Create new loan request
 * - approve()          : Approve loan and create installments
 * - disburse()         : Disburse approved loan
 * - recordPayment()    : Record installment payment
 * - delete()           : Soft delete loan
 * - getStats()         : Get loan statistics
 * - calculateEMI()     : Calculate EMI for loan
 * - getTotalDue()      : Get total amount due
 */
class LoanService extends BaseService
{
    /**
     * Get all loans with pagination
     */
    public function getAll($page = 1, $limit = 50, $filters = [])
    {
        try {
            $offset = ($page - 1) * $limit;
            
            // Build WHERE clause
            $where = "WHERE l.is_deleted = 0";
            $params = [];
            
            if (!empty($filters['status'])) {
                $where .= " AND l.status = ?";
                $params[] = $filters['status'];
            }
            
            if (!empty($filters['member_id'])) {
                $where .= " AND l.member_id = ?";
                $params[] = $filters['member_id'];
            }
            
            // Get total count
            $total = $this->db->selectValue(
                "SELECT COUNT(*) FROM loans l $where",
                $params
            );
            
            // Get paginated data with JOINs
            $query = "
                SELECT 
                    l.loan_id,
                    l.member_id,
                    l.loan_amount,
                    l.interest_rate,
                    l.tenure_months,
                    l.emi_amount,
                    l.status,
                    l.applied_date,
                    l.approved_date,
                    l.disbursed_date,
                    m.member_code,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone
                FROM loans l
                INNER JOIN members m ON l.member_id = m.member_id
                INNER JOIN users u ON m.user_id = u.user_id
                $where
                ORDER BY l.applied_date DESC
                LIMIT ? OFFSET ?
            ";
            
            $loans = $this->db->select($query, [...$params, $limit, $offset]);
            
            return Response::success('Loans retrieved', [
                'data' => $loans,
                'total' => $total,
                'page' => $page,
                'pages' => ceil($total / $limit)
            ]);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to retrieve loans');
        }
    }
    
    /**
     * Get single loan by ID
     */
    public function getById($loanId)
    {
        try {
            $query = "
                SELECT 
                    l.*,
                    m.member_code,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone
                FROM loans l
                INNER JOIN members m ON l.member_id = m.member_id
                INNER JOIN users u ON m.user_id = u.user_id
                WHERE l.loan_id = ? AND l.is_deleted = 0
            ";
            
            $loan = $this->db->selectOne($query, [$loanId]);
            
            if (!$loan) {
                return Response::notFound('Loan not found');
            }
            
            // Get installments
            $installments = $this->db->select(
                "SELECT * FROM installments WHERE loan_id = ? ORDER BY installment_number ASC",
                [$loanId]
            );
            
            $loan['installments'] = $installments;
            
            return Response::success('Loan retrieved', $loan);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to retrieve loan');
        }
    }
    
    /**
     * Create new loan request
     */
    public function create($data)
    {
        try {
            // Validate required fields
            $errors = $this->validate($data, [
                'member_id' => 'required|numeric',
                'loan_amount' => 'required|numeric|min:1000',
                'tenure_months' => 'required|numeric|min:1|max:120',
                'interest_rate' => 'numeric|min:0|max:50',
                'purpose' => 'required|string|max:255'
            ]);
            
            if (!empty($errors)) {
                return Response::validationError($errors);
            }
            
            // Set default interest rate if not provided
            $interestRate = $data['interest_rate'] ?? 12.00;
            
            // Calculate EMI
            $emiAmount = $this->calculateEMI(
                $data['loan_amount'],
                $interestRate,
                $data['tenure_months']
            );
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Insert loan
            $loanId = $this->db->insert('loans', [
                'member_id' => $data['member_id'],
                'loan_amount' => $data['loan_amount'],
                'interest_rate' => $interestRate,
                'tenure_months' => $data['tenure_months'],
                'emi_amount' => $emiAmount,
                'purpose' => $data['purpose'],
                'status' => 'pending',
                'applied_date' => date('Y-m-d H:i:s'),
                'is_deleted' => 0
            ]);
            
            if (!$loanId) {
                throw new Exception('Failed to create loan');
            }
            
            // Log activity
            $this->log('loan_created', "Loan #$loanId created by member ID: " . $data['member_id']);
            
            // Commit transaction
            $this->db->commit();
            
            return Response::created('Loan created successfully', ['loan_id' => $loanId]);
        } catch (Exception $e) {
            $this->db->rollback();
            return $this->handleDbError($e, 'Failed to create loan');
        }
    }
    
    /**
     * Approve loan and create installments
     */
    public function approve($loanId, $data = [])
    {
        try {
            // Get loan
            $loan = $this->db->selectOne(
                "SELECT * FROM loans WHERE loan_id = ? AND is_deleted = 0",
                [$loanId]
            );
            
            if (!$loan) {
                return Response::notFound('Loan not found');
            }
            
            if ($loan['status'] !== 'pending') {
                return Response::error('Only pending loans can be approved', 400);
            }
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Update loan status
            $updated = $this->db->update('loans', 
                ['status' => 'approved', 'approved_date' => date('Y-m-d H:i:s')],
                ['loan_id' => $loanId]
            );
            
            if (!$updated) {
                throw new Exception('Failed to update loan status');
            }
            
            // Create installments
            $startDate = new \DateTime();
            $installmentAmount = $loan['emi_amount'];
            
            for ($i = 1; $i <= $loan['tenure_months']; $i++) {
                // Calculate due date (monthly)
                $dueDate = clone $startDate;
                $dueDate->modify("+$i months");
                
                $this->db->insert('installments', [
                    'loan_id' => $loanId,
                    'installment_number' => $i,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'amount_due' => $installmentAmount,
                    'amount_paid' => 0,
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            // Log activity
            $this->log('loan_approved', "Loan #$loanId approved. Installments created.");
            
            // Commit transaction
            $this->db->commit();
            
            return Response::success('Loan approved successfully', ['installments' => $loan['tenure_months']]);
        } catch (Exception $e) {
            $this->db->rollback();
            return $this->handleDbError($e, 'Failed to approve loan');
        }
    }
    
    /**
     * Disburse approved loan
     */
    public function disburse($loanId)
    {
        try {
            // Get loan
            $loan = $this->db->selectOne(
                "SELECT * FROM loans WHERE loan_id = ? AND is_deleted = 0",
                [$loanId]
            );
            
            if (!$loan) {
                return Response::notFound('Loan not found');
            }
            
            if ($loan['status'] !== 'approved') {
                return Response::error('Only approved loans can be disbursed', 400);
            }
            
            // Update loan status
            $updated = $this->db->update('loans',
                [
                    'status' => 'disbursed',
                    'disbursed_date' => date('Y-m-d H:i:s')
                ],
                ['loan_id' => $loanId]
            );
            
            if (!$updated) {
                return Response::error('Failed to disburse loan', 500);
            }
            
            // Get member for transaction
            $member = $this->db->selectOne(
                "SELECT member_id FROM loans WHERE loan_id = ?",
                [$loanId]
            );
            
            // Record transaction (debit from savings or transfer)
            $this->db->insert('transactions', [
                'member_id' => $member['member_id'],
                'transaction_type' => 'loan_disbursement',
                'amount' => $loan['loan_amount'],
                'description' => "Loan #$loanId disbursed",
                'transaction_date' => date('Y-m-d H:i:s'),
                'status' => 'completed'
            ]);
            
            // Log activity
            $this->log('loan_disbursed', "Loan #$loanId disbursed. Amount: ₹" . $loan['loan_amount']);
            
            return Response::success('Loan disbursed successfully');
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to disburse loan');
        }
    }
    
    /**
     * Record installment payment
     */
    public function recordPayment($installmentId, $data)
    {
        try {
            // Validate amount
            $errors = $this->validate($data, [
                'amount_paid' => 'required|numeric|min:0.01'
            ]);
            
            if (!empty($errors)) {
                return Response::validationError($errors);
            }
            
            // Get installment
            $installment = $this->db->selectOne(
                "SELECT i.*, l.member_id FROM installments i INNER JOIN loans l ON i.loan_id = l.loan_id WHERE i.installment_id = ?",
                [$installmentId]
            );
            
            if (!$installment) {
                return Response::notFound('Installment not found');
            }
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Update installment
            $newPaidAmount = $installment['amount_paid'] + $data['amount_paid'];
            
            $status = $newPaidAmount >= $installment['amount_due'] ? 'paid' : 'partial';
            
            $updated = $this->db->update('installments',
                [
                    'amount_paid' => $newPaidAmount,
                    'status' => $status,
                    'payment_date' => date('Y-m-d H:i:s')
                ],
                ['installment_id' => $installmentId]
            );
            
            if (!$updated) {
                throw new Exception('Failed to update installment');
            }
            
            // Record transaction
            $this->db->insert('transactions', [
                'member_id' => $installment['member_id'],
                'transaction_type' => 'loan_payment',
                'amount' => $data['amount_paid'],
                'description' => "Installment payment for loan (Installment #" . $installment['installment_number'] . ")",
                'transaction_date' => date('Y-m-d H:i:s'),
                'status' => 'completed'
            ]);
            
            // Log activity
            $this->log('payment_recorded', "Payment recorded for installment #$installmentId. Amount: ₹" . $data['amount_paid']);
            
            // Commit transaction
            $this->db->commit();
            
            return Response::success('Payment recorded successfully');
        } catch (Exception $e) {
            $this->db->rollback();
            return $this->handleDbError($e, 'Failed to record payment');
        }
    }
    
    /**
     * Soft delete loan
     */
    public function delete($loanId)
    {
        try {
            $updated = $this->db->update('loans',
                ['is_deleted' => 1],
                ['loan_id' => $loanId]
            );
            
            if (!$updated) {
                return Response::error('Failed to delete loan', 500);
            }
            
            $this->log('loan_deleted', "Loan #$loanId deleted (soft delete)");
            
            return Response::success('Loan deleted successfully');
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to delete loan');
        }
    }
    
    /**
     * Get loan statistics
     */
    public function getStats($memberId = null)
    {
        try {
            $where = "WHERE is_deleted = 0";
            $params = [];
            
            if ($memberId) {
                $where .= " AND member_id = ?";
                $params[] = $memberId;
            }
            
            $stats = $this->db->selectOne("
                SELECT
                    COUNT(*) as total_loans,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_loans,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_loans,
                    SUM(CASE WHEN status = 'disbursed' THEN 1 ELSE 0 END) as active_loans,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_loans,
                    SUM(loan_amount) as total_disbursed,
                    SUM(CASE WHEN status = 'disbursed' THEN loan_amount ELSE 0 END) as outstanding_amount
                FROM loans $where
            ", $params);
            
            return Response::success('Statistics retrieved', $stats);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to retrieve statistics');
        }
    }
    
    /**
     * Calculate EMI (Equated Monthly Installment)
     * Formula: EMI = P * r * (1 + r)^n / ((1 + r)^n - 1)
     * where P = principal, r = monthly rate, n = number of months
     */
    public function calculateEMI($principal, $annualRate, $months)
    {
        $monthlyRate = $annualRate / 12 / 100;
        
        if ($monthlyRate == 0) {
            return round($principal / $months, 2);
        }
        
        $emi = ($principal * $monthlyRate * pow(1 + $monthlyRate, $months)) 
             / (pow(1 + $monthlyRate, $months) - 1);
        
        return round($emi, 2);
    }
    
    /**
     * Get total amount due for member
     */
    public function getTotalDue($memberId)
    {
        try {
            $result = $this->db->selectValue(
                "SELECT SUM(i.amount_due - i.amount_paid) FROM installments i 
                 INNER JOIN loans l ON i.loan_id = l.loan_id 
                 WHERE l.member_id = ? AND i.status IN ('pending', 'partial')",
                [$memberId]
            );
            
            return Response::success('Total due retrieved', ['amount_due' => $result ?? 0]);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to calculate total due');
        }
    }
}
