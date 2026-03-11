<!DOCTYPE html>
<html>

<head>
    <title>Juego Basta</title>
    <!-- Add Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        /* Global Styles */
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f7f7f7;
            /* Light gray background */
            color: #3c3c3c;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #58cc02;
            /* Duolingo Green */
            font-weight: 900;
            font-size: 3rem;
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 2px 2px 0px #46a302;
        }

        h3 {
            color: #afafaf;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            margin-top: 30px;
            border-bottom: 2px solid #e5e5e5;
            padding-bottom: 10px;
        }

        /* Container */
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 0 #e5e5e5;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        /* Forms */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 30px;
        }

        label {
            font-weight: 700;
            text-align: left;
            margin-bottom: -10px;
            font-size: 0.9rem;
            color: #777;
        }

        input {
            padding: 15px;
            border: 2px solid #e5e5e5;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.2s;
            outline: none;
            background: #f7f7f7;
        }

        input:focus {
            border-color: #58cc02;
            background: #fff;
        }

        .input-hint {
            margin-top: -8px;
            font-size: 0.8rem;
            color: #999;
            text-align: left;
        }

        /* Buttons */
        button {
            background-color: #58cc02;
            color: white;
            border: none;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 800;
            border-radius: 12px;
            cursor: pointer;
            box-shadow: 0 4px 0 #46a302;
            transition: transform 0.1s, box-shadow 0.1s;
            text-transform: uppercase;
            margin-top: 10px;
        }

        button:active {
            transform: translateY(4px);
            box-shadow: 0 0 0 #46a302;
        }

        button:hover {
            filter: brightness(1.1);
        }

        /* Secondary form separation */
        .divider {
            border-top: 2px dashed #e5e5e5;
            margin: 30px 0;
            position: relative;
        }

        .divider::after {
            content: "O";
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: #f7f7f7;
            padding: 0 10px;
            color: #cecece;
            font-weight: bold;
        }

        /* List */
        ul {
            list-style: none;
            padding: 0;
            text-align: left;
        }

        li {
            background: #fff;
            border: 2px solid #e5e5e5;
            margin-bottom: 10px;
            padding: 10px 15px;
            border-radius: 12px;
            color: #777;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        li span {
            color: #1cb0f6;
            /* Duo Blue */
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>BASTA</h1>

        <!-- Create Game -->
        <form action="crear_partida.php" method="post">
            <label>Tu Nombre:</label>
            <input type="text" name="nombre" placeholder="Ej. Juan Pérez" required data-name-only="true" autocomplete="off">
            <div class="input-hint">Solo letras, espacios, guion y apostrofe.</div>
            <button type="submit">Crear Partida</button>
        </form>

        <div class="divider"></div>

        <!-- Join Game -->
        <form action="unirse.php" method="post">
            <label>Tu Nombre:</label>
            <input type="text" name="nombre" placeholder="Ej. Ana García" required data-name-only="true" autocomplete="off">
            <div class="input-hint">Solo letras, espacios, guion y apostrofe.</div>
            <label>ID de Partida:</label>
            <input type="number" name="partida" placeholder="Ej. 12" required>
            <button type="submit" style="background-color: #1cb0f6; box-shadow: 0 4px 0 #1899d6;">Unirse</button>
        </form>

        <h3>Partidas disponibles:</h3>
        <ul id="lista-partidas">
            <?php
            include("conectar.php");
            $res = mysqli_query($conn, "SELECT * FROM partidas WHERE estado='en curso' ORDER BY id_partida DESC LIMIT 5");
            if (mysqli_num_rows($res) > 0) {
                while ($row = mysqli_fetch_assoc($res)) {
                    echo "<li><span>ID: " . $row['id_partida'] . "</span> Letra: " . ($row['letra_actual'] ? $row['letra_actual'] : '⏳') . "</li>";
                }
            } else {
                echo "<li style='text-align: center; color: #ccc; border:none;'>No hay partidas activas :(</li>";
            }
            ?>
        </ul>
    </div>

    <script>
        const caracteresNombrePermitidos = /[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]/g;
        const listaPartidas = document.getElementById('lista-partidas');

        function limpiarNombre(valor) {
            return valor.replace(caracteresNombrePermitidos, '').replace(/\s{2,}/g, ' ');
        }

        document.querySelectorAll('input[data-name-only="true"]').forEach((input) => {
            input.addEventListener('input', () => {
                const limpio = limpiarNombre(input.value);
                if (input.value !== limpio) {
                    input.value = limpio;
                }
            });
        });

        function renderizarPartidas(partidas) {
            if (!Array.isArray(partidas) || partidas.length === 0) {
                listaPartidas.innerHTML = "<li style='text-align: center; color: #ccc; border:none;'>No hay partidas activas :(</li>";
                return;
            }

            listaPartidas.innerHTML = partidas.map((partida) => {
                const letra = partida.letra_actual ? partida.letra_actual : '⏳';
                return `<li><span>ID: ${partida.id_partida}</span> Letra: ${letra}</li>`;
            }).join('');
        }

        function cargarPartidas() {
            fetch('partidas_activas.php', { cache: 'no-store' })
                .then((response) => response.json())
                .then((partidas) => renderizarPartidas(partidas))
                .catch((error) => console.error('Error cargando partidas:', error));
        }

        cargarPartidas();
        setInterval(cargarPartidas, 3000);
    </script>
</body>
</html>
