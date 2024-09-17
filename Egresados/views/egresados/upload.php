<?php
require 'vendor/autoload.php'; // Asegúrate de que el autoload esté bien referenciado
require("../config/db.php");

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["file"])) {
    $file = $_FILES["file"]["tmp_name"];

    // Cargar el archivo de Excel
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray();

    // Recorrer cada fila del archivo
    foreach ($data as $row) {
        $ID_usuario = $row[0];
        $nombres = $row[1];
        $apellidos = $row[2];
        $identificacion = $row[3];
        $contraseña = password_hash($row[4], PASSWORD_BCRYPT); // Encriptar la contraseña
        $direccion = $row[5];
        $telefono = $row[6];
        $correo_electronico = $row[7];
        $ID_rol = $row[8];

        // Insertar los datos en la base de datos
        $stmt = $conexion->prepare("INSERT INTO usuarios (ID_usuario, Nombres, Apellidos, Identificacion, contraseña, Direccion, Telefono, Correo_electronico, ID_rol) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssssi", $ID_usuario, $nombres, $apellidos, $identificacion, $contraseña, $direccion, $telefono, $correo_electronico, $ID_rol);
        $stmt->execute();
    }

    echo "Los datos han sido importados exitosamente.";
} else {
    echo "Error al subir el archivo.";
}
?>
