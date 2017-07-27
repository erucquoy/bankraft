-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Client :  127.0.0.1
-- Généré le :  Jeu 27 Juillet 2017 à 09:16
-- Version du serveur :  5.7.14
-- Version de PHP :  5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `bankraft`
--

-- --------------------------------------------------------

--
-- Structure de la table `articles`
--

CREATE TABLE `articles` (
  `article_id` varchar(13) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `price` int(6) NOT NULL,
  `expiration` varchar(4) NOT NULL,
  `required` varchar(13) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `articles`
--

INSERT INTO `articles` (`article_id`, `user_id`, `title`, `description`, `price`, `expiration`, `required`) VALUES
('5415b6d07d866', '5415777599c6b', 'test', 'test', 100, '0', '0');

-- --------------------------------------------------------

--
-- Structure de la table `commands`
--

CREATE TABLE `commands` (
  `article_id` varchar(13) NOT NULL,
  `server_id` varchar(22) NOT NULL,
  `command` text NOT NULL,
  `type` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `commands`
--

INSERT INTO `commands` (`article_id`, `server_id`, `command`, `type`) VALUES
('5415b6d07d866', '54158694d0558273712611', 'op anhackin', 1);

-- --------------------------------------------------------

--
-- Structure de la table `funds`
--

CREATE TABLE `funds` (
  `user_id` varchar(13) NOT NULL,
  `amount` varchar(24) NOT NULL,
  `key` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `funds`
--

INSERT INTO `funds` (`user_id`, `amount`, `key`) VALUES
('5415777599c6b', 'YHOcFWiBblC4gQnde/9gkw==', 'c0ed82136e7484b9'),
('54157da6e4e28', 'TJF8zIGteBXqATyxtRZH1w==', 'bc50c9db4c6896e4');

-- --------------------------------------------------------

--
-- Structure de la table `limits`
--

CREATE TABLE `limits` (
  `article_id` varchar(13) NOT NULL,
  `type` int(1) NOT NULL,
  `amount` int(6) NOT NULL,
  `period` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `logs_starpass`
--

CREATE TABLE `logs_starpass` (
  `id` int(11) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `code` varchar(8) NOT NULL,
  `pays` varchar(20) NOT NULL,
  `palier` varchar(20) NOT NULL,
  `type` varchar(20) NOT NULL,
  `id_palier` varchar(20) NOT NULL,
  `datas` varchar(255) NOT NULL,
  `nb_tokens` varchar(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `logs_starpass`
--

INSERT INTO `logs_starpass` (`id`, `user_id`, `code`, `pays`, `palier`, `type`, `id_palier`, `datas`, `nb_tokens`) VALUES
(1, '54157da6e4e28', 'CN4EM25G', '', '', '', '', '', '100');

-- --------------------------------------------------------

--
-- Structure de la table `options`
--

CREATE TABLE `options` (
  `article_id` varchar(13) NOT NULL,
  `type` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `paypal`
--

CREATE TABLE `paypal` (
  `id` int(11) NOT NULL,
  `userid` varchar(13) NOT NULL,
  `offre` varchar(255) NOT NULL,
  `datas` varchar(255) NOT NULL,
  `transaction_number` varchar(255) NOT NULL,
  `informations` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `servers`
--

CREATE TABLE `servers` (
  `server_id` varchar(22) NOT NULL,
  `user_id` varchar(13) NOT NULL,
  `name` varchar(30) NOT NULL,
  `address` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `servers`
--

INSERT INTO `servers` (`server_id`, `user_id`, `name`, `address`) VALUES
('5415816f6a863720254421', '54157da6e4e28', 'Guiedo\'s server', '127.123.158.10'),
('54158694d0558273712611', '5415777599c6b', 'Anhackin\'s Server', 'www.anhackin.net');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` varchar(13) NOT NULL,
  `type` int(1) NOT NULL,
  `email` varchar(100) NOT NULL,
  `pseudo` varchar(30) NOT NULL,
  `password` varchar(40) NOT NULL,
  `signupdate` datetime NOT NULL,
  `lastsignin` datetime NOT NULL,
  `ip` varchar(15) NOT NULL,
  `pin` int(4) NOT NULL,
  `domain` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `users`
--

INSERT INTO `users` (`id`, `type`, `email`, `pseudo`, `password`, `signupdate`, `lastsignin`, `ip`, `pin`, `domain`) VALUES
('5415777599c6b', 2, 'quentinmalghem@gmail.com', 'anhackin', '29b6423e8233edfb69aa64a038f7a744c64a82b3', '2014-09-14 13:09:41', '2014-09-15 09:18:44', '91.183.123.116', 2412, ''),
('54157da6e4e28', 2, 'erucquoy@gmail.com', 'guiedo', 'a9b9c76be114a6c678368b4f6c3391b24cc33f71', '2014-09-14 13:36:06', '2017-07-16 00:29:57', '::1', 1234, '');

-- --------------------------------------------------------

--
-- Structure de la table `variables`
--

CREATE TABLE `variables` (
  `user_id` varchar(13) NOT NULL,
  `name` varchar(20) NOT NULL,
  `value` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`article_id`);

--
-- Index pour la table `funds`
--
ALTER TABLE `funds`
  ADD PRIMARY KEY (`user_id`);

--
-- Index pour la table `logs_starpass`
--
ALTER TABLE `logs_starpass`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `paypal`
--
ALTER TABLE `paypal`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `servers`
--
ALTER TABLE `servers`
  ADD PRIMARY KEY (`server_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD KEY `id` (`id`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `logs_starpass`
--
ALTER TABLE `logs_starpass`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT pour la table `paypal`
--
ALTER TABLE `paypal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
