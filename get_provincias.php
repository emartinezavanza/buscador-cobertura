<?php
header('Content-Type: application/json');

// Forzar error si PHP no ejecuta nada
if (!function_exists('json_encode')) {
    die("json_encode no está disponible en tu servidor");
}

// Array de prueba
$provincias = ["Albacete", "Alicante", "Almeria", "Cuenca", "Granada", "Murcia", "Valencia"];

// Depuración antes de imprimir
echo json_encode($provincias);
?>
