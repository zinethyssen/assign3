<?php

// Database Server info
$host = 'localhost';
$username = 'root';
$password = ''; 
$database = 'chatroom'; 

// Create a new connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
