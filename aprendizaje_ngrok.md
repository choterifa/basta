# Aprendizajes del Proyecto "BASTA"

## Implementación de Juego Multijugador en PHP

- **Sincronización:** Se utilizó una base de datos MySQL centralizada para mantener el estado del juego.
- **Temporizador:** Para sincronizar el tiempo entre todos los jugadores, se guarda el `tiempo_inicio` (timestamp) en la base de datos y cada cliente calcula su tiempo restante restando el tiempo actual del servidor.
- **AJAX Polling:** Se implementó un script en JavaScript (`check_status.php`) que consulta cada 2 segundos si alguien presionó "BASTA", permitiendo bloquear las pantallas de todos casi en tiempo real.
- **Sesiones:** Uso de `$_SESSION` para identificar jugadores y partidas únicas.

## Ngrok (Herramienta Clave para Exposición Local)

**¿Qué es?**
Una herramienta que crea un túnel seguro desde tu localhost (puerto 80) hacia una URL pública en internet.

**Comandos Clave:**

1.  **Instalación:** Descargar y descomprimir.
2.  **Autenticación:** `./ngrok config add-authtoken TU_TOKEN`
3.  **Iniciar Túnel:** `./ngrok http 80` (Para exponer XAMPP/Apache).

**Pros:**

- No requiere abrir puertos en el router (Port Forwarding).
- Es seguro (conexión encriptada).
- Ideal para demos rápidas, hackathons o compartir tareas escolares.
- Permite que celulares y otras redes externas accedan a tu servidor local.

**Notas:**

- La URL gratuita cambia cada vez que reinicias ngrok.
- Muestra una pantalla de advertencia "Visit Site" en el plan gratuito.
- Si cierras la terminal, se cae el sitio.
