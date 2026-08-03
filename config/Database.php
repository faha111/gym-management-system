<?php
/**
 * Database Connection Class (PDO Singleton Pattern)
 * Gym Management System
 */

// Ensure all date()/time() calls across the app use Sri Lanka local time (UTC+5:30)
date_default_timezone_set('Asia/Colombo');

/**
 * BASE_URL: absolute, site-root-relative path to the project (e.g. "/gym_management").
 * Computed automatically by comparing this file's folder to the web server's document
 * root, so every page/asset link works correctly no matter how deep a page is nested
 * (e.g. members/members.php, member-portal/member_profile.php) or what folder name
 * the project is deployed under in htdocs.
 */
if (!defined('BASE_URL')) {
    $projectRoot = str_replace('\\', '/', dirname(__DIR__)); // filesystem path 1 level above /config
    $docRoot     = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/') : '';
    $base        = ($docRoot !== '' && str_starts_with($projectRoot, $docRoot))
        ? substr($projectRoot, strlen($docRoot))
        : '';
    define('BASE_URL', $base);
}

class Database {
    private $host = "localhost";
    private $db_name = "gym_db";
    private $username = "root";
    private $password = "";
    private static $instance = null;
    private $conn;

    // Private constructor prevents direct instantiation
    private function __construct() {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            die("Database Connection Error: " . $e->getMessage() . "<br>Please ensure XAMPP MySQL service is running and `gym_db` database is created.");
        }
    }

    // Get Database instance (Singleton)
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Get active PDO Connection
    public function getConnection() {
        return $this->conn;
    }
}
