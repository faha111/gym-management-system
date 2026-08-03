<?php
/**
 * Flash Alert Messages Helper
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function setAlert($message, $type = 'success') {
    $_SESSION['flash_alert'] = [
        'message' => $message,
        'type'    => $type // 'success', 'danger', 'warning', 'info'
    ];
}

function displayAlert() {
    if (isset($_SESSION['flash_alert'])) {
        $alert = $_SESSION['flash_alert'];
        $typeClass = $alert['type'] === 'danger' ? 'alert-danger' : ($alert['type'] === 'warning' ? 'alert-warning' : 'alert-success');
        $icon = $alert['type'] === 'danger' ? '⚠️' : ($alert['type'] === 'warning' ? '🔔' : '✅');
        
        echo "<div class='alert-toast {$typeClass}' id='flashAlert'>
                <span>{$icon} {$alert['message']}</span>
                <button class='alert-close' onclick='document.getElementById(\"flashAlert\").remove();'>&times;</button>
              </div>";
        unset($_SESSION['flash_alert']);
    }
}
