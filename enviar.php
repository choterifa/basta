<?php
session_start();
include("conectar.php");
include("round_sync.php");

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

// Marcar partida como finalizada y mantener el mismo cierre compartido
ensureRoundDeadlineMs($conn, $id_partida);

// Categorias
$categorias = ["nombre", "apellido", "flor_fruto", "animal", "color", "cosa", "pais", "verbo"];

$respuestas_jugador = [];
$values = [];

foreach ($categorias as $cat) {
    if (isset($_POST[$cat])) {
        // Sanitizar entrada: quitar símbolos y dejar solo letras/espacios
        $palabraOriginal = trim($_POST[$cat]);
        $palabraLimpia = preg_replace("/[^a-zA-Z\s\x{00C0}-\x{017F}]/u", '', $palabraOriginal);
        $palabra = mysqli_real_escape_string($conn, strtoupper(trim($palabraLimpia)));
        
        $esNombreSinNumeros = !in_array($cat, ["nombre", "apellido"], true) || preg_match("/^[\\p{L}\\s'-]+$/u", $palabraOriginal);
        
        // Validación: 3 caracteres, no solo letra repetida, no duplicada
        $esSoloLetraRepetida = preg_match("/^$letra+$/i", $palabra);
        $tieneLongitudMinima = strlen($palabra) >= 3;
        $esDuplicada = in_array($palabra, $respuestas_jugador);
        
        $puntos = 0; 
        if ($palabra !== '' && $esNombreSinNumeros && !$esSoloLetraRepetida && $tieneLongitudMinima && !$esDuplicada) {
            $primeraLetra = substr($palabra, 0, 1);
            if ($primeraLetra === $letra) {
                $puntos = 10; 
                $respuestas_jugador[] = $palabra;
            }
        }

        $values[] = "('$id_jugador', '$cat', '$palabra', $puntos)";
    }
}

if (!empty($values)) {
    $query = "INSERT INTO respuestas (jugador_id, categoria, palabra, puntos) VALUES " . implode(',', $values);
    mysqli_query($conn, $query) or die(mysqli_error($conn));
}

// Redirigir a resultados
header("Location: resultados.php");
