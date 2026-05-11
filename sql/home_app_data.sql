-- Adminer 5.3.0 MariaDB 10.4.34-MariaDB-log dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- Access denied for user &#039;a376140_homeapp&#039;@&#039;%&#039; to database &#039;d376140_homeapp&#039;
DELIMITER ;;

DROP PROCEDURE IF EXISTS `SpotrebaOd`;;
CREATE PROCEDURE `SpotrebaOd` (IN `in_iduser` int, IN `in_idmeridla` int, IN `in_casOdectu` datetime, IN `in_casOdectuDo` datetime)
BEGIN
    -- Najdeme první datum konce období, pokud in_casOdectuDo není zadán
    DECLARE datDo DATETIME;

    IF in_casOdectuDo IS NOT NULL THEN
        SET datDo = in_casOdectuDo;
    ELSE
        SELECT MIN(casodectu)
        INTO datDo
        FROM odecty
        WHERE idmeridla = in_idmeridla
          AND casodectu > in_casOdectu
          AND zacatekobdobi = 1;
    END IF;

    -- Pokud žádný další začátek období neexistuje, vezmi maximum
   IF datDo IS NULL THEN 
    SET datDo = '9999-12-31 23:59:59';
END IF;

    -- Hlavní výběr dat
    WITH FiltrovaneOdecty AS (
        SELECT o.*
        FROM odecty o
        JOIN meridla2users mu ON mu.idmeridla = o.idmeridla
        WHERE o.idmeridla = in_idmeridla
          AND o.casodectu >= in_casOdectu
          AND o.casodectu < datDo
          AND mu.iduser = in_iduser
    ),
    Spotreba AS (
        SELECT 
            o.idmeridla,
            o.id AS idodectu,
            o.casodectu,
            o.odecet,
            o.poznamka,
            o.zadal,
            o.opravil,
            o.zacatekobdobi,
            LAG(o.odecet) OVER (PARTITION BY o.idmeridla ORDER BY o.casodectu) AS stav_predchozi,
            o.odecet - LAG(o.odecet) OVER (PARTITION BY o.idmeridla ORDER BY o.casodectu) AS spotreba,
            LAG(o.casodectu) OVER (PARTITION BY o.idmeridla ORDER BY o.casodectu) AS cas_predchozi
        FROM FiltrovaneOdecty o
    )
    SELECT 
        m.nazev AS nazevMeridla,
        m.poznamka AS meridloPoznamka,
        s.idmeridla,
        s.idodectu,
        s.odecet,
        s.casodectu,
        s.spotreba,
        s.zacatekobdobi,
        cmj.jednotka,
        c.cenazajednotku,
        s.spotreba * c.cenazajednotku AS naklady,
        s.poznamka AS odecetPoznamka,
        u.username AS userZadal,
        u2.username AS userOpravil,
        CASE 
            WHEN timestampdiff(HOUR, s.cas_predchozi, s.casodectu) > 0
                THEN s.spotreba / timestampdiff(HOUR, s.cas_predchozi, s.casodectu)
            ELSE NULL
        END AS prumernaSpotrebaHodina,
        CASE 
            WHEN timestampdiff(DAY, s.cas_predchozi, s.casodectu) > 0
                THEN s.spotreba / timestampdiff(DAY, s.cas_predchozi, s.casodectu)
            ELSE NULL
        END AS prumernaSpotrebaDen,
        timestampdiff(HOUR, s.cas_predchozi, s.casodectu) AS rozdilHodin,
        timestampdiff(DAY, s.cas_predchozi, s.casodectu) AS rozdilDnu
    FROM Spotreba s
    JOIN meridla m ON m.id = s.idmeridla
    JOIN cis_merne_jednotky cmj ON cmj.id = m.idjednotky
    LEFT JOIN ceniky c ON s.casodectu BETWEEN c.platnyod AND COALESCE(c.platnydo, '9999-12-31')
        AND s.idmeridla = c.idmeridla
    LEFT JOIN users u ON u.id = s.zadal
    LEFT JOIN users u2 ON u2.id = s.opravil
    ORDER BY s.casodectu DESC;

END;;

DELIMITER ;

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `ceniky`;
CREATE TABLE `ceniky` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idmeridla` int(11) NOT NULL,
  `dodavatel` varchar(50) NOT NULL,
  `poznamka` varchar(255) NOT NULL,
  `cenazajednotku` decimal(20,5) NOT NULL,
  `odhadcenyzajednotku` decimal(20,5) NOT NULL DEFAULT 0.00000,
  `platnyod` date NOT NULL,
  `platnydo` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idmeridla` (`idmeridla`),
  CONSTRAINT `ceniky_ibfk_1` FOREIGN KEY (`idmeridla`) REFERENCES `meridla` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `ceniky` (`id`, `idmeridla`, `dodavatel`, `poznamka`, `cenazajednotku`, `odhadcenyzajednotku`, `platnyod`, `platnydo`) VALUES
(1,	1,	'Pražská teplárenská',	'',	541.00000,	0.00000,	'2025-01-01',	'2025-12-31'),
(3,	1,	'PVT',	'',	600.00000,	0.00000,	'2026-01-01',	NULL);

DROP TABLE IF EXISTS `cis_frekvence`;
CREATE TABLE `cis_frekvence` (
  `kodfrekvence` char(1) NOT NULL,
  `frekvence` varchar(15) NOT NULL,
  PRIMARY KEY (`kodfrekvence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `cis_frekvence` (`kodfrekvence`, `frekvence`) VALUES
('d',	'den'),
('h',	'hodina'),
('i',	'minuta'),
('m',	'měsíc'),
('y',	'rok');

DROP TABLE IF EXISTS `cis_merne_jednotky`;
CREATE TABLE `cis_merne_jednotky` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jednotka` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `cis_merne_jednotky` (`id`, `jednotka`) VALUES
(1,	'l'),
(2,	'kWh'),
(3,	'm3');

DROP TABLE IF EXISTS `langstrings`;
CREATE TABLE `langstrings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang` char(2) NOT NULL DEFAULT 'cs',
  `checksum` char(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `string` text NOT NULL,
  `section` varchar(20) NOT NULL DEFAULT 'front',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `login_pokusy`;
CREATE TABLE `login_pokusy` (
  `uid` char(32) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `pocet` int(11) NOT NULL DEFAULT 0,
  `blokacedo` datetime DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `login_pokusy` (`uid`, `ip`, `pocet`, `blokacedo`) VALUES
('a82298023f2c0b90a95c5b18c0acf4d4',	'78.45.222.8',	1,	'2026-05-11 18:04:59');

DROP TABLE IF EXISTS `meridla`;
CREATE TABLE `meridla` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idadmin` int(11) NOT NULL,
  `nazev` varchar(255) NOT NULL,
  `idjednotky` int(11) NOT NULL,
  `poznamka` tinytext DEFAULT NULL,
  `aktivni` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idjednotky` (`idjednotky`),
  KEY `iduser` (`idadmin`),
  CONSTRAINT `meridla_ibfk_2` FOREIGN KEY (`idadmin`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `meridla_ibfk_3` FOREIGN KEY (`idjednotky`) REFERENCES `cis_merne_jednotky` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `meridla` (`id`, `idadmin`, `nazev`, `idjednotky`, `poznamka`, `aktivni`) VALUES
(1,	1,	'Teplá voda',	3,	NULL,	1),
(2,	1,	'Studená voda',	3,	NULL,	1),
(3,	1,	'Elektroměr',	2,	NULL,	1);

DROP TABLE IF EXISTS `meridla2users`;
CREATE TABLE `meridla2users` (
  `iduser` int(11) NOT NULL,
  `idmeridla` int(11) NOT NULL,
  `idrole` int(11) NOT NULL DEFAULT 1,
  UNIQUE KEY `iduser_idmeridla` (`iduser`,`idmeridla`),
  KEY `idmeridla` (`idmeridla`),
  KEY `idrole` (`idrole`),
  CONSTRAINT `meridla2users_ibfk_1` FOREIGN KEY (`iduser`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `meridla2users_ibfk_2` FOREIGN KEY (`idmeridla`) REFERENCES `meridla` (`id`) ON DELETE CASCADE,
  CONSTRAINT `meridla2users_ibfk_3` FOREIGN KEY (`idrole`) REFERENCES `role` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `meridla2users` (`iduser`, `idmeridla`, `idrole`) VALUES
(2,	1,	2),
(2,	2,	2),
(2,	3,	2),
(1,	1,	4),
(1,	2,	4),
(1,	3,	4);

DROP TABLE IF EXISTS `odecty`;
CREATE TABLE `odecty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idmeridla` int(11) NOT NULL,
  `odecet` decimal(12,6) NOT NULL,
  `casodectu` datetime NOT NULL,
  `poznamka` varchar(255) NOT NULL,
  `zadal` int(11) DEFAULT NULL,
  `opravil` int(11) DEFAULT NULL,
  `zacatekobdobi` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `zadal` (`zadal`),
  KEY `idmeridla` (`idmeridla`),
  KEY `opravil` (`opravil`),
  CONSTRAINT `odecty_ibfk_2` FOREIGN KEY (`zadal`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `odecty_ibfk_3` FOREIGN KEY (`idmeridla`) REFERENCES `meridla` (`id`),
  CONSTRAINT `odecty_ibfk_4` FOREIGN KEY (`opravil`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `odecty` (`id`, `idmeridla`, `odecet`, `casodectu`, `poznamka`, `zadal`, `opravil`, `zacatekobdobi`) VALUES
(2,	1,	163.000000,	'2025-01-01 00:00:00',	'První odpočet 2025 dle vyúčtování',	1,	1,	1),
(3,	1,	189.300003,	'2025-04-24 16:00:00',	'',	1,	NULL,	0),
(4,	1,	190.292999,	'2025-04-30 16:00:00',	'',	1,	NULL,	0),
(5,	2,	287.000000,	'2025-01-01 00:00:00',	'První odpočet 2025 dle vyúčtování',	1,	NULL,	0),
(6,	1,	191.193000,	'2025-05-07 19:34:00',	'',	1,	NULL,	0),
(7,	2,	333.823000,	'2025-05-07 19:34:00',	'',	1,	NULL,	0),
(8,	1,	192.184000,	'2025-05-12 09:01:00',	'Před Valencií',	1,	1,	0),
(9,	2,	335.876000,	'2025-05-12 09:02:00',	'Před Valencií',	1,	NULL,	0),
(10,	1,	192.969000,	'2025-05-18 15:35:00',	'',	1,	1,	0),
(11,	2,	337.414000,	'2025-05-18 15:36:00',	'',	1,	1,	0),
(13,	2,	338.035000,	'2025-05-20 16:56:00',	'',	1,	NULL,	0),
(15,	1,	193.318000,	'2025-05-20 16:56:00',	'',	1,	1,	0),
(16,	1,	194.263000,	'2025-05-26 18:13:00',	'',	1,	NULL,	0),
(17,	2,	339.854000,	'2025-05-26 18:14:00',	'',	1,	NULL,	0),
(18,	1,	194.721000,	'2025-05-28 19:53:00',	'',	1,	NULL,	0),
(19,	3,	11439.900000,	'2025-05-31 15:21:00',	'Před změnou PRE =&gt;PPAS',	1,	1,	0),
(20,	1,	195.361000,	'2025-05-31 15:22:00',	'',	1,	1,	0),
(21,	2,	341.792000,	'2025-05-31 15:23:00',	'',	1,	NULL,	0),
(22,	1,	195.726000,	'2025-06-03 05:48:00',	'',	1,	1,	0),
(23,	2,	342.624000,	'2025-06-03 05:49:00',	'',	1,	NULL,	0),
(26,	1,	195.866000,	'2025-06-04 07:02:10',	'',	1,	NULL,	0),
(28,	2,	343.662000,	'2025-06-06 11:01:02',	'',	1,	1,	0),
(29,	1,	196.333000,	'2025-06-06 11:01:49',	'',	1,	NULL,	0),
(30,	1,	197.485000,	'2025-06-13 06:07:34',	'',	1,	NULL,	0),
(31,	2,	345.825000,	'2025-06-13 06:08:06',	'',	1,	NULL,	0),
(32,	1,	198.486000,	'2025-06-19 17:25:19',	'',	1,	NULL,	0),
(33,	2,	347.988000,	'2025-06-19 17:25:54',	'',	1,	NULL,	0),
(34,	1,	198.935000,	'2025-06-23 06:08:34',	'',	1,	NULL,	0),
(35,	1,	200.395000,	'2025-07-01 20:12:26',	'',	1,	NULL,	0),
(36,	2,	352.254000,	'2025-07-01 20:12:46',	'',	1,	NULL,	0),
(37,	1,	200.520000,	'2025-07-04 10:08:56',	'',	1,	NULL,	0),
(38,	1,	201.103000,	'2025-07-08 05:36:58',	'',	1,	NULL,	0),
(39,	2,	353.951000,	'2025-07-08 05:37:15',	'',	1,	NULL,	0),
(40,	1,	203.118000,	'2025-07-17 18:58:32',	'',	1,	NULL,	0),
(41,	2,	357.557000,	'2025-07-17 18:58:56',	'',	1,	NULL,	0),
(42,	1,	205.959000,	'2025-08-04 22:10:44',	'',	1,	NULL,	0),
(43,	1,	207.978000,	'2025-08-19 18:30:08',	'',	1,	NULL,	0),
(44,	1,	216.835000,	'2025-11-22 14:26:42',	'',	1,	NULL,	0),
(45,	2,	389.307000,	'2025-11-22 14:27:12',	'',	1,	NULL,	0),
(46,	1,	217.229000,	'2025-11-24 16:51:12',	'',	1,	NULL,	0),
(47,	2,	390.047000,	'2025-11-24 16:52:18',	'',	1,	NULL,	0),
(48,	1,	217.368000,	'2025-11-25 21:45:19',	'',	1,	NULL,	0),
(49,	1,	217.514000,	'2025-11-27 16:47:00',	'',	1,	NULL,	0),
(50,	2,	390.555000,	'2025-11-27 16:48:00',	'',	1,	NULL,	0),
(51,	1,	218.242000,	'2025-12-03 21:19:12',	'',	1,	NULL,	0),
(52,	2,	392.064000,	'2025-12-03 21:19:57',	'',	1,	NULL,	0),
(53,	1,	220.232000,	'2025-12-20 15:56:32',	'',	1,	NULL,	0),
(54,	2,	396.633000,	'2025-12-20 15:56:50',	'',	1,	NULL,	0),
(55,	1,	221.207000,	'2025-12-30 22:25:45',	'',	1,	NULL,	0),
(56,	1,	221.356000,	'2026-01-01 09:46:30',	'',	1,	1,	1),
(57,	2,	399.688000,	'2026-01-01 09:46:54',	'',	1,	1,	1),
(58,	1,	221.951000,	'2026-01-05 19:56:35',	'',	1,	NULL,	0),
(59,	2,	400.998000,	'2026-01-05 19:56:55',	'',	1,	NULL,	0),
(60,	1,	224.149000,	'2026-01-24 10:36:49',	'',	1,	NULL,	0),
(61,	2,	403.134000,	'2026-01-24 10:37:05',	'',	1,	NULL,	0),
(64,	1,	225.078000,	'2026-02-08 15:13:00',	'',	1,	NULL,	0),
(65,	1,	226.799000,	'2026-02-18 22:11:30',	'',	1,	NULL,	0),
(66,	2,	412.548000,	'2026-02-18 22:12:23',	'',	1,	NULL,	0),
(67,	1,	230.585000,	'2026-03-24 05:41:17',	'',	1,	1,	0),
(68,	2,	421.197000,	'2026-03-24 05:41:42',	'',	1,	NULL,	0),
(69,	1,	231.030000,	'2026-03-30 20:43:26',	'',	1,	NULL,	0),
(70,	2,	422.291000,	'2026-03-30 20:43:46',	'',	1,	NULL,	0),
(71,	1,	231.243000,	'2026-04-05 18:06:55',	'',	1,	NULL,	0),
(72,	1,	232.368000,	'2026-04-15 19:35:29',	'',	1,	NULL,	0),
(73,	1,	234.142000,	'2026-05-04 09:13:07',	'',	1,	NULL,	0),
(74,	2,	430.305000,	'2026-05-04 09:13:22',	'',	1,	NULL,	0);

DROP TABLE IF EXISTS `pausaly`;
CREATE TABLE `pausaly` (
  `id` int(11) NOT NULL,
  `idceniku` int(11) NOT NULL,
  `frekvence` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci NOT NULL,
  `pausal` decimal(20,5) NOT NULL,
  KEY `idceniku` (`idceniku`),
  KEY `frekvence` (`frekvence`),
  CONSTRAINT `pausaly_ibfk_1` FOREIGN KEY (`idceniku`) REFERENCES `ceniky` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pausaly_ibfk_2` FOREIGN KEY (`frekvence`) REFERENCES `cis_frekvence` (`kodfrekvence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


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
  `uuid` binary(16) DEFAULT NULL,
  `login` varchar(50) NOT NULL,
  `heslo` char(32) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `ip` varchar(100) DEFAULT NULL,
  `aktivni` tinyint(4) DEFAULT 2,
  `casregistrace` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `users` (`id`, `uuid`, `login`, `heslo`, `username`, `email`, `ip`, `aktivni`, `casregistrace`) VALUES
(1,	UNHEX('889DF53A3CAA11F0B4673EB23E37A02E'),	'ivan',	'0e45936f31dbcf006d3535c780d83321',	'Ivan',	'ivan.latecka@gmail.com',	NULL,	1,	'2025-05-29 18:10:38'),
(2,	UNHEX('889DF6F83CAA11F0B4673EB23E37A02E'),	'evina',	'3ba33f02d330d3f1772a9b36f2f7f46f',	'Evina',	'eva.dubova@icloud.com',	NULL,	1,	'2025-05-29 18:10:38');

DROP VIEW IF EXISTS `v_spotrebascenami`;
CREATE TABLE `v_spotrebascenami` (`nazevMeridla` varchar(255), `meridloPoznamka` tinytext, `idmeridla` int(11), `idodectu` int(11), `odecet` decimal(12,6), `casodectu` datetime, `spotreba` decimal(13,6), `zacatekobdobi` tinyint(4), `jednotka` varchar(255), `cenazajednotku` decimal(20,5), `naklady` decimal(33,11), `odecetPoznamka` varchar(255), `userZadal` varchar(50), `userOpravil` varchar(50), `prumernaSpotrebaHodina` decimal(17,10), `prumernaSpotrebaDen` decimal(17,10), `rozdilHodin` bigint(21), `rozdilDnu` bigint(21));


DROP VIEW IF EXISTS `v_spotrebascenamicelkem`;
CREATE TABLE `v_spotrebascenamicelkem` (`idmeridla` int(11), `platnyod` date, `platnydo` varchar(10), `cenazajednotku` decimal(20,5), `celkova_spotreba` decimal(35,6), `naklady` decimal(55,11));


DROP TABLE IF EXISTS `zalohy`;
CREATE TABLE `zalohy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idmeridla` int(11) NOT NULL,
  `zaloha` decimal(20,5) NOT NULL,
  `frekvence` char(1) DEFAULT NULL,
  `platnaod` date NOT NULL,
  `platnado` date DEFAULT NULL,
  `poznamka` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idmeridla` (`idmeridla`),
  KEY `frekvence` (`frekvence`),
  CONSTRAINT `zalohy_ibfk_1` FOREIGN KEY (`idmeridla`) REFERENCES `meridla` (`id`) ON DELETE CASCADE,
  CONSTRAINT `zalohy_ibfk_2` FOREIGN KEY (`frekvence`) REFERENCES `cis_frekvence` (`kodfrekvence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

INSERT INTO `zalohy` (`id`, `idmeridla`, `zaloha`, `frekvence`, `platnaod`, `platnado`, `poznamka`) VALUES
(1,	1,	3313.00000,	'm',	'2025-01-01',	'2025-03-31',	''),
(2,	1,	4397.00000,	'm',	'2025-04-01',	NULL,	'');

DROP TABLE IF EXISTS `v_spotrebascenami`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_spotrebascenami` AS with Spotreba as (select `odecty`.`idmeridla` AS `idmeridla`,`odecty`.`id` AS `idodectu`,`odecty`.`casodectu` AS `casodectu`,`odecty`.`odecet` AS `odecet`,`odecty`.`poznamka` AS `poznamka`,`odecty`.`zadal` AS `zadal`,`odecty`.`opravil` AS `opravil`,lag(`odecty`.`odecet`,1) over ( partition by `odecty`.`idmeridla` order by `odecty`.`casodectu`) AS `stav_predchozi`,`odecty`.`odecet` - lag(`odecty`.`odecet`,1) over ( partition by `odecty`.`idmeridla` order by `odecty`.`casodectu`) AS `spotreba`,lag(`odecty`.`casodectu`,1) over ( partition by `odecty`.`idmeridla` order by `odecty`.`casodectu`) AS `cas_predchozi`,`odecty`.`zacatekobdobi` AS `zacatekobdobi` from `odecty`)select `m`.`nazev` AS `nazevMeridla`,`m`.`poznamka` AS `meridloPoznamka`,`s`.`idmeridla` AS `idmeridla`,`s`.`idodectu` AS `idodectu`,`s`.`odecet` AS `odecet`,`s`.`casodectu` AS `casodectu`,`s`.`spotreba` AS `spotreba`,`s`.`zacatekobdobi` AS `zacatekobdobi`,`cmj`.`jednotka` AS `jednotka`,`c`.`cenazajednotku` AS `cenazajednotku`,`s`.`spotreba` * `c`.`cenazajednotku` AS `naklady`,`s`.`poznamka` AS `odecetPoznamka`,`u`.`username` AS `userZadal`,`u2`.`username` AS `userOpravil`,case when timestampdiff(HOUR,`s`.`cas_predchozi`,`s`.`casodectu`) > 0 then `s`.`spotreba` / timestampdiff(HOUR,`s`.`cas_predchozi`,`s`.`casodectu`) else NULL end AS `prumernaSpotrebaHodina`,case when timestampdiff(DAY,`s`.`cas_predchozi`,`s`.`casodectu`) > 0 then `s`.`spotreba` / timestampdiff(DAY,`s`.`cas_predchozi`,`s`.`casodectu`) else NULL end AS `prumernaSpotrebaDen`,timestampdiff(HOUR,`s`.`cas_predchozi`,`s`.`casodectu`) AS `rozdilHodin`,timestampdiff(DAY,`s`.`cas_predchozi`,`s`.`casodectu`) AS `rozdilDnu` from (((((`spotreba` `s` left join `ceniky` `c` on(`s`.`casodectu` between `c`.`platnyod` and coalesce(`c`.`platnydo`,'9999-12-31') and `s`.`idmeridla` = `c`.`idmeridla`)) join `meridla` `m` on(`m`.`id` = `s`.`idmeridla`)) join `cis_merne_jednotky` `cmj` on(`cmj`.`id` = `m`.`idjednotky`)) left join `users` `u` on(`u`.`id` = `s`.`zadal`)) left join `users` `u2` on(`u2`.`id` = `s`.`opravil`)) order by `s`.`idmeridla`,`s`.`casodectu`;

DROP TABLE IF EXISTS `v_spotrebascenamicelkem`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_spotrebascenamicelkem` AS with spotreba as (select `odecty`.`idmeridla` AS `idmeridla`,`odecty`.`casodectu` AS `casodectu`,`odecty`.`odecet` AS `stav_aktualni`,lag(`odecty`.`odecet`,1) over ( partition by `odecty`.`idmeridla` order by `odecty`.`casodectu`) AS `stav_predchozi`,`odecty`.`odecet` - lag(`odecty`.`odecet`,1) over ( partition by `odecty`.`idmeridla` order by `odecty`.`casodectu`) AS `spotreba` from `odecty`)select `s`.`idmeridla` AS `idmeridla`,`c`.`platnyod` AS `platnyod`,coalesce(`c`.`platnydo`,'9999-12-31') AS `platnydo`,`c`.`cenazajednotku` AS `cenazajednotku`,sum(`s`.`spotreba`) AS `celkova_spotreba`,sum(`s`.`spotreba`) * `c`.`cenazajednotku` AS `naklady` from (`spotreba` `s` join `ceniky` `c` on(`s`.`casodectu` between `c`.`platnyod` and coalesce(`c`.`platnydo`,'9999-12-31') and `s`.`idmeridla` = `c`.`idmeridla`)) group by `s`.`idmeridla`,`c`.`id`;

-- 2026-05-11 16:13:31 UTC
