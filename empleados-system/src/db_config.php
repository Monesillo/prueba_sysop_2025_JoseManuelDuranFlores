<?php
$host = 'mysql-container';
$username = 'admin';
$password = 'admin123';
$dbname = 'empleados_db';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die('Conexión fallida: ' . $conn->connect_error);
}
?>
