<?php
// auth.php
// session_start();
include 'db.php'; // Assumes db.php defines $conn

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

function isGuest() {
    return !isset($_SESSION['username']);
}

function checkLogin($role = null) {
    if (!isset($_SESSION['username']) || ($role && $_SESSION['role'] !== $role)) {
        header("Location: login.php");
        exit;
    }
}

function login($username, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time(); // Track last activity for timeout
            
            // Log login activity with IP address
            $login_time = date('Y-m-d H:i:s');
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $stmt = $conn->prepare("INSERT INTO login_logs (user_id, username, role, login_time, ip_address) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("issss", $user['id'], $user['username'], $user['role'], $login_time, $ip_address);
            $stmt->execute();
            $stmt->close();
            return true;
        }
    }
    $stmt->close();
    return false;
}
?>