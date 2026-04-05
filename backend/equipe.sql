-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql-equipe.alwaysdata.net
-- Generation Time: Apr 05, 2026 at 11:41 AM
-- Server version: 11.4.9-MariaDB
-- PHP Version: 8.4.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `equipe_r401`
--

-- --------------------------------------------------------

--
-- Table structure for table `commentaire`
--

CREATE TABLE `commentaire` (
  `commentaire_id` int(11) NOT NULL,
  `contenu` varchar(200) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `joueur_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `commentaire`
--

INSERT INTO `commentaire` (`commentaire_id`, `contenu`, `date`, `joueur_id`) VALUES
(1, 'Le meilleur.', '2022-01-01', 10),
(7, 'Ceci est un test', '2026-03-25', 2),
(8, 'dhiqwudiuqwdq', '2026-03-28', 14),
(9, 'bien', '2026-03-28', 15),
(12, 'kskjqdkqdkqd', '2026-04-02', 2);

-- --------------------------------------------------------

--
-- Table structure for table `joueur`
--

CREATE TABLE `joueur` (
  `joueur_id` int(11) NOT NULL,
  `numero_licence` char(5) NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `taille` decimal(5,2) DEFAULT NULL,
  `poids` decimal(5,2) DEFAULT NULL,
  `statut` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `joueur`
--

INSERT INTO `joueur` (`joueur_id`, `numero_licence`, `nom`, `prenom`, `date_naissance`, `taille`, `poids`, `statut`) VALUES
(2, '00002', 'LARSSON', 'Martin (Rekkles)', '1996-09-20', 180.00, 75.00, 'ACTIF'),
(3, '00003', 'SUNG-WOONG', 'Bae (Bengi)', '1993-11-21', 172.00, 60.00, 'ACTIF'),
(4, '00004', 'BOYER', 'Paul (Soaz)', '1994-01-09', 170.00, 63.00, 'SUSPENDU'),
(5, '00005', 'KIM', 'Bora (Yellowstar)', '1992-02-15', 165.00, 60.00, 'BLESSE'),
(6, '00006', 'BJERG', 'Søren (Bjergsen)', '1996-02-21', 170.00, 60.00, 'ABSENT'),
(7, '00007', 'KYUNG-HO', 'Song (Smeb)', '1995-06-30', 165.00, 60.00, 'ACTIF'),
(8, '00008', 'HYEON-JOON', 'Choi (Doran)', '2000-07-22', 165.00, 60.00, 'ACTIF'),
(9, '00009', 'HYEON-JUN', 'Mun (Oner)', '2002-12-24', 165.00, 60.00, 'ACTIF'),
(10, '00010', 'SANG-HYEOK', 'Lee (Faker)', '1996-05-07', 165.00, 60.00, 'ACTIF'),
(11, '00011', 'SU-HWAN', 'Kim (Peyz)', '2005-12-05', 165.00, 60.00, 'ACTIF'),
(12, '00012', 'MIN-SEOK', 'RYU (Keria)', '2002-10-14', 165.00, 60.00, 'ACTIF'),
(14, '00050', 'Cristiano', 'Ronaldo', '2000-03-24', 189.00, 100.00, 'ABSENT'),
(15, '00060', 'Lionel', 'Messi', '2009-03-24', 190.00, 100.00, 'ACTIF');

-- --------------------------------------------------------

--
-- Table structure for table `participation`
--

CREATE TABLE `participation` (
  `participation_id` int(11) NOT NULL,
  `joueur_id` int(11) NOT NULL,
  `rencontre_id` int(11) DEFAULT NULL,
  `titulaire_ou_remplacant` varchar(20) DEFAULT NULL,
  `poste` varchar(20) DEFAULT NULL,
  `note_performance` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `participation`
--

INSERT INTO `participation` (`participation_id`, `joueur_id`, `rencontre_id`, `titulaire_ou_remplacant`, `poste`, `note_performance`) VALUES
(2, 4, 1, 'TITULAIRE', 'ADCARRY', 5),
(3, 5, 1, 'TITULAIRE', 'SUPPORT', 4),
(4, 6, 1, 'TITULAIRE', 'TOPLANE', 4),
(5, 7, 1, 'TITULAIRE', 'MIDLANE', 1),
(6, 6, 2, 'TITULAIRE', 'JUNGLE', 3),
(7, 7, 2, 'TITULAIRE', 'ADCARRY', 4),
(8, 8, 2, 'TITULAIRE', 'SUPPORT', 4),
(10, 2, 2, 'REMPLACANT', 'SUPPORT', 1),
(12, 2, 3, 'TITULAIRE', 'TOPLANE', 5),
(13, 3, 3, 'TITULAIRE', 'ADCARRY', 5),
(14, 5, 3, 'TITULAIRE', 'SUPPORT', 4),
(15, 6, 3, 'REMPLACANT', 'MIDLANE', 4),
(16, 7, 3, 'REMPLACANT', 'SUPPORT', 2),
(17, 10, 3, 'TITULAIRE', 'MIDLANE', 5),
(23, 2, 1, 'REMPLACANT', 'JUNGLE', 1);

-- --------------------------------------------------------

--
-- Table structure for table `rencontre`
--

CREATE TABLE `rencontre` (
  `rencontre_id` int(11) NOT NULL,
  `date_heure` datetime DEFAULT NULL,
  `equipe_adverse` varchar(50) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `lieu` varchar(20) DEFAULT NULL,
  `resultat` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rencontre`
--

INSERT INTO `rencontre` (`rencontre_id`, `date_heure`, `equipe_adverse`, `adresse`, `lieu`, `resultat`) VALUES
(1, '2018-01-01 10:00:00', 'G2', 'Mail des Drolets, 31320 Castanet-Tolosan', 'EXTERIEUR', 'DEFAITE'),
(2, '2019-05-02 11:00:00', 'GEN.G', '8 Pont de Zuera, 31520 Ramonville-Saint-Agne', 'DOMICILE', 'NUL'),
(3, '2020-06-26 12:00:00', 'FNATICS', '8 Pont de Zuera, 31520 Ramonville-Saint-Agne', 'DOMICILE', 'VICTOIRE'),
(6, '2026-05-01 00:00:00', 'FC Lyon', '1 rue du Stade, Lyon', 'DOMICILE', NULL),
(7, '2026-05-10 18:00:00', 'FC Lyon', '1 rue du Stade, Lyon', 'EXTERIEUR', NULL),
(14, '2026-03-28 15:49:00', 'Lyon', '17 Avenue Isae', 'DOMICILE', 'VICTOIRE'),
(15, '2026-03-28 18:17:00', 'kbkdjbkdmc kjs cj', ',canslkcnklscn', 'DOMICILE', 'NUL'),
(17, '2027-04-04 08:13:00', 'Kong Bap', 'Capitole', 'DOMICILE', NULL),
(18, '2027-05-04 08:18:00', 'Antica', 'Rome', 'EXTERIEUR', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`commentaire_id`),
  ADD KEY `fk_commentaire_joueur` (`joueur_id`);

--
-- Indexes for table `joueur`
--
ALTER TABLE `joueur`
  ADD PRIMARY KEY (`joueur_id`);

--
-- Indexes for table `participation`
--
ALTER TABLE `participation`
  ADD PRIMARY KEY (`participation_id`),
  ADD KEY `fk_participation_joueur` (`joueur_id`),
  ADD KEY `fk_participation_rencontre_id` (`rencontre_id`);

--
-- Indexes for table `rencontre`
--
ALTER TABLE `rencontre`
  ADD PRIMARY KEY (`rencontre_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `commentaire_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `joueur`
--
ALTER TABLE `joueur`
  MODIFY `joueur_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `participation`
--
ALTER TABLE `participation`
  MODIFY `participation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `rencontre`
--
ALTER TABLE `rencontre`
  MODIFY `rencontre_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `commentaire`
--
ALTER TABLE `commentaire`
  ADD CONSTRAINT `fk_commentaire_joueur` FOREIGN KEY (`joueur_id`) REFERENCES `joueur` (`joueur_id`);

--
-- Constraints for table `participation`
--
ALTER TABLE `participation`
  ADD CONSTRAINT `fk_participation_joueur` FOREIGN KEY (`joueur_id`) REFERENCES `joueur` (`joueur_id`),
  ADD CONSTRAINT `fk_participation_rencontre_id` FOREIGN KEY (`rencontre_id`) REFERENCES `rencontre` (`rencontre_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
