<?php
require_once 'db_config.php';

if ($conn) {
    echo 'Conexión exitosa a la base de datos "' . $dbname . '"';
} else {
    echo 'Error de conexión: ' . $conn->connect_error;
}

$conn->close();
?>
