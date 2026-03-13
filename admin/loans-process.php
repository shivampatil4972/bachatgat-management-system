<?php
require_once '../config/config.php';

// Require admin access
requireAdmin();

// Set JSON header
header('Content-Type: application/json');

$db = Database::getInstance();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'add':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            // Validate required fields
            $requiredFields = ['member_id', 'loan_amount', 'interest_rate', 'installment_months', 'disbursement_date', 'purpose'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            $memberId = (int)$_POST['member_id'];
            $principalAmount = (float)$_POST['loan_amount'];
            $interestRate = (float)$_POST['interest_rate'];
            $tenureMonths = (int)$_POST['installment_months'];
            $disbursementDate = $_POST['disbursement_date'];
            $purpose = sanitize($_POST['purpose']);
            $notes = !empty($_POST['notes']) ? sanitize($_POST['notes']) : null;
            
            // Validate amounts
            if ($principalAmount <= 0) {
                throw new Exception('Loan amount must be greater than 0');
            }
            
            if ($interestRate < 0) {
                throw new Exception('Interest rate cannot be negative');
            }
            
            if ($tenureMonths <= 0) {
                throw new Exception('Tenure must be at least 1 month');
            }
            
            // Get member
            $member = $db->selectOne(
                "SELECT m.*, u.email, u.full_name FROM members m JOIN users u ON m.user_id = u.user_id WHERE m.member_id = ?",
                [$memberId]
            );
            
            if (!$member) {
                throw new Exception('Member not found');
            }
            
            // Calculate loan details
            $interestAmount = ($principalAmount * $interestRate * $tenureMonths) / (12 * 100);
            $totalAmount = $principalAmount + $interestAmount;
            $emiAmount = $totalAmount / $tenureMonths;
            
            // Generate loan number
            $loanNumber = generateLoanNumber();
            
            // Start transaction
            $db->beginTransaction();
            
            try {
                // Insert loan
                $loanId = $db->insert('loans', [
                    'member_id'          => $memberId,
                    'loan_number'        => $loanNumber,
                    'loan_amount'        => $principalAmount,
                    'interest_rate'      => $interestRate,
                    'total_amount'       => $totalAmount,
                    'installment_months' => $tenureMonths,
                    'monthly_installment'=> $emiAmount,
                    'application_date'   => $disbursementDate,
                    'disbursement_date'  => $disbursementDate,
                    'purpose'            => $purpose,
                    'remarks'            => $notes,
                    'amount_paid'        => 0,
                    'amount_remaining'   => $totalAmount,
                    'approved_by'        => $_SESSION['user_id'],
                    'status'             => 'disbursed'
                ]);
                
                if (!$loanId) {
                    throw new Exception('Failed to create loan');
                }
                
                // Generate installments
                $dueDate = new DateTime($disbursementDate);
                for ($i = 1; $i <= $tenureMonths; $i++) {
                    $dueDate->modify('+1 month');
                    
                    $db->insert('installments', [
                        'loan_id'            => $loanId,
                        'installment_number' => $i,
                        'installment_amount' => $emiAmount,
                        'due_date'           => $dueDate->format('Y-m-d'),
                        'paid_amount'        => 0,
                        'status'             => 'pending',
                        'recorded_by'        => $_SESSION['user_id']
                    ]);
                }
                
                // Create transaction
                $db->insert('transactions', [
                    'member_id'        => $memberId,
                    'transaction_type' => 'loan_disbursement',
                    'amount'           => $principalAmount,
                    'reference_id'     => $loanId,
                    'transaction_date' => $disbursementDate,
                    'description'      => 'Loan disbursed - ' . $loanNumber,
                    'recorded_by'      => $_SESSION['user_id']
                ]);
                
                // Log activity
                logActivity(
                    $member['user_id'],
                    'loan_created',
                    'Loan of ₹' . number_format($principalAmount, 2) . ' approved for ' . $member['full_name']
                );
                
                // Send notification
                sendNotification(
                    $member['user_id'],
                    'Loan Approved',
                    'Your loan of ₹' . number_format($principalAmount, 2) . ' has been approved. Loan Number: ' . $loanNumber,
                    'success'
                );
                
                $db->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Loan created successfully',
                    'loan_id' => $loanId,
                    'loan_number' => $loanNumber
                ]);
                
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            break;
            
        case 'view':
            $loanId = (int)$_GET['id'];
            
            if (!$loanId) {
                throw new Exception('Invalid loan ID');
            }
            
            $loan = $db->selectOne(
                "SELECT l.*, m.member_code, u.full_name
                FROM loans l
                JOIN members m ON l.member_id = m.member_id
                JOIN users u ON m.user_id = u.user_id
                WHERE l.loan_id = ?",
                [$loanId]
            );
            
            if (!$loan) {
                throw new Exception('Loan not found');
            }
            
            $installments = $db->select(
                "SELECT * FROM installments WHERE loan_id = ? ORDER BY installment_number",
                [$loanId]
            );
            
            echo json_encode([
                'success' => true,
                'loan' => $loan,
                'installments' => $installments ?? []
            ]);
            break;
            
        case 'get_payment_info':
            $loanId = (int)$_GET['id'];
            
            if (!$loanId) {
                throw new Exception('Invalid loan ID');
            }
            
            $loan = $db->findById('loans', $loanId);
            if (!$loan) {
                throw new Exception('Loan not found');
            }
            
            // Get next pending installment
            $pendingInstallment = $db->selectOne(
                "SELECT * FROM installments WHERE loan_id = ? AND status = 'pending' ORDER BY installment_number LIMIT 1",
                [$loanId]
            );
            
            echo json_encode([
                'success' => true,
                'loan' => $loan,
                'pending_installment' => $pendingInstallment ?: null
            ]);
            break;
            
        case 'add_payment':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $loanId = (int)$_POST['loan_id'];
            $amount = (float)$_POST['amount'];
            $paymentDate = $_POST['payment_date'];
            $paymentMethod = sanitize($_POST['payment_method']);
            $notes = !empty($_POST['notes']) ? sanitize($_POST['notes']) : null;
            
            if (!$loanId) {
                throw new Exception('Invalid loan ID');
            }
            
            if ($amount <= 0) {
                throw new Exception('Payment amount must be greater than 0');
            }
            
            // Get loan
            $loan = $db->selectOne(
                "SELECT l.*, m.user_id, u.full_name FROM loans l 
                JOIN members m ON l.member_id = m.member_id 
                JOIN users u ON m.user_id = u.user_id 
                WHERE l.loan_id = ?",
                [$loanId]
            );
            
            if (!$loan) {
                throw new Exception('Loan not found');
            }
            
            if ($loan['status'] !== 'disbursed') {
                throw new Exception('Cannot add payment to inactive loan');
            }
            
            $db->beginTransaction();
            
            try {
                $remainingAmount = $amount;
                
                // Get pending installments
                $pendingInstallments = $db->select(
                    "SELECT * FROM installments WHERE loan_id = ? AND status IN ('pending', 'overdue') ORDER BY installment_number",
                    [$loanId]
                ) ?: [];
                
                foreach ($pendingInstallments as $installment) {
                    if ($remainingAmount <= 0) break;
                    
                    $installmentDue = $installment['installment_amount'] - $installment['paid_amount'];
                    $paymentForThis = min($remainingAmount, $installmentDue);
                    
                    $newPaidAmount = $installment['paid_amount'] + $paymentForThis;
                    $newStatus = ($newPaidAmount >= $installment['installment_amount']) ? 'paid' : 'partial';
                    
                    $db->updateById('installments', $installment['installment_id'], [
                        'paid_amount'  => $newPaidAmount,
                        'payment_date' => $paymentDate,
                        'status'       => $newStatus,
                        'recorded_by'  => $_SESSION['user_id']
                    ]);
                    
                    $remainingAmount -= $paymentForThis;
                }
                
                // Update loan paid amount
                $newPaidAmount = $loan['amount_paid'] + $amount;
                $db->query(
                    "UPDATE loans SET amount_paid = ?, amount_remaining = (total_amount - ?) WHERE loan_id = ?",
                    [$newPaidAmount, $newPaidAmount, $loanId]
                );
                
                // Create transaction
                $db->insert('transactions', [
                    'member_id'        => $loan['member_id'],
                    'transaction_type' => 'installment_payment',
                    'amount'           => $amount,
                    'reference_id'     => $loanId,
                    'transaction_date' => $paymentDate,
                    'description'      => 'Loan payment - ' . $loan['loan_number'],
                    'recorded_by'      => $_SESSION['user_id']
                ]);
                
                // Log activity
                logActivity(
                    $loan['user_id'],
                    'loan_payment',
                    'Loan payment of ₹' . number_format($amount, 2) . ' received for loan ' . $loan['loan_number']
                );
                
                // Send notification
                sendNotification(
                    $loan['user_id'],
                    'Loan Payment Received',
                    'Your payment of ₹' . number_format($amount, 2) . ' has been recorded for loan ' . $loan['loan_number'],
                    'success'
                );
                
                $db->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment recorded successfully'
                ]);
                
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            break;
            
        case 'close':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $loanId = (int)$_GET['id'];
            
            if (!$loanId) {
                throw new Exception('Invalid loan ID');
            }
            
            $loan = $db->selectOne(
                "SELECT l.*, m.user_id FROM loans l
                JOIN members m ON l.member_id = m.member_id
                WHERE l.loan_id = ?",
                [$loanId]
            );
            
            if (!$loan) {
                throw new Exception('Loan not found');
            }
            
            $db->beginTransaction();
            
            try {
                // Update loan status
                $db->updateById('loans', $loanId, ['status' => 'completed']);
                
                // Update all pending installments to completed
                $db->query(
                    "UPDATE installments SET status = 'paid', payment_date = CURDATE() WHERE loan_id = ? AND status IN ('pending', 'overdue')",
                    [$loanId]
                );
                
                // Log activity
                logActivity(
                    $loan['user_id'],
                    'loan_closed',
                    "Loan closed - " . $loan['loan_number']
                );
                
                $db->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Loan closed successfully'
                ]);
                
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
