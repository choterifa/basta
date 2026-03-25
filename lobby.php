<?php
session_start();
include("conectar.php");

if (!isset($_SESSION['id_partida'])) {
    header("Location: index.php");
    exit;
}

$id_partida = $_SESSION['id_partida'];
$nombre_jugador = $_SESSION['nombre'];
$es_host = $_SESSION['es_host'];
?>
<!DOCTYPE html>
<html>

<head>
    <title>Basta! - Sala de Espera</title>
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
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 12px 0 var(--border);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        h1 {
            color: var(--primary);
            font-weight: 900;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .game-id {
            background: var(--secondary);
            color: white;
            padding: 10px 20px;
            border-radius: 12px;
            display: inline-block;
            font-weight: 900;
            font-size: 1.2rem;
            margin-bottom: 30px;
            box-shadow: 0 4px 0 var(--secondary-dark);
        }

        .player-list-container {
            text-align: left;
            margin-bottom: 30px;
        }

        .player-list-container h2 {
            font-size: 1rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            border-bottom: 2px solid var(--border);
            padding-bottom: 8px;
        }

        .player-item {
            background: #fff;
            border: 2px solid var(--border);
            padding: 12px 15px;
            border-radius: 14px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            font-weight: 800;
        }

        .player-avatar {
            width: 35px;
            height: 35px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 12px;
            font-size: 1.1rem;
        }

        .badge-host {
            background: #ffc800;
            color: #8a6a00;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 6px;
            margin-left: auto;
            text-transform: uppercase;
        }

        .waiting-msg {
            font-style: italic;
            color: var(--muted);
            margin-top: 20px;
        }

        .btn {
            width: 100%;
            border: none;
            padding: 18px;
            font-size: 1.2rem;
            font-weight: 900;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.1s;
            text-transform: uppercase;
            color: white;
            background-color: var(--primary);
            box-shadow: 0 6px 0 var(--primary-dark);
        }

        .btn:active {
            transform: translateY(4px);
            box-shadow: none;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .loading-dots:after {
            content: '.';
            animation: dots 1.5s steps(5, end) infinite;
        }

        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60% { content: '...'; }
            80%, 100% { content: ''; }
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>SALA DE ESPERA</h1>
        <p style="color:var(--muted); font-weight:700;">Código de Partida:</p>
        <div class="game-id">#<?php echo $id_partida; ?></div>

        <div class="player-list-container">
            <h2>Jugadores Unidos</h2>
            <div id="player-list">
                <!-- Se carga dinámicamente -->
            </div>
        </div>

        <?php if ($es_host): ?>
            <button class="btn" id="start-btn">Iniciar Partida</button>
            <p class="waiting-msg" style="color:var(--primary);">¡Eres el Host! Inicia cuando estén todos listos.</p>
        <?php else: ?>
            <div class="waiting-msg">Esperando a que el host inicie la partida<span class="loading-dots"></span></div>
        <?php endif; ?>
    </div>

    <script>
        const playerList = document.getElementById('player-list');
        const COLORS = ['#1cb0f6', '#58cc02', '#ce82ff', '#ff4b4b', '#ffc800', '#ff9600'];

        async function updateLobby() {
            try {
                const res = await fetch('check_lobby.php');
                const data = await res.json();

                if (data.status === 'en curso') {
                    window.location.href = 'letra.php';
                    return;
                }

                playerList.innerHTML = data.players.map((p, i) => `
                    <div class="player-item">
                        <div class="player-avatar" style="background:${COLORS[i % COLORS.length]}">
                            ${p.nombre.charAt(0).toUpperCase()}
                        </div>
                        <span>${p.nombre} ${p.id_jugador == <?php echo $_SESSION['id_jugador']; ?> ? '(Tú)' : ''}</span>
                        ${p.es_host == 1 ? '<span class="badge-host">Host</span>' : ''}
                    </div>
                `).join('');

            } catch (e) {
                console.error("Error polling lobby:", e);
            }
        }

        setInterval(updateLobby, 2000);
        updateLobby();

        <?php if ($es_host): ?>
        document.getElementById('start-btn').addEventListener('click', async () => {
            const btn = document.getElementById('start-btn');
            btn.disabled = true;
            btn.textContent = 'Iniciando...';
            
            try {
                const res = await fetch('iniciar_partida.php', { method: 'POST' });
                const data = await res.json();
                if (data.success) {
                    window.location.href = 'letra.php';
                } else {
                    alert('Error al iniciar partida: ' + data.error);
                    btn.disabled = false;
                    btn.textContent = 'Iniciar Partida';
                }
            } catch (e) {
                console.error(e);
                btn.disabled = false;
                btn.textContent = 'Iniciar Partida';
            }
        });
        <?php endif; ?>
    </script>
</body>

</html>
