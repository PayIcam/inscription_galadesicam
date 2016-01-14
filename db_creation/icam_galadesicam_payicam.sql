-- phpMyAdmin SQL Dump
-- version 4.5.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 14, 2016 at 11:57 AM
-- Server version: 5.5.46-0+deb7u1
-- PHP Version: 5.6.16-1~dotdeb+7.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `icam_galadesicam`
--

-- --------------------------------------------------------

--
-- Table structure for table `guests_payicam`
--

CREATE TABLE `guests_payicam` (
  `id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL DEFAULT '-1',
  `icam_id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `nom` varchar(155) DEFAULT NULL,
  `prenom` varchar(155) DEFAULT NULL,
  `repas` tinyint(1) DEFAULT '0',
  `buffet` tinyint(1) NOT NULL,
  `is_icam` tinyint(1) DEFAULT NULL,
  `promo` varchar(45) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(45) DEFAULT NULL,
  `inscription` datetime NOT NULL,
  `sexe` tinyint(1) NOT NULL DEFAULT '1',
  `bracelet_id` int(4) NOT NULL,
  `image` varchar(255) NOT NULL,
  `paiement` varchar(50) NOT NULL DEFAULT 'espece',
  `price` float NOT NULL DEFAULT '17',
  `tickets_boisson` int(11) NOT NULL,
  `champagne` float NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `reservations_payicam`
--

CREATE TABLE `reservations_payicam` (
  `id` int(11) NOT NULL,
  `soirees` int(11) NOT NULL DEFAULT '0',
  `repas` int(11) NOT NULL DEFAULT '0',
  `buffets` int(11) NOT NULL DEFAULT '0',
  `articles` text NOT NULL,
  `date_option` datetime NOT NULL,
  `date_paiement` datetime NOT NULL,
  `status` enum('A','V','W') NOT NULL DEFAULT 'W',
  `url` varchar(255) NOT NULL,
  `login` varchar(255) NOT NULL,
  `prix` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guests_payicam`
--
ALTER TABLE `guests_payicam`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservations_payicam`
--
ALTER TABLE `reservations_payicam`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guests_payicam`
--
ALTER TABLE `guests_payicam`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT for table `reservations_payicam`
--
ALTER TABLE `reservations_payicam`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
