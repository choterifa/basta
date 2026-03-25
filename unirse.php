<?php
session_start();
include("conectar.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $partida_id = isset($_POST['partida']) ? (int) $_POST['partida'] : 0;

    if ($nombre === '' || strlen($nombre) < 3) {
        exit("Nombre invalido. <a href='index.php'>Volver</a>");
    }

    if ($partida_id <= 0) {
        exit("ID de partida invalido. <a href='index.php'>Volver</a>");
    }

    $nombre = mysqli_real_escape_string($conn, $nombre);

    // Verificar si existe la partida y está en espera
    $check = mysqli_query($conn, "SELECT id_partida, estado FROM partidas WHERE id_partida = $partida_id AND estado = 'esperando'");
    if (mysqli_num_rows($check) > 0) {
        // Crear jugador (no host)
        mysqli_query($conn, "INSERT INTO jugadores (nombre, partida_id, es_host) VALUES ('$nombre', $partida_id, 0)");
        $id_jugador = mysqli_insert_id($conn);

        // Guardar en sesión
        $_SESSION['id_jugador'] = $id_jugador;
        $_SESSION['id_partida'] = $partida_id;
        $_SESSION['nombre'] = $nombre;
        $_SESSION['es_host'] = 0;

        header("Location: lobby.php");
    } else {
        echo "La partida no existe o ya comenzó. <a href='index.php'>Volver</a>";
    }
}
?>
