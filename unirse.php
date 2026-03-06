<?php
session_start();
include("conectar.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $partida_id = $_POST['partida'];

    // Verificar si existe la partida
    $check = mysqli_query($conn, "SELECT id_partida, estado FROM partidas WHERE id_partida = '$partida_id'");
    if (mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);

        // Crear jugador
        mysqli_query($conn, "INSERT INTO jugadores (nombre, partida_id) VALUES ('$nombre','$partida_id')");
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
