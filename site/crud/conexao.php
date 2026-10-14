<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'elarion';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_errno) {
    $conn = null;
}

if ($conn) {
    $conn->set_charset('utf8');
}
?>