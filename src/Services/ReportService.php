<?php

namespace Services;

use Exception;
use Response;

/**
 * ReportService
 * Handle all report generation and data analysis
 * 
 * Methods:
 * - getSavingsReport()       : Generate savings report
 * - getLoanReport()          : Generate loan report
 * - getMemberReport()        : Generate member report
 * - getFinancialSummary()    : Get overall financial summary
 * - getTransactionHistory()  : Get transaction history
 * - getMemberPerformance()   : Get member performance metrics
 * - getDefaultersList()      : Get list of defaulters
 * - getMonthlyReport()       : Get monthly aggregated report
 */
class ReportService extends BaseService
{
    /**
     * Get savings report
     */
    public function getSavingsReport($startDate = null, $endDate = null)
    {
        try {
            $startDate = $startDate ?? date('Y-01-01');
            $endDate = $endDate ?? date('Y-m-d');
            
            $query = "
                SELECT
                    COUNT(DISTINCT s.member_id) as active_savers,
                    SUM(s.total_savings) as total_savings,
                    SUM(s.interest_earned) as total_interest_earned,
                    AVG(s.total_savings) as average_savings_per_member,
                    MIN(s.total_savings) as min_savings,
                    MAX(s.total_savings) as max_savings,
                    (SELECT SUM(amount) FROM transactions 
                     WHERE transaction_type = 'savings_deposit' 
                     AND transaction_date BETWEEN ? AND ?) as total_deposits,
                    (SELECT SUM(amount) FROM transactions 
                     WHERE transaction_type = 'savings_withdrawal' 
                     AND transaction_date BETWEEN ? AND ?) as total_withdrawals
                FROM savings
                WHERE is_deleted = 0
            ";
            
            $report = $this->db->selectOne($query, [
                $startDate,
                $endDate,
                $startDate,
                $endDate
            ]);
            
            return Response::success('Savings report generated', $report);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to generate savings report');
        }
    }
    
    /**
     * Get loan report
     */
    public function getLoanReport($startDate = null, $endDate = null)
    {
        try {
            $startDate = $startDate ?? date('Y-01-01');
            $endDate = $endDate ?? date('Y-m-d');
            
            $query = "
                SELECT
                    COUNT(*) as total_loans,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_loans,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_loans,
                    SUM(CASE WHEN status = 'disbursed' THEN 1 ELSE 0 END) as active_loans,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_loans,
                    SUM(loan_amount) as total_disbursed,
                    SUM(CASE WHEN status = 'disbursed' THEN loan_amount ELSE 0 END) as outstanding_principal,
                    SUM(interest_rate * loan_amount / 100 / 12 * tenure_months) as estimated_interest,
                    AVG(loan_amount) as average_loan_amount,
                    AVG(tenure_months) as average_tenure
                FROM loans
                WHERE is_deleted = 0 AND applied_date BETWEEN ? AND ?
            ";
            
            $report = $this->db->selectOne($query, [$startDate, $endDate]);
            
            // Get payment recovery rate
            $recoveryQuery = "
                SELECT
                    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_installments,
                    COUNT(*) as total_installments
                FROM installments
                WHERE created_at BETWEEN ? AND ?
            ";
            
            $recovery = $this->db->selectOne($recoveryQuery, [$startDate, $endDate]);
            
            if ($recovery && $recovery['total_installments'] > 0) {
                $report['payment_recovery_rate'] = round(
                    ($recovery['paid_installments'] / $recovery['total_installments']) * 100,
                    2
                );
            }
            
            return Response::success('Loan report generated', $report);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to generate loan report');
        }
    }
    
    /**
     * Get member report
     */
    public function getMemberReport($startDate = null, $endDate = null)
    {
        try {
            $startDate = $startDate ?? date('Y-01-01');
            $endDate = $endDate ?? date('Y-m-d');
            
            $query = "
                SELECT
                    COUNT(*) as total_members,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_members,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_members,
                    COUNT(DISTINCT m.member_id) as new_members_this_period
                FROM members m
                WHERE m.created_at BETWEEN ? AND ?
            ";
            
            $report = $this->db->selectOne($query, [$startDate, $endDate]);
            
            // Add member engagement metrics
            $engagementQuery = "
                SELECT
                    COUNT(DISTINCT member_id) as members_with_savings,
                    COUNT(DISTINCT member_id) as members_with_loans
                FROM (
                    SELECT member_id FROM savings WHERE total_savings > 0
                    UNION
                    SELECT member_id FROM loans WHERE status IN ('approved', 'disbursed', 'completed')
                ) as engaged_members
            ";
            
            $engagement = $this->db->selectOne($engagementQuery);
            $report['members_engaged'] = $engagement['members_with_savings'] ?? 0;
            
            return Response::success('Member report generated', $report);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to generate member report');
        }
    }
    
    /**
     * Get overall financial summary
     */
    public function getFinancialSummary()
    {
        try {
            $summary = $this->db->selectOne("
                SELECT
                    (SELECT SUM(total_savings) FROM savings WHERE is_deleted = 0) as total_savings,
                    (SELECT SUM(interest_earned) FROM savings WHERE is_deleted = 0) as total_interest_earned,
                    (SELECT SUM(loan_amount) FROM loans WHERE is_deleted = 0) as total_loans_issued,
                    (SELECT SUM(CASE WHEN status = 'disbursed' THEN loan_amount ELSE 0 END) FROM loans WHERE is_deleted = 0) as outstanding_loans,
                    (SELECT COUNT(*) FROM members WHERE status = 'active' AND is_deleted = 0) as active_members,
                    (SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active') as admin_count
            ");
            
            return Response::success('Financial summary retrieved', $summary);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to retrieve financial summary');
        }
    }
    
    /**
     * Get transaction history
     */
    public function getTransactionHistory($memberId = null, $limit = 100)
    {
        try {
            $where = "WHERE 1=1";
            $params = [];
            
            if ($memberId) {
                $where .= " AND member_id = ?";
                $params[] = $memberId;
            }
            
            $query = "
                SELECT
                    transaction_id,
                    member_id,
                    transaction_type,
                    amount,
                    description,
                    transaction_date,
                    status
                FROM transactions
                $where
                ORDER BY transaction_date DESC
                LIMIT ?
            ";
            
            $params[] = $limit;
            
            $transactions = $this->db->select($query, $params);
            
            return Response::success('Transaction history retrieved', $transactions);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to retrieve transaction history');
        }
    }
    
    /**
     * Get member performance metrics
     */
    public function getMemberPerformance($memberId)
    {
        try {
            $performance = $this->db->selectOne("
                SELECT
                    m.member_code,
                    u.first_name,
                    u.last_name,
                    u.email,
                    (SELECT SUM(total_savings) FROM savings WHERE member_id = m.member_id) as total_savings,
                    (SELECT SUM(loan_amount) FROM loans WHERE member_id = m.member_id AND status = 'disbursed') as total_loans,
                    (SELECT COUNT(*) FROM installments i 
                     INNER JOIN loans l ON i.loan_id = l.loan_id 
                     WHERE l.member_id = m.member_id AND i.status = 'paid') as payments_made,
                    (SELECT COUNT(*) FROM installments i 
                     INNER JOIN loans l ON i.loan_id = l.loan_id 
                     WHERE l.member_id = m.member_id AND i.status IN ('pending', 'partial')) as pending_installments,
                    (SELECT COUNT(*) FROM transactions WHERE member_id = m.member_id) as transaction_count
                FROM members m
                INNER JOIN users u ON m.user_id = u.user_id
                WHERE m.member_id = ?
            ", [$memberId]);
            
            if (!$performance) {
                return Response::notFound('Member not found');
            }
            
            // Calculate payment compliance rate
            $totalInstallments = $performance['payments_made'] + $performance['pending_installments'];
            if ($totalInstallments > 0) {
                $performance['payment_compliance_rate'] = round(
                    ($performance['payments_made'] / $totalInstallments) * 100,
                    2
                );
            }
            
            return Response::success('Member performance retrieved', $performance);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to retrieve member performance');
        }
    }
    
    /**
     * Get list of defaulters (members with pending installments)
     */
    public function getDefaultersList($limit = 50)
    {
        try {
            $query = "
                SELECT
                    m.member_code,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
                    COUNT(DISTINCT i.installment_id) as pending_installments,
                    SUM(i.amount_due - i.amount_paid) as total_due,
                    MAX(i.due_date) as last_due_date
                FROM members m
                INNER JOIN users u ON m.user_id = u.user_id
                INNER JOIN loans l ON m.member_id = l.member_id
                INNER JOIN installments i ON l.loan_id = i.loan_id
                WHERE i.status IN ('pending', 'partial') AND i.due_date < NOW()
                GROUP BY m.member_id
                ORDER BY total_due DESC
                LIMIT ?
            ";
            
            $defaulters = $this->db->select($query, [$limit]);
            
            return Response::success('Defaulters list retrieved', $defaulters);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to retrieve defaulters list');
        }
    }
    
    /**
     * Get monthly aggregated report
     */
    public function getMonthlyReport($year = null, $month = null)
    {
        try {
            $year = $year ?? date('Y');
            $month = $month ?? date('m');
            
            $startDate = "$year-$month-01";
            $endDate = date('Y-m-d', strtotime("$startDate +1 month -1 day"));
            
            $report = [
                'period' => "$year-$month",
                'start_date' => $startDate,
                'end_date' => $endDate,
                'savings' => [],
                'loans' => [],
                'transactions' => []
            ];
            
            // Daily savings data
            $report['savings'] = $this->db->select("
                SELECT
                    DATE(transaction_date) as date,
                    SUM(CASE WHEN transaction_type = 'savings_deposit' THEN amount ELSE 0 END) as deposits,
                    SUM(CASE WHEN transaction_type = 'savings_withdrawal' THEN amount ELSE 0 END) as withdrawals
                FROM transactions
                WHERE transaction_date BETWEEN ? AND ? AND transaction_type IN ('savings_deposit', 'savings_withdrawal')
                GROUP BY DATE(transaction_date)
                ORDER BY date ASC
            ", [$startDate, $endDate]);
            
            // Daily loan data
            $report['loans'] = $this->db->select("
                SELECT
                    DATE(disbursed_date) as date,
                    COUNT(*) as loans_disbursed,
                    SUM(loan_amount) as amount_disbursed
                FROM loans
                WHERE disbursed_date BETWEEN ? AND ?
                GROUP BY DATE(disbursed_date)
                ORDER BY date ASC
            ", [$startDate, $endDate]);
            
            // Summary
            $report['summary'] = $this->db->selectOne("
                SELECT
                    SUM(CASE WHEN transaction_type = 'savings_deposit' THEN amount ELSE 0 END) as total_deposits,
                    SUM(CASE WHEN transaction_type = 'savings_withdrawal' THEN amount ELSE 0 END) as total_withdrawals,
                    SUM(CASE WHEN transaction_type = 'loan_payment' THEN amount ELSE 0 END) as total_loan_payments
                FROM transactions
                WHERE transaction_date BETWEEN ? AND ?
            ", [$startDate, $endDate]);
            
            return Response::success('Monthly report generated', $report);
        } catch (Exception $e) {
            return $this->handleDbError($e, 'Failed to generate monthly report');
        }
    }
}
