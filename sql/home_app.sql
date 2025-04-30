-- Adminer 4.8.1 MySQL 10.4.28-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

USE `home_app`;

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

DROP TABLE IF EXISTS `odpocet_zarizeni`;
CREATE TABLE `odpocet_zarizeni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idzarizeni` int(11) NOT NULL,
  `odpocet` float(12,6) NOT NULL,
  `casodpoctu` datetime NOT NULL,
  `poznamka` varchar(255) NOT NULL,
  `zadal` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idzarizeni` (`idzarizeni`),
  KEY `zadal` (`zadal`),
  CONSTRAINT `odpocet_zarizeni_ibfk_1` FOREIGN KEY (`idzarizeni`) REFERENCES `zarizeni` (`id`) ON DELETE CASCADE,
  CONSTRAINT `odpocet_zarizeni_ibfk_2` FOREIGN KEY (`zadal`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(100) NOT NULL,
  `heslo` char(32) NOT NULL,
  `username` char(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `users` (`id`, `login`, `heslo`, `username`) VALUES
(1,	'ivan',	'0e45936f31dbcf006d3535c780d83321',	'Ivan');

DROP TABLE IF EXISTS `zarizeni`;
CREATE TABLE `zarizeni` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idadmin` int(11) NOT NULL,
  `nazevZarizeni` varchar(255) NOT NULL,
  `idjednotky` int(11) DEFAULT NULL,
  `poznamka` tinytext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idjednotky` (`idjednotky`),
  KEY `iduser` (`idadmin`),
  CONSTRAINT `zarizeni_ibfk_1` FOREIGN KEY (`idjednotky`) REFERENCES `cis_jednotky_mereni` (`id`) ON DELETE SET NULL,
  CONSTRAINT `zarizeni_ibfk_2` FOREIGN KEY (`idadmin`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `zarizeni` (`id`, `idadmin`, `nazevZarizeni`, `idjednotky`, `poznamka`) VALUES
(1,	1,	'Teplá voda',	1,	NULL),
(2,	1,	'Studená voda',	1,	NULL);

DROP TABLE IF EXISTS `zarizeni2users`;
CREATE TABLE `zarizeni2users` (
  `iduser` int(11) NOT NULL,
  `idzarizeni` int(11) NOT NULL,
  UNIQUE KEY `iduser_idzarizeni` (`iduser`,`idzarizeni`),
  KEY `idzarizeni` (`idzarizeni`),
  CONSTRAINT `zarizeni2users_ibfk_1` FOREIGN KEY (`iduser`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `zarizeni2users_ibfk_2` FOREIGN KEY (`idzarizeni`) REFERENCES `zarizeni` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


-- 2025-04-30 08:02:36
