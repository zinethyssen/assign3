<?php session_start(); ?>
<div id="overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;
     background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; z-index: 1000;">
  <div style="background: white; padding: 20px; border-radius: 10px; min-width: 300px;">
    <h4>Create New Room</h4>
    <input type="text" id="roomName" class="form-control mb-2" placeholder="Room Name">
    <input type="text" id="roomKey" class="form-control mb-2" placeholder="Key (optional)">
    <div class="d-flex justify-content-end">
      <button class="btn btn-secondary me-2" onclick="document.getElementById('overlay').remove()">Cancel</button>
      <button class="btn btn-primary" onclick="submitRoom()">Create</button>
    </div>
    <div id="createRoomMsg" class="mt-2 text-danger"></div>
  </div>
</div>