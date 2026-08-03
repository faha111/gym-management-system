<?php
require_once __DIR__ . '/../classes/Auth.php';
$auth = new Auth();
$auth->logout();
header("Location: " . BASE_URL . "/auth/login.php");
exit;
