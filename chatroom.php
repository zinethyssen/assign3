<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat Room</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"> <!-- if you have one -->
</head>
<body>

    <!-- Check login status-->
    <?php
    session_start();
    if (!isset($_SESSION['username'])) {
        header('Location: index.php');
        exit();
    }

    $loggedIn = true; // Since user passed the session check
    ?>
<!-- Header with app title, authors and nav bar -->
     <header class="border-top border-bottom p-2">
			<div class="text-center fw-bold fs-5">Chat room via PHP web sockets</div>
			<div class="container">
				<div class="row align-items-center mt-1">
					<div class="col-4"></div>
					<div class="col-4 text-center small">By: Eric Locke and Zinet Hyssen</div>
					<div class="col-4 text-end small">

					<span class="me-2 text-primary" role="button" onclick="showHelp()">Help</span>
					<?php if (!$loggedIn): ?>
						<span id="signup" class="me-2 text-primary" role="button" onclick="showSignup()">Signup</span>
						<span class="text-primary" role="button" onclick="showLogin()">Login</span>
					<?php else: ?>
						<span class="text-primary" role="button" onclick="logout()">Logout</span>
					<?php endif; ?>
					</div>
				</div>
			</div>
			</header>

<!--  UI starts here -->
<div class="container-fluid mt-3">
    <div class="row" style="height: 80vh;">
        <!-- Left panel -->
        <div class="col-3 border-end overflow-auto">
            <div class="d-flex justify-content-between align-items-center px-3 mt-3">
                <h5 class="mb-0">Available Rooms</h5>
                <div id="add-room" style="cursor: pointer; font-size: 1.5rem;">+</div>
            </div>

            <div class="d-flex text-center fw-bold border-bottom py-2 px-3">
                <div class="flex-fill">Room Name</div>
                <div class="flex-fill">Status</div>
                <div class="flex-fill">Join</div>
            </div>
            <div id="room-list"></div>
            <div id="overlay-container"></div>
        </div>

        <!-- Right panel -->
        <div class="col-9 d-flex flex-column">
            <div id="chatroom-name" style="margin: 0 auto;">Chat Room Name</div>
            <div id="chatMessages" class="border rounded p-2 mb-2 overflow-auto flex-grow-1" style="background: #f8f9fa;">
                <!-- Chat messages will go here -->
                <div><strong>User1:</strong> Hello!</div>
                <div><strong>User2:</strong> Hi there!</div>
            </div>
            <form id="chatForm" class="d-flex">
                <input type="text" id="chatInput" class="form-control me-2" placeholder="Type a message..." required>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </div>
    </div>
</div>

<script src="js/app.js"></script>

</body>
</html>
