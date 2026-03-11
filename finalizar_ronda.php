<?php
session_start();
include("conectar.php");
include("round_sync.php");

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_partida'])) {
    echo json_encode(['ok' => false, 'error' => 'No session']);
    exit;
}

$id_partida = (int) $_SESSION['id_partida'];
$deadline_ms = ensureRoundDeadlineMs($conn, $id_partida);

echo json_encode([
    'ok' => true,
    'estado' => 'finalizada',
    'deadline_ms' => $deadline_ms,
]);
