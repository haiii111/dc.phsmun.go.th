<?php
$host = 'db';
$port = 3306;
$dbname = 'dctest';
$user = 'root';
$pass = 'root';

$conn = new mysqli($host, $user, $pass, $dbname, $port);
if ($conn->connect_error) {
  echo "MYSQLI ERROR: " . $conn->connect_error;
  exit;
}

$result = $conn->query("SELECT NOW()");
if (!$result) {
  echo "MYSQLI ERROR: " . $conn->error;
  exit;
}

$row = $result->fetch_row();
echo "MYSQLI OK\n";
echo $row[0];
$result->free();
$conn->close();
