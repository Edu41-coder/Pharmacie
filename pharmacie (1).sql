-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-01-2025 a las 15:56:34
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pharmacie`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `a_commander`
--

CREATE TABLE `a_commander` (
  `produit_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `last_modified` timestamp NULL DEFAULT NULL,
  `last_load_time` timestamp NULL DEFAULT NULL,
  `last_mongo_load` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cheque`
--

CREATE TABLE `cheque` (
  `cheque_id` int(11) NOT NULL,
  `numero_cheque` varchar(50) NOT NULL,
  `client_id` int(11) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `etat` enum('en_attente','valide','refuse') NOT NULL DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `client`
--

CREATE TABLE `client` (
  `client_id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `numero_carte_vitale` varchar(15) DEFAULT NULL,
  `cheques_impayes` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `client`
--

INSERT INTO `client` (`client_id`, `nom`, `prenom`, `email`, `telephone`, `adresse`, `commentaire`, `numero_carte_vitale`, `cheques_impayes`) VALUES
(2, 'Hermosilla', 'Edu', 'hehermosilla@gmail.com', '0660388810', '11 rue de l\'olivier', 'dfffffffffffff', '250', 1),
(6, 'Hermosillitas', 'Eduardo', 'hehermosilla@gmail.com', '0660388811', '11 rue de l\'olivier', NULL, NULL, 0),
(9, 'g12', 'df', 'g12@gmail.com', NULL, NULL, NULL, '023', 0),
(10, 'jair', 'jai', 'hehe@gmail.com', NULL, NULL, NULL, NULL, 0),
(11, 'karl', 'gg', 'efn@gmail.com', NULL, NULL, NULL, '32323568', 0),
(12, 'fre', 're', 'he@lp.co', NULL, NULL, NULL, NULL, 0),
(13, 'Francis', 'Dupont', 'dupont@gmail.com', '0660384515', '11 rue de l&#039;olivier', NULL, NULL, 0),
(14, 'Paul', 'Renard', 'dupont@gmail.com', '4045623835', '45 rue de la republique', NULL, '475689531215478', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `commande`
--

CREATE TABLE `commande` (
  `commande_id` int(11) NOT NULL,
  `date_commande` datetime DEFAULT current_timestamp(),
  `statut` enum('En attente','En cours','Livrée','Annulée') NOT NULL DEFAULT 'En attente',
  `total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `commande_produit`
--

CREATE TABLE `commande_produit` (
  `commande_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventaire`
--

CREATE TABLE `inventaire` (
  `produit_id` int(11) NOT NULL,
  `stock` int(11) NOT NULL,
  `last_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordonnance`
--

CREATE TABLE `ordonnance` (
  `ordonnance_id` int(11) NOT NULL,
  `numero_ordonnance` varchar(50) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `numero_d'ordre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordonnance_produit`
--

CREATE TABLE `ordonnance_produit` (
  `ordonnance_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parametres`
--

CREATE TABLE `parametres` (
  `nom` varchar(100) NOT NULL,
  `valeur` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `parametres`
--

INSERT INTO `parametres` (`nom`, `valeur`) VALUES
('TVA', '5');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `produit`
--

CREATE TABLE `produit` (
  `produit_id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `prix_vente_ht` decimal(10,2) NOT NULL,
  `prescription` enum('oui','non') NOT NULL DEFAULT 'non',
  `taux_remboursement` tinyint(3) UNSIGNED DEFAULT NULL,
  `alerte` int(11) DEFAULT NULL,
  `declencher_alerte` enum('oui','non') NOT NULL DEFAULT 'non',
  `is_deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `produit`
--

INSERT INTO `produit` (`produit_id`, `nom`, `description`, `prix_vente_ht`, `prescription`, `taux_remboursement`, `alerte`, `declencher_alerte`, `is_deleted`) VALUES
(1, 'ACARBOSE BIOGARAN 100 mg ', 'comprimé sécable.\r\nACARBOSE BIOGARAN est un antidiabétique	', 13.87, 'oui', 65, NULL, 'non', 0),
(2, 'ACEBUTOLOL ARROW 400 mg', 'comprimé pelliculé sécable \r\nHypertension artérielle.\r\n Traitement au long cours après infarctus du myocarde', 10.74, 'oui', 65, NULL, 'oui', 0),
(4, 'ACECLOFENAC BIOGARAN 100 mg', 'comprimé pelliculé\r\nanti-inflammatoire non stéroïdien', 3.35, 'oui', 65, NULL, 'non', 0),
(5, 'ACETATE DE CYPROTERONE SANDOZ 100 mg', 'comprimé sécable.\r\ncancer de la prostate ;', 70.44, 'oui', 100, 20, 'oui', 0),
(6, 'ACETYLCYSTEINE EG 200 mg', 'poudre pour solution buvable en sachet-dose	poudre pour solution buvable	orale	Autorisation active	Procédure nationale	Commercialisée	26/12/2003			 EG LABO - LABORATOIRES EUROGENERICS	Non\r\n69896678	ACETYLCYSTEINE EG LABO CONSEIL 200 mg SANS SUCRE, poudre pour solution.\r\npour brochite', 5.82, 'non', NULL, 20, 'non', 0),
(7, 'ACETYLLEUCINE BIOGARAN 500 mg', 'comprimé.\r\ntraitement symptomatique de la crise vertigineuse.', 2.95, 'oui', 30, 20, 'non', 0),
(8, 'ACICLOVIR ALMUS 200 mg, ', 'traitement ou la prévention de certaines formes d’herpès', 8.18, 'oui', NULL, 65, 'oui', 0),
(9, 'ACICLOVIR ALMUS 5 %', 'Manifestations d’infections herpétiques génitales.\r\ncrème	cutanée.', 6.87, 'oui', 65, NULL, 'non', 0),
(10, 'ACIDE ACÉTYLSALICYLIQUE EG LABO CONSEIL 500 mg', 'indiqué en cas de douleurs d\'intensité légère à modérée et/ou de fièvre\r\ncomprimé', 3.04, 'non', NULL, NULL, 'oui', 0),
(11, 'ACIDE ALENDRONIQUE BIOGARAN 70 mg', 'prévient la perte osseuse qui survient chez les femmes ménopausées\r\ncomprimé.', 8.84, 'oui', 65, NULL, 'non', 0),
(12, 'ACIDE ALENDRONIQUE/VITAMINE D3 TEVA SANTE 70 mg/5600 UI,', 'comprimé\r\nboîte de 12', 12.84, 'oui', 65, NULL, 'non', 0),
(13, 'ACIDE FOLIQUE ARROW 5 mg', NULL, 1.33, 'oui', 65, NULL, 'non', 0),
(14, 'ACIDE FUSIDIQUE ARROW 2 %', 'crème	cutanée', 1.76, 'oui', 30, NULL, 'non', 0),
(15, 'ACIDE TIAPROFENIQUE ARROW 100 mg', 'comprimé sécable\r\nanti-inflammatoires non stéroïdiens', 3.92, 'non', NULL, NULL, 'non', 0),
(16, 'ACIDE URSODESOXYCHOLIQUE ARROW 250 mg', '\r\n\r\n\r\ncomprimé pelliculé.\r\ninflammation de la vésicule biliaire,\r\n\r\ninfection ou obstruction des voies biliaires.', 6.83, 'oui', 65, NULL, 'non', 0),
(17, 'DOLIPRANE 100 mg  poudre', 'poudre pour solution buvable en 12 sachet-dose', 2.45, 'non', 65, NULL, 'non', 0),
(18, 'DOLIPRANE 100 mg suppositoire', '10 suppositoire sécable', 2.35, 'non', 65, NULL, 'non', 0),
(19, 'DOLIPRANE 1000 mg comprimé', '8 comprimé', 2.18, 'non', 65, NULL, 'non', 0),
(20, 'DOLIPRANE 1000 mg, comprimé effervescent', 'comprimé effervescent sécable', 2.18, 'non', 65, NULL, 'non', 0),
(21, 'DOLIPRANE 1000 mg, gélule', '8 gélule	orale', 2.18, 'non', 65, NULL, 'non', 0),
(22, 'DOLIPRANE 1000 mg, poudre', 'poudre pour solution buvable en 8 sachet-dose', 2.25, 'non', 65, NULL, 'non', 0),
(23, 'DOLIPRANE 2,4 POUR CENT', ' suspension buvable', 2.50, 'non', 65, NULL, 'non', 0),
(24, 'DOLIPRANE 1000 mg suppositoire', '8suppositoire', 2.40, 'non', 65, NULL, 'non', 0),
(25, 'DOLIPRANELIQUIZ 1000 mg', 'suspension buvable en sachet édulcoré', 3.00, 'oui', 65, NULL, 'non', 0),
(26, 'DOLIPRANEVITAMINEC 500 mg/150 mg', '8 comprimé effervescent', 2.70, 'non', 65, NULL, 'non', 0),
(27, 'DOLIRHUME PARACETAMOL ET PSEUDOEPHEDRINE 500 mg/30 mg,', 'comprimé', 2.00, 'non', 65, NULL, 'non', 0),
(28, 'DORMICALM', 'comprimé enrobé\r\n Médicament traditionnel à base de plantes utilisé pour troubles du sommeil.', 7.84, 'non', NULL, NULL, 'non', 0),
(29, 'ACTIQ 1200 microgrammes', 'Stupéfiant,comprimé avec applicateur buccal\r\n traitement des accès douloureux paroxystiques', 18.17, 'oui', 65, NULL, 'non', 0),
(30, 'ACTISKENAN 10 mg', ' stupéfiant\r\ncomprimé orodispersible en boîte de 14 cp.', 2.26, 'oui', 65, NULL, 'non', 0),
(31, 'CTISOUFRE 4 mg/50 mg', 'états inflammatoires chroniques des voies respiratoires', 7.80, 'non', NULL, NULL, 'oui', 0),
(32, 'ACTONEL 75 mg', 'traitement de la maladie de Paget', 47.69, 'oui', 65, NULL, 'oui', 0),
(33, 'ADEMPAS 2,5 mg', NULL, 25.78, 'oui', 65, NULL, 'oui', 0),
(34, 'ADARTREL 2 mg', NULL, 24.00, 'oui', 65, NULL, 'non', 0),
(35, 'ADOPORT 5 mg', NULL, 29.70, 'oui', 65, NULL, 'non', 0),
(36, 'ALFUZOSINE EG L.P. 10 mg', 'traitement des troubles urinaires dus à un adénome de la prostate.', 9.78, 'oui', 65, NULL, 'non', 0),
(37, 'ALGINATE DE SODIUM/BICARBONATE DE SODIUM SANDOZ 500 mg/267 mg', 'suspension buvable en sachet\r\nreflux gastro-oesophagien ', 4.01, 'non', NULL, NULL, 'oui', 0),
(38, 'ALLOPURINOL BIOGARAN 300 mg', 'comprimé\r\nIl est utilisé pour traiter les excès d\'acide urique lorsqu\'ils sont responsables de goutte ou de calculs rénaux et pour prévenir ainsi ces maladies.', 3.06, 'oui', 65, NULL, 'non', 0),
(39, 'ALMOTRIPTAN TEVA 12,5 mg', 'comprimé pelliculé\r\nsoulager les maux de tête associés aux crises de migraine', 13.11, 'oui', 65, NULL, 'non', 0),
(40, 'ALPRAZOLAM ARROW 0,50 mg', 'comprimé sécable\r\nanxiolytique', 2.25, 'oui', 65, NULL, 'non', 0),
(41, 'AMBRISENTAN TEVA 10 mg', ' traiter l\'hypertension artérielle pulmonaire', 10.20, 'oui', 65, NULL, 'non', 0),
(42, 'AMBROXOL BIOGARAN CONSEIL 30 mg', 'comprimé sécable\r\nexpectorant', 3.99, 'non', NULL, NULL, 'non', 0),
(43, 'AMIODARONE BIOGARAN 200 mg', 'comprimé sécable\r\nantiarythmique', 8.22, 'oui', 65, NULL, 'non', 0),
(44, 'AMISULPRIDE BIOGARAN 200 mg', 'comprimé sécable\r\nantipsychotique', 37.22, 'oui', 65, NULL, 'non', 0),
(45, 'AMITRIPTYLINE SUBSTIPHARM 40 mg/mL', 'solution buvable en gouttes\r\nantidépresseur tricyclique', 4.17, 'oui', 65, NULL, 'non', 0),
(46, 'AMLODIPINE ARROW 10 mg', 'gélule	orale\r\nhypertension', 10.13, 'oui', 65, NULL, 'oui', 0),
(47, 'AMOROLFINE SUBSTIPHARM 5 %', 'vernis à ongles médicamenteux', 9.50, 'oui', 65, NULL, 'non', 0),
(48, 'AMOXICILLINE ARROW 500 mg', 'gélule	orale', 8.50, 'oui', 65, NULL, 'non', 0),
(49, 'AMOXICILLINE ARROW 250 mg/5 mL', 'poudre pour suspension buvable', 9.50, 'oui', 65, NULL, 'non', 0),
(50, 'AMOXICILLINE/ACIDE CLAVULANIQUE EG 500 mg/62,5 mg', 'comprimé pelliculé', 9.50, 'oui', 65, NULL, 'non', 0),
(51, 'AMOXICILLINE/ACIDE CLAVULANIQUE BIOGARAN 100 mg/12,50 mg', 'par ml NOURRISSONS, poudre pour suspension buvable en flacon', 7.80, 'oui', 65, NULL, 'non', 0),
(52, 'AMOXICILLINE/ACIDE CLAVULANIQUE TEVA 1 g/ 125 mg ADULTES', 'poudre pour suspension buvable en sachet-dose', 9.50, 'oui', 65, NULL, 'non', 0),
(53, 'ANAFRANIL 75 mg', 'comprimé pelliculé sécable', 15.78, 'oui', 65, NULL, 'non', 0),
(54, 'ANAGRELIDE SANDOZ 0,5 mg', 'gélule', 10.48, 'oui', 65, NULL, 'oui', 0),
(55, 'ANASTROZOLE EG 1 mg', ' Traitement du cancer du sein', 85.74, 'oui', 100, NULL, 'oui', 0),
(56, 'ANDROCUR 50 mg', 'comprimé sécable', 22.52, 'oui', 65, NULL, 'non', 0),
(57, 'ANTARENE 200 mg', 'comprimé pelliculé', 5.60, 'oui', 65, NULL, 'non', 0),
(58, 'APREPITANT ARROW 125 mg', ' gélule', 11.20, 'non', 65, NULL, 'non', 0),
(59, 'APROVEL 150 mg', 'comprimé pelliculé', 18.40, 'oui', 65, NULL, 'non', 0),
(60, 'AQUA MARINA BOIRON', 'degré de dilution compris entre 2CH et 30CH', 5.40, 'non', NULL, NULL, 'non', 0),
(61, 'ARALIA RACEMOSA LEHNING', 'degré de dilution compris entre 2CH et 30CH', 4.80, 'non', NULL, NULL, 'non', 0),
(62, 'ARANESP 150 microgrammes', 'solution injectable en seringue préremplie', 6.20, 'non', NULL, NULL, 'non', 0),
(63, 'ARBUTUS UNEDO BOIRON', 'degré de dilution compris entre 2CH et 30CH', 3.80, 'non', NULL, NULL, 'non', 0),
(64, 'ARCALION 200 mg', 'comprimé enrobé', 10.50, 'oui', 65, NULL, 'non', 0),
(65, 'ARGENTUM NITRICUM LEHNING', 'degré de dilution compris entre 2CH et 30CH ', 3.50, 'oui', NULL, NULL, 'non', 0),
(66, 'ARIPIPRAZOLE ALMUS 15 mg', 'comprimé', 3.40, 'non', NULL, NULL, 'non', 0),
(67, 'ARNICA MONTANA TEINTURE MERE BOIRON', 'liquide pour application cutanée', 6.30, 'non', NULL, NULL, 'non', 0),
(68, 'ARNICALME', 'comprimé orodispersible', 4.70, 'non', NULL, NULL, 'non', 0),
(69, 'ARNITROSIUM', 'comprimé sublingual', 3.85, 'non', NULL, NULL, 'non', 0),
(70, 'ARTHRODONT 1 POUR CENT', 'pâte gingivale', 5.80, 'non', NULL, NULL, 'non', 0),
(71, 'ASCABIOL 10 %', 'émulsion pour application cutanée', 3.90, 'oui', NULL, NULL, 'non', 0),
(72, 'ASPEGIC 500 mg', 'poudre pour solution buvable', 4.80, 'non', NULL, NULL, 'non', 0),
(73, 'ASPIRINE UPSA VITAMINEE C TAMPONNEE EFFERVESCENTE', 'comprimé effervescent', 5.71, 'oui', NULL, NULL, 'non', 0),
(74, 'ATAZANAVIR BIOGARAN 300 mg', 'gélule', 18.50, 'oui', 65, NULL, 'oui', 0),
(75, 'ATENOLOL ARROW 100 mg', 'comprimé pelliculé sécable', 10.75, 'oui', NULL, NULL, 'non', 0),
(76, 'AGOMELATINE BIOGARAN 25 mg', 'comprimé pelliculé', 5.50, 'oui', 65, NULL, 'non', 0),
(77, 'MERCRYL SOLUTION MOUSSANTE', 'solution pour application cutanée', 7.80, 'non', NULL, NULL, 'non', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role`
--

CREATE TABLE `role` (
  `role_id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `role`
--

INSERT INTO `role` (`role_id`, `nom`, `description`) VALUES
(1, 'admin', NULL),
(2, 'pharmacien', NULL),
(3, 'vendeur', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `user`
--

INSERT INTO `user` (`user_id`, `nom`, `prenom`, `email`, `password`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 'edu', 'edu', 'edu@gmail.com', '$2y$10$/HL5f7kHax8T7o0DuYofPOuQXP/uRWdJAxra69UpnQWNzqYmHQkBG', 2, '2024-08-06 07:54:05', '2024-09-02 23:35:42'),
(2, 'due', 'due', 'due@gmail.com', '$2y$10$5mVuC0hFSzO3HNum/Wi8d.7y6c4TfDHHxaaUoSJ4a1/FOR.ecacW.', 1, '2024-09-03 01:39:29', '2024-09-03 01:45:23'),
(4, 'deuse', 'deuu', 'deu@gmail.com', '$2y$10$FRrIzAezlCjdfN1g5tW2bOaNalzIRN4ZTUvB1d0G8NFh/0/m5Fafq', 1, '2024-09-03 02:15:29', '2024-10-31 03:06:38'),
(5, 'cae', 'cae', 'cae@gmail.com', '$2y$10$gRM0tobyqwO9VfIOywQ.ROvB.vS5PViAdpOfF5ydycH.MxU6RUJT2', 3, '2024-09-04 03:36:08', '2024-09-04 03:36:08'),
(8, 'klaus', 'klaus', 'klaus@gmail.com', '$2y$10$.b6ubzSvgioUn2B/8domgeacf.4/cXHm7mzyuNgvyswkLAn.YA4te', 1, '2024-09-08 11:03:22', '2024-10-23 00:21:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vente`
--

CREATE TABLE `vente` (
  `vente_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `montant` decimal(10,2) NOT NULL DEFAULT 0.00,
  `montant_regle` decimal(10,2) NOT NULL DEFAULT 0.00,
  `a_rembourser` decimal(10,2) NOT NULL DEFAULT 0.00,
  `commentaire` text DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vente_ordonnance`
--

CREATE TABLE `vente_ordonnance` (
  `vente_id` int(11) NOT NULL,
  `ordonnance_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vente_paiement`
--

CREATE TABLE `vente_paiement` (
  `paiement_id` int(11) NOT NULL,
  `vente_id` int(11) NOT NULL,
  `mode_paiement` set('especes','carte_bleu','cheque') DEFAULT NULL,
  `montant` decimal(10,2) NOT NULL,
  `numero_cheque` varchar(50) DEFAULT NULL,
  `date_paiement` timestamp NOT NULL DEFAULT current_timestamp(),
  `cheque_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vente_produit`
--

CREATE TABLE `vente_produit` (
  `vente_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `quantite` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `a_commander`
--
ALTER TABLE `a_commander`
  ADD PRIMARY KEY (`produit_id`);

--
-- Indices de la tabla `cheque`
--
ALTER TABLE `cheque`
  ADD PRIMARY KEY (`cheque_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indices de la tabla `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`client_id`);

--
-- Indices de la tabla `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`commande_id`);

--
-- Indices de la tabla `commande_produit`
--
ALTER TABLE `commande_produit`
  ADD PRIMARY KEY (`commande_id`,`produit_id`),
  ADD KEY `produit_id` (`produit_id`);

--
-- Indices de la tabla `inventaire`
--
ALTER TABLE `inventaire`
  ADD PRIMARY KEY (`produit_id`);

--
-- Indices de la tabla `ordonnance`
--
ALTER TABLE `ordonnance`
  ADD PRIMARY KEY (`ordonnance_id`);

--
-- Indices de la tabla `ordonnance_produit`
--
ALTER TABLE `ordonnance_produit`
  ADD PRIMARY KEY (`ordonnance_id`,`produit_id`),
  ADD KEY `produit_id` (`produit_id`);

--
-- Indices de la tabla `parametres`
--
ALTER TABLE `parametres`
  ADD PRIMARY KEY (`nom`);

--
-- Indices de la tabla `produit`
--
ALTER TABLE `produit`
  ADD PRIMARY KEY (`produit_id`);

--
-- Indices de la tabla `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `nom` (`nom`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indices de la tabla `vente`
--
ALTER TABLE `vente`
  ADD PRIMARY KEY (`vente_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `fk_vente_user` (`user_id`);

--
-- Indices de la tabla `vente_ordonnance`
--
ALTER TABLE `vente_ordonnance`
  ADD PRIMARY KEY (`vente_id`,`ordonnance_id`),
  ADD KEY `ordonnance_id` (`ordonnance_id`);

--
-- Indices de la tabla `vente_paiement`
--
ALTER TABLE `vente_paiement`
  ADD PRIMARY KEY (`paiement_id`),
  ADD KEY `fk_vente_paiement` (`vente_id`),
  ADD KEY `fk_vente_paiement_cheque` (`cheque_id`);

--
-- Indices de la tabla `vente_produit`
--
ALTER TABLE `vente_produit`
  ADD PRIMARY KEY (`vente_id`,`produit_id`),
  ADD KEY `produit_id` (`produit_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cheque`
--
ALTER TABLE `cheque`
  MODIFY `cheque_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `client`
--
ALTER TABLE `client`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `commande`
--
ALTER TABLE `commande`
  MODIFY `commande_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ordonnance`
--
ALTER TABLE `ordonnance`
  MODIFY `ordonnance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `produit`
--
ALTER TABLE `produit`
  MODIFY `produit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT de la tabla `role`
--
ALTER TABLE `role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `vente`
--
ALTER TABLE `vente`
  MODIFY `vente_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vente_paiement`
--
ALTER TABLE `vente_paiement`
  MODIFY `paiement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cheque`
--
ALTER TABLE `cheque`
  ADD CONSTRAINT `cheque_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`);

--
-- Filtros para la tabla `commande_produit`
--
ALTER TABLE `commande_produit`
  ADD CONSTRAINT `commande_produit_ibfk_1` FOREIGN KEY (`commande_id`) REFERENCES `commande` (`commande_id`),
  ADD CONSTRAINT `commande_produit_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`produit_id`);

--
-- Filtros para la tabla `inventaire`
--
ALTER TABLE `inventaire`
  ADD CONSTRAINT `inventaire_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`produit_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ordonnance_produit`
--
ALTER TABLE `ordonnance_produit`
  ADD CONSTRAINT `ordonnance_produit_ibfk_1` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnance` (`ordonnance_id`),
  ADD CONSTRAINT `ordonnance_produit_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`produit_id`);

--
-- Filtros para la tabla `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`);

--
-- Filtros para la tabla `vente`
--
ALTER TABLE `vente`
  ADD CONSTRAINT `fk_vente_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `vente_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `vente_ordonnance`
--
ALTER TABLE `vente_ordonnance`
  ADD CONSTRAINT `fk_vente_ordonnance_ordonnance` FOREIGN KEY (`ordonnance_id`) REFERENCES `ordonnance` (`ordonnance_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vente_ordonnance_vente` FOREIGN KEY (`vente_id`) REFERENCES `vente` (`vente_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vente_paiement`
--
ALTER TABLE `vente_paiement`
  ADD CONSTRAINT `fk_vente_paiement` FOREIGN KEY (`vente_id`) REFERENCES `vente` (`vente_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_vente_paiement_cheque` FOREIGN KEY (`cheque_id`) REFERENCES `cheque` (`cheque_id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `vente_produit`
--
ALTER TABLE `vente_produit`
  ADD CONSTRAINT `fk_vente_produit_produit` FOREIGN KEY (`produit_id`) REFERENCES `produit` (`produit_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_vente_produit_vente` FOREIGN KEY (`vente_id`) REFERENCES `vente` (`vente_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
