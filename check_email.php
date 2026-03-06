<?php
// check_email.php  –  AJAX endpoint: is the e-mail already registered?

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once 'config.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$email = trim($_POST['email'] ?? '');

// Basic server-side format check
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['available' => false, 'message' => 'Invalid e-mail format.']);
    exit;
}

try {
    $pdo  = getPDO();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM students WHERE email = ?');
    $stmt->execute([$email]);
    $count = (int) $stmt->fetchColumn();

    echo json_encode([
        'available' => $count === 0,
        'message'   => $count === 0 ? 'E-mail is available.' : 'E-mail is already registered.',
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    // Never expose raw DB errors to the client
    echo json_encode(['error' => 'Server error. Please try again later.']);
}
