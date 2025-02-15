<?php
session_start();
require_once 'db_config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $rfc = trim($_POST['rfc']);
    $estatus = $_POST['estatus'];
    $vacante = $_POST['vacante'];

    if (strlen($nombre) < 3 || strlen($nombre) > 100) {
        $error = "El nombre debe tener entre 3 y 100 caracteres.";
    } elseif (!preg_match('/^\d{10}$/', $telefono)) {
        $error = "El teléfono debe tener exactamente 10 dígitos numéricos.";
    } elseif (strlen($rfc) != 13) {
        $error = "El RFC debe tener 13 caracteres.";
    } else {
        $fecha_actual = new DateTime();
        $fecha_nacimiento_dt = new DateTime($fecha_nacimiento);
        $edad = $fecha_actual->diff($fecha_nacimiento_dt)->y;

        if ($edad < 18) {
            $error = "El empleado debe ser mayor de 18 años.";
        }
    }

    if (empty($error)) {
        $nombre_correo = strtolower(str_replace(' ', '.', $nombre));
        $correo = "$nombre_correo@empresa.com";
        $rfc_part = strtoupper(substr($rfc, 0, 3));
        $contrasena = "Empresa2024$rfc_part";
        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

        $sql_check_empleado = "SELECT id FROM empleados WHERE correo = ?";
        $stmt_check_empleado = $conn->prepare($sql_check_empleado);
        $stmt_check_empleado->bind_param("s", $correo);
        $stmt_check_empleado->execute();
        $stmt_check_empleado->store_result();

        if ($stmt_check_empleado->num_rows > 0) {
            $error = "Este correo ya está registrado en empleados.";
        } else {
            $sql_check_usuario = "SELECT id FROM usuarios WHERE correo = ?";
            $stmt_check_usuario = $conn->prepare($sql_check_usuario);
            $stmt_check_usuario->bind_param("s", $correo);
            $stmt_check_usuario->execute();
            $stmt_check_usuario->store_result();

            if ($stmt_check_usuario->num_rows > 0) {
                $stmt_check_usuario->bind_result($usuario_id);
                $stmt_check_usuario->fetch();
            } else {
                $sql_usuario = "INSERT INTO usuarios (nombre, correo, contrasena, tipo_usuario) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql_usuario);
                $stmt->bind_param("ssss", $nombre, $correo, $contrasena_hash, $vacante);

                if ($stmt->execute()) {
                    $usuario_id = $conn->insert_id;
                } else {
                    $error = "Error al registrar el usuario: " . $stmt->error;
                }
            }

            if (!isset($error)) {
                $sql_empleado = "INSERT INTO empleados (usuario_id, nombre, telefono, correo, fecha_nacimiento, rfc, estatus, vacante) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_empleado = $conn->prepare($sql_empleado);
                $stmt_empleado->bind_param("isssssss", $usuario_id, $nombre, $telefono, $correo, $fecha_nacimiento, $rfc, $estatus, $vacante);

                if ($stmt_empleado->execute()) {
                    header("Location: empleados.php");
                    exit;
                } else {
                    $error = "Error al insertar empleado: " . $stmt_empleado->error;
                }
            }
        }

        $stmt_check_usuario->close();
        $stmt_check_empleado->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Empleado</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let fechaActual = new Date();
            let anioMin = fechaActual.getFullYear() - 18;
            let mes = String(fechaActual.getMonth() + 1).padStart(2, '0');
            let dia = String(fechaActual.getDate()).padStart(2, '0');
            let fechaMinima = anioMin + "-" + mes + "-" + dia;
            document.getElementById("fecha_nacimiento").setAttribute("max", fechaMinima);
        });
    </script>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Registrar Nuevo Empleado</h2>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <form action="registro.php" method="POST">
        <div class="form-group">
            <label for="nombre">Nombre Completo</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required minlength="3" maxlength="100">
        </div>
        <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" required pattern="\d{10}" title="Debe contener exactamente 10 números">
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
        <div class="form-group">
            <label for="vacante">Vacante</label>
            <select class="form-control" id="vacante" name="vacante" required>
                <option value="admin">Administrador</option>
                <option value="empleado">Empleado</option>
                <option value="ventas">Ventas</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Registrar</button>
    </form>
    <a href="empleados.php" class="btn btn-secondary mt-3">Volver a la lista</a>
</div>
</body>
</html>

<?php $conn->close(); ?>
