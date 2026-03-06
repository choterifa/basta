<?php
session_start();
include("conectar.php");

if (!isset($_SESSION['id_partida'])) {
    echo json_encode(['error' => 'No session']);
    exit;
}

$id_partida = $_SESSION['id_partida'];
$result = mysqli_query($conn, "SELECT letra_actual, estado FROM partidas WHERE id_partida = $id_partida");
$row = mysqli_fetch_assoc($result);

echo json_encode($row);
