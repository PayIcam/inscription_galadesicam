-- phpMyAdmin SQL Dump
-- version 4.5.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 16, 2016 at 10:34 AM
-- Server version: 5.5.46-0+deb7u1
-- PHP Version: 5.6.16-1~dotdeb+7.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `icam_galadesicam`
--

-- --------------------------------------------------------

--
-- Table structure for table `guests_payicam`
--

DROP TABLE IF EXISTS `guests_payicam`;
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
  `bracelet_id` int(4) DEFAULT NULL,
  `plage_horaire_entrees` varchar(255) NOT NULL DEFAULT '22h30-23h',
  `image` varchar(255) DEFAULT NULL,
  `paiement` varchar(50) NOT NULL DEFAULT 'espece',
  `price` float NOT NULL DEFAULT '17',
  `tickets_boisson` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `reservations_payicam`
--

DROP TABLE IF EXISTS `reservations_payicam`;
CREATE TABLE IF NOT EXISTS `reservations_payicam` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `soirees` int(11) NOT NULL DEFAULT '0',
  `articles` text NOT NULL,
  `date_option` datetime NOT NULL,
  `date_paiement` datetime NOT NULL,
  `status` enum('A','V','W') NOT NULL DEFAULT 'W',
  `tra_id_payicam` int(11) NOT NULL,
  `tra_url_payicam` varchar(255) NOT NULL,
  `login` varchar(255) NOT NULL,
  `price` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
