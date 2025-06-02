<?php
session_start();
$loggedIn = isset($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Room via PHP Web Sockets</title>
	 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	 <link rel="stylesheet" href="css/style.css">

</head>
<body>
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
						<span class="me-2 text-primary" role="button" onclick="showSignup()">Signup</span>
						<span class="text-primary" role="button" onclick="showLogin()">login</span>
					<?php else: ?>
						<span class="text-primary" role="button" onclick="location.href='logout.php'">logout</span>
					<?php endif; ?>
					</div>
				</div>
			</div>
			</header>
	  
	  <!-- Help Overlay -->
		<div id="helpOverlay">
			<div id="helpBox">
				<div id="closeHelp" onclick="hideHelp()">[x]</div>
				<h2>Chat Room Instructions</h2>
				<p>
					instructions
				</p>
			</div>
		</div>

		<!-- Signup Modal -->
		<div id="signupOverlay" style="display: none;" class="overlay">
		<div class="modal-box">
			<div class="close-btn" onclick="hideSignup()">[x]</div>
			<h2>Signup</h2>
			<form id="signupForm">
				<label>Username: <input type="text" name="username" required></label><br><br>
				<label>Password: <input type="password" name="password" required></label><br><br>
				<label>Screen Name: <input type="text" name="screenName" required></label><br><br>
				<button type="submit">Sign Up</button>
			</form>
			<div id="signupResult" style="margin-top: 10px;"></div>
		</div>
		</div>

		<!-- Login Modal -->
			<div id="loginOverlay" style="display: none;" class="overlay">
			<div class="modal-box">
				<div class="close-btn" onclick="hideLogin()">[x]</div>
				<h2>Login</h2>
				<form id="loginForm">
					<label>Username: <input type="text" name="username" required></label><br><br>
					<label>Password: <input type="password" name="password" required></label><br><br>
					<button type="submit">Log In</button>
				</form>
				<div id="loginResult" style="margin-top: 10px;"></div>
			</div>


		<script>
			function showHelp() {
				document.getElementById('helpOverlay').style.display = 'flex';
			}

			function hideHelp() {
				document.getElementById('helpOverlay').style.display = 'none';
			}

			function showSignup() {
				document.getElementById('signupOverlay').style.display = 'flex';
			}

			function hideSignup() {
				document.getElementById('signupOverlay').style.display = 'none';
			}

			function showLogin() {
				document.getElementById('loginOverlay').style.display = 'flex';
			}

			function hideLogin() {
				document.getElementById('loginOverlay').style.display = 'none';
			}

			document.getElementById('signupForm')?.addEventListener('submit', async (e) => {
				e.preventDefault();
				const formData = new FormData(e.target);
				const response = await fetch('actions/signup.php', {
					method: 'POST',
					body: formData
				});
				const result = await response.json();
				document.getElementById('signupResult').textContent = result.message;
				if (result.success) {
					setTimeout(() => location.reload(), 1000);
				}
			});

			document.getElementById('loginForm')?.addEventListener('submit', async (e) => {
				e.preventDefault();
				const formData = new FormData(e.target);
				const response = await fetch('actions/login.php', {
					method: 'POST',
					body: formData
				});
				const result = await response.json();
				document.getElementById('loginResult').textContent = result.message;
				if (result.success) {
					setTimeout(() => location.reload(), 1000);
				}
			});
			</script>

    
</body>
</html>