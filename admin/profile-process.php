<?php
require_once '../config/config.php';

// Require admin access
requireAdmin();

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
            if (!is_array($user) || !isset($user['user_id'])) {
                throw new Exception('User not found');
            }

            $requiredFields = ['full_name', 'email', 'phone'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
                }
            }

            $fullName = sanitize($_POST['full_name']);
            $email = sanitize($_POST['email']);
            $phone = sanitize($_POST['phone']);
            $newProfileImage = null;
            $oldProfileImage = $user['profile_image'] ?? null;

            if (!validateEmail($email)) {
                throw new Exception('Invalid email format');
            }

            if (!validatePhone($phone)) {
                throw new Exception('Invalid phone number. Must be 10 digits starting with 6-9');
            }

            $existingUser = $db->selectOne(
                'SELECT user_id FROM users WHERE email = ? AND user_id != ?',
                [$email, $user['user_id']]
            );
            if ($existingUser) {
                throw new Exception('Email already exists');
            }

            $existingPhone = $db->selectOne(
                'SELECT user_id FROM users WHERE phone = ? AND user_id != ?',
                [$phone, $user['user_id']]
            );
            if ($existingPhone) {
                throw new Exception('Phone number already exists');
            }

            if (isset($_FILES['profile_image']) && ($_FILES['profile_image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = uploadFile($_FILES['profile_image'], UPLOADS_PATH . 'profiles/', ALLOWED_IMAGE_TYPES);
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message'] ?? 'Failed to upload profile picture');
                }
                $newProfileImage = $uploadResult['filename'];
            }

            $db->beginTransaction();

            try {
                $userUpdateData = [
                    'email' => $email,
                    'full_name' => $fullName,
                    'phone' => $phone
                ];

                if ($newProfileImage) {
                    $userUpdateData['profile_image'] = $newProfileImage;
                }

                $db->updateById('users', $user['user_id'], $userUpdateData);

                $_SESSION['full_name'] = $fullName;
                $_SESSION['email'] = $email;
                if ($newProfileImage) {
                    $_SESSION['profile_image'] = $newProfileImage;
                }

                logActivity($user['user_id'], 'profile_updated', 'Admin profile information updated');

                $db->commit();

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
            if (!is_array($user) || !isset($user['user_id'])) {
                throw new Exception('User not found');
            }

            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                throw new Exception('All password fields are required');
            }

            $userData = $db->findById('users', $user['user_id']);
            if (!password_verify($currentPassword, $userData['password'])) {
                throw new Exception('Current password is incorrect');
            }

            if ($newPassword !== $confirmPassword) {
                throw new Exception('New passwords do not match');
            }

            $passwordValidation = validatePassword($newPassword);
            if (!$passwordValidation['valid']) {
                throw new Exception($passwordValidation['message']);
            }

            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            $db->updateById('users', $user['user_id'], [
                'password' => $hashedPassword
            ]);

            logActivity($user['user_id'], 'password_changed', 'Admin password changed successfully');

            echo json_encode([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    if ($db->getConnection()->inTransaction()) {
        $db->rollback();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
