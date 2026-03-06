<?php
include("conectar.php");

// Intentar agregar la columna tiempo_inicio
$sql = "ALTER TABLE partidas ADD COLUMN tiempo_inicio INT(11) DEFAULT NULL";
if (mysqli_query($conn, $sql)) {
    echo "Columna tiempo_inicio agregada correctamente.<br>";
} else {
    echo "Error o la columna ya existe: " . mysqli_error($conn) . "<br>";
}

echo "Estructura actualizada.";
