<!-- chatroom-ui.php -->
<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
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
                <div><strong>User1:</strong> Hello!</div>
                <div><strong>User2:</strong> Hi there!</div>
            </div>
            <form id="chatForm" class="d-flex">
                <input type="text" id="inputMessage" class="form-control me-2" placeholder="Type a message..." autocomplete="off" required>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </div>
    </div>
</div>
</html>
