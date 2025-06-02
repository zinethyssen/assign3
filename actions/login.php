<?php

header('Content-Type: application/json');  // <-- Added JSON header

// Start the session
session_start(); 

// Include the database connection
require '../db.php'; 

// Get the user input
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password']; 

    // query to get password hash and screen name from database
    $stmt = $conn->prepare("SELECT password, screenName FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($hashedPassword, $screenName);

    // If user exists, validate password
    if ($stmt->fetch()) {
        if (password_verify($password, $hashedPassword)) {
            // Password is correct, start session
            $_SESSION['username'] = $username;
            $_SESSION['screenName'] = $screenName;
            echo json_encode([
                'success' => true,
                'message' => 'Login successful'
            ]);
        } else {
            // Wrong password
            echo json_encode([
                'success' => false,
                'message' => 'Incorrect password.'
            ]);
        }
    } else {
        // No such user found
        echo json_encode([
            'success' => false,
            'message' => 'Username not found.'
        ]);
    }    
    $stmt->close();

    // Close the connection
    $conn->close();

    exit();  // <-- Important to stop script after outputting JSON
}
