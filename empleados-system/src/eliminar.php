<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $sql = "UPDATE empleados SET estatus = 'inactivo' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Empleado desactivado correctamente."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al desactivar el empleado."]);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>