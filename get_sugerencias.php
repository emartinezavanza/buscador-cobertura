<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/conexion.php';

// Obtener los parámetros enviados desde el frontend
$provincia = $_GET['provincia'] ?? '';
$municipio = $_GET['municipio'] ?? '';
$tipo_via = $_GET['tipo_via'] ?? '';
$term = $_GET['term'] ?? '';

// Verificar que se han recibido los parámetros necesarios
if (empty($provincia) || empty($municipio) || empty($tipo_via) || empty($term)) {
    echo json_encode(["error" => "⚠️ Faltan parámetros: provincia='$provincia', municipio='$municipio', tipo_via='$tipo_via', term='$term'"]);
    exit;
}

// Mapeo de provincias a tablas
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

// Depuración: mostrar los parámetros recibidos
error_log("Buscando en: $tabla - Municipio: $municipio - TipoVía: $tipo_via - Term: $term");

// Consulta SQL para buscar direcciones
$query = "SELECT DISTINCT TRIM(NombreVia) AS NombreVia 
          FROM $tabla 
          WHERE poblacion COLLATE utf8_general_ci = ? 
          AND TipoVia COLLATE utf8_general_ci = ? 
          AND NombreVia COLLATE utf8_general_ci LIKE ? 
          ORDER BY NombreVia ASC";
$stmt = $conn->prepare($query);
$searchTerm = "%$term%"; // Buscar coincidencias parciales
$stmt->bind_param("sss", $municipio, $tipo_via, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$direcciones = [];
while ($row = $result->fetch_assoc()) {
    $direcciones[] = $row['NombreVia'];
}

// Respuesta JSON con resultados o mensaje de error
if (!empty($direcciones)) {
    echo json_encode(["status" => "✅ Direcciones encontradas", "direcciones" => $direcciones]);
} else {
    echo json_encode(["error" => "⚠️ No se encontraron direcciones", "query" => $query]);
}

exit;
?>
