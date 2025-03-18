<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'conexion.php';

$query = "SELECT COUNT(*) as total FROM municipio";
$result = $conn->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    echo "La base de datos está funcionando. Número de municipio: " . $row['total'];
} else {
    echo "Error en la consulta: " . $conn->error;
}
?>
