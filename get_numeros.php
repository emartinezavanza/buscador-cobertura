<?php
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/conexion.php';

// Obtener los parámetros de la solicitud
$provincia = $_GET['provincia'] ?? '';
$municipio = $_GET['municipio'] ?? '';
$tipo_via = $_GET['tipo_via'] ?? '';
$nombrevia = $_GET['nombrevia'] ?? '';

// Verificar que se enviaron los parámetros necesarios
if (empty($provincia) || empty($municipio) || empty($tipo_via) || empty($nombrevia)) {
    echo json_encode(["error" => "⚠️ Faltan parámetros: provincia='$provincia', municipio='$municipio', tipo_via='$tipo_via', nombrevia='$nombrevia'"]);
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

// 🛠 Depuración: mostrar los parámetros recibidos en los logs del servidor
error_log("📌 Buscando en: $tabla | Municipio: $municipio | Tipo de Vía: $tipo_via | Calle: $nombrevia");

// Consulta SQL para obtener los números disponibles en esa calle
$query = "SELECT DISTINCT TRIM(Numero) AS Numero 
          FROM $tabla 
          WHERE poblacion COLLATE utf8_general_ci = ? 
          AND TipoVia COLLATE utf8_general_ci = ? 
          AND NombreVia COLLATE utf8_general_ci = ? 
          ORDER BY CAST(Numero AS UNSIGNED), Numero ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $municipio, $tipo_via, $nombrevia);
$stmt->execute();
$result = $stmt->get_result();

$numeros = [];
while ($row = $result->fetch_assoc()) {
    $numeros[] = $row['Numero'];
}

// Si hay resultados, devolverlos en JSON
if (!empty($numeros)) {
    echo json_encode(["status" => "✅ Números encontrados", "numeros" => $numeros]);
} else {
    echo json_encode(["error" => "⚠️ No hay números disponibles para esta calle"]);
}

exit;
?>
