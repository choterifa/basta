CREATE DATABASE IF NOT EXISTS basta;
USE basta;

CREATE TABLE `partidas` (
  `id_partida` int(11) NOT NULL AUTO_INCREMENT,
  `letra_actual` char(1) DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'esperando',
  `tiempo_inicio` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_partida`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `jugadores` (
  `id_jugador` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) DEFAULT NULL,
  `partida_id` int(11) DEFAULT NULL,
  `es_host` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id_jugador`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `respuestas` (
  `id_respuesta` int(11) NOT NULL AUTO_INCREMENT,
  `jugador_id` int(11) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `palabra` varchar(100) DEFAULT NULL,
  `puntos` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_respuesta`)
) ENGINE=InnoDB AUTO_INCREMENT=893 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

