<?php
// api/schedules.php
require_once '../config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$db = getDB();

// Validate DB connection
if (!$db) {
    respond(['success' => false, 'message' => 'Database connection failed'], 500);
}

if ($method === 'GET') {

    // ── GET ALL ─────────────────────────
    if ($action === 'all') {
        $result = $db->query("
            SELECT s.*, sl.name AS shipping_line 
            FROM schedules s 
            JOIN shipping_lines sl ON s.shipping_line_id = sl.id 
            ORDER BY s.departure_time
        ");

        if (!$result) {
            respond(['success' => false, 'message' => $db->error], 500);
        }

        respond([
            'success' => true,
            'schedules' => $result->fetch_all(MYSQLI_ASSOC)
        ]);
    }

    // ── GET BY LINE ─────────────────────
    if ($action === 'by_line') {

        if (!isset($_GET['shipping_line_id'])) {
            respond(['success' => false, 'message' => 'Missing shipping_line_id'], 400);
        }

        $line_id = intval($_GET['shipping_line_id']);

        // DEBUG: return early if invalid ID
        if ($line_id <= 0) {
            respond(['success' => false, 'message' => 'Invalid shipping_line_id'], 400);
        }

        $stmt = $db->prepare("
            SELECT * FROM schedules 
            WHERE shipping_line_id=? 
            AND status='active'
            ORDER BY departure_time
        ");

        if (!$stmt) {
            respond(['success' => false, 'message' => $db->error], 500);
        }

        $stmt->bind_param('i', $line_id);
        $stmt->execute();

        $result = $stmt->get_result();

        respond([
            'success' => true,
            'count' => $result->num_rows, // 👈 helpful debug
            'schedules' => $result->fetch_all(MYSQLI_ASSOC)
        ]);
    }
}

if ($method === 'POST') {
    $data = getInput();

    // ── CREATE ──────────────────────────
    if ($action === 'create') {
        $line_id = intval($data['shipping_line_id'] ?? 0);
        $route   = trim($data['route'] ?? '');
        $time    = $data['departure_time'] ?? '';

        if (!$line_id || !$route || !$time) {
            respond(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        // ✅ FIX: include status
        $stmt = $db->prepare("
            INSERT INTO schedules (shipping_line_id, route, departure_time, status) 
            VALUES (?,?,?,'active')
        ");

        if (!$stmt) {
            respond(['success' => false, 'message' => $db->error], 500);
        }

        $stmt->bind_param('iss', $line_id, $route, $time);
        $stmt->execute();

        respond(['success' => true, 'message' => 'Schedule created']);
    }

    // ── UPDATE ──────────────────────────
    if ($action === 'update') {
        $id      = intval($data['id'] ?? 0);
        $route   = trim($data['route'] ?? '');
        $time    = $data['departure_time'] ?? '';
        $status  = $data['status'] ?? 'active';

        if (!$id) {
            respond(['success' => false, 'message' => 'Invalid ID'], 400);
        }

        $stmt = $db->prepare("
            UPDATE schedules 
            SET route=?, departure_time=?, status=? 
            WHERE id=?
        ");

        if (!$stmt) {
            respond(['success' => false, 'message' => $db->error], 500);
        }

        $stmt->bind_param('sssi', $route, $time, $status, $id);
        $stmt->execute();

        respond(['success' => true, 'message' => 'Schedule updated']);
    }
}

if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);

    if (!$id) {
        respond(['success' => false, 'message' => 'Invalid ID'], 400);
    }

    $stmt = $db->prepare("DELETE FROM schedules WHERE id=?");

    if (!$stmt) {
        respond(['success' => false, 'message' => $db->error], 500);
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();

    respond(['success' => true, 'message' => 'Schedule deleted']);
}

// Default fallback
respond(['success' => false, 'message' => 'Invalid request'], 400);