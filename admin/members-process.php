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
            $requiredFields = ['full_name', 'email', 'phone', 'password'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            $fullName = sanitize($_POST['full_name']);
            $email = sanitize($_POST['email']);
            $phone = sanitize($_POST['phone']);
            $password = $_POST['password'];
            $dateOfBirth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
            $gender = !empty($_POST['gender']) ? sanitize($_POST['gender']) : null;
            $aadharNumber = !empty($_POST['aadhar_number']) ? sanitize($_POST['aadhar_number']) : null;
            $address = !empty($_POST['address']) ? sanitize($_POST['address']) : null;
            $city = !empty($_POST['city']) ? sanitize($_POST['city']) : null;
            $state = !empty($_POST['state']) ? sanitize($_POST['state']) : null;
            $pincode = !empty($_POST['pincode']) ? sanitize($_POST['pincode']) : null;
            $newProfileImage = null;
            
            // Validate email
            if (!validateEmail($email)) {
                throw new Exception('Invalid email format');
            }
            
            // Validate phone
            if (!validatePhone($phone)) {
                throw new Exception('Invalid phone number. Must be 10 digits starting with 6-9');
            }
            
            // Validate password
            if (!validatePassword($password)) {
                throw new Exception('Password must be at least 8 characters with uppercase, lowercase, and number');
            }
            
            // Check if email exists
            if ($db->selectOne("SELECT user_id FROM users WHERE email = ?", [$email])) {
                throw new Exception('Email already exists');
            }
            
            // Check if phone exists
            if ($db->selectOne("SELECT user_id FROM users WHERE phone = ?", [$phone])) {
                throw new Exception('Phone number already exists');
            }
            
            // Validate Aadhar if provided
            if ($aadharNumber && !preg_match('/^[0-9]{12}$/', $aadharNumber)) {
                throw new Exception('Invalid Aadhar number. Must be 12 digits');
            }
            
            // Validate Pincode if provided
            if ($pincode && !preg_match('/^[0-9]{6}$/', $pincode)) {
                throw new Exception('Invalid pincode. Must be 6 digits');
            }

            // Upload profile image if provided
            if (isset($_FILES['profile_image']) && ($_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = uploadFile($_FILES['profile_image'], UPLOADS_PATH . 'profiles/', ALLOWED_IMAGE_TYPES);
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message'] ?? 'Failed to upload profile picture');
                }
                $newProfileImage = $uploadResult['filename'];
            }
            
            // Start transaction
            $db->beginTransaction();
            
            try {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Generate member code
                $memberCode = generateMemberCode();
                
                // Insert user
                $userId = $db->insert('users', [
                    'email' => $email,
                    'password' => $hashedPassword,
                    'full_name' => $fullName,
                    'phone' => $phone,
                    'role' => 'member',
                    'profile_image' => $newProfileImage ?: 'default-avatar.png',
                    'status' => 'active'
                ]);
                
                if (!$userId) {
                    throw new Exception('Failed to create user account');
                }
                
                // Insert member
                $memberId = $db->insert('members', [
                    'user_id'      => $userId,
                    'member_code'  => $memberCode,
                    'date_of_birth'=> $dateOfBirth,
                    'gender'       => $gender,
                    'aadhar_number'=> $aadharNumber,
                    'address'      => $address,
                    'city'         => $city,
                    'state'        => $state,
                    'pincode'      => $pincode,
                    'joining_date' => date('Y-m-d'),
                    'status'       => 'active'
                ]);
                
                if (!$memberId) {
                    throw new Exception('Failed to create member profile');
                }
                
                // Log activity
                logActivity($userId, 'member_created', "New member added: $fullName ($memberCode)");
                
                // Send notification
                sendNotification(
                    $userId,
                    'Welcome to Bachat Gat!',
                    "Your account has been created successfully. Your member code is: $memberCode",
                    'success'
                );
                
                $db->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Member added successfully',
                    'member_id' => $memberId,
                    'member_code' => $memberCode
                ]);
                
            } catch (Exception $e) {
                $db->rollback();
                if ($newProfileImage) {
                    deleteFile(UPLOADS_PATH . 'profiles/' . $newProfileImage);
                }
                throw $e;
            }
            break;
            
        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $memberId = (int)$_POST['member_id'];
            $userId = (int)$_POST['user_id'];
            
            if (!$memberId || !$userId) {
                throw new Exception('Invalid member ID');
            }
            
            // Validate required fields
            $requiredFields = ['full_name', 'email', 'phone'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }
            
            $fullName = sanitize($_POST['full_name']);
            $email = sanitize($_POST['email']);
            $phone = sanitize($_POST['phone']);
            $dateOfBirth = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
            $gender = !empty($_POST['gender']) ? sanitize($_POST['gender']) : null;
            $aadharNumber = !empty($_POST['aadhar_number']) ? sanitize($_POST['aadhar_number']) : null;
            $address = !empty($_POST['address']) ? sanitize($_POST['address']) : null;
            $city = !empty($_POST['city']) ? sanitize($_POST['city']) : null;
            $state = !empty($_POST['state']) ? sanitize($_POST['state']) : null;
            $pincode = !empty($_POST['pincode']) ? sanitize($_POST['pincode']) : null;
            $status = sanitize($_POST['status']);
            $newProfileImage = null;
            $oldProfileImage = null;
            
            // Validate email
            if (!validateEmail($email)) {
                throw new Exception('Invalid email format');
            }
            
            // Validate phone
            if (!validatePhone($phone)) {
                throw new Exception('Invalid phone number. Must be 10 digits starting with 6-9');
            }
            
            // Check if email exists for other users
            $existingUser = $db->selectOne(
                "SELECT user_id FROM users WHERE email = ? AND user_id != ?",
                [$email, $userId]
            );
            if ($existingUser) {
                throw new Exception('Email already exists');
            }
            
            // Check if phone exists for other users
            $existingPhone = $db->selectOne(
                "SELECT user_id FROM users WHERE phone = ? AND user_id != ?",
                [$phone, $userId]
            );
            if ($existingPhone) {
                throw new Exception('Phone number already exists');
            }
            
            // Validate Aadhar if provided
            if ($aadharNumber && !preg_match('/^[0-9]{12}$/', $aadharNumber)) {
                throw new Exception('Invalid Aadhar number. Must be 12 digits');
            }
            
            // Validate Pincode if provided
            if ($pincode && !preg_match('/^[0-9]{6}$/', $pincode)) {
                throw new Exception('Invalid pincode. Must be 6 digits');
            }

            // Fetch existing user image
            $existingUserData = $db->findById('users', $userId);
            if (!$existingUserData) {
                throw new Exception('User not found');
            }
            $oldProfileImage = $existingUserData['profile_image'] ?? null;

            // Upload profile image if provided
            if (isset($_FILES['profile_image']) && ($_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = uploadFile($_FILES['profile_image'], UPLOADS_PATH . 'profiles/', ALLOWED_IMAGE_TYPES);
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message'] ?? 'Failed to upload profile picture');
                }
                $newProfileImage = $uploadResult['filename'];
            }
            
            // Start transaction
            $db->beginTransaction();
            
            try {
                // Update user
                $userUpdateData = [
                    'email' => $email,
                    'full_name' => $fullName,
                    'phone' => $phone,
                    'status' => $status
                ];
                if ($newProfileImage) {
                    $userUpdateData['profile_image'] = $newProfileImage;
                }
                $userUpdated = $db->updateById('users', $userId, $userUpdateData);
                
                // Update member
                $memberUpdated = $db->updateById('members', $memberId, [
                    'date_of_birth' => $dateOfBirth,
                    'gender' => $gender,
                    'aadhar_number' => $aadharNumber,
                    'address' => $address,
                    'city' => $city,
                    'state' => $state,
                    'pincode' => $pincode,
                    'status' => $status
                ]);
                
                // Log activity
                logActivity($userId, 'member_updated', "Member profile updated: $fullName");
                
                $db->commit();

                // Remove old profile image only after successful commit
                if ($newProfileImage && $oldProfileImage && $oldProfileImage !== 'default-avatar.png' && $oldProfileImage !== $newProfileImage) {
                    deleteFile(UPLOADS_PATH . 'profiles/' . $oldProfileImage);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Member updated successfully'
                ]);
                
            } catch (Exception $e) {
                $db->rollback();
                if ($newProfileImage) {
                    deleteFile(UPLOADS_PATH . 'profiles/' . $newProfileImage);
                }
                throw $e;
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $memberId = (int)$_GET['id'];
            
            if (!$memberId) {
                throw new Exception('Invalid member ID');
            }
            
            // Get member details
            $member = $db->findById('members', $memberId);
            if (!$member) {
                throw new Exception('Member not found');
            }
            
            // Get user details for logging
            $user = $db->findById('users', $member['user_id']);
            $memberName = $user['full_name'] ?? 'Unknown';
            
            // Check if member has any outstanding loan balance
            $activeLoans = $db->selectOne(
                "SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'disbursed' AND amount_remaining > 0",
                [$memberId]
            );
            if ($activeLoans['count'] > 0) {
                throw new Exception('Cannot delete member with outstanding loan balance. Please close all loans first.');
            }
            
            // Start transaction
            $db->beginTransaction();
            
            try {
                // Delete member (will cascade to user due to FK)
                $db->query("DELETE FROM members WHERE member_id = ?", [$memberId]);
                $db->query("DELETE FROM users WHERE user_id = ?", [$member['user_id']]);
                
                // Log activity
                logActivity(
                    getCurrentUser()['user_id'],
                    'member_deleted',
                    "Member deleted: {$memberName} ({$member['member_code']})"
                );
                
                $db->commit();
                
                // If no members remain, reset AUTO_INCREMENT so IDs restart from 1
                $remainingCount = $db->selectValue("SELECT COUNT(*) FROM members");
                if ($remainingCount == 0) {
                    $db->query("ALTER TABLE members AUTO_INCREMENT = 1");
                    // Also reset users AUTO_INCREMENT to next after highest remaining user (admins)
                    $maxUserId = $db->selectValue("SELECT COALESCE(MAX(user_id), 0) FROM users");
                    $db->query("ALTER TABLE users AUTO_INCREMENT = " . ($maxUserId + 1));
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Member deleted successfully'
                ]);
                
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
            break;
            
        case 'view':
            $memberId = (int)$_GET['id'];
            
            if (!$memberId) {
                throw new Exception('Invalid member ID');
            }
            
            $member = $db->selectOne(
                "SELECT 
                    m.*,
                    u.full_name,
                    u.email,
                    u.phone,
                    u.profile_image,
                    u.status,
                    u.created_at,
                    (SELECT COUNT(*) FROM savings WHERE member_id = m.member_id) AS savings_count,
                    (SELECT COUNT(*) FROM loans WHERE member_id = m.member_id AND status = 'disbursed') AS loans_count
                FROM members m
                JOIN users u ON m.user_id = u.user_id
                WHERE m.member_id = ?",
                [$memberId]
            );
            
            if (!$member) {
                throw new Exception('Member not found');
            }
            
            echo json_encode([
                'success' => true,
                'member' => $member
            ]);
            break;
            
        case 'get':
            $memberId = (int)$_GET['id'];
            
            if (!$memberId) {
                throw new Exception('Invalid member ID');
            }
            
            $member = $db->selectOne(
                "SELECT m.*, u.full_name, u.email, u.phone, u.profile_image
                FROM members m
                JOIN users u ON m.user_id = u.user_id
                WHERE m.member_id = ?",
                [$memberId]
            );
            
            if (!$member) {
                throw new Exception('Member not found');
            }
            
            echo json_encode([
                'success' => true,
                'member' => $member
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
