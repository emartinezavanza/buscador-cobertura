<?php
header('Content-Type: application/json; charset=UTF-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/conexion.php';

// Obtener parámetros
$provincia = $_GET['provincia'] ?? '';
$municipio = $_GET['municipio'] ?? '';
$tipo_via = $_GET['tipo_via'] ?? '';
$nombrevia = $_GET['nombrevia'] ?? '';
$numero = $_GET['numero'] ?? '';

// Verificar que todos los parámetros estén presentes
if (empty($provincia) || empty($municipio) || empty($tipo_via) || empty($nombrevia) || empty($numero)) {
    echo json_encode(["error" => "⚠️ Faltan parámetros", "provincia" => $provincia, "municipio" => $municipio, "tipo_via" => $tipo_via, "nombrevia" => $nombrevia, "numero" => $numero]);
    exit;
}

// Mapear provincias a tablas
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
    echo json_encode(["error" => "❌ Provincia no válida"]);
    exit;
}

$tabla = $tablas[$provincia];

// Consulta SQL con manejo de valores NULL en "Planta"
$query = "SELECT DISTINCT TRIM(COALESCE(Planta, 'Sin especificar')) AS Planta 
          FROM $tabla 
          WHERE poblacion = ? 
          AND TipoVia = ? 
          AND NombreVia = ? 
          AND Numero = ? 
          ORDER BY Planta ASC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(["error" => "❌ Error en la consulta SQL: " . $conn->error]);
    exit;
}

$stmt->bind_param("ssss", $municipio, $tipo_via, $nombrevia, $numero);
$stmt->execute();
$result = $stmt->get_result();

$pisos = [];
while ($row = $result->fetch_assoc()) {
    $pisos[] = trim($row['Planta']);
}

// Depuración: Verificar si hay pisos antes de enviar JSON
if (!empty($pisos)) {
    echo json_encode([
        "status" => "✅ Pisos encontrados",
        "provincia" => $provincia,
        "municipio" => $municipio,
        "tipo_via" => $tipo_via,
        "nombrevia" => $nombrevia,
        "numero" => $numero,
        "num_pisos" => count($pisos),
        "pisos" => $pisos
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    echo json_encode(["error" => "⚠️ No hay pisos disponibles"]);
}
exit;
?>
