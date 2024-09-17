<?php
// Incluir el archivo que contiene la clase Database
require_once '../../config/database.php'; // Ajusta la ruta según la ubicación real del archivo

// Crear una instancia de la clase Database
$database = new Database();
$db = $database->getConnection();

// Verificar si se ha recibido un ID para eliminar
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Preparar y ejecutar la consulta para eliminar el registro
    $query = "DELETE FROM egresados WHERE id = :delete_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':delete_id', $delete_id);

    if ($stmt->execute()) {
        // Redirigir a la misma página después de la eliminación
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error al intentar eliminar el registro.";
    }
}

// Obtener y mostrar la lista de egresados
$query = "
    SELECT 
        e.*, 
        lr.Ciudad AS CiudadResidencia, 
        ps.NombrePrograma AS NombreProgramaFormacion 
    FROM 
        egresados e
    LEFT JOIN 
        lugarresidencia lr ON e.LugarResidenciaID = lr.ID
    LEFT JOIN 
        programaformacionsena ps ON e.ProgramaFormacionSENAID = ps.ID
";
$result = $db->query($query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Egresados</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <style>
        /* Estilos personalizados */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            width: 100%;
            margin: 20px auto;
            max-width: 1200px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #d1bda1;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #66CC99;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #e0f2e9;
        }
        tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #c8e6c9;
        }
        .header, .footer {
            padding: 10px 0;
            text-align: center;
        }
        .header img {
            height: 50px;
        }
        .delete-button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        .delete-button:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
<?php include '../../../Menu/Menu/menu.php'; ?>

    <div class="container">
        <header class="header">
            <h1>Informe de Egresados</h1>
            <div class="datetime">
                <?php
                date_default_timezone_set('America/Bogota');
                $fechaActual = date("d/m/Y");
                $horaActual = date("h:i a");
                ?>
                <div>Fecha actual: <?php echo $fechaActual; ?></div>
                <div>Hora actual: <?php echo $horaActual; ?></div>
            </div>
        </header>
        <section>
            <div class="button-row">
                <a href="create.php" class="btn-edit">Agregar Nuevos Egresados</a>
            </div>
            
            <table id="egresadosTable" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <!-- Encabezados de la tabla -->
                        <th>MES</th>
                        <th>Nombres</th>
                        <th>Tipo<br>Documento</th>
                        <th>Numero<br>Documento</th>
                        <th>Lugar<br>Residencia</th>
                        <th>Direccion<br>Residencia</th>
                        <th>Correo<br>Electronico</th>
                        <th>Telefono<br>Celular</th>
                        <th>Ocupacion<br>Actual</th>
                        <th>Vinculacion<br>Patrocinio</th>
                        <th>Centro <br>Formacion</th>
                        <th>Ficha</th>
                        <th>Fecha<br>Certificacion</th>
                        <th>Estudios<br>Adicionales</th>
                        <th>Fecha<br>Ultima<br>Llamada</th>
                        <th>Numero<br>Fijo</th>
                        <th>Genero</th>
                        <th>Otro<br>Telefono<br>Contacto</th>
                        <th>Programa<br>Formacion<br>SENA</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->rowCount() > 0): ?>
                        <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['MES']); ?></td>
                                <td><?php echo htmlspecialchars($row['NOMBRES']); ?></td>
                                <td><?php echo htmlspecialchars($row['TipoDocumentoID']); ?></td>
                                <td><?php echo htmlspecialchars($row['NumeroDocumento']); ?></td>
                                <td><?php echo htmlspecialchars($row['CiudadResidencia']); ?></td>
                                <td><?php echo htmlspecialchars($row['DireccionResidencia']); ?></td>
                                <td><?php echo htmlspecialchars($row['CorreoElectronico']); ?></td>
                                <td><?php echo htmlspecialchars($row['TelefonoCelular']); ?></td>
                                <td><?php echo htmlspecialchars($row['OcupacionActual']); ?></td>
                                <td><?php echo htmlspecialchars($row['VinculacionPatrocinio']); ?></td>
                                <td><?php echo htmlspecialchars($row['CentroFormacion']); ?></td>
                                <td><?php echo htmlspecialchars($row['Ficha']); ?></td>
                                <td><?php echo htmlspecialchars($row['FechaCertificacion']); ?></td>
                                <td><?php echo htmlspecialchars($row['EstudiosAdicionales']); ?></td>
                                <td><?php echo htmlspecialchars($row['FechaUltimaLlamada']); ?></td>
                                <td><?php echo htmlspecialchars($row['NumeroFijo']); ?></td>
                                <td><?php echo htmlspecialchars($row['Genero']); ?></td>
                                <td><?php echo htmlspecialchars($row['OtroTelefonoContacto']); ?></td>
                                <td><?php echo htmlspecialchars($row['NombreProgramaFormacion']); ?></td>
                                <td class="actions">
                                    <?php
                                    $url_update = 'update.php?id=' . $row['id'];
                                    echo "<a href='" . htmlspecialchars($url_update) . "' class='btn-edit'>Editar</a>";
                                    $url_delete = htmlspecialchars($_SERVER["PHP_SELF"]) . "?delete_id=" . $row['id'];
                                    echo "<a href='" . $url_delete . "' class='btn-delete' onclick=\"return confirm('¿Estás seguro de que quieres eliminar este registro?');\">Eliminar</a>";
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="19">No se encontraron egresados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
    <footer class="footer">
        <p>Todos los derechos reservados</p>
    </footer>

    <script>
        $(document).ready(function() {
            $('#egresadosTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'copyHtml5',
                    'csvHtml5',
                    'excelHtml5',
                    'pdfHtml5'
                ],
                language: {
                    decimal: "",
                    emptyTable: "No hay datos disponibles en la tabla",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                    infoEmpty: "Mostrando 0 a 0 de 0 entradas",
                    infoFiltered: "(filtrado de _MAX_ entradas totales)",
                    lengthMenu: "Mostrar _MENU_ entradas",
                    loadingRecords: "Cargando...",
                    processing: "Procesando...",
                    search: "Buscar:",
                    zeroRecords: "No se encontraron registros coincidentes",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    },
                    aria: {
                        sortAscending: ": activar para ordenar la columna ascendente",
                        sortDescending: ": activar para ordenar la columna descendente"
                    }
                },
                paging: true,
                pageLength: 10
            });
        });
    </script>
</body>
</html>
