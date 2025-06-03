<?php
// Create and listen to server connection
$EOF = 3;
$port = 8090;
$serverSocket = createServerConnection($port);
socket_listen($serverSocket) or die("Unable to start server, exiting!");
echo "Server now running on port $port\n";

// Check for incoming messages or connect/disconnect requests
$listOfConnectedClients = [];
$connectedClientsHandshakes = [];

// handshake is a mechanism by which the server and the connecting clients introduce each other,
// authenticate and establish how they want to communicate/the rules.

do {
    $clientsWithData = waitForIncomingMessageFromClients($listOfConnectedClients, $serverSocket);

    // Do we have a connection request – i.e. serverSocket is one of the clients with data?
    if (in_array($serverSocket, $clientsWithData)) {
        $newSocket = socket_accept($serverSocket);
        if (performHandshake($newSocket)) {
            $listOfConnectedClients[] = $newSocket;
            echo "connected. #clients: " . count($listOfConnectedClients) . "\n";
        } else {
            disconnectClient($newSocket, $listOfConnectedClients, $connectedClientsHandshakes, $clientsWithData);
        }
    } else {
        // must be regular data message or disconnect message
        foreach ($clientsWithData as $clientSocket) {
            $len = @socket_recv($clientSocket, $buffer, 1024, 0);
            if ($len === false || $len === 0 || strlen($message = unmask($buffer)) == 0 || ord($message[0]) == $EOF) {
                disconnectClient($clientSocket, $listOfConnectedClients, $connectedClientsHandshakes, $clientsWithData);
            } else {
                if (!empty($message)) {
                    // if the message is a JSON string, this is where to convert it
                    echo "Received: >" . $message . "<\n";

                    // Broadcast message to OTHER clients
                    foreach ($listOfConnectedClients as $client) {
                        if ($client != $clientSocket) {
                            sendMessage($client, $message);
                        }
                    }
                }
            }
        }
    }
} while (true);


// --- Helper functions ---

function createServerConnection($port, $host='0.0.0.0') {
    $serverSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_set_option($serverSocket, SOL_SOCKET, SO_REUSEADDR, 1);
    socket_bind($serverSocket, $host, $port);
    return $serverSocket;
}

function waitForIncomingMessageFromClients($clients, $serverSocket) {
    $readList = $clients;
    $readList[] = $serverSocket;
    $writeList = $exceptionList = [];
    socket_select($readList, $writeList, $exceptionList, NULL);
    return $readList;
}

function disconnectClient($clientSocket, &$listOfConnectedClients, &$connectedClientsHandshakes, &$clientsWithData) {
    if (($clientKey = array_search($clientSocket, $clientsWithData)) !== false) {
        unset($clientsWithData[$clientKey]);
    }
    if (($clientKey = array_search($clientSocket, $listOfConnectedClients)) !== false) {
        unset($listOfConnectedClients[$clientKey]);
        unset($connectedClientsHandshakes[$clientKey]);
        echo "disconnected client\n";
    }
    socket_close($clientSocket);
}

    function performHandshake($clientSocket) {
        $data = socket_recv($clientSocket, $headers, 1024, 0);
        if ($data === false || $data == 0) return false;
        $headers = parseHeaders($headers);

        echo "Performing handshake...\n";

        if (!isset($headers['Sec-WebSocket-Key'])) return false;
            $secKey = $headers['Sec-WebSocket-Key'];
            $secAccept = base64_encode(sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
            $handshakeResponse = "HTTP/1.1 101 Switching Protocols\r\n" .
                                "Upgrade: websocket\r\n" .
                                "Connection: Upgrade\r\n" .
                                "Sec-WebSocket-Accept: $secAccept\r\n\r\n";
            socket_write($clientSocket, $handshakeResponse, strlen($handshakeResponse));
            
            echo "Handshake response sent\n";

            return true;

}

function parseHeaders($headers) {
    $headers = explode("\r\n", $headers);
    $headerArray = [];
    foreach ($headers as $header) {
        $parts = explode(": ", $header);
        if (count($parts) === 2) {
            $headerArray[$parts[0]] = $parts[1];
        }
    }
    return $headerArray;
}

function unmask($payload) {
    if (strlen($payload) == 0) return "";
    $length = ord($payload[1]) & 127;
    if ($length == 126) {
        $masks = substr($payload, 4, 4);
        $data = substr($payload, 8);
    } elseif ($length == 127) {
        $masks = substr($payload, 10, 4);
        $data = substr($payload, 14);
    } else {
        $masks = substr($payload, 2, 4);
        $data = substr($payload, 6);
    }

    $unmaskedText = '';
    for ($i = 0; $i < strlen($data); ++$i) {
        $unmaskedText .= $data[$i] ^ $masks[$i % 4];
    }
    return $unmaskedText;
}

function mask($message) {
    $frame = [];
    $frame[0] = 129;

    $length = strlen($message);
    if ($length <= 125) {
        $frame[1] = $length;
    } elseif ($length <= 65535) {
        $frame[1] = 126;
        $frame[2] = ($length >> 8) & 255;
        $frame[3] = $length & 255;
    } else {
        $frame[1] = 127;
        $frame[2] = ($length >> 56) & 255;
        $frame[3] = ($length >> 48) & 255;
        $frame[4] = ($length >> 40) & 255;
        $frame[5] = ($length >> 32) & 255;
        $frame[6] = ($length >> 24) & 255;
        $frame[7] = ($length >> 16) & 255;
        $frame[8] = ($length >> 8) & 255;
        $frame[9] = $length & 255;
    }

    foreach (str_split($message) as $char) {
        $frame[] = ord($char);
    }

    return implode(array_map("chr", $frame));
}

function sendMessage($client, $message) {
    $message = mask($message);
    socket_write($client, $message, strlen($message));
}
