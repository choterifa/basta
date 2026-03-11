<?php
session_start();
include("conectar.php");

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

if ($nombre === '' || !preg_match("/^[\\p{L}\\s'-]+$/u", $nombre)) {
    exit("Nombre invalido. Solo se permiten letras, espacios, guion y apostrofe. <a href='index.php'>Volver</a>");
}

$nombre = mysqli_real_escape_string($conn, $nombre);

// Crear partida
$tiempo_inicio = time();
mysqli_query($conn, "INSERT INTO partidas (estado, letra_actual, tiempo_inicio) VALUES ('en curso', NULL, '$tiempo_inicio')");
$id_partida = mysqli_insert_id($conn);

// Crear jugador (host)
mysqli_query($conn, "INSERT INTO jugadores (nombre, partida_id) VALUES ('$nombre','$id_partida')");
$id_jugador = mysqli_insert_id($conn);

// Guardar en sesión
$_SESSION['id_jugador'] = $id_jugador;
$_SESSION['id_partida'] = $id_partida;
$_SESSION['nombre'] = $nombre;

header("Location: letra.php");
