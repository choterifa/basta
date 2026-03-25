<?php
include("conectar.php");

header('Content-Type: application/json; charset=utf-8');

$partidas = [];
$res = mysqli_query($conn, "SELECT id_partida FROM partidas WHERE estado='esperando' ORDER BY id_partida DESC LIMIT 5");

while ($row = mysqli_fetch_assoc($res)) {
    $partidas[] = [
        'id_partida' => (int) $row['id_partida']
    ];
}

echo json_encode($partidas);
