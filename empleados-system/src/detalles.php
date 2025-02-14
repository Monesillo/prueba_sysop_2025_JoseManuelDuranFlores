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

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Empleado</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Detalles del Empleado</h2>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><?php echo $empleado['nombre']; ?></h5>
            <p class="card-text"><strong>ID:</strong> <?php echo $empleado['id']; ?></p>
            <p class="card-text"><strong>Teléfono:</strong> <?php echo $empleado['telefono']; ?></p>
            <p class="card-text"><strong>Correo:</strong> <?php echo $empleado['correo']; ?></p>
            <p class="card-text"><strong>Fecha de Nacimiento:</strong> <?php echo $empleado['fecha_nacimiento']; ?></p>
            <p class="card-text"><strong>RFC:</strong> <?php echo $empleado['rfc']; ?></p>
            <p class="card-text"><strong>Estatus:</strong> <?php echo $empleado['estatus']; ?></p>
            <p class="card-text"><strong>Fecha de Creación:</strong> <?php echo $empleado['fecha_creacion']; ?></p>
        </div>
    </div>

    <a href="index.php" class="btn btn-primary mt-3">Volver a la lista de empleados</a>
</div>

</body>
</html>

<?php $conn->close(); ?>