<?php
// api/auth.php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST') {
    $data = getInput();

    if ($action === 'register') {
        $name    = trim($data['name'] ?? '');
        $email   = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $contact = trim($data['contact'] ?? '');

        if (!$name || !$email || !$password) {
            respond(['success' => false, 'message' => 'All fields required'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            respond(['success' => false, 'message' => 'Invalid email'], 400);
        }
        if (strlen($password) < 6) {
            respond(['success' => false, 'message' => 'Password must be at least 6 characters'], 400);
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            respond(['success' => false, 'message' => 'Email already registered'], 409);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO users (name, email, password, contact) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $hash, $contact);
        if ($stmt->execute()) {
            respond(['success' => true, 'message' => 'Registration successful']);
        } else {
            respond(['success' => false, 'message' => 'Registration failed'], 500);
        }
    }

    if ($action === 'login') {
        $email    = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (!$email || !$password) {
            respond(['success' => false, 'message' => 'Email and password required'], 400);
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user || !password_verify($password, $user['password'])) {
            respond(['success' => false, 'message' => 'Invalid credentials'], 401);
        }
        if ($user['status'] === 'inactive') {
            respond(['success' => false, 'message' => 'Account is inactive'], 403);
        }

        unset($user['password']);
        respond(['success' => true, 'user' => $user]);
    }

    if ($action === 'admin_login') {
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if (!$username || !$password) {
            respond(['success' => false, 'message' => 'Username and password required'], 400);
        }

        $db = getDB();
        $stmt = $db->prepare('SELECT id, username, password FROM admins WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if (!$admin || !password_verify($password, $admin['password'])) {
            respond(['success' => false, 'message' => 'Invalid admin credentials'], 401);
        }

        unset($admin['password']);
        respond(['success' => true, 'admin' => $admin]);
    }
}

respond(['success' => false, 'message' => 'Invalid request'], 400);
?>
