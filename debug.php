<?php
include("conectar.php");

echo "<h1>Diagnóstico de DB Basta</h1>";

$tables = ["partidas", "jugadores", "respuestas"];

foreach ($tables as $table) {
    echo "<h2>Tabla: $table</h2>";
    $res = mysqli_query($conn, "DESCRIBE $table");
    if (!$res) {
        echo "<p style='color:red'>Error: " . mysqli_error($conn) . "</p>";
        continue;
    }
    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($res)) {
        echo "<tr>";
        foreach ($row as $val) echo "<td>" . ($val === null ? 'NULL' : $val) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<h2>Estado de Sesión</h2>";
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (isset($_SESSION['id_partida'])) {
    $pid = $_SESSION['id_partida'];
    echo "<h2>Jugadores en Partida $pid</h2>";
    $res = mysqli_query($conn, "SELECT * FROM jugadores WHERE partida_id = $pid");
    while($row = mysqli_fetch_assoc($res)) {
        echo "<p>ID: {$row['id_jugador']} - Nombre: {$row['nombre']} - Host: {$row['es_host']}</p>";
    }
    
    echo "<h2>Conteo de Respuestas en Partida $pid</h2>";
    $res = mysqli_query($conn, "SELECT jugador_id, COUNT(*) as total FROM respuestas WHERE jugador_id IN (SELECT id_jugador FROM jugadores WHERE partida_id = $pid) GROUP BY jugador_id");
    while($row = mysqli_fetch_assoc($res)) {
        echo "<p>Jugador ID {$row['jugador_id']}: {$row['total']} respuestas</p>";
    }
}
?>
