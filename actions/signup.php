<?
// Start the session
session_start();

// Include the database connection
require 'db.php';

// Get user input
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);

    // hash the password using php
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $screenName = trim($_POST['screenName']);

    // check if the username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Username is already taken.";
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
        echo "Screen name is already taken.";
        $stmt->close();
        $conn->close();
        exit(); 
    }
    $stmt->close();

    // If both username and screen name are available, add the new user
    $stmt = $conn->prepare("INSERT INTO users (username, password, screenName) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $screenName);

    if ($stmt->execute()) {
        // Automatically log the user in
        $_SESSION['username'] = $username;
        $_SESSION['screenName'] = $screenName;
        echo "Signup successful";
    } else {
        echo "Signup failed. Please try again.";
    }
   
    $stmt->close();

    // Close the connection
    $conn->close();
}
?>