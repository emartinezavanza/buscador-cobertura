<?php
require_once __DIR__ . '/conexion.php';

// ⚡ Forzar la salida JSON desde el inicio
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");

// ⚠️ Habilitar errores en PHP para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 📌 Obtener la provincia
$provincia = $_GET['provincia'] ?? '';
if (empty($provincia)) {
    die(json_encode(["error" => "❌ No se recibió ninguna provincia"], JSON_UNESCAPED_UNICODE));
}

// 🔍 Mapear la provincia con su tabla correspondiente
$tablas = [
    "Albacete" => "cobertura_02",
    "Alicante" => "cobertura_03",
    "Almeria" => "cobertura_04",
    "Cuenca" => "cobertura_16",
    "Granada" => "cobertura_18",
    "Murcia" => "cobertura_30",
    "Valencia" => "cobertura_46"
];

if (!isset($tablas[$provincia])) {
    die(json_encode(["error" => "❌ Provincia no válida: $provincia"], JSON_UNESCAPED_UNICODE));
}

$tabla = $tablas[$provincia];

// 🛠 Construcción de la consulta SQL
$query = "SELECT DISTINCT TRIM(poblacion) AS poblacion FROM $tabla WHERE poblacion IS NOT NULL AND poblacion != '' ORDER BY poblacion ASC";

// 🚀 Ejecutar la consulta
$result = $conn->query($query);

// 🔍 Si hay error en la consulta, mostrarlo y detener ejecución
if (!$result) {
    die(json_encode([
        "error" => "❌ Error en la consulta SQL",
        "sql" => $query,
        "mysql_error" => $conn->error
    ], JSON_UNESCAPED_UNICODE));
}

// 🔍 Verificar si hay datos
$num_rows = $result->num_rows;
if ($num_rows === 0) {
    die(json_encode([
        "error" => "⚠️ No se encontraron municipios en la tabla: $tabla",
        "sql" => $query,
        "num_registros" => $num_rows
    ], JSON_UNESCAPED_UNICODE));
}

// 🏙 Recoger municipios y limpiar nombres evitando valores NULL
$municipios = [];
while ($row = $result->fetch_assoc()) {
    $municipio = $row['poblacion'] ?? ''; // Si es NULL, se convierte a una cadena vacía
    $municipio = trim($municipio); // Elimina espacios en blanco
    $municipio = preg_replace('/[^a-zA-Z0-9ÁÉÍÓÚáéíóúÑñ\s-]/u', '', $municipio); // Solo letras, números, espacios y guiones

    if (!empty($municipio)) {
        $municipios[] = $municipio;
    }
}

// 📌 Respuesta JSON final
echo json_encode([
    "status" => "✅ Municipios encontrados",
    "provincia" => $provincia,
    "num_municipios" => count($municipios),
    "municipios" => $municipios
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

exit;
?>
