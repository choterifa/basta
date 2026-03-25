<?php
include("conectar.php");

header('Content-Type: application/json; charset=utf-8');

// Limpieza automática: eliminar partidas 'esperando' de más de 30 minutos
$limite_tiempo = time() - (30 * 60); 
mysqli_query($conn, "DELETE FROM partidas WHERE estado='esperando' AND tiempo_inicio < $limite_tiempo");

$partidas = [];
// Solo mostrar partidas recientes que estén esperando
$res = mysqli_query($conn, "SELECT id_partida FROM partidas WHERE estado='esperando' AND tiempo_inicio >= $limite_tiempo ORDER BY id_partida DESC LIMIT 5");

while ($row = mysqli_fetch_assoc($res)) {
    $partidas[] = [
        'id_partida' => (int) $row['id_partida']
    ];
}

echo json_encode($partidas);
