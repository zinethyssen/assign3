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
						<span id="signup" class="me-2 text-primary" role="button" onclick="showSignup()">Signup</span>
						<span class="text-primary" role="button" onclick="showLogin()">Login</span>
					<?php else: ?>
						<span class="text-primary" role="button" onclick="logout()">Logout</span>
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
					Welcome! After signing up and logging in, please feel free to create a new chat room by clicking the "+" symbol next to available rooms or join an existing room that is listed below that line. You can then participate in messaging in the chatroom on the right side by entering your message in the text box and hitting send.
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
		</div>

		<!-- chatroom load in -->
		<div id="chatroom" style="display: <?= isset($_SESSION['username']) ? 'block' : 'none' ?>;">
			<div class="container-fluid mt-3">
				<div class="row" style="height: 80vh;">
					<!-- Left panel: list of chat rooms -->
					<div class="col-3 border-end overflow-auto">
						<div class="d-flex justify-content-between align-items-center px-3 mt-3">
							<h5 class="mb-0">Available Rooms</h5>
							<div id="add-room" style="cursor: pointer; font-size: 1.5rem; line-height: 1;">+</div>
						</div>

						<div class="d-flex text-center fw-bold border-bottom py-2 px-3">
							<div class="flex-fill">Room Name</div>
							<div class="flex-fill">Status</div>
							<div class="flex-fill">Join</div>
						</div>
						<div id="room-list"></div>
						<div id="overlay-container"></div>
					</div>

					<!-- Right panel: chat messages and input -->
					<div class="col-9 d-flex flex-column">
						<div id="chatroom-name" style="margin: 0 auto; width: fit-content;">Chat Room Name</div>
						<div id="chatMessages" class="border rounded p-2 mb-2 overflow-auto flex-grow-1" style="background: #f8f9fa;">
							<!-- Chat messages will go here -->
						</div>
						<form id="chatForm" class="d-flex">
							<input type="text" id="chatInput" class="form-control me-2" placeholder="Type a message..." autocomplete="off" required>
							<button type="submit" id="sendbtn" class="btn btn-primary">Send</button>
						</form>
					</div>
				</div>
			</div>
		</div>

		<script>
		const sessionData = <?= json_encode($_SESSION) ?>;
		console.log('Session data:', sessionData);

		var socket = false;
        let screenName = sessionData.screenName;
        let roomName = "";

		async function logout() {
			const res = await fetch('/actions/logout.php', { method: 'POST' });
			const result = await res.json();
			if (result.success) {
			location.href = result.redirect;
			}
			hideChatroom();
		}

		fetch('/actions/getRooms.php')
			.then(res => res.json())
			.then(rooms => {
			const container = document.getElementById('room-list');
			container.innerHTML = '';
			rooms.forEach(room => {
				const div = document.createElement('div');
				div.className = 'd-flex text-center py-2 px-3 border-bottom';

				const nameDiv = document.createElement('div');
				nameDiv.className = 'flex-fill';
				nameDiv.textContent = room.name;
				div.appendChild(nameDiv);

				const lockDiv = document.createElement('div');
				lockDiv.className = 'flex-fill';
				const img = document.createElement('img');
				img.src = room.locked ? '../images/lock.png' : '../images/unlock.jpeg';
				img.alt = room.locked ? 'Locked' : 'Unlocked';
				img.style.width = '20px';
				img.style.height = '20px';
				lockDiv.appendChild(img);
				div.appendChild(lockDiv);

				const btnDiv = document.createElement('div');
				btnDiv.className = 'flex-fill';
				const btn = document.createElement('button');
				btn.className = 'btn btn-sm btn-primary';
				btn.textContent = 'Join';
				btnDiv.appendChild(btn);
				div.appendChild(btnDiv);

				container.appendChild(div);
			});
			setupRoomJoinListener();
			})
			.catch(console.error);
///////////////////////////////////////////////////////////////
		function connectToServer(selectedRoom) {
            if (socket) {
                alert("Already connected");
                return;
            }


            roomName = selectedRoom;  // set room from button

            socket = new WebSocket("ws://localhost:8080");


			socket.onopen = function(event) {
			screenName = sessionData.screenName;
			if (!screenName) {       
				alert("Missing screen name in session.");
				return;
			}
			socket.send(`/name ${screenName}`);


				const joinMessage = {
					type: "join",
					screenname: screenName,
					room: roomName
				};

				socket.send(JSON.stringify(joinMessage));

				document.getElementById("chatMessages").innerHTML = `<div style="color:red">Connected to server, joined room: ${roomName}</div>`;
			};


            socket.onclose = function(event) {
                document.getElementById("chatMessages").innerHTML += "<div>Disconnected from server</div>";
                socket = null;

            };

            socket.onmessage = function(event) {
                // Parse JSON and only show messages for this room
                try {
                    const data = JSON.parse(event.data);
                    if (data.room === roomName) {
                        let displayMsg = "";
                        if (data.type === "message") {
                            const sender = data.screenname === screenName ? "me" : data.screenname;
                            displayMsg = `<div style="color:blue">${sender}: ${data.message}</div>`;
                        } else if (data.type === "join") {
                            displayMsg = `<div style="color:green">${data.screenname} joined the room.</div>`;
                        }
                        document.getElementById("chatMessages").innerHTML += displayMsg;
                    }
                } catch(e) {
                    // fallback if message is not JSON
                    document.getElementById("chatMessages").innerHTML += `<div>${event.data}</div>`;
                }
            };

            socket.onerror = function(e) {
                console.error("WebSocket error", e);
            };
        }


		function disconnectFromServer() {
            if (socket) socket.close();
            socket = false;
            document.getElementById("chatMessages").innerHTML += `<div style="color:red">Disconnected from server</div>`;
        }


		function sendMessage() {
            if (!socket) {
                alert("Connect first");
                return;
            }

            const messageText = document.getElementById("chatInput").value.trim();
            if (messageText.length === 0) {
                alert("Can't send empty string");
                return;
            }

            const chatMessage = {
                type: "message",
                room: roomName,
                screenname: screenName,
                message: messageText
            };

            socket.send(JSON.stringify(chatMessage));
            // document.getElementById("chatMessages").innerHTML += `<div style="color:green">me: ${messageText}</div>`;
            document.getElementById("chatInput").value = "";
        }
///////////////////////////////////////////

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

		function hideChatroom() {
			document.getElementById('chatroom').style.display = 'none';
		}
		function showChatroom() {
			document.getElementById('chatroom').style.display = 'flex';
		}

		async function submitRoom() {
			const name = document.getElementById("roomName").value.trim();
			const key = document.getElementById("roomKey").value.trim();

			const res = await fetch('/actions/createRoom.php', {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				body: JSON.stringify({ name, key })
			});

			const text = await res.text();
			console.log('Server response:', text);

			try {
				const result = JSON.parse(text);
				const msgBox = document.getElementById("createRoomMsg");

				if (result.success) {
					msgBox.classList.remove("text-danger");
					msgBox.classList.add("text-success");
					msgBox.textContent = "Room created!";

					// Fetch latest room list from server (database-backed)
					await updateRooms();

					setupRoomJoinListener();

					// Close the overlay form
					document.getElementById('overlay-container').innerHTML = '';
				} else {
					msgBox.classList.remove("text-success");
					msgBox.classList.add("text-danger");
					msgBox.textContent = result.error || "Failed to create room.";
				}
			} catch (err) {
				console.error('Invalid JSON from server:', err);
			}
		}

		// Call this once after login or page load to handle "Join" button logic
		function setupRoomJoinListener() {
			document.getElementById('room-list').addEventListener('click', (e) => {
				if (e.target.tagName === 'BUTTON') {
					const button = e.target;
					const roomDiv = button.closest('div.d-flex');
					const roomName = roomDiv?.querySelector('div.flex-fill')?.textContent.trim();

					if (button.textContent === 'Join') {
						connectToServer(roomName);
						console.log(`Joining room: ${roomName} as ${screenName}`);
						button.textContent = 'Leave';
						button.className = 'btn btn-sm btn-danger';
					} else if (button.textContent === 'Leave') {
						disconnectFromServer(roomName);
						console.log(`Left room: ${roomName}`);
						button.textContent = 'Join';
						button.className = 'btn btn-sm btn-primary';
					}
				}
			});
		}

		// Fetch the current list of chatrooms from the server
		async function updateRooms() {
			const res = await fetch('/actions/getRooms.php');
			const rooms = await res.json();
			const container = document.getElementById('room-list');
			container.innerHTML = '';
			rooms.forEach(room => {
				const div = document.createElement('div');
				div.className = 'd-flex text-center py-2 px-3 border-bottom';

				const nameDiv = document.createElement('div');
				nameDiv.className = 'flex-fill';
				nameDiv.textContent = room.name;
				div.appendChild(nameDiv);

				const lockDiv = document.createElement('div');
				lockDiv.className = 'flex-fill';
				const img = document.createElement('img');
				img.src = room.locked ? '../images/lock.png' : '../images/unlock.jpeg';
				img.alt = room.locked ? 'Locked' : 'Unlocked';
				img.style.width = '20px';
				img.style.height = '20px';
				lockDiv.appendChild(img);
				div.appendChild(lockDiv);

				const btnDiv = document.createElement('div');
				btnDiv.className = 'flex-fill';
				const btn = document.createElement('button');
				btn.className = 'btn btn-sm btn-primary';
				btn.textContent = 'Join';
				btnDiv.appendChild(btn);
				div.appendChild(btnDiv);

				container.appendChild(div);
			});
		}

		document.getElementById('chatroom').addEventListener('click', async (e) => { // sending messages
			if (e.target.id === 'sendbtn') {
				sendMessage();
			}
		});


		

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
				showChatroom();
				await updateRooms();
				setupRoomJoinListener();
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
				window.screenName = result.screenName;
				hideLogin();
				showChatroom();
				await updateRooms();
				setupRoomJoinListener();

				const loginSpan = document.querySelector('span.text-primary[onclick^="showLogin"]');
				if (loginSpan) {
					loginSpan.onclick = logout;
					loginSpan.innerHTML = 'Logout';
				}
				document.getElementById('signup').style.display = 'none';
			}
		});

		document.getElementById('chatroom').addEventListener('click', async (e) => {
			if (e.target.id === 'add-room') {
			const response = await fetch('/actions/newRoom.php');
			const html = await response.text();
			const container = document.getElementById('overlay-container');
			container.innerHTML = html;

			// Run scripts embedded in fetched HTML
			container.querySelectorAll('script').forEach(oldScript => {
			try {
				const newScript = document.createElement('script');
				if (oldScript.src) {
				newScript.src = oldScript.src;
				} else if (oldScript.textContent.trim() !== '') {
				newScript.textContent = oldScript.textContent;
				} else {
				// Skip empty scripts
				return;
				}
				document.body.appendChild(newScript);
				oldScript.remove();
			} catch (e) {
				console.error('Failed to inject script:', e);
			}
			});

			}
		});
		</script>

    
</body>
</html>