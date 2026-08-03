<?php
/**
 * Plan Class - Object-Oriented Management for Membership Plans
 */

require_once __DIR__ . '/../config/Database.php';

class Plan {
    private $db;
    private $table = "plans";

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Get all plans
    public function getAll() {
        $sql = "SELECT * FROM {$this->table} ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get active plans only
    public function getActive() {
        $sql = "SELECT * FROM {$this->table} WHERE status = 'Active' ORDER BY price ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get single plan by ID
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // Create a new membership plan
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (name, duration_months, price, description, status) 
                VALUES (:name, :duration_months, :price, :description, :status)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':name'            => trim($data['name']),
            ':duration_months' => (int)$data['duration_months'],
            ':price'           => (float)$data['price'],
            ':description'    => trim($data['description'] ?? ''),
            ':status'         => $data['status'] ?? 'Active'
        ]);
    }

    // Update an existing plan
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET name = :name, duration_months = :duration_months, price = :price, 
                    description = :description, status = :status 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id'              => (int)$id,
            ':name'            => trim($data['name']),
            ':duration_months' => (int)$data['duration_months'],
            ':price'           => (float)$data['price'],
            ':description'    => trim($data['description'] ?? ''),
            ':status'         => $data['status'] ?? 'Active'
        ]);
    }

    // Delete a plan
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => (int)$id]);
    }

    // Count total plans
    public function count() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'];
    }
}
