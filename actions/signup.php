<?php
// Start the session
header('Content-Type: application/json');
session_start();

// Include the database connection
require '../db.php';

// Get user input
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $screenName = trim($_POST['screenName']);

    // hash the password using php
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // check if the username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username already taken.'
        ]);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // check if the screen name already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE screenName = ?");
    $stmt->bind_param("s", $screenName);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Screen name already taken.'
        ]);
        $stmt->close();
        $conn->close();
        exit();
    }
    $stmt->close();

    // If both username and screen name are available, add the new user
    $stmt = $conn->prepare("INSERT INTO users (username, password, screenName) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashedPassword, $screenName);

    if ($stmt->execute()) {
    // Automatically log the user in
    $_SESSION['username'] = $username;
    $_SESSION['screenName'] = $screenName;

    echo json_encode([
        'success' => true,
        'message' => 'Signup successful',
        'redirect' => 'chatroom.php'
    ]);
    exit();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Signup failed. Please try again.'
    ]);
    exit();
}


    $stmt->close();
    $conn->close();
}
?>
