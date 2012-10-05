-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Generation Time: Oct 05, 2012 at 07:32 AM
-- Server version: 5.1.63-nmm1-log
-- PHP Version: 5.3.13-nmm1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `d013e015`
--

-- --------------------------------------------------------

--
-- Table structure for table `hits`
--

CREATE TABLE IF NOT EXISTS `hits` (
  `hit_id` int(11) NOT NULL AUTO_INCREMENT,
  `survey_id` varchar(150) NOT NULL,
  `worker_id` varchar(100) NOT NULL,
  `guid` varchar(55) NOT NULL,
  `status` varchar(20) NOT NULL,
  `time_created` datetime NOT NULL,
  `date_returned` datetime NOT NULL,
  PRIMARY KEY (`hit_id`),
  KEY `survey_id` (`survey_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=376 ;

-- --------------------------------------------------------

--
-- Table structure for table `surveys`
--

CREATE TABLE IF NOT EXISTS `surveys` (
  `survey_id` varchar(100) NOT NULL,
  `survey_name` varchar(250) NOT NULL,
  `survey_link` varchar(400) NOT NULL,
  `min_time` int(11) NOT NULL,
  `debrief` text NOT NULL,
  `created_by` int(11) NOT NULL,
  PRIMARY KEY (`survey_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `hash` varchar(256) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `institution` varchar(200) NOT NULL,
  `email` varchar(150) NOT NULL,
  `active` int(11) NOT NULL DEFAULT '0',
  `last_login` datetime NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
