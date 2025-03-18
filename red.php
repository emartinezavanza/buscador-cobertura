<?php
// Habilitar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir conexión
require_once __DIR__ . '/conexion.php';

// ⚡ Forzar salida JSON limpia
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header("Cache-Control: no-cache, must-revalidate");
ob_clean(); // Limpia cualquier salida antes del JSON

// 📌 Obtener y limpiar parámetros
$provincia = isset($_GET['provincia']) ? trim($_GET['provincia']) : '';
$municipio = isset($_GET['municipio']) ? trim($_GET['municipio']) : '';

// ⚠️ Validar que los parámetros existen
if (empty($provincia) || empty($municipio)) {
    die(json_encode(["error" => "❌ Faltan parámetros: provincia o municipio no definidos"], JSON_UNESCAPED_UNICODE));
}

// 🔍 Mapear la provincia con su tabla correspondiente
$tablas = [
    "albacete" => "cobertura_02",
    "alicante" => "cobertura_03",
    "almeria" => "cobertura_04",
    "cuenca" => "cobertura_16",
    "granada" => "cobertura_18",
    "murcia" => "cobertura_30",
    "valencia" => "cobertura_46"
];

$provincia = strtolower($provincia);

if (!isset($tablas[$provincia])) {
    die(json_encode(["error" => "❌ Provincia no válida: $provincia"], JSON_UNESCAPED_UNICODE));
}

$tabla = $tablas[$provincia];

// 🛠 Consulta SQL para obtener la columna `red`
$query = "SELECT DISTINCT TRIM(red) AS red FROM $tabla WHERE TRIM(poblacion) = ? ORDER BY red ASC";

// 🚀 Preparar la consulta
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $municipio);
$stmt->execute();
$result = $stmt->get_result();

// 🔍 Recoger datos de redes
$redes = [];
while ($row = $result->fetch_assoc()) {
    if (!empty($row['red']) && !in_array($row['red'], $redes)) {
        $redes[] = $row['red'];
    }
}

// 📌 Respuesta JSON final
ob_end_clean(); // Evita texto extra en la respuesta
echo json_encode([
    "status" => "✅ Redes encontradas",
    "provincia" => ucfirst($provincia),
    "municipio" => $municipio,
    "num_redes" => count($redes),
    "redes" => $redes
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

exit;
?>
