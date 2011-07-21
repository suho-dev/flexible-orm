-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 01, 2011 at 01:19 PM
-- Server version: 5.1.41
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE IF NOT EXISTS `cars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `brand` varchar(255) NOT NULL,
  `colour` varchar(32) NOT NULL,
  `doors` int(1) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  `age` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `brand`, `colour`, `doors`, `owner_id`, `name`, `age`) VALUES
(1, 'Alfa Romeo', 'Blue', 4, 1, '156Ti', 6),
(2, 'Volkswagen', 'black', 5, 1, NULL, 0),
(3, 'Volkswagen', 'black', 2, 2, NULL, 0),
(4, 'Toyota', 'White', 4, 2, NULL, 10);

-- --------------------------------------------------------

--
-- Table structure for table `manufacturers`
--

CREATE TABLE IF NOT EXISTS `manufacturers` (
  `name` varchar(32) NOT NULL,
  `country` varchar(32) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `manufacturers`
--

INSERT INTO `manufacturers` (`name`, `country`) VALUES
('Volkswagen', 'Germany'),
('Alfa Romeo', 'Italy'),
('Toyota', 'Japan');

-- --------------------------------------------------------

--
-- Table structure for table `my_elephants`
--

CREATE TABLE IF NOT EXISTS `my_elephants` (
  `name` varchar(32) NOT NULL,
  `weight` decimal(8,2) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `my_elephants`
--

INSERT INTO `my_elephants` (`name`, `weight`) VALUES
('me', '2.50'),
('Roger', '1234.50'),
('Tim', '1400.00');

-- --------------------------------------------------------

--
-- Table structure for table `owners`
--

CREATE TABLE IF NOT EXISTS `owners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `age` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `owners`
--

INSERT INTO `owners` (`id`, `name`, `age`) VALUES
(1, 'Jarrod', 31),
(2, 'Steve', 34);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
