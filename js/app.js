let socket = new WebSocket("ws://" + window.location.hostname + ":8090");

socket.onopen = () => console.log("✅ Connected to WebSocket server");

socket.onmessage = event => {
    const chatBox = document.getElementById("chatMessages");
    const msg = document.createElement("div");
    msg.innerHTML = "<strong>Server:</strong> " + event.data;
    chatBox.appendChild(msg);
};

socket.onclose = () => console.log("❌ Disconnected from WebSocket server");

document.getElementById("chatForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const input = document.getElementById("chatInput");
    const message = input.value.trim();
    if (message && socket.readyState === WebSocket.OPEN) {
        socket.send(message);
        const msg = document.createElement("div");
        msg.innerHTML = "<strong>Me:</strong> " + message;
        chatBox.appendChild(msg);
        input.value = "";
    }
});
