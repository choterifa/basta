<?php
session_start();
include("conectar.php");

if (!isset($_SESSION['id_partida']) || !isset($_SESSION['id_jugador'])) {
    header("Location: index.php");
    exit;
}

$id_partida = $_SESSION['id_partida'];
$id_jugador = $_SESSION['id_jugador'];

// Obtener la letra actual de la partida
$query = mysqli_query($conn, "SELECT letra_actual FROM partidas WHERE id_partida = $id_partida");
$partida = mysqli_fetch_assoc($query);
$letra = $partida['letra_actual'];

// Marcar partida como finalizada (si soy el primero en enviar o se acabó el tiempo)
mysqli_query($conn, "UPDATE partidas SET estado='finalizada' WHERE id_partida=$id_partida");

// Categorias
$categorias = ["nombre", "apellido", "flor_fruto", "animal", "color", "cosa", "pais", "verbo"];

foreach ($categorias as $cat) {
    if (isset($_POST[$cat])) {
        // Sanitizar entrada
        $palabra = mysqli_real_escape_string($conn, trim(strtoupper($_POST[$cat])));
        $esNombreSinNumeros = !in_array($cat, ["nombre", "apellido"], true) || preg_match("/^[\\p{L}\\s'-]+$/u", $_POST[$cat]);
        
        // Validar que la palabra empiece con la letra correcta (si no está vacía)
        $puntos = 0; // Por defecto 0 puntos
        if ($palabra !== '' && $esNombreSinNumeros) {
            $primeraLetra = substr($palabra, 0, 1);
            if ($primeraLetra === $letra) {
                $puntos = 10; // Palabras válidas valen 10 puntos
            }
        }

        // Insertar respuesta
        $query = "INSERT INTO respuestas (jugador_id, categoria, palabra, puntos) 
                  VALUES ('$id_jugador', '$cat', '$palabra', $puntos)";
        mysqli_query($conn, $query);
    }
}

// Redirigir a resultados
header("Location: resultados.php");
