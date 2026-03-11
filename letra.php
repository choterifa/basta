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
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            background: white;
            padding: 20px;
            border-radius: 20px;
            box-shadow: 0 10px 0 #e5e5e5;
            width: 95%;
            max-width: 800px;
            max-height: 95vh;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }

        h1 {
            color: #3c3c3c;
            text-align: center;
            margin: 0 0 5px 0;
            font-weight: 900;
            font-size: 1.5rem;
        }

        h2 {
            text-align: center;
            color: #777;
            font-size: 1rem;
            margin: 0 0 10px 0;
        }

        #letra-display {
            display: inline-block;
            background: #1cb0f6;
            color: white;
            padding: 2px 15px;
            border-radius: 12px;
            font-size: 1.8rem;
            font-weight: 900;
            box-shadow: 0 4px 0 #1899d6;
            transform: rotate(-3deg) translateY(-3px);
            margin-left: 5px;
        }

        .timer {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ff4b4b;
            color: white;
            padding: 8px 15px;
            border-radius: 30px;
            font-weight: 900;
            font-size: 1.2rem;
            box-shadow: 0 3px 0 #d43b3b;
            z-index: 100;
            animation: pulse 1s infinite;
        }

        .round-status {
            display: none;
            margin: 0 auto 14px;
            padding: 10px 14px;
            max-width: 460px;
            border-radius: 14px;
            background: #fff7d6;
            color: #8a6a00;
            font-weight: 800;
            text-align: center;
        }

        .round-status.visible {
            display: block;
        }

        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            overflow-y: auto;
            padding: 5px;
            align-content: start;
        }

        /* Make button span full width */
        .btn-container {
            grid-column: 1 / -1;
            margin-top: 10px;
        }

        .field-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: 800;p
            color: #777;
            margin-bottom: 3px;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        input {
            padding: 10px;
            border: 2px solid #e5e5e5;
            border-radius: 12px;
            font-size: 1rem;
            font-family: 'Nunito', sans-serif;
            background: #f7f7f7;
            outline: none;
            transition: all 0.2s;
            width: 100%;
            box-sizing: border-box;
        }

        input.invalid {
            border-color: #ff4b4b;
            background: #fff0f0;
        }

        input.valid {
            border-color: #58cc02;
            background: #f0fff0;
        }

        .error-message {
            color: #ff4b4b;
            font-size: 0.75rem;
            margin-top: 3px;
            display: none;
        }

        .error-message.visible {
            display: block;
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
            padding: 15px;
            font-size: 1.5rem;
            font-weight: 900;
            border-radius: 16px;
            cursor: pointer;
            box-shadow: 0 5px 0 #d43b3b;
            text-transform: uppercase;
            width: 100%;
            transition: all 0.1s;
        }

        button.btn-basta:active {
            transform: translateY(5px);
            box-shadow: none;
        }

        /* Mobile adjustment */
        @media (max-width: 600px) {
            form {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            h1 {
                font-size: 1.2rem;
            }

            #letra-display {
                font-size: 1.5rem;
            }

            .timer {
                font-size: 1rem;
                padding: 5px 10px;
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- Relative container for absolute timer -->
        <div style="position: relative; width: 100%;">
            <h1>Juego en curso</h1>
            <h2>Letra: <span id="letra-display"><?php echo $letra; ?></span></h2>
            <div id="timer-display" class="timer"><?php echo $tiempo_restante; ?></div>
        </div>
        <div id="round-status" class="round-status"></div>

        <form id="game-form" action="enviar.php" method="post">

            <div class="field-group">
                <label>Nombre:</label>
                <input type="text" name="nombre" autocomplete="off" data-validate="true" data-name-only="true">
                <span class="error-message">Si no comienza con la letra <?php echo $letra; ?>, vale 0 puntos</span>
            </div>

            <div class="field-group">
                <label>Apellido:</label>
                <input type="text" name="apellido" autocomplete="off" data-validate="true" data-name-only="true">
                <span class="error-message">Si no comienza con la letra <?php echo $letra; ?>, vale 0 puntos</span>
            </div>

            <div class="field-group">
                <label>Flor o Fruto:</label>
                <input type="text" name="flor_fruto" autocomplete="off" data-validate="true">
                <span class="error-message">Si no comienza con la letra <?php echo $letra; ?>, vale 0 puntos</span>
            </div>

            <div class="field-group">
                <label>Animal:</label>
                <input type="text" name="animal" autocomplete="off" data-validate="true">
                <span class="error-message">Si no comienza con la letra <?php echo $letra; ?>, vale 0 puntos</span>
            </div>

            <div class="field-group">
                <label>Color:</label>
                <input type="text" name="color" autocomplete="off" data-validate="true">
                <span class="error-message">Si no comienza con la letra <?php echo $letra; ?>, vale 0 puntos</span>
            </div>

            <div class="field-group">
                <label>Cosa:</label>
                <input type="text" name="cosa" autocomplete="off" data-validate="true">
                <span class="error-message">Si no comienza con la letra <?php echo $letra; ?>, vale 0 puntos</span>
            </div>

            <div class="field-group">
                <label>País:</label>
                <input type="text" name="pais" autocomplete="off" data-validate="true">
                <span class="error-message">Si no comienza con la letra <?php echo $letra; ?>, vale 0 puntos</span>
            </div>

            <div class="field-group">
                <label>Verbo:</label>
                <input type="text" name="verbo" autocomplete="off" data-validate="true">
                <span class="error-message">Si no comienza con la letra <?php echo $letra; ?>, vale 0 puntos</span>
            </div>

            <div class="btn-container">
                <button type="button" class="btn-basta" onclick="stopGame()">
                    ¡BASTA!
                </button>
            </div>

        </form>
    </div>

    <script>
        const letraActual = '<?php echo $letra; ?>';
        let timeLeft = <?php echo $tiempo_restante; ?>;
        const timerDisplay = document.getElementById('timer-display');
        const form = document.getElementById('game-form');
        const roundStatus = document.getElementById('round-status');
        let gameEnded = false;
        let submitTimeout = null;

        function mostrarEstadoRonda(message) {
            if (!message) return;
            roundStatus.textContent = message;
            roundStatus.classList.add('visible');
        }

        function programarEnvio(deadlineMs, message) {
            if (gameEnded) return;
            gameEnded = true;
            clearInterval(timerInterval);
            clearInterval(pollingInterval);
            if (submitTimeout) clearTimeout(submitTimeout);
            mostrarEstadoRonda(message || 'La ronda termino. Enviando respuestas...');

            const delay = Math.max(0, deadlineMs - Date.now());
            submitTimeout = setTimeout(() => {
                form.submit();
            }, delay);
        }

        function finalizarRonda(message) {
            if (gameEnded) return;

            fetch('finalizar_ronda.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: 'finalizar=1'
            })
                .then(response => response.json())
                .then(data => {
                    if (data && data.deadline_ms) {
                        programarEnvio(data.deadline_ms, message);
                        return;
                    }
                    throw new Error('No deadline received');
                })
                .catch(() => {
                    gameEnded = true;
                    clearInterval(timerInterval);
                    clearInterval(pollingInterval);
                    mostrarEstadoRonda(message || 'La ronda termino. Enviando respuestas...');
                    form.submit();
                });
        }

        // Validación de campos
        function validarCampo(input) {
            const valor = input.value.trim();
            const errorSpan = input.parentElement.querySelector('.error-message');
            const soloLetras = input.dataset.nameOnly === 'true';
            
            if (valor === '') {
                input.classList.remove('valid', 'invalid');
                errorSpan.classList.remove('visible');
                return true; // Campo vacío es válido (no se responde)
            }

            if (soloLetras) {
                const limpio = valor.replace(/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]/g, '').replace(/\s{2,}/g, ' ');
                if (valor !== limpio) {
                    input.value = limpio;
                }
            }
            
            const valorActual = input.value.trim();
            const primeraLetra = valorActual.charAt(0).toUpperCase();
            const esValido = primeraLetra === letraActual;
            
            if (esValido) {
                input.classList.remove('invalid');
                input.classList.add('valid');
                errorSpan.classList.remove('visible');
            } else {
                input.classList.remove('valid');
                input.classList.add('invalid');
                errorSpan.classList.add('visible');
            }
            
            return esValido;
        }

        // Agregar listeners a todos los campos
        document.querySelectorAll('input[data-validate="true"]').forEach(input => {
            input.addEventListener('input', () => validarCampo(input));
            input.addEventListener('blur', () => validarCampo(input));
        });

        // Marcar visualmente campos que darán 0 puntos, pero sin bloquear el envío
        form.addEventListener('submit', function() {
            document.querySelectorAll('input[data-validate="true"]').forEach(input => {
                validarCampo(input);
            });
        });

        // Initial display
        timerDisplay.textContent = timeLeft;

        // Temporizador
        const timerInterval = setInterval(() => {
            if (gameEnded) return;
            timeLeft--;
            if (timeLeft >= 0) {
                timerDisplay.textContent = timeLeft;
            }
            if (timeLeft <= 0) {
                finalizarRonda("Tiempo fuera. Cerrando ronda...");
            }
        }, 1000);

        // Función para el botón BASTA
        function stopGame() {
            if (gameEnded) return;
            if (confirm("¿Estás seguro de detener el juego?")) {
                finalizarRonda("Ronda terminada. Enviando respuestas...");
            }
        }

        // Polling para chequear si alguien más presionó BASTA
        const pollingInterval = setInterval(() => {
            if (gameEnded) return;
            fetch('check_status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.estado === 'finalizada' && data.deadline_ms) {
                        programarEnvio(data.deadline_ms, "Alguien presiono BASTA. Enviando respuestas...");
                    }
                })
                .catch(err => console.error("Error polling status:", err));
        }, 250); // Chequear rapido para cerrar la ronda en todos casi al mismo tiempo
    </script>

</body>

</html>
