-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 13 sep. 2022 à 21:53
-- Version du serveur : 10.4.22-MariaDB
-- Version de PHP : 8.0.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `syslogme`
--

-- --------------------------------------------------------

--
-- Structure de la table `t_token_type`
--

CREATE TABLE `t_token_type` (
  `pk_token_type` int(11) NOT NULL,
  `token_type` varchar(255) NOT NULL,
  PRIMARY KEY (`pk_token_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `t_token_type`
--

INSERT INTO `t_token_type` (`pk_token_type`, `token_type`) VALUES
(1, 'remember_me'),
(2, 'reset_password'),
(3, 'verify_account');

-- --------------------------------------------------------

--
-- Structure de la table `t_user`
--

CREATE TABLE `t_user` (
  `pk_user` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fk_user_role` int(11) NOT NULL DEFAULT 1,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`pk_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `t_user`
--

INSERT INTO `t_user` (`email`, `password`, `fk_user_role`, `verified_at`, `created_at`, `updated_at`, `deleted_at`, `last_login_at`) VALUES
('admin@test.ch', '$2y$10$wp2qGwkoM3Xw.gMadBayQ.g.jWuPaWWEf/5ojjQOVd0KmPQA3ZjmG', 2, '2022-04-26 12:21:21', '2022-04-26 12:21:21', '2022-08-06 00:08:31', NULL, '2022-08-06 00:08:31'),
('user@test.ch', '$2y$10$wp2qGwkoM3Xw.gMadBayQ.g.jWuPaWWEf/5ojjQOVd0KmPQA3ZjmG', 1, '2022-04-29 08:55:02', '2022-04-29 08:54:45', '2022-09-13 19:52:33', NULL, '2022-09-13 19:52:33');

-- --------------------------------------------------------

--
-- Structure de la table `t_user_role`
--

CREATE TABLE `t_user_role` (
  `pk_user_role` int(11) NOT NULL,
  `user_role` varchar(255) NOT NULL,
  PRIMARY KEY (`pk_user_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `t_user_role`
--

INSERT INTO `t_user_role` (`pk_user_role`, `user_role`) VALUES
(1, 'user'),
(2, 'admin');

-- --------------------------------------------------------

--
-- Structure de la table `t_user_token`
--

CREATE TABLE `t_user_token` (
  `pk_user_token` int(11) NOT NULL AUTO_INCREMENT,
  `fk_user_email` varchar(255) NOT NULL,
  `fk_token_type` int(11) NOT NULL,
  `selector` varchar(255) NOT NULL,
  `validator` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`pk_user_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `t_token_type`
--
ALTER TABLE `t_token_type`
  ADD UNIQUE KEY `pk_token_type` (`pk_token_type`),
  ADD UNIQUE KEY `token_type` (`token_type`);

--
-- Index pour la table `t_user`
--
ALTER TABLE `t_user`
  ADD UNIQUE KEY `pk_user` (`pk_user`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `t_user_fk_user_role` (`fk_user_role`);

--
-- Index pour la table `t_user_role`
--
ALTER TABLE `t_user_role`
  ADD UNIQUE KEY `pk_user_role` (`pk_user_role`),
  ADD UNIQUE KEY `user_role` (`user_role`);

--
-- Index pour la table `t_user_token`
--
ALTER TABLE `t_user_token`
  ADD UNIQUE KEY `pk_user_token` (`pk_user_token`),
  ADD KEY `t_user_token_fk_user_email` (`fk_user_email`),
  ADD KEY `t_user_token_fk_token_type` (`fk_token_type`);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `t_user`
--
ALTER TABLE `t_user`
  ADD CONSTRAINT `t_user_fk_user_role` FOREIGN KEY (`fk_user_role`) REFERENCES `t_user_role` (`pk_user_role`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `t_user_token`
--
ALTER TABLE `t_user_token`
  ADD CONSTRAINT `t_user_token_fk_token_type` FOREIGN KEY (`fk_token_type`) REFERENCES `t_token_type` (`pk_token_type`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `t_user_token_fk_user_email` FOREIGN KEY (`fk_user_email`) REFERENCES `t_user` (`email`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
