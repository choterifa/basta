<?php
session_start();
include("conectar.php");

if (!isset($_SESSION['id_partida']) || !$_SESSION['es_host']) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$id_partida = $_SESSION['id_partida'];

// Elegir una letra al azar
$letras = range('A', 'Z');
$letra = $letras[array_rand($letras)];
$tiempo_inicio = time();

// Actualizar partida a 'en curso'
$sql = "UPDATE partidas SET estado = 'en curso', letra_actual = '$letra', tiempo_inicio = $tiempo_inicio WHERE id_partida = $id_partida";

if (mysqli_query($conn, $sql)) {
    // Limpiar respuestas anteriores para esta partida
    mysqli_query($conn, "DELETE FROM respuestas WHERE jugador_id IN (SELECT id_jugador FROM jugadores WHERE partida_id = $id_partida)");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
}
?>
