<?php
/**
 * Member Class - Object-Oriented Management for Gym Members
 */

require_once __DIR__ . '/../config/Database.php';

class Member {
    private $db;
    private $table = "members";

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Generate unique member code (e.g. GYM-1005)
    public function generateMemberCode() {
        $sql = "SELECT id FROM {$this->table} ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();
        $nextId = $row ? $row['id'] + 1001 : 1001;
        return "GYM-" . $nextId;
    }

    // Validate member input array
    public function validate($data, $id = null) {
        $errors = [];

        if (empty($data['first_name'])) {
            $errors[] = "First name is required.";
        } elseif (!preg_match('/^[A-Za-z\s\'\-]+$/', trim($data['first_name']))) {
            $errors[] = "First name can only contain letters.";
        }
        if (empty($data['last_name'])) {
            $errors[] = "Last name is required.";
        } elseif (!preg_match('/^[A-Za-z\s\'\-]+$/', trim($data['last_name']))) {
            $errors[] = "Last name can only contain letters.";
        }
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Valid email address is required.";
        } else {
            // Check unique email
            $sql = "SELECT id FROM {$this->table} WHERE email = :email";
            if ($id) {
                $sql .= " AND id != :id";
            }
            $stmt = $this->db->prepare($sql);
            $params = [':email' => trim($data['email'])];
            if ($id) $params[':id'] = (int)$id;
            $stmt->execute($params);
            if ($stmt->fetch()) {
                $errors[] = "Email address is already in use by another member.";
            }
        }
        if (empty($data['phone'])) {
            $errors[] = "Phone number is required.";
        } elseif (!preg_match('/^0[0-9]{9}$/', trim($data['phone']))) {
            $errors[] = "Phone number must be a valid Sri Lankan number (e.g. 0772352232).";
        }
        if (empty($data['plan_id'])) {
            $errors[] = "Please select a valid membership plan.";
        }
        // New members can't be registered with a backdated join date
        if ($id === null && !empty($data['join_date']) && $data['join_date'] < date('Y-m-d')) {
            $errors[] = "Registration date cannot be in the past.";
        }

        return $errors;
    }

    // Get all members with Joined Plan & Trainer details
    public function getAll($search = '', $statusFilter = '') {
        $sql = "SELECT m.*, 
                       p.name as plan_name, p.price as plan_price, p.duration_months,
                       t.full_name as trainer_name 
                FROM {$this->table} m
                JOIN plans p ON m.plan_id = p.id
                LEFT JOIN trainers t ON m.trainer_id = t.id
                WHERE 1=1";
        
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (m.first_name LIKE :search 
                        OR m.last_name LIKE :search 
                        OR m.member_code LIKE :search 
                        OR m.email LIKE :search 
                        OR m.phone LIKE :search)";
            $params[':search'] = '%' . trim($search) . '%';
        }

        if (!empty($statusFilter)) {
            $sql .= " AND m.status = :status";
            $params[':status'] = $statusFilter;
        }

        $sql .= " ORDER BY m.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Get single member details by ID
    public function getById($id) {
        $sql = "SELECT m.*, 
                       p.name as plan_name, p.price as plan_price, p.duration_months,
                       t.full_name as trainer_name
                FROM {$this->table} m
                JOIN plans p ON m.plan_id = p.id
                LEFT JOIN trainers t ON m.trainer_id = t.id
                WHERE m.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch();
    }

    // Create a new member
    public function create($data, $photoFileName = 'default_user.png') {
        $member_code = $this->generateMemberCode();
        
        // Auto-calculate expiration date based on plan duration
        $join_date = !empty($data['join_date']) ? $data['join_date'] : date('Y-m-d');
        
        // Fetch plan duration
        $stmtPlan = $this->db->prepare("SELECT duration_months FROM plans WHERE id = :id");
        $stmtPlan->execute([':id' => (int)$data['plan_id']]);
        $plan = $stmtPlan->fetch();
        $durationMonths = $plan ? (int)$plan['duration_months'] : 1;

        $expire_date = date('Y-m-d', strtotime("+$durationMonths months", strtotime($join_date)));

        $sql = "INSERT INTO {$this->table} 
                (member_code, first_name, last_name, email, phone, gender, dob, address, plan_id, trainer_id, join_date, expire_date, status, photo) 
                VALUES 
                (:member_code, :first_name, :last_name, :email, :phone, :gender, :dob, :address, :plan_id, :trainer_id, :join_date, :expire_date, :status, :photo)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':member_code' => $member_code,
            ':first_name'  => trim($data['first_name']),
            ':last_name'   => trim($data['last_name']),
            ':email'       => trim($data['email']),
            ':phone'       => trim($data['phone']),
            ':gender'      => $data['gender'] ?? 'Male',
            ':dob'         => !empty($data['dob']) ? $data['dob'] : null,
            ':address'     => trim($data['address'] ?? ''),
            ':plan_id'     => (int)$data['plan_id'],
            ':trainer_id'  => !empty($data['trainer_id']) ? (int)$data['trainer_id'] : null,
            ':join_date'   => $join_date,
            ':expire_date' => $expire_date,
            ':status'      => $data['status'] ?? 'Active',
            ':photo'       => $photoFileName
        ]);

        return $result ? $this->db->lastInsertId() : false;
    }

    // Update existing member
    public function update($id, $data, $photoFileName = null) {
        // Fetch current member details
        $current = $this->getById($id);
        if (!$current) return false;

        $join_date = !empty($data['join_date']) ? $data['join_date'] : $current['join_date'];
        
        // Recalculate expiration date if plan or join date changed
        $stmtPlan = $this->db->prepare("SELECT duration_months FROM plans WHERE id = :id");
        $stmtPlan->execute([':id' => (int)$data['plan_id']]);
        $plan = $stmtPlan->fetch();
        $durationMonths = $plan ? (int)$plan['duration_months'] : 1;

        $expire_date = date('Y-m-d', strtotime("+$durationMonths months", strtotime($join_date)));
        $photo = $photoFileName ? $photoFileName : $current['photo'];

        $sql = "UPDATE {$this->table} 
                SET first_name = :first_name, 
                    last_name  = :last_name, 
                    email      = :email, 
                    phone      = :phone, 
                    gender     = :gender, 
                    dob        = :dob, 
                    address    = :address, 
                    plan_id    = :plan_id, 
                    trainer_id = :trainer_id, 
                    join_date  = :join_date, 
                    expire_date= :expire_date, 
                    status     = :status, 
                    photo      = :photo 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'          => (int)$id,
            ':first_name'  => trim($data['first_name']),
            ':last_name'   => trim($data['last_name']),
            ':email'       => trim($data['email']),
            ':phone'       => trim($data['phone']),
            ':gender'      => $data['gender'] ?? 'Male',
            ':dob'         => !empty($data['dob']) ? $data['dob'] : null,
            ':address'     => trim($data['address'] ?? ''),
            ':plan_id'     => (int)$data['plan_id'],
            ':trainer_id'  => !empty($data['trainer_id']) ? (int)$data['trainer_id'] : null,
            ':join_date'   => $join_date,
            ':expire_date' => $expire_date,
            ':status'      => $data['status'] ?? 'Active',
            ':photo'       => $photo
        ]);
    }

    // Delete a member
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => (int)$id]);
    }

    // System Dashboard Counters
    public function countTotal() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->query($sql);
        return $stmt->fetch()['total'];
    }

    public function countActive() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'Active'";
        $stmt = $this->db->query($sql);
        return $stmt->fetch()['total'];
    }

    public function countExpired() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'Expired' OR expire_date < CURDATE()";
        $stmt = $this->db->query($sql);
        return $stmt->fetch()['total'];
    }
}
