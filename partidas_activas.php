<?php
include("conectar.php");

header('Content-Type: application/json; charset=utf-8');

$partidas = [];
$res = mysqli_query($conn, "SELECT id_partida, letra_actual FROM partidas WHERE estado='en curso' ORDER BY id_partida DESC LIMIT 5");

while ($row = mysqli_fetch_assoc($res)) {
    $partidas[] = [
        'id_partida' => (int) $row['id_partida'],
        'letra_actual' => $row['letra_actual'] ?: null,
    ];
}

echo json_encode($partidas);
