<?php
session_start();
include("conectar.php");

if (!isset($_SESSION['id_partida'])) {
    header("Location: index.php");
    exit;
}

$id_partida = $_SESSION['id_partida'];
$categorias_lista = ["nombre", "apellido", "flor_fruto", "animal", "color", "cosa", "pais", "verbo"];

$jugadores = [];
$query_jugadores = "SELECT id_jugador, nombre FROM jugadores WHERE partida_id = $id_partida ORDER BY id_jugador ASC";
$result_jugadores = mysqli_query($conn, $query_jugadores);

while ($row = mysqli_fetch_assoc($result_jugadores)) {
    $jugadores[(int) $row['id_jugador']] = [
        'id' => (int) $row['id_jugador'],
        'nombre' => $row['nombre'],
    ];
}

// 1. Obtener todas las respuestas de la partida
$query = "SELECT r.id_respuesta, r.categoria, r.palabra, r.puntos, r.jugador_id, j.nombre 
          FROM respuestas r 
          JOIN jugadores j ON r.jugador_id = j.id_jugador 
          WHERE j.partida_id = $id_partida";
$result = mysqli_query($conn, $query);

$respuestas = [];
while ($row = mysqli_fetch_assoc($result)) {
    $respuestas[] = $row;
}

// 2. Calcular frecuencias por categoría y palabra solo con respuestas validas
$frecuencias = []; // [categoria][palabra] => count
foreach ($respuestas as $resp) {
    if (empty($resp['palabra']) || (int) $resp['puntos'] <= 0) continue;

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

foreach ($jugadores as $id_jugador => $jugador) {
    $puntos_por_jugador[$id_jugador] = ['nombre' => $jugador['nombre'], 'total' => 0, 'id' => $id_jugador];
}

foreach ($respuestas as $resp) {
    $puntos = 0;
    $cat = $resp['categoria'];
    $pal = $resp['palabra'];
    $respuesta_valida = (int) $resp['puntos'] > 0;

    if (!empty($pal) && $respuesta_valida) {
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
            padding: 24px;
            color: #3c3c3c;
        }

        .page {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        h1 {
            color: #ffc800;
            text-transform: uppercase;
            font-weight: 900;
            font-size: clamp(2rem, 4vw, 3rem);
            text-shadow: 2px 2px 0 #e6b400;
            margin: 0 0 18px;
        }

        .winner {
            background: #ffc800;
            color: white;
            padding: 18px 24px;
            border-radius: 20px;
            font-size: clamp(1.2rem, 2.2vw, 1.8rem);
            font-weight: 900;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 28px;
            box-shadow: 0 4px 0 #e6b400;
        }

        .table-shell {
            background: white;
            border-radius: 22px;
            overflow: hidden;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            min-width: 980px;
            background: white;

        }

        th {
            background-color: #1cb0f6;
            color: white;
            padding: 16px 14px;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.08em;
        }

        td {
            padding: 16px 14px;
            border-bottom: 1px solid #edf1f5;
            color: #3c3c3c;
            font-weight: 700;
            vertical-align: top;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tbody tr:nth-child(even) td {
            background: #fbfcfe;
        }

        tbody tr:hover td {
            background-color: #f0faff;
        }

        .player-name {
            font-size: 1.25rem;
            font-weight: 900;
            white-space: nowrap;
        }

        .cell-word {
            display: block;
            min-height: 1.4em;
            font-size: 0.98rem;
            word-break: break-word;
        }

        .cell-word.empty {
            color: #b7bec8;
        }

        .cell-points {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 800;
            background: #edf7ff;
            color: #1c7eb8;
        }

        .cell-points.zero {
            background: #f1f3f5;
            color: #7a848f;
        }

        .total-score {
            font-size: 1.5rem;
            font-weight: 900;
            white-space: nowrap;
        }

        .btn {
            display: inline-block;
            margin-top: 32px;
            background-color: #58cc02;
            color: white;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 800;
            border-radius: 15px;
            text-decoration: none;
            box-shadow: 0 3px 0 #46a302;
            transition: transform 0.1s;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        .btn:active {
            transform: translateY(5px);
            box-shadow: none;
        }

        @media (max-width: 768px) {
            body {
                padding: 16px;
            }

            .winner {
                width: calc(100% - 8px);
                box-sizing: border-box;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <h1>Resultados de la Ronda</h1>

        <?php if (count($puntos_por_jugador) > 0): ?>
            <div class="winner">
                <span>👑</span>
                <span>Ganador: <?php echo htmlspecialchars($puntos_por_jugador[0]['nombre']); ?> (<?php echo $puntos_por_jugador[0]['total']; ?> pts)</span>
            </div>
        <?php endif; ?>

        <div class="table-shell">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Jugador</th>
                            <?php foreach ($categorias_lista as $cat_header): ?>
                                <th><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $cat_header))); ?></th>
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
                                <td><span class="player-name"><?php echo htmlspecialchars($player['nombre']); ?></span></td>
                                <?php foreach ($categorias_lista as $cat):
                                    $data = $cats[$cat] ?? ['palabra' => '', 'puntos' => 0];
                                    $palabra = trim($data['palabra']);
                                    $palabra_clase = $palabra === '' ? 'cell-word empty' : 'cell-word';
                                    $puntos_clase = (int) $data['puntos'] === 0 ? 'cell-points zero' : 'cell-points';
                                ?>
                                    <td>
                                        <span class="<?php echo $palabra_clase; ?>">
                                            <?php echo $palabra === '' ? 'Sin respuesta' : htmlspecialchars($palabra); ?>
                                        </span>
                                        <span class="<?php echo $puntos_clase; ?>"><?php echo (int) $data['puntos']; ?> pts</span>
                                    </td>
                                <?php endforeach; ?>
                                <td><span class="total-score"><?php echo $player['total']; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <a href="index.php" class="btn">Volver al Inicio</a>
    </div>

</body>

</html>
