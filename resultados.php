<?php
session_start();
include("conectar.php");

if (!isset($_SESSION['id_partida'])) {
    header("Location: index.php");
    exit;
}

$id_partida = $_SESSION['id_partida'];

// 1. Obtener todas las respuestas de la partida
$query = "SELECT r.id_respuesta, r.categoria, r.palabra, r.jugador_id, j.nombre 
          FROM respuestas r 
          JOIN jugadores j ON r.jugador_id = j.id_jugador 
          WHERE j.partida_id = $id_partida";
$result = mysqli_query($conn, $query);

$respuestas = [];
while ($row = mysqli_fetch_assoc($result)) {
    $respuestas[] = $row;
}

// 2. Calcular frecuencias por categoría y palabra
$frecuencias = []; // [categoria][palabra] => count
foreach ($respuestas as $resp) {
    if (empty($resp['palabra'])) continue;

    $cat = $resp['categoria'];
    $pal = $resp['palabra'];

    if (!isset($frecuencias[$cat][$pal])) {
        $frecuencias[$cat][$pal] = 0;
    }
    $frecuencias[$cat][$pal]++;
}

// 3. Asignar puntos
$puntos_por_jugador = []; // [jugador_id] => ['nombre'=>, 'total'=>, 'id'=>]
$detalles_jugador = []; // [jugador_id][categoria] => [palabra, puntos]
$categorias_lista = ["nombre", "apellido", "flor_fruto", "animal", "color", "cosa", "pais", "verbo"];

foreach ($respuestas as $resp) {
    $puntos = 0;
    $cat = $resp['categoria'];
    $pal = $resp['palabra'];

    if (!empty($pal)) {
        $count = $frecuencias[$cat][$pal] ?? 0;

        if ($count == 1) $puntos = 100;
        elseif ($count == 2) $puntos = 50;
        elseif ($count == 3) $puntos = 25;
        else $puntos = 0; // >= 4
    }

    $id_jugador = $resp['jugador_id'];
    $nombre_jugador = $resp['nombre'];

    if (!isset($puntos_por_jugador[$id_jugador])) {
        $puntos_por_jugador[$id_jugador] = ['nombre' => $nombre_jugador, 'total' => 0, 'id' => $id_jugador];
    }
    $puntos_por_jugador[$id_jugador]['total'] += $puntos;

    $detalles_jugador[$id_jugador][$cat] = ['palabra' => $pal, 'puntos' => $puntos];
}

// Ordenar ganadores
usort($puntos_por_jugador, function ($a, $b) {
    return $b['total'] - $a['total'];
});

?>

<!DOCTYPE html>
<html>

<head>
    <title>Resultados Basta</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 20px;
            text-align: center;
        }

        h1 {
            color: #ffc800;
            text-transform: uppercase;
            font-weight: 900;
            font-size: 2.5rem;
            text-shadow: 2px 2px 0 #e6b400;
            margin-bottom: 10px;
        }

        .winner {
            background: #ffc800;
            color: white;
            padding: 20px;
            border-radius: 20px;
            font-size: 1.8rem;
            font-weight: 900;
            display: inline-block;
            margin-bottom: 30px;
            box-shadow: 0 6px 0 #e6b400;
            animation: bounce 1s infinite alternate;
        }

        table {
            margin: 0 auto;
            border-collapse: separate;
            border-spacing: 0;
            width: 95%;
            max-width: 1000px;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 0 #e5e5e5;
        }

        th {
            background-color: #1cb0f6;
            color: white;
            padding: 15px;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }

        td {
            padding: 12px;
            border-bottom: 2px solid #f7f7f7;
            color: #3c3c3c;
            font-weight: 700;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: #f0faff;
        }

        small {
            display: block;
            margin-top: 4px;
            color: #888;
            font-weight: 600;
            font-size: 0.8em;
        }

        .btn {
            display: inline-block;
            margin-top: 40px;
            background-color: #58cc02;
            color: white;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 800;
            border-radius: 15px;
            text-decoration: none;
            box-shadow: 0 5px 0 #46a302;
            transition: transform 0.1s;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        .btn:active {
            transform: translateY(5px);
            box-shadow: none;
        }

        @keyframes bounce {
            from {
                transform: translateY(0);
            }

            to {
                transform: translateY(-10px);
            }
        }
    </style>
</head>

<body>

    <h1>Resultados de la Ronda</h1>

    <?php if (count($puntos_por_jugador) > 0): ?>
        <div class="winner">
            👑 Ganador: <?php echo $puntos_por_jugador[0]['nombre']; ?> (<?php echo $puntos_por_jugador[0]['total']; ?> pts)
        </div>
    <?php endif; ?>

    <br>

    <table>
        <thead>
            <tr>
                <th>Jugador</th>
                <?php foreach ($categorias_lista as $cat_header): ?>
                    <th><?php echo ucfirst(str_replace('_', ' ', $cat_header)); ?></th>
                <?php endforeach; ?>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($puntos_por_jugador as $player):
                $pid = $player['id'];
                $cats = $detalles_jugador[$pid] ?? [];
            ?>
                <tr>
                    <td><strong><?php echo $player['nombre']; ?></strong></td>
                    <?php foreach ($categorias_lista as $cat):
                        $data = $cats[$cat] ?? ['palabra' => '-', 'puntos' => 0];
                    ?>
                        <td>
                            <?php echo $data['palabra']; ?>
                            <br>
                            <small style="color: blue;">(<?php echo $data['puntos']; ?>pts)</small>
                        </td>
                    <?php endforeach; ?>
                    <td><strong><?php echo $player['total']; ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br><br>
    <a href="index.php" class="btn">Volver al Inicio</a>

</body>

</html>