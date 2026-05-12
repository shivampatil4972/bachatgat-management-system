<?php
/**
 * Member Service
 * Handles all member-related business logic
 */
class MemberService extends BaseService {
    
    /**
     * Get all members with pagination
     */
    public function getAll($page = 1, $limit = 50, $filters = []) {
        try {
            $offset = ($page - 1) * $limit;
            
            // Build query with filters
            $where = "WHERE m.status = 'active'";
            $params = [];
            
            if (!empty($filters['search'])) {
                $where .= " AND (u.full_name LIKE ? OR u.email LIKE ?)";
                $search = '%' . $filters['search'] . '%';
                $params = [$search, $search];
            }
            
            // Get total count
            $total = $this->db->selectValue(
                "SELECT COUNT(*) FROM members m $where",
                $params
            );
            
            // Get paginated data
            $query = "
                SELECT 
                    m.member_id,
                    m.member_code,
                    m.joining_date,
                    m.total_savings,
                    m.status,
                    u.user_id,
                    u.full_name,
                    u.email,
                    u.phone,
                    (SELECT COUNT(*) FROM loans WHERE member_id = m.member_id AND status = 'disbursed') as active_loans
                FROM members m
                INNER JOIN users u ON m.user_id = u.user_id
                $where
                ORDER BY m.joining_date DESC
                LIMIT ? OFFSET ?
            ";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $members = $this->db->select($query, $params);
            
            return [
                'data' => $members,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ];
            
        } catch (Exception $e) {
            $this->handleDbError($e, ['method' => 'getAll']);
        }
    }
    
    /**
     * Get single member by ID
     */
    public function getById($memberId) {
        try {
            return $this->db->selectOne(
                "SELECT m.*, u.* FROM members m 
                 JOIN users u ON m.user_id = u.user_id 
                 WHERE m.member_id = ?",
                [$memberId]
            );
        } catch (Exception $e) {
            $this->handleDbError($e, ['member_id' => $memberId]);
        }
    }
    
    /**
     * Create new member
     */
    public function create($data) {
        try {
            // Validate
            $rules = [
                'full_name' => 'required|min:3',
                'email' => 'required|email',
                'phone' => 'required',
                'password' => 'required|min:6'
            ];
            
            $errors = $this->validate($data, $rules);
            if ($errors) {
                return Response::validationError($errors);
            }
            
            // Check duplicate email
            if ($this->db->selectOne("SELECT user_id FROM users WHERE email = ?", [$data['email']])) {
                return Response::error('Email already registered', 409);
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                // Create user
                $userId = $this->db->insert('users', [
                    'email' => $data['email'],
                    'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
                    'full_name' => $data['full_name'],
                    'phone' => $data['phone'],
                    'role' => 'member',
                    'status' => 'active'
                ]);
                
                // Generate member code
                $memberCode = $this->generateMemberCode();
                
                // Create member
                $memberId = $this->db->insert('members', [
                    'user_id' => $userId,
                    'member_code' => $memberCode,
                    'aadhar_number' => $data['aadhar_number'] ?? null,
                    'address' => $data['address'] ?? null,
                    'city' => $data['city'] ?? null,
                    'state' => $data['state'] ?? null,
                    'pincode' => $data['pincode'] ?? null,
                    'joining_date' => date('Y-m-d'),
                    'total_savings' => 0,
                    'status' => 'active'
                ]);
                
                $this->db->commit();
                
                $this->log('Member created', [
                    'member_id' => $memberId,
                    'email' => $data['email']
                ]);
                
                return Response::success('Member created successfully', [
                    'member_id' => $memberId,
                    'member_code' => $memberCode,
                    'user_id' => $userId
                ], 201);
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            $this->handleDbError($e, ['data' => $data]);
        }
    }
    
    /**
     * Update member
     */
    public function update($memberId, $data) {
        try {
            $member = $this->getById($memberId);
            if (!$member) {
                return Response::notFound('Member not found');
            }
            
            $this->db->beginTransaction();
            
            try {
                // Update member details
                if (!empty($data['aadhar_number']) || !empty($data['address'])) {
                    $this->db->update(
                        "UPDATE members SET aadhar_number = ?, address = ?, city = ?, state = ?, pincode = ? WHERE member_id = ?",
                        [$data['aadhar_number'] ?? null, $data['address'] ?? null, $data['city'] ?? null, $data['state'] ?? null, $data['pincode'] ?? null, $memberId]
                    );
                }
                
                // Update user details if provided
                if (!empty($data['full_name']) || !empty($data['phone'])) {
                    $this->db->update(
                        "UPDATE users SET full_name = ?, phone = ? WHERE user_id = ?",
                        [$data['full_name'] ?? $member['full_name'], $data['phone'] ?? $member['phone'], $member['user_id']]
                    );
                }
                
                $this->db->commit();
                
                $this->log('Member updated', ['member_id' => $memberId]);
                
                return Response::success('Member updated successfully');
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            $this->handleDbError($e, ['member_id' => $memberId]);
        }
    }
    
    /**
     * Delete member (soft delete)
     */
    public function delete($memberId) {
        try {
            $member = $this->getById($memberId);
            if (!$member) {
                return Response::notFound('Member not found');
            }
            
            $this->db->update(
                "UPDATE members SET status = 'inactive' WHERE member_id = ?",
                [$memberId]
            );
            
            $this->log('Member deleted', ['member_id' => $memberId]);
            
            return Response::success('Member deleted successfully');
            
        } catch (Exception $e) {
            $this->handleDbError($e, ['member_id' => $memberId]);
        }
    }
    
    /**
     * Get member statistics
     */
    public function getStats($memberId) {
        try {
            $stats = $this->db->selectOne(
                "SELECT 
                    m.total_savings,
                    COUNT(DISTINCT s.saving_id) as total_deposits,
                    COUNT(DISTINCT l.loan_id) as total_loans,
                    SUM(CASE WHEN l.status = 'disbursed' THEN 1 ELSE 0 END) as active_loans,
                    (SELECT SUM(installment_amount) FROM installments WHERE loan_id IN (SELECT loan_id FROM loans WHERE member_id = m.member_id AND status = 'disbursed') AND status = 'pending') as pending_emi
                FROM members m
                LEFT JOIN savings s ON m.member_id = s.member_id
                LEFT JOIN loans l ON m.member_id = l.member_id
                WHERE m.member_id = ?
                GROUP BY m.member_id",
                [$memberId]
            );
            
            return Response::success('Member statistics retrieved', $stats);
            
        } catch (Exception $e) {
            $this->handleDbError($e, ['member_id' => $memberId]);
        }
    }
    
    /**
     * Generate unique member code
     */
    private function generateMemberCode() {
        $lastMember = $this->db->selectOne(
            "SELECT member_code FROM members ORDER BY member_id DESC LIMIT 1"
        );
        
        if (!$lastMember) {
            return 'MEM001';
        }
        
        $lastCode = $lastMember['member_code'];
        $number = (int)substr($lastCode, 3) + 1;
        
        return 'MEM' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
?>
