<?php
// logout.php
session_start();
include 'db.php';

$is_auto_logout = isset($_POST['auto_logout']) && $_POST['auto_logout'] === 'true';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $logout_time = date('Y-m-d H:i:s');
    $login_time = date('Y-m-d H:i:s', $_SESSION['login_time']);
    $duration = time() - $_SESSION['login_time'];
    $session_period = "$login_time - $logout_time";
    $logout_reason = $is_auto_logout ? 'auto_inactivity' : 'manual';
    
    $stmt = $conn->prepare("UPDATE login_logs SET logout_time = ?, duration = ?, session_period = ?, logout_reason = ? WHERE user_id = ? AND logout_time IS NULL");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sissi", $logout_time, $duration, $session_period, $logout_reason, $user_id);
    $stmt->execute();
    $stmt->close();
}

session_unset();
session_destroy();

// Return JSON for AJAX (auto logout) or redirect for manual logout
if ($is_auto_logout) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success']);
    exit;
} else {
    header("Location: login.php");
    exit;
}
?>
