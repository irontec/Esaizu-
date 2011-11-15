-- phpMyAdmin SQL Dump
-- version 3.3.7deb5build0.10.10.1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 23-05-2011 a las 12:50:06
-- Versión del servidor: 5.1.49
-- Versión de PHP: 5.3.3-1ubuntu9.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `esaizu`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ci_sessions`
--

CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(16) NOT NULL DEFAULT '0',
  `user_agent` varchar(50) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `remoteId` varchar(40) DEFAULT NULL,
  `idU` int(10) unsigned NOT NULL,
  `idUP` bigint(20) unsigned NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `data` text,
  `publishDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `link` varchar(200) DEFAULT NULL,
  `owner` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idU` (`idU`),
  KEY `idUP` (`idUP`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15474 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `message_queue`
--

CREATE TABLE IF NOT EXISTS `message_queue` (
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  `idU` int(10) unsigned NOT NULL,
  `idUP` bigint(20) unsigned NOT NULL,
  `title` varchar(140) NOT NULL,
  `publishDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `post` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `publishDate` (`publishDate`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plugins`
--

CREATE TABLE IF NOT EXISTS `plugins` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `activated` tinyint(1) unsigned NOT NULL,
  `className` varchar(45) DEFAULT NULL,
  `updateFrecuency` int(10) unsigned NOT NULL DEFAULT '500',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

INSERT INTO `plugins` (`id`, `name`, `activated`, `className`, `updateFrecuency`) VALUES
(1, 'wordpress', 1, 'Wordpress', 600),
(2, 'twitter', 0, 'Twitter', 70),
(3, 'facebook', 0, 'Facebook', 90),
(4, 'linkedIn', 0, 'Linkedin', 700),
(5, 'feed', 1, 'Feed', 110),
(6, 'flickr', 0, 'Flickr', 6500);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userName` varchar(45) NOT NULL,
  `password` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '0',
  `activationCode` varchar(50) DEFAULT NULL,
  `forgottenPasswordCode` varchar(50) DEFAULT NULL,
  `deleteAccountCode` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `lastVisit` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=42 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `userColumnPlugins`
--

CREATE TABLE IF NOT EXISTS `userColumnPlugins` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idC` bigint(20) unsigned NOT NULL,
  `idP` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idC` (`idC`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=162 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `userColumns`
--

CREATE TABLE IF NOT EXISTS `userColumns` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `idU` bigint(20) unsigned NOT NULL,
  `identificador` varchar(80) DEFAULT NULL,
  `order` smallint(5) unsigned DEFAULT NULL,
  `minimized` int(1) unsigned NOT NULL DEFAULT '0',
  `lastUserCheck` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `filters` text NOT NULL,
  `type` varchar(25) NOT NULL DEFAULT 'standard',
  PRIMARY KEY (`id`),
  KEY `idU` (`idU`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=93 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `userPlugins`
--

CREATE TABLE IF NOT EXISTS `userPlugins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idU` int(10) unsigned NOT NULL,
  `idP` int(11) NOT NULL,
  `alias` varchar(25) NOT NULL,
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `auth` text,
  `metadata` text NOT NULL,
  `lastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idU` (`idU`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=97 ;
