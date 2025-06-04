<?php
require '../db.php';
header('Content-Type: application/json');

$stmt = $conn->prepare("SELECT chatroomName, chatroomKey FROM list_of_chatrooms");
$stmt->execute();
$stmt->bind_result($chatroomName, $chatroomKey);

$rooms = [];
while ($stmt->fetch()) {
    $rooms[] = [
        'name' => $chatroomName,
        'locked' => !empty($chatroomKey)
    ];
}

$stmt->close();
$conn->close();

echo json_encode($rooms);
?>
