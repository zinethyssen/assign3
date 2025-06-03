<?php
session_start();
$loggedIn = isset($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Chat Room via PHP Web Sockets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <!-- Header -->
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
            <p>instructions</p>
        </div>
    </div>

    <!-- Signup Modal -->
    <div id="signupOverlay" style="display: none;" class="overlay">
        <div class="modal-box">
            <div class="close-btn" onclick="hideSignup()">[x]</div>
            <h2>Signup</h2>
            <form id="signupForm">
                <label>Username: <input type="text" name="username" required></label><br /><br />
                <label>Password: <input type="password" name="password" required></label><br /><br />
                <label>Screen Name: <input type="text" name="screenName" required></label><br /><br />
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
                <label>Username: <input type="text" name="username" required></label><br /><br />
                <label>Password: <input type="password" name="password" required></label><br /><br />
                <button type="submit">Log In</button>
            </form>
            <div id="loginResult" style="margin-top: 10px;"></div>
        </div>
    </div>

    <!-- Chatroom Loader -->
    <div id="chatroom"></div>

    <script>
    async function logout() {
        const res = await fetch('/actions/logout.php', { method: 'POST' });
        const result = await res.json();
        if (result.success) location.href = result.redirect;
    }

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

    async function submitRoom() {
        const name = document.getElementById("roomName").value.trim();
        const key = document.getElementById("roomKey").value.trim();

        const res = await fetch('/actions/createRoom.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, key })
        });

        const text = await res.text();
        const msgBox = document.getElementById("createRoomMsg");

        try {
            const result = JSON.parse(text);

            if (result.success) {
                msgBox.classList.remove("text-danger");
                msgBox.classList.add("text-success");
                msgBox.textContent = "Room created!";

                const row = document.createElement('div');
                row.className = 'd-flex text-center py-2 px-3 border-bottom';

                const nameDiv = document.createElement('div');
                nameDiv.className = 'flex-fill';
                nameDiv.textContent = name;

                const statusDiv = document.createElement('div');
                statusDiv.className = 'flex-fill';
                const img = document.createElement('img');
                img.src = key ? '../images/lock.png' : '../images/unlock.jpeg';
                img.alt = key ? 'Locked' : 'Unlocked';
                img.style.width = img.style.height = '20px';
                statusDiv.appendChild(img);

                const joinDiv = document.createElement('div');
                joinDiv.className = 'flex-fill';
                const btn = document.createElement('button');
                btn.className = 'btn btn-sm btn-primary';
                btn.textContent = 'Join';
                joinDiv.appendChild(btn);

                row.append(nameDiv, statusDiv, joinDiv);
                document.getElementById('room-list').appendChild(row);
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

    document.getElementById('signupForm')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const response = await fetch('actions/signup.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        document.getElementById('signupResult').textContent = result.message;
        if (result.success) setTimeout(() => location.reload(), 1000);
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
            const chatroomResponse = await fetch('chatroom.php');
            const chatroomHTML = await chatroomResponse.text();
            document.getElementById('chatroom').innerHTML = chatroomHTML;
            initWebSocket();
            hideLogin();

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

            container.querySelectorAll('script').forEach(oldScript => {
                try {
                    const newScript = document.createElement('script');
                    if (oldScript.src) {
                        newScript.src = oldScript.src;
                    } else if (oldScript.textContent.trim() !== '') {
                        newScript.textContent = oldScript.textContent;
                    } else {
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

    // === WebSocket logic for real-time chat ===
    function initWebSocket() {
        const ws = new WebSocket('ws://localhost:8080'); // change to your WebSocket server URL if different

        const chatMessages = document.getElementById('chatMessages');
        const chatForm = document.getElementById('chatForm');
        const inputMessage = document.getElementById('inputMessage');
        const roomList = document.getElementById('room-list');

        const username = <?php echo json_encode($loggedIn ? $_SESSION['username'] : ''); ?>;
        const screenName = <?php echo json_encode($loggedIn && isset($_SESSION['screenName']) ? $_SESSION['screenName'] : ''); ?>;

        let currentRoomId = null;

        ws.onopen = () => {
            console.log('WebSocket connected');
            fetchRooms();
        };

        ws.onclose = () => {
            console.log('WebSocket disconnected');
        };

        ws.onerror = (e) => {
            console.error('WebSocket error:', e);
        };

        ws.onmessage = (event) => {
            const data = JSON.parse(event.data);
            if (data.type === 'rooms') {
                roomList.innerHTML = '';
                data.rooms.forEach(room => {
                    const div = document.createElement('div');
                    div.className = 'd-flex text-center py-2 px-3 border-bottom';

                    const nameDiv = document.createElement('div');
                    nameDiv.className = 'flex-fill';
                    nameDiv.textContent = room.name;

                    const statusDiv = document.createElement('div');
                    statusDiv.className = 'flex-fill';
                    const img = document.createElement('img');
                    img.src = room.key ? '../images/lock.png' : '../images/unlock.jpeg';
                    img.alt = room.key ? 'Locked' : 'Unlocked';
                    img.style.width = img.style.height = '20px';
                    statusDiv.appendChild(img);

                    const joinDiv = document.createElement('div');
                    joinDiv.className = 'flex-fill';
                    const btn = document.createElement('button');
                    btn.className = 'btn btn-sm btn-primary';
                    btn.textContent = 'Join';
                    btn.addEventListener('click', () => joinRoom(room.id));
                    joinDiv.appendChild(btn);

                    div.append(nameDiv, statusDiv, joinDiv);
                    roomList.appendChild(div);
                });
            } else if (data.type === 'message' && data.roomId === currentRoomId) {
                const isMe = data.sender === screenName;
                const messageEl = document.createElement('div');
                messageEl.textContent = (isMe ? 'me: ' : data.sender + ': ') + data.message;
                chatMessages.appendChild(messageEl);
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        };

        function fetchRooms() {
            ws.send(JSON.stringify({ action: 'getRooms' }));
        }

        function joinRoom(roomId) {
            currentRoomId = roomId;
            ws.send(JSON.stringify({ action: 'join', roomId: roomId, screenName }));
            chatMessages.innerHTML = '';
        }

        chatForm?.addEventListener('submit', (e) => {
            e.preventDefault();
            if (!currentRoomId) return alert('Join a room first.');
            const msg = inputMessage.value.trim();
            if (!msg) return;
            ws.send(JSON.stringify({
                action: 'message',
                roomId: currentRoomId,
                sender: screenName,
                message: msg
            }));
            inputMessage.value = '';
        });
    }

    <?php if ($loggedIn): ?>
        window.onload = () => {
            fetch('chatroom.php')
                .then(res => res.text())
                .then(html => {
                    document.getElementById('chatroom').innerHTML = html;
                    initWebSocket();
                });
        };
    <?php endif; ?>
    </script>

</body>
</html>
