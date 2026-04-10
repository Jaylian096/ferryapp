<?php
// api/shipping_lines.php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db = getDB();

if ($method === 'GET') {
    $result = $db->query("SELECT * FROM shipping_lines WHERE status='active' ORDER BY name");
    respond(['success' => true, 'lines' => $result->fetch_all(MYSQLI_ASSOC)]);
}

if ($method === 'POST') {
    $data = getInput();
    if ($action === 'create') {
        $name  = trim($data['name'] ?? '');
        $route = trim($data['route'] ?? '');
        $stmt  = $db->prepare('INSERT INTO shipping_lines (name, route) VALUES (?,?)');
        $stmt->bind_param('ss', $name, $route);
        $stmt->execute();
        respond(['success' => true, 'message' => 'Shipping line created']);
    }
    if ($action === 'update') {
        $id     = intval($data['id'] ?? 0);
        $name   = trim($data['name'] ?? '');
        $route  = trim($data['route'] ?? '');
        $status = $data['status'] ?? 'active';
        $stmt   = $db->prepare('UPDATE shipping_lines SET name=?, route=?, status=? WHERE id=?');
        $stmt->bind_param('sssi', $name, $route, $status, $id);
        $stmt->execute();
        respond(['success' => true, 'message' => 'Updated']);
    }
}

if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $db->prepare('DELETE FROM shipping_lines WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    respond(['success' => true, 'message' => 'Deleted']);
}

respond(['success' => false, 'message' => 'Invalid request'], 400);
?>
