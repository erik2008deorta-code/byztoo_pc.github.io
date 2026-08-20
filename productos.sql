-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-07-2026 a las 14:29:41
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `byztoo_pc`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `oferta` decimal(10,2) DEFAULT NULL,
  `imagen` varchar(255) NOT NULL,
  `descripcion` text NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `agotado` tinyint(1) NOT NULL DEFAULT 0,
  `especificaciones` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`especificaciones`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `precio`, `oferta`, `imagen`, `descripcion`, `stock`, `agotado`, `especificaciones`) VALUES
(1, 'Memoria RAM 16GB DDR3', 2300.00, 1840.00, 'img/16deramddr3.png', 'Especificaciones:\r\nCapacidad:16 (Modulo único-Single Chanel)\r\nTecnología:  DDR3 SDRAM\r\n\r\nFormato: DIMM de 240 pines (Tamaño estándar para PC de escritorio)\r\n\r\nVelocidad/Frecuencia: 1600MHz (PC3-12800)\r\n\r\nVoltaje de operación: 1.5V (Voltaje estándar DDR3)\r\n\r\n', 0, 0, NULL),
(2, 'Memoria RAM 16GB DDR5', 3300.00, 2640.00, 'img/16deramddr5.png', 'Especificación: Capacidad: 16 GB (1x16GB)\r\n\r\nTipo de memoria: DDR5 SDRAM\r\n\r\nFrecuencia/Velocidad: 6000MHz (PC548000)\r\n\r\nVoltaje de funcionamiento:1.35V(avelocidad XMP/EXPO)\r\n\r\nDisipador de calor: Aluminio anodizado (con o sin RGB)', 0, 0, NULL),
(3, 'Auriculares Gamer Negros - Microfono No Retractil', 2700.00, NULL, 'img/Auriscolor1.png', 'Auriculares con sonido envolvente y micrófono con cancelación de ruido. Diseño ergonómico para largas sesiones de juego.', 0, 0, NULL),
(4, 'Auriculares Gamer Blancos - Microfono Retractil', 3700.00, NULL, 'img/Auriscolor2.png', 'Excelente calidad de audio y confort. Conectividad versátil y luces RGB ajustables a tu estilo de forma simple.', 0, 0, NULL),
(5, 'Monitor 24\" Full HD', 4150.00, NULL, 'img/MonitorComun.png', 'Monitor estándar con panel IPS y resolución Full HD. Perfecto para la oficina y uso diario multimedia.', 0, 0, NULL),
(6, 'Monitor Gamer 24\" 60Hz', 5077.00, NULL, 'img/MonitorGamer60hz.png', 'Monitor diseñado para gaming inicial. Tiempo de respuesta bajo y colores vibrantes para no perder ningún detalle.', 0, 0, NULL),
(7, 'Procesador Intel i7-12700', 4500.00, NULL, 'img/ProcesadorIntel16hiloscuartaageneracion.png', 'Especificaciones: Número de Núcleos Totales 12 núcleos\r\n\r\nNúmero de Hilos (Threads) 20 hilos\r\n\r\nFrecuencia Turbo Máxima	Hasta 4.90 GHz\r\n\r\nMemoria Caché 25 MB Intel®\r\n\r\nConsumo Base (TDP) 65 W\r\nConsumo Máximo (Turbo Power)180 W\r\n\r\nSoporte de Memoria RAM	Dual Channel: DDR4 hasta 3200 MHz / DDR5 hasta 4800 MHz (Capacidad máx: 128 GB)\r\n\r\nGráficos Integrados Intel® UHD Graphics 770\r\n\r\nDisipador incluido Sí (Intel Laminar RM1 en versión en la caja)', 0, 0, NULL),
(8, 'Procesador Intel Core i7-5960X', 6095.00, NULL, 'img/ProcesadorIntel16hilosultimageneracion.png', 'Potencia bruta con arquitectura de última generación. Máximo rendimiento en juegos AAA y renderizado.', 0, 0, NULL),
(9, 'Procesador Ryzen 9 5950X', 14000.00, NULL, 'img/ProcesadorRyzen16hilosultimageneracion.png', 'Eficiencia y rendimiento superior. 16 hilos para dominar el multitasking y las tareas más exigentes de manera rápida.', 0, 0, NULL),
(10, 'Teclado Mecánico 100% Violeta', 5951.00, NULL, 'img/teclado100.png', 'Teclado de formato completo con switches mecánicos táctiles, ideal para gaming y redacción.', 0, 0, NULL),
(11, 'Teclado Mecánico 100% Verde y Naranja', 2027.00, NULL, 'img/Teclado100color2.png', 'Diseño llamativo con iluminación RGB personalizable. Switches de rápida respuesta para juegos.', 0, 0, NULL),
(12, 'Teclado Mecánico 60% Violeta', 2629.00, NULL, 'img/Teclado100color3.png', 'Estética única con teclas de doble inyección. Durabilidad extrema y confort garantizado.', 0, 0, NULL),
(13, 'Mouse Gamer Negro', 1561.00, NULL, 'img/Mousecolor1.png', 'Sensor óptico de alta precisión con DPI ajustable. Diseño ergonómico para agarre de palma.', 0, 0, NULL),
(14, 'Mouse Gamer Dorado', 2504.00, NULL, 'img/mousecolor2.png', 'Ultraligero y con botones programables. Perfecto para juegos competitivos tipo shooter.', 0, 0, NULL),
(15, 'Mouse Gamer Violeta', 2854.00, NULL, 'img/mousecolor3.png', 'Estilo y rendimiento en un solo dispositivo. Iluminación RGB dinámica y cable trenzado reforzado.', 0, 0, NULL),
(16, 'Mouse Gamer Verde Dorado', 1540.00, NULL, 'img/mousecolor4.png', 'Comodidad superior para largas horas de uso. Incluye pesas ajustables para personalizar su centro de gravedad.', 0, 0, NULL),
(17, 'Gabinete PC Gamer', 65000.00, NULL, 'img/pc_tower.png', 'Torre ATX con panel de vidrio templado y ventiladores RGB preinstalados. Excelente flujo de aire.', 0, 0, '{\"Procesador\":\"Intel Core i7 12700F\",\"Memoria RAM\":\"32GB (2x16GB) DDR4 3200MHz\",\"Almacenamiento\":\"SSD 1TB NVMe M.2\",\"Placa de Video\":\"NVIDIA RTX 4060 8GB\",\"Fuente\":\"650W 80 Plus Bronze\"}'),
(18, 'Gabinete rgb', 2500.00, NULL, 'img/gabinetergb.png', 'Comodidad superior para largas horas de uso. Incluye pesas ajustables para personalizar su centro de gravedad.', 0, 0, NULL),
(19, 'Gabinete Blanco', 3000.00, NULL, 'img/gabineteBlanco.png', 'Un gabinete Gamer diseñados para quienes quieren llevar su experiencia al maximo (Gabinet color blanco con rgb especialmente pensado en gamers) .', 0, 0, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
