<?php
$EOF = 3;
$port = 8080;

$serverSocket = createServerConnection($port);
socket_listen($serverSocket) or die("Unable to start server!");

echo "Server running on port $port\n";

// Keep track of all connected clients and their metadata
$listOfConnectedClients = [];
$clientInfo = []; // socket => ['screenname' => 'Eric', 'room' => 'Room1']

do {
    $clientsWithData = waitForIncomingMessageFromClients($listOfConnectedClients, $serverSocket);

    if (in_array($serverSocket, $clientsWithData)) {
        $newSocket = socket_accept($serverSocket);
        if (performHandshake($newSocket)) {
            $listOfConnectedClients[] = $newSocket;
            echo "New client connected. Total: " . count($listOfConnectedClients) . "\n";
        } else {
            disconnectClient($newSocket, $listOfConnectedClients, $clientInfo, $clientsWithData);
        }
    } else {
        foreach ($clientsWithData as $clientSocket) {
            $len = @socket_recv($clientSocket, $buffer, 1024, 0);
            if ($len === false || $len == 0) {
                disconnectClient($clientSocket, $listOfConnectedClients, $clientInfo, $clientsWithData);
            } else {
                $message = unmask($buffer);
                if (empty($message)) continue;

                $decoded = json_decode($message, true);
                if (!$decoded || !isset($decoded['action'])) continue;

                if ($decoded['action'] === 'join') {
                    $screenname = $decoded['screenname'] ?? 'anon';
                    $room = $decoded['room'] ?? 'lobby';
                    $clientInfo[(int)$clientSocket] = ['screenname' => $screenname, 'room' => $room];
                    echo "$screenname joined $room\n";
                }

                if ($decoded['action'] === 'message') {
                    $text = $decoded['text'] ?? '';
                    $room = $clientInfo[(int)$clientSocket]['room'] ?? '';
                    $sender = $clientInfo[(int)$clientSocket]['screenname'] ?? 'anon';

                    foreach ($listOfConnectedClients as $otherSocket) {
                        if ($otherSocket == $clientSocket) {
                            $out = json_encode(['from' => 'me', 'text' => $text]);
                        } else if (($clientInfo[(int)$otherSocket]['room'] ?? '') === $room) {
                            $out = json_encode(['from' => $sender, 'text' => $text]);
                        } else {
                            continue;
                        }
                        socket_write($otherSocket, mask($out), strlen($out));
                    }
                }
            }
        }
    }
} while (true);

// -------------------- Helper Functions --------------------

function createServerConnection($port, $host = 0) {
    $serverSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    socket_set_option($serverSocket, SOL_SOCKET, SO_REUSEADDR, 1);
    socket_bind($serverSocket, $host, $port);
    return $serverSocket;
}

function waitForIncomingMessageFromClients($clients, $serverSocket) {
    $readList = $clients;
    $readList[] = $serverSocket;
    $writeList = $exceptionList = [];
    socket_select($readList, $writeList, $exceptionList, null);
    return $readList;
}

function disconnectClient($clientSocket, &$listOfConnectedClients, &$clientInfo, &$clientsWithData) {
    if (($key = array_search($clientSocket, $clientsWithData)) !== false) {
        unset($clientsWithData[$key]);
    }
    if (($key = array_search($clientSocket, $listOfConnectedClients)) !== false) {
        unset($listOfConnectedClients[$key]);
        unset($clientInfo[(int)$clientSocket]);
    }
    socket_close($clientSocket);
    echo "Client disconnected\n";
}

function performHandshake($clientSocket) {
    $len = @socket_recv($clientSocket, $headers, 1024, 0);
    if ($len === false || $len == 0) return false;

    $headers = explode("\r\n", $headers);
    $headerArray = [];
    foreach ($headers as $header) {
        $parts = explode(": ", $header);
        if (count($parts) === 2) {
            $headerArray[$parts[0]] = $parts[1];
        }
    }

    if (!isset($headerArray['Sec-WebSocket-Key'])) return false;

    $secKey = $headerArray['Sec-WebSocket-Key'];
    $uuid = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
    $secAccept = base64_encode(pack('H*', sha1($secKey . $uuid)));

    $handshakeResponse = "HTTP/1.1 101 Switching Protocols\r\n" .
        "Upgrade: websocket\r\n" .
        "Connection: Upgrade\r\n" .
        "Sec-WebSocket-Accept: $secAccept\r\n\r\n";

    socket_write($clientSocket, $handshakeResponse, strlen($handshakeResponse));
    return true;
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

    $unmaskedtext = '';
    for ($i = 0; $i < strlen($data); ++$i) {
        $unmaskedtext .= $data[$i] ^ $masks[$i % 4];
    }
    return $unmaskedtext;
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
        for ($i = 0; $i < 8; $i++) {
            $frame[2 + $i] = ($length >> (56 - 8 * $i)) & 255;
        }
    }

    foreach (str_split($message) as $char) {
        $frame[] = ord($char);
    }

    return implode(array_map("chr", $frame));
}
