<?php
$host = "hl813.dinaserver.com";
$dbname = "avanza_cobertura";
$user = "avanza_cobertura";
$pass = "n[Zf8tNw1k5ie8!t";

$conn = new mysqli($host, $user, $pass, $dbname);

if (!$conn) {
    die(json_encode(["error" => "Fallo en la conexión a la base de datos"]));
}

?>
