<?php
/**
 * Attendance Class - Object-Oriented Management for Daily Member Attendance
 */

require_once __DIR__ . '/../config/Database.php';

class Attendance {
    private $db;
    private $table = "attendance";

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Get attendance records for a specific date or recent days
    public function getRecords($date = null) {
        $selectedDate = $date ? $date : date('Y-m-d');
        
        $sql = "SELECT a.*, 
                       m.member_code, m.first_name, m.last_name, m.photo,
                       p.name as plan_name
                FROM {$this->table} a
                JOIN members m ON a.member_id = m.id
                JOIN plans p ON m.plan_id = p.id
                WHERE a.date = :date
                ORDER BY a.check_in DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':date' => $selectedDate]);
        return $stmt->fetchAll();
    }

    // Get attendance records for an entire month (YYYY-MM), most recent first
    public function getRecordsByMonth($yearMonth) {
        $sql = "SELECT a.*, 
                       m.member_code, m.first_name, m.last_name, m.photo,
                       p.name as plan_name
                FROM {$this->table} a
                JOIN members m ON a.member_id = m.id
                JOIN plans p ON m.plan_id = p.id
                WHERE DATE_FORMAT(a.date, '%Y-%m') = :ym
                ORDER BY a.date DESC, a.check_in DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ym' => $yearMonth]);
        return $stmt->fetchAll();
    }

    // Per-member visit counts for a given month, most frequent first
    public function getMonthlySummary($yearMonth) {
        $sql = "SELECT m.id, m.member_code, m.first_name, m.last_name, m.photo,
                       COUNT(a.id) as visit_count
                FROM {$this->table} a
                JOIN members m ON a.member_id = m.id
                WHERE DATE_FORMAT(a.date, '%Y-%m') = :ym
                GROUP BY m.id
                ORDER BY visit_count DESC, m.first_name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':ym' => $yearMonth]);
        return $stmt->fetchAll();
    }

    // Check-in a member
    public function checkIn($memberId, $notes = '') {
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        // Check if member is already checked in today without checking out
        $sqlCheck = "SELECT id FROM {$this->table} WHERE member_id = :member_id AND date = :date AND check_out IS NULL";
        $stmtCheck = $this->db->prepare($sqlCheck);
        $stmtCheck->execute([
            ':member_id' => (int)$memberId,
            ':date'      => $today
        ]);

        if ($stmtCheck->fetch()) {
            return ["success" => false, "message" => "Member is already checked in today!"];
        }

        $sql = "INSERT INTO {$this->table} (member_id, check_in, date, notes) VALUES (:member_id, :check_in, :date, :notes)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            ':member_id' => (int)$memberId,
            ':check_in'  => $now,
            ':date'      => $today,
            ':notes'     => trim($notes)
        ]);

        return ["success" => $result, "message" => $result ? "Member checked in successfully!" : "Failed to check in member."];
    }

    // Check-out a member
    public function checkOut($attendanceId) {
        $now = date('Y-m-d H:i:s');
        $sql = "UPDATE {$this->table} SET check_out = :check_out WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':check_out' => $now,
            ':id'        => (int)$attendanceId
        ]);
    }

    // Delete attendance log
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => (int)$id]);
    }

    // Count today's check-ins
    public function countTodayCheckIns() {
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE date = :date";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':date' => $today]);
        $row = $stmt->fetch();
        return $row['total'];
    }
}
