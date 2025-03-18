<?php
require_once __DIR__ . '/conexion.php';

// ⚡ Forzar salida JSON limpia
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");

// ⚠️ Habilitar errores en PHP solo en depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('output_buffering', 'off'); // Evita que se acumulen datos en el buffer
ini_set('zlib.output_compression', 0); // Desactiva la compresión de salida
ob_clean(); // Limpia cualquier salida antes del JSON

// 📌 Obtener parámetros
$provincia = $_GET['provincia'] ?? '';
$municipio = $_GET['municipio'] ?? '';

// ⚠️ Validar que ambos parámetros existen
if (!$provincia || !$municipio) {
    die(json_encode(["error" => "❌ Faltan parámetros: provincia o municipio no definidos"], JSON_UNESCAPED_UNICODE));
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
$query = "SELECT DISTINCT TRIM(TipoVia) AS TipoVia FROM $tabla WHERE TRIM(poblacion) = ? ORDER BY TipoVia ASC";

// 🚀 Preparar la consulta
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $municipio);
$stmt->execute();
$result = $stmt->get_result();

// 🏙 Recoger tipos de vía
$tipos_via = [];
while ($row = $result->fetch_assoc()) {
    if (!empty($row['TipoVia'])) {
        $tipos_via[] = trim($row['TipoVia']); // Limpiar espacios extra
    }
}

// 📌 Forzar salida JSON limpia y detener el script
ob_end_clean(); // Elimina cualquier salida previa
echo json_encode([
    "status" => "✅ Tipos de vía encontrados",
    "provincia" => $provincia,
    "municipio" => $municipio,
    "num_tipos_via" => count($tipos_via),
    "tipos_via" => $tipos_via
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
?>
