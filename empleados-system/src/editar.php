<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID no proporcionado.";
    exit;
}

$empleado_id = (int)$_GET['id'];

$sql = "SELECT e.*, u.tipo_usuario 
        FROM empleados e
        LEFT JOIN usuarios u ON e.usuario_id = u.id
        WHERE e.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $empleado_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $empleado = $result->fetch_assoc();
} else {
    echo "Empleado no encontrado.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $rfc = trim($_POST['rfc']);
    $estatus = $_POST['estatus'];
    $vacante = $_POST['vacante'];

    $error = "";

    if (strlen($nombre) < 3 || strlen($nombre) > 100) {
        $error = "El nombre debe tener entre 3 y 100 caracteres.";
    } elseif (!preg_match('/^[0-9]{10}$/', $telefono)) {
        $error = "El teléfono debe contener exactamente 10 números.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "Correo no válido.";
    } elseif (!preg_match('/^[A-Z0-9]{13}$/', strtoupper($rfc))) {
        $error = "El RFC debe tener 13 caracteres alfanuméricos.";
    } else {
        $fecha_actual = new DateTime();
        $fecha_nac = new DateTime($fecha_nacimiento);
        $diferencia = $fecha_actual->diff($fecha_nac)->y;

        if ($diferencia < 18) {
            $error = "El empleado debe ser mayor de 18 años.";
        }
    }

    if (empty($error)) {
        $sql_update = "UPDATE empleados SET 
            nombre = ?, telefono = ?, correo = ?, fecha_nacimiento = ?, 
            rfc = ?, estatus = ?, vacante = ?
            WHERE id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("sssssssi", $nombre, $telefono, $correo, $fecha_nacimiento, $rfc, $estatus, $vacante, $empleado_id);

        if ($stmt->execute()) {
            $sql_update_user = "UPDATE usuarios SET tipo_usuario = ? WHERE id = ?";
            $stmt_user = $conn->prepare($sql_update_user);
            $stmt_user->bind_param("si", $vacante, $empleado['usuario_id']);
            $stmt_user->execute();

            header("Location: empleados.php");
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

    <?php if (!empty($error)) { echo "<div class='alert alert-danger'>$error</div>"; } ?>

    <form action="editar.php?id=<?php echo $empleado['id']; ?>" method="POST">
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($empleado['nombre']); ?>" required minlength="3" maxlength="100">
        </div>
        <div class="form-group">
            <label for="telefono">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($empleado['telefono']); ?>" required pattern="[0-9]{10}" title="Debe contener exactamente 10 dígitos numéricos">
        </div>
        <div class="form-group">
            <label for="correo">Correo Electrónico</label>
            <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($empleado['correo']); ?>" required>
        </div>
        <div class="form-group">
            <label for="fecha_nacimiento">Fecha de Nacimiento</label>
            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($empleado['fecha_nacimiento']); ?>" required>
        </div>
        <div class="form-group">
            <label for="rfc">RFC</label>
            <input type="text" class="form-control" id="rfc" name="rfc" value="<?php echo htmlspecialchars($empleado['rfc']); ?>" required pattern="[A-Za-z0-9]{13}" title="Debe contener exactamente 13 caracteres alfanuméricos">
        </div>
        <div class="form-group">
            <label for="estatus">Estatus</label>
            <select class="form-control" id="estatus" name="estatus" required>
                <option value="activo" <?php echo ($empleado['estatus'] == 'activo') ? 'selected' : ''; ?>>Activo</option>
                <option value="inactivo" <?php echo ($empleado['estatus'] == 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
            </select>
        </div>
        <div class="form-group">
            <label for="vacante">Rol</label>
            <select class="form-control" id="vacante" name="vacante" required>
                <option value="admin" <?php echo ($empleado['tipo_usuario'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                <option value="empleado" <?php echo ($empleado['tipo_usuario'] == 'empleado') ? 'selected' : ''; ?>>Empleado</option>
                <option value="ventas" <?php echo ($empleado['tipo_usuario'] == 'ventas') ? 'selected' : ''; ?>>Ventas</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
    </form>

    <a href="empleados.php" class="btn btn-secondary mt-3">Volver a la lista</a>
</div>

</body>
</html>

<?php $conn->close(); ?>
