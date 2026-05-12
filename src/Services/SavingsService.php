<?php

namespace Services;

use Exception;
use Response;

/**
 * SavingsService
 * Handle all savings-related business logic
 * 
 * Methods:
 * - getAll()           : Get paginated list of savings
 * - getById()          : Get single savings record
 * - deposit()          : Record savings deposit
 * - withdraw()         : Record savings withdrawal
 * - recordInterest()   : Record interest on savings
 * - getMemberSavings() : Get member's total savings
 * - getStats()         : Get savings statistics
 * - getSavingsHistory(): Get monthly savings trend
 */
class SavingsService extends BaseService
{
    /**
     * Get all savings records with pagination
     */
    public function getAll($page = 1, $limit = 50, $filters = [])
    {
        try {
            $offset = ($page - 1) * $limit;
            
            $where = "WHERE s.is_deleted = 0";
            $params = [];
            
            if (!empty($filters['member_id'])) {
                $where .= " AND s.member_id = ?";
                $params[] = $filters['member_id'];
            }
            
            // Get total count
            $total = $this->db->selectValue(
                "SELECT COUNT(*) FROM savings s $where",
                $params
            );
            
            // Get paginated data
            $query = "
                SELECT 
                    s.savings_id,
                    s.member_id,
                    s.total_savings,
                    s.interest_earned,
                    s.last_deposit_date,
                    m.member_code,
                    u.first_name,
                    u.last_name,
                    u.email
                FROM savings s
                INNER JOIN members m ON s.member_id = m.member_id
                INNER JOIN users u ON m.user_id = u.user_id
                $where
                ORDER BY s.last_deposit_date DESC
                LIMIT ? OFFSET ?
            ";
            
            $savings = $this->db->select($query, [...$params, $limit, $offset]);
            
            return Response::success('Savings retrieved', [
                'data' => $savings,
                'total' => $total,
                'page' => $page,
                'pages' => ceil($total / $limit)
            ]);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to retrieve savings');
        }
    }
    
    /**
     * Get single savings record by ID
     */
    public function getById($savingsId)
    {
        try {
            $savings = $this->db->selectOne(
                "SELECT s.*, m.member_code, u.first_name, u.last_name, u.email
                 FROM savings s
                 INNER JOIN members m ON s.member_id = m.member_id
                 INNER JOIN users u ON m.user_id = u.user_id
                 WHERE s.savings_id = ? AND s.is_deleted = 0",
                [$savingsId]
            );
            
            if (!$savings) {
                return Response::notFound('Savings record not found');
            }
            
            return Response::success('Savings retrieved', $savings);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to retrieve savings');
        }
    }
    
    /**
     * Record savings deposit
     */
    public function deposit($data)
    {
        try {
            // Validate
            $errors = $this->validate($data, [
                'member_id' => 'required|numeric',
                'amount' => 'required|numeric|min:0.01',
                'deposit_date' => 'date_format:Y-m-d'
            ]);
            
            if (!empty($errors)) {
                return Response::validationError($errors);
            }
            
            $depositDate = $data['deposit_date'] ?? date('Y-m-d');
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Get or create savings record
            $savings = $this->db->selectOne(
                "SELECT * FROM savings WHERE member_id = ? AND is_deleted = 0",
                [$data['member_id']]
            );
            
            if (!$savings) {
                // Create new savings record
                $savingsId = $this->db->insert('savings', [
                    'member_id' => $data['member_id'],
                    'total_savings' => $data['amount'],
                    'interest_earned' => 0,
                    'last_deposit_date' => $depositDate,
                    'created_at' => date('Y-m-d H:i:s'),
                    'is_deleted' => 0
                ]);
                
                if (!$savingsId) {
                    throw new Exception('Failed to create savings record');
                }
            } else {
                // Update existing record
                $newTotal = $savings['total_savings'] + $data['amount'];
                $updated = $this->db->update('savings',
                    [
                        'total_savings' => $newTotal,
                        'last_deposit_date' => $depositDate
                    ],
                    ['savings_id' => $savings['savings_id']]
                );
                
                if (!$updated) {
                    throw new Exception('Failed to update savings record');
                }
                
                $savingsId = $savings['savings_id'];
            }
            
            // Record transaction
            $this->db->insert('transactions', [
                'member_id' => $data['member_id'],
                'transaction_type' => 'savings_deposit',
                'amount' => $data['amount'],
                'description' => 'Savings deposit',
                'transaction_date' => date('Y-m-d H:i:s'),
                'status' => 'completed'
            ]);
            
            // Log activity
            $this->log('savings_deposit', "Savings deposit of ₹" . $data['amount'] . " for member ID: " . $data['member_id']);
            
            $this->db->commit();
            
            return Response::success('Deposit recorded successfully', ['savings_id' => $savingsId]);
        } catch (Exception $e) {
            $this->db->rollback();
            return $this->handleDbError($e, 'Failed to record deposit');
        }
    }
    
    /**
     * Record savings withdrawal
     */
    public function withdraw($data)
    {
        try {
            // Validate
            $errors = $this->validate($data, [
                'member_id' => 'required|numeric',
                'amount' => 'required|numeric|min:0.01',
                'withdrawal_date' => 'date_format:Y-m-d'
            ]);
            
            if (!empty($errors)) {
                return Response::validationError($errors);
            }
            
            // Get savings record
            $savings = $this->db->selectOne(
                "SELECT * FROM savings WHERE member_id = ? AND is_deleted = 0",
                [$data['member_id']]
            );
            
            if (!$savings) {
                return Response::error('No savings record found for this member', 400);
            }
            
            // Check sufficient balance
            if ($savings['total_savings'] < $data['amount']) {
                return Response::error('Insufficient savings balance', 400);
            }
            
            $withdrawalDate = $data['withdrawal_date'] ?? date('Y-m-d');
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Update savings
            $newTotal = $savings['total_savings'] - $data['amount'];
            $updated = $this->db->update('savings',
                ['total_savings' => $newTotal],
                ['savings_id' => $savings['savings_id']]
            );
            
            if (!$updated) {
                throw new Exception('Failed to update savings record');
            }
            
            // Record transaction
            $this->db->insert('transactions', [
                'member_id' => $data['member_id'],
                'transaction_type' => 'savings_withdrawal',
                'amount' => $data['amount'],
                'description' => 'Savings withdrawal',
                'transaction_date' => date('Y-m-d H:i:s'),
                'status' => 'completed'
            ]);
            
            // Log activity
            $this->log('savings_withdrawal', "Savings withdrawal of ₹" . $data['amount'] . " for member ID: " . $data['member_id']);
            
            $this->db->commit();
            
            return Response::success('Withdrawal recorded successfully', ['new_balance' => $newTotal]);
        } catch (Exception $e) {
            $this->db->rollback();
            return $this->handleDbError($e, 'Failed to record withdrawal');
        }
    }
    
    /**
     * Record interest on savings
     */
    public function recordInterest($data)
    {
        try {
            // Validate
            $errors = $this->validate($data, [
                'member_id' => 'required|numeric',
                'interest_amount' => 'required|numeric|min:0'
            ]);
            
            if (!empty($errors)) {
                return Response::validationError($errors);
            }
            
            // Get savings record
            $savings = $this->db->selectOne(
                "SELECT * FROM savings WHERE member_id = ? AND is_deleted = 0",
                [$data['member_id']]
            );
            
            if (!$savings) {
                return Response::error('Savings record not found', 404);
            }
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // Update savings with interest
            $newInterest = $savings['interest_earned'] + $data['interest_amount'];
            $newTotal = $savings['total_savings'] + $data['interest_amount'];
            
            $updated = $this->db->update('savings',
                [
                    'total_savings' => $newTotal,
                    'interest_earned' => $newInterest
                ],
                ['savings_id' => $savings['savings_id']]
            );
            
            if (!$updated) {
                throw new Exception('Failed to update interest');
            }
            
            // Record transaction
            $this->db->insert('transactions', [
                'member_id' => $data['member_id'],
                'transaction_type' => 'interest_credit',
                'amount' => $data['interest_amount'],
                'description' => 'Interest credited on savings',
                'transaction_date' => date('Y-m-d H:i:s'),
                'status' => 'completed'
            ]);
            
            // Log activity
            $this->log('interest_credited', "Interest of ₹" . $data['interest_amount'] . " credited to member ID: " . $data['member_id']);
            
            $this->db->commit();
            
            return Response::success('Interest recorded successfully', ['new_total' => $newTotal]);
        } catch (Exception $e) {
            $this->db->rollback();
            return $this->handleDbError($e, 'Failed to record interest');
        }
    }
    
    /**
     * Get member's total savings
     */
    public function getMemberSavings($memberId)
    {
        try {
            $savings = $this->db->selectOne(
                "SELECT * FROM savings WHERE member_id = ? AND is_deleted = 0",
                [$memberId]
            );
            
            if (!$savings) {
                return Response::success('Savings retrieved', [
                    'total_savings' => 0,
                    'interest_earned' => 0,
                    'last_deposit_date' => null
                ]);
            }
            
            return Response::success('Savings retrieved', $savings);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to retrieve member savings');
        }
    }
    
    /**
     * Get savings statistics
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
                    COUNT(*) as active_members,
                    SUM(total_savings) as total_savings,
                    SUM(interest_earned) as total_interest,
                    AVG(total_savings) as average_savings,
                    MIN(total_savings) as min_savings,
                    MAX(total_savings) as max_savings
                FROM savings $where
            ", $params);
            
            return Response::success('Statistics retrieved', $stats);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to retrieve statistics');
        }
    }
    
    /**
     * Get monthly savings trend
     */
    public function getSavingsHistory($memberId, $months = 12)
    {
        try {
            $query = "
                SELECT
                    DATE_TRUNC(transaction_date, 'month') as month,
                    SUM(CASE WHEN transaction_type = 'savings_deposit' THEN amount ELSE 0 END) as deposits,
                    SUM(CASE WHEN transaction_type = 'savings_withdrawal' THEN amount ELSE 0 END) as withdrawals,
                    SUM(CASE WHEN transaction_type = 'interest_credit' THEN amount ELSE 0 END) as interest
                FROM transactions
                WHERE member_id = ? AND transaction_date >= DATE_SUB(NOW(), INTERVAL ? MONTH)
                GROUP BY DATE_TRUNC(transaction_date, 'month')
                ORDER BY month DESC
            ";
            
            $history = $this->db->select($query, [$memberId, $months]);
            
            return Response::success('History retrieved', $history);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to retrieve history');
        }
    }
}
