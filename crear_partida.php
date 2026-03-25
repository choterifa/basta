<?php
session_start();
include("conectar.php");

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

if ($nombre === '' || strlen($nombre) < 3) {
    exit("Nombre invalido. <a href='index.php'>Volver</a>");
}

$nombre = mysqli_real_escape_string($conn, $nombre);

// Crear partida en estado 'esperando'
mysqli_query($conn, "INSERT INTO partidas (estado, letra_actual, tiempo_inicio) VALUES ('esperando', NULL, NULL)");
$id_partida = mysqli_insert_id($conn);

// Crear jugador (host)
mysqli_query($conn, "INSERT INTO jugadores (nombre, partida_id, es_host) VALUES ('$nombre', '$id_partida', 1)");
$id_jugador = mysqli_insert_id($conn);

// Guardar en sesión
$_SESSION['id_jugador'] = $id_jugador;
$_SESSION['id_partida'] = $id_partida;
$_SESSION['nombre'] = $nombre;
$_SESSION['es_host'] = 1;

header("Location: lobby.php");
?>
