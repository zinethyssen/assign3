<!-- chatroom-ui.php -->

<div class="container-fluid mt-3">
    <div class="row" style="height: 80vh;">
        <!-- Left panel: list of chat rooms -->
        <div class="col-3 border-end overflow-auto d-flex justify-content-center" style="gap: 10px;">
            <h5 class="mt-3 mb-0">Available Rooms</h5>
            <div id="add-room" style="cursor: pointer; font-size: 1.5rem; line-height: 1;">+</div>
        </div>

        <!-- Right panel: chat messages and input -->
        <div class="col-9 d-flex flex-column">
            <div id="chatMessages" class="border rounded p-2 mb-2 overflow-auto flex-grow-1" style="background: #f8f9fa;">
                <!-- Chat messages will go here -->
                <div><strong>User1:</strong> Hello!</div>
                <div><strong>User2:</strong> Hi there!</div>
            </div>
            <form id="chatForm" class="d-flex">
                <input type="text" id="chatInput" class="form-control me-2" placeholder="Type a message..." autocomplete="off" required>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </div>
    </div>
</div>
