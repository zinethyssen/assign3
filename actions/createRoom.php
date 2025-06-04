<?php
session_start();
header('Content-Type: application/json');
require '../db.php';

$data = json_decode(file_get_contents("php://input"), true);
$name = trim($data['name'] ?? '');
$key = trim($data['key'] ?? '');
$user = $_SESSION['username'] ?? 'guest';

if ($name === '') {
    echo json_encode(['success' => false, 'error' => 'Room name cannot be empty.']);
    exit;
}

// Check if room already exists in DB
$stmt = $conn->prepare("SELECT chatroomName FROM list_of_chatrooms WHERE chatroomName = ?");
$stmt->bind_param("s", $name);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Room already exists.']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Insert the new room
$stmt = $conn->prepare("INSERT INTO list_of_chatrooms (chatroomName, chatroomKey, creatorUsername) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $key, $user);
$success = $stmt->execute();
$stmt->close();
$conn->close();

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to create room.']);
}
