<?php
// Datos de conexión
$host = "hl813.dinaserver.com";
$dbname = "avanza_cobertura";
$user = "avanza_cobertura";
$pass = "n[Zf8tNw1k5ie8!t";

// Recibir datos del formulario
$provincia = $_POST['provincia'];
$municipio = $_POST['municipio'];
$calle = $_POST['direccion'];
$numero = $_POST['numero'];
$piso = $_POST['piso'];
$telefono = $_POST['telefono'];
$email = $_POST['email'];

// Mapeo de provincias con sus tablas
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
    echo json_encode(["status" => "error", "message" => "Provincia no válida."]);
    exit;
}

// Conectar a la base de datos
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Error en la conexión: " . $conn->connect_error]));
}

$tabla = $tablas[$provincia];

// Consulta SQL para buscar cobertura
$sql = "SELECT red FROM $tabla 
        WHERE poblacion = ? 
        AND NombreVia = ? 
        AND Numero = ? 
        AND Planta = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssis", $municipio, $calle, $numero, $piso);
$stmt->execute();
$result = $stmt->get_result();

// Si encuentra cobertura
if ($row = $result->fetch_assoc()) {
    $red = $row['red'];

    // Guardar datos en Excel
    guardarEnExcel($provincia, $municipio, $calle, $numero, $piso, $telefono, $email);

    echo json_encode([
        "status" => "success",
        "red" => $red
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "No se encontró cobertura en esta dirección."]);
}

$conn->close();

// Función para guardar los datos en un archivo Excel
function guardarEnExcel($provincia, $municipio, $calle, $numero, $piso, $telefono, $email) {
    $archivo = "busquedas_cobertura.csv";
    $datos = [$provincia, $municipio, $calle, $numero, $piso, $telefono, $email, date("Y-m-d H:i:s")];

    // Guardar en CSV
    $file = fopen($archivo, "a");
    fputcsv($file, $datos);
    fclose($file);
}
?>
