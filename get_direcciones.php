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
$tipoVia = isset($_GET['tipo_via']) ? trim($_GET['tipo_via']) : ''; // ✅ Nuevo: Filtrar por tipo de vía

// ⚠️ Validar que todos los parámetros existen
if (empty($provincia) || empty($municipio) || empty($tipoVia)) {
    die(json_encode(["error" => "❌ Faltan parámetros: provincia, municipio o tipo de vía no definidos"], JSON_UNESCAPED_UNICODE));
}

// 🔍 Mapear la provincia con su tabla correspondiente (forzamos minúsculas para evitar errores)
$tablas = [
    "albacete" => "cobertura_02",
    "alicante" => "cobertura_03",
    "almeria" => "cobertura_04",
    "cuenca" => "cobertura_16",
    "granada" => "cobertura_18",
    "murcia" => "cobertura_30",
    "valencia" => "cobertura_46"
];

$provincia = strtolower($provincia); // Convertimos la provincia a minúsculas para que coincida

if (!isset($tablas[$provincia])) {
    die(json_encode(["error" => "❌ Provincia no válida: $provincia"], JSON_UNESCAPED_UNICODE));
}

$tabla = $tablas[$provincia];

// 🛠 Construcción de la consulta SQL con filtro por Tipo de Vía
$query = "SELECT DISTINCT TRIM(NombreVia) AS NombreVia FROM $tabla WHERE TRIM(poblacion) = ? AND TRIM(TipoVia) = ? ORDER BY NombreVia ASC";

// 🚀 Preparar la consulta
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $municipio, $tipoVia);
$stmt->execute();
$result = $stmt->get_result();

// 🏙 Recoger nombres de calles y limpiar caracteres especiales
$direcciones = [];
while ($row = $result->fetch_assoc()) {
    $nombreVia = trim($row['NombreVia']);
    $nombreVia = preg_replace('/[^a-zA-Z0-9ÁÉÍÓÚáéíóúÑñ\s\-\(\)\/\.]/u', '', $nombreVia); // Solo permitimos letras, números, espacios y caracteres comunes

    if (!empty($nombreVia)) {
        $direcciones[] = $nombreVia;
    }
}

// 📌 Respuesta JSON final asegurando que solo devuelve direcciones limpias
ob_end_clean(); // Evita texto extra en la respuesta
echo json_encode([
    "status" => "✅ Direcciones encontradas",
    "provincia" => ucfirst($provincia),
    "municipio" => $municipio,
    "tipo_via" => $tipoVia,
    "num_direcciones" => count($direcciones),
    "direcciones" => $direcciones
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
?>
