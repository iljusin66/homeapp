-- Adminer 4.8.1 MySQL 10.4.28-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `ceny_jednotky`;
CREATE TABLE `ceny_jednotky` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idzarizeni` int(11) NOT NULL,
  `idjednotky` int(11) NOT NULL,
  `dodavatel` varchar(50) NOT NULL,
  `poznamka` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idzarizeni` (`idzarizeni`),
  KEY `idjednotky` (`idjednotky`),
  CONSTRAINT `ceny_jednotky_ibfk_1` FOREIGN KEY (`idzarizeni`) REFERENCES `zarizeni` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ceny_jednotky_ibfk_2` FOREIGN KEY (`idjednotky`) REFERENCES `cis_jednotky_mereni` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `cis_jednotky_mereni`;
CREATE TABLE `cis_jednotky_mereni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jednotka` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `cis_jednotky_mereni` (`id`, `jednotka`) VALUES
(1,	'l'),
(2,	'kWh');

DROP TABLE IF EXISTS `langstrings`;
CREATE TABLE `langstrings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` char(2) NOT NULL DEFAULT 'cs',
  `checksum` char(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `string` text NOT NULL,
  `section` varchar(20) NOT NULL DEFAULT 'front',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `odecet_zarizeni`;
CREATE TABLE `odecet_zarizeni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idzarizeni` int(11) NOT NULL,
  `odecet` float(12,6) NOT NULL,
  `casodpoctu` datetime NOT NULL,
  `poznamka` varchar(255) NOT NULL,
  `zadal` int(11) DEFAULT NULL,
  `opravil` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `zadal` (`zadal`),
  KEY `idzarizeni` (`idzarizeni`),
  KEY `opravil` (`opravil`),
  CONSTRAINT `odecet_zarizeni_ibfk_2` FOREIGN KEY (`zadal`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `odecet_zarizeni_ibfk_3` FOREIGN KEY (`idzarizeni`) REFERENCES `zarizeni` (`id`),
  CONSTRAINT `odecet_zarizeni_ibfk_4` FOREIGN KEY (`opravil`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `odecet_zarizeni` (`id`, `idzarizeni`, `odecet`, `casodpoctu`, `poznamka`, `zadal`, `opravil`) VALUES
(1,	1,	487.540009,	'2025-05-06 19:30:00',	'Hokus pokus',	1,	1);

DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `role` (`id`, `role`) VALUES
(1,	'reader'),
(2,	'writer'),
(3,	'editor'),
(4,	'admin');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) NOT NULL,
  `heslo` char(32) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `users` (`id`, `login`, `heslo`, `username`, `email`) VALUES
(1,	'ivan',	'0e45936f31dbcf006d3535c780d83321',	'Ivan',	NULL);

DROP TABLE IF EXISTS `zarizeni`;
CREATE TABLE `zarizeni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idadmin` int(11) NOT NULL,
  `nazev` varchar(255) NOT NULL,
  `idjednotky` int(11) DEFAULT NULL,
  `poznamka` tinytext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idjednotky` (`idjednotky`),
  KEY `iduser` (`idadmin`),
  CONSTRAINT `zarizeni_ibfk_1` FOREIGN KEY (`idjednotky`) REFERENCES `cis_jednotky_mereni` (`id`) ON DELETE SET NULL,
  CONSTRAINT `zarizeni_ibfk_2` FOREIGN KEY (`idadmin`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `zarizeni` (`id`, `idadmin`, `nazev`, `idjednotky`, `poznamka`) VALUES
(1,	1,	'Teplá voda',	1,	NULL),
(2,	1,	'Studená voda',	1,	NULL);

DROP TABLE IF EXISTS `zarizeni2users`;
CREATE TABLE `zarizeni2users` (
  `iduser` int(11) NOT NULL,
  `idzarizeni` int(11) NOT NULL,
  `idrole` int(11) NOT NULL DEFAULT 1,
  UNIQUE KEY `iduser_idzarizeni` (`iduser`,`idzarizeni`),
  KEY `idzarizeni` (`idzarizeni`),
  KEY `idrole` (`idrole`),
  CONSTRAINT `zarizeni2users_ibfk_1` FOREIGN KEY (`iduser`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `zarizeni2users_ibfk_2` FOREIGN KEY (`idzarizeni`) REFERENCES `zarizeni` (`id`) ON DELETE CASCADE,
  CONSTRAINT `zarizeni2users_ibfk_3` FOREIGN KEY (`idrole`) REFERENCES `role` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `zarizeni2users` (`iduser`, `idzarizeni`, `idrole`) VALUES
(1,	1,	4),
(1,	2,	4);

-- 2025-05-07 05:20:32
