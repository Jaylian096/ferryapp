<?php
// api/bookings.php
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    $db = getDB();

    if ($action === 'user') {
        $user_id = intval($_GET['user_id'] ?? 0);
        $stmt = $db->prepare('
            SELECT b.*, sl.name AS shipping_line, s.route, s.departure_time,
                   bd.passenger_type, bd.class_type, bd.fare, bd.cargo_type, bd.cargo_price
            FROM bookings b
            JOIN shipping_lines sl ON b.shipping_line_id = sl.id
            JOIN schedules s ON b.schedule_id = s.id
            LEFT JOIN booking_details bd ON bd.booking_id = b.id
            WHERE b.user_id = ?
            ORDER BY b.booking_date DESC
        ');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        respond(['success' => true, 'bookings' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
    }

    if ($action === 'all') {
        $result = $db->query('
            SELECT b.*, u.name AS user_name, u.email AS user_email,
                   sl.name AS shipping_line, s.route, s.departure_time,
                   bd.passenger_type, bd.class_type, bd.fare, bd.cargo_type, bd.cargo_price
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN shipping_lines sl ON b.shipping_line_id = sl.id
            JOIN schedules s ON b.schedule_id = s.id
            LEFT JOIN booking_details bd ON bd.booking_id = b.id
            ORDER BY b.booking_date DESC
        ');
        respond(['success' => true, 'bookings' => $result->fetch_all(MYSQLI_ASSOC)]);
    }

    if ($action === 'stats') {
        $total    = $db->query('SELECT COUNT(*) AS c FROM bookings')->fetch_assoc()['c'];
        $revenue  = $db->query("SELECT SUM(total_price) AS r FROM bookings WHERE status != 'cancelled'")->fetch_assoc()['r'] ?? 0;
        $users    = $db->query("SELECT COUNT(*) AS c FROM users WHERE role='user'")->fetch_assoc()['c'];
        $schedules= $db->query("SELECT COUNT(*) AS c FROM schedules WHERE status='active'")->fetch_assoc()['c'];
        $monthly  = $db->query("SELECT SUM(total_price) AS r FROM bookings WHERE MONTH(booking_date)=MONTH(NOW()) AND YEAR(booking_date)=YEAR(NOW()) AND status != 'cancelled'")->fetch_assoc()['r'] ?? 0;
        respond(['success' => true, 'stats' => compact('total','revenue','users','schedules','monthly')]);
    }

    if ($action === 'single') {
        $ref = $_GET['ref'] ?? '';
        $stmt = $db->prepare('
            SELECT b.*, u.name AS user_name, u.email, u.contact,
                   sl.name AS shipping_line, s.route, s.departure_time,
                   bd.passenger_type, bd.class_type, bd.fare, bd.cargo_type, bd.cargo_price
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN shipping_lines sl ON b.shipping_line_id = sl.id
            JOIN schedules s ON b.schedule_id = s.id
            LEFT JOIN booking_details bd ON bd.booking_id = b.id
            WHERE b.reference_no = ?
        ');
        $stmt->bind_param('s', $ref);
        $stmt->execute();
        $booking = $stmt->get_result()->fetch_assoc();
        if ($booking) respond(['success' => true, 'booking' => $booking]);
        else respond(['success' => false, 'message' => 'Booking not found'], 404);
    }
}

if ($method === 'POST') {
    $data = getInput();

    if ($action === 'create') {
        $user_id         = intval($data['user_id'] ?? 0);
        $shipping_line_id= intval($data['shipping_line_id'] ?? 0);
        $schedule_id     = intval($data['schedule_id'] ?? 0);
        $passenger_type  = $data['passenger_type'] ?? '';
        $class_type      = $data['class_type'] ?? 'N/A';
        $cargo_type      = $data['cargo_type'] ?? null;
        $fare            = floatval($data['fare'] ?? 0);
        $cargo_price     = floatval($data['cargo_price'] ?? 0);
        $total_price     = $fare + $cargo_price;

        if (!$user_id || !$shipping_line_id || !$schedule_id || !$passenger_type) {
            respond(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        $db  = getDB();
        $ref = generateRef();

        $stmt = $db->prepare('INSERT INTO bookings (user_id, shipping_line_id, schedule_id, total_price, reference_no) VALUES (?,?,?,?,?)');
        $stmt->bind_param('iiiis', $user_id, $shipping_line_id, $schedule_id, $total_price, $ref);
        if (!$stmt->execute()) {
            respond(['success' => false, 'message' => 'Booking failed'], 500);
        }
        $booking_id = $db->insert_id;

        $stmt2 = $db->prepare('INSERT INTO booking_details (booking_id, passenger_type, class_type, fare, cargo_type, cargo_price) VALUES (?,?,?,?,?,?)');
        $stmt2->bind_param('issdsd', $booking_id, $passenger_type, $class_type, $fare, $cargo_type, $cargo_price);
        $stmt2->execute();

        respond(['success' => true, 'reference_no' => $ref, 'booking_id' => $booking_id, 'total_price' => $total_price]);
    }

    if ($action === 'cancel') {
        $booking_id = intval($data['booking_id'] ?? 0);
        $user_id    = intval($data['user_id'] ?? 0);
        $db = getDB();
        $stmt = $db->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND user_id=?");
        $stmt->bind_param('ii', $booking_id, $user_id);
        $stmt->execute();
        respond(['success' => true, 'message' => 'Booking cancelled']);
    }

    if ($action === 'update_status') {
        $booking_id = intval($data['booking_id'] ?? 0);
        $status     = $data['status'] ?? '';
        $db = getDB();
        $stmt = $db->prepare("UPDATE bookings SET status=? WHERE id=?");
        $stmt->bind_param('si', $status, $booking_id);
        $stmt->execute();
        respond(['success' => true, 'message' => 'Status updated']);
    }
}

if ($method === 'DELETE') {
    $booking_id = intval($_GET['id'] ?? 0);
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM bookings WHERE id=?");
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    respond(['success' => true, 'message' => 'Booking deleted']);
}

respond(['success' => false, 'message' => 'Invalid request'], 400);
?>
