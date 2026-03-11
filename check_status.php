<?php
session_start();
include("conectar.php");
include("round_sync.php");

if (!isset($_SESSION['id_partida'])) {
    echo json_encode(['error' => 'No session']);
    exit;
}

$id_partida = $_SESSION['id_partida'];
$result = mysqli_query($conn, "SELECT letra_actual, estado FROM partidas WHERE id_partida = $id_partida");
$row = mysqli_fetch_assoc($result);

if ($row && $row['estado'] === 'finalizada') {
    $row['deadline_ms'] = ensureRoundDeadlineMs($conn, $id_partida);
} else {
    $row['deadline_ms'] = null;
}

echo json_encode($row);
