<?php
session_start();
require_once 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $rfc = $_POST['rfc'];
    $estatus = $_POST['estatus'];

    $correo = strtolower(str_replace(' ', '.', $nombre)) . '@empresa.com';

    $contrasena = password_hash('defaultpassword', PASSWORD_DEFAULT);

    if (strlen($nombre) < 3 || strlen($nombre) > 100) {
        $error = "El nombre debe tener entre 3 y 100 caracteres.";
    } elseif (strlen($telefono) != 10) {
        $error = "El teléfono debe tener 10 caracteres.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Correo no válido.";
    } elseif (strlen($rfc) != 13) {
        $error = "El RFC debe tener 13 caracteres.";
    }

    if (!isset($error)) {
        $sql_usuario = "INSERT INTO usuarios (nombre, correo, contrasena, tipo_usuario) 
                        VALUES ('$nombre', '$correo', '$contrasena', 'empleado')";
        if ($conn->query($sql_usuario) === TRUE) {
            $usuario_id = $conn->insert_id;

            $sql_empleado = "INSERT INTO empleados (usuario_id, nombre, telefono, correo, fecha_nacimiento, rfc, estatus) 
                             VALUES ('$usuario_id', '$nombre', '$telefono', '$correo', '$fecha_nacimiento', '$rfc', '$estatus')";
            if ($conn->query($sql_empleado) === TRUE) {
                header("Location: index.php");
                exit;
            } else {
                $error = "Error al insertar el empleado: " . $conn->error;
            }
        } else {
            $error = "Error al insertar el usuario: " . $conn->error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Empleado</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Registrar Nuevo Empleado</h2>

    <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>

    <form action="registro.php" method="POST">
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required minlength="3" maxlength="100">
        </div>
        <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" required minlength="10" maxlength="10">
        </div>
        <div class="form-group">
            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
        </div>
        <div class="form-group">
            <label for="rfc">RFC</label>
            <input type="text" class="form-control" id="rfc" name="rfc" required minlength="13" maxlength="13">
        </div>
        <div class="form-group">
            <label for="estatus">Estatus</label>
            <select class="form-control" id="estatus" name="estatus" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Registrar</button>
    </form>

    <a href="index.php" class="btn btn-secondary mt-3">Volver a la lista</a>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>

<?php $conn->close(); ?>