<?php
session_start();
require_once 'db_config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);
    $contrasena = $_POST['contrasena'];

    if (!empty($correo) && !empty($contrasena)) {
        // Consulta SQL con LEFT JOIN para obtener el estatus de empleados
        $sql = "
            SELECT u.id, u.nombre, u.correo, u.contrasena, u.tipo_usuario, 
                   COALESCE(e.estatus, 'activo') as estatus
            FROM usuarios u
            LEFT JOIN empleados e ON u.id = e.usuario_id
            WHERE u.correo = ?";

        $stmt = $conn->prepare($sql);

        // Verificar si la consulta se preparó correctamente
        if (!$stmt) {
            die("Error en la consulta SQL: " . $conn->error);
        }

        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $nombre, $correo, $hash, $tipo_usuario, $estatus);
            $stmt->fetch();

            // Verificar si el usuario está activo
            if ($estatus !== 'activo') {
                $error = "Tu cuenta está inactiva. Contacta al administrador.";
            } elseif (password_verify($contrasena, $hash)) {
                // Iniciar sesión
                $_SESSION['usuario_id'] = $id;
                $_SESSION['nombre'] = $nombre;
                $_SESSION['tipo_usuario'] = $tipo_usuario;

                // Redirigir según el tipo de usuario
                if ($tipo_usuario === 'admin') {
                    header("Location: empleados.php"); // Exclusivo para administradores
                } else {
                    header("Location: main_empleados.php"); // Para empleados y ventas
                }
                exit;
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "El correo no está registrado.";
        }
        $stmt->close();
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header text-center">
                    <img src="https://cdn.freebiesupply.com/logos/large/2x/php-1-logo-png-transparent.png" alt="Logo" width="100">
                    <h3 class="mt-2">Iniciar Sesión</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    <form action="login.php" method="POST">
                        <div class="form-group">
                            <label for="correo">Correo Electrónico</label>
                            <input type="email" class="form-control" id="correo" name="correo" required>
                        </div>
                        <div class="form-group">
                            <label for="contrasena">Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="mostrarContrasena">
                                        👁️
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById("mostrarContrasena").addEventListener("click", function() {
        let campo = document.getElementById("contrasena");
        campo.type = campo.type === "password" ? "text" : "password";
    });
</script>
</body>
</html>