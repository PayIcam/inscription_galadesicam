-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u8
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le: Mer 05 Avril 2017 à 23:16
-- Version du serveur: 5.5.54
-- Version de PHP: 5.6.30-1~dotdeb+7.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `spring_festival`
--

-- --------------------------------------------------------

--
-- Structure de la table `administrateurs`
--

CREATE TABLE IF NOT EXISTS `administrateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nom` varchar(55) NOT NULL,
  `prenom` varchar(55) NOT NULL,
  `online` tinyint(1) NOT NULL DEFAULT '0',
  `role_id` int(2) NOT NULL DEFAULT '3',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- Contenu de la table `administrateurs`
--


-- --------------------------------------------------------

--
-- Structure de la table `annuaire_eleves`
--

CREATE TABLE IF NOT EXISTS `annuaire_eleves` (
  `user_id` int(11) NOT NULL,
  `last_name` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `first_name` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `promotion_year` int(4) DEFAULT NULL,
  `promotion_code` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `school_site` varchar(20) CHARACTER SET latin1 DEFAULT NULL,
  `email` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
  `phone_number` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  FULLTEXT KEY `last_name` (`last_name`,`first_name`,`phone_number`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `configs`
--

CREATE TABLE IF NOT EXISTS `configs` (
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `configs`
--

INSERT INTO `configs` (`name`, `value`) VALUES
('authentification', '1'),
('inscriptions', '0'),
('maintenance', ''),
('modifications_places', '0'),
('quota_soirees', '800'),
('websitename', 'Spring Festival');

-- --------------------------------------------------------

--
-- Structure de la table `entrees`
--

CREATE TABLE IF NOT EXISTS `entrees` (
  `guest_id` int(11) NOT NULL,
  `arrived` tinyint(1) DEFAULT NULL,
  `arrival_time` datetime DEFAULT NULL,
  PRIMARY KEY (`guest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `etudiants_icam_lille`
--

CREATE TABLE IF NOT EXISTS `etudiants_icam_lille` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `promo` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `sexe` tinyint(1) NOT NULL DEFAULT '1',
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6315 ;

--
-- Contenu de la table `etudiants_icam_lille`
--

-- --------------------------------------------------------

--
-- Structure de la table `guests`
--

CREATE TABLE IF NOT EXISTS `guests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(155) DEFAULT NULL,
  `prenom` varchar(155) DEFAULT NULL,
  `is_icam` tinyint(1) DEFAULT NULL,
  `promo` varchar(45) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(45) DEFAULT NULL,
  `inscription` datetime NOT NULL,
  `sexe` tinyint(1) NOT NULL DEFAULT '1',
  `bracelet_id` int(4) DEFAULT NULL,
  `plage_horaire_entrees` varchar(255) NOT NULL DEFAULT '22h30-23h',
  `image` varchar(255) DEFAULT NULL,
  `paiement` varchar(50) NOT NULL DEFAULT 'espece',
  `price` float NOT NULL DEFAULT '17',
  `tickets_boisson` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- Structure de la table `guests_payicam`
--

CREATE TABLE IF NOT EXISTS `guests_payicam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `guest_id` int(11) NOT NULL DEFAULT '-1',
  `icam_id` int(11) DEFAULT NULL,
  `reservation_id` int(11) NOT NULL,
  `nom` varchar(155) DEFAULT NULL,
  `prenom` varchar(155) DEFAULT NULL,
  `is_icam` tinyint(1) DEFAULT NULL,
  `promo` varchar(45) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(45) DEFAULT NULL,
  `inscription` datetime NOT NULL,
  `sexe` tinyint(1) NOT NULL DEFAULT '1',
  `bracelet_id` int(4) NOT NULL,
  `plage_horaire_entrees` varchar(255) NOT NULL DEFAULT '22h30-23h',
  `image` varchar(255) NOT NULL,
  `paiement` varchar(50) NOT NULL DEFAULT 'espece',
  `price` float NOT NULL DEFAULT '17',
  `tickets_boisson` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;


-- --------------------------------------------------------

--
-- Structure de la table `icam_has_guest`
--

CREATE TABLE IF NOT EXISTS `icam_has_guest` (
  `icam_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  PRIMARY KEY (`icam_id`,`guest_id`),
  KEY `fk_icam_has_guest` (`icam_id`),
  KEY `fk_gest_has_icam` (`guest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Contenu de la table `icam_has_guest`
--

-- --------------------------------------------------------

--
-- Structure de la table `reservations_payicam`
--

CREATE TABLE IF NOT EXISTS `reservations_payicam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `soirees` int(11) NOT NULL DEFAULT '0',
  `repas` int(11) NOT NULL DEFAULT '0',
  `buffets` int(11) NOT NULL DEFAULT '0',
  `articles` text NOT NULL,
  `date_option` datetime NOT NULL,
  `date_paiement` datetime NOT NULL,
  `status` enum('A','V','W') NOT NULL DEFAULT 'W',
  `tra_id_payicam` int(11) NOT NULL,
  `tra_url_payicam` varchar(255) NOT NULL,
  `login` varchar(255) NOT NULL,
  `price` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `slug` varchar(60) NOT NULL,
  `level` int(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Contenu de la table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `level`) VALUES
(1, 'Administrateur', 'admin', 2),
(2, 'Membre', 'member', 1),
(3, 'Non inscrit', 'non-inscrit', 0);

-- --------------------------------------------------------

--
-- Structure de la table `vestiaire`
--

CREATE TABLE IF NOT EXISTS `vestiaire` (
  `guest_id` int(11) NOT NULL,
  `vestiaire` tinyint(1) DEFAULT NULL,
  `vestiaire_in` time DEFAULT NULL,
  `vestiaire_out` time DEFAULT NULL,
  PRIMARY KEY (`guest_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `entrees`
--
ALTER TABLE `entrees`
  ADD CONSTRAINT `fk_entrees_guests` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `vestiaire`
--
ALTER TABLE `vestiaire`
  ADD CONSTRAINT `fk_vestiaire_guests` FOREIGN KEY (`guest_id`) REFERENCES `guests` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
