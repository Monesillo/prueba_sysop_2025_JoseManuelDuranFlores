<?php
session_start();
require_once 'db_config.php';

if (isset($_GET['id'])) {
    $empleado_id = $_GET['id'];

    $sql = "SELECT * FROM empleados WHERE id = $empleado_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $empleado = $result->fetch_assoc();
    } else {
        echo "Empleado no encontrado.";
        exit;
    }
} else {
    echo "ID no proporcionado.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $correo = $_POST['correo'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $rfc = $_POST['rfc'];
    $estatus = $_POST['estatus'];

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
        $sql_update = "UPDATE empleados SET 
            nombre = '$nombre',
            telefono = '$telefono',
            correo = '$correo',
            fecha_nacimiento = '$fecha_nacimiento',
            rfc = '$rfc',
            estatus = '$estatus'
            WHERE id = $empleado_id";

        if ($conn->query($sql_update) === TRUE) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Error al actualizar: " . $conn->error;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Empleado</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Editar Empleado</h2>

    <?php if (isset($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>

    <form action="editar.php?id=<?php echo $empleado['id']; ?>" method="POST">
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $empleado['nombre']; ?>" required minlength="3" maxlength="100">
        </div>
        <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo $empleado['telefono']; ?>" required minlength="10" maxlength="10">
        </div>
        <div class="form-group">
            <label for="correo">Correo Electrónico</label>
            <input type="email" class="form-control" id="correo" name="correo" value="<?php echo $empleado['correo']; ?>" required>
        </div>
        <div class="form-group">
            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo $empleado['fecha_nacimiento']; ?>" required>
        </div>
        <div class="form-group">
            <label for="rfc">RFC</label>
            <input type="text" class="form-control" id="rfc" name="rfc" value="<?php echo $empleado['rfc']; ?>" required minlength="13" maxlength="13">
        </div>
        <div class="form-group">
            <label for="estatus">Estatus</label>
            <select class="form-control" id="estatus" name="estatus" required>
                <option value="activo" <?php echo ($empleado['estatus'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                <option value="inactivo" <?php echo ($empleado['estatus'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>

    <a href="index.php" class="btn btn-secondary mt-3">Volver a la lista</a>
</div>

</body>
</html>

<?php $conn->close(); ?>