<?php
session_start();
include("conectar.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $partida_id = isset($_POST['partida']) ? (int) $_POST['partida'] : 0;

    if ($nombre === '' || !preg_match("/^[\\p{L}\\s'-]+$/u", $nombre)) {
        exit("Nombre invalido. Solo se permiten letras, espacios, guion y apostrofe. <a href='index.php'>Volver</a>");
    }

    if ($partida_id <= 0) {
        exit("ID de partida invalido. <a href='index.php'>Volver</a>");
    }

    $nombre = mysqli_real_escape_string($conn, $nombre);

    // Verificar si existe la partida
    $check = mysqli_query($conn, "SELECT id_partida, estado FROM partidas WHERE id_partida = $partida_id");
    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);

        // Crear jugador
        mysqli_query($conn, "INSERT INTO jugadores (nombre, partida_id) VALUES ('$nombre', $partida_id)");
        $id_jugador = mysqli_insert_id($conn);

        // Guardar en sesión
        $_SESSION['id_jugador'] = $id_jugador;
        $_SESSION['id_partida'] = $partida_id;
        $_SESSION['nombre'] = $nombre;

        header("Location: letra.php");
    } else {
        echo "La partida no existe. <a href='index.php'>Volver</a>";
    }
}
