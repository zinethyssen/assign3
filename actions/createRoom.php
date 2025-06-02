<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);

$data = json_decode(file_get_contents("php://input"), true);
$name = trim($data['name'] ?? '');
$key = trim($data['key'] ?? '');
$user = $_SESSION['username'] ?? 'guest';

if (!isset($_SESSION['chatrooms'])) {
    $_SESSION['chatrooms'] = [];
}

if ($name === '') {
    echo json_encode(['success' => false, 'error' => 'Room name cannot be empty.']);
    exit;
}

if (isset($_SESSION['chatrooms'][$name])) {
    echo json_encode(['success' => false, 'error' => 'Room already exists.']);
    exit;
}

$_SESSION['chatrooms'][$name] = [
    'key' => $key,
    'creator' => $user
];

echo json_encode(['success' => true]);

// no closing PHP tag on purpose
