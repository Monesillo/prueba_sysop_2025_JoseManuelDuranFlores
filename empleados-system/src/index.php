<?php
session_start();
require_once 'db_config.php';

$registros_por_pagina = 5;
$paginacion = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
$inicio = ($paginacion - 1) * $registros_por_pagina;

$sql = "SELECT * FROM empleados WHERE estatus = 'activo' LIMIT $inicio, $registros_por_pagina";
$result = $conn->query($sql);

$sql_total = "SELECT COUNT(*) AS total FROM empleados WHERE estatus = 'activo'";
$total_result = $conn->query($sql_total);
$total_empleados = $total_result->fetch_assoc()['total'];
$total_paginas = ceil($total_empleados / $registros_por_pagina);

if (isset($_POST['buscar'])) {
    $buscar = $_POST['buscar'];
    $sql = "SELECT * FROM empleados WHERE estatus = 'activo' AND nombre LIKE '%$buscar%' LIMIT $inicio, $registros_por_pagina";
    $result = $conn->query($sql);
}

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
    <h2 class="text-center">Consulta General de Empleados</h2>

    <div class="d-flex mb-3">
        <input type="text" id="search" class="form-control w-25" placeholder="Buscar empleado...">
        <button class="btn btn-primary ml-2" id="search-btn">
            <i class="fa fa-search"></i> Buscar
        </button>
        
        <a href="registro.php" class="btn btn-success ml-auto">Agregar Nuevo Empleado</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['nombre']; ?></td>
                    <td><?php echo $row['telefono']; ?></td>
                    <td><?php echo $row['correo']; ?></td>
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
                <a class="page-link" href="index.php?pagina=<?php echo $paginacion - 1; ?>">Anterior</a>
            </li>
            <?php for ($i = 1; $i <= $total_paginas; $i++) : ?>
                <li class="page-item <?php echo ($paginacion == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="index.php?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo ($paginacion == $total_paginas) ? 'disabled' : ''; ?>">
                <a class="page-link" href="index.php?pagina=<?php echo $paginacion + 1; ?>">Siguiente</a>
            </li>
        </ul>
    </nav>
</div>

<script>
    function confirmarEliminacion(id) {
        if (confirm('¿Seguro que deseas desactivar este empleado?')) {
            window.location.href = 'eliminar.php?id=' + id;
        }
    }
</script>

</body>
</html>

<?php $conn->close(); ?>