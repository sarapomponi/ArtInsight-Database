-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Creato il: Mar 10, 2026 alle 20:06
-- Versione del server: 9.1.0
-- Versione PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mostre`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `autore`
--

DROP TABLE IF EXISTS `autore`;
CREATE TABLE IF NOT EXISTS `autore` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `cognome` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `movimento` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nazionalita` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `autore`
--

INSERT INTO `autore` (`id`, `nome`, `cognome`, `movimento`, `nazionalita`) VALUES
(1, 'Giacomo', 'Balla', 'Futurismo', 'Italia'),
(2, 'Paul', 'Cezanne', 'Post-impressionista', 'Francia'),
(3, 'Beato', 'Angelico', 'Primo Rinascimento', 'Italia'),
(4, 'Michelangelo', 'Merisi (Caravaggio)', 'Barocco', 'Italia'),
(5, 'Gerardo', 'Dottori', 'Futurismo', 'Italia'),
(11, 'Pietro', 'Vannucci (Perugino)', 'Rinascimento ', 'Italia'),
(8, 'Gentile', 'Da Fabriano', 'Gotico Internazionale', 'Italia'),
(12, 'Bernardino', 'Di Betto (Pinturicchio)', 'Rinascimento ', 'Italia'),
(13, 'Andrea', 'Della Robbia', 'Rinascimento ', 'Italia'),
(14, 'Andrea', 'Mantegna', 'Rinascimento ', 'Italia'),
(15, 'Giovanni', 'Bellini', 'Rinascimento Veneziano', 'Italia'),
(16, 'Gian Lorenzo', 'Bernini', 'Barocco', 'Italia'),
(17, 'Diego ', 'Velázquez', 'Barocco', 'Spagna'),
(18, 'Giacinto', 'Brandi', 'Barocco', 'Italia'),
(19, 'Lorenzo', 'Lotto', 'Alto Rinascimento', 'Italia'),
(20, 'Tiziano', 'Vecellio', 'Rinascimento ', 'Italia'),
(21, 'Dosso', 'Dossi', 'Rinascimento ', 'Italia'),
(22, 'Guido', 'Reni', 'Barocco', 'Italia'),
(23, 'Pierre-Auguste ', 'Renoir', 'Impressionismo', 'Francia'),
(24, 'Wassily ', 'Kandinsky', 'Astrattismo', 'Russia'),
(25, 'Andrea', 'Modigliani', 'Simbolismo', 'Italia');

-- --------------------------------------------------------

--
-- Struttura della tabella `collocazione`
--

DROP TABLE IF EXISTS `collocazione`;
CREATE TABLE IF NOT EXISTS `collocazione` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `collocazione`
--

INSERT INTO `collocazione` (`id`, `nome`) VALUES
(1, 'Galleria Nazionale d\'Arte Moderna (RM)'),
(2, 'Galleria degli Uffizi (FI)'),
(3, 'Musee d\'Orsay (PARIGI)'),
(4, 'Museo di San Marco (FI)'),
(8, 'Galleria Borghese (RM)'),
(9, 'Kimbell Art Museum (USA)'),
(10, 'Palazzo Barberini (RM)'),
(11, 'Museo Civico di Palazzo della Penna (PG)'),
(12, 'The Museum of Modern Art (NY)'),
(13, 'Peggy Guggenheim (VE)'),
(14, 'Musei Capitolini (RM)'),
(16, 'Galleria Nazionale dell\'Umbria (PG)'),
(17, 'Museo Nazionale di San Matteo (PI)'),
(18, 'Museo della Città di Bettona (PG)'),
(19, 'Complesso Museale di San Francesco (PG)'),
(20, 'Palazzo dei Priori (PG)'),
(21, 'Museo del duomo di Città di Castello (PG)'),
(22, 'Pinacoteca di Brera (MI)'),
(23, 'Gallerie dell\'Accademia (VE)'),
(24, 'Accademia Carrara (BG)'),
(25, 'Museo Correr (VE)'),
(26, 'Musei Vaticani (CDV)'),
(27, 'Palazzo Ducale (VE)'),
(28, 'Musée du Louvre'),
(29, 'British Museum'),
(30, 'Nobile Collegio del Cambio (PG)'),
(31, 'Collezione Koelliker (MI)'),
(32, 'Museo Nacional del Prado'),
(33, 'Musée Fabre'),
(34, 'Fondazione Longhi (FI)'),
(35, 'National Gallery of Scotland'),
(36, 'Louvre Abu Dhabi'),
(38, 'National Gallery of Art of Washington'),
(39, 'Scuderie del Quirinale (RM)'),
(40, 'Palazzo Reale di Madrid'),
(41, 'Monasterio de San Lorenzo de El Escorial'),
(42, 'Basilica di Santa Cecilia in Trastevere (RM)'),
(43, 'Museo dell\'Ara Pacis (RM)'),
(44, 'Detroit Institute of Arts');

-- --------------------------------------------------------

--
-- Struttura della tabella `enti`
--

DROP TABLE IF EXISTS `enti`;
CREATE TABLE IF NOT EXISTS `enti` (
  `id_ente` int NOT NULL AUTO_INCREMENT,
  `nome_ente` varchar(100) NOT NULL,
  PRIMARY KEY (`id_ente`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `enti`
--

INSERT INTO `enti` (`id_ente`, `nome_ente`) VALUES
(1, 'Galleria Borghese (RM)'),
(2, 'Galleria Nazionale d\'Arte Moderna (RM)'),
(3, 'Musee d\'Orsay (PARIGI)'),
(4, 'Museo di San Marco (FI)'),
(5, 'Kimbell Art Museum (USA)'),
(6, 'Palazzo Barberini (RM)'),
(7, 'Museo Civico di Palazzo della Penna (PG)'),
(8, 'Hillman Periodicals Fund'),
(9, 'Collezione Peggy Guggenheim (VE)'),
(10, 'Musei Capitolini (RM)'),
(12, 'Museo Louvre'),
(13, 'Museo del Louvre'),
(14, 'Museo Nazionale di San Matteo (PI)'),
(15, 'Museo della Città (PG)'),
(16, 'Museo della Città di Bettona (PG)'),
(17, 'Complesso Museale di San Francesco (PG)'),
(18, 'Comune di Perugia'),
(19, 'Museo del Duomo (PG)'),
(20, 'Museo del Duomo (PG) di Città di Castello'),
(21, 'Pinacoteca di Brera (MI)'),
(22, 'Venezia, Gallerie dell\'Accademia (VE)'),
(23, 'Accademia Carrara (BG)'),
(24, 'Museo Correr (VE)'),
(25, 'Gallerie dell\'Accademia (VE)'),
(26, 'Musei Vaticani (CDV)'),
(27, 'Palazzo Ducale (VE)'),
(28, 'Musée du Louvre'),
(29, 'British Museum'),
(30, 'Nobile Collegio del Cambio (PG)'),
(31, 'Galleria degli Uffizi'),
(32, 'Collezione Koelliker (MI)'),
(33, 'Museo del Prado'),
(34, 'Musée Fabre'),
(35, 'Fondazione Longhi (FI)'),
(36, 'National Gallery of Scotland'),
(37, 'Louvre Abu Dhabi'),
(38, 'Museo Nacional del Prado'),
(39, 'National Gallery of Art'),
(40, 'Palazzo Reale di Madrid'),
(41, 'Monastero dell\'Escorial'),
(42, 'Patrimonio Nacional'),
(43, 'Basilica di Santa Cecilia in Trastevere'),
(44, 'Detroit Institute of Arts');

-- --------------------------------------------------------

--
-- Struttura della tabella `esposizione`
--

DROP TABLE IF EXISTS `esposizione`;
CREATE TABLE IF NOT EXISTS `esposizione` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_opera` int NOT NULL,
  `id_mostra` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_opera` (`id_opera`),
  KEY `id_mostra` (`id_mostra`)
) ENGINE=MyISAM AUTO_INCREMENT=146 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `esposizione`
--

INSERT INTO `esposizione` (`id`, `id_opera`, `id_mostra`) VALUES
(3, 4, 1),
(91, 62, 9),
(90, 61, 9),
(6, 5, 1),
(7, 6, 1),
(8, 9, 2),
(9, 1, 3),
(10, 2, 3),
(11, 3, 3),
(83, 56, 7),
(81, 54, 7),
(106, 55, 7),
(15, 15, 3),
(16, 16, 3),
(17, 17, 1),
(18, 18, 1),
(84, 57, 7),
(105, 58, 7),
(21, 21, 3),
(33, 32, 1),
(23, 23, 2),
(24, 24, 2),
(94, 65, 9),
(107, 59, 9),
(27, 26, 1),
(79, 52, 7),
(78, 51, 7),
(30, 29, 3),
(31, 30, 3),
(32, 31, 3),
(34, 33, 1),
(98, 64, 9),
(104, 63, 9),
(80, 53, 7),
(102, 66, 9),
(101, 67, 9),
(103, 68, 9),
(108, 69, 10),
(112, 70, 10),
(110, 71, 10),
(111, 72, 10),
(113, 73, 10),
(114, 74, 10),
(117, 75, 11),
(116, 76, 11),
(118, 77, 11),
(119, 78, 11),
(120, 79, 11),
(121, 80, 11),
(123, 81, 12),
(124, 82, 12),
(125, 83, 12),
(126, 84, 13),
(127, 85, 13),
(128, 86, 13),
(129, 87, 13),
(130, 88, 13),
(134, 89, 14),
(133, 90, 14),
(137, 91, 14),
(139, 92, 14),
(140, 93, 15),
(141, 94, 15),
(142, 95, 16),
(143, 96, 16),
(144, 97, 16);

-- --------------------------------------------------------

--
-- Struttura della tabella `mostra`
--

DROP TABLE IF EXISTS `mostra`;
CREATE TABLE IF NOT EXISTS `mostra` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titolo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `data_inizio` date NOT NULL,
  `data_fine` date NOT NULL,
  `curatore` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `sede` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `mostra`
--

INSERT INTO `mostra` (`id`, `titolo`, `data_inizio`, `data_fine`, `curatore`, `sede`) VALUES
(9, 'Giovanni Bellini La nascita della pittura devozionale umanistica ', '2014-04-09', '2014-07-13', 'Sandrina Bandera, Matteo Ceriana, Keith Christiansen, Emanuela Daffra, Andrea De Marchi, Mariolina Olivari', '22'),
(2, 'Inferno', '2021-10-15', '2022-01-23', 'Jean Clair, Laura Bossi', '8'),
(3, 'Il Tempo del Futurismo', '2024-10-03', '2025-04-27', 'Gabriele Simongini', '1'),
(7, 'TUTTA L’UMBRIA IN UNA MOSTRA', '2018-03-10', '2018-06-10', 'Pierini Marco, Galassi Cristina', '16'),
(10, 'Velázquez e Bernini: autoritratti in mostra al Nobile Collegio del cambio', '2017-06-22', '2017-10-22', 'Francesco Federico Mancini', '30'),
(11, 'Il tempo di Caravaggio. Capolavori della collezione di Roberto Longhi', '2020-06-16', '2021-05-02', 'Maria Cristina Bandera', '14'),
(12, 'Tiziano. Dialoghi di Natura e di Amore', '2022-06-14', '2022-09-18', 'Maria Giovanna Sarti', '8'),
(13, 'Dosso Dossi. Il Fregio di Enea', '2023-04-04', '2023-06-11', 'Marina Minozzi', '8'),
(14, ' Da Caravaggio a Bernini: capolavori del Seicento italiano nelle collezioni reali di Spagna', '2017-04-14', '2017-07-30', 'Gonzalo Redín Michaus', '39'),
(15, 'Guido Reni a Roma. Il sacro e la natura', '2022-03-01', '2022-05-22', 'Francesca Cappelletti', '8'),
(16, 'IMPRESSIONISMO e oltre. Capolavori dal Detroit Institute of Arts', '2025-12-04', '2026-05-03', 'Ilaria Miarelli Mariani, Claudio Zambianchi', '43');

-- --------------------------------------------------------

--
-- Struttura della tabella `opera`
--

DROP TABLE IF EXISTS `opera`;
CREATE TABLE IF NOT EXISTS `opera` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titolo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `id_collocazione` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `datazione` varchar(20) NOT NULL,
  `id_autore` int NOT NULL,
  `id_tipologia` int NOT NULL,
  `id_tecnica` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_autore` (`id_autore`),
  KEY `id_tipologia` (`id_tipologia`),
  KEY `id_tecnica` (`id_tecnica`),
  KEY `collocazione` (`id_collocazione`)
) ENGINE=MyISAM AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `opera`
--

INSERT INTO `opera` (`id`, `titolo`, `id_collocazione`, `datazione`, `id_autore`, `id_tipologia`, `id_tecnica`) VALUES
(59, 'Cristo in pietà tra la Madonna e san Giovanni (cimasa del Polittico di Pisa)', '22', '1455', 14, 1, 11),
(24, 'Giudizio Finale', '4', '1425', 3, 1, 2),
(56, 'Sant\'Antonio da Padova', '18', '1505', 13, 2, 9),
(23, 'La tentazione di Sant\'Antonio', '3', '1887', 2, 1, 1),
(55, 'Madonna col Bambino benedicente', '19', '1490', 12, 1, 2),
(29, 'Incendio', '11', '1926', 5, 1, 1),
(53, 'Sant\'Antonio da Padova', '18', '1513', 11, 1, 8),
(52, 'San Girolamo', '16', '1480', 11, 1, 7),
(30, 'Lampada ad arco', '12', '1910', 1, 1, 1),
(31, 'Velocità astratta+rumore', '13', '1914', 1, 1, 3),
(54, 'Annunciazione', '16', '1500', 11, 1, 2),
(51, 'Madonna dell\'Umiltà', '17', '1425', 8, 1, 2),
(57, 'Madonna col bambino e angeli', '20', '1486', 12, 1, 10),
(58, 'Madonna col Bambino e san Giovannino', '21', '1490', 12, 1, 2),
(61, 'Pietà', '24', '1460', 15, 1, 2),
(62, 'Cristo in pietà sorretto da due angeli', '25', '1460', 15, 1, 12),
(63, 'Cristo in pietà sorretto dalla Madonna, san Giovanni evangelista e una pia donna', '23', '1457', 14, 6, 13),
(64, 'Madonna col Bambino in trono', '23', '1475', 15, 1, 14),
(65, 'Imbalsamazione di Cristo', '26', '1473', 15, 1, 12),
(66, 'La Pietà con i santi Marco e Nicolò', '27', '1472', 15, 1, 8),
(67, 'Cristo crocifisso tra la Vergine e san Giovanni evangelista', '28', '1465', 15, 1, 2),
(68, 'Cristo in pietà sorretto dalla Madonna', '29', '1480', 15, 6, 15),
(69, 'Autoritratto', '2', '1635', 16, 1, 1),
(70, 'Ritratto d\'uomo', '14', '1630', 17, 1, 1),
(71, 'Autoritratto mentre disegna', '31', '1625', 16, 1, 1),
(72, 'Autoritratto', '32', '1635', 16, 1, 1),
(73, 'Autoritratto', '2', '1643', 17, 1, 1),
(74, 'Autoritratto', '33', '1635', 16, 1, 1),
(75, 'Ragazzo morso da un ramarro', '34', '1597 circa', 4, 1, 1),
(76, 'San Sebastiano curato dagli angeli', '34', '1660-1670 circa', 18, 1, 1),
(77, 'Madonna addolorata', '34', '1545 circa', 19, 1, 12),
(78, 'San Giovanni Evangelista', '34', '1545 circa', 19, 1, 12),
(79, 'San Pietro martire', '34', '1540 circa', 19, 1, 12),
(80, 'Santo domenicano in preghiera', '34', '1540 circa', 19, 1, 12),
(81, 'Amor sacro e Amor profano', '8', '1514-1515', 20, 1, 1),
(82, 'Venere che benda amore  ', '8', '1565 circa', 20, 1, 1),
(83, 'Le tre età dell\'uomo', '35', '1512 circa', 20, 1, 1),
(84, 'La peste cretese', '36', '1520-1521 circa', 21, 1, 1),
(85, 'Arrivo dei Troiani alle isole Strofadi e attacco delle Arpie', '32', '1518-1519 circa', 21, 1, 1),
(86, 'Giochi siciliani in memoria di Anchise e fondazione di una città in Sicilia', '36', '1518-1519 circa', 21, 1, 1),
(87, 'La riparazione delle navi troiane', '38', '1518-1519 circa', 21, 1, 1),
(88, 'La costruzione del tempio di Venere a Erice e le offerte alla tomba di Anchise', '38', '1518-1519 circa', 21, 1, 1),
(89, 'Salomè con la testa del Battista', '40', '1607', 4, 1, 1),
(90, 'Santa Caterina', '41', '1606', 22, 1, 1),
(91, 'Conversione di Saulo', '40', '1621', 22, 1, 1),
(92, 'Cristo crocifisso', '41', '1654-1656', 16, 2, 16),
(93, 'Sant\'Apollonia in preghiera', '32', '1600-1603', 22, 1, 17),
(94, 'Il martirio di Sant\'Apollonia', '32', '1600-1603', 22, 1, 17),
(95, 'Donna in poltrona ', '19', '1874', 23, 1, 1),
(96, 'Studio per dipinto con forma bianca', '44', '1913', 24, 1, 1),
(97, 'Giovane uomo con berretto', '44', '1909', 25, 1, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `opera_tema`
--

DROP TABLE IF EXISTS `opera_tema`;
CREATE TABLE IF NOT EXISTS `opera_tema` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_opera` int NOT NULL,
  `id_tema` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_opera` (`id_opera`),
  KEY `id_tema` (`id_tema`)
) ENGINE=MyISAM AUTO_INCREMENT=187 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `opera_tema`
--

INSERT INTO `opera_tema` (`id`, `id_opera`, `id_tema`) VALUES
(48, 51, 9),
(49, 51, 10),
(51, 52, 11),
(53, 53, 11),
(52, 52, 12),
(47, 51, 11),
(8, 24, 2),
(9, 29, 3),
(50, 51, 8),
(11, 23, 2),
(12, 30, 3),
(13, 31, 3),
(54, 53, 13),
(55, 54, 14),
(56, 54, 15),
(111, 55, 17),
(110, 55, 16),
(59, 56, 18),
(60, 56, 15),
(61, 56, 13),
(62, 57, 11),
(63, 57, 17),
(109, 58, 19),
(108, 58, 17),
(107, 58, 11),
(113, 59, 20),
(112, 59, 21),
(74, 62, 22),
(73, 61, 20),
(72, 61, 23),
(75, 62, 23),
(76, 62, 20),
(106, 63, 20),
(105, 63, 23),
(104, 63, 11),
(103, 63, 14),
(92, 64, 20),
(91, 64, 11),
(83, 65, 21),
(84, 65, 24),
(100, 66, 20),
(99, 66, 23),
(98, 67, 23),
(97, 67, 25),
(101, 68, 23),
(102, 68, 20),
(114, 69, 26),
(118, 70, 27),
(116, 71, 26),
(117, 72, 26),
(119, 73, 26),
(120, 74, 26),
(126, 75, 31),
(125, 75, 29),
(123, 76, 32),
(124, 76, 23),
(127, 77, 21),
(128, 77, 23),
(129, 78, 32),
(130, 78, 33),
(131, 79, 32),
(132, 79, 34),
(133, 80, 32),
(134, 80, 11),
(138, 81, 36),
(137, 81, 30),
(139, 82, 30),
(140, 82, 36),
(141, 83, 30),
(142, 83, 37),
(143, 84, 38),
(144, 84, 39),
(145, 85, 38),
(146, 85, 40),
(147, 85, 39),
(148, 86, 38),
(149, 86, 39),
(150, 87, 38),
(151, 87, 39),
(152, 87, 42),
(153, 88, 38),
(154, 88, 39),
(164, 89, 43),
(163, 89, 45),
(162, 90, 34),
(161, 90, 46),
(160, 90, 32),
(167, 91, 47),
(168, 91, 43),
(174, 92, 48),
(173, 92, 23),
(172, 92, 25),
(175, 93, 32),
(176, 93, 46),
(177, 93, 34),
(178, 94, 32),
(179, 94, 34),
(180, 94, 49),
(181, 95, 27),
(182, 96, 50),
(183, 97, 51),
(184, 97, 27);

-- --------------------------------------------------------

--
-- Struttura della tabella `partecipazioni`
--

DROP TABLE IF EXISTS `partecipazioni`;
CREATE TABLE IF NOT EXISTS `partecipazioni` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_autore` int NOT NULL,
  `id_mostra` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_autore` (`id_autore`),
  KEY `id_mostra` (`id_mostra`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `partecipazioni`
--

INSERT INTO `partecipazioni` (`id`, `id_autore`, `id_mostra`) VALUES
(1, 1, 3),
(2, 2, 2),
(3, 3, 2),
(4, 4, 1),
(5, 5, 3),
(6, 9, 8);

-- --------------------------------------------------------

--
-- Struttura della tabella `prestatore`
--

DROP TABLE IF EXISTS `prestatore`;
CREATE TABLE IF NOT EXISTS `prestatore` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_ente` int NOT NULL,
  `paese` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_ente` (`id_ente`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `prestatore`
--

INSERT INTO `prestatore` (`id`, `id_ente`, `paese`) VALUES
(1, 1, 'Italia'),
(3, 2, 'Italia'),
(4, 3, 'Francia'),
(5, 4, 'Italia'),
(6, 5, 'Stati Uniti'),
(7, 6, 'Italia'),
(8, 7, 'Italia'),
(9, 8, 'Stati Uniti'),
(10, 9, 'Italia'),
(11, 10, 'Italia'),
(19, 16, 'Italia'),
(18, 14, 'Italia'),
(20, 17, 'Italia'),
(21, 18, 'Italia'),
(22, 20, 'Italia'),
(23, 21, 'Italia'),
(24, 25, 'Italia'),
(25, 23, 'Italia'),
(26, 24, 'Italia'),
(27, 26, 'Città del Vaticano'),
(28, 27, 'Italia'),
(29, 28, 'Francia'),
(30, 29, 'Regno Unito'),
(32, 31, 'Italia'),
(33, 32, 'Italia'),
(34, 38, 'Spagna'),
(35, 34, 'Francia'),
(36, 35, 'Italia'),
(37, 36, 'Regno Unito'),
(38, 37, 'Emirati Arabi Uniti'),
(39, 39, 'Stati Uniti'),
(40, 40, 'Spagna'),
(41, 41, 'Spagna'),
(42, 42, 'Spagna'),
(43, 43, 'Italia'),
(44, 44, 'Stati Uniti');

-- --------------------------------------------------------

--
-- Struttura della tabella `prestito`
--

DROP TABLE IF EXISTS `prestito`;
CREATE TABLE IF NOT EXISTS `prestito` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_opera` int NOT NULL,
  `organizzatore` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `inizio` date NOT NULL,
  `fine` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idopera` (`id_opera`),
  KEY `organizzatore` (`organizzatore`)
) ENGINE=MyISAM AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `prestito`
--

INSERT INTO `prestito` (`id`, `id_opera`, `organizzatore`, `inizio`, `fine`) VALUES
(1, 12, '1', '2025-07-01', '2025-07-31'),
(2, 13, '1', '2025-07-18', '2025-07-31'),
(3, 14, '1', '2025-07-24', '2025-07-25'),
(4, 15, '1', '2025-07-24', '2025-07-25'),
(5, 16, '1', '2025-07-24', '2025-07-25'),
(6, 17, '1', '2025-07-17', '2025-08-07'),
(7, 18, '1', '2025-08-01', '2025-07-25'),
(8, 20, '1', '2025-07-25', '2025-07-31'),
(9, 21, '1', '2025-07-31', '2025-07-31'),
(73, 55, '20', '2018-03-10', '2018-06-10'),
(11, 23, '5', '2021-10-15', '2022-01-23'),
(12, 24, '5', '2021-10-15', '2022-01-23'),
(14, 26, '5', '2025-03-07', '2025-07-20'),
(33, 42, '9', '2026-01-08', '2026-01-24'),
(17, 29, '7', '2024-12-03', '2025-02-28'),
(18, 30, '8', '2024-12-03', '2025-02-28'),
(19, 31, '10', '2024-12-03', '2025-02-28'),
(20, 32, '11', '2025-07-17', '2025-07-31'),
(21, 33, '9', '2025-07-10', '2025-07-31'),
(51, 53, '19', '2018-03-10', '2018-06-10'),
(50, 51, '18', '2018-03-10', '2018-06-10'),
(53, 56, '19', '2018-03-10', '2018-06-10'),
(54, 57, '21', '2018-03-10', '2018-06-10'),
(72, 58, '22', '2018-03-10', '2018-06-10'),
(57, 61, '25', '2014-04-09', '2014-07-13'),
(58, 62, '26', '2014-04-09', '2014-07-13'),
(71, 63, '24', '2014-04-09', '2014-07-13'),
(65, 64, '24', '2014-04-09', '2014-07-13'),
(61, 65, '27', '2014-04-09', '2014-07-13'),
(69, 66, '28', '2014-04-09', '2014-07-13'),
(68, 67, '29', '2014-04-09', '2014-07-13'),
(70, 68, '30', '2014-04-09', '2014-07-13'),
(74, 69, '32', '2017-06-22', '2017-10-22'),
(78, 70, '11', '2017-06-22', '2017-10-22'),
(76, 71, '33', '2017-06-22', '2017-10-22'),
(77, 72, '34', '2017-06-22', '2017-10-22'),
(79, 73, '32', '2017-06-22', '2017-10-22'),
(80, 74, '35', '2017-06-22', '2017-10-22'),
(83, 75, '36', '2020-06-16', '2021-05-02'),
(82, 76, '36', '2020-06-16', '2021-05-02'),
(84, 77, '36', '2020-06-16', '2021-05-02'),
(85, 78, '36', '2020-06-16', '2021-05-02'),
(86, 79, '36', '2020-06-16', '2021-05-02'),
(87, 80, '36', '2020-06-16', '2021-05-02'),
(88, 83, '37', '2022-06-14', '2022-09-18'),
(89, 84, '38', '2023-04-04', '2023-06-11'),
(90, 85, '34', '2023-04-04', '2023-06-11'),
(91, 86, '38', '2023-04-04', '2023-06-11'),
(92, 87, '39', '2023-04-04', '2023-06-11'),
(93, 88, '39', '2023-04-04', '2023-06-11'),
(97, 89, '42', '2017-04-14', '2017-07-30'),
(96, 90, '42', '2017-04-14', '2017-07-30'),
(100, 91, '42', '2017-04-14', '2017-07-30'),
(102, 92, '42', '2017-04-14', '2017-07-30'),
(103, 93, '34', '2022-03-01', '2022-05-22'),
(104, 94, '34', '2022-03-01', '2022-05-22'),
(105, 95, '44', '2025-12-04', '2026-05-03'),
(106, 96, '44', '2025-12-04', '2026-05-03'),
(107, 97, '44', '2025-12-04', '2026-05-03');

-- --------------------------------------------------------

--
-- Struttura della tabella `tecnica`
--

DROP TABLE IF EXISTS `tecnica`;
CREATE TABLE IF NOT EXISTS `tecnica` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `tecnica`
--

INSERT INTO `tecnica` (`id`, `nome`) VALUES
(1, 'Olio su tela'),
(2, 'Tempera su tavola'),
(3, 'Olio su tavola non verniciata'),
(7, 'Affresco staccato e riportato su tela'),
(8, 'Tempera su tela'),
(9, 'Terracotta invetriata'),
(10, 'Affresco staccato'),
(11, 'Tempera con finiture a olio su tavola'),
(12, 'Olio su tavola'),
(13, 'Penna e inchiostro bruno su carta controfondata'),
(14, 'Tempera e olio su tavola'),
(15, 'Penna e inchiostro bruno, acquerello bruno su carta'),
(16, 'Fusione in bronzo dorato'),
(17, 'Olio su rame');

-- --------------------------------------------------------

--
-- Struttura della tabella `tema`
--

DROP TABLE IF EXISTS `tema`;
CREATE TABLE IF NOT EXISTS `tema` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `tema`
--

INSERT INTO `tema` (`id`, `nome`) VALUES
(11, 'Devozione'),
(10, 'Preziosità'),
(9, 'Naturalismo'),
(8, 'Umiltà'),
(12, 'Penitenza'),
(13, 'Santità'),
(14, 'Annunciazione'),
(15, 'Purezza'),
(16, 'Benedizione'),
(17, 'Madonna col bambino'),
(18, 'Ascetismo'),
(19, 'San Giovannino'),
(20, 'Pietà'),
(21, 'Compassione'),
(22, 'Angeli'),
(23, 'Passione'),
(24, 'Deposizione'),
(25, 'Crocifissione'),
(26, 'Autoritratto'),
(27, 'Ritratto'),
(29, 'Chiaroscuro'),
(30, 'Allegoria'),
(31, 'Natura morta'),
(32, 'Agiografia'),
(33, 'Evangelista'),
(34, 'Martire'),
(36, 'Mitologia'),
(37, 'Tempo'),
(38, 'Epica'),
(39, 'Paesaggio'),
(40, 'Mostri'),
(41, 'Inferno'),
(42, 'Viaggio'),
(43, 'Storia Sacra'),
(45, 'Eroina Biblica'),
(46, 'Contemplazione'),
(47, 'Conversione'),
(48, 'Sacrificio'),
(49, 'Tortura'),
(50, 'Astrazione'),
(51, 'Primitivismo');

-- --------------------------------------------------------

--
-- Struttura della tabella `tipologia`
--

DROP TABLE IF EXISTS `tipologia`;
CREATE TABLE IF NOT EXISTS `tipologia` (
  `id` int NOT NULL AUTO_INCREMENT,
  `categoria` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `tipologia`
--

INSERT INTO `tipologia` (`id`, `categoria`) VALUES
(1, 'Pittura'),
(2, 'Scultura'),
(6, 'Disegno');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
