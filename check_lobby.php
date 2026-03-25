<?php
session_start();
include("conectar.php");

if (!isset($_SESSION['id_partida'])) {
    echo json_encode(['error' => 'No session']);
    exit;
}

$id_partida = $_SESSION['id_partida'];

// Obtener estado de la partida
$res_p = mysqli_query($conn, "SELECT estado FROM partidas WHERE id_partida = $id_partida");
$partida = mysqli_fetch_assoc($res_p);

// Obtener jugadores
$res_j = mysqli_query($conn, "SELECT id_jugador, nombre, es_host FROM jugadores WHERE partida_id = $id_partida ORDER BY id_jugador ASC");
$players = [];
while ($row = mysqli_fetch_assoc($res_j)) {
    $players[] = $row;
}

echo json_encode([
    'status' => $partida['estado'],
    'players' => $players
]);
?>
