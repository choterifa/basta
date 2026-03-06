<?php
session_start();
include("conectar.php");

if (!isset($_SESSION['id_partida'])) {
    header("Location: index.php");
    exit;
}

$id_partida = $_SESSION['id_partida'];
$id_jugador = $_SESSION['id_jugador'];

// Obtener estado actual
$query = mysqli_query($conn, "SELECT letra_actual, estado, tiempo_inicio FROM partidas WHERE id_partida = $id_partida");
$partida = mysqli_fetch_assoc($query);

$letra = $partida['letra_actual'];

// Si no hay letra, generar una (solo si el estado es 'en curso')
if (!$letra && $partida['estado'] == 'en curso') {
    $letras = range('A', 'Z');
    $letra = $letras[array_rand($letras)];
    mysqli_query($conn, "UPDATE partidas SET letra_actual='$letra' WHERE id_partida=$id_partida");
}

// Calcular tiempo restante
$tiempo_limite = 60; // Duración total en segundos
$tiempo_inicio = $partida['tiempo_inicio'] ? $partida['tiempo_inicio'] : time(); // Fallback por si es null
$tiempo_transcurrido = time() - $tiempo_inicio;
$tiempo_restante = $tiempo_limite - $tiempo_transcurrido;

if ($tiempo_restante < 0) $tiempo_restante = 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Jugando Basta - Letra <?php echo $letra; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 0 #e5e5e5;
            width: 100%;
            max-width: 600px;
        }
        h1 {
            color: #3c3c3c;
            text-align: center;
            margin-top: 0;
            font-weight: 900;
        }
        h2 {
            text-align: center;
            color: #777;
            font-size: 1.2rem;
            margin-top: -10px;
        }
        #letra-display {
            display: inline-block;
            background: #1cb0f6;
            color: white;
            padding: 5px 20px;
            border-radius: 15px;
            font-size: 2.5rem;
            font-weight: 900;
            box-shadow: 0 6px 0 #1899d6;
            transform: rotate(-3deg) translateY(-5px);
            margin-left: 10px;
        }
        .timer {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #ff4b4b;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 900;
            font-size: 1.5rem;
            box-shadow: 0 4px 0 #d43b3b;
            z-index: 100;
            animation: pulse 1s infinite;
        }

        form {
            display: grid;
            gap: 15px;
            margin-top: 20px;
        }
        
        label {
            font-weight: 800;
            color: #777;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        input {
            padding: 12px;
            border: 2px solid #e5e5e5;
            border-radius: 12px;
            font-size: 1.1rem;
            font-family: 'Nunito', sans-serif;
            background: #f7f7f7;
            outline: none;
            transition: all 0.2s;
            width: 100%;
            box-sizing: border-box; /* Fix width overflow */
        }
        input:focus {
            border-color: #1cb0f6;
            background: #fff;
            transform: scale(1.01);
        }

        button.btn-basta {
            background-color: #ff4b4b;
            color: white;
            border: none;
            padding: 20px;
            font-size: 2rem;
            font-weight: 900;
            border-radius: 16px;
            cursor: pointer;
            box-shadow: 0 6px 0 #d43b3b;
            text-transform: uppercase;
            margin-top: 20px;
            width: 100%;
            transition: all 0.1s;
        }
        button.btn-basta:active {
            transform: translateY(6px);
            box-shadow: none;
        }

        @keyframes pulse {
           0% { transform: scale(1); }
           50% { transform: scale(1.05); }
           100% { transform: scale(1); }
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Juego en curso</h1>
        <h2>Letra: <span id="letra-display"><?php echo $letra; ?></span></h2>

        <div id="timer-display" class="timer"><?php echo $tiempo_restante; ?></div>

        <form id="game-form" action="enviar.php" method="post">

            <label>Nombre:</label>
            <input type="text" name="nombre" autocomplete="off">

            <label>Apellido:</label>
            <input type="text" name="apellido" autocomplete="off">

            <label>Flor o Fruto:</label>
            <input type="text" name="flor_fruto" autocomplete="off">

            <label>Animal:</label>
            <input type="text" name="animal" autocomplete="off">

            <label>Color:</label>
            <input type="text" name="color" autocomplete="off">

            <label>Cosa:</label>
            <input type="text" name="cosa" autocomplete="off">

            <label>País:</label>
            <input type="text" name="pais" autocomplete="off">

            <label>Verbo:</label>
            <input type="text" name="verbo" autocomplete="off">

            <br>
            <button type="button" class="btn-basta" onclick="stopGame()">
                ¡BASTA!
            </button>

        </form>
    </div>

    <script>
        let timeLeft = <?php echo $tiempo_restante; ?>;
        const timerDisplay = document.getElementById('timer-display');
        const form = document.getElementById('game-form');
        let gameEnded = false;

        // Initial display
        timerDisplay.textContent = timeLeft;

        function endGame(message) {
            if (gameEnded) return;
            gameEnded = true;
            clearInterval(timerInterval);
            clearInterval(pollingInterval);
            if (message) alert(message);
            form.submit();
        }

        // Temporizador
        const timerInterval = setInterval(() => {
            if (gameEnded) return;
            timeLeft--;
            if (timeLeft >= 0) {
                timerDisplay.textContent = timeLeft;
            }
            if (timeLeft <= 0) {
                endGame("¡Tiempo fuera!");
            }
        }, 1000);

        // Función para el botón BASTA
        function stopGame() {
            if (gameEnded) return;
            if (confirm("¿Estás seguro de detener el juego?")) {
                endGame();
            }
        }

        // Polling para chequear si alguien más presionó BASTA
        const pollingInterval = setInterval(() => {
            if (gameEnded) return;
            fetch('check_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.estado === 'finalizada') {
                        endGame("¡Alguien presionó BASTA! Enviando respuestas...");
                    }
                })
                .catch(err => console.error("Error polling status:", err));
        }, 2000); // Chequear cada 2 segundos
    </script>

</body>

</html>