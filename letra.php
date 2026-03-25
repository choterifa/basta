<?php
session_start();
include("conectar.php");

if (!isset($_SESSION['id_partida'])) {
    header("Location: index.php");
    exit;
}

$id_partida = $_SESSION['id_partida'] ?? 0;
$id_jugador = $_SESSION['id_jugador'] ?? 0;


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
    <title>¡Basta! - Jugando</title>
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
            --error: #ff4b4b;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--bg);
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
            padding: 25px;
            border-radius: 28px;
            box-shadow: 0 12px 0 var(--border);
            width: 95%;
            max-width: 850px;
            max-height: 98vh;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            position: relative;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--bg);
        }

        h1 {
            color: var(--text);
            margin: 0;
            font-weight: 900;
            font-size: 1.8rem;
            letter-spacing: -1px;
        }

        .letra-capsule {
            background: var(--secondary);
            color: white;
            padding: 8px 25px;
            border-radius: 16px;
            font-size: 2.5rem;
            font-weight: 900;
            box-shadow: 0 6px 0 var(--secondary-dark);
            transform: rotate(-2deg);
        }

        .timer {
            background: var(--error);
            color: white;
            padding: 10px 20px;
            border-radius: 40px;
            font-weight: 900;
            font-size: 1.5rem;
            box-shadow: 0 4px 0 #d43b3b;
            animation: pulse-border 1.5s infinite;
        }

        @keyframes pulse-border {
            0% { box-shadow: 0 4px 0 #d43b3b, 0 0 0 0px rgba(255, 75, 75, 0.4); }
            70% { box-shadow: 0 4px 0 #d43b3b, 0 0 0 15px rgba(255, 75, 75, 0); }
            100% { box-shadow: 0 4px 0 #d43b3b, 0 0 0 0px rgba(255, 75, 75, 0); }
        }

        #game-form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            overflow-y: auto;
            padding: 10px 5px;
            margin-bottom: 10px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            position: relative;
        }

        label {
            font-weight: 800;
            color: var(--muted);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            margin-left: 5px;
        }

        input {
            padding: 15px;
            border: 2px solid var(--border);
            border-radius: 16px;
            font-size: 1.1rem;
            font-family: inherit;
            background: var(--bg);
            outline: none;
            transition: all 0.2s;
            font-weight: 700;
        }

        input:focus {
            border-color: var(--secondary);
            background: #fff;
            transform: scale(1.01);
        }

        input.valid {
            border-color: var(--primary);
            background: #f0fff0;
        }

        input.invalid {
            border-color: var(--error);
            background: #fff0f0;
        }

        .error-hint {
            color: var(--error);
            font-size: 0.75rem;
            font-weight: 800;
            margin-left: 5px;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .error-hint.visible { opacity: 1; }

        .btn-basta {
            grid-column: 1 / -1;
            background-color: var(--error);
            color: white;
            border: none;
            padding: 20px;
            font-size: 2rem;
            font-weight: 900;
            border-radius: 20px;
            cursor: pointer;
            box-shadow: 0 8px 0 #d43b3b;
            text-transform: uppercase;
            transition: all 0.1s;
            margin-top: 10px;
        }

        .btn-basta:active {
            transform: translateY(8px);
            box-shadow: none;
        }

        .round-status {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff7d6;
            color: #8a6a00;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 900;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            z-index: 1000;
            display: none;
        }

        .round-status.visible { display: block; }
        
        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            50% { transform: translateX(5px); }
            75% { transform: translateX(-5px); }
            100% { transform: translateX(0); }
        }
        
        .shake { animation: shake 0.3s ease-in-out; }
        
        .field-hint {
            font-size: 0.75rem;
            font-weight: 800;
            color: #ff4b4b;
            margin-top: 5px;
            height: 1.2em;
            display: none;
            text-align: left;
        }

        .input-group input.invalid + .field-hint {
            display: block;
        }

        .duplicate {
            border-color: #ff9d00 !important;
            box-shadow: 0 4px 0 #e68a00 !important;
            background-color: #fff9f0 !important;
        }
        
        .input-group input.duplicate + .field-hint {
            color: #e68a00;
            opacity: 1;
        }

        @media (max-width: 600px) {
            #game-form { grid-template-columns: 1fr; }
            h1 { font-size: 1.2rem; }
            .letra-capsule { font-size: 1.8rem; }
            .timer { font-size: 1.1rem; padding: 8px 15px; }
        }
    </style>
</head>

<body>
    <div id="round-status" class="round-status"></div>

    <div class="container">
        <div class="header">
            <div>
                <p style="margin:0; font-weight:800; color:var(--muted); font-size:0.8rem; text-transform:uppercase;">Letra actual:</p>
                <div class="letra-capsule"><?php echo $letra; ?></div>
            </div>
            <h1>¡CORRE!</h1>
            <div id="timer-display" class="timer"><?php echo $tiempo_restante; ?></div>
        </div>

        <form id="game-form" action="enviar.php" method="post">
            <?php
            $campos = [
                'nombre' => 'Nombre',
                'apellido' => 'Apellido',
                'flor_fruto' => 'Flor o Fruto',
                'animal' => 'Animal',
                'color' => 'Color',
                'cosa' => 'Cosa',
                'pais' => 'País',
                'verbo' => 'Verbo'
            ];

            foreach ($campos as $cat => $label) : ?>
                <div class="input-group">
                    <label for="<?php echo $cat; ?>">
                        <?php echo htmlspecialchars($label); ?>
                    </label>
                    <input type="text" id="<?php echo $cat; ?>" name="<?php echo $cat; ?>" 
                           placeholder="Escribe aquí..." 
                           data-validate="true"
                           autocomplete="off"
                           maxlength="30">
                    <div class="field-hint"></div>
                </div>
            <?php endforeach; ?>
            <button type="button" class="btn-basta" onclick="confirmStop()">¡BASTA!</button>
        </form>
    </div>

    <script>
        const letraActual = '<?php echo $letra; ?>';
        let timeLeft = <?php echo $tiempo_restante; ?>;
        const timerDisplay = document.getElementById('timer-display');
        const form = document.getElementById('game-form');
        const roundStatus = document.getElementById('round-status');
        let gameEnded = false;

        function mostrarStatus(msg) {
            roundStatus.textContent = msg;
            roundStatus.classList.add('visible');
        }

        function programarEnvio(deadlineMs, message) {
            if (gameEnded) return;
            gameEnded = true;
            clearInterval(timerInterval);
            clearInterval(pollingInterval);
            mostrarStatus(message || 'Ronda terminada. Enviando...');

            const delay = Math.max(0, deadlineMs - Date.now());
            setTimeout(() => form.submit(), delay);
        }

        function finalizarRonda(message) {
            if (gameEnded) return;
            fetch('finalizar_ronda.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'finalizar=1'
            })
            .then(r => r.json())
            .then(data => programarEnvio(data.deadline_ms, message))
            .catch(() => {
                gameEnded = true;
                mostrarStatus(message);
                form.submit();
            });
        }

        function confirmStop() {
            if (gameEnded) return;
            if (confirm("¿Seguro que quieres detener el juego para todos?")) {
                finalizarRonda("¡Presionaste BASTA! Enviando...");
            }
        }

        // Validación Avanzada
        const inputs = document.querySelectorAll('input[data-validate="true"]');
        
        inputs.forEach(input => {
            // Bloquear Pegado (Anti-Copy/Paste)
            input.addEventListener('paste', e => e.preventDefault());

            input.addEventListener('input', function() {
                // Limpiar símbolos y números (Solo letras y espacios)
                this.value = this.value.replace(/[^a-zA-Z\s\u00C0-\u017F]/g, '');
                
                let val = this.value.toUpperCase();
                
                // Si el primer carácter no es el correcto, lo bloqueamos
                if (val.length > 0 && val.charAt(0) !== letraActual) {
                    this.value = ''; // Clear the input if it starts with the wrong letter
                    this.classList.add('invalid', 'shake');
                    setTimeout(() => this.classList.remove('shake'), 400);
                    updateInputStatus(this);
                    return;
                }

                checkDuplicates();
                updateInputStatus(this);
            });
        });

        function checkDuplicates() {
            let usedWords = {};
            inputs.forEach(inp => {
                let word = inp.value.trim().toUpperCase();
                let hint = inp.parentElement.querySelector('.field-hint');
                if (word.length >= 2) {
                    if (!usedWords[word]) usedWords[word] = [];
                    usedWords[word].push(inp);
                }
            });

            // Limpiar estados de duplicado
            inputs.forEach(inp => {
                inp.classList.remove('duplicate');
            });

            // Marcar duplicados
            for (let word in usedWords) {
                if (usedWords[word].length > 1) {
                    usedWords[word].forEach(inp => {
                        inp.classList.add('duplicate', 'invalid');
                        inp.parentElement.querySelector('.field-hint').textContent = 'Palabra repetida';
                    });
                }
            }
        }

        function updateInputStatus(input) {
            let val = input.value.trim().toUpperCase();
            let hint = input.parentElement.querySelector('.field-hint');
            
            if (val === '') {
                input.classList.remove('valid', 'invalid', 'duplicate');
                hint.textContent = '';
                return;
            }

            if (input.classList.contains('duplicate')) {
                hint.textContent = 'Palabra repetida';
                return;
            }

            if (val.charAt(0) !== letraActual) {
                input.classList.add('invalid');
                input.classList.remove('valid');
                hint.textContent = `Debe empezar con ${letraActual}`;
            } else if (val.length < 3) {
                input.classList.add('invalid');
                input.classList.remove('valid');
                hint.textContent = 'Mínimo 3 letras';
            } else {
                input.classList.add('valid');
                input.classList.remove('invalid');
                hint.textContent = '';
            }
        }

        const timerInterval = setInterval(() => {
            if (gameEnded) return;
            timeLeft--;
            if (timeLeft >= 0) timerDisplay.textContent = timeLeft;
            if (timeLeft <= 0) finalizarRonda("¡Tiempo terminado!");
        }, 1000);

        const pollingInterval = setInterval(() => {
            if (gameEnded) return;
            fetch('check_status.php')
                .then(r => r.json())
                .then(data => {
                    if (data.estado === 'finalizada') {
                        programarEnvio(data.deadline_ms, "¡Alguien dijo BASTA!");
                    }
                }).catch(() => {});
        }, 400);
    </script>
</body>
</html>
