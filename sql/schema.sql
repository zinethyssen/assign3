-- Create the database tables if they are not currently on the server
-- Create the users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    screenName VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Create the list of chatrooms table
CREATE TABLE list_of_chatrooms (
    chatroomName VARCHAR(50) PRIMARY KEY,
    chatroomKey VARCHAR(50),
    creatorUsername VARCHAR(50)
);

-- Create the table to keep track of the room occupants
CREATE TABLE current_chatroom_occupants (
    chatroomName VARCHAR(50),
    screenName VARCHAR(50)
);
