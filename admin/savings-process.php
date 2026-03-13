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
            $requiredFields = ['member_id', 'amount', 'deposit_date', 'transaction_type', 'transaction_mode'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            $memberId       = (int)$_POST['member_id'];
            $amount         = (float)$_POST['amount'];
            $depositDate    = $_POST['deposit_date'];
            $transactionType = sanitize($_POST['transaction_type']);
            $transactionMode = sanitize($_POST['transaction_mode']);
            $remarks        = !empty($_POST['remarks']) ? sanitize($_POST['remarks']) : null;
            $month          = date('Y-m', strtotime($depositDate));
            
            // Validate amount
            if ($amount <= 0) {
                throw new Exception('Amount must be greater than 0');
            }
            
            // Validate member exists
            $member = $db->selectOne(
                "SELECT m.*, u.full_name FROM members m JOIN users u ON m.user_id = u.user_id WHERE m.member_id = ?",
                [$memberId]
            );
            if (!$member) {
                throw new Exception('Member not found');
            }
            
            // Validate type
            $validTypes = ['deposit', 'withdrawal'];
            if (!in_array($transactionType, $validTypes)) {
                throw new Exception('Invalid transaction type');
            }
            
            // Validate mode
            $validModes = ['cash', 'online', 'cheque'];
            if (!in_array($transactionMode, $validModes)) {
                throw new Exception('Invalid payment mode');
            }
            
            // Start transaction
            $db->beginTransaction();
            
            try {
                // Insert saving
                $savingId = $db->insert('savings', [
                    'member_id'        => $memberId,
                    'amount'           => $amount,
                    'deposit_date'     => $depositDate,
                    'month'            => $month,
                    'transaction_type' => $transactionType,
                    'transaction_mode' => $transactionMode,
                    'remarks'          => $remarks,
                    'recorded_by'      => $_SESSION['user_id']
                ]);
                
                if (!$savingId) {
                    throw new Exception('Failed to record savings');
                }
                
                // Create transaction record
                $txType = ($transactionType === 'deposit') ? 'saving_deposit' : 'saving_withdrawal';
                $db->insert('transactions', [
                    'member_id'        => $memberId,
                    'transaction_type' => $txType,
                    'amount'           => $amount,
                    'reference_id'     => $savingId,
                    'transaction_date' => $depositDate,
                    'description'      => 'Savings ' . $transactionType . ' via ' . $transactionMode,
                    'recorded_by'      => $_SESSION['user_id']
                ]);
                
                // Log activity
                logActivity(
                    $member['user_id'],
                    'savings_added',
                    "Savings of ₹" . number_format($amount, 2) . " (" . $transactionType . ") added for {$member['full_name']}"
                );
                
                // Send notification
                sendNotification(
                    $member['user_id'],
                    'Savings Recorded',
                    "Your savings of ₹" . number_format($amount, 2) . " has been recorded successfully.",
                    'success'
                );
                
                $db->commit();
                
                echo json_encode([
                    'success'   => true,
                    'message'   => 'Savings recorded successfully',
                    'saving_id' => $savingId
                ]);
                
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            break;
            
        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $savingId = (int)$_POST['saving_id'];
            
            if (!$savingId) {
                throw new Exception('Invalid saving ID');
            }
            
            // Validate required fields
            $requiredFields = ['member_id', 'amount', 'deposit_date', 'transaction_type', 'transaction_mode'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            $memberId        = (int)$_POST['member_id'];
            $amount          = (float)$_POST['amount'];
            $depositDate     = $_POST['deposit_date'];
            $transactionType = sanitize($_POST['transaction_type']);
            $transactionMode = sanitize($_POST['transaction_mode']);
            $remarks         = !empty($_POST['remarks']) ? sanitize($_POST['remarks']) : null;
            $month           = date('Y-m', strtotime($depositDate));
            
            // Validate amount
            if ($amount <= 0) {
                throw new Exception('Amount must be greater than 0');
            }
            
            // Get old saving record
            $oldSaving = $db->findById('savings', $savingId);
            if (!$oldSaving) {
                throw new Exception('Saving record not found');
            }
            
            // Validate member exists
            $member = $db->selectOne(
                "SELECT m.*, u.full_name FROM members m JOIN users u ON m.user_id = u.user_id WHERE m.member_id = ?",
                [$memberId]
            );
            if (!$member) {
                throw new Exception('Member not found');
            }
            
            // Validate type
            $validTypes = ['deposit', 'withdrawal'];
            if (!in_array($transactionType, $validTypes)) {
                throw new Exception('Invalid transaction type');
            }
            
            // Validate mode
            $validModes = ['cash', 'online', 'cheque'];
            if (!in_array($transactionMode, $validModes)) {
                throw new Exception('Invalid payment mode');
            }
            
            // Start transaction
            $db->beginTransaction();
            
            try {
                // Update saving
                $db->updateById('savings', $savingId, [
                    'member_id'        => $memberId,
                    'amount'           => $amount,
                    'deposit_date'     => $depositDate,
                    'month'            => $month,
                    'transaction_type' => $transactionType,
                    'transaction_mode' => $transactionMode,
                    'remarks'          => $remarks
                ]);
                
                // Update associated transaction if it exists
                $txType = ($transactionType === 'deposit') ? 'saving_deposit' : 'saving_withdrawal';
                $db->update(
                    "UPDATE transactions SET amount = ?, transaction_type = ?, transaction_date = ?,
                     description = ?, recorded_by = ? WHERE reference_id = ? AND transaction_type IN ('saving_deposit','saving_withdrawal')",
                    [
                        $amount,
                        $txType,
                        $depositDate,
                        'Savings ' . $transactionType . ' via ' . $transactionMode,
                        $_SESSION['user_id'],
                        $savingId
                    ]
                );
                
                // Log activity
                logActivity(
                    getCurrentUser()['user_id'],
                    'savings_updated',
                    "Savings record updated - Amount: ₹" . number_format($amount, 2)
                );
                
                $db->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Savings updated successfully'
                ]);
                
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $savingId = (int)$_GET['id'];
            
            if (!$savingId) {
                throw new Exception('Invalid saving ID');
            }
            
            // Get saving details
            $saving = $db->findById('savings', $savingId);
            if (!$saving) {
                throw new Exception('Saving record not found');
            }
            
            // Get member details
            $member = $db->selectOne(
                "SELECT m.*, u.full_name FROM members m JOIN users u ON m.user_id = u.user_id WHERE m.member_id = ?",
                [$saving['member_id']]
            );
            
            // Start transaction
            $db->beginTransaction();
            
            try {
                // Delete saving
                $db->query("DELETE FROM savings WHERE saving_id = ?", [$savingId]);
                
                // Delete associated transaction
                $db->query(
                    "DELETE FROM transactions WHERE reference_id = ? AND transaction_type IN ('saving_deposit','saving_withdrawal')",
                    [$savingId]
                );
                
                // Log activity
                logActivity(
                    getCurrentUser()['user_id'],
                    'savings_deleted',
                    "Savings record deleted - Amount: ₹" . number_format($saving['amount'], 2) . " for {$member['full_name']}"
                );
                
                $db->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Savings deleted successfully'
                ]);
                
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            break;
            
        case 'get':
            $savingId = (int)$_GET['id'];
            
            if (!$savingId) {
                throw new Exception('Invalid saving ID');
            }
            
            $saving = $db->findById('savings', $savingId);
            
            if (!$saving) {
                throw new Exception('Saving record not found');
            }
            
            // Get all active members for dropdown
            $members = $db->select("SELECT m.member_id, m.member_code, u.full_name FROM members m JOIN users u ON m.user_id = u.user_id WHERE m.status = 'active' ORDER BY u.full_name");
            
            echo json_encode([
                'success' => true,
                'saving' => $saving,
                'members' => $members
            ]);
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
