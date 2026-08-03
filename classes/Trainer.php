<?php
/**
 * Trainer Class - Object-Oriented Management for Personal Trainers & Staff
 */

require_once __DIR__ . '/../config/Database.php';

class Trainer {
    private $db;
    private $table = "trainers";

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Get all trainers
    public function getAll() {
        $sql = "SELECT t.*, 
                       (SELECT COUNT(*) FROM members m WHERE m.trainer_id = t.id) as total_assigned_members 
                FROM {$this->table} t 
                ORDER BY t.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get active trainers only
    public function getActive() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'Active' ORDER BY full_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get single trainer by ID
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch();
    }

    // Create new trainer
    public function create($data, $photoFileName = 'default_user.png') {
        $sql = "INSERT INTO {$this->table} (full_name, email, phone, specialization, joining_date, status, photo) 
                VALUES (:full_name, :email, :phone, :specialization, :joining_date, :status, :photo)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':full_name'      => trim($data['full_name']),
            ':email'          => trim($data['email']),
            ':phone'          => trim($data['phone']),
            ':specialization' => trim($data['specialization']),
            ':joining_date'   => $data['joining_date'],
            ':status'         => $data['status'] ?? 'Active',
            ':photo'          => $photoFileName
        ]);
    }

    // Update existing trainer
    public function update($id, $data, $photoFileName = null) {
        $current = $this->getById($id);
        $photo = $photoFileName ? $photoFileName : $current['photo'];

        $sql = "UPDATE {$this->table} 
                SET full_name = :full_name, email = :email, phone = :phone, 
                    specialization = :specialization, joining_date = :joining_date, 
                    status = :status, photo = :photo 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'             => (int)$id,
            ':full_name'      => trim($data['full_name']),
            ':email'          => trim($data['email']),
            ':phone'          => trim($data['phone']),
            ':specialization' => trim($data['specialization']),
            ':joining_date'   => $data['joining_date'],
            ':status'         => $data['status'] ?? 'Active',
            ':photo'          => $photo
        ]);
    }

    // Delete a trainer
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => (int)$id]);
    }

    // Count total active trainers
    public function countActive() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'Active'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'];
    }
}
