-- Adminer 5.2.1 MariaDB 11.4.8-MariaDB-ubu2404-log dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

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
  `platnyod` date NOT NULL,
  `platnydo` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idmeridla` (`idmeridla`),
  CONSTRAINT `ceniky_ibfk_1` FOREIGN KEY (`idmeridla`) REFERENCES `meridla` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `cis_frekvence`;
CREATE TABLE `cis_frekvence` (
  `kodfrekvence` char(1) NOT NULL,
  `frekvence` varchar(15) NOT NULL,
  PRIMARY KEY (`kodfrekvence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


DROP TABLE IF EXISTS `cis_merne_jednotky`;
CREATE TABLE `cis_merne_jednotky` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jednotka` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_czech_ci;


DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;


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


DROP TABLE IF EXISTS `v_spotrebascenami`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_spotrebascenami` AS with Spotreba as (select `odecty`.`idmeridla` AS `idmeridla`,`odecty`.`id` AS `idodectu`,`odecty`.`casodectu` AS `casodectu`,`odecty`.`odecet` AS `odecet`,`odecty`.`poznamka` AS `poznamka`,`odecty`.`zadal` AS `zadal`,`odecty`.`opravil` AS `opravil`,lag(`odecty`.`odecet`,1) over ( partition by `odecty`.`idmeridla` order by `odecty`.`casodectu`) AS `stav_predchozi`,`odecty`.`odecet` - lag(`odecty`.`odecet`,1) over ( partition by `odecty`.`idmeridla` order by `odecty`.`casodectu`) AS `spotreba`,lag(`odecty`.`casodectu`,1) over ( partition by `odecty`.`idmeridla` order by `odecty`.`casodectu`) AS `cas_predchozi`,`odecty`.`zacatekobdobi` AS `zacatekobdobi` from `odecty`)select `m`.`nazev` AS `nazevMeridla`,`m`.`poznamka` AS `meridloPoznamka`,`s`.`idmeridla` AS `idmeridla`,`s`.`idodectu` AS `idodectu`,`s`.`odecet` AS `odecet`,`s`.`casodectu` AS `casodectu`,`s`.`spotreba` AS `spotreba`,`s`.`zacatekobdobi` AS `zacatekobdobi`,`cmj`.`jednotka` AS `jednotka`,`c`.`cenazajednotku` AS `cenazajednotku`,`s`.`spotreba` * `c`.`cenazajednotku` AS `naklady`,`s`.`poznamka` AS `odecetPoznamka`,`u`.`username` AS `userZadal`,`u2`.`username` AS `userOpravil`,case when timestampdiff(HOUR,`s`.`cas_predchozi`,`s`.`casodectu`) > 0 then `s`.`spotreba` / timestampdiff(HOUR,`s`.`cas_predchozi`,`s`.`casodectu`) else NULL end AS `prumernaSpotrebaHodina`,case when timestampdiff(DAY,`s`.`cas_predchozi`,`s`.`casodectu`) > 0 then `s`.`spotreba` / timestampdiff(DAY,`s`.`cas_predchozi`,`s`.`casodectu`) else NULL end AS `prumernaSpotrebaDen`,timestampdiff(HOUR,`s`.`cas_predchozi`,`s`.`casodectu`) AS `rozdilHodin`,timestampdiff(DAY,`s`.`cas_predchozi`,`s`.`casodectu`) AS `rozdilDnu` from (((((`spotreba` `s` left join `ceniky` `c` on(`s`.`casodectu` between `c`.`platnyod` and coalesce(`c`.`platnydo`,'9999-12-31') and `s`.`idmeridla` = `c`.`idmeridla`)) join `meridla` `m` on(`m`.`id` = `s`.`idmeridla`)) join `cis_merne_jednotky` `cmj` on(`cmj`.`id` = `m`.`idjednotky`)) left join `users` `u` on(`u`.`id` = `s`.`zadal`)) left join `users` `u2` on(`u2`.`id` = `s`.`opravil`)) order by `s`.`idmeridla`,`s`.`casodectu`;

DROP TABLE IF EXISTS `v_spotrebascenamicelkem`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `v_spotrebascenamicelkem` AS with spotreba as (select `odecty`.`idmeridla` AS `idmeridla`,`odecty`.`casodectu` AS `casodectu`,`odecty`.`odecet` AS `stav_aktualni`,lag(`odecty`.`odecet`,1) over ( partition by `odecty`.`idmeridla` order by `odecty`.`casodectu`) AS `stav_predchozi`,`odecty`.`odecet` - lag(`odecty`.`odecet`,1) over ( partition by `odecty`.`idmeridla` order by `odecty`.`casodectu`) AS `spotreba` from `odecty`)select `s`.`idmeridla` AS `idmeridla`,`c`.`platnyod` AS `platnyod`,coalesce(`c`.`platnydo`,'9999-12-31') AS `platnydo`,`c`.`cenazajednotku` AS `cenazajednotku`,sum(`s`.`spotreba`) AS `celkova_spotreba`,sum(`s`.`spotreba`) * `c`.`cenazajednotku` AS `naklady` from (`spotreba` `s` join `ceniky` `c` on(`s`.`casodectu` between `c`.`platnyod` and coalesce(`c`.`platnydo`,'9999-12-31') and `s`.`idmeridla` = `c`.`idmeridla`)) group by `s`.`idmeridla`,`c`.`id`;

-- 2026-01-25 19:53:18 UTC
