<?php
$fp = stream_socket_client("ssl://smtp.gmail.com:465", $errno, $errstr, 30, STREAM_CLIENT_CONNECT);
if (!$fp) {
    echo "ERROR: $errno - $errstr\n";
} else {
    echo "Connected to Gmail!\n";
    fclose($fp);
}