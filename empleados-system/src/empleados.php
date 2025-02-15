<?php
session_start();
require_once 'db_config.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$registros_por_pagina = 10; // Ahora se muestran 10 empleados por página
$paginacion = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$paginacion = max($paginacion, 1);
$inicio = ($paginacion - 1) * $registros_por_pagina;

// Verificar si hay búsqueda
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : "";

// Consulta SQL con búsqueda activa
$sql = "SELECT e.id, e.nombre, e.telefono, e.correo, e.estatus, u.tipo_usuario 
        FROM empleados e
        LEFT JOIN usuarios u ON e.usuario_id = u.id
        WHERE e.estatus = 'activo'";

if (!empty($buscar)) {
    $sql .= " AND (e.nombre LIKE '%$buscar%' OR e.correo LIKE '%$buscar%' OR u.tipo_usuario LIKE '%$buscar%')";
}

$sql .= " LIMIT $inicio, $registros_por_pagina";
$result = $conn->query($sql);

// Obtener el total de empleados activos filtrados
$sql_total = "SELECT COUNT(*) AS total FROM empleados WHERE estatus = 'activo'";
if (!empty($buscar)) {
    $sql_total .= " AND (nombre LIKE '%$buscar%' OR correo LIKE '%$buscar%')";
}
$total_result = $conn->query($sql_total);
$total_empleados = $total_result->fetch_assoc()['total'];
$total_paginas = ceil($total_empleados / $registros_por_pagina);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empleados</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="text-center">Consulta General de Empleados Activos</h2>
        <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>

    <div class="d-flex mb-3 justify-content-between">
        <form method="GET" action="empleados.php" class="d-flex">
            <input type="text" name="buscar" class="form-control d-inline-block w-auto" placeholder="Buscar empleado..." value="<?php echo htmlspecialchars($buscar); ?>">
            <button type="submit" class="btn btn-primary ml-2">
                <i class="fa fa-search"></i> Buscar
            </button>
        </form>
        <a href="registro.php" class="btn btn-success">Agregar Nuevo Empleado</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Estatus</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr id="row-<?php echo $row['id']; ?>">
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['nombre']; ?></td>
                    <td><?php echo $row['telefono']; ?></td>
                    <td><?php echo $row['correo']; ?></td>
                    <td><?php echo ucfirst($row['estatus']); ?></td>
                    <td><?php echo ucfirst($row['tipo_usuario']); ?></td>
                    <td>
                        <a href="editar.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                        <button class="btn btn-danger btn-sm" onclick="confirmarEliminacion(<?php echo $row['id']; ?>)">Eliminar</button>
                        <a href="detalles.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">Detalles</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <nav aria-label="Página de navegación">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($paginacion == 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="empleados.php?pagina=<?php echo $paginacion - 1; ?>&buscar=<?php echo urlencode($buscar); ?>">Anterior</a>
            </li>
            <?php for ($i = 1; $i <= $total_paginas; $i++) : ?>
                <li class="page-item <?php echo ($paginacion == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="empleados.php?pagina=<?php echo $i; ?>&buscar=<?php echo urlencode($buscar); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($paginacion == $total_paginas) ? 'disabled' : ''; ?>">
                <a class="page-link" href="empleados.php?pagina=<?php echo $paginacion + 1; ?>&buscar=<?php echo urlencode($buscar); ?>">Siguiente</a>
            </li>
        </ul>
    </nav>
</div>

<script>
    function confirmarEliminacion(id) {
        if (confirm('¿Seguro que deseas desactivar este empleado?')) {
            fetch('eliminar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    document.getElementById("row-" + id).remove();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }
</script>

</body>
</html>

<?php $conn->close(); ?>
