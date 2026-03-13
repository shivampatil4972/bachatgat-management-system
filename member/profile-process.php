<?php
require_once '../config/config.php';

// Require member access
requireMember();

// Set JSON header
header('Content-Type: application/json');

$db = Database::getInstance();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $user = getCurrentUser();
            $member = getCurrentMember();
            
            // Ensure user and member are valid arrays
            if (!is_array($user) || !isset($user['user_id'])) {
                throw new Exception('User not found');
            }
            if (!is_array($member) || !isset($member['member_id'])) {
                throw new Exception('Member not found');
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
            $newProfileImage = null;
            $oldProfileImage = $user['profile_image'] ?? null;
            
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
                [$email, $user['user_id']]
            );
            if ($existingUser) {
                throw new Exception('Email already exists');
            }
            
            // Check if phone exists for other users
            $existingPhone = $db->selectOne(
                "SELECT user_id FROM users WHERE phone = ? AND user_id != ?",
                [$phone, $user['user_id']]
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
                    'phone' => $phone
                ];
                if ($newProfileImage) {
                    $userUpdateData['profile_image'] = $newProfileImage;
                }
                $db->updateById('users', $user['user_id'], $userUpdateData);
                
                // Update member
                $db->updateById('members', $member['member_id'], [
                    'date_of_birth' => $dateOfBirth,
                    'gender' => $gender,
                    'aadhar_number' => $aadharNumber,
                    'address' => $address,
                    'city' => $city,
                    'state' => $state,
                    'pincode' => $pincode
                ]);
                
                // Update session variables
                $_SESSION['full_name'] = $fullName;
                $_SESSION['email'] = $email;
                if ($newProfileImage) {
                    $_SESSION['profile_image'] = $newProfileImage;
                }
                
                // Log activity
                logActivity($user['user_id'], 'profile_updated', 'Profile information updated');
                
                $db->commit();

                // Remove old profile image only after successful commit
                if ($newProfileImage && $oldProfileImage && $oldProfileImage !== 'default-avatar.png' && $oldProfileImage !== $newProfileImage) {
                    deleteFile(UPLOADS_PATH . 'profiles/' . $oldProfileImage);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ]);
                
            } catch (Exception $e) {
                $db->rollback();
                if ($newProfileImage) {
                    deleteFile(UPLOADS_PATH . 'profiles/' . $newProfileImage);
                }
                throw $e;
            }
            break;
            
        case 'change_password':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }
            
            $user = getCurrentUser();
            
            // Ensure user is a valid array
            if (!is_array($user) || !isset($user['user_id'])) {
                throw new Exception('User not found');
            }
            
            // Validate required fields
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                throw new Exception('All password fields are required');
            }
            
            // Verify current password
            $userData = $db->findById('users', $user['user_id']);
            if (!password_verify($currentPassword, $userData['password'])) {
                throw new Exception('Current password is incorrect');
            }
            
            // Validate new password
            if ($newPassword !== $confirmPassword) {
                throw new Exception('New passwords do not match');
            }
            
            if (!validatePassword($newPassword)) {
                throw new Exception('Password must be at least 8 characters with uppercase, lowercase, and number');
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $db->updateById('users', $user['user_id'], [
                'password' => $hashedPassword
            ]);
            
            // Log activity
            logActivity($user['user_id'], 'password_changed', 'Password changed successfully');
            
            // Send notification
            sendNotification(
                $user['user_id'],
                'Password Changed',
                'Your password has been changed successfully.',
                'info'
            );
            
            echo json_encode([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    // Rollback transaction if active
    if ($db->getConnection()->inTransaction()) {
        $db->rollback();
    }
    
    // Log the error for debugging
    error_log("Profile Update Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
