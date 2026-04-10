<?php
// api/users.php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db = getDB();

if ($method === 'GET') {
    if ($action === 'all') {
        $result = $db->query("SELECT id, name, email, contact, role, status, created_at FROM users ORDER BY created_at DESC");
        respond(['success' => true, 'users' => $result->fetch_all(MYSQLI_ASSOC)]);
    }
    if ($action === 'admins') {
        $result = $db->query("SELECT id, username, created_at FROM admins ORDER BY created_at DESC");
        respond(['success' => true, 'admins' => $result->fetch_all(MYSQLI_ASSOC)]);
    }
    if ($action === 'single') {
        $id   = intval($_GET['id'] ?? 0);
        $stmt = $db->prepare('SELECT id, name, email, contact, role, status FROM users WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        respond(['success' => true, 'user' => $user]);
    }
}

if ($method === 'POST') {
    $data = getInput();

    if ($action === 'update_status') {
        $id     = intval($data['id'] ?? 0);
        $status = $data['status'] ?? 'active';
        $stmt   = $db->prepare('UPDATE users SET status=? WHERE id=?');
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
        respond(['success' => true, 'message' => 'Status updated']);
    }

    if ($action === 'create_admin') {
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        if (!$username || !$password) respond(['success' => false, 'message' => 'Fields required'], 400);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO admins (username, password) VALUES (?,?)');
        $stmt->bind_param('ss', $username, $hash);
        if ($stmt->execute()) respond(['success' => true, 'message' => 'Admin created']);
        else respond(['success' => false, 'message' => 'Username taken'], 409);
    }

    if ($action === 'delete_admin') {
        $id   = intval($data['id'] ?? 0);
        $stmt = $db->prepare('DELETE FROM admins WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        respond(['success' => true, 'message' => 'Admin deleted']);
    }

    if ($action === 'update_user') {
        $id      = intval($data['id'] ?? 0);
        $name    = trim($data['name'] ?? '');
        $contact = trim($data['contact'] ?? '');
        $stmt    = $db->prepare('UPDATE users SET name=?, contact=? WHERE id=?');
        $stmt->bind_param('ssi', $name, $contact, $id);
        $stmt->execute();
        respond(['success' => true, 'message' => 'User updated']);
    }
}

if ($method === 'DELETE') {
    $id   = intval($_GET['id'] ?? 0);
    $stmt = $db->prepare('DELETE FROM users WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    respond(['success' => true, 'message' => 'User deleted']);
}

respond(['success' => false, 'message' => 'Invalid request'], 400);
?>
