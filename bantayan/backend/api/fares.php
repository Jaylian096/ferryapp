<?php
// api/fares.php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db = getDB();

if ($method === 'GET') {
    if ($action === 'all') {
        $result = $db->query('SELECT f.*, sl.name AS shipping_line FROM fares f JOIN shipping_lines sl ON f.shipping_line_id = sl.id ORDER BY sl.name, f.passenger_type');
        respond(['success' => true, 'fares' => $result->fetch_all(MYSQLI_ASSOC)]);
    }
    if ($action === 'by_line') {
        $line_id = intval($_GET['shipping_line_id'] ?? 0);
        $stmt = $db->prepare('SELECT * FROM fares WHERE shipping_line_id=?');
        $stmt->bind_param('i', $line_id);
        $stmt->execute();
        respond(['success' => true, 'fares' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
    }
    if ($action === 'cargo_all') {
        $result = $db->query('SELECT cr.*, sl.name AS shipping_line FROM cargo_rates cr JOIN shipping_lines sl ON cr.shipping_line_id = sl.id ORDER BY sl.name, cr.cargo_type');
        respond(['success' => true, 'cargo_rates' => $result->fetch_all(MYSQLI_ASSOC)]);
    }
    if ($action === 'cargo_by_line') {
        $line_id = intval($_GET['shipping_line_id'] ?? 0);
        $stmt = $db->prepare('SELECT * FROM cargo_rates WHERE shipping_line_id=?');
        $stmt->bind_param('i', $line_id);
        $stmt->execute();
        respond(['success' => true, 'cargo_rates' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
    }
}

if ($method === 'POST') {
    $data = getInput();
    if ($action === 'update_fare') {
        $id    = intval($data['id'] ?? 0);
        $price = floatval($data['price'] ?? 0);
        $stmt  = $db->prepare('UPDATE fares SET price=? WHERE id=?');
        $stmt->bind_param('di', $price, $id);
        $stmt->execute();
        respond(['success' => true, 'message' => 'Fare updated']);
    }
    if ($action === 'create_fare') {
        $line_id   = intval($data['shipping_line_id'] ?? 0);
        $ptype     = $data['passenger_type'] ?? '';
        $class     = $data['class_type'] ?? 'N/A';
        $price     = floatval($data['price'] ?? 0);
        $stmt      = $db->prepare('INSERT INTO fares (shipping_line_id, passenger_type, class_type, price) VALUES (?,?,?,?)');
        $stmt->bind_param('issd', $line_id, $ptype, $class, $price);
        $stmt->execute();
        respond(['success' => true, 'message' => 'Fare created']);
    }
    if ($action === 'update_cargo') {
        $id    = intval($data['id'] ?? 0);
        $price = floatval($data['price'] ?? 0);
        $stmt  = $db->prepare('UPDATE cargo_rates SET price=? WHERE id=?');
        $stmt->bind_param('di', $price, $id);
        $stmt->execute();
        respond(['success' => true, 'message' => 'Cargo rate updated']);
    }
    if ($action === 'create_cargo') {
        $line_id    = intval($data['shipping_line_id'] ?? 0);
        $cargo_type = $data['cargo_type'] ?? '';
        $price      = floatval($data['price'] ?? 0);
        $stmt       = $db->prepare('INSERT INTO cargo_rates (shipping_line_id, cargo_type, price) VALUES (?,?,?)');
        $stmt->bind_param('isd', $line_id, $cargo_type, $price);
        $stmt->execute();
        respond(['success' => true, 'message' => 'Cargo rate created']);
    }
}

if ($method === 'DELETE') {
    $id   = intval($_GET['id'] ?? 0);
    $type = $_GET['type'] ?? 'fare';
    $table = $type === 'cargo' ? 'cargo_rates' : 'fares';
    $stmt = $db->prepare("DELETE FROM $table WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    respond(['success' => true, 'message' => 'Deleted']);
}

respond(['success' => false, 'message' => 'Invalid request'], 400);
?>
