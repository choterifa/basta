<?php
session_start();
include("conectar.php");

if (!isset($_SESSION['id_partida']) || !isset($_SESSION['id_jugador'])) {
    header("Location: index.php");
    exit;
}

$id_partida = $_SESSION['id_partida'];
$id_jugador = $_SESSION['id_jugador'];

// Marcar partida como finalizada (si soy el primero en enviar o se acabó el tiempo)
mysqli_query($conn, "UPDATE partidas SET estado='finalizada' WHERE id_partida=$id_partida");

// Categorias
$categorias = ["nombre", "apellido", "flor_fruto", "animal", "color", "cosa", "pais", "verbo"];

foreach ($categorias as $cat) {
    if (isset($_POST[$cat])) {
        // Sanitizar entrada
        $palabra = mysqli_real_escape_string($conn, trim(strtoupper($_POST[$cat])));

        // Insertar respuesta
        $query = "INSERT INTO respuestas (jugador_id, categoria, palabra, puntos) 
                  VALUES ('$id_jugador', '$cat', '$palabra', 0)";
        mysqli_query($conn, $query);
    }
}

// Redirigir a resultados
header("Location: resultados.php");
