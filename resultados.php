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

// 0. Sincronización robusta: Esperar a que el total esperado de respuestas esté en la BD
include("round_sync.php");
$deadline_ms = readRoundDeadlineMs($id_partida);
$current_ms = (int) round(microtime(true) * 1000);

// Contar total de registros en respuestas para esta partida
$query_count = "SELECT COUNT(*) as total_filas, COUNT(DISTINCT jugador_id) as total_enviaron 
                FROM respuestas 
                WHERE jugador_id IN (SELECT id_jugador FROM jugadores WHERE partida_id = $id_partida)";
$res_count = mysqli_query($conn, $query_count);
$row_count = mysqli_fetch_assoc($res_count);
$total_filas = (int) $row_count['total_filas'];
$total_enviaron = (int) $row_count['total_enviaron'];

$total_jugadores = count($jugadores);
$filas_esperadas = $total_jugadores * 8; 

// Tiempo de gracia: 12 segundos desde que se creó el deadline o se detectó que faltan.
$wait_buffer = 12000;
$should_wait = ($total_filas < $filas_esperadas);

// Si tenemos deadline, respetamos el buffer desde el deadline
if ($deadline_ms && $current_ms > ($deadline_ms + $wait_buffer)) {
    $should_wait = false; 
}

// Si alguien presionó forzar cálculo
if (isset($_GET['force'])) $should_wait = false;

if ($should_wait) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="refresh" content="1">
        <title>Sincronizando...</title>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Nunito', sans-serif; background: #f7f7f7; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .wait-box { text-align: center; background: white; padding: 40px; border-radius: 30px; box-shadow: 0 10px 0 #e5e5e5; max-width: 450px; width: 90%; }
            .loader { border: 8px solid #f3f3f3; border-top: 8px solid #1cb0f6; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto 20px; }
            @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            .btn-force { display: inline-block; margin-top: 20px; color: #afafaf; text-decoration: none; font-size: 0.8rem; border: 1px solid #ddd; padding: 5px 15px; border-radius: 10px; transition: all 0.2s; }
            .btn-force:hover { background: #eee; color: #3c3c3c; }
            .pills { display: flex; gap: 5px; justify-content: center; margin-top: 15px; }
            .pill { width: 10px; height: 10px; border-radius: 50%; background: #ddd; }
            .pill.active { background: var(--secondary, #1cb0f6); }
        </style>
    </head>
    <body>
        <div class="wait-box">
            <div class="loader"></div>
            <h2 style="margin:0; color:#3c3c3c;">Sincronizando puntuación...</h2>
            <p style="color:#afafaf;"><?php echo $total_enviaron; ?> de <?php echo $total_jugadores; ?> jugadores listos.</p>
            <div style="margin: 15px 0; background: #eee; height: 12px; border-radius: 6px; overflow: hidden; border: 2px solid #fff;">
                <div style="background: #1cb0f6; width: <?php echo ($total_filas/$filas_esperadas)*100; ?>%; height: 100%; transition: width 0.3s;"></div>
            </div>
            <p style="font-size: 0.85rem; color: #3c3c3c; font-weight: 700;">Recibidas <?php echo $total_filas; ?> de <?php echo $filas_esperadas; ?> respuestas.</p>
            <a href="?force=1" class="btn-force">¿No carga? Calcular con lo que hay</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// 1. Obtener todas las respuestas de la partida
$query = "SELECT r.id_respuesta, r.categoria, r.palabra, r.puntos, r.jugador_id, j.nombre 
          FROM respuestas r 
          JOIN jugadores j ON r.jugador_id = j.id_jugador 
          WHERE j.partida_id = $id_partida";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conn));
}

$respuestas = [];
while ($row = mysqli_fetch_assoc($result)) {
    $respuestas[] = $row;
}

// Debug (optional, remove in production)
// if (count($respuestas) === 0) { /* Log something? */ }

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
    $pal = strtoupper(trim($resp['palabra']));
    
    // Si la palabra estaba marcada con puntos en la DB, es potencialmente válida
    $respuesta_valida_base = (int) $resp['puntos'] > 0;

    if (!empty($pal) && $respuesta_valida_base) {
        $count = $frecuencias[$cat][$pal] ?? 0;

        if ($count == 1) $puntos = 100;
        elseif ($count == 2) $puntos = 50;
        elseif ($count == 3) $puntos = 25;
        elseif ($count >= 4) $puntos = 10;
    }

    $id_jugador = (int)$resp['jugador_id'];
    $nombre_jugador = $resp['nombre'];

    if (!isset($puntos_por_jugador[$id_jugador])) {
        $puntos_por_jugador[$id_jugador] = ['nombre' => $nombre_jugador, 'total' => 0, 'id' => $id_jugador];
    }
    $puntos_por_jugador[$id_jugador]['total'] += $puntos;

    $detalles_jugador[$id_jugador][$cat] = ['palabra' => $pal, 'puntos' => $puntos];
}

// Ordenar ganadores por total descendente
usort($puntos_por_jugador, function ($a, $b) {
    return $b['total'] - $a['total'];
});

?>
<!DOCTYPE html>
<html>

<head>
    <title>Resultados Basta</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #58cc02;
            --primary-dark: #46a302;
            --secondary: #1cb0f6;
            --secondary-dark: #1899d6;
            --bg: #f7f7f7;
            --text: #3c3c3c;
            --muted: #afafaf;
            --border: #e5e5e5;
            --gold: #ffc800;
            --gold-dark: #e6b400;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--bg);
            margin: 0;
            padding: 24px;
            color: var(--text);
        }

        .page {
            max-width: 1300px;
            margin: 0 auto;
            text-align: center;
        }

        h1 {
            color: var(--gold);
            text-transform: uppercase;
            font-weight: 900;
            font-size: clamp(2rem, 4vw, 3rem);
            text-shadow: 2px 2px 0 var(--gold-dark);
            margin: 0 0 18px;
        }

        .table-shell {
            background: white;
            border-radius: 28px;
            box-shadow: 0 12px 0 var(--border);
            overflow: hidden;
            margin-top: 20px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            min-width: 1000px;
            background: white;
        }

        th {
            background-color: var(--secondary);
            color: white;
            padding: 20px 15px;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        td {
            padding: 18px 15px;
            border-bottom: 2px solid var(--bg);
            color: var(--text);
            font-weight: 700;
        }

        tbody tr:hover td {
            background-color: #f0faff;
        }

        .player-name {
            font-size: 1.1rem;
            font-weight: 900;
        }

        .cell-word {
            display: block;
            font-size: 1rem;
            word-break: break-word;
        }

        .cell-word.empty {
            color: var(--muted);
            font-style: italic;
        }

        .cell-points {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 0.8rem;
            font-weight: 900;
            background: #edf7ff;
            color: var(--secondary-dark);
        }

        .cell-points.zero {
            background: #f1f3f5;
            color: var(--muted);
        }

        .total-score {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--primary);
        }

        /* Modal / Popover Winner */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            backdrop-filter: blur(5px);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .winner-card {
            background: white;
            padding: 50px 40px;
            border-radius: 35px;
            box-shadow: 0 15px 0 var(--gold-dark);
            text-align: center;
            max-width: 400px;
            width: 90%;
            transform: scale(0.8);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }

        .modal-overlay.show .winner-card {
            transform: scale(1);
        }

        .winner-crown {
            font-size: 5rem;
            display: block;
            margin-bottom: 15px;
            animation: bounce 1s infinite alternate;
        }

        .winner-title {
            color: var(--gold);
            font-weight: 900;
            font-size: 2.5rem;
            margin: 0;
            text-shadow: 2px 2px 0 var(--gold-dark);
        }

        .winner-name {
            font-size: 2rem;
            font-weight: 900;
            margin: 15px 0;
            color: var(--text);
        }

        .winner-score {
            background: var(--primary);
            color: white;
            padding: 10px 25px;
            border-radius: 20px;
            font-size: 1.5rem;
            font-weight: 900;
            display: inline-block;
            box-shadow: 0 5px 0 var(--primary-dark);
            margin-bottom: 30px;
        }

        .close-btn {
            background: var(--secondary);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 900;
            border-radius: 16px;
            cursor: pointer;
            box-shadow: 0 5px 0 var(--secondary-dark);
            width: 100%;
            text-transform: uppercase;
        }

        .close-btn:active {
            transform: translateY(5px);
            box-shadow: none;
        }

        .btn-home {
            display: inline-block;
            margin-top: 30px;
            background: var(--primary);
            color: white;
            padding: 15px 50px;
            font-size: 1.2rem;
            font-weight: 900;
            border-radius: 18px;
            text-decoration: none;
            box-shadow: 0 6px 0 var(--primary-dark);
        }

        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-20px); }
        }

        @media (max-width: 768px) {
            body { padding: 15px; }
        }
    </style>
</head>

<body>
    <!-- Winner Modal -->
    <?php if (count($puntos_por_jugador) > 0): 
        $ganador = $puntos_por_jugador[0];
    ?>
    <div class="modal-overlay show" id="winnerModal">
        <div class="winner-card">
            <span class="winner-crown">👑</span>
            <h2 class="winner-title">¡GANADOR!</h2>
            <div class="winner-name"><?php echo htmlspecialchars($ganador['nombre']); ?></div>
            <div class="winner-score"><?php echo $ganador['total']; ?> Puntos</div>
            <button class="close-btn" onclick="document.getElementById('winnerModal').classList.remove('show')">¡Genial!</button>
        </div>
    </div>
    <?php endif; ?>

    <div class="page">
        <h1>Resultados finales</h1>

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
                        <?php 
                        foreach ($puntos_por_jugador as $player):
                            $pid = (int)$player['id'];
                            $cats = $detalles_jugador[$pid] ?? [];
                        ?>
                            <tr>
                                <td><span class="player-name"><?php echo htmlspecialchars($player['nombre']); ?></span></td>
                                <?php foreach ($categorias_lista as $cat):
                                    $data = $cats[$cat] ?? ['palabra' => '', 'puntos' => 0];
                                    $palabra = trim($data['palabra']);
                                    $display_text = $palabra === '' ? 'Sin respuesta' : htmlspecialchars($palabra);
                                    $palabra_clase = $palabra === '' ? 'cell-word empty' : 'cell-word';
                                    $puntos_clase = (int) $data['puntos'] === 0 ? 'cell-points zero' : 'cell-points';
                                ?>
                                    <td>
                                        <span class="<?php echo $palabra_clase; ?>">
                                            <?php echo $display_text; ?>
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

        <a href="index.php" class="btn-home">Volver al Inicio</a>
    </div>

    <script>
        // Auto-close modal after 10 seconds if user doesn't click
        setTimeout(() => {
            const modal = document.getElementById('winnerModal');
            if (modal) modal.classList.remove('show');
        }, 10000);
    </script>
</body>

</html>
