<?php
/**
 * Payment Class - Object-Oriented Management for Payments & Invoices
 */

require_once __DIR__ . '/../config/Database.php';

class Payment {
    private $db;
    private $table = "payments";

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Generate unique invoice number (e.g. INV-2026-005)
    public function generateInvoiceNumber() {
        $year = date('Y');
        $sql = "SELECT id FROM {$this->table} ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();
        $nextId = $row ? $row['id'] + 1 : 1;
        return sprintf("INV-%s-%03d", $year, $nextId);
    }

    // Get all payments with member details
    public function getAll() {
        $sql = "SELECT p.*, 
                       m.member_code, m.first_name, m.last_name, m.email,
                       pl.name as plan_name
                FROM {$this->table} p
                JOIN members m ON p.member_id = m.id
                JOIN plans pl ON m.plan_id = pl.id
                ORDER BY p.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get single payment record
    public function getById($id) {
        $sql = "SELECT p.*, 
                       m.member_code, m.first_name, m.last_name, m.email, m.phone,
                       pl.name as plan_name
                FROM {$this->table} p
                JOIN members m ON p.member_id = m.id
                JOIN plans pl ON m.plan_id = pl.id
                WHERE p.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch();
    }

    // Create a new payment record
    public function create($data) {
        $invoice_no = $this->generateInvoiceNumber();
        $sql = "INSERT INTO {$this->table} (member_id, invoice_no, amount, payment_date, payment_method, payment_status, notes) 
                VALUES (:member_id, :invoice_no, :amount, :payment_date, :payment_method, :payment_status, :notes)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':member_id'      => (int)$data['member_id'],
            ':invoice_no'      => $invoice_no,
            ':amount'          => (float)$data['amount'],
            ':payment_date'    => !empty($data['payment_date']) ? $data['payment_date'] : date('Y-m-d'),
            ':payment_method'  => $data['payment_method'] ?? 'Cash',
            ':payment_status'  => $data['payment_status'] ?? 'Paid',
            ':notes'           => trim($data['notes'] ?? '')
        ]);
    }

    // Delete a payment record
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => (int)$id]);
    }

    // Flip a Pending payment to Paid once the Admin has confirmed it was collected
    public function markPaid($id) {
        $sql = "UPDATE {$this->table} SET payment_status = 'Paid' WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => (int)$id]);
    }

    // Calculate total revenue generated
    public function getTotalRevenue() {
        $sql = "SELECT SUM(amount) as total FROM {$this->table} WHERE payment_status = 'Paid'";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();
        return $row['total'] ? (float)$row['total'] : 0.00;
    }
}
