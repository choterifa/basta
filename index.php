<!DOCTYPE html>
<html>

<head>
    <title>Basta! - Lobby</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Add Google Font -->
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

        /* Global Styles */
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

        h1 {
            color: var(--primary);
            font-weight: 900;
            font-size: 3.5rem;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 3px 3px 0px var(--primary-dark);
            letter-spacing: -2px;
        }

        .card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 12px 0 var(--border);
            width: 100%;
            max-width: 420px;
            text-align: center;
            box-sizing: border-box;
        }

        h2 {
            font-size: 1.5rem;
            font-weight: 900;
            margin-bottom: 25px;
            color: var(--text);
        }

        /* Forms */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 25px;
            text-align: left;
        }

        label {
            font-weight: 800;
            font-size: 0.9rem;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input {
            padding: 18px;
            border: 2px solid var(--border);
            border-radius: 16px;
            font-size: 1.1rem;
            font-family: inherit;
            transition: all 0.2s;
            outline: none;
            background: #fdfdfd;
            font-weight: 700;
        }

        input:focus {
            border-color: var(--primary);
            background: #fff;
            transform: scale(1.02);
        }

        input.invalid {
            border-color: var(--error);
            background: #fff0f0;
        }

        .error-hint {
            color: var(--error);
            font-size: 0.85rem;
            font-weight: 700;
            margin-top: -8px;
            display: none;
        }

        .error-hint.visible {
            display: block;
        }

        /* Buttons */
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
            letter-spacing: 1px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background-color: var(--primary);
            box-shadow: 0 6px 0 var(--primary-dark);
        }

        .btn-secondary {
            background-color: var(--secondary);
            box-shadow: 0 6px 0 var(--secondary-dark);
        }

        .btn:active {
            transform: translateY(4px);
            box-shadow: 0 2px 0 rgba(0,0,0,0.1);
        }

        .btn:disabled {
            filter: grayscale(0.5);
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Divider */
        .divider {
            border-top: 2px solid var(--border);
            margin: 40px 0;
            position: relative;
        }

        .divider::after {
            content: "Ó";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 0 15px;
            color: var(--muted);
            font-weight: 900;
            font-size: 1rem;
        }

        /* List */
        .available-games {
            margin-top: 30px;
            text-align: left;
        }

        .available-games h3 {
            font-size: 0.9rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid var(--border);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        li {
            background: #fff;
            border: 2px solid var(--border);
            margin-bottom: 12px;
            padding: 12px 18px;
            border-radius: 14px;
            color: var(--text);
            font-weight: 800;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s;
            cursor: pointer;
        }

        li:hover {
            transform: translateX(5px);
            border-color: var(--secondary);
        }

        li span.id-badge {
            background: var(--secondary);
            color: white;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.8rem;
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
            vertical-align: middle;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>

<body>
    <div class="card">
        <h1>BASTA!</h1>

        <!-- Create Game -->
        <div class="form-section">
            <h2>Crear Partida</h2>
            <div class="form-group">
                <label>Tu Nombre</label>
                <input type="text" id="create-name" placeholder="Ej. Juan Pérez" maxlength="30" autocomplete="off">
                <div class="error-hint" id="create-error">Mínimo 3 letras, sin números.</div>
            </div>
            <button class="btn btn-primary" id="create-btn">Nueva Partida</button>
        </div>

        <div class="divider"></div>

        <!-- Join Game -->
        <div class="form-section">
            <h2>Unirse a Partida</h2>
            <div class="form-group">
                <label>Tu Nombre</label>
                <input type="text" id="join-name" placeholder="Ej. Ana García" maxlength="30" autocomplete="off">
                <div class="error-hint" id="join-name-error">Mínimo 3 letras, sin números.</div>
            </div>
            <div class="form-group">
                <label>ID de Partida</label>
                <input type="number" id="join-id" placeholder="Cód: 123" autocomplete="off">
                <div class="error-hint" id="join-id-error">Ingresa un ID válido.</div>
            </div>
            <button class="btn btn-secondary" id="join-btn">Unirme ahora</button>
        </div>

        <div class="available-games">
            <h3>Partidas en espera</h3>
            <ul id="lista-partidas">
                <!-- Se carga dinámicamente -->
            </ul>
        </div>
    </div>

    <script>
        const regNombre = /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]{3,30}$/;
        
        // Helpers
        function validateName(name) {
            return regNombre.test(name.trim());
        }

        function normalizeName(val) {
            return val.replace(/[^A-Za-zÁÉÍÓÚÜÑáéíóúüñ\s'-]/g, '').replace(/\s{2,}/g, ' ');
        }

        function toggleLoading(btn, isLoading, originalText) {
            btn.disabled = isLoading;
            btn.innerHTML = isLoading ? '<div class="loading-spinner"></div> Cargando...' : originalText;
        }

        // Input effects
        document.querySelectorAll('input[type="text"]').forEach(input => {
            input.addEventListener('input', function() {
                const normalized = normalizeName(this.value);
                if (this.value !== normalized) this.value = normalized;
                this.classList.remove('invalid');
                const errorId = this.id === 'create-name' ? 'create-error' : 'join-name-error';
                document.getElementById(errorId).classList.remove('visible');
            });
        });

        // Crear Partida
        const createBtn = document.getElementById('create-btn');
        createBtn.addEventListener('click', async () => {
            const nameInput = document.getElementById('create-name');
            const name = nameInput.value.trim();

            if (!validateName(name)) {
                nameInput.classList.add('invalid');
                document.getElementById('create-error').classList.add('visible');
                return;
            }

            toggleLoading(createBtn, true, 'Nueva Partida');

            try {
                const formData = new FormData();
                formData.append('nombre', name);

                const response = await fetch('crear_partida.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    window.location.href = 'lobby.php';
                } else {
                    alert('Error al crear la partida.');
                }
            } catch (err) {
                console.error(err);
            } finally {
                toggleLoading(createBtn, false, 'Nueva Partida');
            }
        });

        // Unirse a Partida
        const joinBtn = document.getElementById('join-btn');
        joinBtn.addEventListener('click', async () => {
            const nameInput = document.getElementById('join-name');
            const idInput = document.getElementById('join-id');
            const name = nameInput.value.trim();
            const partidaId = idInput.value;

            let valid = true;
            if (!validateName(name)) {
                nameInput.classList.add('invalid');
                document.getElementById('join-name-error').classList.add('visible');
                valid = false;
            }
            if (!partidaId || partidaId <= 0) {
                idInput.classList.add('invalid');
                document.getElementById('join-id-error').classList.add('visible');
                valid = false;
            }

            if (!valid) return;

            toggleLoading(joinBtn, true, 'Unirme ahora');

            try {
                const formData = new FormData();
                formData.append('nombre', name);
                formData.append('partida', partidaId);

                const response = await fetch('unirse.php', {
                    method: 'POST',
                    body: formData
                });
                
                const text = await response.text();
                if (text.includes('Location: lobby.php') || response.redirected) {
                    window.location.href = 'lobby.php';
                } else if (text.includes('no existe')) {
                    alert('La partida no existe o ya comenzó.');
                } else {
                    // Fallback parse if it didn't redirect automatically
                    window.location.href = 'lobby.php';
                }
            } catch (err) {
                console.error(err);
            } finally {
                toggleLoading(joinBtn, false, 'Unirme ahora');
            }
        });

        // Poll partidas
        async function cargarPartidas() {
            try {
                const res = await fetch('partidas_activas.php');
                const partidas = await res.json();
                const lista = document.getElementById('lista-partidas');
                
                if (partidas.length === 0) {
                    lista.innerHTML = '<li style="border:none; color:#ccc; justify-content:center;">No hay salas abiertas :(</li>';
                    return;
                }

                lista.innerHTML = partidas.map(p => `
                    <li onclick="document.getElementById('join-id').value = ${p.id_partida}; document.getElementById('join-name').focus();">
                        <span class="id-badge">ID: ${p.id_partida}</span>
                        <span>Esperando...</span>
                    </li>
                `).join('');
            } catch (e) {
                console.error(e);
            }
        }

        cargarPartidas();
        setInterval(cargarPartidas, 4000);
    </script>
</body>

</html>
