-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 07 sep. 2026 à 21:20
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `isstm_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin_notes`
--

CREATE TABLE `admin_notes` (
  `user_id` int(11) NOT NULL,
  `contenu` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `admin_notes`
--

INSERT INTO `admin_notes` (`user_id`, `contenu`, `updated_at`) VALUES
(3, '', '2026-09-07 08:51:13');

-- --------------------------------------------------------

--
-- Structure de la table `amis_demandes`
--

CREATE TABLE `amis_demandes` (
  `id` int(11) NOT NULL,
  `demandeur_id` int(11) NOT NULL,
  `destinataire_id` int(11) NOT NULL,
  `statut` enum('en_attente','acceptee','refusee') NOT NULL DEFAULT 'en_attente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `amis_demandes`
--

INSERT INTO `amis_demandes` (`id`, `demandeur_id`, `destinataire_id`, `statut`, `created_at`, `updated_at`) VALUES
(2, 23, 10, 'acceptee', '2026-08-26 13:42:42', '2026-08-26 16:43:30'),
(5, 23, 14, 'en_attente', '2026-08-28 20:27:40', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `campus_blocs`
--

CREATE TABLE `campus_blocs` (
  `id` int(11) NOT NULL,
  `bloc_key` varchar(50) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `signification` text DEFAULT NULL,
  `fondation` varchar(255) DEFAULT NULL,
  `fondateurs` varchar(255) DEFAULT NULL,
  `slogan` varchar(255) DEFAULT NULL,
  `objectifs` text DEFAULT NULL,
  `activites` text DEFAULT NULL,
  `danse` varchar(255) DEFAULT NULL,
  `mampiavaka` varchar(255) DEFAULT NULL,
  `images` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `campus_blocs`
--

INSERT INTO `campus_blocs` (`id`, `bloc_key`, `nom`, `signification`, `fondation`, `fondateurs`, `slogan`, `objectifs`, `activites`, `danse`, `mampiavaka`, `images`, `created_at`) VALUES
(1, 'mafami', 'MAFAMI', 'Mpianatra, mpiasa, mpandraharaha Avy amin’ny Faritanin’ny Antananarivo MIray', 'vers 1978', 'Mr Julien', NULL, 'Fanampiana ny mpianatra avy any Antananarivo mandrato fianarana aty Mahajanga\nFifanampianana amin’ny fitadiavana asa\nFifandraisana maharitra eo amin’ny samy mpianatra', 'AG (Assemblée Générale)\nPot de contact\nRéception de bloc\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions)', NULL, NULL, 'images/campus/CAMPUS-UNIVERSITAIRE/MAFAMI1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/MAFAMI2.jpg,images/campus/CAMPUS-UNIVERSITAIRE/MAFAMI3.jpg,images/campus/CAMPUS-UNIVERSITAIRE/MAFAMI4.jpg', '2026-08-12 22:05:24'),
(2, 'set', 'SET', 'Solidarité des Etudiants venant de Tsaratanana', '14 juillet 2009', 'Mr Nantenaina Ohatra, Mr Mano et Mr Bien Rangé', 'Firaisankina Fahendrena Fampandrosoana', 'Fanampiana ny mpianatra avy any Tsaratanana\r\nFifandraisana maharitra eo amin’ny samy mpianatra', 'AG (Assemblée Générale)\r\nRéception de bloc/Novices (Miss et Mister)\r\nActivités sportives (Match inter-promotions)', 'Tsinjaka Sakalava', 'Volamena', 'images/campus/CAMPUS-UNIVERSITAIRE/SET1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/SET2.jpg', '2026-08-12 22:05:24'),
(3, 'aeom', 'AEOM', 'Solidarité des Etudiants Originaire de Mampikony', 'vers 1995', 'Les membres fondent l’association', NULL, 'Fanampiana ny mpianatra\nFifanampianana amin’ny fiainana sy ny fahasarotan’ny fiainana\nFifandraisana maharitra', 'AG (Assemblée Générale)\nBonne année/Pacques\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions, Match SEUM)', NULL, 'famokarana Tongolo (Capital d’Oignons)', 'images/campus/CAMPUS-UNIVERSITAIRE/AEOM1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/AEPM2.jpg', '2026-08-12 22:05:24'),
(4, 'ueffort', 'UEFFORT', 'Union des Etudiants venant de l’Ex-prefecture de FORT-dauphin', 'vers 2002', 'Mr Tsitomotsy Arsène, Dr Hery, Ministre Pobert', NULL, 'Fanampiana ny mpianatra avy lavitra indrindra avy any Fort Dauphin\nFifanampianana amin’ny fitadiavana asa\nFifandraisana eo amin’ny samy mpianatra\nFanampianana ireo mpianatra zandry', 'Pot de contact\nHavoria Atiké\nBonne année\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions, SEUM)', 'Androy (Ganaiké), Taolagnaro (Mangaliba), Bar (Karitaky)', NULL, 'images/campus/CAMPUS-UNIVERSITAIRE/UEEFORT1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/UEEFORT2.jpg,images/campus/CAMPUS-UNIVERSITAIRE/UEEFORT4.jpg', '2026-08-12 22:05:24'),
(5, 'sebaum', 'SEBAUM', 'Solidarité des Etudiants de Befandriana nord Associé à l’Université de Mahajanga', 'vers 1987', 'Les membres', NULL, 'Mampitambatra ny faritra Befandriana avaratra\nFifanampianana amin’ny fiainana sy hanamora ny fiainana\nFifandraisana maharitra eo amin’ny samy mpianatra', 'AG (Assemblée Générale)\nReception porte\nRéception de bloc\nReception SERS\nRéception des Novices (Miss et Mister,dance traditionnelle,moderne)\nActivités sportives (Match inter-promotions,SEUM )', 'Bawejy, Antosy, Malesa', NULL, 'images/campus/CAMPUS-UNIVERSITAIRE/SEBAUM1.jpg', '2026-08-12 22:05:24'),
(6, 'aevafi', 'AEVAFI', 'Association des Etudiants venant de la region de VAtovavy FItovinany', 'vers 1999', 'Mr IBRAHIM', NULL, 'Fifanampianana eo amin’ny fianarana sy ny fahasarotan’ny fiainana\nFiraisankina\nFifandraisana', 'AG (Assemblée Générale)\nReception de bloc(+sortie)\nRéception Grand Sud\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions,SEUM, Basket et volley, Moraingy)', 'Batselaka, Sadebaka, Dombolo, Basesa', NULL, 'images/campus/CAMPUS-UNIVERSITAIRE/AEVAFI1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/AEVAFI2.jpg,images/campus/CAMPUS-UNIVERSITAIRE/AEVAFI3.jpg', '2026-08-12 22:05:24'),
(7, 'aefum', 'AEFUM', 'Association des Etudiants Fenarois de l’ Université de Mahajanga', 'vers 1983', 'President de la Cadre Betsileo', NULL, 'Fanampiana ny mpianatra avy lavitra\nFifanampianana eo amin’ny fianarana\nFifandraisana sy fanamorana ny fiainana', 'AG (Assemblée Générale)\nRéception des Novices (Miss et Mister)\nActivités sportives foot et basket (Match inter-promotions, inter-associations)', 'Dihy Betsileo(KIDODO)', NULL, 'images/campus/CAMPUS-UNIVERSITAIRE/AEFUM1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/AEFUM2.jpg', '2026-08-12 22:05:24'),
(8, 'fimpafato', 'FIMPAFATO', 'FIkambanan’ny MPianatra Avy amin’ny FAritanin’ny TOliara', 'vers 1986', 'Mr Julien', NULL, 'Fanampiana ny mpianatra avy amin’ny faritra Atsimon’ny Nosy\nHamoaka olom-banona ho reharehan’ny faritra Atsimo\nFifandraisana maharitra', 'AG (Assemblée Générale)\nRepas social\nOuverture de cellule de prière\nRéception des Novices (Miss et Mister)\nActivités sportives foot et basket (Match inter-promotions)', 'TSAPIKY', NULL, 'images/campus/CAMPUS-UNIVERSITAIRE/FIMPAFATO.jpg', '2026-08-12 22:05:24'),
(9, 'seora', 'SEORA', 'Solidarité des Etudiants Originaire d’Antsohihy', 'vers 1987', 'les membres qui fondent d’associations', NULL, 'Fanampiana ny mpianatra amin’ny fianarana\nFitadiavana nahombiazana\nFifandraisana maharitra eo amin’ny samy mpianatra eto amin’ny université Mahajanga', 'AG (Assemblée Générale)\nBonne année/Sortie de pacques\nRéception de Dinosaure\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions 2fois pas ans)', 'Antosy, Bawejy, Malesa, Salesa, Alalaosy', 'Drakaka(Crabes)', 'images/campus/CAMPUS-UNIVERSITAIRE/SEORA1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/SEORA2.jpg', '2026-08-12 22:05:24'),
(10, 'semen', 'SEMEN', 'Solidarité des Etudiants venant de la region de MENabe', 'vers 2004', 'Dr Rhejaly', 'Filongoa-Faihira-Faheza', 'Fanampiana ny mpianatra avy amin’ny faritra Menabe\nFifanampianana amin’ny fitadiavana asa\nFifandraisana maharitra', 'AG (Assemblée Générale)\nRéception des Novices (Miss et Mister)\nArrosage 2ème année(L2)\nActivités sportives (Match inter-promotions,SEUM)', 'KILALAKY, Tsinjaky DABA', 'Fambolena Tsaramaso', 'images/campus/CAMPUS-UNIVERSITAIRE/SEMEN1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/SEMEN2.jpg', '2026-08-12 22:05:24'),
(11, 'fimtama', 'FIMTAMA', 'FIkambanan’ny Mpianatra TAtsinanana eto MAhajanga', '10 Mai 1980', 'Dr Talata Michel', 'Tatsinagnana uni Ensemble, nous pouvons réussir', 'Fampivondronana ny mpianatra avy any Toamasina\nFifanampianana amin’ny fiainana\nFifandraisana maharitra eo amin’ny samy mpianatra', 'Pot de contact\nAG (Assemblée Générale)/Anniversaire\nBonne année, excursion\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions, par porte)', 'BASESA, WATSAWATSA', 'Café, jirofo,Vanille', 'images/campus/CAMPUS-UNIVERSITAIRE/FIMTAMA.jpg', '2026-08-12 22:05:24'),
(12, 'uenam', 'UENAM', 'Union des Etudiants Natifs d’Amoron’i Mania', '11 Mai 2011, officialisé en 2012', 'Dr Bernard', NULL, 'Fanampiana ny mpianatra ahatagny antanana\nFifanampianana amin’ny fitadiavana asa\nFifandraisana maharitra eo amin’ny samy mpianatra\nFanomezana lanja ny agnaran-dRay', 'AG (Assemblée Générale)\nPot de contact\nRéception des Novices (Miss et Mister)\nSortie de Promotion\nActivités sportives foot et basket (Match inter-promotions)', 'KIDODO', 'SAVIKA', '', '2026-08-12 22:05:24'),
(13, 'aesa', 'AESA', 'Association des Etudiants Solidaire d’ Ambatoboeny', 'vers 2008', 'Mr Andry Rivo', NULL, 'Fanampiana ny mpianatra\nFifanampianana amin’ny fiainana andavanandro\nFifandraisana sy fampianarana ireo zandry', 'AG (Assemblée Générale)\nGrande soirée\nConcours Miss et Mister/Dance\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions, par filière, SEUM, par association)', 'Tsinjaky Sakalava, Base 15', 'fambolena Lojy sy Voanjo', 'images/campus/CAMPUS-UNIVERSITAIRE/AESA1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/AESA2.jpg', '2026-08-12 22:05:24'),
(14, 'ajeum', 'AJEUM', 'Association des Jeunes Universitaire venant de Marovoay', 'vers 2011', 'les membres de l’association', NULL, 'Fanampiana eo amin’ny lafiny fianarana\nFanosehana ireo zandry mba ho tafita\nFiraisankinan’ny mpianatra avy any Marovoay', 'AG (Assemblée Générale)\nConcours de dance\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions,SEUM)', 'Dihy Sakalava', 'Fambolem-bary(Capital du riz)', 'images/campus/CAMPUS-UNIVERSITAIRE/AJEUM1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/AJEUM2.jpg,images/campus/CAMPUS-UNIVERSITAIRE/AJEUM3.jpg,images/campus/CAMPUS-UNIVERSITAIRE/AJEUM4.jpg,images/campus/CAMPUS-UNIVERSITAIRE/AJEUM5.jpg', '2026-08-12 22:05:24'),
(15, 'senum', 'SENUM', 'Solidarité des Etudiants venant de Nosy-be à l’Université de Mahajanga', 'vers 2005', 'Dr Bachiro', NULL, 'Fifanampianana eo amin’ny samy mpianatra\nFanamorana ny fiainan’ny mpianatra\nFifandraisana maharitra', 'AG (Assemblée Générale)\nConcours Miss et Mister\nRéception de bloc\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions, inter-associations)', 'SALEGY, TSOTSOBE, DIAMBOAY, MOGOJO', 'Ylang-Ylang, KATAKATA, Vanille', 'images/campus/CAMPUS-UNIVERSITAIRE/SENUM1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/SENUM2.jpg,images/campus/CAMPUS-UNIVERSITAIRE/SENUM3.jpg', '2026-08-12 22:05:24'),
(16, 'triangle', 'TRIANGLE VERT', 'Mpianatra, mpiasa, mpandraharaha Avy amin’ny Faritanin’ny Antananarivo MIray', 'vers 1993', 'Pr Jean Ralay', 'Vagnon’Aina, Vagnon-Karena', 'Fampitambarana ny mpianatra zanaka SAVA\nFifanampianana amin’ny fiainana andavanandro\nFifandraisana maharitra', 'AG (Assemblée Générale)\nKAYAMBA\nRéception de bloc\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions, inter-associations, par porte)', 'SALEGY', NULL, 'images/campus/CAMPUS-UNIVERSITAIRE/TRIANGLE VERT1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/TRIANGLE VERT2.jpg', '2026-08-12 22:05:24'),
(17, 'uesdiana', 'UES-DIANA', 'Union des Etudiants Solidaire de DIANA', 'vers 2015', 'Mr RABEARINY Eduord Chrissant', NULL, 'Fanomezana lanja ny fianarana\nFifanampianana amin’ny fitadiavana asa\nFiraisankinan’ny mpianatra avy amin’ny faritra DIANA', 'AG (Assemblée Générale)\nRéception des Novices (Miss et Mister, dance traditionnelle)\nActivités sportives (Match inter-promotions, inter-association, SEUM)', 'TROTROBE', NULL, 'images/campus/CAMPUS-UNIVERSITAIRE/UES-DIANA1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/UES-DIANA2.jpg', '2026-08-12 22:05:24'),
(18, 'mampiami', 'MAMPIAMI', 'MAromangagn’e MPIanatry Antesaka MIraindraiky / MAromangagn’e MPIanatry Atsimo-antsinanana MIraindraiky', '22 juillet 2006', 'Mr Lahijosy Mirido Raelson', NULL, 'Rassembler et Unifier toute les etudiants venant de la region Sud Est\nRaffermir la solidarité entre les Diaspora', 'AG (Assemblée Générale)\nPot de contact\nCours de langue\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions, Match Grand Sud)', NULL, 'Jirofo, Café, Letchi', 'images/campus/CAMPUS-UNIVERSITAIRE/MAMPIAMI1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/MAMPIAMI2.jpg,images/campus/CAMPUS-UNIVERSITAIRE/MAMPIAMI3.jpg', '2026-08-12 22:05:24'),
(19, 'aina', 'AINA', 'Association des Intellectuelles Natifs d Andapa', '14 Août 2014', 'Mr Patrick Jannet', 'l’union fait la force', 'Fanampiana ny mpianatra avy any Andapa\nFifanampianana eo amin’ny fiainana andavanandro\nFampifandraisana ny mpianatra sy ny firaisankina maharitra', 'AG (Assemblée Générale)\nConcours de dance, Karaoké\nRéception des Novices (Miss et Mister)\nActivités sportives foot et basketball (Match inter-promotions,SEUM, Inter-associations)', NULL, NULL, 'images/campus/CAMPUS-UNIVERSITAIRE/AINA1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/AINA2.jpg', '2026-08-12 22:05:24'),
(20, 'uemar', 'UEMar', 'Union des Etudiants Marambitsy', 'vers 2007', 'Les membres qui fondent l’association', 'Filongoa Maiva', 'Fifanampianana amin’ny fahasarotan’ny fiainana\nFampiraisana ny faritra mitsinjo sy soalala\nFiraisankinan’ny mpianatra', 'AG (Assemblée Générale)\nConcours Miss et Mister(membres)\nKaraoké\nMoraingy\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions, SEUM, inter-filière, par Commune, par porte)', 'MINOTSIKY, PEKA-TRATRA, TROMBA-BIBY, TROMBA', 'Fambolena Katsaka sy Vary', 'images/campus/CAMPUS-UNIVERSITAIRE/UEMar.jpg', '2026-08-12 22:05:24'),
(21, 'sema', 'SEMA', 'Solidarité des Etudiants venant de MAevatanana', 'vers 2011', 'Mr Jules Man', NULL, 'Fampitambarana ny mpianatra avy any Maevatanana mandrato fianarana eto mahajanga\nFifanampianana amin’ny fitadiavana asa\nFiraisankina maharitra', 'AG (Assemblée Générale)\nPot de contact\nRéception de bloc\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions, inter-associations)', 'Dance LAMACHINE', 'Volamena sy ny fambolena Black-eyes', 'images/campus/CAMPUS-UNIVERSITAIRE/SEMA1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/SEMA2.jpg', '2026-08-12 22:05:24'),
(22, 'tampama', 'TAMPAMA', 'Tamingan’ Analalava Mandalim-PAhaizana eto MAhajanga', 'vers 1986', 'Les membres qui l’ont fondée', NULL, 'Fampitambarana ny mpianatra avy any Maevatanana mandrato fianarana eto mahajanga\nFifanampianana amin’ny fitadiavana asa\nFiraisankina maharitra', 'AG (Assemblée Générale)\nRéception de Dinosaure\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions, inter-associations)', 'Antosy, Bawejy, Malesa', NULL, '', '2026-08-12 22:05:24'),
(23, 'aesva', 'AESVA', 'Association des Etudiants Solidaires de VAkinankaratra', 'vers 2016', 'Pr RANDRIAMBOLOLONA Théophile et Mr Solofo', NULL, 'Fampivondronana ny mpianatra avy any amin’ny faritra vakinankaratra\nFifanampianana amin’ny fiainana andavanandro\nFiraisankina maharitra sy fifanampiana eo amin’ny lafiny fianarana', 'AG (Assemblée Générale)\nPot de contact\nRéception des Novices (Miss et Mister)\nSortie\nActivités sportives (Match inter-promotions, inter-associations, SEUM)', 'Dihy soroka , Velatanana', 'Legumes', 'images/campus/CAMPUS-UNIVERSITAIRE/AESVA1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/AESVA2.jpg,images/campus/CAMPUS-UNIVERSITAIRE/AESVA3.jpg', '2026-08-12 22:05:24'),
(24, 'seorm', 'SEORM', 'Solidarité des Etudiants Originaire de la Region Melaky', 'vers 2009', '… ???', NULL, 'Fanamorana ny fiainan’ny mpianatra\nFifanampianana eo amin’ny fianarana\nFiraisankina maharitra\nFanamaivanana ny vesatry ny fiainana', 'AG (Assemblée Générale)\nBonne année ensemble\nRéception des Novices (Miss et Mister)\nSortie ensemble\nActivités sportives (Match inter-promotions, inter-associations, Grand Sud, SEUM)', 'Tsinjaky Daba, Kilalaky, Degoly', 'Fambolena Côcô(voanio)', 'images/campus/CAMPUS-UNIVERSITAIRE/SEOREM1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/SEOREM2.jpg', '2026-08-12 22:05:24'),
(25, 'mgama', 'MGAMA', 'Mpianatra Gaonan’Alaotra MAngoro', '05 novembre 1981', 'Mr Rivo', NULL, 'Hampiray ny mpianatra avy any Antsihanaka\nFifanampianana eo amin’ny fiainana andavanandro\nFiraisankina maharitra', 'AG (Assemblée Générale)\nKaraoké\nConcours de dance\nConcours Miss et Mister\nRéception des Novices (Miss et Mister, dance traditionnelle)\nActivités sportives (Match inter-promotions, inter-associations, SEUM)', NULL, 'Fambolem-bary', 'images/campus/CAMPUS-UNIVERSITAIRE/MGAMA.jpg', '2026-08-12 22:05:24'),
(26, 'fimpama', 'FIMPAMA', 'FIkambanan’ny MPianatra avy avaratra andrefan’Antsiranana eto MAhajanga', 'vers 1977', 'Membres', NULL, 'Firaisankina\nFahaiza-miaina\nFanampiana ireo mpianatra avy lavitra', 'AG (Assemblée Générale)\nConcours de dance(culture nord)\nConcours Miss et Mister\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions, inter-associations,SEUM)', 'MIGODRO, TROMBA', 'Fambolena CACAO', 'images/campus/CAMPUS-UNIVERSITAIRE/BLOCXXVI1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/BLOCXXVI2.jpg,images/campus/CAMPUS-UNIVERSITAIRE/BLOCXXVI3.jpg', '2026-08-12 22:05:24'),
(27, 'seob', 'SEOB', 'Solidarité des Etudiants Originaires de Bealanana', 'vers 2000', 'Zokibee miray', 'Aza adigna ny raha nialagna antanagna, ny fianarana', 'Fifanampianana eo amin’ny samy mpianatra\nFifanampianana amin’ny fitadiavana asa sy ny fiainana andavanandro\nFiraisankina maharitra', 'AG (Assemblée Générale)\nConcours Miss et Mister\nPot de porte\nRéception de bloc\nRéception SERS\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions, inter-associations)', 'Bawejy', 'DRAKIDRAKY fotsy maso', 'images/campus/CAMPUS-UNIVERSITAIRE/SEOB.jpg', '2026-08-12 22:05:24'),
(28, 'aep', 'AEP', 'Association des Etudiants de Port-bergé', 'vers 2003', 'Les membres', NULL, 'Fampitambarana ny mpianatra avy any Maevatanana mandrato fianarana eto mahajanga\nFifanampianana amin’ny fitadiavana asa\nFiraisankina maharitra', 'AG (Assemblée Générale)\nRéception de Dinosaure\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions, inter-associations,SEUM)', 'BAWEJY, MALESY, LALAOSY, ANTOSY', 'Black-Eyes', 'images/campus/CAMPUS-UNIVERSITAIRE/AEP1.jpg,images/campus/CAMPUS-UNIVERSITAIRE/AEP2.jpg', '2026-08-12 22:05:24'),
(29, 'fmaatm', 'FMAATM', 'Fikambanan’ny Mpianatra Ambaratonga Ambony Terak’i Mandritsara', '02 fevrier 1987', 'Pr RAMAROSON sy Pr VITAMARINA', NULL, 'Fampitambarana ny mpianatra avy any Mandritsara\nFifanampianana amin’ny fiainana andavanandro\nFampirisihana ny mpianatra ho tafita lavitra\nFiraisankina maharitra eo amin’ny mpikambana', 'AG (Assemblée Générale)\nDance culturelle par promotion\nConcours Miss et Mister\nRéception de porte (chaque porte l’organise)\nRéception de Dinosaure(+4ans=dinosaure)\nRéception des Novices (Miss et Mister)\nActivités sportives (Match inter-promotions(2fois par ans), inter-associations)', 'SESIBE(danse ensemble), MALESA', 'Voasary', '', '2026-08-12 22:05:24'),
(30, 'semavi', 'SEMAVI', 'Solidarité des Etudiants MAhajanga VIlle', '19 novembre 1996', 'Pr Klain sy Pr William', 'Fanabeazana-Fahendrena-Firaisankina', 'Fampitambarana ny zanak’i Mahajanga\nFifanampianana amin’ny fianarana\nFiraisankina maharitra', 'AG (Assemblée Générale)\nRéception de bloc\nConcours Miss et Mister\nArtistique (fangalana artiste)\nRéception des Novices (Miss et Mister)\nSortie ensemble\nActivités sportives (Match inter-promotions, inter-associations, basket)', 'Dance Sakalava Boina', NULL, '', '2026-08-12 22:05:24');

-- --------------------------------------------------------

--
-- Structure de la table `communaute_comments`
--

CREATE TABLE `communaute_comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `communaute_comments`
--

INSERT INTO `communaute_comments` (`id`, `post_id`, `parent_id`, `user_id`, `contenu`, `created_at`, `updated_at`) VALUES
(7, 6, NULL, 3, 'opop', '2026-08-25 23:49:30', NULL),
(8, 6, NULL, 3, 'opop', '2026-08-25 23:49:32', NULL),
(10, 6, 7, 23, 'nk', '2026-08-25 23:59:39', NULL),
(11, 6, 7, 23, 'nknjk', '2026-08-25 23:59:41', NULL),
(12, 6, 7, 23, 'nkj', '2026-08-25 23:59:44', NULL),
(13, 6, 7, 23, 'nknjk', '2026-08-25 23:59:48', NULL),
(42, 6, 8, 23, 'll', '2026-08-26 14:11:54', NULL),
(43, 6, 8, 23, 'llljkl', '2026-08-26 14:11:57', NULL),
(44, 6, 8, 23, 'jkljkl', '2026-08-26 14:11:59', NULL),
(45, 6, 8, 23, 'jlkj', '2026-08-26 14:12:02', NULL),
(46, 6, NULL, 23, 'jlkjl', '2026-08-26 14:12:04', NULL),
(52, 17, NULL, 3, 'gjhgj', '2026-08-28 20:36:07', NULL),
(53, 17, NULL, 3, 'hgj', '2026-08-28 20:36:07', NULL),
(54, 17, NULL, 3, 'gj', '2026-08-28 20:36:08', NULL),
(55, 17, NULL, 3, 'ghj', '2026-08-28 20:36:08', NULL),
(56, 17, NULL, 3, 'hgj', '2026-08-28 20:36:09', NULL),
(57, 17, NULL, 3, 'hgj', '2026-08-28 20:36:09', NULL),
(58, 17, NULL, 3, 'ghj', '2026-08-28 20:36:09', NULL),
(59, 17, NULL, 3, 'hgj', '2026-08-28 20:36:09', NULL),
(61, 17, NULL, 3, 'fdsff', '2026-08-28 20:37:46', NULL),
(62, 17, NULL, 3, 'sdf', '2026-08-28 20:37:46', NULL),
(63, 17, NULL, 3, 'sdf', '2026-08-28 20:37:47', NULL),
(64, 17, NULL, 3, 'ds', '2026-08-28 20:37:47', NULL),
(65, 17, NULL, 3, 'f', '2026-08-28 20:37:47', NULL),
(66, 17, NULL, 3, 'd', '2026-08-28 20:37:47', NULL),
(67, 17, NULL, 3, 'sdff', '2026-08-28 20:37:48', NULL),
(68, 17, NULL, 3, 'fin', '2026-08-28 20:37:51', NULL),
(69, 17, 67, 3, 'sqd', '2026-08-28 20:37:54', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `communaute_notifications`
--

CREATE TABLE `communaute_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('nouvelle_publication','reponse_commentaire') NOT NULL,
  `post_id` int(11) NOT NULL,
  `comment_id` int(11) DEFAULT NULL,
  `actor_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `communaute_notifications`
--

INSERT INTO `communaute_notifications` (`id`, `user_id`, `type`, `post_id`, `comment_id`, `actor_id`, `is_read`, `created_at`) VALUES
(62, 3, 'reponse_commentaire', 6, 8, 23, 1, '2026-08-26 14:11:54'),
(63, 3, 'reponse_commentaire', 6, 8, 23, 1, '2026-08-26 14:11:57'),
(64, 3, 'reponse_commentaire', 6, 8, 23, 1, '2026-08-26 14:11:59'),
(65, 3, 'reponse_commentaire', 6, 8, 23, 1, '2026-08-26 14:12:02'),
(71, 3, 'nouvelle_publication', 17, NULL, 23, 1, '2026-08-28 20:16:15'),
(72, 10, 'nouvelle_publication', 17, NULL, 23, 1, '2026-08-28 20:16:15'),
(73, 14, 'nouvelle_publication', 17, NULL, 23, 0, '2026-08-28 20:16:15'),
(74, 63, 'nouvelle_publication', 17, NULL, 23, 0, '2026-08-28 20:16:15');

-- --------------------------------------------------------

--
-- Structure de la table `communaute_posts`
--

CREATE TABLE `communaute_posts` (
  `id` int(11) NOT NULL,
  `auteur_id` int(11) NOT NULL,
  `type` enum('actualite','resultat','emploi_du_temps','examen','media','autre') NOT NULL DEFAULT 'autre',
  `contenu` text DEFAULT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `media_type` enum('image','video','pdf','none') NOT NULL DEFAULT 'none',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `communaute_posts`
--

INSERT INTO `communaute_posts` (`id`, `auteur_id`, `type`, `contenu`, `media_path`, `media_type`, `created_at`, `updated_at`) VALUES
(5, 3, 'autre', 'opopop', 'uploads/communaute_6a8e29ec828b2.jpg', 'image', '2026-08-25 23:49:00', NULL),
(6, 3, 'actualite', 'iiiii', 'uploads/communaute_6a8e29fbcfd66.png', 'image', '2026-08-25 23:49:15', NULL),
(9, 3, 'media', 'popopopopo', NULL, 'none', '2026-08-26 00:27:40', '2026-08-26 03:28:57'),
(17, 23, 'autre', 'TSY MIANATRA RAHAMPITSO', NULL, 'none', '2026-08-28 20:16:15', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `communaute_post_media`
--

CREATE TABLE `communaute_post_media` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `media_path` varchar(255) NOT NULL,
  `media_type` enum('image','video','pdf') NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `communaute_post_media`
--

INSERT INTO `communaute_post_media` (`id`, `post_id`, `media_path`, `media_type`, `display_order`) VALUES
(5, 9, 'uploads/communaute_6a8e32fc6a745_0.jpg', 'image', 1),
(6, 9, 'uploads/communaute_6a8e32fc712ba_1.jpg', 'image', 2),
(7, 9, 'uploads/communaute_6a8e331d91586_0.mp4', 'video', 3),
(8, 9, 'uploads/communaute_6a8e334918846_0.png', 'image', 4),
(9, 9, 'uploads/communaute_6a8e33491999b_1.png', 'image', 5),
(10, 9, 'uploads/communaute_6a8e33491cb7c_2.jpg', 'image', 6),
(11, 9, 'uploads/communaute_6a8e33491ddf1_3.png', 'image', 7),
(12, 9, 'uploads/communaute_6a8e33492103a_4.png', 'image', 8),
(13, 9, 'uploads/communaute_6a8e3349220c1_5.jpg', 'image', 9),
(14, 9, 'uploads/communaute_6a8e334924a95_6.png', 'image', 10),
(15, 9, 'uploads/communaute_6a8e334928117_7.jpg', 'image', 11),
(21, 17, 'uploads/communaute_6a91ec8f98b8d_0.jpg', 'image', 1);

-- --------------------------------------------------------

--
-- Structure de la table `communaute_reactions`
--

CREATE TABLE `communaute_reactions` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('like','love') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `communaute_reactions`
--

INSERT INTO `communaute_reactions` (`id`, `post_id`, `user_id`, `type`, `created_at`) VALUES
(5, 6, 3, 'love', '2026-08-25 23:49:26'),
(8, 6, 23, 'like', '2026-08-25 23:59:58'),
(10, 17, 10, 'love', '2026-08-28 20:17:43'),
(13, 9, 10, 'love', '2026-08-28 20:17:50'),
(15, 6, 10, 'like', '2026-08-28 20:17:53'),
(16, 17, 3, 'love', '2026-08-28 20:36:03'),
(17, 17, 23, 'love', '2026-08-28 22:52:06');

-- --------------------------------------------------------

--
-- Structure de la table `dm_attachments`
--

CREATE TABLE `dm_attachments` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(20) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `dm_attachments`
--

INSERT INTO `dm_attachments` (`id`, `message_id`, `file_path`, `original_name`, `file_type`, `mime_type`, `file_size`) VALUES
(1, 13, 'uploads/dm/dm_13_6a9208624b73c.png', 'Capture d\'écran 2026-08-26 180314.png', 'image', 'image/png', 552206);

-- --------------------------------------------------------

--
-- Structure de la table `dm_conversations`
--

CREATE TABLE `dm_conversations` (
  `id` int(11) NOT NULL,
  `user_a_id` int(11) NOT NULL,
  `user_b_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `dm_conversations`
--

INSERT INTO `dm_conversations` (`id`, `user_a_id`, `user_b_id`, `created_at`) VALUES
(2, 10, 23, '2026-08-26 13:43:42');

-- --------------------------------------------------------

--
-- Structure de la table `dm_messages`
--

CREATE TABLE `dm_messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL,
  `deleted_for_everyone_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `dm_messages`
--

INSERT INTO `dm_messages` (`id`, `conversation_id`, `sender_id`, `content`, `created_at`, `read_at`, `deleted_for_everyone_at`) VALUES
(3, 2, 10, 'aaa', '2026-08-26 13:43:56', '2026-08-26 17:12:40', NULL),
(4, 2, 23, 'im', '2026-08-26 14:12:44', '2026-08-28 23:18:28', NULL),
(7, 2, 10, 'gj', '2026-08-28 20:18:34', '2026-08-28 23:19:25', NULL),
(8, 2, 10, 'lmù', '2026-08-28 20:18:37', '2026-08-28 23:19:25', NULL),
(9, 2, 10, 'lkm', '2026-08-28 20:18:39', '2026-08-28 23:19:25', NULL),
(10, 2, 10, '🥶🥶🥶', '2026-08-28 20:18:44', '2026-08-28 23:19:25', NULL),
(11, 2, 23, 'df', '2026-08-28 20:19:31', '2026-08-29 01:08:15', NULL),
(13, 2, 10, NULL, '2026-08-28 22:14:58', '2026-08-29 01:52:46', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `dm_message_hides`
--

CREATE TABLE `dm_message_hides` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(20) NOT NULL DEFAULT 'public',
  `file_path` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `documents`
--

INSERT INTO `documents` (`id`, `title`, `category`, `file_path`, `created_at`) VALUES
(1, 'FICHE DE PREINSCRIPTION 2025-2026', 'public', 'uploads/doc_6a8b620ab9103.pdf', '2026-08-24 00:11:38'),
(7, 'DEMANDE DE CERTIFICAT DE SCOLARITE 2025-2026', 'etudiant', 'uploads/doc_6a8df551ce869.pdf', '2026-08-25 23:04:33'),
(8, 'DEMANDE DE RELEVE DE NOTE 2025-2026', 'etudiant', 'uploads/doc_6a8df568e037f.pdf', '2026-08-25 23:04:56');

-- --------------------------------------------------------

--
-- Structure de la table `evenements`
--

CREATE TABLE `evenements` (
  `id` int(11) NOT NULL,
  `titre_fr` varchar(255) NOT NULL,
  `titre_en` varchar(255) DEFAULT NULL,
  `titre_mg` varchar(255) DEFAULT NULL,
  `description_fr` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `description_mg` text DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime DEFAULT NULL,
  `lieu` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `categorie` varchar(30) NOT NULL DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `evenements`
--

INSERT INTO `evenements` (`id`, `titre_fr`, `titre_en`, `titre_mg`, `description_fr`, `description_en`, `description_mg`, `date_debut`, `date_fin`, `lieu`, `image_path`, `categorie`, `created_at`) VALUES
(2, 'Examen', '', '', 'Ce soir; on se voit au lounge', 'Tonight; see you in the lounge', 'Anio alina; hahita anao ao amin\'ny efitrano fandraisam-bahiny', '2026-08-26 12:00:00', '2026-09-04 12:00:00', 'Valiha lounge', NULL, 'ceremonie', '2026-08-26 09:00:52'),
(4, 'Examen', 'Examen', 'Fanadinana', 'Préparer pour l\'examen', 'Prepare for the exam', 'Miomàna amin\'ny fanadinana', '2026-09-05 12:06:00', '2026-09-12 12:07:00', 'ISSTM', 'uploads/event_6a9a8a638ff81.jpg', 'examen', '2026-09-04 09:07:47');

-- --------------------------------------------------------

--
-- Structure de la table `filieres`
--

CREATE TABLE `filieres` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `mention` varchar(150) DEFAULT NULL,
  `niveaux` varchar(50) DEFAULT NULL,
  `slug` varchar(150) NOT NULL,
  `nom_fr` varchar(255) NOT NULL,
  `nom_en` varchar(255) DEFAULT NULL,
  `nom_mg` varchar(255) DEFAULT NULL,
  `description_fr` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `description_mg` text DEFAULT NULL,
  `debouches_fr` text DEFAULT NULL,
  `debouches_en` text DEFAULT NULL,
  `debouches_mg` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `historique_fr` text DEFAULT NULL,
  `historique_en` text DEFAULT NULL,
  `historique_mg` text DEFAULT NULL,
  `avantages_fr` text DEFAULT NULL,
  `avantages_en` text DEFAULT NULL,
  `avantages_mg` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `filieres`
--

INSERT INTO `filieres` (`id`, `code`, `mention`, `niveaux`, `slug`, `nom_fr`, `nom_en`, `nom_mg`, `description_fr`, `description_en`, `description_mg`, `debouches_fr`, `debouches_en`, `debouches_mg`, `image_path`, `display_order`, `created_at`, `historique_fr`, `historique_en`, `historique_mg`, `avantages_fr`, `avantages_en`, `avantages_mg`) VALUES
(1, 'GI', 'STNPA', 'L1,L2,L3', 'genie-informatique', 'Génie Informatique', 'Computer Engineering', 'Solosaina Engineering', 'Formation en développement logiciel, systèmes d\'information et technologies web.', 'Training in software development, information systems and web technologies.', 'Fanofanana amin\'ny fampandrosoana ny rindrambaiko, rafitra vaovao sy ny teknolojia web.', 'Développeur / Développeuse logiciel\r\nAdministrateur systèmes et réseaux\r\nIngénieur en cybersécurité\r\nData analyst / Data scientist\r\nChef de projet informatique\r\nIngénieur DevOps\r\nConsultant en systèmes d\'information', 'Software Developer\r\nSystems and Networks Administrator\r\nCybersecurity Engineer\r\nData analyst / Data scientist\r\nIT Project Manager\r\nDevOps Engineer\r\nInformation Systems Consultant', 'Software Developer\r\nAdministrateur Systems sy Networks\r\nCybersecurity Engineer\r\nMpikaroka momba ny angona/Mpikaroka momba ny data\r\nIT Project Manager\r\nDevOps Injeniera\r\nInformation Systems Consultant', 'uploads/filiere_6a7a6c495a5a0.jpg', 1, '2026-08-10 23:06:30', 'L\'informatique en tant que discipline scientifique est née au milieu du XXe siècle, avec les travaux fondateurs d\'Alan Turing sur la calculabilité et la construction des premiers ordinateurs électroniques dans les années 1940-1950. Le génie informatique s\'est ensuite structuré comme filière d\'ingénierie à part entière à partir des années 1960-1970, avec l\'essor des langages de programmation, des systèmes d\'exploitation et des réseaux. Depuis les années 2000, la discipline connaît une expansion continue portée par Internet, le cloud computing, les objets connectés et l\'intelligence artificielle, faisant du génie informatique l\'un des secteurs technologiques les plus dynamiques au monde.', 'Computer science as a scientific discipline originated in the mid-20th century, with Alan Turing\'s seminal work on computability and the construction of the first electronic computers in the 1940s-1950s. Computer engineering was then structured as an engineering field in its own right from the 1960s to the 1970s, with the rise of programming languages, operating systems and networks. Since the 2000s, the discipline has seen continuous expansion driven by the Internet, cloud computing, connected objects and artificial intelligence, making computer engineering one of the fastest growing technology sectors in the world.', 'Computer siansa toy ny fitsipi-pifehezana ara-tsiansa Avy amin\'ny tapaky ny taonjato faha-20, amin\'ny Alan Turing ny asa seminal amin\'ny computability sy ny fanorenana ny voalohany elektronika ordinatera ao amin\'ny 1940s-1950s. Solosaina injeniera dia avy eo structured ho toy ny saha injeniera ao amin\'ny manokana ny zo avy amin\'ny taona 1960 ny taona 1970, noho ny fiakaran\'ny fandaharana ny fiteny, rafitra miasa sy ny tambajotra. Hatramin\'ny taona 2000, ny famaizana efa hita mitohy fanitarana entin\'ny ny Internet, rahona computing, mifandray amin\'ny zavatra sy fahaizana artifisialy, mahatonga solosaina injeniera iray amin\'ireo sehatra teknolojia mitombo haingana indrindra eto amin\'izao tontolo izao.', 'Un secteur en croissance constante, avec une forte demande de compétences dans presque tous les pays\r\nDes débouchés variés : développement logiciel, cybersécurité, intelligence artificielle, gestion de bases de données, administration réseau\r\nUne formation qui développe la logique, la rigueur et la capacité à résoudre des problèmes complexes\r\nDes outils et technologies universels, transférables dans quasiment tous les secteurs d\'activité\r\nLa possibilité de travailler à distance ou de créer sa propre entreprise (startups, freelance)\r\nUne veille technologique permanente qui rend le métier stimulant et jamais routinier', 'A constantly growing sector, with a high demand for skills in almost all countries\r\nVarious opportunities: software development, cybersecurity, artificial intelligence, database management, network administration\r\nTraining that develops logic, rigor and the ability to solve complex problems\r\nUniversal tools and technologies, transferable in almost all sectors of activity\r\nThe ability to work remotely or start your own business (startups, freelance)\r\nA permanent technological watch that makes the job stimulating and never routine', 'Ny fitomboan\'ny sehatra tsy mitsaha-mitombo, miaraka amin\'ny tinady goavana ho an\'ny fahaiza-manao any amin\'ny ankamaroan\'ny firenena rehetra\r\nIsan-karazany fahafahana: rindrambaiko fampandrosoana, [object Window], solon-tsaina, angona fitantanana, tambajotra fitantanana\r\nFanofanana izay mampivelatra lojika, henjana ary ny fahafahana hamaha olana sarotra\r\nFitaovana sy teknolojia manerantany, miampita amin\'ny ankamaroan\'ny sehatra rehetra\r\nNy fahaizana miasa lavitra na manomboka ny orinasanao manokana (startups, mahaleo-tena)\r\nNy fiambenana maharitra ara-teknolojika izay mahatonga ny asa mandrisika ary tsy fahazarana mihitsy'),
(2, 'GB', 'STNPA', 'L2,L3,M1,M2', 'genie-biomedical', 'Génie Biomédical', 'Biomedical Engineering', 'Injeniera Biomedikaly', 'Formation aux technologies médicales, à la maintenance des équipements et à l\'ingénierie de la santé.', 'Training in medical technologies, equipment maintenance, and health engineering.', 'Fiofanana momba ny teknolojia ara-pitsaboana, fikojakojana fitaovana ary injenieran\'ny fahasalamana.', 'Ingénieur biomédical hospitalier\r\nTechnicien de maintenance d\'équipements médicaux\r\nResponsable qualité en dispositifs médicaux\r\nIngénieur d\'application pour fabricants d\'équipements de santé\r\nConsultant en infrastructures hospitalières', 'Hospital Biomedical Engineer\nMedical Equipment Maintenance Technician\nMedical Devices Quality Manager\nApplication Engineer for Health Equipment Manufacturers\nHospital Infrastructure Consultant', 'Revolisionan\'ny fanaovana fandidiana atidoha alefa fahitalavitra\nMedical Fitaovana Maintenance Technician\nMpiasan\'ny kalitaon\'ny fitaovana ara-pitsaboana\nApplication Engineer ho an\'ny fitaovana ara-pahasalamana mpanamboatra\n- Administrateur Suite Business', 'uploads/filiere_6a7a6c55e7c30.jpg', 2, '2026-08-10 23:06:30', 'Le génie biomédical est une discipline relativement récente, apparue au cours du XXe siècle à la croisée de l\'ingénierie, de la médecine et de la biologie. Son essor a été porté par l\'invention d\'appareils médicaux majeurs comme l\'électrocardiographe, l\'imagerie par résonance magnétique (IRM) et le scanner, ainsi que par le développement des prothèses et des dispositifs d\'assistance. Depuis les années 1980, la discipline s\'est structurée en filière d\'ingénierie à part entière dans de nombreuses universités à travers le monde, répondant à un besoin croissant de professionnels capables de concevoir, maintenir et optimiser les équipements médicaux.', 'Biomedical engineering is a relatively new discipline that emerged during the 20th century at the crossroads of engineering, medicine and biology. Its rise was driven by the invention of major medical devices such as the electrocardiograph, magnetic resonance imaging (MRI) and the scanner, as well as the development of prostheses and assistive devices. Since the 1980s, the discipline has been structured as a full-fledged engineering track in many universities around the world, responding to a growing need for professionals capable of designing, maintaining and optimizing medical equipment.', 'Biomedical injeniera dia somary шинэ famaizana izay nipoitra nandritra ny taonjato faha-20 amin\'ny sampanan-dalana ny injeniera, fanafody sy ny biolojia. Ny fiakarany dia entin\'ny famoronana fitaovana ara-pitsaboana lehibe toy ny electrocardiograph, andriamby akony fitarafana (MRI) sy ny scanner, ary koa ny fampandrosoana ny prostheses sy ny fitaovana manampy. Hatramin\'ny taona 1980, ny fitsipi-pifehezana efa voarafitra ho toy ny feno-draharahan\'ny injeniera lalana ao amin\'ny anjerimanontolo maro manerana izao tontolo izao, mamaly ny tsy mitsaha-mitombo mila matihanina afaka planina, foana ary optimizing fitaovana ara-pitsaboana.', 'Un secteur porteur de sens, directement utile à la santé et au bien-être des populations\r\nUne forte demande d\'ingénieurs biomédicaux dans les hôpitaux, cliniques et industries pharmaceutiques\r\nUne formation pluridisciplinaire (électronique, informatique, biologie, mécanique)\r\nDes opportunités dans la maintenance, la conception et la gestion des équipements médicaux\r\nUn secteur en expansion avec la modernisation continue des infrastructures de santé\r\nUne profession reconnue et valorisée dans le secteur public comme privé', 'A meaningful sector, directly useful for the health and well-being of populations\nStrong demand for biomedical engineers in hospitals, clinics and pharmaceutical industries\nMultidisciplinary training (electronics, computer science, biology, mechanics)\nOpportunities in the maintenance, design and management of medical equipment\nAn expanding sector with the continuous modernization of health infrastructure\nA profession recognized and valued in both the public and private sectors', 'Sehatra manan-danja, ilaina mivantana ho an\'ny fahasalamana sy ny fahasalaman\'ny mponina\nStrong fangatahana biomedical injeniera ao amin\'ny hopitaly, toeram-pitsaboana sy ny orinasa fanafody\nFiofanana maro samihafa (elektronika, siansa momba ny solosaina, biolojia, mekanika)\nFahafahana ao amin\'ny fikojakojana, famolavolana sy ny fitantanana ny fitaovana fitsaboana\nNy fanitarana ny sehatry ny fanavaozana tsy tapaka ny foto-drafitrasa ara-pahasalamana\nAsa fantatra sy sarobidy amin\'ny sehatra miankina sy tsy miankina'),
(3, 'GEI', 'STNPA', 'L1,L2,L3', 'genie-electronique-informatique', 'Génie Électronique et Informatique', 'Electronics and Computer Engineering', 'Injeniera Elektronika sy Informatika', 'Formation en électronique embarquée, Internet des Objets (IoT) et systèmes intelligents.', 'Training in embedded electronics, Internet of Things (IoT), and intelligent systems.', 'Fiofanana momba ny elektronika anaty, Internet an\'ny Zavatra (IoT) ary rafitra manan-tsaina.', 'Ingénieur en électronique embarquée\nConcepteur de circuits imprimés (PCB)\nIngénieur systèmes embarqués\nTechnicien supérieur en maintenance électronique\nIngénieur R&D en objets connectés', 'Embedded Electronics Engineer\nPrinted circuit board (PCB) designer\nEmbedded Systems Engineer\nSenior Electronics Maintenance Technician\nConnected Objects R&D Engineer', 'Nandinika lalina Electronics Engineer\nNatonta faritra board (PCB) mpamorona\nNandinika lalina Systems Engineer\nManam-pahaizana momba ny teknisianina momba ny teknisianina momba ny f\nMifandray zavatra R & D Engineer', 'uploads/filiere_6a7a6c5f5200e.jpg', 3, '2026-08-10 23:06:30', 'Le génie électronique est né avec l\'invention du transistor en 1947, qui a révolutionné la miniaturisation des circuits et ouvert la voie à l\'électronique moderne. Associée à l\'informatique à partir des années 1970 avec l\'apparition des microprocesseurs, cette filière hybride forme des ingénieurs capables de concevoir aussi bien le matériel (circuits, cartes électroniques) que les logiciels qui les pilotent. Elle est aujourd\'hui au cœur de nombreuses innovations technologiques, des smartphones aux systèmes embarqués industriels.', 'Electronic engineering was born with the invention of the transistor in 1947, which revolutionized the miniaturization of circuits and paved the way for modern electronics. Associated with computer science from the 1970s with the appearance of microprocessors, this hybrid sector trains engineers capable of designing both the hardware (circuits, electronic boards) and the software that drives them. It is now at the heart of many technological innovations, from smartphones to industrial embedded systems.', 'Electronic injeniera dia teraka ny namorona ny transistor tamin\'ny 1947, izay revolutionized ny miniaturization ny faritra, ary nasiany rarivato ny lalana ho an\'ny fitaovana maoderina. Mifandray amin\'ny solosaina siansa avy amin\'ny taona 1970 miaraka amin\'ny endriky ny microprocessors, mifangaro io sehatra Mampianatra injeniera afaka nanaovany na ny fitaovana (faritra, elektronika zana-kazo) sy ny rindrambaiko izay mitarika azy ireo. Izany dia izao eo anivon\'ny maro ny teknolojia fanavaozana, avy amin\'ny finday avo lenta ny indostria nandinika lalina rafitra.', 'Une double compétence matériel + logiciel très recherchée par les employeurs\nDes applications dans des secteurs variés : télécommunications, automobile, aéronautique, électroménager\nUne formation technique solide, base pour évoluer vers des postes de conception avancée\nUn secteur en croissance avec la multiplication des objets connectés (IoT)\nUne profession qui allie créativité technique et rigueur scientifique', 'Dual hardware + software skills highly sought after by employers\nApplications in various sectors: telecommunications, automotive, aeronautics, household appliances\nSolid technical training, the basis for moving into advanced design positions\nA growing sector with the proliferation of connected objects (IoT)\nA profession that combines technical creativity and scientific rigour', 'Dual hardware + rindrambaiko fahaiza-manao tena nitady taorian\'ny mpampiasa\nFampiharana isan-karazany sehatra: fifandraisan-davitra, fiara, aeronautics, fitaovana tokantrano\nFampiofanana ara-teknika mafy orina, ny fototry ny mifindra ho endrika mandroso toerana\nNy sehatra tsy mitsaha-mitombo miaraka amin\'ny fitomboan\'ny zavatra mifandraika (IoT)\nNy asa izay Mitambatra famoronana ara-teknika sy siantifika fatratra'),
(4, 'GE', 'STI', 'L1,L2,L3', 'genie-electrique', 'Génie Électrique', 'Electrical Engineering', 'Injeniera Elektrika', 'Formation en électrotechnique, automatisme, et gestion de l\'énergie électrique.', 'Training in electrotechnics, automation, and electrical energy management.', 'Fiofanana momba ny elektroteknika, automatisme ary fitantanana ny angovo elektrika.', 'Ingénieur en distribution d\'énergie électrique\nChef de projet en énergies renouvelables\nIngénieur en installations électriques industrielles\nTechnicien supérieur en électrotechnique\nResponsable maintenance électrique', '', 'Electric Power Distribution injeniera\nMpitantana tetik \'asa momba ny angovo azo havaozina\nInjeniera fampitaovana herinaratra indostrialy\nManampahaizana momba ny teknisianina momba ny herinaratra\nMpitantana fikojakojana herinaratra', 'uploads/filiere_6a7a6c6a870b8.jpg', 4, '2026-08-10 23:06:30', 'Le génie électrique trouve ses racines dans les travaux du XIXe siècle sur l\'électromagnétisme (Faraday, Maxwell) et les premières applications industrielles de l\'électricité, notamment la distribution d\'énergie développée par Edison et Tesla. Devenue une discipline d\'ingénierie à part entière au tournant du XXe siècle, elle a accompagné l\'électrification progressive du monde : réseaux de distribution, machines électriques, puis systèmes de production d\'énergie renouvelable depuis les années 2000.', NULL, 'Herinaratra injeniera manana ny fakany tamin\'ny taonjato fahasivy ambin\'ny folo miasa amin\'ny electromagnetism (Faraday, Maxwell) ary ny voalohany indostrialy fampiharana ny herinaratra, indrindra fa ny fizarana ny angovo mandroso ny Edison sy Tesla. Lasa injeniera fifehezan ao amin\'ny manokana zo tamin\'ny taonjato faha-20, dia niaraka ny tsikelikely electrification \'izao tontolo izao: fizarana tambajotra, herinaratra milina, dia indray ny famokarana angovo rafitra hatramin\'ny taona 2000.', 'Une filière indispensable : l\'électricité reste au cœur de toutes les infrastructures modernes\nDes débouchés stables dans la production, le transport et la distribution d\'énergie\nDes opportunités croissantes dans les énergies renouvelables (solaire, éolien, hydraulique)\nUne formation qui ouvre à la fois vers l\'industrie, le bâtiment et les bureaux d\'études\nUne expertise recherchée pour la maintenance et la modernisation des infrastructures électriques existantes', NULL, 'Sehatra iray tena ilaina: ny herinaratra dia mitoetra ao anatin\'ny fon\'ny fotodrafitrasa maoderina rehetra\nTranon\'omby baovao amin\'ny famokarana angovo, fifindran\'ny sy ny fizarana\nMitombo ny fahafaha-manao amin\'ny angovo azo havaozina (masoandro, rivotra, hydro)\nFanofanana izay manokatra ny orinasa, ny fananganana sy ny famolavolana birao\nExpertise nitady ny fikojakojana sy ny toetr\'andro ny efa misy herinaratra foto-drafitrasa'),
(5, 'GIND', 'STI', 'L1,L2,L3,M1,M2', 'genie-industriel', 'Génie Industriel', 'Industrial Engineering', 'Injeniera Indostrialy', 'Formation en management industriel, logistique, qualité et organisation de la production.', 'Training in industrial management, logistics, quality, and production organization.', 'Fiofanana momba ny fitantanana indostrialy, lojistika, kalitao ary fandaminana ny famokarana.', 'Ingénieur qualité\nResponsable de production\nIngénieur logistique / supply chain\nConsultant en amélioration continue (Lean/Six Sigma)\nIngénieur méthodes et process', '', '', 'uploads/filiere_6a7a6d53d5a74.jpg', 5, '2026-08-10 23:06:30', 'Le génie industriel s\'est développé au début du XXe siècle avec les travaux de Frederick Taylor et Henry Ford sur l\'organisation scientifique du travail et la production en série. La discipline a ensuite intégré les méthodes de gestion de la qualité (Toyota, Lean management) à partir des années 1950-1980, pour devenir une filière axée sur l\'optimisation globale des systèmes de production, alliant ingénierie technique, gestion et logistique.', NULL, NULL, 'Une formation transversale, à la croisée de la technique, de la gestion et de la logistique\nDes compétences recherchées dans toutes les industries manufacturières\nDes débouchés dans l\'optimisation des processus, la qualité et la supply chain\nUne vision globale des systèmes de production, utile pour évoluer vers des postes de direction\nUne discipline qui valorise à la fois la rigueur analytique et le sens de l\'organisation', NULL, NULL),
(6, 'GT', 'STI', 'M1,M2', 'genie-thermique', 'Génie Thermique', 'Thermal Engineering', 'Injeniera Termika', 'Formation en énergétique, thermodynamique, et optimisation de l\'efficacité énergétique.', 'Training in energetics, thermodynamics, and energy efficiency optimization.', 'Fiofanana momba ny angovo, termodinamika ary fanatsarana ny fahombiazan\'ny angovo.', 'Ingénieur en climatisation et ventilation (CVC)\nIngénieur efficacité énergétique\nTechnicien supérieur en installations thermiques\nChargé d\'études thermiques du bâtiment\nIngénieur en procédés industriels thermiques', '', '', 'uploads/filiere_6a7a6ca91e9cf.jpg', 6, '2026-08-10 23:06:30', 'Le génie thermique s\'appuie sur les principes de la thermodynamique établis au XIXe siècle par des scientifiques comme Carnot, Clausius et Kelvin. Il s\'est développé comme discipline d\'ingénierie appliquée avec l\'essor des machines thermiques, des systèmes de chauffage, de ventilation et de climatisation (CVC) au cours du XXe siècle, et connaît aujourd\'hui un regain d\'intérêt avec les enjeux d\'efficacité énergétique et de transition écologique.', NULL, NULL, 'Une expertise essentielle pour le confort thermique des bâtiments et des industries\nDes débouchés croissants liés à la transition énergétique et aux économies d\'énergie\nDes applications variées : climatisation, chauffage, réfrigération, procédés industriels\nUne formation technique solide en thermodynamique et en mécanique des fluides\nUne discipline de plus en plus valorisée face aux enjeux climatiques actuels', NULL, NULL),
(7, 'GCIVIL', 'STGC', 'L1,L2,L3,M1,M2', 'genie-civil', 'Génie Civil', 'Civil Engineering', 'Injeniera Sivily', 'Formation en construction, calcul de structures et utilisation des matériaux de construction.', 'Training in construction, structural calculation, and use of construction materials.', 'Fiofanana momba ny fanorenana, fikajiana rafitra ary fampiasana fitaovam-panorenana.', 'Ingénieur BTP (bâtiment et travaux publics)\nConducteur de travaux\nIngénieur structures\nChef de projet en construction\nTechnicien supérieur en génie civil', '', '', 'uploads/filiere_6a7a6d02f219e.jpg', 7, '2026-08-10 23:06:30', 'Le génie civil est l\'une des plus anciennes formes d\'ingénierie, ses racines remontant aux grandes constructions de l\'Antiquité (pyramides, aqueducs romains). Il s\'est structuré comme discipline scientifique moderne au XVIIIe et XIXe siècle avec le développement de la résistance des matériaux et du calcul des structures, permettant la construction de ponts, de chemins de fer et de grands bâtiments. Aujourd\'hui, le génie civil intègre des outils numériques avancés (BIM, modélisation 3D) tout en restant fondé sur ces principes historiques.', NULL, NULL, 'Une filière historique et toujours indispensable : bâtir les infrastructures de demain\nDes débouchés stables dans le BTP, secteur porteur d\'emplois dans de nombreux pays\nDes opportunités variées : bâtiment, travaux publics, hydraulique, topographie\nUne formation concrète, avec un impact visible et durable sur le territoire\nUne discipline en pleine modernisation grâce aux outils numériques (BIM, modélisation 3D)', NULL, NULL),
(8, 'GHYD', 'STGC', 'L1,L2,L3', 'genie-hydraulique', 'Génie Hydraulique', 'Hydraulic Engineering', 'Injeniera Hydraulika', 'Formation en gestion des ressources en eau, hydraulique urbaine et aménagements.', 'Training in water resource management, urban hydraulics, and developments.', 'Fiofanana momba ny fitantanana ny rano, ny rano an-tanàn-dehibe ary ny fanajariana.', 'Ingénieur hydraulicien\nIngénieur en gestion des ressources en eau\nIngénieur en assainissement\nChargé d\'études en irrigation\nTechnicien supérieur en réseaux hydrauliques', 'Hydraulic engineer\nWater resources management engineer\nSanitation engineer\nIrrigation study manager\nSenior technician in hydraulic networks', '', 'uploads/filiere_6a7a6cb41aa1c.jpg', 8, '2026-08-10 23:06:30', 'L\'hydraulique compte parmi les plus anciennes sciences de l\'ingénieur, illustrée par les systèmes d\'irrigation et d\'adduction d\'eau de l\'Antiquité (Égypte, Mésopotamie, Empire romain). Elle s\'est formalisée scientifiquement à partir du XVIIIe siècle avec les travaux de Bernoulli et d\'autres pionniers de la mécanique des fluides, avant de devenir une filière d\'ingénierie moderne appliquée à la gestion de l\'eau, à l\'irrigation, à l\'assainissement et aux barrages hydroélectriques.', 'Hydraulics is one of the oldest engineering sciences, illustrated by the irrigation and water supply systems of Antiquity (Egypt, Mesopotamia, Roman Empire). It became scientifically formalized from the 18th century with the work of Bernoulli and other pioneers in fluid mechanics, before becoming a modern engineering sector applied to water management, irrigation, sanitation and hydroelectric dams.', NULL, 'Une expertise cruciale pour la gestion durable des ressources en eau\nDes débouchés dans l\'irrigation, l\'assainissement, l\'approvisionnement en eau potable\nDes opportunités dans les grands projets d\'infrastructure (barrages, réseaux hydrauliques)\nUne discipline stratégique face aux enjeux climatiques et à la raréfaction de l\'eau\nUne formation qui combine mécanique des fluides, génie civil et environnement', 'Crucial expertise for the sustainable management of water resources\nOpportunities in irrigation, sanitation, drinking water supply\nOpportunities in large infrastructure projects (dams, hydraulic networks)\nA strategic discipline in the face of climate issues and water scarcity\nTraining that combines fluid mechanics, civil engineering and the environment', NULL),
(9, 'GARCHI', 'STGC', 'L1,L2,L3', 'genie-architecture', 'Génie Architecture', 'Architectural Engineering', 'Injeniera Maritrano', 'Formation en conception architecturale, urbanisme, et design d\'espace durable.', 'Training in architectural design, urban planning, and sustainable space design.', 'Fiofanana momba ny famolavolana maritrano, fandrindrana ny tanàna ary famolavolana habaka maharitra.', 'Architecte / Ingénieur architecte\nDessinateur-projeteur en bâtiment\nChef de projet en conception architecturale\nUrbaniste\nConsultant en construction durable', '', '', 'uploads/filiere_6a7a6cd5935b5.jpg', 9, '2026-08-10 23:06:30', 'L\'architecture est l\'une des disciplines les plus anciennes de l\'humanité, présente depuis les premières civilisations à travers la conception d\'habitats et de monuments. Le génie architectural moderne, qui associe la créativité de l\'architecture aux exigences techniques de l\'ingénierie (structures, matériaux, réglementation), s\'est développé au cours du XXe siècle avec l\'essor de l\'urbanisation et des nouvelles techniques de construction, intégrant aujourd\'hui les enjeux de durabilité et de conception assistée par ordinateur.', NULL, NULL, 'Une filière qui allie créativité artistique et rigueur technique\nDes débouchés dans la conception de bâtiments résidentiels, publics et industriels\nDes opportunités croissantes autour de l\'architecture durable et écologique\nUne formation qui développe la vision spatiale, le dessin technique et la gestion de projet\nUne discipline valorisante, avec un impact concret et visible sur le cadre de vie', NULL, NULL),
(14, 'FE', 'STI', 'L1,L2,L3', 'froid-energie', 'Froid et Énergie', 'Refrigeration and Energy', 'Hatsiaka sy Angovo', 'Formation en systèmes frigorifiques, climatisation et gestion des énergies renouvelables.', 'Training in refrigeration systems, air conditioning and renewable energy management.', 'Fampiofanana momba ny rafitra fampangatsiahana, fanamaivanana rivotra ary fitantanana angovo azo havaozina.', 'Ingénieur frigoriste\nIngénieur en efficacité énergétique\nTechnicien supérieur en froid industriel\nResponsable maintenance énergétique\nChargé d\'études en systèmes frigorifiques', '', '', 'uploads/filiere_6a7cdb1c85620.jpg', 10, '2026-08-12 20:31:02', 'Les techniques du froid se sont développées à partir du XIXe siècle avec l\'invention des premières machines frigorifiques, permettant la conservation des denrées alimentaires à grande échelle. Associée aux enjeux énergétiques modernes, cette filière a évolué pour englober la production, la distribution et l\'optimisation de l\'énergie, en particulier dans un contexte de transition énergétique et de recherche d\'efficacité, devenant une discipline stratégique pour l\'industrie agroalimentaire et le bâtiment.', NULL, NULL, 'Une expertise recherchée dans l\'agroalimentaire, la logistique et la conservation des denrées\nDes débouchés liés aux enjeux actuels d\'efficacité énergétique\nDes applications variées : réfrigération industrielle, climatisation, production d\'énergie\nUne formation technique alliant thermodynamique, électrotechnique et mécanique\nUn secteur stratégique pour la sécurité alimentaire et la transition énergétique', NULL, NULL),
(15, 'EII', 'STNPA', 'M1,M2', 'electronique-informatique-industrielle', 'Électronique et Informatique Industrielle', 'Electronics and Industrial Computing', 'Elektronika sy Informatika Indostrialy', 'Formation en systèmes électroniques industriels, automatisation et informatique appliquée à l\'industrie.', 'Training in industrial electronic systems, automation and computing applied to industry.', 'Fampiofanana momba ny rafitra elektronika indostrialy, fanaratana ary informatika ampiharina amin\'ny indostria.', 'Ingénieur automaticien\nIngénieur en informatique industrielle\nTechnicien supérieur en maintenance industrielle\nIngénieur en robotique industrielle\nIntégrateur de systèmes automatisés', '', '', 'uploads/filiere_6a7cdb29ea27c.png', 11, '2026-08-12 20:31:02', 'Cette filière est née de la convergence entre l\'électronique, l\'informatique et l\'automatisation industrielle, un mouvement amorcé dans les années 1970-1980 avec l\'apparition des automates programmables. Elle a pris une importance croissante avec la robotisation des usines et, plus récemment, avec l\'avènement de l\'industrie 4.0, qui connecte capteurs, machines et systèmes informatiques pour automatiser et optimiser les processus de production.', NULL, NULL, 'Une filière au cœur de la modernisation industrielle (automatisation, robotique)\nDes débouchés dans la conception et la maintenance de systèmes automatisés\nUne double compétence électronique + informatique très recherchée en industrie\nDes opportunités croissantes avec l\'essor de l\'industrie 4.0 et de la robotique\nUne formation technique complète, adaptée aux besoins des usines modernes', NULL, NULL),
(16, 'TR', 'STNPA', 'M1,M2', 'telecommunications-reseaux', 'Télécommunications et Réseaux', 'Telecommunications and Networks', 'Fifandraisan-davitra sy Tambajotra', 'Formation en réseaux informatiques, télécommunications et infrastructures numériques.', 'Training in computer networks, telecommunications and digital infrastructure.', 'Fampiofanana momba ny tambajotra informatika, fifandraisan-davitra ary rafitra nomerika.', 'Ingénieur réseaux et télécommunications\nAdministrateur systèmes et réseaux\nIngénieur en sécurité des réseaux\nTechnicien supérieur en télécommunications\nConsultant en infrastructures réseau', '', '', 'uploads/filiere_6a7cdb36c6676.jpg', 12, '2026-08-12 20:31:02', 'Les télécommunications ont débuté avec l\'invention du télégraphe électrique au XIXe siècle, suivie du téléphone, puis de la radio et de la télévision au XXe siècle. La discipline a connu une révolution majeure avec l\'apparition d\'Internet dans les années 1990 et le développement des réseaux mobiles (2G à 5G), transformant les télécommunications en une filière technologique centrale de la société connectée d\'aujourd\'hui.', NULL, NULL, 'Un secteur stratégique, indispensable à la connectivité mondiale\nDes débouchés dans les opérateurs télécoms, les entreprises et les administrations\nDes opportunités croissantes avec le déploiement de la fibre optique et de la 5G\nUne formation technique solide en réseaux, transmission de données et sécurité\nUn secteur en perpétuelle évolution technologique, stimulant pour les esprits curieux', NULL, NULL),
(17, 'GL', 'STNPA', 'M1,M2', 'genie-logiciel', 'Génie Logiciel', 'Software Engineering', 'Injeniera Rindrambaiko', 'Formation avancée en conception, développement et gestion de projets logiciels.', 'Advanced training in software design, development and project management.', 'Fampiofanana mandroso momba ny famoronana, famolavolana ary fitantanana tetikasa rindrambaiko.', 'Développeur / Développeuse d\'applications\nIngénieur logiciel\nChef de projet informatique\nArchitecte logiciel\nTesteur / Ingénieur qualité logicielle', '', '', 'uploads/filiere_6a7cdb45b9eea.jpg', 13, '2026-08-12 20:31:02', 'Le génie logiciel est né dans les années 1960-1970 en réponse à la fameuse « crise du logiciel », lorsque la complexité croissante des programmes a révélé le besoin de méthodes rigoureuses de conception, de test et de gestion de projet informatique. Depuis, la discipline n\'a cessé d\'évoluer avec l\'apparition de nouvelles méthodologies (agile, DevOps) et de nouveaux paradigmes de programmation, structurant aujourd\'hui l\'ensemble de l\'industrie du logiciel.', NULL, NULL, 'Une filière au cœur de la transformation numérique de tous les secteurs\nDes débouchés très nombreux : applications web, mobiles, systèmes d\'entreprise\nUne formation qui développe la pensée logique et la gestion de projet\nDes méthodologies modernes (agile, DevOps) qui valorisent le travail en équipe\nUne profession offrant de nombreuses possibilités d\'évolution ou d\'entrepreneuriat', NULL, NULL),
(18, 'ISEA', 'STI', 'M1,M2', 'ingenierie-systemes-electriques-automatises', 'Ingénierie des Systèmes Électriques Automatisés', 'Engineering of Automated Electrical Systems', 'Injeniera momba ny Rafitra Elektrika Mandrindra Tena', 'Formation avancée en automatisation industrielle, robotique et systèmes électriques intelligents.', 'Advanced training in industrial automation, robotics and smart electrical systems.', 'Fampiofanana mandroso momba ny fanaratana indostrialy, robotika ary rafitra elektrika mahay.', 'Ingénieur en systèmes automatisés\nIngénieur électrotechnicien\nTechnicien supérieur en automatisme industriel\nResponsable maintenance industrielle\nIngénieur en électronique de puissance', '', '', 'uploads/filiere_6a7cdb5123b94.jpg', 14, '2026-08-12 20:31:02', 'Cette filière combine l\'histoire du génie électrique et celle de l\'automatisation industrielle, deux disciplines qui ont convergé au cours du XXe siècle avec l\'introduction des automates programmables et des systèmes de contrôle-commande. Elle s\'est développée pour répondre aux besoins croissants d\'automatisation des processus industriels, alliant électrotechnique, électronique de puissance et informatique industrielle.', NULL, NULL, 'Une expertise très recherchée dans l\'industrie moderne et l\'automatisation\nDes débouchés dans la conception, l\'installation et la maintenance de systèmes automatisés\nUne formation pluridisciplinaire : électrotechnique, automatisme, informatique industrielle\nDes opportunités croissantes avec la modernisation des infrastructures industrielles\nUne discipline stratégique pour la compétitivité industrielle', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `filiere_blocks`
--

CREATE TABLE `filiere_blocks` (
  `id` int(11) NOT NULL,
  `filiere_id` int(11) NOT NULL,
  `block_type` enum('text','image') NOT NULL,
  `content_fr` text DEFAULT NULL,
  `content_en` text DEFAULT NULL,
  `content_mg` text DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `caption_fr` varchar(255) DEFAULT NULL,
  `caption_en` varchar(255) DEFAULT NULL,
  `caption_mg` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `filiere_blocks`
--

INSERT INTO `filiere_blocks` (`id`, `filiere_id`, `block_type`, `content_fr`, `content_en`, `content_mg`, `image_path`, `caption_fr`, `caption_en`, `caption_mg`, `display_order`, `created_at`) VALUES
(1, 1, 'image', NULL, NULL, NULL, 'uploads/filiere_photo_6a80908d66569_0.jpg', NULL, NULL, NULL, 2, '2026-08-15 21:19:10'),
(2, 1, 'text', 'L\'ingénieur en informatique est un professionnel scientifique polyvalent qui intervient dans de nombreux domaines liés à l\'informatique et au numérique : développement logiciel, systèmes d\'information, réseaux, informatique industrielle ou encore intelligence artificielle. C\'est l\'une des filières d\'ingénierie les plus recherchées, car elle ouvre sur une grande diversité de spécialisations. On distingue notamment les ingénieurs en systèmes d\'information, qui développent et maintiennent les logiciels organisationnels des entreprises ; les informaticiens de gestion, spécialisés dans les logiciels de gestion de données et les fonctions managériales ; les ingénieurs réseaux et télécoms, qui conçoivent les technologies de communication ; et les ingénieurs en informatique industrielle, qui interviennent sur la maintenance et l\'automatisation des équipements de production.', 'The computer engineer is a versatile scientific professional who intervenes in many fields related to computer science and digital technology: software development, information systems, networks, industrial computing or artificial intelligence. It is one of the most sought-after engineering streams, as it opens up a wide variety of specializations. These include information systems engineers, who develop and maintain companies\' organizational software; management computer scientists, who specialize in data management software and managerial functions; network and telecom engineers, who design communication technologies; and industrial computer engineers, who work on the maintenance and automation of production equipment.', 'Ny injeniera dia zavatra maro ara-tsiansa matihanina izay mandray anjara amin\'ny sehatra maro mifandray amin\'ny solosaina sy ny teknolojia nomerika: rindrambaiko fampandrosoana, rafitra vaovao, tambajotra, orinasa computing na solon-tsaina. Izany no iray amin\'ireo indrindra taorian\'ny injeniera renirano, satria manokatra ny isan-karazany ny specializations. Anisan\'izany ny rafitra vaovao injeniera, izay hampivelatra sy hihazona ny orinasa\' organizational rindrambaiko; fitantanana solosaina mpahay siansa, izay misahana manokana ny angon-drakitra fitantanana ny rindrambaiko sy ny pitantanana asa; tambajotra sy ny fifandraisan-davitra injeniera, izay famolavolana ny fifandraisana teknolojia; sy ny orinasa solosaina injeniera, izay miasa amin\'ny ny fikojakojana sy ny automation ny famokarana fitaovana.', NULL, NULL, NULL, NULL, 1, '2026-08-15 21:23:56'),
(3, 1, 'text', 'Réussir dans cette filière demande une sensibilité scientifique marquée et une véritable aptitude à l\'analyse. L\'ingénieur informatique doit faire preuve de créativité pour résoudre des problèmes complexes, mais aussi de rigueur et de méthode : en programmation comme en architecture système, l\'à-peu-près n\'a pas sa place. Le sens du relationnel et de la communication est tout aussi essentiel, car les projets informatiques impliquent souvent de travailler en équipe et d\'échanger avec des utilisateurs non spécialistes. Enfin, une curiosité constante pour les nouvelles technologies est indispensable dans un secteur qui évolue en permanence.', 'Success in this sector requires a marked scientific sensitivity and a real aptitude for analysis. The IT engineer must be creative to solve complex problems, but also rigorous and methodical: in programming as in system architecture, the near has no place. Equally essential is the sense of interpersonal and communication skills, as IT projects often involve working in teams and interacting with non-specialist users. Finally, a constant curiosity for new technologies is essential in a sector that is constantly evolving.', 'Ny fahombiazana amin\'ity sehatra ity dia mitaky fahaiza-manavaka ara-tsiansa sy fahaizana tena izy hanaovana fanadihadiana. Ny injeniera IT dia tsy maintsy mamorona hamahana olana sarotra, fa koa henjana sy metodika: amin\'ny fandaharana toy ny amin\'ny rafitra maritrano, ny akaiky tsy misy toerana. Ny tena ilaina ihany koa dia ny fahaiza-mifandray sy ny fifandraisana amin\'ny hafa, satria matetika ny tetikasa IT dia miara-miasa amin\'ny ekipa ary mifandray amin\'ny mpampiasa tsy manam-pahaizana. Farany, ny fahalianana tsy tapaka ho an\'ny teknolojia vaovao dia tena ilaina amin\'ny sehatra iray izay mivoatra hatrany.', NULL, NULL, NULL, NULL, 3, '2026-08-15 21:23:56'),
(4, 1, 'text', 'La formation d\'ingénieur en informatique s\'appuie généralement sur une base solide en mathématiques appliquées, en algorithmique et en langages de programmation (Python, Java, C++…), complétée par l\'étude des systèmes d\'exploitation, des bases de données et des réseaux. Les stages en entreprise, réalisés tout au long du cursus, permettent aux étudiants de se spécialiser progressivement dans des domaines porteurs comme l\'intelligence artificielle, la cybersécurité ou le développement web et mobile. C\'est précisément cette approche — associer une formation académique rigoureuse à une expérience pratique en entreprise — que la filière Génie Informatique de l\'ISSTM cherche à offrir à ses étudiants.', 'Computer engineering training is generally based on a solid foundation in applied mathematics, algorithms and programming languages (Python, Java, C++...), supplemented by the study of operating systems, databases and networks. Internships in companies, carried out throughout the course, allow students to gradually specialize in promising fields such as artificial intelligence, cybersecurity or web and mobile development. It is precisely this approach — combining rigorous academic training with practical experience in business — that ISSTM\'s Computer Engineering program seeks to offer its students.', 'Ny fampiofanana ara-tsolosaina dia mifototra amin\'ny fototra matanjaka amin\'ny matematika, ny algorithms sy ny programming languages (Python, Java, C++...), miaraka amin\'ny fianarana ny rafitra miasa, databases ary tambajotra. Ny fiofànana amin\'ny orinasa, tontosaina mandritra ny fianarana, dia ahafahan\'ireo mpianatra miandalana miompana amin\'ny fanomezan-toky toy ny fampivelarana ny sehatra fahaizana artifisialy, ny fiarovana an-tserasera na ny tranonkala sy ny fampivoarana finday. Izany indrindra io fomba fanao — henjana akademika Mitambatra fiofanana amin\'ny traikefa azo ampiharina eo amin\'ny raharaham-barotra — fa ny ISSTM ny Computer Engineering fandaharana mikatsaka ny hanatitra ny mpianatra.', NULL, NULL, NULL, NULL, 4, '2026-08-15 21:23:56'),
(5, 1, 'text', 'À titre de référence sur le marché européen (France), un ingénieur informatique débutant perçoit généralement entre 3 000 et 3 600 euros bruts mensuels selon sa spécialité, une rémunération qui peut atteindre 6 000 à 6 500 euros bruts en fin de carrière. Les salaires réels varient bien sûr fortement selon le pays, la taille de l\'entreprise et l\'expérience du candidat — mais ces chiffres illustrent la reconnaissance et la stabilité économique dont bénéficie généralement ce métier à l\'international.', 'As a reference on the European market (France), a beginner computer engineer generally receives between 3,000 and 3,600 euros gross monthly depending on his specialty, a remuneration that can reach 6,000 to 6,500 euros gross at the end of his career. Of course, real salaries vary greatly depending on the country, the size of the company and the candidate\'s experience — but these figures illustrate the recognition and economic stability that this profession generally enjoys internationally.', 'Ho fanondroana ny tsena Eoropeana (Frantsa), amin\'ny ankapobeny dia mandray eo anelanelan\'ny 3.000 ka hatramin\'ny 3600 euros eo ny injeniera solosaina iray arakaraky ny fahaizany manokana, karama iray izay mety hahatratra 6.000 hatramin\'ny 6.500 euros raha vantany vao vita ny asany. Mazava ho azy, miovaova be arakaraka ny firenena ny karama tena izy, ny haben\'ny orinasa sy ny traikefan\'ilay kandidà — saingy ireo tarehimarika ireo dia maneho ny fanekena sy ny fahamarinan-toerana ara-toekarena izay ankafizin\'ity asa ity eo amin\'ny sehatra iraisam-pirenena amin\'ny ankapobeny.', NULL, NULL, NULL, NULL, 5, '2026-08-15 21:23:56'),
(6, 1, 'text', 'Les diplômés en génie informatique exercent aussi bien dans des sociétés de services informatiques (SSII), des entreprises de tous secteurs, des bureaux d\'études que dans des unités de production industrielle. Cette polyvalence constitue l\'un des grands atouts de la filière : elle permet de s\'orienter vers le développement logiciel, l\'administration de systèmes, la cybersécurité, la gestion de projets numériques ou encore la recherche, selon ses affinités et les opportunités du marché de l\'emploi.', 'Graduates in computer engineering work in IT service companies (IT services companies), companies in all sectors, design offices and industrial production units. This versatility is one of the great assets of the sector: it makes it possible to move towards software development, systems administration, cybersecurity, digital project management or research, according to its affinities and the opportunities of the job market.', 'Nahazo diplaoma in solosaina injeniera miasa ao amin\'ny IT service orinasa (IZANY tolotra orinasa), orinasa amin\'ny sehatra rehetra, famolavolana birao sy ny orinasa famokarana vondrona. Ity fahafaha-manao ity dia iray amin\'ireo fananan\'ny sehatra: azo atao ny mandroso mankany amin\'ny fampandrosoana ny rindrambaiko, ny fitantanana ny rafitra, ny fiarovana an-tserasera, ny fitantanana tetik\'asa nomerika na ny fikarohana, araka ny fikambanana sy ny fahafaha-manao ny tsenan\'ny asa.', NULL, NULL, NULL, NULL, 6, '2026-08-15 21:23:56'),
(9, 1, 'text', 'Le cursus se structure classiquement en trois années de licence, consacrées aux fondamentaux (algorithmique, programmation, mathématiques, bases de données, systèmes), suivies de deux années de master permettant une spécialisation progressive vers l\'intelligence artificielle, la cybersécurité, le développement web/mobile ou l\'administration système. Des projets pratiques et des stages en entreprise jalonnent chaque année du parcours.', 'The curriculum is classically structured in three years of bachelor\'s degree, devoted to fundamentals (algorithmic, programming, mathematics, databases, systems), followed by two years of master\'s degree allowing a progressive specialization towards artificial intelligence, cybersecurity, web/mobile development or system administration. Practical projects and internships in companies punctuate each year of the course.', 'Ny fandaharam-pianarana dia Classically narafitra tamin\'ny telo taona ny mari-pahaizana licence, natokana ho an\'ny fototra (algorithmic, fandaharana, Matematika, angona, rafitra), arahin\'ny roa taona ny mari-pahaizana tompony mamela ny miandalana fahaizana artifisialy specialization, cybersecurity, tranonkala/finday fampandrosoana na ny rafitra fitantanana. Tetikasa sy fiofanana arak\'asa any amin\'ny orinasa marim-potoana isan-taona ny mazava ho azy.', NULL, NULL, NULL, NULL, 7, '2026-08-15 21:31:33'),
(10, 1, 'text', 'Pour réussir dans cette filière, il est conseillé de pratiquer la programmation en dehors des cours (projets personnels, plateformes d\'exercices en ligne), de participer à des projets de groupe pour développer ses compétences collaboratives, et de rester en veille active sur les nouvelles technologies, un secteur qui évolue extrêmement vite.', 'To succeed in this field, it is advisable to practice programming outside of classes (personal projects, online exercise platforms), to participate in group projects to develop collaborative skills, and to stay actively on the lookout for new technologies, a sector that is evolving extremely quickly.', 'Mba hahombiazan\'ity sehatra ity dia tsara ny mampihatra ny fandaharana ivelan\'ny kilasy (tetikasa manokana, sehatra fampiasana an-tserasera), mba handraisana anjara amin\'ny tetikasan\'ny vondrona hampivelatra fahaiza-manao ifarimbonana, ary ho mavitrika amin\'ny fijerena teknolojia vaovao, sehatra izay mivoatra haingana dia haingana.', NULL, NULL, NULL, NULL, 8, '2026-08-15 21:31:33'),
(11, 1, 'text', 'Après quelques années d\'expérience, un ingénieur informatique peut évoluer vers des postes à responsabilités : chef de projet, architecte logiciel, responsable des systèmes d\'information, ou encore se spécialiser davantage dans des domaines de pointe comme l\'intelligence artificielle ou la cybersécurité. L\'entrepreneuriat (création de startup technologique) est également une voie fréquemment empruntée par les diplômés de cette filière.', 'After a few years of experience, an IT engineer can progress to positions of responsibility: project manager, software architect, information systems manager, or specialize more in cutting-edge fields such as artificial intelligence or cybersecurity. Entrepreneurship (creation of technological startups) is also a path frequently taken by graduates of this sector.', 'Taorian\'ny taona vitsivitsy ny traikefa, ny injeniera IT afaka mandroso amin\'ny toerana ny andraikitra: tetikasa mpitantana, rindrambaiko mpanao mari-trano, rafitra vaovao mpitantana, na misahana bebe kokoa eo amin\'ny fanapahana-lelan saha toy ny solon-tsaina na ny cybersecurity. Ny fandraharahana (famoronana teknolojia startups) ihany koa dia lalana iray matetika nalain\'ireo nahazo diplaoma tao amin\'io sehatra io.', NULL, NULL, NULL, NULL, 9, '2026-08-15 21:31:33'),
(12, 2, 'text', 'Les étudiants en génie biomédical acquièrent une double compétence rare : une solide culture scientifique en électronique, en informatique et en mécanique, associée à des notions de physiologie et d\'anatomie indispensables pour comprendre le fonctionnement du corps humain. Le programme couvre la conception de dispositifs médicaux, l\'instrumentation biomédicale, le traitement du signal (ECG, EEG, imagerie) et les normes de sécurité et de qualité propres au secteur de la santé.', 'Biomedical engineering students acquire a rare double skill: a solid scientific culture in electronics, computer science and mechanics, combined with notions of physiology and anatomy essential to understand the functioning of the human body. The programme covers medical device design, biomedical instrumentation, signal processing (ECG, EEG, imaging) and health sector-specific safety and quality standards.', 'Mpianatry ny injeniera biomedical fahaizana roa tsy fahita firy: mafy orina siansa kolontsaina amin\'ny fitaovana elektronika, informatika sy ny milina, miaraka hevitra ny physiology sy Anatomy tena ilaina mba hahatakatra ny fiasan\'ny ny vatan\'olombelona. Ny fandaharanasa dia mirakitra ny famolavolana fitaovana ara-pahasalamana, ny fitaovana biomedical, ny fanodinana ny signal (ECG, EEG, imaging) ary ny fenitra ara-pahasalamana manokana sy ny fenitra.', NULL, NULL, NULL, NULL, 1, '2026-08-15 21:31:33'),
(13, 2, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c55e7c30.jpg', NULL, NULL, NULL, 2, '2026-08-15 21:31:33'),
(14, 2, 'text', 'Cette filière convient aux étudiants rigoureux, minutieux et sensibles aux enjeux humains et sanitaires. La précision est essentielle : un appareil médical mal calibré peut avoir des conséquences directes sur la santé des patients. Un bon relationnel est également utile, car l\'ingénieur biomédical travaille souvent en lien avec le personnel médical et les fabricants d\'équipements.', 'This course is suitable for students who are rigorous, meticulous and sensitive to human and health issues. Accuracy is essential: a poorly calibrated medical device can have direct consequences on the health of patients. Good interpersonal skills are also useful, as the biomedical engineer often works in conjunction with medical staff and equipment manufacturers.', 'Mazava ho azy fa mety ho an\'ny mpianatra izay henjana, tena nitandrina sy mora tohina amin\'ny olombelona sy ny fahasalamana olana. araka ny marina dia tena ilaina: ny zara calibrated fitaovana ara-pitsaboana mety hisy vokany mivantana eo amin\'ny fahasalamana ny marary. Tsara fifandraisana amin\'ny fahaiza-manao ihany koa ilaina, toy ny biomedical injeniera matetika miasa miaraka amin\'ny mpitsabo mpiasa sy ny fitaovana mpanamboatra.', NULL, NULL, NULL, NULL, 3, '2026-08-15 21:31:33'),
(15, 2, 'text', 'Le cursus se structure classiquement en trois années de licence (bases scientifiques : électronique, informatique, biologie) suivies de deux années de master de spécialisation en instrumentation médicale, imagerie ou gestion des équipements hospitaliers. Des stages en milieu hospitalier ou en entreprise de dispositifs médicaux jalonnent la formation.', 'The curriculum is classically structured into three years of bachelor\'s degree (scientific bases: electronics, computer science, biology) followed by two years of master\'s degree specializing in medical instrumentation, imaging or management of hospital equipment. Internships in hospitals or in medical device companies punctuate the training.', 'Ny fandaharam-pianarana dia Classically narafitra ho telo taona ny mari-pahaizana licence (siansa faladiany: elektronika, solosaina siansa, biolojia) nanaraka ny roa taona ny mari-pahaizana maîtrise manokana amin\'ny fitaovana ara-pitsaboana, fitarafana na ny fitantanana ny hopitaly fitaovana. Fiofanana any amin\'ny hopitaly na eo amin\'ny fitaovana ara-pitsaboana orinasa punctuate ny fampiofanana.', NULL, NULL, NULL, NULL, 4, '2026-08-15 21:31:33'),
(16, 2, 'text', 'Il est recommandé de consolider tôt ses bases en électronique analogique et numérique, de s\'intéresser activement aux nouvelles technologies médicales (télémédecine, objets connectés de santé) et de saisir toutes les occasions de stage en environnement hospitalier pour comprendre les contraintes réelles du terrain.', 'It is recommended to consolidate your bases in analog and digital electronics early, to take an active interest in new medical technologies (telemedicine, connected health objects) and to seize all internship opportunities in a hospital environment to understand the real constraints of the field.', 'Amporisihina ny hanamafisana orina ny fototrao amin\'ny fitaovana analoga sy elektronika nomerika aloha, mba handraisana fahalianana mavitrika amin\'ny teknolojia ara-pitsaboana vaovao (telemedicine, mifandray amin\'ny fahasalamana) ary haka ny fahafaha-manao rehetra any amin\'ny toeram-pitsaboana mba hahatakarana ny tena faneriterena eny an-kianja.', NULL, NULL, NULL, NULL, 5, '2026-08-15 21:31:33'),
(17, 3, 'text', 'Cette filière hybride forme des ingénieurs à la fois capables de concevoir des circuits électroniques (cartes, capteurs, microcontrôleurs) et de développer les logiciels qui les pilotent. Le programme couvre l\'électronique analogique et numérique, la programmation embarquée, les systèmes microprocesseurs et les bases des réseaux et objets connectés.', 'This hybrid sector trains engineers who are both capable of designing electronic circuits (cards, sensors, microcontrollers) and developing the software that drives them. The program covers analog and digital electronics, embedded programming, microprocessor systems, and the basics of networks and connected objects.', 'Io sehatra mifangaro Mampianatra injeniera izay samy afaka planina elektronika faritra (karatra, Sela Mpandray Hafanana, microcontrollers) sy ny fampandrosoana ny rindrambaiko izay mitarika azy ireo. Ny fandaharana mandrakotra Analog sy nomerika elektronika, nandinika lalina famolavolavolana, [object Window], ary ny fototra ao amin\'ny tambajotra sy ny zavatra mifandray.', NULL, NULL, NULL, NULL, 1, '2026-08-15 21:31:33'),
(18, 3, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c5f5200e.jpg', NULL, NULL, NULL, 2, '2026-08-15 21:31:33'),
(19, 3, 'text', 'Un bon équilibre entre goût pour la manipulation concrète (montage de circuits, tests matériels) et rigueur logique en programmation est un atout majeur. La patience et la méthode sont essentielles, car le débogage d\'un système embarqué demande souvent de vérifier autant le matériel que le logiciel.', 'A good balance between taste for concrete manipulation (circuit assembly, hardware tests) and logical rigor in programming is a major asset. Patience and method are essential, as debugging an embedded system often requires checking both hardware and software.', 'Ny tsara ny fifandanjana eo amin\'ny fanandramana ho an\'ny simenitra manipulation (faritra fiangonana, fitaovana fitsapana) sy ny lojika henjana amin\'ny fandaharana dia tombony lehibe. Faharetana sy ny fomba tena ilaina, toy ny debugging ny nandinika lalina matetika rafitra mitaky ny fanamarinana na ny fitaovana sy ny rindrambaiko.', NULL, NULL, NULL, NULL, 3, '2026-08-15 21:31:33'),
(20, 3, 'text', 'La formation alterne cours théoriques et travaux pratiques en laboratoire d\'électronique, avec une part croissante de projets de conception au fil des années. Les niveaux master permettent de se spécialiser vers les systèmes embarqués, l\'IoT ou l\'électronique de puissance.', 'The training alternates between theoretical courses and practical work in the electronics laboratory, with an increasing share of design projects over the years. The master levels allow you to specialize in embedded systems, IoT or power electronics.', 'Ny fampiofanana hafa eo amin\'ny teorika taranja sy ny asa azo ampiharina ao amin\'ny elektronika laboratoara, miaraka amin\'ny fitomboan\'ny anjara amin\'ny famolavolana tetikasa nandritra ny taona maro. Ny tompony ambaratonga mamela anao manokana amin\'ny nandinika lalina rafitra, IoT na ny hery elektronika.', NULL, NULL, NULL, NULL, 4, '2026-08-15 21:31:33'),
(21, 3, 'text', 'Multipliez les projets personnels (petits montages, cartes Arduino ou équivalents) pour développer une intuition pratique en complément des cours. La double compétence matériel/logiciel se construit avant tout par la pratique répétée.', 'Multiply personal projects (small montages, Arduino boards or equivalent) to develop a practical intuition in addition to the courses. Dual hardware/software competence is built primarily through repeated practice.', 'Mampitombo tetikasa manokana (kely montages, Arduino zana-kazo na mitovy) mba hampitombo ny azo ampiharina intuition ankoatra ny antokony. Dual fitaovana/rindrambaiko fahaizana naorina voalohany indrindra amin\'ny alalan\'ny miverimberina.', NULL, NULL, NULL, NULL, 5, '2026-08-15 21:31:33'),
(22, 4, 'text', 'Le programme couvre les circuits électriques, les machines électriques (moteurs, transformateurs, générateurs), la distribution et le transport de l\'énergie, ainsi que les bases de l\'automatisme et des énergies renouvelables. Les étudiants apprennent à dimensionner des installations électriques et à en assurer la sécurité.', NULL, 'Ny fandaharana manarona herinaratra faritra, herinaratra milina (Motors, mpanova, gropy), fizarana angovo sy ny fitaterana, ary koa ny fototry ny automatique sy ny angovo azo havaozina. Ny mpianatra dia mianatra ny fomba fandrefesana ny fitaovana elektrika ary miantoka ny fiarovana azy ireo.', NULL, NULL, NULL, NULL, 1, '2026-08-15 21:31:33'),
(23, 4, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c6a870b8.jpg', NULL, NULL, NULL, 2, '2026-08-15 21:31:33'),
(24, 4, 'text', 'La filière demande de la rigueur (le respect des normes de sécurité électrique est non négociable), un bon niveau en mathématiques et en physique, ainsi qu\'un intérêt pour le travail de terrain autant que pour le calcul théorique.', NULL, 'Ny sehatra dia mitaky henjana (fanarahana herinaratra fiarovana ny fenitra dia tsy azo iadian-kevitra), tsara lenta amin\'ny matematika sy fizika, ary koa ny liana amin\'ny saha miasa ary koa ny teorika kajy.', NULL, NULL, NULL, NULL, 3, '2026-08-15 21:31:33'),
(25, 4, 'text', 'Après un socle commun en électrotechnique durant la licence, le master permet de se spécialiser en distribution d\'énergie, en énergies renouvelables ou en installations industrielles, avec des travaux pratiques réguliers sur des équipements réels.', NULL, 'Taorian\'ny iombonana fototra amin\'ny herinaratra injeniera nandritra ny mari-pahaizana licence, ny mari-pahaizana maîtrise dia mamela anao manokana amin\'ny angovo fizarana, angovo azo havaozina na ny orinasa fametrahana, amin\'ny asa mahomby tsy tapaka eo amin\'ny tena fitaovana.', NULL, NULL, NULL, NULL, 4, '2026-08-15 21:31:33'),
(26, 4, 'text', 'Renforcez vos bases en mathématiques (équations différentielles, nombres complexes) qui sont omniprésentes en électrotechnique, et cherchez des stages en entreprise de distribution d\'énergie ou sur des chantiers d\'installation pour ancrer la théorie dans la pratique.', NULL, 'Manamafy orina ny fototry ny matematika (Differential mira, sarotra isa) izay hita na aiza na aiza ao amin\'ny herinaratra injeniera, ary mitady fiofanana amin\'ny fizarana angovo orinasa na ny fametrahana toerana anchor teoria amin\'ny fampiharana.', NULL, NULL, NULL, NULL, 5, '2026-08-15 21:31:33'),
(27, 5, 'text', 'Cette filière transversale forme à l\'optimisation des systèmes de production : gestion de la qualité, organisation des chaînes de production, logistique, gestion de projet et amélioration continue (Lean management). Les étudiants apprennent à analyser un processus industriel dans sa globalité pour en améliorer la performance.', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-15 21:31:33'),
(28, 5, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6d53d5a74.jpg', NULL, NULL, NULL, 2, '2026-08-15 21:31:33'),
(29, 5, 'text', 'Le génie industriel convient aux étudiants qui aiment autant la technique que l\'organisation et la gestion humaine. Un bon sens de la communication et de la coordination est essentiel, car l\'ingénieur industriel travaille souvent à l\'interface entre plusieurs équipes (production, qualité, logistique).', NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-08-15 21:31:33'),
(30, 5, 'text', 'Le cursus combine des enseignements techniques généraux avec des modules de gestion, de statistiques et de méthodes qualité. Les stages en entreprise industrielle sont particulièrement formateurs pour comprendre les réalités du terrain de production.', NULL, NULL, NULL, NULL, NULL, NULL, 4, '2026-08-15 21:31:33'),
(31, 5, 'text', 'Développez votre capacité d\'analyse et de synthèse : le génie industriel demande de savoir simplifier des systèmes complexes pour proposer des solutions concrètes et mesurables. La maîtrise d\'outils de gestion de projet est un plus très recherché.', NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-08-15 21:31:33'),
(32, 6, 'text', 'Le programme couvre la thermodynamique, la mécanique des fluides, les transferts de chaleur, ainsi que la conception et le dimensionnement de systèmes de chauffage, ventilation, climatisation et réfrigération. Les étudiants apprennent également les bases de l\'efficacité énergétique des bâtiments et procédés industriels.', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-15 21:31:33'),
(33, 6, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6ca91e9cf.jpg', NULL, NULL, NULL, 2, '2026-08-15 21:31:33'),
(34, 6, 'text', 'Un bon niveau en physique et en mathématiques appliquées est indispensable, tout comme une curiosité pour les enjeux énergétiques et environnementaux actuels, de plus en plus centraux dans ce secteur.', NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-08-15 21:31:33'),
(35, 6, 'text', 'La formation s\'appuie sur des cours théoriques solides en thermodynamique et mécanique des fluides, complétés par des travaux pratiques sur des installations de climatisation et de chauffage, puis une spécialisation en efficacité énergétique ou en procédés industriels.', NULL, NULL, NULL, NULL, NULL, NULL, 4, '2026-08-15 21:31:33'),
(36, 6, 'text', 'Suivez de près les évolutions réglementaires et technologiques liées à la transition énergétique : ce secteur évolue rapidement et les compétences en efficacité énergétique sont de plus en plus valorisées sur le marché du travail.', NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-08-15 21:31:33'),
(37, 7, 'text', 'Les étudiants apprennent à concevoir, dimensionner et superviser la construction de bâtiments et d\'infrastructures : résistance des matériaux, calcul de structures (béton armé, charpente), géotechnique, et gestion de chantier. Les outils numériques modernes comme la modélisation 3D (BIM) sont de plus en plus intégrés au cursus.', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-15 21:31:33'),
(38, 7, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6d02f219e.jpg', NULL, NULL, NULL, 2, '2026-08-15 21:31:33'),
(39, 7, 'text', 'La filière demande de la rigueur mathématique, un bon sens spatial et une capacité à travailler aussi bien sur plan qu\'en conditions réelles de chantier. Le leadership et la gestion d\'équipe sont des atouts précieux pour la supervision de travaux.', NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-08-15 21:31:33'),
(40, 7, 'text', 'Après des bases solides en résistance des matériaux et en calcul de structures durant la licence, le master permet de se spécialiser en bâtiment, travaux publics, ou hydraulique, avec des projets de conception et des stages sur chantier.', NULL, NULL, NULL, NULL, NULL, NULL, 4, '2026-08-15 21:31:33'),
(41, 7, 'text', 'Profitez de chaque stage sur chantier pour confronter la théorie à la réalité du terrain, et familiarisez-vous tôt avec les logiciels de conception assistée par ordinateur (CAO/BIM), devenus incontournables dans la profession.', NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-08-15 21:31:33'),
(42, 8, 'text', 'Cette filière forme à la gestion de l\'eau sous toutes ses formes : mécanique des fluides appliquée, dimensionnement de réseaux d\'irrigation et d\'adduction d\'eau, systèmes d\'assainissement, et conception d\'ouvrages hydrauliques comme les barrages ou les stations de pompage.', 'This sector provides training in water management in all its forms: applied fluid mechanics, sizing of irrigation and water supply networks, sanitation systems, and design of hydraulic structures such as dams or pumping stations.', NULL, NULL, NULL, NULL, NULL, 1, '2026-08-15 21:31:33'),
(43, 8, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6cb41aa1c.jpg', NULL, NULL, NULL, 2, '2026-08-15 21:31:33'),
(44, 8, 'text', 'Un bon niveau en mathématiques et en mécanique des fluides est essentiel, ainsi qu\'une sensibilité aux enjeux environnementaux liés à la gestion durable des ressources en eau, de plus en plus stratégiques face aux défis climatiques.', 'A good level in mathematics and fluid mechanics is essential, as well as sensitivity to environmental issues linked to the sustainable management of water resources, which are increasingly strategic in the face of climate challenges.', NULL, NULL, NULL, NULL, NULL, 3, '2026-08-15 21:31:33'),
(45, 8, 'text', 'La formation associe des bases en génie civil et en mécanique des fluides à des enseignements spécialisés sur les réseaux hydrauliques et l\'assainissement, complétés par des projets d\'application sur des cas concrets de gestion de l\'eau.', 'The training combines basics in civil engineering and fluid mechanics with specialized teaching on hydraulic networks and sanitation, supplemented by application projects on concrete cases of water management.', NULL, NULL, NULL, NULL, NULL, 4, '2026-08-15 21:31:33'),
(46, 8, 'text', 'Intéressez-vous aux enjeux locaux de gestion de l\'eau (irrigation agricole, accès à l\'eau potable) : ce sont des problématiques concrètes sur lesquelles vous pourrez appliquer directement vos compétences dès l\'entrée dans la vie professionnelle.', 'Take an interest in local water management issues (agricultural irrigation, access to drinking water): these are concrete issues to which you will be able to directly apply your skills upon entering professional life.', NULL, NULL, NULL, NULL, NULL, 5, '2026-08-15 21:31:33'),
(47, 9, 'text', 'Le programme combine des enseignements techniques (résistance des matériaux, réglementation du bâtiment) avec des compétences créatives : dessin technique, conception spatiale, et de plus en plus, modélisation 3D et outils de conception assistée par ordinateur.', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-15 21:31:33'),
(48, 9, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6cd5935b5.jpg', NULL, NULL, NULL, 2, '2026-08-15 21:31:33'),
(49, 9, 'text', 'Cette filière convient aux étudiants qui allient sensibilité artistique et rigueur technique. Une bonne vision spatiale et un goût prononcé pour le dessin et la conception sont des atouts majeurs pour réussir dans ce domaine.', NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-08-15 21:31:33'),
(50, 9, 'text', 'La formation alterne ateliers de conception, cours théoriques sur les matériaux et la réglementation, et projets pratiques de conception de bâtiments, avec une place grandissante accordée à la construction durable et écologique.', NULL, NULL, NULL, NULL, NULL, NULL, 4, '2026-08-15 21:31:33'),
(51, 9, 'text', 'Constituez un portfolio de vos projets dès le début de votre formation : c\'est un outil essentiel pour valoriser votre travail auprès des employeurs et affiner votre propre style de conception au fil du temps.', NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-08-15 21:31:33'),
(52, 14, 'text', 'Cette filière forme aux techniques de production du froid (réfrigération industrielle et domestique) et à la gestion de l\'énergie associée : thermodynamique appliquée, dimensionnement d\'installations frigorifiques, et optimisation énergétique des systèmes de conservation.', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-15 21:31:33'),
(53, 14, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb1c85620.jpg', NULL, NULL, NULL, 2, '2026-08-15 21:31:33'),
(54, 14, 'text', 'Un bon niveau en physique et en thermodynamique est nécessaire, ainsi qu\'un intérêt pour les enjeux liés à la conservation des denrées alimentaires et à l\'efficacité énergétique, deux thématiques stratégiques pour l\'économie locale.', NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-08-15 21:31:33'),
(55, 14, 'text', 'Le cursus combine des bases en thermodynamique et en électrotechnique avec des travaux pratiques sur des installations frigorifiques réelles, avant une spécialisation en froid industriel ou en efficacité énergétique.', NULL, NULL, NULL, NULL, NULL, NULL, 4, '2026-08-15 21:31:33'),
(56, 14, 'text', 'Ce secteur est directement lié à des besoins concrets (agroalimentaire, logistique) : privilégiez les stages en entreprise pour comprendre les contraintes pratiques de maintenance et d\'exploitation des installations frigorifiques.', NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-08-15 21:31:33'),
(57, 15, 'text', 'Le programme forme à l\'automatisation des processus industriels : automates programmables, capteurs et actionneurs, supervision de systèmes de production, et bases de la robotique industrielle, à la croisée de l\'électronique et de l\'informatique.', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-15 21:31:33'),
(58, 15, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb29ea27c.png', NULL, NULL, NULL, 2, '2026-08-15 21:31:33'),
(59, 15, 'text', 'Cette filière convient aux étudiants curieux de comprendre le fonctionnement des lignes de production modernes, alliant logique de programmation et compréhension des systèmes physiques (capteurs, moteurs, vérins).', NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-08-15 21:31:33'),
(60, 15, 'text', 'La formation combine cours théoriques et travaux pratiques sur des automates et des maquettes industrielles, avec une spécialisation progressive vers l\'informatique industrielle, la robotique ou la supervision de systèmes automatisés.', NULL, NULL, NULL, NULL, NULL, NULL, 4, '2026-08-15 21:31:33'),
(61, 15, 'text', 'Développez une bonne compréhension pratique des automates programmables (API) : c\'est l\'outil central de cette filière, et une bonne maîtrise pratique fait souvent la différence à l\'embauche.', NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-08-15 21:31:33'),
(62, 16, 'text', 'Les étudiants apprennent à concevoir, déployer et sécuriser des infrastructures de communication : réseaux filaires et sans fil, protocoles de transmission de données, téléphonie, et bases de la cybersécurité des réseaux.', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-15 21:31:33'),
(63, 16, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb36c6676.jpg', NULL, NULL, NULL, 2, '2026-08-15 21:31:33'),
(64, 16, 'text', 'Un bon niveau en mathématiques et en logique est utile, ainsi qu\'une curiosité pour les technologies de connectivité en constante évolution (fibre optique, réseaux mobiles, objets connectés).', NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-08-15 21:31:33'),
(65, 16, 'text', 'La formation couvre les fondamentaux des réseaux et des télécommunications avant de permettre une spécialisation en administration réseau, sécurité informatique ou ingénierie télécoms, avec des travaux pratiques sur des équipements réels.', NULL, NULL, NULL, NULL, NULL, NULL, 4, '2026-08-15 21:31:33'),
(66, 16, 'text', 'Obtenir des certifications reconnues dans le domaine des réseaux en complément de la formation académique est un excellent moyen de renforcer son profil auprès des employeurs de ce secteur très normé.', NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-08-15 21:31:33'),
(67, 17, 'text', 'Cette filière se concentre sur la conception, le développement et la gestion de projets logiciels de qualité : méthodologies de développement (agile, DevOps), architecture logicielle, tests et assurance qualité, et gestion de projets informatiques.', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-15 21:31:33'),
(68, 17, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb45b9eea.jpg', NULL, NULL, NULL, 2, '2026-08-15 21:31:33'),
(69, 17, 'text', 'Le génie logiciel convient aux étudiants organisés, aimant travailler en équipe et capables de penser un projet dans sa globalité, au-delà du simple code : qualité, maintenabilité, collaboration.', NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-08-15 21:31:33'),
(70, 17, 'text', 'La formation s\'appuie sur des projets de groupe réguliers simulant des conditions réelles de développement logiciel, avec un apprentissage progressif des méthodologies professionnelles utilisées en entreprise.', NULL, NULL, NULL, NULL, NULL, NULL, 4, '2026-08-15 21:31:33'),
(71, 17, 'text', 'Participez à des projets collaboratifs (open source, projets d\'école en équipe) pour développer très tôt de bonnes pratiques de travail en équipe, aussi importantes que les compétences techniques pures dans ce métier.', NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-08-15 21:31:33'),
(72, 18, 'text', 'Le programme combine électrotechnique, automatisme et informatique industrielle pour former des ingénieurs capables de concevoir et maintenir des systèmes de production entièrement automatisés, incluant la programmation d\'automates et la supervision à distance.', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-08-15 21:31:33'),
(73, 18, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb5123b94.jpg', NULL, NULL, NULL, 2, '2026-08-15 21:31:33'),
(74, 18, 'text', 'Cette filière demande une bonne polyvalence technique (électricité, mécanique, informatique) ainsi qu\'une capacité à résoudre des pannes complexes impliquant plusieurs systèmes interconnectés.', NULL, NULL, NULL, NULL, NULL, NULL, 3, '2026-08-15 21:31:33'),
(75, 18, 'text', 'La formation associe des bases solides en électrotechnique à des enseignements spécialisés en automatisme et en informatique industrielle, avec de nombreux travaux pratiques sur des systèmes automatisés réels.', NULL, NULL, NULL, NULL, NULL, NULL, 4, '2026-08-15 21:31:33'),
(76, 18, 'text', 'La polyvalence est votre meilleur atout dans cette filière : ne négligez aucun des trois piliers (électrique, automatisme, informatique), car c\'est leur combinaison qui fait la valeur de ce profil sur le marché du travail.', NULL, NULL, NULL, NULL, NULL, NULL, 5, '2026-08-15 21:31:33'),
(77, 1, 'text', 'Au-delà de la simple programmation, le génie informatique est une discipline qui embrasse la conception de systèmes complexes dans leur globalité : matériel, logiciel, réseaux et données s\'articulent pour répondre à des besoins concrets, qu\'il s\'agisse d\'automatiser une tâche administrative, de sécuriser une transaction bancaire ou de faire dialoguer des milliers d\'objets connectés en temps réel. Un ingénieur informatique n\'est donc pas seulement un développeur : c\'est un architecte de solutions capable de comprendre un problème métier, de le traduire en spécifications techniques, puis de construire un système fiable, performant et évolutif. Cette vision d\'ensemble, qui dépasse largement l\'écriture de code, est précisément ce qui distingue un ingénieur formé en école d\'un simple codeur autodidacte, et c\'est cette polyvalence que la filière cherche à transmettre à ses étudiants dès les premières années.', 'Beyond simple programming, computer engineering is a discipline that embraces the design of complex systems as a whole: hardware, software, networks and data are articulated to meet concrete needs, whether it is to automate an administrative task, secure a bank transaction or have thousands of connected objects interact in real time. An IT engineer is therefore not just a developer: he is a solutions architect capable of understanding a business problem, translating it into technical specifications, and then building a reliable, high-performance and scalable system. This overall vision, which goes well beyond writing code, is precisely what distinguishes a school-trained engineer from a simple self-taught coder, and it is this versatility that the course seeks to transmit to its students from the first years.', 'Ankoatra ny tsotra famolavolavolana, solosaina injeniera dia famaizana izay manaiky ny famolavolana ny rafitra sarotra iray manontolo: fitaovana, rindrambaiko, tambajotra sy ny angona dia tononina mba hihaona mivaingana ilaina, na dia ny automate iray panjakana asa, azo antoka ny banky transaction na manana an\'arivony mifandray zavatra mifandray mifandray amin\'ny fotoana tena izy. Iray IZANY injeniera dia tsy hoe fotsiny developer: izy dia vahaolana architect afaka ny fahatakarana ny raharaham-barotra olana, mandika azy ho fepetra arahana ara-teknika, ary avy eo dia fanorenana azo antoka, avo-fampisehoana sy ny scalable rafitra. Ity vina manontolo ity, izay mandeha lavitra noho ny fanoratana kaody, dia ny mampiavaka ny injenieran\'ny sekoly iray nofanina tamin\'ny kaody tsotra iray ampianarina tena izy, ary izany no fahafaha-manao fa ny fianarana dia mikasa ny hamindra ny mpianatra ao aminy avy amin\'ireo taona voalohany.', NULL, NULL, NULL, NULL, 10, '2026-08-15 21:52:08'),
(78, 1, 'text', 'L\'histoire de l\'informatique est jalonnée de ruptures technologiques majeures qui ont chacune redéfini le métier d\'ingénieur : les débuts avec les calculateurs à lampes des années 1940, la révolution du transistor puis du circuit intégré dans les années 1960-70 qui a rendu les ordinateurs accessibles aux entreprises, l\'avènement du micro-ordinateur personnel dans les années 1980, puis l\'explosion d\'Internet dans les années 1990 qui a transformé un outil de calcul isolé en réseau mondial interconnecté. Les années 2000 et 2010 ont ensuite vu émerger le cloud computing, les smartphones et les réseaux sociaux, avant que la décennie actuelle ne soit marquée par l\'intelligence artificielle générative. Chacune de ces vagues a créé de nouveaux métiers et rendu obsolètes d\'anciennes compétences, illustrant à quel point ce secteur récompense la capacité d\'apprentissage continu plus que la simple accumulation de connaissances figées.', 'The history of computing is marked by major technological breakthroughs that have each redefined the engineering profession: the beginnings with lamp computers in the 1940s, the revolution of the transistor and then the integrated circuit in the 1960s-70s that made computers accessible to companies, the advent of the personal microcomputer in the 1980s, then the explosion of the Internet in the 1990s that transformed an isolated computing tool into an interconnected global network. The 2000s and 2010s saw the emergence of cloud computing, smartphones and social networks, before the current decade was marked by generative artificial intelligence. Each of these waves has created new jobs and made old skills obsolete, illustrating how this sector rewards the capacity for continuous learning more than just the accumulation of frozen knowledge.', 'Ny tantaran\'ny computing dia voamariky ny lehibe teknolojia breakthroughs izay samy redefined ny injeniera asa: ny fiandohan\'ny amin\'ny jiro ordinatera ao amin\'ny taona 1940, ny revolisiona ny transistor ary avy eo ny Integrated faritra ao amin\'ny 1960s-70s izay nanao solosaina azon\'ny orinasa, ny fahatongavan\'ny ny solosaina manokana ao amin\'ny taona 1980s, avy eo ny fipoahana ny Aterineto ao amin\'ny taona 1990 izay nanova ny mitoka-monina computing fitaovana ho interconnected tambajotra manerantany. Tamin\'ny taona 2000 sy 2010 no nahitana ny firongatry ny rahona computing, finday avo lenta sy ny tambajotra sosialy, talohan\'ny folo taona amin\'izao fotoana izao dia nomarihin\'ny solon-tsaina niteraka. Nahatonga asa vaovao ny onja tsirairay avy ary nahatonga ny fahaizana efa tranainy ho lany andro, maneho ny fomba nahazoan\'ity sehatra ity valisoa amin\'ny fahaizana mianatra tsy an-kiato mihoatra noho ny fivangongoan\'ny fahalalana mangatsiaka.', NULL, NULL, NULL, NULL, 11, '2026-08-15 21:52:09'),
(79, 1, 'text', 'Les compétences acquises en génie informatique trouvent des applications concrètes dans pratiquement tous les secteurs économiques : la banque et la finance pour la sécurisation des transactions et la détection de fraude, la santé pour la gestion des dossiers médicaux et l\'aide au diagnostic, l\'agriculture pour l\'optimisation des rendements grâce à des capteurs connectés, l\'éducation pour les plateformes d\'apprentissage en ligne, ou encore l\'administration publique pour la dématérialisation des services aux citoyens. À Madagascar comme ailleurs, la transformation numérique des entreprises et des institutions crée une demande croissante pour des professionnels capables de concevoir des systèmes d\'information adaptés aux réalités locales, qu\'il s\'agisse de connectivité limitée, de contraintes budgétaires ou de besoins spécifiques en matière de paiement mobile et de services financiers numériques.', 'The skills acquired in computer engineering find concrete applications in virtually all economic sectors: banking and finance for the security of transactions and the detection of fraud, health for the management of medical records and diagnostic assistance, agriculture for the optimization of yields thanks to connected sensors, education for e-learning platforms, or public administration for the dematerialization of services to citizens. In Madagascar as elsewhere, the digital transformation of businesses and institutions is creating a growing demand for professionals capable of designing information systems adapted to local realities, whether limited connectivity, budgetary constraints or specific needs in terms of mobile payment and digital financial services.', 'Ny fahaiza-manao azo amin\'ny solosaina injeniera mahita mivaingana fampiharana in saika sehatra ara-toekarena rehetra: ny banky sy ny fitantanam-bola ho amin\'ny fiarovana ny varotra sy ny mamantatra ny hosoka, ny fahasalamana ho an\'ny fitantanana ny firaketana an-tsoratra ara-pitsaboana sy ny fizahana aretina fanampiana, ny fambolena ho fanatsarana ny vokatra noho ny mifandray Sela Mpandray Hafanana, ny fanabeazana ho an\'ny e-fianarana sehatra, na ny fitantanam-bahoaka ho amin\'ny fampihenana ny asa ho an\'ny olom-pirenena. Any Madagasikara tahaka ny any an-kafa, mamorona tinady tsy mitsaha-mitombo ho an\'ireo matianina afaka mamolavola rafitra fampahalalam-baovao mifanaraka amin\'ny zavamisy eo an-toerana ny fanovàna nomerika ny fandraharahana sy ny andrim-panjakana, na voafetra aza ny fifandraisana, ny teritery amin\'ny tetibola na filàna manokana eo amin\'ny fandoavana finday sy ny tolotra ara-bola nomerika.', NULL, NULL, NULL, NULL, 12, '2026-08-15 21:52:11'),
(80, 1, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c495a5a0.jpg', NULL, NULL, NULL, 13, '2026-08-15 21:52:11');
INSERT INTO `filiere_blocks` (`id`, `filiere_id`, `block_type`, `content_fr`, `content_en`, `content_mg`, `image_path`, `caption_fr`, `caption_en`, `caption_mg`, `display_order`, `created_at`) VALUES
(81, 1, 'text', 'Au quotidien, un ingénieur informatique manipule un éventail d\'outils qui évoluent constamment : des environnements de développement intégrés (IDE) comme Visual Studio Code ou IntelliJ, des systèmes de gestion de versions comme Git pour collaborer sur du code, des conteneurs comme Docker pour déployer des applications de façon reproductible, ou encore des plateformes cloud comme AWS, Azure ou Google Cloud pour héberger des services à grande échelle. La maîtrise de langages de programmation variés (Python pour la data et l\'IA, JavaScript pour le web, Java ou C# pour les systèmes d\'entreprise) reste centrale, mais elle s\'accompagne désormais d\'une bonne compréhension des méthodologies agiles, des outils d\'intégration continue et, de plus en plus, des assistants de programmation basés sur l\'intelligence artificielle qui transforment la manière même d\'écrire du code.', 'On a daily basis, an IT engineer handles a range of tools that are constantly evolving: integrated development environments (IDEs) like Visual Studio Code or IntelliJ, version management systems like Git to collaborate on code, containers like Docker to deploy applications in a reproducible way, or cloud platforms like AWS, Azure or Google Cloud to host services at scale. Mastery of various programming languages (Python for data and AI, JavaScript for the web, Java or C# for enterprise systems) remains central, but it is now accompanied by a good understanding of agile methodologies, continuous integration tools and, increasingly, artificial intelligence-based programming assistants that are transforming the very way code is written.', 'Amin\'ny andavanandro, ny injeniera IT dia mikarakara fitaovana maromaro izay mivoatra tsy tapaka: ny tontolon\'ny fampandrosoana (IDEs) toy ny Visual Studio Code na IntelliJ, ny rafitra fitantanana ny version toy ny GIT mba hiara-miasa amin\'ny code, ny kaontenera toy ny Docker mba hampiasa ny fampiharana amin\'ny fomba iray azo havaozina, na ny sehatra rahona toy ny AWS, Azure na Google Cloud mba hampiantrano tolotra amin\'ny ambaratonga. Mijanona ho ivon-toerana ny famolavolavolana fiteny samihafa (Python ho an\'ny angon-drakitra sy AI, JavaScript ho an\'ny tranonkala, Java na C# ho an\'ny rafitra orinasa), fa ankehitriny dia miaraka amin\'ny fahatakarana tsara ny fomba fiasa henjana, fitaovana fampidirana tsy tapaka, ary tsy mitsaha-mitombo ny isan\'ireo mpanampy amin\'ny fandaharana rindrambaiko izay manova ny fomba fanoratana ny fitsipi-pitenenana.', NULL, NULL, NULL, NULL, 14, '2026-08-15 21:52:12'),
(82, 1, 'text', 'La journée d\'un ingénieur informatique varie beaucoup selon son poste, mais elle combine généralement plusieurs temps forts : une réunion d\'équipe matinale pour faire le point sur l\'avancement des tâches, des phases de développement ou de résolution de bugs en concentration, des échanges avec des collègues pour résoudre un problème technique complexe, et des moments de revue de code où l\'on relit et commente le travail des autres pour en garantir la qualité. À cela s\'ajoutent régulièrement des phases de veille technologique, de formation continue ou de tests, car livrer un logiciel fiable demande bien plus que d\'écrire des lignes de code : cela implique de vérifier, documenter et anticiper les usages réels que les utilisateurs feront du système.', 'An IT engineer\'s day varies a lot depending on their position, but it usually combines several highlights: a morning team meeting to take stock of the progress of tasks, phases of development or bug fixing in concentration, exchanges with colleagues to solve a complex technical problem, and moments of code review where we reread and comment on the work of others to ensure quality. To this are regularly added phases of technological monitoring, continuous training or testing, because delivering reliable software requires much more than writing lines of code: it involves verifying, documenting and anticipating the real uses that users will make of the system.', 'Ny IT ny andro injeniera Miovaova be dia be arakaraka ny toerana, fa matetika Mitambatra maro ny zava-nisongadina: maraina ekipa fivoriana mba haka tahiry ny fandrosoan\'ny asa, dingana ny fampandrosoana na ny bibikely manamboatra ny fifantohana, fifanakalozana amin\'ny mpiara-miasa mba hamahana ny olana ara-teknika sarotra, sy ny fotoana ny fehezan-dalàna famerenana izay reread sy ny fanehoan-kevitra momba ny asan\'ny hafa mba hahazoana antoka tsara. Ho azy io dia tsy tapaka koa ny dingana fanaraha-maso ny teknolojia, mitohy fiofanana na ny fizahan-toetra, satria manome azo antoka rindrambaiko mitaky zavatra mihoatra noho ny fanoratana andalana ny fehezan-dalàna: Tafiditra amin\'izany ny fanamarinana, ny fandraketana sy ny mialoha ny tena fampiasana ny mpampiasa dia manao ny rafitra.', NULL, NULL, NULL, NULL, 15, '2026-08-15 21:52:14'),
(83, 1, 'text', 'Sur le plan technique, la formation développe une maîtrise progressive de plusieurs piliers : l\'algorithmique et les structures de données, qui permettent de résoudre des problèmes de façon efficace ; les bases de données relationnelles et non relationnelles, pour stocker et interroger l\'information de façon fiable ; les réseaux informatiques, indispensables pour comprendre comment les systèmes communiquent entre eux ; et les principes de génie logiciel, qui structurent la façon de concevoir des applications maintenables sur le long terme. Ces compétences techniques ne sont pas cloisonnées : elles s\'articulent entre elles dans des projets concrets où l\'étudiant apprend à faire des choix d\'architecture cohérents plutôt qu\'à simplement appliquer des recettes apprises par cœur.', 'Technically, the training develops a progressive mastery of several pillars: algorithmics and data structures, which allow problems to be solved effectively; relational and non-relational databases, to store and query information reliably; computer networks, which are essential to understand how systems communicate with each other; and software engineering principles, which structure how to design long-term maintainable applications. These technical skills are not compartmentalized: they are articulated among themselves in concrete projects where the student learns to make coherent architectural choices rather than simply applying recipes learned by rote.', 'Ara-teknika, ny fampiofanana dia mampivelatra ny miandalana fifehezana ny andry maro: algorithmics sy ny angon-drakitra rafitra, izay mamela ny olana mba ho voavaha amim-pahombiazana; relational sy tsy ara-pivavahana angona, mba hitahiry sy nanontany vaovao reliably; solosaina tambajotra, izay tena ilaina mba hahatakatra ny fomba rafitra mifandray amin\'ny tsirairay; sy ny rindrambaiko injeniera fitsipika, izay rafitra ny fomba mamaritra maharitra maintainable fampiharana. Ireo fahaiza-manao ara-teknika ireo dia tsy natambatra: Voakambana amin\'ny tetikasa mivaingana izy ireo izay ianaran\'ny mpianatra ny manao safidy ara-javakanto mirindra izy ireo fa tsy ny fampiharana fomba fikarakaràna fotsiny ihany.', NULL, NULL, NULL, NULL, 16, '2026-08-15 21:52:16'),
(84, 1, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c495a5a0.jpg', NULL, NULL, NULL, 17, '2026-08-15 21:52:16'),
(85, 1, 'text', 'Contrairement à une idée reçue, le génie informatique n\'est pas un métier solitaire pratiqué derrière un écran : la grande majorité des projets logiciels se construisent en équipe, avec des développeurs, des designers, des chefs de projet et des utilisateurs finaux qui doivent constamment communiquer pour éviter les malentendus coûteux. La capacité à expliquer un concept technique à un interlocuteur non spécialiste, à documenter clairement son travail, à accepter la critique constructive lors des revues de code et à gérer son temps face à des échéances parfois serrées sont autant de compétences humaines que la formation cherche à développer, notamment à travers des projets de groupe qui simulent les conditions réelles du monde professionnel.', 'Contrary to popular belief, computer engineering is not a solitary profession practiced behind a screen: the vast majority of software projects are built in teams, with developers, designers, project managers and end users who must constantly communicate to avoid costly misunderstandings. The ability to explain a technical concept to a non-specialist, to clearly document his work, to accept constructive criticism during code reviews and to manage his time in the face of sometimes tight deadlines are all human skills that the training seeks to develop, especially through group projects that simulate the real conditions of the professional world.', 'Tsy araka ny inoan\'ny besinimaro, ny injenieran\'ny solosaina dia tsy asa irery ao ambadiky ny efijery: ny ankamaroan\'ireo rindrambaiko tetikasa dia natsangana tao amin\'ny ekipa, miaraka amin\'ny mpandraharaha, mpamorona, tetikasa mpitantana ary mpampiasa farany izay tsy maintsy mifandray tsy tapaka mba hisorohana ny tsy fifankahazoana lafo. Ny fahafahana manazava hevitra ara-teknika amin\'ny tsy fahaizana manokana, mba hanangonana mazava tsara ny asany, ny fanekena ireo kiana mahasoa mandritra ny fanamarihan\'ny kaody ary ny fitantanana ny fotoanany manoloana ny fotoana voafetra matetika dia ny fahaizan\'olombelona mikatsaka ny hampivelatra azy, indrindra amin\'ny alalan\'ny tetikasa iombonana izay maka tahaka ny tena zava-misy iainan\'ny tontolo arak\'asa.', NULL, NULL, NULL, NULL, 18, '2026-08-15 21:52:17'),
(86, 1, 'text', 'Le programme se déploie généralement sur cinq années réparties en deux cycles. Le premier cycle (licence, L1 à L3) pose les bases : mathématiques discrètes et algèbre linéaire, algorithmique et programmation structurée, architecture des ordinateurs, systèmes d\'exploitation, bases de données et premières notions de réseaux. Le second cycle (master, M1-M2) permet une spécialisation progressive, avec des unités d\'enseignement plus avancées en génie logiciel, intelligence artificielle, cybersécurité ou administration de systèmes, ainsi qu\'un projet de fin d\'études conséquent qui met en application l\'ensemble des compétences acquises. Cette progression graduelle, du général vers le spécialisé, permet à chaque étudiant de construire un profil solide tout en se laissant le temps de découvrir les domaines qui l\'intéressent le plus avant de s\'y engager pleinement.', 'The program typically spans five years in two cycles. The first cycle (bachelor\'s degree, L1 to L3) lays the foundations: discrete mathematics and linear algebra, algorithmic and structured programming, computer architecture, operating systems, databases and first notions of networks. The second cycle (master, M1-M2) allows a progressive specialization, with more advanced teaching units in software engineering, artificial intelligence, cybersecurity or systems administration, as well as a consequent graduation project that applies all the skills acquired. This gradual progression, from general to specialized, allows each student to build a solid profile while allowing themselves time to discover the areas that interest them the most before fully committing to them.', 'Amin\'ny ankapobeny, ny solika dia azo ampiasaina mandritra ny dimy taona amin\'ny fiaramanidina roa. Ny voalohany tsingerina (mari-pahaizana licence, L1 ny L3) mametraka ny fototra: discrete matematika sy ny Linear algebra, algorithmic sy voarafitra fandaharana, solosaina maritrano, rafitra fandidiana, soratra fototra sy ny hevitra voalohany ny tambajotra. Ny fihodinana faharoa (tompony, M1-M2) dia mamela ny miandalana specialization, miaraka amin\'ny fampianarana bebe kokoa nandroso vondrona ao amin\'ny rindrambaiko injeniera, solon-tsaina, cybersecurity na ny rafitra fitantanana, ary koa ny vokany diplaoma tetikasa izay mampihatra ny fahaiza-manao rehetra azony. Io fandrosoana tsikelikely, avy amin\'ny ankapobeny ny manokana, dia mamela ny mpianatra tsirairay mba hanorina mafy orina ny mombamomba azy raha mamela ny tenany fotoana hahita ny faritra izay mahaliana azy ireo indrindra alohan\'ny tanteraka ny fanoloran-tena ho azy ireo.', NULL, NULL, NULL, NULL, 19, '2026-08-15 21:52:18'),
(87, 1, 'text', 'Les travaux pratiques occupent une place centrale dans la formation, car l\'informatique s\'apprend avant tout en pratiquant : développement d\'une application web complète avec base de données, conception d\'un petit système embarqué, mise en place d\'une infrastructure réseau sécurisée, ou encore développement d\'un modèle d\'intelligence artificielle simple pour classer des données. Ces projets sont pensés pour être de plus en plus complexes et autonomes au fil des années, jusqu\'au projet de fin d\'études qui demande souvent plusieurs mois de travail en équipe pour livrer un produit fonctionnel, documenté et testé, dans des conditions proches de celles rencontrées en entreprise.', 'Practical work occupies a central place in the training, because IT is learned above all by practicing: development of a complete web application with database, design of a small embedded system, implementation of a secure network infrastructure, or development of a simple artificial intelligence model to classify data. These projects are designed to be increasingly complex and autonomous over the years, up to the end-of-studies project, which often requires several months of teamwork to deliver a functional product, documented and tested, in conditions close to those encountered in the company.', 'Azo ampiharina ny asa mitana ny foibe toerana ao amin\'ny fanofanana, satria IT dia nianatra mihoatra noho ny zava-drehetra amin\'ny alalan\'ny fampiharana: ny fampandrosoana ny feno tranonkala fampiharana amin\'ny banky angona, famolavolana ny kely nandinika lalina ny rafitra, ny fampiharana ny azo antoka tambajotra foto-drafitrasa, na ny fampandrosoana ny tsotra solon-tsaina modely mba classify angona. Ireo tetikasa ireo dia natao ho miha-sarotra sy mahaleo tena nandritra ny taona maro, hatramin\'ny faran\'ny-taratasy tetikasa, izay matetika dia mitaky volana maromaro ny Aza Tia Tena hamonjy Functional vokatra, voarakitra sy naka fanahy, ao amin\'ny toe-javatra akaiky ireo nihaona tao amin\'ny orinasa.', NULL, NULL, NULL, NULL, 20, '2026-08-15 21:52:20'),
(88, 1, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c495a5a0.jpg', NULL, NULL, NULL, 21, '2026-08-15 21:52:20'),
(89, 1, 'text', 'Le secteur informatique est l\'un des domaines de recherche les plus actifs au monde, avec des avancées qui se traduisent très rapidement en applications concrètes : l\'intelligence artificielle générative, capable de produire du texte, des images ou du code ; l\'informatique quantique, encore expérimentale mais porteuse de ruptures majeures pour certains calculs complexes ; ou encore les avancées en cybersécurité face à des menaces de plus en plus sophistiquées. Pour un étudiant, comprendre que ces recherches ne sont pas de la science-fiction mais des axes de travail concrets sur lesquels il pourra un jour contribuer, que ce soit en entreprise ou en poursuivant des études doctorales, est une source de motivation importante tout au long de la formation.', 'The IT sector is one of the most active fields of research in the world, with advances that are very quickly translated into concrete applications: generative artificial intelligence, capable of producing text, images or code; quantum computing, still experimental but carrying major breakthroughs for some complex calculations; or advances in cybersecurity in the face of increasingly sophisticated threats. For a student, understanding that this research is not science fiction but concrete lines of work on which he can one day contribute, whether in a company or by pursuing doctoral studies, is an important source of motivation throughout the training.', 'Ny sehatry ny IT no iray amin\'ny sehatra fikarohana mavitrika indrindra manerantany, miaraka amin\'ny fandrosoana izay haingana dia voadika amin\'ny fampiharana azo tsapain-tanana: fahaizana artifisialy, fahaizana mamokatra lahatsoratra, sary na kaody; informatika, mbola andrana nefa mitondra fandrosoana lehibe ho an\'ny fikajiana sarotra; na fandrosoana amin\'ny fiarovana an-tserasera manoloana ny fitomboan\'ny fandrahonana. Fa ny mpianatra, fahatakarana fa ity fikarohana dia tsy siansa noforonin\'ny eritreritra, fa mivaingana andalana ny asa izay afaka mandray anjara indray andro any, na ao amin\'ny orinasa, na amin\'ny alalan\'ny fianarana doctorat nanenjika, dia loharano manan-danja ny antony manosika mandritra ny fiofanana.', NULL, NULL, NULL, NULL, 22, '2026-08-15 21:52:21'),
(90, 1, 'text', 'L\'informatique n\'est pas un secteur immatériel sans impact environnemental : la fabrication des équipements, la consommation énergétique des centres de données et le renouvellement rapide du matériel soulèvent des enjeux de durabilité de plus en plus pris au sérieux par la profession. Des notions de sobriété numérique, d\'optimisation énergétique des logiciels (l\'écoconception logicielle) et de prolongation de la durée de vie du matériel commencent à s\'intégrer aux formations d\'ingénieurs, en complément des compétences purement techniques. Sensibiliser les futurs ingénieurs à ces questions dès leur formation, plutôt que de les considérer comme secondaires, permet de former des professionnels capables d\'intégrer ces contraintes dans leurs choix de conception futurs.', 'IT is not an intangible sector with no environmental impact: the manufacture of equipment, the energy consumption of data centers and the rapid renewal of equipment raise sustainability issues that are increasingly taken seriously by the profession. Notions of digital sobriety, energy optimization of software (software eco-design) and extension of the lifespan of equipment are starting to be integrated into engineering training, in addition to purely technical skills. Sensitizing future engineers to these issues as soon as they are trained, rather than considering them as secondary, makes it possible to train professionals capable of integrating these constraints into their future design choices.', 'Tsy sehatra tsy azo tsapain-tanana izany ary tsy misy fiantraikany eo amin\'ny tontolo iainana: ny fanamboarana fitaovana, ny fandaniana angovo avy amin\'ny foiben-tahiry ary ny fanavaozana haingana ny fitaovana dia miteraka olana ara-pahavelomana izay mihabetsaka raisin\'ny asa ho zava-dehibe. Ny fiheverana ny fahamaotinana nomerika, ny fanatsarana ny angovo amin\'ny rindrambaiko (software eco-design) ary ny fanitarana ny faharetan\'ny fitaovana dia manomboka amin\'ny fampiofanana ara-teknika, ankoatra ny fahaiza-manao ara-teknika fotsiny. Sensitizing hoavy injeniera ireo olana raha vao nampiofanina izy ireo, fa tsy mihevitra azy ireo ho faharoa, mahatonga azy ireo ho azo atao ny hampiofana matihanina afaka mampiditra ireo faneriterena ho any amin\'ny ho avy famolavolana safidy.', NULL, NULL, NULL, NULL, 23, '2026-08-15 21:52:22'),
(91, 1, 'text', 'À Madagascar, le secteur numérique est en pleine structuration, porté par le développement des télécommunications mobiles, l\'essor du paiement mobile et une demande croissante en services numériques pour les entreprises comme pour l\'administration publique. Les diplômés en génie informatique peuvent y trouver des opportunités variées : développement de solutions logicielles adaptées au contexte local, accompagnement de la transformation numérique des entreprises malgaches, ou encore travail à distance pour des clients internationaux, une pratique de plus en plus répandue dans le secteur. Cette filière offre ainsi une réelle employabilité locale tout en ouvrant des perspectives à l\'international, un double avantage précieux pour les étudiants qui se projettent dans leur avenir professionnel.', 'In Madagascar, the digital sector is being structured, driven by the development of mobile telecommunications, the rise of mobile payment and a growing demand for digital services for businesses and public administration. Computer engineering graduates can find a variety of opportunities: developing software solutions adapted to the local context, supporting the digital transformation of Malagasy companies, or working remotely for international clients, a practice that is increasingly widespread in the sector. This sector thus offers real local employability while opening up international prospects, a valuable double advantage for students who are planning for their professional future.', 'Ao Madagasikara, voarafitra ny sehatra nomerika, entin\'ny fampandrosoana ny fifandraisandavitra finday, ny fiakaran\'ny fandoavana finday ary ny fitomboan\'ny tinady amin\'ny tolotra nomerika ho an\'ny orinasa sy ny sampan-draharaham-panjakana. Afaka mahita karazan\'asa azo atao ny mpianatra ho injeniera amin\'ny solosaina: ny fampivelarana ny vahaolana rindrambaiko mifanaraka amin\'ny zava-misy eo an-toerana, ny fanohanana ny fanovana nomerika ny orinasa Malagasy, na ny fiasana avy lavitra ho an\'ny mpanjifa iraisam-pirenena, fomba fanao izay miha-mahazo vahana hatrany eo amin\'ny sehatra. Ity sehatra ity dia manome tena eo an-toerana ny tsenan\'ny asa rehefa manokatra ny fanantenana iraisam-pirenena, ny manan-danja avo roa heny tombony ho an\'ny mpianatra izay mikasa ny ho avy matihanina.', NULL, NULL, NULL, NULL, 24, '2026-08-15 21:52:24'),
(92, 1, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c495a5a0.jpg', NULL, NULL, NULL, 25, '2026-08-15 21:52:24'),
(93, 1, 'text', 'Pour les étudiants souhaitant approfondir encore leurs compétences après le master, plusieurs voies existent : un doctorat pour se diriger vers la recherche académique ou industrielle, des certifications professionnelles reconnues dans des domaines spécifiques comme le cloud computing, la cybersécurité ou la gestion de projet, ou encore des formations courtes et intensives (bootcamps) pour se spécialiser rapidement sur une technologie précise. Le secteur informatique se distingue par la diversité de ces parcours de spécialisation continue : contrairement à d\'autres filières où le diplôme initial fixe largement la trajectoire professionnelle, un ingénieur informatique peut réorienter sa carrière plusieurs fois en fonction des technologies émergentes et de ses propres centres d\'intérêt.', 'For students wishing to further deepen their skills after the master\'s, several paths exist: a doctorate to move towards academic or industrial research, recognized professional certifications in specific fields such as cloud computing, cybersecurity or project management, or short and intensive training (bootcamps) to quickly specialize in a specific technology. The IT sector is distinguished by the diversity of these courses of continuous specialization: unlike other courses where the initial diploma largely determines the professional trajectory, an IT engineer can redirect his career several times according to emerging technologies and his own interests.', 'Ho an\'ny mpianatra maniry ny hampitombo bebe kokoa ny fahaizany araka ny tompony, maro ny lalana misy: ny doctorat mba hifindra mankany amin\'ny akademika na orinasa fikarohana, fantatra matihanina certifications amin\'ny sehatra manokana toy ny rahona computing, cybersecurity na ny tetikasa fitantanana, na fohy sy fampiofanana mafy (bootcamps) mba haingana misahana manokana ny teknolojia manokana. Ny sehatra IT dia miavaka amin\'ny fahasamihafan\'ireo fampianarana tsy tapaka ireo: tsy toy ny fampianarana hafa izay hamarinin\'ny diplaoma voalohany ny lalan\'ny matihanina, ny injeniera IT dia afaka mamadika ny asany imbetsaka araka ny teknolojia vao misondrotra sy ny tombontsoany manokana.', NULL, NULL, NULL, NULL, 26, '2026-08-15 21:52:25'),
(94, 1, 'text', 'S\'investir dans des communautés professionnelles constitue un atout non négligeable pour un futur ingénieur informatique : participer à des hackathons, contribuer à des projets open source, rejoindre des groupes locaux de développeurs ou obtenir des certifications reconnues par l\'industrie (comme celles proposées par les grands fournisseurs de cloud) permettent de se constituer un réseau professionnel et de démontrer concrètement ses compétences à de futurs employeurs, au-delà du simple diplôme. Ces engagements, souvent réalisés en parallèle des études, sont de plus en plus valorisés par les recruteurs car ils témoignent d\'une motivation réelle et d\'une capacité à apprendre en autonomie, des qualités très recherchées dans un secteur en évolution constante.', 'Investing in professional communities is a significant asset for a future IT engineer: participating in hackathons, contributing to open source projects, joining local groups of developers or obtaining industry-recognized certifications (such as those offered by large cloud providers) allow you to build a professional network and demonstrate your skills concretely to future employers, beyond just a diploma. These commitments, often carried out in parallel with studies, are increasingly valued by recruiters because they show real motivation and an ability to learn independently, qualities that are highly sought after in a constantly changing sector.', 'Ny fampiasam-bola amin\'ny vondrom-piarahamonina matihanina dia tombony manan-danja ho an\'ny injeniera IT amin\'ny ho avy: mandray anjara amin\'ny hackathons, mandray anjara amin\'ny tetikasa open source, manatevin-daharana ny vondrona mpandraharaha eo an-toerana na mahazo ny mari-pahaizana momba ny orinasa (toy ireo natolotry ny mpamatsy rahona lehibe) mamela anao hanangana tambajotra matihanina ary hampiseho ny fahaizanao mifanaraka amin\'ny mpampiasa ho avy, ankoatra ny diplaoma fotsiny. Ireo fanoloran-tena ireo, matetika atao mifanaraka amin\'ny fianarana, dia mihamitombo hatrany ny lanjany amin\'ny mpandray mpiasa satria mampiseho ny tena antony manosika sy ny fahaizana mianatra tsy miankina, toetra izay tena tadiavina tokoa ao anatin\'ny sehatra miova foana.', NULL, NULL, NULL, NULL, 27, '2026-08-15 21:52:27'),
(95, 1, 'text', 'Pour un étudiant qui hésite encore à s\'engager dans cette filière, il faut retenir que le génie informatique n\'est pas réservé à ceux qui codent depuis l\'enfance : c\'est une discipline qui s\'apprend, se pratique et se perfectionne progressivement, à condition d\'y consacrer de la curiosité et de la persévérance. La technologie continuera d\'évoluer plus vite que n\'importe quelle autre discipline d\'ingénierie, ce qui signifie que la meilleure préparation n\'est pas de mémoriser des outils qui seront peut-être obsolètes dans dix ans, mais de développer une véritable capacité à apprendre, à s\'adapter et à résoudre des problèmes nouveaux — c\'est précisément cette compétence fondamentale que la filière Génie Informatique de l\'ISSTM cherche à transmettre à chacun de ses étudiants.', 'For a student who is still hesitant to engage in this field, it should be remembered that computer engineering is not reserved for those who have been coding since childhood: it is a discipline that can be learned, practiced and perfected gradually, provided that curiosity and perseverance are devoted to it. Technology will continue to evolve faster than any other engineering discipline, which means that the best preparation is not to memorize tools that may be obsolete in ten years, but to develop a real ability to learn, adapt and solve new problems — it is precisely this fundamental skill that the ISSTM Computer Engineering program seeks to transmit to each of its students.', 'Ho an\'ny mpianatra izay mbola misalasala hiditra amin\'ity sehatra ity, dia tokony hotsarovana fa ny injenierian\'ny solosaina dia tsy natokana ho an\'ireo izay efa hatramin\'ny fahazazany: famaizana azo ianarana, ampiharina ary vita tsikelikely, raha toa ka voatokana ho azy ny fahalianana sy ny fikirizana. Ny teknolojia dia hanohy hivoatra haingana kokoa noho ny fitsipi-pifehezana hafa, izay midika fa ny fiomanana tsara indrindra dia tsy ny mitadidy ireo fitaovana mety ho lany andro ao anatin\'ny folo taona, fa ny hampivelatra ny tena fahaizana mianatra, hampifanaraka ary hamaha olana vaovao — indrindra io fahaiza-manao fototra io izay tadiavin\'ny ISSTM Computer Engineering Program mba hampita ny tsirairay amin\'ireo mpianatra.', NULL, NULL, NULL, NULL, 28, '2026-08-15 21:52:28'),
(96, 1, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c495a5a0.jpg', NULL, NULL, NULL, 29, '2026-08-15 21:52:28'),
(97, 2, 'text', 'Le génie biomédical se situe au carrefour de l\'ingénierie et de la médecine : il ne s\'agit pas de former des médecins, mais des ingénieurs capables de concevoir, entretenir et améliorer les technologies que les médecins utilisent au quotidien, des appareils de mesure simples (tensiomètres, oxymètres) aux équipements d\'imagerie médicale sophistiqués (IRM, scanners). Cette discipline exige de comprendre à la fois le fonctionnement des circuits électroniques et des logiciels embarqués, et les bases du corps humain que ces appareils sont censés observer ou assister, ce qui en fait l\'une des filières d\'ingénierie les plus pluridisciplinaires qui soient.', 'Biomedical engineering is at the crossroads of engineering and medicine: it is not about training doctors, but engineers capable of designing, maintaining and improving the technologies that doctors use on a daily basis, from simple measuring devices (blood pressure monitors, oximeters) to sophisticated medical imaging equipment (MRI scanners). This discipline requires understanding both the operation of electronic circuits and embedded software, and the basics of the human body that these devices are supposed to observe or assist, making it one of the most multidisciplinary engineering courses available.', 'Biomedical injeniera eo amin\'ny sampanan-dalana ny injeniera sy ny fitsaboana: dia tsy momba ny fampiofanana dokotera, fa injeniera afaka planina, hihazona sy manatsara ny teknolojia izay dokotera ampiasaina isan\'andro, avy tsotra fitaovana fandrefesana (tosidra mpanaramaso, oximeters) ny fitaovana be pitsiny fitarafana ara-pitsaboana (MRI skanner). Fifehezana izany dia mitaky ny fahatakarana na ny fiasan\'ny elektronika faritra sy nandinika lalina rindrambaiko, ary ny fototry ny olombelona vatana fa ireo fitaovana ireo dia tokony hitandrina na hanampy, ka mahatonga azy io iray amin\'ireo tena multidisciplinary injeniera taranja misy.', NULL, NULL, NULL, NULL, 6, '2026-08-15 21:52:29'),
(98, 2, 'text', 'Si les premiers instruments médicaux mécaniques remontent à l\'Antiquité, le génie biomédical moderne naît véritablement au XXe siècle avec l\'électrocardiographe (1903), qui a permis pour la première fois d\'enregistrer l\'activité électrique du cœur, puis avec le développement du scanner à rayons X dans les années 1970 et de l\'IRM dans les années 1980, deux inventions qui ont révolutionné le diagnostic médical sans recourir à la chirurgie. Plus récemment, la miniaturisation de l\'électronique a permis l\'essor des dispositifs portables de santé connectée, ouvrant une nouvelle ère où la surveillance médicale ne se limite plus à l\'hôpital mais accompagne le patient au quotidien.', 'While the first mechanical medical instruments date back to antiquity, modern biomedical engineering was born in the twentieth century with the electrocardiograph (1903), which made it possible for the first time to record the electrical activity of the heart, then with the development of the X-ray scanner in the 1970s and MRI in the 1980s, two inventions that revolutionized medical diagnosis without resorting to surgery. More recently, the miniaturization of electronics has allowed the rise of wearable connected health devices, opening a new era where medical surveillance is no longer limited to the hospital but accompanies the patient on a daily basis.', 'Raha ny voalohany fitaovana mekanika fitsaboana daty fahiny, biomedical injeniera maoderina teraka tamin\'ny taonjato faha-20 amin\'ny electrocardiograph (1903), izay azo atao ho an\'ny fotoana voalohany mba hanoratra ny herinaratra ao am-po asa, dia miaraka amin\'ny fampandrosoana ny X-ray scanner ao amin\'ny 1970s sy ny MRI ao amin\'ny 1980, inventions roa izay revolutionized fitsaboana aretina tsy mampiasa fandidiana. Vao haingana, nahafahana nampifandray ireo fitaovana ara-pahasalamana ny fampiroboroboana ny fitaovana elektronika, nanokatra vanim-potoana vaovao izay tsy voafetra ho ao amin\'ny hopitaly intsony ny fanarahamaso ara-pitsaboana fa miaraka amin\'ny marary amin\'ny fiainana andavanandro.', NULL, NULL, NULL, NULL, 7, '2026-08-15 21:52:31'),
(99, 2, 'text', 'Les ingénieurs biomédicaux interviennent dans des domaines très variés : la conception d\'implants et de prothèses, le développement de dispositifs de diagnostic (capteurs, systèmes d\'imagerie), la télémédecine qui permet un suivi médical à distance, ou encore la maintenance des équipements hospitaliers, un enjeu critique dans les pays où l\'accès à des techniciens qualifiés reste limité. À Madagascar en particulier, la maintenance et l\'adaptation d\'équipements médicaux aux réalités locales — coupures d\'électricité, climat, disponibilité des pièces détachées — représentent un champ d\'action concret et particulièrement utile pour les diplômés de cette filière.', 'Biomedical engineers work in a wide variety of fields: the design of implants and prostheses, the development of diagnostic devices (sensors, imaging systems), telemedicine that allows remote medical monitoring, or the maintenance of hospital equipment, a critical issue in countries where access to qualified technicians remains limited. In Madagascar in particular, the maintenance and adaptation of medical equipment to local realities — power cuts, climate, availability of spare parts — represent a concrete and particularly useful field of action for graduates of this sector.', 'Biomedical injeniera miasa ao amin\'ny isan-karazany ny saha: ny famolavolana ny implants sy ny prostheses, ny fampandrosoana ny fizahana aretina fitaovana (Sela Mpandray Hafanana, fitarafana rafitra), telemedicine izay mamela lavitra fanaraha-maso ara-pitsaboana, na ny fikojakojana ny fitaovana hopitaly, ny olana manan-danja any amin\'ny firenena izay mbola voafetra ny fidirana amin\'ny mahafeno fepetra teknisiana. Amin\'ny ankapobeny eto Madagasikara, ny fikojakojana sy ny fampifanarahana ireo fitaovana ara-pitsaboana amin\'ny zava-misy ao an-toerana — ny fahatapahan\'ny herinaratra, ny toetrandro, ny fahazoana ny piesy — dia maneho sehatra mivaingana sy tena ilaina indrindra amin\'ny hetsika ho an\'ireo nahazo diplaoma amin\'io sehatra io.', NULL, NULL, NULL, NULL, 8, '2026-08-15 21:52:33'),
(100, 2, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c55e7c30.jpg', NULL, NULL, NULL, 9, '2026-08-15 21:52:33'),
(101, 2, 'text', 'Le quotidien d\'un ingénieur biomédical implique la manipulation d\'oscilloscopes et de bancs de test électroniques pour vérifier le bon fonctionnement des appareils, de logiciels de traitement du signal pour analyser des données physiologiques (ECG, EEG), et de plus en plus d\'outils de conception assistée par ordinateur pour la modélisation de prothèses ou d\'implants sur mesure, parfois destinés à être fabriqués par impression 3D. La connaissance des normes de sécurité et de qualité propres au secteur médical est tout aussi essentielle que la maîtrise technique pure.', 'The daily life of a biomedical engineer involves the manipulation of oscilloscopes and electronic test benches to check the proper functioning of the devices, signal processing software to analyze physiological data (ECG, EEG), and increasingly computer-aided design tools for the modeling of prostheses or custom implants, sometimes intended to be manufactured by 3D printing. Knowledge of the safety and quality standards specific to the medical sector is just as essential as pure technical mastery.', 'Ny fiainana andavanandro ny biomedical injeniera Tafiditra ny fanodinkodinana ny oscilloscopes sy ny elektronika fitsapana dabilio mba hijerena ny mety fampandehanana ny fitaovana, faneva fanodinana rindrambaiko mba handinika physiological tahirin-kevitra (ECG, EEG), ary mihabetsaka solosaina-nanampy famolavolana fitaovana ho an\'ny modeling ny prostheses na fanao implants, indraindray natao ho vokarina ny 3D fanontam-pirinty. Ny fahalalana ny fenitra momba ny fiarovana sy ny kalitao manokana amin\'ny sehatry ny fitsaboana dia zava-dehibe toy ny fifehezana ara-teknika madio.', NULL, NULL, NULL, NULL, 10, '2026-08-15 21:52:34'),
(102, 2, 'text', 'Un ingénieur biomédical hospitalier partage typiquement sa journée entre des interventions de maintenance préventive et corrective sur les équipements du service, des échanges avec le personnel soignant pour comprendre leurs besoins ou résoudre un dysfonctionnement signalé, et un suivi administratif rigoureux de l\'état du parc d\'équipements, indispensable pour anticiper les remplacements et garantir la continuité des soins. Dans l\'industrie, la journée ressemble davantage à celle d\'un ingénieur R&D classique, partagée entre conception, tests et validation réglementaire des nouveaux dispositifs.', 'A hospital biomedical engineer typically divides his day between preventive and corrective maintenance interventions on the equipment of the service, exchanges with the nursing staff to understand their needs or resolve a reported malfunction, and rigorous administrative monitoring of the state of the equipment fleet, essential to anticipate replacements and guarantee the continuity of care. In industry, the day is more like that of a conventional R&D engineer, divided between design, testing and regulatory validation of new devices.', 'Ny hôpitaly biomedical injeniera matetika mizara ny andro eo amin\'ny fisorohana sy ny fanitsiana fikojakojana asa eo amin\'ny fitaovana ny fanompoana, fifanakalozana amin\'ny mpitsabo mpanampy mpiasa mba hahatakatra ny zavatra ilainy, na tapa-kevitra ny tsy hiasa araka ny tokony, ary henjana ny fitantanana ny fanaraha-maso ny toetry ny fitaovana sambo, tena ilaina ny mialoha fanoloana sy miantoka ny fitohizan\'ny fikarakarana. Ao amin\'ny orinasa, ny andro toy izany bebe kokoa ny mahazatra R & D injeniera, nizara eo amin\'ny famolavolana, fanaovana fitiliana sy fitsipika fankatoavana ny fitaovana vaovao.', NULL, NULL, NULL, NULL, 11, '2026-08-15 21:52:36'),
(103, 2, 'text', 'La formation développe des compétences en électronique analogique et numérique pour comprendre le fonctionnement interne des appareils, en traitement du signal pour interpréter des données physiologiques souvent bruitées, en informatique embarquée pour programmer les systèmes de contrôle des dispositifs, et des notions de physiologie humaine indispensables pour concevoir des équipements réellement adaptés à leur usage médical. Cette combinaison rare de compétences techniques et biologiques est précisément ce qui rend les diplômés de cette filière si recherchés dans le secteur de la santé.', 'The training develops skills in analog and digital electronics to understand the internal functioning of devices, signal processing to interpret often noisy physiological data, embedded computing to program device control systems, and notions of human physiology essential to design equipment really adapted to their medical use. This rare combination of technical and biological skills is precisely what makes graduates of this track so sought after in the health sector.', 'Ny fiofanana dia manangana fahaiza-manao amin\'ny Analog sy fitaovana nomerika mba hahatakatra ny anatiny fampandehanana ny fitaovana, faneva fanodinana ny mandika matetika mitabataba physiological tahirin-kevitra, nandinika lalina computing ny fandaharana fitaovana fanaraha-maso ny rafitra, ary ny hevitry ny olombelona physiology tena ilaina hanao ny mari fitaovana tena mifanaraka ny fampiasana ara-pitsaboana. Io tsy fahita firy mitambatra ny fahaiza-manao ara-teknika sy biolojika no mahatonga nahazo diplaoma io lalana ka nitady taorian\'ny eo amin\'ny sehatry ny fahasalamana.', NULL, NULL, NULL, NULL, 12, '2026-08-15 21:52:37'),
(104, 2, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c55e7c30.jpg', NULL, NULL, NULL, 13, '2026-08-15 21:52:37'),
(105, 2, 'text', 'Le sens de la précision et de la responsabilité est central dans ce métier : une erreur de calibration sur un appareil médical peut avoir des conséquences directes sur la santé d\'un patient, ce qui impose une rigueur méthodologique de tous les instants. La capacité à communiquer avec des professionnels de santé, qui n\'ont pas de formation technique, est également essentielle pour comprendre leurs besoins réels et leur expliquer le fonctionnement ou les limites d\'un équipement, un exercice de vulgarisation constant qui demande autant de pédagogie que de compétence technique.', 'The sense of precision and responsibility is central in this business: a calibration error on a medical device can have direct consequences on the health of a patient, which requires methodological rigor at all times. The ability to communicate with health professionals, who do not have technical training, is also essential to understand their real needs and explain to them the operation or limitations of equipment, a constant extension exercise that requires as much pedagogy as technical competence.', 'Ny fahatsapana ny fametrahana mazava tsara sy ny andraikitra no ivon\'ny ity raharaham-barotra: ny calibration fahadisoana eo amin\'ny fitaovana ara-pitsaboana mety hisy vokany mivantana eo amin\'ny fahasalamana ny marary, izay mitaky methodological henjana amin\'ny fotoana rehetra. Tena ilaina ihany koa ny fahafahana mifandray amin\'ireo matihanina amin\'ny fahasalamana, izay tsy manana fiofanana ara-teknika, mba hahatakarana ny tena filàn\'izy ireo sy hanazavana amin\'izy ireo ny fandidiana na ny fetran\'ny fitaovana, fampiharana fanitarana tsy tapaka izay mitaky fahaizana enti-mampianatra toy ny fahaizana ara-teknika.', NULL, NULL, NULL, NULL, 14, '2026-08-15 21:52:38'),
(106, 2, 'text', 'Après une licence consacrée aux fondamentaux scientifiques communs (électronique, informatique, mathématiques) complétés par des bases de biologie et d\'anatomie, le master permet une spécialisation vers l\'instrumentation médicale, l\'imagerie biomédicale ou la gestion des équipements hospitaliers. Des stages en milieu hospitalier, souvent obligatoires, permettent aux étudiants de confronter très tôt leur formation théorique aux réalités du terrain médical, une expérience jugée indispensable par la plupart des professionnels du secteur.', 'After a bachelor\'s degree in common scientific fundamentals (electronics, computer science, mathematics) supplemented by bases in biology and anatomy, the master\'s degree allows a specialization in medical instrumentation, biomedical imaging or hospital equipment management. Internships in hospitals, often compulsory, allow students to confront their theoretical training very early on with the realities of the medical field, an experience considered essential by most professionals in the sector.', 'Taorian\'ny mari-pahaizana licence iombonana ara-tsiansa fototra (elektronika, solosaina siansa, Matematika) nameno ny faladiany eo amin\'ny biolojia sy ny Anatomy, ny mari-pahaizana maîtrise dia mamela ny specialization amin\'ny fitaovana ara-pitsaboana, biomedical fitarafana na ny fitsaboana fitaovana fitantanana hopitaly. Fiofanana any amin\'ny hopitaly, matetika dia tsy maintsy, mamela ny mpianatra mba hiatrika ny teorika fiofanana tany am-boalohany tamin\'ny ny zava-misy ao amin\'ny sehatry ny fitsaboana, ny zava-nitranga heverina tena ilaina ny ankamaroan\'ny matihanina ao amin\'ny sehatra.', NULL, NULL, NULL, NULL, 15, '2026-08-15 21:52:40'),
(107, 2, 'text', 'Les travaux pratiques typiques incluent la conception d\'un circuit d\'acquisition de signal physiologique (par exemple un capteur de rythme cardiaque), l\'étude et la maintenance d\'un appareil médical existant, ou encore des projets de fin d\'études portant sur l\'amélioration d\'un dispositif médical spécifique en réponse à un besoin identifié en stage hospitalier. Ces projets ont souvent un impact concret et immédiat, ce qui constitue une source de motivation forte pour des étudiants sensibles à l\'utilité sociale de leur futur métier.', 'Typical practical work includes the design of a physiological signal acquisition circuit (e.g. a heart rate sensor), the study and maintenance of an existing medical device, or end-of-studies projects on the improvement of a specific medical device in response to an identified need in a hospital internship. These projects often have a concrete and immediate impact, which is a source of strong motivation for students sensitive to the social utility of their future profession.', 'Mahazatra dia ahitana ny famolavolana ny ara-batana famantarana fahazoana faritra (e.g. fo tahan\'ny sensor), ny fianarana sy ny fikojakojana ny iray efa misy fitaovana fitsaboana, na faran\'ny-fianarana tetikasa momba ny fanatsarana ny fitaovana ara-pitsaboana manokana ho valin\'ny iray fantatra filàna ao amin\'ny hopitaly internship. Matetika ireny tetikasa ireny no misy fiantraikany mivaingana sy eo noho eo, izay loharanon\'ny fandrisihana matanjaka ho an\'ireo mpianatra mora tohina amin\'ny filàna ara-tsosialin\'ny asany ho avy.', NULL, NULL, NULL, NULL, 16, '2026-08-15 21:52:41'),
(108, 2, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c55e7c30.jpg', NULL, NULL, NULL, 17, '2026-08-15 21:52:41'),
(109, 2, 'text', 'Le génie biomédical est un terrain d\'innovation particulièrement actif : impression 3D d\'implants sur mesure, intelligence artificielle appliquée au diagnostic par imagerie, capteurs portables toujours plus miniaturisés pour la santé connectée, ou encore développement de dispositifs à bas coût adaptés aux contextes à ressources limitées, un axe de recherche particulièrement pertinent pour les pays en développement. Cette dernière thématique, parfois appelée « ingénierie biomédicale frugale », constitue un champ de recherche directement applicable au contexte malgache.', 'Biomedical engineering is a particularly active field of innovation: 3D printing of custom-made implants, artificial intelligence applied to imaging diagnostics, increasingly miniaturized wearable sensors for connected health, or development of low-cost devices adapted to contexts with limited resources, an area of research particularly relevant for developing countries. This last theme, sometimes called \"frugal biomedical engineering\", is a field of research directly applicable to the Malagasy context.', 'Biomedical injeniera dia tena mavitrika sehatry ny zava-baovao: 3D fanontam-pirinty ny fanao-nanao implants, fahaizana artifisialy ampiharina fitarafana diagnostics, mainka miniaturized wearable Sela Mpandray Hafanana ho an\'ny fahasalamana mifandray, na ny fampandrosoana ny vidiny ambany fitaovana mifanaraka amin\'ny toe-javatra voafetra loharanon-karena, ny sehatry ny fikarohana manan-danja indrindra ho an\'ny tany an-dalam-pandrosoana. Io lohahevitra farany io, izay indraindray antsoina hoe «injenieria biomedikaly frugal», dia sampam-pikarohana azo ampiharina mivantana amin\'ny teny Malagasy.', NULL, NULL, NULL, NULL, 18, '2026-08-15 21:52:43'),
(110, 2, 'text', 'La question de la durabilité se pose différemment dans ce secteur : il s\'agit moins de sobriété énergétique que de garantir un accès équitable et durable aux soins, en concevant des équipements robustes, réparables et adaptés aux infrastructures locales plutôt que dépendants de conditions idéales rarement réunies partout. La réparabilité et la maintenance à long terme des équipements médicaux, plutôt que leur remplacement systématique, constituent des enjeux économiques et éthiques majeurs sur lesquels les ingénieurs biomédicaux ont un rôle direct à jouer.', 'The question of sustainability arises differently in this sector: it is less about energy sobriety than about ensuring equitable and sustainable access to care, by designing equipment that is robust, repairable and adapted to local infrastructures rather than dependent on ideal conditions rarely found everywhere. Repairability and long-term maintenance of medical equipment, rather than its systematic replacement, are major economic and ethical issues on which biomedical engineers have a direct role to play.', 'Mipoitra amin\'ny fomba hafa ao amin\'ity sehatra ity ny resaka fampandrosoana maharitra: tsy dia momba ny fahambonian\'ny angovo loatra izany fa mikasika ny fiantohana ny fahazoana miralenta sy maharitra ny fikarakarana, amin\'ny alalan\'ny famoronana fitaovana matanjaka, azo havaozina sy ampifanarahana amin\'ireo fotodrafitrasa ao an-toerana, fa tsy dia miankina loatra amin\'ny fepetra idealy izay tsy hita na aiza na aiza. Famerenana indray sy ny maharitra fikojakojana ny fitaovana ara-pitsaboana, fa tsy ny paika fanoloana, dia lehibe ara-toekarena sy ny etika olana izay injeniera biomedical manana anjara mivantana.', NULL, NULL, NULL, NULL, 19, '2026-08-15 21:52:44');
INSERT INTO `filiere_blocks` (`id`, `filiere_id`, `block_type`, `content_fr`, `content_en`, `content_mg`, `image_path`, `caption_fr`, `caption_en`, `caption_mg`, `display_order`, `created_at`) VALUES
(111, 2, 'text', 'Le secteur de la santé à Madagascar fait face à des défis importants en matière d\'équipement et de maintenance biomédicale, ce qui crée une réelle opportunité pour des ingénieurs formés localement et sensibilisés aux contraintes spécifiques du pays. Les diplômés de cette filière peuvent ainsi jouer un rôle direct dans l\'amélioration de l\'accès aux soins, que ce soit au sein d\'établissements hospitaliers, d\'organisations non gouvernementales de santé, ou d\'entreprises spécialisées dans la distribution d\'équipements médicaux.', 'The health sector in Madagascar faces significant challenges in terms of equipment and biomedical maintenance, which creates a real opportunity for locally trained engineers who are aware of the country\'s specific constraints. Graduates of this sector can thus play a direct role in improving access to care, whether in hospitals, non-governmental health organizations, or companies specializing in the distribution of medical equipment.', 'Misedra olana goavana ny sehatry ny fahasalamana eto Madagasikara eo amin\'ny resaka fitaovana sy ny fikojakojana ny biomedikaly, izay miteraka fahafahana tena marina ho an\'ireo injeniera voaofana ao an-toerana izay mahafantatra ireo teritery manokan\'ny firenena. Ireo nahazo diplaoma amin\'ity sehatra ity dia afaka mandray anjara mivantana amin\'ny fanatsarana ny fidirana amin\'ny fikarakarana, na any amin\'ny toeram-pitsaboana, na fikambanana tsy miankina amin\'ny fanjakana, na orinasa misahana manokana ny fizarana fitaovana fitsaboana.', NULL, NULL, NULL, NULL, 20, '2026-08-15 21:52:46'),
(112, 2, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c55e7c30.jpg', NULL, NULL, NULL, 21, '2026-08-15 21:52:46'),
(113, 2, 'text', 'Après le master, plusieurs voies de spécialisation s\'offrent aux diplômés : un doctorat pour s\'orienter vers la recherche en instrumentation médicale ou en imagerie, des formations complémentaires en réglementation des dispositifs médicaux, ou encore des spécialisations en gestion hospitalière pour évoluer vers des postes de responsable technique au sein d\'établissements de santé. La diversité de ces trajectoires reflète la nature elle-même pluridisciplinaire de la filière.', 'After the master\'s degree, several specialization paths are available to graduates: a doctorate to focus on research in medical instrumentation or imaging, additional training in the regulation of medical devices, or specializations in hospital management to progress to positions of technical manager within health institutions. The diversity of these trajectories reflects the multidisciplinary nature of the sector itself.', 'Taorian\'ny mari-pahaizana master, maro ny lalana specialization misy ny nahazo diplaoma: ny doctorat mba hifantoka amin\'ny fikarohana ao amin\'ny fitaovana ara-pitsaboana na fitarafana, fanampiny fiofanana ao amin\'ny lalàna ny fitaovana ara-pitsaboana, na specializations in hopitaly fitantanana ny handroso ny toerana ny mpitantana ara-teknika ao anatin\'ny andrim-panjakana ara-pahasalamana. Ny fahasamihafàn\'ireo làlana ireo dia taratry ny natiora marolafy misy eo amin\'ilay sehatra ihany.', NULL, NULL, NULL, NULL, 22, '2026-08-15 21:52:48'),
(114, 2, 'text', 'S\'impliquer dans des associations professionnelles de génie biomédical, participer à des congrès spécialisés ou obtenir des certifications reconnues en matière de normes qualité des dispositifs médicaux constituent des atouts précieux pour construire une carrière solide dans ce secteur très réglementé. Ces engagements permettent également de rester informé des évolutions réglementaires internationales, qui influencent directement la conception et la commercialisation des équipements médicaux.', 'Involvement in biomedical engineering professional associations, participation in specialized congresses or obtaining recognized certifications in medical device quality standards are valuable assets for building a strong career in this highly regulated sector. These commitments also make it possible to stay informed of international regulatory developments, which directly influence the design and marketing of medical equipment.', 'Fandraisana anjara amin\'ny fikambanana matihanina biomedical injeniera, fandraisana anjara amin\'ny kongresy manokana na nahazo mari-pahaizana fantatra amin\'ny fitaovana ara-pitsaboana kalitao fitsipika no fananan\'ny sarobidy ho an\'ny fanorenana asa mafy ao amin\'io sehatra nifehy. Ireo fanoloran-tena ireo dia ahafahana mampahafantatra hatrany ny fivoaran\'ny lalàna iraisam-pirenena, izay misy fiantraikany mivantana amin\'ny famolavolana sy ny fivarotana fitaovana fitsaboana.', NULL, NULL, NULL, NULL, 23, '2026-08-15 21:52:50'),
(115, 2, 'text', 'Pour réussir dans cette filière exigeante, il faut cultiver à la fois la rigueur scientifique et une réelle sensibilité aux enjeux humains de la santé : un bon ingénieur biomédical ne perd jamais de vue que derrière chaque appareil qu\'il conçoit ou répare se trouve un patient dont la santé, parfois la vie, en dépend directement. C\'est cette conscience de l\'impact concret de son travail qui distingue véritablement cette filière des autres branches de l\'ingénierie.', 'To succeed in this demanding sector, it is necessary to cultivate both scientific rigour and a real sensitivity to human health issues: a good biomedical engineer never loses sight of the fact that behind every device he designs or repairs is a patient whose health, sometimes life, depends directly on it. It is this awareness of the concrete impact of his work that truly distinguishes this sector from other branches of engineering.', 'Mba hahombiazana amin\'io sehatra io, ilaina ny mamboly hetaheta siantifika sy fahatsapana marina eo amin\'ny olana ara-pahasalaman\'ny olombelona: tsy ho saropady mihitsy ny injeniera biomedikaly tsara raha ao ambadiky ny fitaovana tsirairay noforoniny na fanamboarana izany dia marary iray izay ny fahasalamany, indraindray ny fiainana, dia miankina mivantana amin\'izany. Izany fahatsapana izany ny fiantraikany mivaingana amin\'ny asany izay tena manavaka tanteraka ity sehatra ity amin\'ny sampana hafa amin\'ny sehatry ny injeniera.', NULL, NULL, NULL, NULL, 24, '2026-08-15 21:52:52'),
(116, 2, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c55e7c30.jpg', NULL, NULL, NULL, 25, '2026-08-15 21:52:52'),
(117, 3, 'text', 'Cette filière hybride forme des ingénieurs à cheval entre deux mondes trop souvent enseignés séparément : l\'électronique, qui s\'intéresse à la conception physique des circuits et des composants, et l\'informatique, qui s\'intéresse à la logique et aux algorithmes qui les pilotent. Un système embarqué moderne ne peut être conçu efficacement sans cette double compétence, ce qui rend les diplômés de cette filière particulièrement polyvalents et recherchés sur le marché du travail.', 'This hybrid course trains engineers straddling two worlds too often taught separately: electronics, which is interested in the physical design of circuits and components, and computer science, which is interested in the logic and algorithms that drive them. A modern in-vehicle system cannot be designed effectively without this dual skill, which makes graduates of this track particularly versatile and sought after in the job market.', 'Ity Mazava ho azy fa manofana injeniera mifangaro straddling tontolo roa koa nampianatra matetika misaraka: fitaovana elektronika, izay liana amin\'ny ara-batana faritra famolavolana sy ny singa, ary ny solosaina siansa, izay liana amin\'ny lojika sy ny algorithms izay handroaka azy ireo. Ny rafitra in-fiara maoderina dia tsy azo natao amim-pahombiazana raha tsy misy io fahaiza-manao roa sosona io, izay mahatonga ireo nahazo diplaoma tamin\'ity làlana ity indrindra indrindra ary nitady izay teo amin\'ny tsenan\'ny asa.', NULL, NULL, NULL, NULL, 6, '2026-08-15 21:52:54'),
(118, 3, 'text', 'L\'histoire de cette discipline commence véritablement avec l\'invention du microprocesseur en 1971, qui a permis pour la première fois d\'intégrer une unité de calcul programmable sur une seule puce électronique, ouvrant la voie aux systèmes embarqués modernes. Les décennies suivantes ont vu la miniaturisation continue des composants, la démocratisation des microcontrôleurs à bas coût dans les années 2000, puis l\'explosion de l\'Internet des objets (IoT) dans les années 2010, qui a multiplié les usages de l\'électronique embarquée.', 'The history of this discipline truly begins with the invention of the microprocessor in 1971, which made it possible for the first time to integrate a programmable computing unit on a single electronic chip, paving the way for modern embedded systems. The following decades saw the continuous miniaturization of components, the democratization of low-cost microcontrollers in the 2000s, and then the explosion of the Internet of Things (IoT) in the 2010s, which multiplied the uses of embedded electronics.', 'Ny tantaran \'izany famaizana tena manomboka amin\'ny namorona ny microprocessor in 1971, izay nahatonga azo tanterahina voalohany mampiditra ny programmable computing rafitra iray eo amin\'ny Chip elektronika iray, hanjaka ny lalana ho an\'ny rafitra maoderina nandinika lalina. Ireto manaraka ireto nahita ny folo taona mitohy miniaturization ny singa, ny demokrasia ny vidiny ambany microcontrollers ao amin\'ny taona 2000, ary avy eo ny fipoahana ny Internet-javatra (IoT) ao amin\'ny 2010s, izay maro ny fampiasana ny nandinika lalina elektronika.', NULL, NULL, NULL, NULL, 7, '2026-08-15 21:52:56'),
(119, 3, 'text', 'Les compétences en électronique et informatique embarquée trouvent des applications dans l\'automobile, l\'électroménager intelligent, les dispositifs médicaux portables, les systèmes de sécurité et de vidéosurveillance, ou encore l\'agriculture de précision avec des capteurs connectés pour optimiser l\'irrigation. Cette diversité de débouchés permet aux diplômés de choisir un secteur d\'application qui correspond à leurs centres d\'intérêt personnels, tout en conservant un socle de compétences techniques transférable.', 'Electronics and embedded computing skills find applications in the automotive, smart home appliances, portable medical devices, security and video surveillance systems, or precision agriculture with connected sensors to optimize irrigation. This diversity of opportunities allows graduates to choose an application sector that suits their personal interests, while maintaining a transferable base of technical skills.', 'Electronics sy nandinika lalina computing fahaiza-manao mahita fampiharana ao amin\'ny fiara, manan-tsaina an-trano fitaovana, fitaovana ara-pitsaboana portable, ny fiarovana sy ny lahatsary rafitra fanaraha-maso, na marina tsara ny fambolena amin\'ny mifandray Sela Mpandray Hafanana mba manatsara fitarihan-drano. Io fahasamihafana ny fahafahana nahazo diplaoma dia mamela ny mpianatra hisafidy ny fampiharana sehatra izay tiany ny tombontsoa manokana, raha mbola azo afindra toerana ny fahaiza-manao ara-teknika.', NULL, NULL, NULL, NULL, 8, '2026-08-15 21:52:57'),
(120, 3, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c5f5200e.jpg', NULL, NULL, NULL, 9, '2026-08-15 21:52:57'),
(121, 3, 'text', 'La pratique quotidienne implique la manipulation de microcontrôleurs, d\'outils de conception de circuits imprimés (PCB), d\'oscilloscopes pour analyser des signaux électriques, et de langages de programmation bas niveau comme le C ou le C++ pour écrire du code optimisé pour des ressources matérielles limitées. La maîtrise de protocoles de communication entre systèmes (I2C, SPI, UART, Bluetooth Low Energy) est également centrale dans cette discipline.', 'Daily practice involves the manipulation of microcontrollers, printed circuit board (PCB) design tools, oscilloscopes to analyze electrical signals, and low-level programming languages like C or C++ to write code optimized for limited hardware resources. Mastery of communication protocols between systems (I2C, SPI, UART, Bluetooth Low Energy) is also central in this discipline.', NULL, NULL, NULL, NULL, NULL, 10, '2026-08-15 21:52:59'),
(122, 3, 'text', 'Une journée type combine souvent des phases de conception sur ordinateur (schémas électroniques, écriture de code embarqué), des phases de manipulation physique en laboratoire (soudure, tests de circuits, débogage matériel), et des phases de test où l\'on vérifie que le système matériel et logiciel fonctionnent correctement ensemble — une étape souvent plus complexe qu\'en informatique pure, car un bug peut provenir aussi bien d\'une erreur de code que d\'un défaut matériel.', 'A typical day often combines computer design phases (electronic diagrams, embedded code writing), physical laboratory manipulation phases (welding, circuit testing, hardware debugging), and testing phases where we check that the hardware and software system work properly together — a step that is often more complex than in pure computing, because a bug can come from both a code error and a hardware defect.', NULL, NULL, NULL, NULL, NULL, 11, '2026-08-15 21:53:02'),
(123, 3, 'text', 'La formation développe une maîtrise de l\'électronique analogique et numérique, de la programmation bas niveau adaptée aux contraintes des systèmes embarqués, de la conception de circuits imprimés, et des bases des réseaux pour permettre à ces systèmes de communiquer entre eux ou avec des serveurs distants dans le cadre de l\'Internet des objets.', 'The training develops a mastery of analog and digital electronics, low-level programming adapted to the constraints of embedded systems, the design of printed circuits, and the basics of networks to allow these systems to communicate with each other or with remote servers as part of the Internet of Things.', NULL, NULL, NULL, NULL, NULL, 12, '2026-08-15 21:53:04'),
(124, 3, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c5f5200e.jpg', NULL, NULL, NULL, 13, '2026-08-15 21:53:04'),
(125, 3, 'text', 'La patience et la méthode sont des qualités essentielles dans cette filière, car le débogage d\'un système embarqué demande souvent d\'isoler méthodiquement la source d\'un problème parmi de nombreuses causes possibles, matérielles comme logicielles. Le travail en équipe est également central, notamment lors de projets qui associent des compétences en électronique, en programmation et parfois en mécanique.', 'Patience and method are essential qualities in this sector, because debugging an embedded system often requires methodically isolating the source of a problem from many possible causes, both hardware and software. Teamwork is also central, especially in projects that combine skills in electronics, programming and sometimes mechanics.', NULL, NULL, NULL, NULL, NULL, 14, '2026-08-15 21:53:07'),
(126, 3, 'text', 'Le cursus alterne cours théoriques d\'électronique et d\'informatique durant la licence, puis approfondit progressivement les systèmes embarqués, l\'Internet des objets ou l\'électronique de puissance durant le master, avec une part croissante de travaux pratiques en laboratoire d\'électronique, jusqu\'à un projet de fin d\'études qui implique la conception complète d\'un système embarqué fonctionnel.', 'The curriculum alternates theoretical courses in electronics and computer science during the bachelor\'s degree, then gradually deepens embedded systems, the Internet of Things or power electronics during the master\'s degree, with an increasing share of practical work in the electronics laboratory, until a graduation project that involves the complete design of a functional embedded system.', NULL, NULL, NULL, NULL, NULL, 15, '2026-08-15 21:53:09'),
(127, 3, 'text', 'Les projets typiques incluent la conception d\'un objet connecté simple, le développement d\'un système de contrôle automatisé, ou la réalisation d\'une carte électronique complète depuis le schéma jusqu\'au circuit imprimé fonctionnel. Ces projets demandent de mobiliser simultanément des compétences en électronique, en programmation et souvent en résolution de problèmes pratiques imprévus.', 'Typical projects include the design of a simple connected object, the development of an automated control system, or the realization of a complete electronic board from the diagram to the functional printed circuit board. These projects require the simultaneous mobilization of skills in electronics, programming and often in solving unforeseen practical problems.', NULL, NULL, NULL, NULL, NULL, 16, '2026-08-15 21:53:11'),
(128, 3, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c5f5200e.jpg', NULL, NULL, NULL, 17, '2026-08-15 21:53:11'),
(129, 3, 'text', 'Cette filière est directement connectée aux grandes tendances technologiques actuelles : l\'intelligence artificielle embarquée, permettant à de petits appareils d\'exécuter des modèles d\'IA sans connexion à un serveur distant, la miniaturisation continue des composants, ou encore les nouveaux protocoles de communication à très faible consommation d\'énergie qui permettent de déployer des capteurs autonomes pendant des années sans changer de batterie.', 'This sector is directly connected to current major technological trends: embedded artificial intelligence, allowing small devices to run AI models without connecting to a remote server, the continuous miniaturization of components, or new communication protocols with very low energy consumption that allow autonomous sensors to be deployed for years without changing batteries.', NULL, NULL, NULL, NULL, NULL, 18, '2026-08-15 21:53:13'),
(130, 3, 'text', 'La conception de systèmes économes en énergie est un enjeu central de cette discipline, notamment pour les objets connectés alimentés par batterie qui doivent fonctionner de façon autonome le plus longtemps possible. La réflexion sur la réparabilité et le recyclage des composants électroniques, dans un contexte de production croissante de déchets électroniques mondiaux, devient un sujet de préoccupation croissant.', 'The design of energy-efficient systems is a central issue in this discipline, especially for battery-powered connected objects that must operate autonomously for as long as possible. Reflection on the reparability and recycling of electronic components, in a context of increasing global e-waste production, is becoming a growing concern.', NULL, NULL, NULL, NULL, NULL, 19, '2026-08-15 21:53:15'),
(131, 3, 'text', 'Le développement de solutions électroniques et connectées adaptées au contexte local — agriculture de précision, systèmes de sécurité à bas coût, solutions de suivi énergétique dans des zones à électrification limitée — représente un champ d\'application particulièrement pertinent pour les diplômés malgaches de cette filière, capables de concevoir des dispositifs robustes et adaptés aux réalités du pays.', 'The development of electronic and connected solutions adapted to the local context — precision agriculture, low-cost security systems, energy monitoring solutions in areas with limited electrification — represents a particularly relevant field of application for Malagasy graduates in this field, capable of designing robust devices adapted to the realities of the country.', NULL, NULL, NULL, NULL, NULL, 20, '2026-08-15 21:53:17'),
(132, 3, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c5f5200e.jpg', NULL, NULL, NULL, 21, '2026-08-15 21:53:17'),
(133, 3, 'text', 'Les diplômés peuvent poursuivre vers un doctorat en systèmes embarqués ou en microélectronique, se spécialiser via des formations complémentaires en intelligence artificielle embarquée ou en cybersécurité des objets connectés, ou encore rejoindre des formations en gestion de l\'innovation pour évoluer vers des postes de responsable de développement produit dans l\'industrie électronique.', 'Graduates can pursue a PhD in embedded systems or microelectronics, specialize through additional training in embedded artificial intelligence or cybersecurity of connected objects, or join training in innovation management to progress to positions of product development manager in the electronics industry.', NULL, NULL, NULL, NULL, NULL, 22, '2026-08-15 21:53:19'),
(134, 3, 'text', 'Participer à des concours de robotique ou d\'électronique, contribuer à des projets open hardware, ou obtenir des certifications sur des plateformes de développement embarqué spécifiques constituent d\'excellents moyens de démontrer concrètement ses compétences pratiques, particulièrement valorisées dans ce secteur où la théorie seule ne suffit pas à convaincre un employeur.', 'Participating in robotics or electronics competitions, contributing to open hardware projects, or obtaining certifications on specific embedded development platforms are excellent ways to demonstrate practical skills, particularly valued in this sector where theory alone is not enough to convince an employer.', NULL, NULL, NULL, NULL, NULL, 23, '2026-08-15 21:53:21'),
(135, 3, 'text', 'Cette filière récompense particulièrement les étudiants curieux qui aiment comprendre le fonctionnement intime des systèmes qu\'ils utilisent au quotidien : démonter un appareil électronique, comprendre comment un capteur transforme un phénomène physique en signal numérique, ou construire ses propres petits projets en dehors des cours sont autant de réflexes qui développent une intuition précieuse, complémentaire des connaissances théoriques.', 'This track particularly rewards curious students who like to understand the intimate functioning of the systems they use on a daily basis: disassembling an electronic device, understanding how a sensor transforms a physical phenomenon into a digital signal, or building their own small projects outside of classes are all reflexes that develop a valuable intuition, complementary to theoretical knowledge.', NULL, NULL, NULL, NULL, NULL, 24, '2026-08-15 21:53:23'),
(136, 3, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c5f5200e.jpg', NULL, NULL, NULL, 25, '2026-08-15 21:53:23'),
(137, 4, 'text', 'L\'électricité est la colonne vertébrale invisible de toute société moderne : sans elle, ni éclairage, ni communication, ni industrie ne pourraient fonctionner. Le génie électrique forme des ingénieurs capables de produire, transporter, distribuer et exploiter cette énergie en toute sécurité, depuis la centrale de production jusqu\'à la prise électrique domestique, en passant par les lignes à haute tension et les postes de transformation.', 'Electricity is the invisible backbone of any modern society: without it, neither lighting, communication, nor industry could work. Electrical engineering trains engineers capable of safely producing, transporting, distributing and exploiting this energy, from the production plant to the domestic electrical outlet, via high-voltage lines and transformer substations.', 'Herinaratra no lafika tsy hita maso ny fiaraha-monina misy ankehitriny: raha tsy misy azy, tsy misy jiro, ny fifandraisana, na ny orinasa afaka miasa. Injeniera herinaratra manofana injeniera afaka soa aman-tsara mamokatra, nitatitra, mizara sy fanararaotana izany angovo, avy amin\'ny famokarana fototra ho any amin\'ny an-trano herinaratra fivoahan\'ny, avo-malefaka amin\'ny alalan\'ny tsipika sy ny mpanova substations.', NULL, NULL, NULL, NULL, 6, '2026-08-15 21:53:25'),
(138, 4, 'text', 'L\'histoire du génie électrique commence avec les découvertes de Faraday sur l\'induction électromagnétique dans les années 1830, puis s\'accélère avec la guerre des courants entre Edison et Tesla/Westinghouse à la fin du XIXe siècle, qui a définitivement établi les bases de la distribution électrique moderne. Le XXe siècle a vu l\'électrification progressive du monde entier, puis, depuis les années 2000, l\'émergence des énergies renouvelables qui redessine profondément les réseaux électriques.', 'The history of electrical engineering begins with Faraday\'s discoveries about electromagnetic induction in the 1830s, then accelerates with the current war between Edison and Tesla/Westinghouse in the late nineteenth century, which definitively laid the foundations for modern electrical distribution. The twentieth century saw the gradual electrification of the whole world, then, since the 2000s, the emergence of renewable energies that profoundly redesigned electricity networks.', 'Ny tantaran\'ny herinaratra injeniera manomboka amin\'ny Faraday ny nahitana momba ny herinaratra induction ao amin\'ny 1830s, dia accelerates amin\'ny amin\'izao fotoana izao ny ady eo amin\'ny Edison sy Tesla/Westinghouse tamin\'ny faramparan\'ny taonjato fahasivy ambin\'ny folo, izay nametraka ny fototry ny fizarana herinaratra maoderina. Ny taonjato faharoapolo nahita ny tsikelikely electrification \'izao tontolo izao, avy eo, hatramin\'ny taona 2000, ny firongatry ny angovo azo havaozina izay lalina nohavaozina herinaratra tambajotra.', NULL, NULL, NULL, NULL, 7, '2026-08-15 21:53:28'),
(139, 4, 'text', 'Les compétences en génie électrique s\'appliquent à la production d\'énergie (centrales thermiques, hydrauliques, solaires, éoliennes), au transport et à la distribution via les réseaux électriques, aux installations industrielles nécessitant une alimentation fiable, et aux bâtiments résidentiels et commerciaux dont la sécurité électrique doit être garantie par des professionnels qualifiés.', 'Electrical engineering skills apply to power generation (thermal, hydraulic, solar, wind), transmission and distribution via power grids, industrial installations requiring reliable power, and residential and commercial buildings whose electrical safety must be guaranteed by qualified professionals.', 'Herinaratra injeniera fahaiza-manao mihatra amin\'ny fahefana taranaka (mafana, hydraulic, masoandro, rivotra), fifindran\'ny sy ny fizarana amin\'ny alalan\'ny fahefana Grids, orinasa fametrahana mila hery azo antoka, sy ny toeram-ponenana sy ny trano ara-barotra izay herinaratra fiarovana dia tsy maintsy ho antoka ny mahafeno fepetra matihanina.', NULL, NULL, NULL, NULL, 8, '2026-08-15 21:53:30'),
(140, 4, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c6a870b8.jpg', NULL, NULL, NULL, 9, '2026-08-15 21:53:30'),
(141, 4, 'text', 'Le travail quotidien implique l\'utilisation de logiciels de simulation de réseaux électriques, d\'appareils de mesure (multimètres, pinces ampèremétriques, analyseurs de qualité d\'énergie), et de logiciels de conception assistée par ordinateur pour dimensionner des installations électriques conformes aux normes de sécurité en vigueur.', 'The daily work involves the use of electrical network simulation software, measuring devices (multimeters, ammeter clamps, energy quality analyzers), and computer-aided design software to size electrical installations that comply with current safety standards.', 'Ny asa isan\'andro Tafiditra ny fampiasana ny herinaratra Network simulation rindrambaiko, fandrefesana fitaovana (multimeters, ammeter clamps, angovo tsara mpandalina), ary ny solosaina-nanampy famolavolana rindrambaiko mba habe fametrahana herinaratra izay manaraka ny amin\'izao fotoana izao ny fenitra fiarovana.', NULL, NULL, NULL, NULL, 10, '2026-08-15 21:53:32'),
(142, 4, 'text', 'Un ingénieur électricien alterne entre le travail de bureau d\'études (calculs de dimensionnement, conception de schémas électriques) et les interventions sur site (inspection d\'installations, supervision de chantiers, résolution de pannes), ce qui demande une bonne condition physique en plus des compétences techniques, notamment lors d\'interventions sur des installations en hauteur ou en environnement industriel.', 'An electrical engineer alternates between design office work (sizing calculations, design of electrical diagrams) and on-site interventions (inspection of installations, supervision of worksites, resolution of breakdowns), which requires good physical condition in addition to technical skills, especially when working on installations at height or in an industrial environment.', 'An herinaratra injeniera alternates eo amin\'ny famolavolana ny asa birao (sizing kajikajy, famolavolana ny herinaratra sary) ary on-toerana (fanaraha-maso ny fametrahana, ny fanaraha-maso ny asa, fanapahan-kevitra ny rava), izay mitaky toe-javatra ara-batana tsara, ankoatra ny fahaiza-manao ara-teknika, indrindra rehefa miasa amin\'ny fametrahana amin\'ny avo, na amin\'ny indostria tontolo iainana.', NULL, NULL, NULL, NULL, 11, '2026-08-15 21:53:34'),
(143, 4, 'text', 'La formation développe une maîtrise des circuits électriques, des machines électriques tournantes (moteurs, générateurs), des transformateurs, des systèmes de protection électrique, et des bases de l\'électronique de puissance nécessaire à l\'intégration des énergies renouvelables dans les réseaux existants.', 'The training develops a mastery of electrical circuits, rotating electrical machines (motors, generators), transformers, electrical protection systems, and the basics of power electronics necessary for the integration of renewable energies into existing networks.', 'Ny fampiofanana dia mampivelatra ny fifehezana ny herinaratra faritra, miodina milina herinaratra (Motors, gropy), Transformers, rafitra fiarovana herinaratra, ary ny fototry ny hery elektronika ilaina ho fampidirana ny angovo azo havaozina ho efa misy tambajotra.', NULL, NULL, NULL, NULL, 12, '2026-08-15 21:53:36'),
(144, 4, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c6a870b8.jpg', NULL, NULL, NULL, 13, '2026-08-15 21:53:36'),
(145, 4, 'text', 'La rigueur est une qualité non négociable dans ce métier : une erreur de dimensionnement ou de câblage peut provoquer un incendie ou mettre en danger des vies humaines, ce qui impose le respect scrupuleux des normes de sécurité électrique à chaque étape d\'un projet, de la conception à la mise en service.', 'Rigour is a non-negotiable quality in this business: an error in sizing or wiring can cause a fire or endanger human lives, which requires scrupulous compliance with electrical safety standards at every stage of a project, from design to commissioning.', 'Ny Rigour dia kalitao tsy azo iadian-kevitra amin\'ity orinasa ity: ny fahadisoana amin\'ny sizing na wiring dia mety hiteraka afo na mety hampidi-doza ny fiainan\'ny olombelona, izay mitaky ny fanarahan-dàlana amin\'ny fenitry ny fiarovana ny herinaratra amin\'ny sehatra rehetra amin\'ny tetikasa iray, manomboka amin\'ny famolavolana hatramin\'ny fananganana.', NULL, NULL, NULL, NULL, 14, '2026-08-15 21:53:38'),
(146, 4, 'text', 'Après un socle commun en électrotechnique et en mathématiques appliquées durant la licence, le master permet de se spécialiser en distribution d\'énergie, en énergies renouvelables ou en installations industrielles, avec des travaux pratiques réguliers sur des bancs d\'essai reproduisant des conditions réelles d\'exploitation.', 'After a common foundation in electrical engineering and applied mathematics during the bachelor\'s degree, the master\'s degree allows you to specialize in energy distribution, renewable energies or industrial installations, with regular practical work on test benches reproducing real operating conditions.', 'Taorian\'ny iombonana fototra amin\'ny herinaratra injeniera sy ny matematika ampiharina nandritra ny mari-pahaizana licence, ny mari-pahaizana maîtrise dia mamela anao misahana ny angovo fizarana, angovo azo havaozina na orinasa fametrahana, amin\'ny asa tsy tapaka azo ampiharina eo amin\'ny fitsapana dabilio miteraka tena miasa fepetra.', NULL, NULL, NULL, NULL, 15, '2026-08-15 21:53:40'),
(147, 4, 'text', 'Les projets typiques incluent le dimensionnement d\'une installation électrique pour un bâtiment, la conception d\'un petit système de production d\'énergie renouvelable, ou l\'étude d\'un réseau de distribution électrique à l\'échelle d\'un quartier, avec prise en compte des contraintes réglementaires et économiques.', 'Typical projects include the sizing of an electrical installation for a building, the design of a small renewable energy production system, or the study of a district-wide electrical distribution network, taking into account regulatory and economic constraints.', 'Mahazatra dia ahitana tetikasa ny sizing ny herinaratra fametrahana ho an\'ny trano iray, ny famolavolana ny kely angovo azo havaozina rafitra famokarana, na ny fianarana ny distrika manerana herinaratra fizarana tambajotra, fandraisany an-tànana ny fitsipika sy ny faneriterena ara-toekarena.', NULL, NULL, NULL, NULL, 16, '2026-08-15 21:53:42'),
(148, 4, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c6a870b8.jpg', NULL, NULL, NULL, 17, '2026-08-15 21:53:42'),
(149, 4, 'text', 'L\'intégration massive des énergies renouvelables dans les réseaux électriques constitue le grand chantier actuel de cette discipline : contrairement aux centrales traditionnelles, la production solaire ou éolienne est intermittente, ce qui demande de nouvelles approches d\'ingénierie pour stocker l\'énergie et stabiliser les réseaux, un domaine de recherche particulièrement actif aujourd\'hui.', 'The massive integration of renewable energies into electricity grids is the current major project of this discipline: unlike traditional power plants, solar or wind generation is intermittent, which requires new engineering approaches to store energy and stabilize networks, a field of research that is particularly active today.', 'Ny fampidirana ireo angovo azo havaozina ho amin\'ny tambajotran\'ny herinaratra no tetikasa lehibe indrindra amin\'izao fotoana izao: tsy toy ny toby famokarana herinaratra nentim-paharazana, ny herin\'ny masoandro na ny famokarana herinaratra dia mitsitaitaika, izay mitaky ny fomba fiasan\'ny injeniera vaovao hitehirizana angovo sy hampitoniana ny tambajotra, sehatry ny fikarohana izay tena mavitrika amin\'izao fotoana izao.', NULL, NULL, NULL, NULL, 18, '2026-08-15 21:53:44'),
(150, 4, 'text', 'La transition énergétique place le génie électrique au cœur des enjeux de durabilité : optimiser l\'efficacité des réseaux, réduire les pertes de transport, et faciliter l\'intégration des sources d\'énergie renouvelables sont autant de missions qui donnent à cette filière un rôle direct dans la lutte contre le changement climatique.', 'The energy transition places electrical engineering at the heart of sustainability issues: optimizing network efficiency, reducing transport losses, and facilitating the integration of renewable energy sources are all missions that give this sector a direct role in the fight against climate change.', 'Ny tetezamita ny angovo fametrahana herinaratra injeniera ao am-pon\'ny maharitra olana: manatsara ny tambajotra fahombiazana, mampihena ny fatiantoka fitaterana, ary fanamorana ny fampidirana ny angovo azo havaozina loharanom-baovao dia iraka rehetra izay manome sehatra ity mivantana anjara asa any amin\'ny ady amin\'ny fiovaovan\'ny toetr\'andro.', NULL, NULL, NULL, NULL, 19, '2026-08-15 21:53:46'),
(151, 4, 'text', 'Madagascar dispose d\'un potentiel important en énergies renouvelables (hydraulique, solaire) encore largement sous-exploité, ce qui crée de réelles opportunités pour des ingénieurs électriciens capables de concevoir et d\'exploiter des installations de production d\'énergie adaptées au contexte local, notamment pour l\'électrification de zones rurales non raccordées au réseau national.', 'Madagascar has a significant potential in renewable energies (hydraulic, solar) still largely under-exploited, which creates real opportunities for electrical engineers able to design and operate energy production facilities adapted to the local context, especially for the electrification of rural areas not connected to the national grid.', 'Manana otrikarena goavana i Madagasikara eo amin\'ny angovo azo havaozina (hydraulic, solar) izay mbola tsy dia voatrandraka loatra, izay miteraka fahafahana tena ho an\'ireo injenieran\'ny herinaratra afaka mamolavola sy mampiasa fotodrafitrasa famokarana angovo mifanaraka amin\'ny zava-misy ao an-toerana, indrindra ho an\'ny famatsiana herinaratra ireo faritra ambanivohitra tsy misy ifandraisany amin\'ny tambajotra nasionaly.', NULL, NULL, NULL, NULL, 20, '2026-08-15 21:53:47'),
(152, 4, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c6a870b8.jpg', NULL, NULL, NULL, 21, '2026-08-15 21:53:47'),
(153, 4, 'text', 'Après le master, les diplômés peuvent poursuivre vers un doctorat en systèmes électriques, se spécialiser en gestion de l\'énergie via des formations complémentaires, ou obtenir des certifications professionnelles reconnues en matière de sécurité électrique, très valorisées dans le secteur industriel.', 'After the master\'s degree, graduates can pursue a doctorate in electrical systems, specialize in energy management through additional training, or obtain recognized professional certifications in electrical safety, highly valued in the industrial sector.', 'Taorian\'ny mari-pahaizana maîtrise, nahazo diplaoma dia afaka hanenjika ny doctorat ao amin\'ny rafitra herinaratra, manokana amin\'ny angovo fitantanana amin\'ny alalan\'ny fampiofanana fanampiny, na mahazo ekena matihanina certifications amin\'ny herinaratra fiarovana, tena sarobidy ao amin\'ny sehatry ny indostria.', NULL, NULL, NULL, NULL, 22, '2026-08-15 21:53:49'),
(154, 4, 'text', 'S\'investir dans des associations professionnelles d\'ingénieurs électriciens, participer à des projets d\'électrification communautaire, ou obtenir des certifications reconnues en installations électriques constituent des atouts solides pour construire une carrière dans ce secteur réglementé et exigeant.', 'Investing in professional associations of electrical engineers, participating in community electrification projects, or obtaining recognized certifications in electrical installations are solid assets to build a career in this regulated and demanding sector.', 'Fampiasam-bola ao amin\'ny matihanina fikambanana ny herinaratra injeniera, mandray anjara amin\'ny fiaraha-monina electrification tetikasa, na ny fahazoana fantatra certifications amin\'ny fametrahana herinaratra dia mafy orina fananan\'ny hanorina asa ity nifehy sy mitaky sehatra.', NULL, NULL, NULL, NULL, 23, '2026-08-15 21:53:51'),
(155, 4, 'text', 'Pour réussir dans cette filière, il faut apprécier autant le travail théorique de calcul que l\'intervention concrète sur le terrain, et développer un respect rigoureux des normes de sécurité qui, loin d\'être une contrainte administrative, protège directement des vies humaines à chaque installation électrique réalisée.', 'To succeed in this sector, it is necessary to appreciate both the theoretical work of calculation and the concrete intervention in the field, and to develop a rigorous respect for safety standards which, far from being an administrative constraint, directly protects human lives at each electrical installation carried out.', 'Mba hahombiazana amin\'ity sehatra ity, dia ilaina ny mankasitraka na ny teorika asa ny kajy sy ny azo tsapain-tànana ny fidirana an-tsehatra any an-tsaha, ary ny hampivelatra henjana fanajana ny fiarovana fitsipika izay, lavitra ny maha-fepetra ara-panjakana, miaro ny fiainan\'ny olombelona mivantana isaky ny fametrahana herinaratra atao.', NULL, NULL, NULL, NULL, 24, '2026-08-15 21:53:53'),
(156, 4, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6c6a870b8.jpg', NULL, NULL, NULL, 25, '2026-08-15 21:53:53'),
(157, 5, 'text', 'Le génie industriel ne conçoit pas des produits, mais les systèmes qui les fabriquent : cette filière forme des ingénieurs capables d\'analyser une chaîne de production dans sa globalité pour en optimiser la performance, réduire les gaspillages et améliorer la qualité, une approche transversale qui touche autant à la technique qu\'à l\'organisation humaine.', 'Industrial engineering does not design products, but the systems that manufacture them: this sector trains engineers capable of analyzing a production line as a whole to optimize performance, reduce waste and improve quality, a transversal approach that affects both technology and human organization.', NULL, NULL, NULL, NULL, NULL, 6, '2026-08-15 21:53:55'),
(158, 5, 'text', 'Le génie industriel trouve ses racines dans les travaux de Frederick Taylor sur l\'organisation scientifique du travail au début du XXe siècle, puis dans le système de production Toyota développé après la Seconde Guerre mondiale, qui a introduit les concepts de production à flux tendu et d\'amélioration continue aujourd\'hui connus sous le nom de Lean management, devenus une référence mondiale en gestion industrielle.', 'Industrial engineering has its roots in the work of Frederick Taylor on the scientific organization of work in the early twentieth century, and then in the Toyota production system developed after World War II, which introduced the concepts of just-in-time production and continuous improvement today known as Lean management, which has become a global reference in industrial management.', NULL, NULL, NULL, NULL, NULL, 7, '2026-08-15 21:53:57'),
(159, 5, 'text', 'Les compétences en génie industriel s\'appliquent à l\'industrie manufacturière (automobile, agroalimentaire, textile), à la logistique et à la gestion de la chaîne d\'approvisionnement, à la gestion de la qualité dans tout type d\'organisation, et à l\'optimisation de processus dans des secteurs aussi variés que la santé ou les services.', 'Industrial engineering skills apply to the manufacturing industry (automotive, agri-food, textile), logistics and supply chain management, quality management in any type of organization, and process optimization in sectors as diverse as healthcare or services.', NULL, NULL, NULL, NULL, NULL, 8, '2026-08-15 21:53:59'),
(160, 5, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6d53d5a74.jpg', NULL, NULL, NULL, 9, '2026-08-15 21:53:59'),
(161, 5, 'text', 'Le quotidien d\'un ingénieur industriel implique l\'utilisation d\'outils de gestion de projet, de logiciels de simulation de flux de production, d\'outils statistiques pour l\'analyse de la qualité, et de tableaux de bord de performance pour suivre en temps réel l\'efficacité d\'une chaîne de production.', 'The daily life of an industrial engineer involves the use of project management tools, production flow simulation software, statistical tools for quality analysis, and performance dashboards to monitor the efficiency of a production line in real time.', NULL, NULL, NULL, NULL, NULL, 10, '2026-08-15 21:54:01'),
(162, 5, 'text', 'Une journée type alterne entre l\'analyse de données de production pour identifier des sources d\'inefficacité, des réunions avec les équipes opérationnelles pour comprendre les contraintes réelles du terrain, et la conception de plans d\'amélioration qui doivent ensuite être mis en œuvre et suivis dans la durée.', 'A typical day alternates between analyzing production data to identify sources of inefficiency, meetings with operational teams to understand the real constraints of the field, and designing improvement plans that must then be implemented and monitored over time.', NULL, NULL, NULL, NULL, NULL, 11, '2026-08-15 21:54:03'),
(163, 5, 'text', 'La formation développe des compétences en gestion de la qualité, en analyse statistique de processus, en logistique et gestion de la chaîne d\'approvisionnement, et en méthodes d\'amélioration continue, complétées par des bases techniques générales permettant de comprendre les procédés de production sur lesquels l\'ingénieur intervient.', 'The training develops skills in quality management, statistical process analysis, logistics and supply chain management, and continuous improvement methods, complemented by general technical bases to understand the production processes on which the engineer intervenes.', NULL, NULL, NULL, NULL, NULL, 12, '2026-08-15 21:54:05'),
(164, 5, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6d53d5a74.jpg', NULL, NULL, NULL, 13, '2026-08-15 21:54:05'),
(165, 5, 'text', 'Le sens de la communication et de la coordination est central dans ce métier, car l\'ingénieur industriel travaille constamment à l\'interface entre plusieurs équipes aux intérêts parfois divergents (production, qualité, logistique, direction), ce qui demande une capacité à fédérer autour d\'objectifs communs.', 'The sense of communication and coordination is central in this profession, because the industrial engineer constantly works at the interface between several teams with sometimes divergent interests (production, quality, logistics, management), which requires an ability to federate around common objectives.', NULL, NULL, NULL, NULL, NULL, 14, '2026-08-15 21:54:07'),
(166, 5, 'text', 'Le cursus combine des enseignements techniques généraux avec des modules de gestion, de statistiques et de méthodes qualité, avant de permettre une spécialisation en logistique, en gestion de la qualité ou en amélioration continue durant le master, avec des stages en entreprise industrielle particulièrement formateurs.', 'The curriculum combines general technical courses with modules in management, statistics and quality methods, before allowing a specialization in logistics, quality management or continuous improvement during the master\'s degree, with internships in industrial companies that are particularly instructive.', NULL, NULL, NULL, NULL, NULL, 15, '2026-08-15 21:54:09'),
(167, 5, 'text', 'Les projets typiques incluent l\'analyse et la réorganisation d\'une ligne de production existante, la mise en place d\'un système de gestion de la qualité, ou un projet de fin d\'études réalisé en entreprise consistant à résoudre un problème réel d\'efficacité opérationnelle rencontré par l\'organisation d\'accueil.', 'Typical projects include the analysis and reorganization of an existing production line, the implementation of a quality management system, or an end-of-studies project carried out in a company consisting of solving a real operational efficiency problem encountered by the host organization.', NULL, NULL, NULL, NULL, NULL, 16, '2026-08-15 21:54:11'),
(168, 5, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6d53d5a74.jpg', NULL, NULL, NULL, 17, '2026-08-15 21:54:11'),
(169, 5, 'text', 'L\'industrie 4.0, qui connecte capteurs, machines et systèmes d\'information pour automatiser et optimiser les processus de production en temps réel, constitue le grand axe d\'innovation actuel de cette discipline, transformant profondément la façon dont les usines modernes sont conçues et pilotées.', 'Industry 4.0, which connects sensors, machines and information systems to automate and optimize production processes in real time, is the current focus of innovation in this discipline, profoundly transforming the way modern factories are designed and operated.', NULL, NULL, NULL, NULL, NULL, 18, '2026-08-15 21:54:13'),
(170, 5, 'text', 'L\'optimisation des ressources, la réduction des déchets de production et l\'économie circulaire sont des enjeux de plus en plus intégrés aux méthodes du génie industriel, qui dispose d\'outils particulièrement adaptés pour concilier performance économique et responsabilité environnementale au sein des organisations.', 'The optimization of resources, the reduction of production waste and the circular economy are increasingly integrated into the methods of industrial engineering, which has particularly adapted tools to reconcile economic performance and environmental responsibility within organizations.', NULL, NULL, NULL, NULL, NULL, 19, '2026-08-15 21:54:15'),
(171, 5, 'text', 'Le tissu industriel malgache, encore en développement, offre un terrain d\'application concret pour des ingénieurs capables de structurer et d\'optimiser des processus de production, que ce soit dans l\'agroalimentaire, le textile ou d\'autres secteurs manufacturiers stratégiques pour l\'économie du pays.', 'The Malagasy industrial fabric, still in development, offers a concrete field of application for engineers capable of structuring and optimizing production processes, whether in the agri-food, textile or other manufacturing sectors strategic for the country\'s economy.', NULL, NULL, NULL, NULL, NULL, 20, '2026-08-15 21:54:17'),
(172, 5, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6d53d5a74.jpg', NULL, NULL, NULL, 21, '2026-08-15 21:54:17'),
(173, 5, 'text', 'Après le master, les diplômés peuvent poursuivre vers un doctorat en génie industriel ou en recherche opérationnelle, obtenir des certifications reconnues en gestion de projet ou en amélioration continue (Lean Six Sigma), ou compléter leur formation par un diplôme de gestion pour évoluer vers des postes de direction opérationnelle.', 'After the master\'s degree, graduates can pursue a PhD in industrial engineering or operations research, obtain recognized certifications in project management or continuous improvement (Lean Six Sigma), or complete their training with a management degree to progress to operational management positions.', NULL, NULL, NULL, NULL, NULL, 22, '2026-08-15 21:54:19'),
(174, 5, 'text', 'S\'investir dans des associations professionnelles de génie industriel, obtenir des certifications reconnues en gestion de la qualité ou en gestion de projet, et développer un réseau au sein du tissu industriel local constituent des atouts précieux pour une carrière durable dans ce secteur.', 'Investing in industrial engineering professional associations, obtaining recognized certifications in quality management or project management, and developing a network within the local industrial fabric are valuable assets for a sustainable career in this sector.', NULL, NULL, NULL, NULL, NULL, 23, '2026-08-15 21:54:21');
INSERT INTO `filiere_blocks` (`id`, `filiere_id`, `block_type`, `content_fr`, `content_en`, `content_mg`, `image_path`, `caption_fr`, `caption_en`, `caption_mg`, `display_order`, `created_at`) VALUES
(175, 5, 'text', 'Cette filière convient particulièrement aux étudiants qui aiment résoudre des problèmes concrets à l\'échelle d\'une organisation entière plutôt qu\'à l\'échelle d\'un seul produit ou système technique : c\'est une discipline qui demande de savoir jongler entre rigueur analytique et sens pratique du terrain industriel.', 'This course is particularly suitable for students who like to solve concrete problems at the level of an entire organization rather than at the level of a single product or technical system: it is a discipline that requires knowing how to juggle analytical rigor and practical sense of the industrial field.', NULL, NULL, NULL, NULL, NULL, 24, '2026-08-15 21:54:22'),
(176, 5, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6d53d5a74.jpg', NULL, NULL, NULL, 25, '2026-08-15 21:54:22'),
(177, 6, 'text', 'Le génie thermique s\'intéresse à toutes les formes de transfert et de transformation de la chaleur : chauffer un bâtiment, climatiser un espace, réfrigérer des denrées alimentaires, ou optimiser un procédé industriel qui produit ou consomme de la chaleur relèvent tous des mêmes principes physiques que cette filière enseigne à maîtriser et à appliquer concrètement.', 'Thermal engineering is interested in all forms of heat transfer and transformation: heating a building, air conditioning a space, refrigerating foodstuffs, or optimizing an industrial process that produces or consumes heat all fall under the same physical principles that this sector teaches to master and apply in practice.', NULL, NULL, NULL, NULL, NULL, 6, '2026-08-15 21:54:24'),
(178, 6, 'text', 'Cette discipline s\'appuie sur les lois de la thermodynamique formalisées au XIXe siècle par des scientifiques comme Sadi Carnot, Rudolf Clausius et Lord Kelvin, avant de devenir une filière d\'ingénierie appliquée avec le développement des machines thermiques puis des systèmes modernes de chauffage, ventilation et climatisation au cours du XXe siècle.', 'This discipline is based on the laws of thermodynamics formalized in the nineteenth century by scientists such as Sadi Carnot, Rudolf Clausius and Lord Kelvin, before becoming an applied engineering sector with the development of thermal machines and then modern heating, ventilation and air conditioning systems during the twentieth century.', NULL, NULL, NULL, NULL, NULL, 7, '2026-08-15 21:54:26'),
(179, 6, 'text', 'Les compétences en génie thermique s\'appliquent au bâtiment (chauffage, ventilation, climatisation), à l\'industrie agroalimentaire (chaînes du froid), aux procédés industriels nécessitant un contrôle précis de la température, ou encore à l\'optimisation énergétique des installations existantes, un enjeu de plus en plus stratégique face à la hausse des coûts de l\'énergie.', 'Thermal engineering skills apply to buildings (heating, ventilation, air conditioning), the agri-food industry (cold chains), industrial processes requiring precise temperature control, or the energy optimization of existing installations, an increasingly strategic issue in the face of rising energy costs.', NULL, NULL, NULL, NULL, NULL, 8, '2026-08-15 21:54:28'),
(180, 6, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6ca91e9cf.jpg', NULL, NULL, NULL, 9, '2026-08-15 21:54:28'),
(181, 6, 'text', 'Le travail quotidien implique l\'utilisation de logiciels de simulation thermique de bâtiments, d\'instruments de mesure (thermocouples, caméras thermiques), et d\'outils de dimensionnement d\'installations de chauffage, ventilation et climatisation conformes aux normes de confort et d\'efficacité énergétique.', 'The daily work involves the use of thermal simulation software for buildings, measuring instruments (thermocouples, thermal cameras), and sizing tools for heating, ventilation and air conditioning installations that comply with comfort and energy efficiency standards.', NULL, NULL, NULL, NULL, NULL, 10, '2026-08-15 21:54:30'),
(182, 6, 'text', 'Une journée type peut combiner des études de dimensionnement d\'installations thermiques sur ordinateur, des visites de sites pour évaluer les performances énergétiques d\'un bâtiment ou d\'un procédé industriel, et des échanges avec des équipes de maintenance pour résoudre des dysfonctionnements sur des installations existantes.', 'A typical day can combine sizing studies of thermal installations on a computer, site visits to assess the energy performance of a building or industrial process, and exchanges with maintenance teams to resolve malfunctions on existing installations.', NULL, NULL, NULL, NULL, NULL, 11, '2026-08-15 21:54:32'),
(183, 6, 'text', 'La formation développe des compétences en thermodynamique, en mécanique des fluides, en transferts de chaleur, et en dimensionnement de systèmes de chauffage, ventilation, climatisation et réfrigération, complétées par des notions d\'efficacité énergétique de plus en plus centrales dans la pratique professionnelle.', 'The training develops skills in thermodynamics, fluid mechanics, heat transfers, and the sizing of heating, ventilation, air conditioning and refrigeration systems, complemented by notions of energy efficiency that are increasingly central to professional practice.', NULL, NULL, NULL, NULL, NULL, 12, '2026-08-15 21:54:34'),
(184, 6, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6ca91e9cf.jpg', NULL, NULL, NULL, 13, '2026-08-15 21:54:34'),
(185, 6, 'text', 'La curiosité pour les enjeux énergétiques et environnementaux actuels est un atout précieux dans cette filière, tout comme la rigueur mathématique nécessaire pour résoudre des équations de transfert thermique souvent complexes, appliquées à des situations concrètes et variées.', 'Curiosity about current energy and environmental issues is a valuable asset in this sector, as is the mathematical rigor needed to solve often complex heat transfer equations, applied to concrete and varied situations.', NULL, NULL, NULL, NULL, NULL, 14, '2026-08-15 21:54:36'),
(186, 6, 'text', 'La formation s\'appuie sur des cours théoriques solides en thermodynamique et mécanique des fluides durant la licence, complétés par des travaux pratiques sur des installations de climatisation et de chauffage, avant une spécialisation en efficacité énergétique ou en procédés industriels durant le master.', 'The training is based on solid theoretical courses in thermodynamics and fluid mechanics during the bachelor\'s degree, supplemented by practical work on air conditioning and heating installations, before specializing in energy efficiency or industrial processes during the master\'s degree.', NULL, NULL, NULL, NULL, NULL, 15, '2026-08-15 21:54:38'),
(187, 6, 'text', 'Les projets typiques incluent le dimensionnement d\'un système de climatisation pour un bâtiment, l\'audit énergétique d\'une installation existante, ou la conception d\'un système de production de froid pour la conservation de denrées alimentaires, avec une attention particulière portée à l\'efficacité énergétique globale.', 'Typical projects include the sizing of an air conditioning system for a building, the energy audit of an existing facility, or the design of a refrigeration production system for food preservation, with particular attention to overall energy efficiency.', NULL, NULL, NULL, NULL, NULL, 16, '2026-08-15 21:54:40'),
(188, 6, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6ca91e9cf.jpg', NULL, NULL, NULL, 17, '2026-08-15 21:54:40'),
(189, 6, 'text', 'L\'efficacité énergétique des bâtiments et des procédés industriels constitue le grand axe d\'innovation actuel de cette discipline, avec le développement de nouvelles technologies de récupération de chaleur, de pompes à chaleur plus performantes, et de matériaux isolants toujours plus efficaces.', 'The energy efficiency of buildings and industrial processes is the current focus of innovation in this discipline, with the development of new heat recovery technologies, more efficient heat pumps, and ever more efficient insulating materials.', NULL, NULL, NULL, NULL, NULL, 18, '2026-08-15 21:54:42'),
(190, 6, 'text', 'Face aux enjeux climatiques, le génie thermique joue un rôle direct dans la réduction de la consommation énergétique des bâtiments et des industries, ce qui en fait une discipline de plus en plus stratégique dans les politiques de transition énergétique à l\'échelle mondiale comme locale.', 'Faced with climate challenges, thermal engineering plays a direct role in reducing the energy consumption of buildings and industries, making it an increasingly strategic discipline in energy transition policies at both global and local levels.', NULL, NULL, NULL, NULL, NULL, 19, '2026-08-15 21:54:44'),
(191, 6, 'text', 'Le climat tropical de Madagascar pose des défis spécifiques en matière de confort thermique et de conservation des denrées, créant une demande réelle pour des ingénieurs capables de concevoir des solutions de climatisation et de réfrigération adaptées, économes en énergie et accessibles aux réalités économiques locales.', 'Madagascar\'s tropical climate poses specific challenges in terms of thermal comfort and food preservation, creating a real demand for engineers capable of designing adapted air conditioning and refrigeration solutions that are energy-efficient and accessible to local economic realities.', NULL, NULL, NULL, NULL, NULL, 20, '2026-08-15 21:54:46'),
(192, 6, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6ca91e9cf.jpg', NULL, NULL, NULL, 21, '2026-08-15 21:54:46'),
(193, 6, 'text', 'Après le master, les diplômés peuvent poursuivre vers un doctorat en thermique appliquée, se spécialiser via des formations complémentaires en efficacité énergétique des bâtiments, ou obtenir des certifications professionnelles reconnues en génie climatique, très valorisées dans le secteur du bâtiment.', 'After the master\'s degree, graduates can pursue a doctorate in applied thermal engineering, specialize through additional training in energy efficiency of buildings, or obtain recognized professional certifications in climate engineering, highly valued in the building sector.', NULL, NULL, NULL, NULL, NULL, 22, '2026-08-15 21:54:49'),
(194, 6, 'text', 'S\'investir dans des associations professionnelles du génie climatique, suivre des formations continues sur les nouvelles réglementations thermiques, et obtenir des certifications reconnues en efficacité énergétique constituent des atouts solides pour évoluer dans ce secteur en pleine mutation.', 'Investing in professional climate engineering associations, continuing education on new thermal regulations, and obtaining recognized certifications in energy efficiency are solid assets to evolve in this rapidly changing sector.', NULL, NULL, NULL, NULL, NULL, 23, '2026-08-15 21:54:51'),
(195, 6, 'text', 'Cette filière convient aux étudiants qui aiment comprendre les phénomènes physiques invisibles mais omniprésents qui régissent notre confort quotidien, et qui souhaitent contribuer concrètement à un enjeu aussi universel que la gestion de l\'énergie thermique dans un monde aux ressources limitées.', 'This course is suitable for students who like to understand the invisible but ubiquitous physical phenomena that govern our daily comfort, and who wish to contribute concretely to an issue as universal as the management of thermal energy in a world with limited resources.', NULL, NULL, NULL, NULL, NULL, 24, '2026-08-15 21:54:54'),
(196, 6, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6ca91e9cf.jpg', NULL, NULL, NULL, 25, '2026-08-15 21:54:54'),
(197, 7, 'text', 'Le génie civil est la discipline qui donne forme physique à notre environnement bâti : routes, ponts, bâtiments, barrages et infrastructures diverses naissent tous du travail d\'ingénieurs civils capables de transformer un besoin humain en structure sûre, durable et fonctionnelle, en respectant des contraintes techniques, économiques et environnementales souvent complexes.', 'Civil engineering is the discipline that gives physical form to our built environment: roads, bridges, buildings, dams and various infrastructures are all born from the work of civil engineers capable of transforming a human need into a safe, sustainable and functional structure, respecting often complex technical, economic and environmental constraints.', NULL, NULL, NULL, NULL, NULL, 6, '2026-08-15 21:54:56'),
(198, 7, 'text', 'Cette discipline est l\'une des plus anciennes formes d\'ingénierie, ses racines remontant aux grandes constructions de l\'Antiquité, mais elle s\'est véritablement structurée comme science moderne au XVIIIe et XIXe siècle avec le développement de la résistance des matériaux, permettant pour la première fois de calculer précisément la solidité d\'une structure avant sa construction.', 'This discipline is one of the oldest forms of engineering, its roots dating back to the great constructions of Antiquity, but it was truly structured as a modern science in the eighteenth and nineteenth centuries with the development of the resistance of materials, making it possible for the first time to calculate precisely the solidity of a structure before its construction.', NULL, NULL, NULL, NULL, NULL, 7, '2026-08-15 21:54:58'),
(199, 7, 'text', 'Les compétences en génie civil s\'appliquent au bâtiment résidentiel et commercial, aux travaux publics (routes, ponts, réseaux), à l\'hydraulique urbaine, et à la géotechnique nécessaire pour adapter chaque construction à la nature du sol sur lequel elle repose, un enjeu particulièrement important dans les zones sujettes aux glissements de terrain ou aux inondations.', 'Civil engineering skills apply to residential and commercial building, public works (roads, bridges, networks), urban hydraulics, and the geotechnics necessary to adapt each construction to the nature of the soil on which it rests, a particularly important issue in areas prone to landslides or flooding.', NULL, NULL, NULL, NULL, NULL, 8, '2026-08-15 21:55:00'),
(200, 7, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6d02f219e.jpg', NULL, NULL, NULL, 9, '2026-08-15 21:55:00'),
(201, 7, 'text', 'Le travail quotidien implique l\'utilisation de logiciels de calcul de structures, d\'outils de modélisation 3D (BIM) de plus en plus incontournables dans la profession, et d\'instruments de mesure topographique pour relever avec précision les caractéristiques d\'un terrain avant d\'y construire un ouvrage.', 'The daily work involves the use of structural calculation software, 3D modeling tools (BIM) increasingly essential in the profession, and topographic measurement instruments to accurately identify the characteristics of a terrain before building a structure.', NULL, NULL, NULL, NULL, NULL, 10, '2026-08-15 21:55:02'),
(202, 7, 'text', 'Une journée type combine souvent du travail de bureau d\'études (calculs de structures, plans) et des visites de chantier pour superviser l\'avancement des travaux, vérifier la conformité aux plans, et résoudre les imprévus techniques qui surviennent inévitablement lors de toute construction d\'envergure.', 'A typical day often combines engineering work (structural calculations, plans) and site visits to oversee the progress of work, check compliance with plans, and resolve technical contingencies that inevitably arise during any major construction.', NULL, NULL, NULL, NULL, NULL, 11, '2026-08-15 21:55:04'),
(203, 7, 'text', 'La formation développe des compétences en résistance des matériaux, en calcul de structures (béton armé, charpente métallique), en géotechnique, et en gestion de chantier, complétées par une maîtrise croissante des outils numériques de conception assistée par ordinateur.', 'The training develops skills in material resistance, structural design (reinforced concrete, metal framework), geotechnics, and site management, complemented by an increasing mastery of digital computer-aided design tools.', NULL, NULL, NULL, NULL, NULL, 12, '2026-08-15 21:55:06'),
(204, 7, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6d02f219e.jpg', NULL, NULL, NULL, 13, '2026-08-15 21:55:06'),
(205, 7, 'text', 'Le leadership et la gestion d\'équipe sont des compétences précieuses pour la supervision de travaux, qui impliquent souvent de coordonner de nombreux corps de métier différents sur un même chantier, dans le respect de délais et de budgets parfois contraignants.', 'Leadership and team management are valuable skills for supervising work, which often involve coordinating many different trades on the same site, within sometimes restrictive deadlines and budgets.', NULL, NULL, NULL, NULL, NULL, 14, '2026-08-15 21:55:08'),
(206, 7, 'text', 'Après des bases solides en résistance des matériaux et en calcul de structures durant la licence, le master permet de se spécialiser en bâtiment, travaux publics ou hydraulique, avec des projets de conception complets et des stages sur chantier particulièrement formateurs pour comprendre les réalités du terrain.', 'After a solid foundation in material resistance and structural design during the bachelor\'s degree, the master\'s degree allows you to specialize in building, public works or hydraulic engineering, with complete design projects and on-site internships that are particularly instructive to understand the realities of the field.', NULL, NULL, NULL, NULL, NULL, 15, '2026-08-15 21:55:09'),
(207, 7, 'text', 'Les projets typiques incluent le calcul et le dimensionnement d\'une structure de bâtiment, la conception d\'un ouvrage d\'art (pont, mur de soutènement), ou un projet de fin d\'études portant sur la conception complète d\'un bâtiment, de l\'étude de sol jusqu\'aux plans d\'exécution détaillés.', 'Typical projects include the calculation and sizing of a building structure, the design of an engineering structure (bridge, retaining wall), or an end-of-study project dealing with the complete design of a building, from the soil study to the detailed execution plans.', NULL, NULL, NULL, NULL, NULL, 16, '2026-08-15 21:55:11'),
(208, 7, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6d02f219e.jpg', NULL, NULL, NULL, 17, '2026-08-15 21:55:11'),
(209, 7, 'text', 'La modélisation numérique du bâtiment (BIM), qui permet de concevoir et de simuler un ouvrage entier en 3D avant sa construction, transforme profondément la profession en facilitant la coordination entre les différents intervenants et en réduisant les erreurs de conception.', 'Digital Building Modeling (BIM), which allows you to design and simulate an entire work in 3D before it is built, is profoundly transforming the profession by facilitating coordination between different stakeholders and reducing design errors.', NULL, NULL, NULL, NULL, NULL, 18, '2026-08-15 21:55:13'),
(210, 7, 'text', 'La construction durable, utilisant des matériaux locaux et à faible impact environnemental, ainsi que la conception de bâtiments résilients face aux catastrophes naturelles (cyclones, inondations), constituent des enjeux majeurs pour le génie civil contemporain, particulièrement pertinents dans les régions exposées à ces risques.', 'Sustainable construction, using local materials and with low environmental impact, as well as the design of buildings resilient to natural disasters (cyclones, floods), are major issues for contemporary civil engineering, particularly relevant in regions exposed to these risks.', NULL, NULL, NULL, NULL, NULL, 19, '2026-08-15 21:55:15'),
(211, 7, 'text', 'Madagascar fait face à des besoins considérables en infrastructures — routes, bâtiments, ouvrages de gestion de l\'eau — ce qui offre aux ingénieurs civils formés localement de nombreuses opportunités concrètes, notamment dans la conception d\'ouvrages résilients face aux cyclones et aux inondations qui touchent régulièrement le pays.', 'Madagascar faces considerable infrastructure needs — roads, buildings, water management works — which offers locally trained civil engineers many concrete opportunities, especially in the design of resilient structures in the face of cyclones and floods that regularly affect the country.', NULL, NULL, NULL, NULL, NULL, 20, '2026-08-15 21:55:17'),
(212, 7, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6d02f219e.jpg', NULL, NULL, NULL, 21, '2026-08-15 21:55:17'),
(213, 7, 'text', 'Après le master, les diplômés peuvent poursuivre vers un doctorat en structures ou en géotechnique, obtenir des certifications professionnelles reconnues en gestion de chantier ou en BIM, ou se spécialiser en ingénierie parasismique ou anticyclonique, des compétences particulièrement recherchées dans les zones à risque.', 'After the master\'s degree, graduates can pursue a PhD in structures or geotechnics, obtain recognized professional certifications in site management or BIM, or specialize in earthquake or anticyclonic engineering, skills that are particularly sought after in high-risk areas.', NULL, NULL, NULL, NULL, NULL, 22, '2026-08-15 21:55:19'),
(214, 7, 'text', 'S\'investir dans des ordres professionnels d\'ingénieurs, participer à des projets de construction communautaire, et développer une maîtrise reconnue des outils de conception numérique constituent des atouts solides pour une carrière durable dans ce secteur en constante évolution technologique.', '', NULL, NULL, NULL, NULL, NULL, 23, '2026-08-15 21:55:20'),
(215, 7, 'text', 'Cette filière convient aux étudiants qui souhaitent voir concrètement le fruit de leur travail prendre forme dans le paysage qui les entoure : peu de disciplines d\'ingénierie offrent une satisfaction aussi tangible que de voir un bâtiment ou un pont conçu sur plan devenir une réalité durable et utile à la collectivité.', '', NULL, NULL, NULL, NULL, NULL, 24, '2026-08-15 21:55:21'),
(216, 7, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6d02f219e.jpg', NULL, NULL, NULL, 25, '2026-08-15 21:55:21'),
(217, 8, 'text', 'L\'eau est une ressource à la fois vitale et complexe à maîtriser : le génie hydraulique forme des ingénieurs capables de concevoir les systèmes qui permettent de capter, transporter, distribuer et évacuer l\'eau de façon sûre et durable, qu\'il s\'agisse d\'irrigation agricole, d\'approvisionnement en eau potable ou de gestion des eaux usées.', 'Water is a resource that is both vital and complex to control: hydraulic engineering trains engineers capable of designing systems that allow water to be captured, transported, distributed and evacuated in a safe and sustainable manner, whether for agricultural irrigation, drinking water supply or wastewater management.', NULL, NULL, NULL, NULL, NULL, 6, '2026-08-15 21:55:22'),
(218, 8, 'text', 'Cette discipline compte parmi les plus anciennes sciences de l\'ingénieur, illustrée par les systèmes d\'irrigation de l\'Égypte antique ou les aqueducs romains, avant de se formaliser scientifiquement à partir du XVIIIe siècle avec les travaux de Bernoulli sur la mécanique des fluides, fondements toujours enseignés aujourd\'hui.', 'This discipline is among the oldest engineering sciences, illustrated by the irrigation systems of ancient Egypt or Roman aqueducts, before becoming scientifically formalized from the 18th century with the work of Bernoulli on fluid mechanics, foundations still taught today.', NULL, NULL, NULL, NULL, NULL, 7, '2026-08-15 21:55:23'),
(219, 8, 'text', 'Les compétences en génie hydraulique s\'appliquent à l\'irrigation agricole, à l\'approvisionnement en eau potable des villes, à l\'assainissement des eaux usées, et à la conception de grands ouvrages hydrauliques comme les barrages, essentiels tant pour la production d\'énergie que pour la gestion des risques d\'inondation.', 'Hydraulic engineering skills apply to agricultural irrigation, the supply of drinking water to cities, wastewater treatment, and the design of large hydraulic structures such as dams, essential both for energy production and for flood risk management.', NULL, NULL, NULL, NULL, NULL, 8, '2026-08-15 21:55:24'),
(220, 8, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6cb41aa1c.jpg', NULL, NULL, NULL, 9, '2026-08-15 21:55:24'),
(221, 8, 'text', 'Le travail quotidien implique l\'utilisation de logiciels de modélisation hydraulique pour simuler l\'écoulement de l\'eau dans un réseau, d\'instruments de mesure de débit et de qualité de l\'eau, et d\'outils de conception assistée par ordinateur pour dimensionner des ouvrages hydrauliques complexes.', 'Daily work involves the use of hydraulic modeling software to simulate water flow in a network, flow and water quality measuring instruments, and computer-aided design tools to size complex hydraulic structures.', NULL, NULL, NULL, NULL, NULL, 10, '2026-08-15 21:55:25'),
(222, 8, 'text', 'Une journée type peut combiner des études de dimensionnement de réseaux sur ordinateur, des visites de terrain pour évaluer l\'état d\'infrastructures hydrauliques existantes, et des échanges avec des collectivités locales pour comprendre leurs besoins en matière de gestion de l\'eau.', 'A typical day can combine computer network sizing studies, field visits to assess the state of existing hydraulic infrastructure, and discussions with local communities to understand their water management needs.', NULL, NULL, NULL, NULL, NULL, 11, '2026-08-15 21:55:26'),
(223, 8, 'text', 'La formation développe des compétences en mécanique des fluides appliquée, en dimensionnement de réseaux hydrauliques, en hydrologie pour comprendre le cycle de l\'eau à l\'échelle d\'un bassin versant, et en assainissement, complétées par des bases de génie civil nécessaires à la conception d\'ouvrages.', 'The training develops skills in applied fluid mechanics, in the design of hydraulic networks, in hydrology to understand the water cycle at the scale of a watershed, and in sanitation, supplemented by the basics of civil engineering necessary for the design of structures.', NULL, NULL, NULL, NULL, NULL, 12, '2026-08-15 21:55:27'),
(224, 8, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6cb41aa1c.jpg', NULL, NULL, NULL, 13, '2026-08-15 21:55:27'),
(225, 8, 'text', 'La sensibilité aux enjeux environnementaux liés à la gestion durable de l\'eau est une qualité précieuse dans cette filière, tout comme la rigueur mathématique nécessaire pour modéliser des écoulements souvent complexes et imprévisibles.', 'Sensitivity to environmental issues linked to sustainable water management is a valuable quality in this sector, as is the mathematical rigor necessary to model flows that are often complex and unpredictable.', NULL, NULL, NULL, NULL, NULL, 14, '2026-08-15 21:55:28'),
(226, 8, 'text', 'La formation associe des bases en génie civil et en mécanique des fluides durant la licence à des enseignements spécialisés sur les réseaux hydrauliques et l\'assainissement durant le master, complétés par des projets d\'application sur des cas concrets de gestion de l\'eau.', 'The training combines basics in civil engineering and fluid mechanics during the bachelor\'s degree with specialized teaching on hydraulic networks and sanitation during the master\'s degree, supplemented by application projects on concrete cases of water management.', NULL, NULL, NULL, NULL, NULL, 15, '2026-08-15 21:55:29'),
(227, 8, 'text', 'Les projets typiques incluent le dimensionnement d\'un réseau d\'irrigation, l\'étude d\'un système d\'assainissement pour une petite ville, ou un projet de fin d\'études portant sur la gestion des ressources en eau d\'un bassin versant spécifique, souvent en lien avec des problématiques locales réelles.', 'Typical projects include the sizing of an irrigation network, the study of a sanitation system for a small town, or a final-year project focusing on the management of water resources in a specific watershed, often linked to real local issues.', NULL, NULL, NULL, NULL, NULL, 16, '2026-08-15 21:55:30'),
(228, 8, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6cb41aa1c.jpg', NULL, NULL, NULL, 17, '2026-08-15 21:55:30'),
(229, 8, 'text', 'La gestion intelligente de l\'eau grâce à des capteurs connectés, permettant de détecter précocement les fuites dans un réseau ou d\'optimiser l\'irrigation en temps réel selon les besoins réels des cultures, constitue un axe d\'innovation actuel particulièrement porteur pour cette discipline.', 'Intelligent water management using connected sensors, making it possible to detect leaks in a network early or to optimize irrigation in real time according to the actual needs of crops, constitutes a particularly promising current area of ​​innovation for this discipline.', NULL, NULL, NULL, NULL, NULL, 18, '2026-08-15 21:55:31'),
(230, 8, 'text', 'Face au changement climatique et à la raréfaction de la ressource en eau dans de nombreuses régions du monde, le génie hydraulique occupe une place stratégique dans l\'adaptation des sociétés à ces nouveaux défis, notamment via une gestion plus efficace et équitable de l\'eau disponible.', 'Faced with climate change and the scarcity of water resources in many regions of the world, hydraulic engineering occupies a strategic place in the adaptation of societies to these new challenges, in particular through more efficient and equitable management of available water.', NULL, NULL, NULL, NULL, NULL, 19, '2026-08-15 21:55:33'),
(231, 8, 'text', 'Madagascar, malgré des ressources en eau globalement abondantes, fait face à des défis importants d\'accès à l\'eau potable et d\'irrigation agricole dans de nombreuses régions, ce qui offre aux ingénieurs hydrauliciens formés localement un champ d\'action direct et particulièrement utile pour le développement du pays.', 'Madagascar, despite generally abundant water resources, faces significant challenges in access to drinking water and agricultural irrigation in many regions, which offers locally trained hydraulic engineers a direct and particularly useful field of action for the development of the country.', NULL, NULL, NULL, NULL, NULL, 20, '2026-08-15 21:55:34'),
(232, 8, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6cb41aa1c.jpg', NULL, NULL, NULL, 21, '2026-08-15 21:55:34'),
(233, 8, 'text', 'Après le master, les diplômés peuvent poursuivre vers un doctorat en hydraulique ou en hydrologie, se spécialiser en gestion intégrée des ressources en eau via des formations complémentaires, ou rejoindre des organisations internationales spécialisées dans l\'accès à l\'eau et l\'assainissement.', 'After the master\'s degree, graduates can pursue a doctorate in hydraulics or hydrology, specialize in integrated water resources management via additional training, or join international organizations specializing in access to water and sanitation.', NULL, NULL, NULL, NULL, NULL, 22, '2026-08-15 21:55:35'),
(234, 8, 'text', 'S\'investir dans des projets de gestion communautaire de l\'eau, participer à des associations professionnelles spécialisées, et développer une expertise reconnue en modélisation hydraulique constituent des atouts précieux pour une carrière à fort impact social dans ce secteur.', 'Getting involved in community water management projects, participating in specialized professional associations, and developing recognized expertise in hydraulic modeling constitute valuable assets for a career with strong social impact in this sector.', NULL, NULL, NULL, NULL, NULL, 23, '2026-08-15 21:55:36'),
(235, 8, 'text', 'Cette filière convient particulièrement aux étudiants sensibles aux enjeux d\'accès équitable à une ressource aussi essentielle que l\'eau, et qui souhaitent mettre leurs compétences techniques au service de besoins humains fondamentaux, une motivation qui donne un sens profond à ce métier d\'ingénieur.', 'This sector is particularly suitable for students sensitive to the issues of equitable access to a resource as essential as water, and who wish to put their technical skills at the service of fundamental human needs, a motivation which gives deep meaning to this engineering profession.', NULL, NULL, NULL, NULL, NULL, 24, '2026-08-15 21:55:37'),
(236, 8, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6cb41aa1c.jpg', NULL, NULL, NULL, 25, '2026-08-15 21:55:37'),
(237, 9, 'text', 'À la croisée de l\'art et de la technique, le génie architectural forme des professionnels capables de concevoir des espaces à la fois esthétiques, fonctionnels et structurellement sûrs, en conciliant les aspirations créatives d\'un projet avec les contraintes bien réelles de résistance des matériaux, de réglementation et de budget.', '', NULL, NULL, NULL, NULL, NULL, 6, '2026-08-15 21:55:38'),
(238, 9, 'text', 'L\'architecture est l\'une des disciplines les plus anciennes de l\'humanité, mais le génie architectural moderne, qui associe la créativité de l\'architecture aux exigences techniques de l\'ingénierie, s\'est véritablement développé au cours du XXe siècle avec l\'essor de l\'urbanisation rapide et des nouvelles techniques de construction en béton armé et en acier.', '', NULL, NULL, NULL, NULL, NULL, 7, '2026-08-15 21:55:39'),
(239, 9, 'text', 'Les compétences en génie architectural s\'appliquent à la conception de bâtiments résidentiels, publics et industriels, à l\'urbanisme pour penser l\'organisation des espaces à l\'échelle d\'un quartier ou d\'une ville, et de plus en plus à l\'architecture durable qui intègre des considérations écologiques dès la phase de conception.', '', NULL, NULL, NULL, NULL, NULL, 8, '2026-08-15 21:55:40'),
(240, 9, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6cd5935b5.jpg', NULL, NULL, NULL, 9, '2026-08-15 21:55:40'),
(241, 9, 'text', 'Le travail quotidien implique l\'utilisation de logiciels de dessin technique et de modélisation 3D, d\'outils de conception assistée par ordinateur permettant de visualiser un projet avant sa construction, et une connaissance approfondie des réglementations du bâtiment qui encadrent chaque projet de construction.', '', NULL, NULL, NULL, NULL, NULL, 10, '2026-08-15 21:55:41'),
(242, 9, 'text', 'Une journée type alterne entre des phases de conception créative (esquisses, plans), des réunions avec des clients pour comprendre et affiner leurs besoins, et des échanges avec des ingénieurs structures pour vérifier la faisabilité technique des idées architecturales envisagées.', '', NULL, NULL, NULL, NULL, NULL, 11, '2026-08-15 21:55:42'),
(243, 9, 'text', 'La formation développe une vision spatiale affûtée, une maîtrise du dessin technique et de la modélisation 3D, des connaissances en résistance des matériaux et en réglementation du bâtiment, et une compréhension des enjeux de gestion de projet nécessaires pour mener un chantier à son terme.', '', NULL, NULL, NULL, NULL, NULL, 12, '2026-08-15 21:55:43'),
(244, 9, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6cd5935b5.jpg', NULL, NULL, NULL, 13, '2026-08-15 21:55:44'),
(245, 9, 'text', 'La sensibilité artistique doit ici se conjuguer avec une véritable rigueur technique : un beau projet architectural qui ne respecte pas les contraintes structurelles ou réglementaires reste inapplicable, ce qui demande de savoir concilier créativité et pragmatisme tout au long d\'un projet.', '', NULL, NULL, NULL, NULL, NULL, 14, '2026-08-15 21:55:45'),
(246, 9, 'text', 'La formation alterne ateliers de conception créative, cours théoriques sur les matériaux et la réglementation, et projets pratiques de conception de bâtiments, avec une place grandissante accordée à la construction durable et à l\'utilisation de matériaux locaux et écologiques.', '', NULL, NULL, NULL, NULL, NULL, 15, '2026-08-15 21:55:46'),
(247, 9, 'text', 'Les projets typiques incluent la conception complète d\'un bâtiment résidentiel, l\'étude d\'un projet d\'aménagement urbain à petite échelle, ou un projet de fin d\'études portant sur la conception d\'un équipement public intégrant des considérations d\'architecture durable et de confort thermique adapté au climat local.', '', NULL, NULL, NULL, NULL, NULL, 16, '2026-08-15 21:55:47'),
(248, 9, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6cd5935b5.jpg', NULL, NULL, NULL, 17, '2026-08-15 21:55:47'),
(249, 9, 'text', 'L\'architecture durable, utilisant des matériaux locaux à faible impact environnemental et des techniques de conception bioclimatique qui réduisent les besoins en climatisation ou en chauffage, constitue une tendance majeure et un axe de recherche particulièrement actif dans la profession aujourd\'hui.', '', NULL, NULL, NULL, NULL, NULL, 18, '2026-08-15 21:55:48'),
(250, 9, 'text', 'La conception de bâtiments économes en énergie, résilients face aux événements climatiques extrêmes, et utilisant des matériaux à faible impact environnemental place l\'architecture au cœur des enjeux de durabilité, un rôle que les jeunes générations d\'architectes sont de plus en plus amenées à assumer.', '', NULL, NULL, NULL, NULL, NULL, 19, '2026-08-15 21:55:49'),
(251, 9, 'text', 'Le contexte climatique tropical de Madagascar et la richesse de ses matériaux de construction traditionnels offrent un terrain particulièrement riche pour une architecture bioclimatique adaptée, capable de conjuguer confort thermique naturel, esthétique locale et durabilité environnementale.', '', NULL, NULL, NULL, NULL, NULL, 20, '2026-08-15 21:55:50'),
(252, 9, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6cd5935b5.jpg', NULL, NULL, NULL, 21, '2026-08-15 21:55:50'),
(253, 9, 'text', 'Après le diplôme, les architectes peuvent poursuivre vers des spécialisations en urbanisme, en architecture durable, ou en gestion de grands projets de construction, certains choisissant également de développer leur propre cabinet d\'architecture après quelques années d\'expérience professionnelle.', '', NULL, NULL, NULL, NULL, NULL, 22, '2026-08-15 21:55:51'),
(254, 9, 'text', 'Constituer un portfolio solide de ses projets, participer à des concours d\'architecture étudiants, et s\'investir dans des ordres professionnels d\'architectes constituent des étapes essentielles pour construire une réputation professionnelle reconnue dans ce secteur où le talent se juge avant tout sur les réalisations concrètes.', '', NULL, NULL, NULL, NULL, NULL, 23, '2026-08-15 21:55:52'),
(255, 9, 'text', 'Cette filière convient aux étudiants qui veulent laisser une empreinte durable et visible dans le paysage bâti qui les entoure, en donnant forme à des espaces qui amélioreront concrètement le quotidien de ceux qui les habiteront ou les fréquenteront pendant des décennies.', '', NULL, NULL, NULL, NULL, NULL, 24, '2026-08-15 21:55:53'),
(256, 9, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7a6cd5935b5.jpg', NULL, NULL, NULL, 25, '2026-08-15 21:55:53'),
(257, 14, 'text', 'Le froid n\'est pas une absence de chaleur mais une technologie à part entière : cette filière forme des ingénieurs capables de concevoir et d\'exploiter les systèmes qui permettent de conserver des denrées alimentaires, de climatiser des espaces, ou d\'optimiser la consommation énergétique de ces installations souvent gourmandes en électricité.', '', NULL, NULL, NULL, NULL, NULL, 6, '2026-08-15 21:55:55'),
(258, 14, 'text', 'Les techniques du froid se sont développées à partir du XIXe siècle avec l\'invention des premières machines frigorifiques mécaniques, qui ont permis pour la première fois de conserver des denrées alimentaires à grande échelle et sur de longues distances, révolutionnant le commerce alimentaire mondial.', '', NULL, NULL, NULL, NULL, NULL, 7, '2026-08-15 21:55:56'),
(259, 14, 'text', 'Les compétences en froid et énergie s\'appliquent à la chaîne du froid agroalimentaire (conservation, transport, distribution), à la climatisation des bâtiments et véhicules, et à l\'optimisation énergétique de ces installations, un enjeu économique majeur pour les entreprises qui en dépendent fortement.', '', NULL, NULL, NULL, NULL, NULL, 8, '2026-08-15 21:55:57'),
(260, 14, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb1c85620.jpg', NULL, NULL, NULL, 9, '2026-08-15 21:55:57'),
(261, 14, 'text', 'Le travail quotidien implique l\'utilisation d\'instruments de mesure de température et de pression, de logiciels de dimensionnement d\'installations frigorifiques, et une connaissance approfondie des fluides frigorigènes et de leur impact environnemental, un sujet de plus en plus réglementé au niveau international.', '', NULL, NULL, NULL, NULL, NULL, 10, '2026-08-15 21:55:58'),
(262, 14, 'text', 'Une journée type combine des interventions de maintenance sur des installations frigorifiques existantes, des études de dimensionnement pour de nouveaux projets, et un suivi rigoureux de la performance énergétique des systèmes en exploitation, essentiel pour maîtriser les coûts d\'exploitation souvent élevés.', '', NULL, NULL, NULL, NULL, NULL, 11, '2026-08-15 21:55:59'),
(263, 14, 'text', 'La formation développe des compétences en thermodynamique appliquée au froid, en dimensionnement d\'installations frigorifiques, en électrotechnique nécessaire au fonctionnement de ces systèmes, et en optimisation énergétique, un axe de plus en plus central dans la pratique professionnelle.', '', NULL, NULL, NULL, NULL, NULL, 12, '2026-08-15 21:56:00'),
(264, 14, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb1c85620.jpg', NULL, NULL, NULL, 13, '2026-08-15 21:56:00'),
(265, 14, 'text', 'La rigueur technique et une bonne compréhension des enjeux économiques sont essentielles dans ce métier, car une installation frigorifique mal dimensionnée ou mal entretenue peut entraîner des pertes financières importantes, que ce soit en denrées gâchées ou en surconsommation énergétique.', '', NULL, NULL, NULL, NULL, NULL, 14, '2026-08-15 21:56:01'),
(266, 14, 'text', 'Le cursus combine des bases en thermodynamique et en électrotechnique durant la licence avec des travaux pratiques sur des installations frigorifiques réelles, avant une spécialisation en froid industriel ou en efficacité énergétique durant le master.', '', NULL, NULL, NULL, NULL, NULL, 15, '2026-08-15 21:56:02'),
(267, 14, 'text', 'Les projets typiques incluent le dimensionnement d\'une chambre froide pour la conservation de denrées, l\'audit énergétique d\'une installation frigorifique existante, ou un projet de fin d\'études portant sur l\'optimisation d\'une chaîne du froid complète, de la production jusqu\'à la distribution.', '', NULL, NULL, NULL, NULL, NULL, 16, '2026-08-15 21:56:03'),
(268, 14, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb1c85620.jpg', NULL, NULL, NULL, 17, '2026-08-15 21:56:03'),
(269, 14, 'text', 'Le développement de fluides frigorigènes moins nocifs pour l\'environnement et de systèmes de froid plus économes en énergie constitue le grand axe d\'innovation actuel de cette discipline, poussé par une réglementation internationale de plus en plus stricte sur l\'impact climatique des gaz frigorigènes traditionnels.', '', NULL, NULL, NULL, NULL, NULL, 18, '2026-08-15 21:56:04'),
(270, 14, 'text', 'La réduction de l\'impact environnemental des systèmes frigorifiques, à la fois en termes de consommation énergétique et de fuites de gaz à fort pouvoir de réchauffement climatique, constitue un enjeu de durabilité majeur pour cette filière, directement lié aux engagements climatiques internationaux.', '', NULL, NULL, NULL, NULL, NULL, 19, '2026-08-15 21:56:05'),
(271, 14, 'text', 'Dans un pays comme Madagascar où les pertes post-récolte agricoles restent importantes faute d\'infrastructures de conservation adaptées, les compétences en froid industriel représentent un levier concret pour réduire le gaspillage alimentaire et améliorer la sécurité alimentaire du pays.', '', NULL, NULL, NULL, NULL, NULL, 20, '2026-08-15 21:56:07'),
(272, 14, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb1c85620.jpg', NULL, NULL, NULL, 21, '2026-08-15 21:56:07'),
(273, 14, 'text', 'Après le master, les diplômés peuvent poursuivre vers un doctorat en thermodynamique appliquée, obtenir des certifications professionnelles reconnues en manipulation de fluides frigorigènes, ou se spécialiser en gestion énergétique de grandes installations industrielles.', '', NULL, NULL, NULL, NULL, NULL, 22, '2026-08-15 21:56:08'),
(274, 14, 'text', 'S\'investir dans des associations professionnelles du froid et du génie climatique, obtenir des certifications reconnues en manipulation des fluides frigorigènes, et développer une expertise en efficacité énergétique constituent des atouts précieux pour une carrière stable dans ce secteur.', '', NULL, NULL, NULL, NULL, NULL, 23, '2026-08-15 21:56:09'),
(275, 14, 'text', 'Cette filière convient aux étudiants intéressés par une discipline concrète et immédiatement applicable, dont l\'impact se mesure directement en termes de réduction du gaspillage alimentaire et de maîtrise des coûts énergétiques, deux enjeux particulièrement stratégiques pour l\'économie locale.', '', NULL, NULL, NULL, NULL, NULL, 24, '2026-08-15 21:56:10'),
(276, 14, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb1c85620.jpg', NULL, NULL, NULL, 25, '2026-08-15 21:56:10'),
(277, 15, 'text', 'Derrière chaque usine moderne se cache un réseau invisible d\'automates, de capteurs et de systèmes de supervision qui coordonnent la production sans intervention humaine constante : cette filière forme des ingénieurs capables de concevoir, programmer et maintenir ces systèmes d\'automatisation industrielle qui transforment la façon dont les biens sont fabriqués.', '', NULL, NULL, NULL, NULL, NULL, 6, '2026-08-15 21:56:11'),
(278, 15, 'text', 'Cette filière est née de la convergence entre l\'électronique, l\'informatique et l\'automatisation industrielle, un mouvement amorcé dans les années 1970-1980 avec l\'apparition des premiers automates programmables, qui ont remplacé les systèmes de commande câblés traditionnels par une logique programmable bien plus flexible.', '', NULL, NULL, NULL, NULL, NULL, 7, '2026-08-15 21:56:12'),
(279, 15, 'text', 'Les compétences en électronique et informatique industrielle s\'appliquent à l\'automatisation des chaînes de production, à la robotique industrielle, à la supervision à distance de processus industriels, et de plus en plus à l\'industrie 4.0, qui connecte l\'ensemble de ces systèmes pour une gestion optimisée en temps réel.', '', NULL, NULL, NULL, NULL, NULL, 8, '2026-08-15 21:56:13'),
(280, 15, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb29ea27c.png', NULL, NULL, NULL, 9, '2026-08-15 21:56:13'),
(281, 15, 'text', 'Le travail quotidien implique la programmation d\'automates programmables industriels, la manipulation de capteurs et d\'actionneurs variés (moteurs, vérins pneumatiques), et l\'utilisation de logiciels de supervision qui permettent de visualiser et de contrôler à distance l\'état d\'une ligne de production entière.', '', NULL, NULL, NULL, NULL, NULL, 10, '2026-08-15 21:56:14'),
(282, 15, 'text', 'Une journée type alterne entre la programmation ou la modification de programmes d\'automates en bureau d\'études, des interventions sur site pour installer ou dépanner des équipements automatisés, et des échanges avec les équipes de production pour comprendre les besoins réels d\'un processus industriel.', '', NULL, NULL, NULL, NULL, NULL, 11, '2026-08-15 21:56:15'),
(283, 15, 'text', 'La formation développe des compétences en programmation d\'automates, en électronique industrielle, en réseaux de communication industriels, et des bases de robotique, une combinaison qui permet de concevoir des systèmes automatisés complets depuis les capteurs jusqu\'à la supervision.', '', NULL, NULL, NULL, NULL, NULL, 12, '2026-08-15 21:56:17'),
(284, 15, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb29ea27c.png', NULL, NULL, NULL, 13, '2026-08-15 21:56:17'),
(285, 15, 'text', 'La logique et la méthode sont essentielles dans ce métier, car un système automatisé complexe implique de nombreux composants interconnectés dont une panne peut avoir des répercussions en cascade sur toute une chaîne de production, ce qui demande une capacité de diagnostic rigoureuse et systématique.', '', NULL, NULL, NULL, NULL, NULL, 14, '2026-08-15 21:56:18'),
(286, 15, 'text', 'La formation combine cours théoriques et travaux pratiques sur des automates et des maquettes industrielles reproduisant des conditions réelles de production, avec une spécialisation progressive vers l\'informatique industrielle, la robotique ou la supervision de systèmes automatisés durant le master.', '', NULL, NULL, NULL, NULL, NULL, 15, '2026-08-15 21:56:19'),
(287, 15, 'text', 'Les projets typiques incluent la programmation d\'un automate pour contrôler un petit système mécanique, la conception d\'une interface de supervision pour visualiser l\'état d\'une installation, ou un projet de fin d\'études portant sur l\'automatisation complète d\'un processus industriel simple.', '', NULL, NULL, NULL, NULL, NULL, 16, '2026-08-15 21:56:20'),
(288, 15, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb29ea27c.png', NULL, NULL, NULL, 17, '2026-08-15 21:56:20'),
(289, 15, 'text', 'L\'industrie 4.0, qui interconnecte l\'ensemble des équipements d\'une usine grâce à l\'Internet industriel des objets pour permettre une maintenance prédictive et une optimisation continue de la production, constitue la grande transformation actuelle de cette discipline, avec des implications majeures sur les compétences recherchées.', '', NULL, NULL, NULL, NULL, NULL, 18, '2026-08-15 21:56:21'),
(290, 15, 'text', 'L\'automatisation intelligente permet de réduire le gaspillage de ressources et d\'énergie dans les processus industriels en optimisant précisément les paramètres de production, ce qui place cette discipline au cœur des enjeux d\'efficacité industrielle et de réduction de l\'empreinte environnementale des usines.', '', NULL, NULL, NULL, NULL, NULL, 19, '2026-08-15 21:56:22'),
(291, 15, 'text', 'Le développement industriel progressif de Madagascar crée une demande croissante pour des ingénieurs capables de moderniser et d\'automatiser des lignes de production, notamment dans les secteurs agroalimentaire et textile qui constituent des piliers importants de l\'économie industrielle du pays.', '', NULL, NULL, NULL, NULL, NULL, 20, '2026-08-15 21:56:23'),
(292, 15, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb29ea27c.png', NULL, NULL, NULL, 21, '2026-08-15 21:56:23');
INSERT INTO `filiere_blocks` (`id`, `filiere_id`, `block_type`, `content_fr`, `content_en`, `content_mg`, `image_path`, `caption_fr`, `caption_en`, `caption_mg`, `display_order`, `created_at`) VALUES
(293, 15, 'text', 'Après le master, les diplômés peuvent poursuivre vers un doctorat en automatique ou en robotique, se spécialiser via des certifications reconnues sur des plateformes d\'automates spécifiques utilisées dans l\'industrie, ou évoluer vers des postes de responsable de la maintenance industrielle.', '', NULL, NULL, NULL, NULL, NULL, 22, '2026-08-15 21:56:24'),
(294, 15, 'text', 'Obtenir des certifications reconnues sur des plateformes d\'automatisation industrielle spécifiques, participer à des projets de robotique, et développer une expertise pratique reconnue constituent des atouts particulièrement valorisés par les employeurs de ce secteur très technique.', '', NULL, NULL, NULL, NULL, NULL, 23, '2026-08-15 21:56:25'),
(295, 15, 'text', 'Cette filière convient aux étudiants qui aiment comprendre comment les systèmes physiques et informatiques s\'articulent pour automatiser des tâches complexes, et qui trouvent une satisfaction particulière à voir une chaîne de production fonctionner de façon fluide et autonome grâce à leur travail de conception.', '', NULL, NULL, NULL, NULL, NULL, 24, '2026-08-15 21:56:26'),
(296, 15, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb29ea27c.png', NULL, NULL, NULL, 25, '2026-08-15 21:56:26'),
(297, 16, 'text', 'Chaque appel téléphonique, chaque message envoyé, chaque page web consultée transite par une infrastructure de télécommunications complexe et largement invisible : cette filière forme des ingénieurs capables de concevoir, déployer et sécuriser les réseaux qui rendent possible cette connectivité permanente à laquelle nous sommes aujourd\'hui tous habitués.', '', NULL, NULL, NULL, NULL, NULL, 6, '2026-08-15 21:56:28'),
(298, 16, 'text', 'Les télécommunications ont débuté avec l\'invention du télégraphe électrique au XIXe siècle, suivie du téléphone, puis de la radio, avant que l\'apparition d\'Internet dans les années 1990 et le développement successif des réseaux mobiles (de la 2G à la 5G) ne transforment radicalement l\'ampleur et la vitesse des communications mondiales.', '', NULL, NULL, NULL, NULL, NULL, 7, '2026-08-15 21:56:29'),
(299, 16, 'text', 'Les compétences en télécommunications et réseaux s\'appliquent aux opérateurs de téléphonie mobile et Internet, aux entreprises pour la gestion de leurs infrastructures réseau internes, à la cybersécurité pour protéger ces réseaux contre les menaces croissantes, et au déploiement de nouvelles technologies comme la fibre optique.', '', NULL, NULL, NULL, NULL, NULL, 8, '2026-08-15 21:56:30'),
(300, 16, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb36c6676.jpg', NULL, NULL, NULL, 9, '2026-08-15 21:56:30'),
(301, 16, 'text', 'Le travail quotidien implique la configuration d\'équipements réseau (routeurs, commutateurs), l\'utilisation d\'outils de supervision pour surveiller la performance et la sécurité d\'un réseau en temps réel, et une connaissance approfondie des protocoles de communication qui régissent les échanges de données.', '', NULL, NULL, NULL, NULL, NULL, 10, '2026-08-15 21:56:31'),
(302, 16, 'text', 'Une journée type alterne entre la configuration et le déploiement de nouveaux équipements réseau, la surveillance de la performance et de la sécurité d\'infrastructures existantes, et la résolution d\'incidents qui peuvent affecter la connectivité de milliers d\'utilisateurs simultanément, ce qui demande une capacité à réagir rapidement sous pression.', '', NULL, NULL, NULL, NULL, NULL, 11, '2026-08-15 21:56:32'),
(303, 16, 'text', 'La formation développe des compétences en architecture de réseaux, en protocoles de transmission de données, en sécurité informatique appliquée aux réseaux, et en technologies de télécommunications mobiles et fixes, un socle technique large qui permet de s\'adapter à l\'évolution rapide de ce secteur.', '', NULL, NULL, NULL, NULL, NULL, 12, '2026-08-15 21:56:33'),
(304, 16, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb36c6676.jpg', NULL, NULL, NULL, 13, '2026-08-15 21:56:33'),
(305, 16, 'text', 'La rigueur méthodique et une bonne capacité de résolution de problèmes sous pression sont essentielles, car une panne réseau peut affecter instantanément un très grand nombre d\'utilisateurs et demande souvent une intervention rapide pour rétablir le service dans les meilleurs délais possibles.', '', NULL, NULL, NULL, NULL, NULL, 14, '2026-08-15 21:56:34'),
(306, 16, 'text', 'La formation couvre les fondamentaux des réseaux et des télécommunications durant la licence, avant de permettre une spécialisation en administration réseau, en sécurité informatique ou en ingénierie télécoms durant le master, avec des travaux pratiques réguliers sur des équipements réels.', '', NULL, NULL, NULL, NULL, NULL, 15, '2026-08-15 21:56:35'),
(307, 16, 'text', 'Les projets typiques incluent la conception et la configuration d\'un petit réseau d\'entreprise, l\'étude de la sécurisation d\'une infrastructure réseau contre des attaques courantes, ou un projet de fin d\'études portant sur le déploiement d\'une nouvelle technologie de télécommunication dans un contexte spécifique.', '', NULL, NULL, NULL, NULL, NULL, 16, '2026-08-15 21:56:36'),
(308, 16, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb36c6676.jpg', NULL, NULL, NULL, 17, '2026-08-15 21:56:36'),
(309, 16, 'text', 'Le déploiement continu de nouvelles générations de réseaux mobiles, l\'expansion de la fibre optique, et l\'essor de l\'Internet des objets qui multiplie le nombre d\'appareils connectés à gérer, constituent les grands axes de transformation actuels de cette discipline en perpétuelle évolution technologique.', '', NULL, NULL, NULL, NULL, NULL, 18, '2026-08-15 21:56:38'),
(310, 16, 'text', 'L\'optimisation de la consommation énergétique des infrastructures de télécommunications, particulièrement énergivores à l\'échelle mondiale, ainsi que la réduction de la fracture numérique en connectant des zones rurales encore isolées, constituent des enjeux de durabilité et d\'équité auxquels cette discipline peut directement contribuer.', '', NULL, NULL, NULL, NULL, NULL, 19, '2026-08-15 21:56:39'),
(311, 16, 'text', 'Madagascar connaît un développement rapide de ses infrastructures de télécommunications mobiles, mais de nombreuses zones rurales restent encore mal connectées, ce qui offre aux ingénieurs télécoms formés localement un rôle essentiel dans l\'extension de la connectivité et la réduction de la fracture numérique du pays.', '', NULL, NULL, NULL, NULL, NULL, 20, '2026-08-15 21:56:40'),
(312, 16, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb36c6676.jpg', NULL, NULL, NULL, 21, '2026-08-15 21:56:40'),
(313, 16, 'text', 'Après le master, les diplômés peuvent poursuivre vers un doctorat en télécommunications, obtenir des certifications professionnelles reconnues internationalement en administration réseau ou en cybersécurité, très valorisées par les employeurs du secteur, ou se spécialiser en ingénierie des réseaux mobiles de nouvelle génération.', '', NULL, NULL, NULL, NULL, NULL, 22, '2026-08-15 21:56:41'),
(314, 16, 'text', 'Obtenir des certifications professionnelles reconnues en réseaux et en sécurité informatique, participer à des communautés techniques spécialisées, et se tenir informé des évolutions technologiques rapides de ce secteur constituent des atouts essentiels pour une carrière durable en télécommunications.', '', NULL, NULL, NULL, NULL, NULL, 23, '2026-08-15 21:56:42'),
(315, 16, 'text', 'Cette filière convient aux étudiants passionnés par la connectivité et les infrastructures invisibles qui rendent possible notre monde hyperconnecté, et qui trouvent une satisfaction particulière à garantir la fiabilité d\'un service dont dépendent aujourd\'hui la quasi-totalité des activités économiques et sociales.', '', NULL, NULL, NULL, NULL, NULL, 24, '2026-08-15 21:56:44'),
(316, 16, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb36c6676.jpg', NULL, NULL, NULL, 25, '2026-08-15 21:56:44'),
(317, 17, 'text', 'Écrire du code fonctionnel est une chose ; construire un logiciel fiable, maintenable et évolutif sur le long terme en est une autre : le génie logiciel forme des ingénieurs capables de gérer la complexité croissante des projets informatiques modernes grâce à des méthodologies rigoureuses de conception, de test et de gestion de projet.', '', NULL, NULL, NULL, NULL, NULL, 6, '2026-08-15 21:56:45'),
(318, 17, 'text', 'Le génie logiciel est né dans les années 1960-1970 en réponse à la fameuse « crise du logiciel », lorsque la complexité croissante des programmes a révélé le besoin de méthodes rigoureuses de développement, donnant naissance à une discipline d\'ingénierie à part entière, distincte de la simple programmation.', '', NULL, NULL, NULL, NULL, NULL, 7, '2026-08-15 21:56:46'),
(319, 17, 'text', 'Les compétences en génie logiciel s\'appliquent au développement d\'applications web et mobiles, aux systèmes d\'information d\'entreprise, à la gestion de projets informatiques complexes impliquant de larges équipes, et à l\'assurance qualité logicielle, un domaine souvent sous-estimé mais essentiel à la fiabilité des systèmes modernes.', '', NULL, NULL, NULL, NULL, NULL, 8, '2026-08-15 21:56:47'),
(320, 17, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb45b9eea.jpg', NULL, NULL, NULL, 9, '2026-08-15 21:56:47'),
(321, 17, 'text', 'Le travail quotidien implique l\'utilisation de méthodologies de développement agile, d\'outils de gestion de versions et d\'intégration continue, de frameworks de test automatisé, et de plus en plus d\'assistants de programmation basés sur l\'intelligence artificielle qui accélèrent certaines tâches répétitives du développement.', '', NULL, NULL, NULL, NULL, NULL, 10, '2026-08-15 21:56:48'),
(322, 17, 'text', 'Une journée type alterne entre des réunions d\'équipe pour planifier le travail à venir, des phases de développement et de revue de code, et des tests visant à garantir que les nouvelles fonctionnalités développées n\'introduisent pas de régressions dans le logiciel existant, une discipline essentielle trop souvent négligée par les développeurs débutants.', '', NULL, NULL, NULL, NULL, NULL, 11, '2026-08-15 21:56:49'),
(323, 17, 'text', 'La formation développe des compétences en architecture logicielle, en méthodologies de développement (agile, DevOps), en tests et assurance qualité, et en gestion de projets informatiques, complétées par une bonne maîtrise de plusieurs langages et paradigmes de programmation.', '', NULL, NULL, NULL, NULL, NULL, 12, '2026-08-15 21:56:51'),
(324, 17, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb45b9eea.jpg', NULL, NULL, NULL, 13, '2026-08-15 21:56:51'),
(325, 17, 'text', 'Le travail en équipe et la communication sont absolument centraux dans ce métier : un logiciel de taille conséquente est rarement écrit par une seule personne, ce qui demande de savoir collaborer efficacement, documenter clairement son travail, et accepter la critique constructive lors des revues de code.', '', NULL, NULL, NULL, NULL, NULL, 14, '2026-08-15 21:56:53'),
(326, 17, 'text', 'La formation s\'appuie sur des projets de groupe réguliers simulant des conditions réelles de développement logiciel en entreprise, avec un apprentissage progressif des méthodologies professionnelles (agile, gestion de projet) de plus en plus utilisées dans l\'industrie du logiciel à l\'échelle mondiale.', '', NULL, NULL, NULL, NULL, NULL, 15, '2026-08-15 21:56:55'),
(327, 17, 'text', 'Les projets typiques incluent le développement complet d\'une application en équipe suivant une méthodologie agile, la mise en place d\'un pipeline d\'intégration continue automatisé, ou un projet de fin d\'études portant sur la conception d\'un logiciel répondant à un besoin métier réel et documenté.', '', NULL, NULL, NULL, NULL, NULL, 16, '2026-08-15 21:56:56'),
(328, 17, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb45b9eea.jpg', NULL, NULL, NULL, 17, '2026-08-15 21:56:56'),
(329, 17, 'text', 'L\'intégration croissante de l\'intelligence artificielle dans les outils de développement, capable d\'assister ou d\'automatiser certaines tâches de programmation, transforme progressivement le métier de développeur vers un rôle davantage centré sur la conception, l\'architecture et la validation que sur l\'écriture manuelle de chaque ligne de code.', '', NULL, NULL, NULL, NULL, NULL, 18, '2026-08-15 21:56:57'),
(330, 17, 'text', 'La conception de logiciels économes en ressources informatiques (écoconception logicielle), qui réduit indirectement la consommation énergétique des infrastructures qui les font fonctionner, devient un critère de qualité de plus en plus pris en compte dans les bonnes pratiques du génie logiciel moderne.', '', NULL, NULL, NULL, NULL, NULL, 19, '2026-08-15 21:56:58'),
(331, 17, 'text', 'Le développement rapide du secteur numérique malgache crée une demande croissante pour des ingénieurs logiciels capables de concevoir des applications adaptées au contexte local, que ce soit pour des besoins de paiement mobile, de commerce en ligne ou de services numériques pour les entreprises et l\'administration.', '', NULL, NULL, NULL, NULL, NULL, 20, '2026-08-15 21:56:59'),
(332, 17, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb45b9eea.jpg', NULL, NULL, NULL, 21, '2026-08-15 21:56:59'),
(333, 17, 'text', 'Après le master, les diplômés peuvent poursuivre vers un doctorat en génie logiciel, obtenir des certifications professionnelles reconnues en architecture logicielle ou en méthodologies agiles, ou se spécialiser dans des domaines de pointe comme le développement de logiciels critiques nécessitant une fiabilité extrême.', '', NULL, NULL, NULL, NULL, NULL, 22, '2026-08-15 21:57:01'),
(334, 17, 'text', 'Contribuer à des projets open source, participer à des hackathons, et construire un portfolio de projets personnels démontrant sa maîtrise de bonnes pratiques de développement constituent d\'excellents moyens de se distinguer auprès des employeurs dans un secteur où les réalisations concrètes comptent souvent plus que le seul diplôme.', '', NULL, NULL, NULL, NULL, NULL, 23, '2026-08-15 21:57:03'),
(335, 17, 'text', 'Cette filière convient aux étudiants organisés qui aiment penser un projet dans sa globalité plutôt que de se concentrer uniquement sur l\'écriture de code, et qui comprennent que la valeur d\'un bon ingénieur logiciel se mesure autant à la qualité et à la maintenabilité de son travail qu\'à sa rapidité d\'exécution.', '', NULL, NULL, NULL, NULL, NULL, 24, '2026-08-15 21:57:04'),
(336, 17, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb45b9eea.jpg', NULL, NULL, NULL, 25, '2026-08-15 21:57:04'),
(337, 18, 'text', 'Cette filière forme des ingénieurs polyvalents capables de concevoir des systèmes qui combinent puissance électrique et intelligence de contrôle : une chaîne de production moderne, un ascenseur intelligent ou un système de gestion énergétique de bâtiment reposent tous sur cette association entre électrotechnique et automatisme que cette discipline enseigne à maîtriser conjointement.', '', NULL, NULL, NULL, NULL, NULL, 6, '2026-08-15 21:57:05'),
(338, 18, 'text', 'Cette filière combine l\'histoire du génie électrique et celle de l\'automatisation industrielle, deux disciplines qui ont convergé au cours du XXe siècle avec l\'introduction des automates programmables et des systèmes de contrôle-commande, permettant de piloter des installations électriques complexes de façon automatisée et fiable.', '', NULL, NULL, NULL, NULL, NULL, 7, '2026-08-15 21:57:06'),
(339, 18, 'text', 'Les compétences en ingénierie des systèmes électriques automatisés s\'appliquent à l\'automatisation industrielle, à la gestion technique des bâtiments (éclairage, climatisation, sécurité automatisés), aux systèmes de production d\'énergie automatisés, et à la maintenance de systèmes électriques complexes intégrant de nombreux composants interconnectés.', '', NULL, NULL, NULL, NULL, NULL, 8, '2026-08-15 21:57:07'),
(340, 18, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb5123b94.jpg', NULL, NULL, NULL, 9, '2026-08-15 21:57:07'),
(341, 18, 'text', 'Le travail quotidien implique la programmation de systèmes de contrôle-commande, la manipulation d\'équipements électrotechniques (moteurs, variateurs de vitesse), et l\'utilisation de logiciels de supervision permettant de piloter et de surveiller à distance des installations électriques automatisées complexes.', '', NULL, NULL, NULL, NULL, NULL, 10, '2026-08-15 21:57:08'),
(342, 18, 'text', 'Une journée type alterne entre la conception ou la modification de schémas électriques et de programmes de contrôle, des interventions sur site pour installer ou dépanner des systèmes automatisés, et des échanges techniques avec les équipes de production pour comprendre les besoins réels d\'un processus industriel donné.', '', NULL, NULL, NULL, NULL, NULL, 11, '2026-08-15 21:57:09'),
(343, 18, 'text', 'La formation développe des compétences en électrotechnique, en automatisme et programmation d\'automates, en informatique industrielle, et en électronique de puissance, une combinaison rare qui permet de concevoir des systèmes électriques automatisés complets et cohérents.', '', NULL, NULL, NULL, NULL, NULL, 12, '2026-08-15 21:57:10'),
(344, 18, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb5123b94.jpg', NULL, NULL, NULL, 13, '2026-08-15 21:57:10'),
(345, 18, 'text', 'La polyvalence technique et une capacité de diagnostic méthodique sont essentielles dans ce métier, car un dysfonctionnement dans un système automatisé complexe peut provenir aussi bien d\'un problème électrique que d\'une erreur de programmation, ce qui demande de savoir naviguer entre plusieurs domaines de compétence à la fois.', '', NULL, NULL, NULL, NULL, NULL, 14, '2026-08-15 21:57:11'),
(346, 18, 'text', 'La formation associe des bases solides en électrotechnique durant la licence à des enseignements spécialisés en automatisme et en informatique industrielle durant le master, avec de nombreux travaux pratiques sur des systèmes automatisés réels reproduisant des conditions industrielles concrètes.', '', NULL, NULL, NULL, NULL, NULL, 15, '2026-08-15 21:57:13'),
(347, 18, 'text', 'Les projets typiques incluent la conception d\'un système électrique automatisé simple, la programmation d\'un automate pour contrôler une installation électrique, ou un projet de fin d\'études portant sur l\'automatisation complète d\'un système énergétique.', '', NULL, NULL, NULL, NULL, NULL, 16, '2026-08-15 21:57:14'),
(348, 18, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb5123b94.jpg', NULL, NULL, NULL, 17, '2026-08-15 21:57:14'),
(349, 18, 'text', 'L\'intégration croissante de systèmes de gestion énergétique intelligents, capables d\'optimiser automatiquement la consommation électrique d\'un bâtiment ou d\'une usine en temps réel selon les besoins réels, constitue un axe d\'innovation majeur et porteur pour cette discipline dans un contexte de hausse des coûts énergétiques.', '', NULL, NULL, NULL, NULL, NULL, 18, '2026-08-15 21:57:15'),
(350, 18, 'text', 'L\'optimisation automatisée de la consommation électrique, permettant de réduire significativement le gaspillage énergétique dans les bâtiments et les installations industrielles, place cette discipline au cœur des enjeux de transition énergétique et d\'efficacité des infrastructures modernes.', '', NULL, NULL, NULL, NULL, NULL, 19, '2026-08-15 21:57:16'),
(351, 18, 'text', 'La modernisation progressive des infrastructures électriques et industrielles à Madagascar crée une demande croissante pour des ingénieurs capables de concevoir et de maintenir des systèmes automatisés fiables, un profil rare et particulièrement recherché par les entreprises industrielles du pays.', '', NULL, NULL, NULL, NULL, NULL, 20, '2026-08-15 21:57:18'),
(352, 18, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb5123b94.jpg', NULL, NULL, NULL, 21, '2026-08-15 21:57:18'),
(353, 18, 'text', 'Après le master, les diplômés peuvent poursuivre vers un doctorat en automatique ou en génie électrique, obtenir des certifications reconnues sur des systèmes de contrôle-commande spécifiques utilisés dans l\'industrie, ou évoluer vers des postes de responsable technique de systèmes électriques automatisés.', '', NULL, NULL, NULL, NULL, NULL, 22, '2026-08-15 21:57:19'),
(354, 18, 'text', 'Obtenir des certifications reconnues en automatisme industriel, participer à des projets d\'automatisation concrets dès la formation, et développer une expertise pratique reconnue sur des équipements réels constituent des atouts particulièrement valorisés par les employeurs de ce secteur technique et exigeant.', '', NULL, NULL, NULL, NULL, NULL, 23, '2026-08-15 21:57:21'),
(355, 18, 'text', 'Cette filière convient aux étudiants qui ne veulent pas choisir entre l\'électricité, l\'automatisme et l\'informatique, mais qui souhaitent au contraire maîtriser ces trois domaines conjointement pour concevoir des systèmes électriques intelligents, une polyvalence de plus en plus valorisée dans l\'industrie moderne.', '', NULL, NULL, NULL, NULL, NULL, 24, '2026-08-15 21:57:22'),
(356, 18, 'image', NULL, NULL, NULL, 'uploads/filiere_6a7cdb5123b94.jpg', NULL, NULL, NULL, 25, '2026-08-15 21:57:22'),
(357, 2, 'text', 'La filière s\'inscrit dans une chaîne de valeur hospitalière claire : l\'acquisition d\'équipements médicaux modernes et conformes, leur maintenance par un suivi préventif rigoureux assuré par des experts formés localement, la réduction des temps d\'arrêt des technologies pour garantir leur disponibilité, et enfin des soins de qualité reposant sur des diagnostics précis et des thérapies continues pour les patients. L\'ingénieur biomédical est le maillon essentiel qui relie ces quatre étapes et garantit la sécurité clinique.', NULL, NULL, NULL, NULL, NULL, NULL, 26, '2026-08-25 19:39:16'),
(358, 2, 'text', 'À l\'Institut Supérieur des Sciences et Technologies de Mahajanga (ISSTM), le parcours Génie Biomédical fait partie intégrante de la mention Sciences du Numérique et Physiques Appliquées (STNPA), aux côtés des parcours Génie Informatique et Électronique Informatique en licence, et Génie Logiciel et Télécoms & Réseaux en master. Il allie sciences fondamentales du numérique et applications médicales réelles, avec une synergie forte entre électronique, instrumentation et informatique, et une professionnalisation soutenue par des stages cliniques en milieu hospitalier. L\'ISSTM organise l\'ensemble de son offre de formation autour de 3 mentions indépendantes — Génies Civils (STGC), Technologies Industrielles (STI) et Numérique & Physiques Appliquées (STNPA) — représentant au total 9 parcours de licence et 10 parcours de master.', NULL, NULL, NULL, NULL, NULL, NULL, 27, '2026-08-25 19:39:16'),
(359, 2, 'text', 'Le cursus du Génie Biomédical se déroule sur cinq années, de la licence au master, avec une immersion pratique progressive : en L1 (Santé & PACES), les étudiants posent les bases théoriques fondamentales en sciences de la santé. En L2 (Technique & Labo), place aux sciences de l\'ingénieur et aux travaux pratiques rigoureux en électronique. En L3 (Biomédical Pro), les étudiants apprennent le fonctionnement des matériels biomédicaux à travers un stage clinique hospitalier et un mémoire de fin de licence. En M1 (Ingénierie Avancée), la formation monte en expertise technique industrielle de haut niveau, complétée par un stage professionnel à l\'extérieur. Enfin, en M2 (PFE & Expertise), les étudiants approfondissent la physique numérique appliquée à travers des travaux pratiques avancés et un long stage de projet de fin d\'études en milieu hospitalier.', NULL, NULL, NULL, NULL, NULL, NULL, 28, '2026-08-25 19:39:16'),
(360, 2, 'text', 'La formation à l\'ISSTM s\'appuie sur des infrastructures pratiques dédiées pour concrétiser les concepts théoriques : des laboratoires spécialisés en informatique, génie électrique et génie électronique, des ateliers pratiques de métrologie et de fabrication mécanique pour la conception de pièces, ainsi qu\'une bibliothèque numérique donnant accès à des milliers d\'ouvrages scientifiques de référence internationale.', NULL, NULL, NULL, NULL, NULL, NULL, 29, '2026-08-25 19:39:16'),
(361, 2, 'text', 'Si le programme de l\'ISSTM offre un excellent niveau théorique, plusieurs défis d\'apprentissage subsistent à Madagascar : l\'accès aux technologies de pointe reste limité, les équipements réels demeurant rares et coûteux au sein des laboratoires universitaires académiques, et le réseau d\'accueil hospitalier pour les stages pratiques doit continuellement s\'élargir afin de confronter les étudiants à des pannes réelles.', NULL, NULL, NULL, NULL, NULL, NULL, 30, '2026-08-25 19:39:16'),
(362, 2, 'text', 'L\'ISSTM porte une vision ambitieuse pour l\'avenir de cette filière : la création progressive d\'un laboratoire de métrologie et d\'essais biomédicaux dédié au sein même de l\'institut pour centraliser la recherche appliquée locale, la signature de nouveaux partenariats cliniques avec des CHU nationaux et des centres médicaux régionaux pour élargir les stages, ainsi que le développement d\'une recherche appliquée tournée vers la conception de solutions techniques à bas coût, adaptées aux réalités électriques et climatiques des hôpitaux de brousse. L\'ambition affichée : former des compétences locales pour garantir la souveraineté technologique de la santé à Madagascar.', NULL, NULL, NULL, NULL, NULL, NULL, 31, '2026-08-25 19:39:16');

-- --------------------------------------------------------

--
-- Structure de la table `gallery_albums`
--

CREATE TABLE `gallery_albums` (
  `id` int(11) NOT NULL,
  `title_fr` varchar(255) NOT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `title_mg` varchar(255) DEFAULT NULL,
  `description_fr` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `description_mg` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `author` varchar(150) DEFAULT NULL,
  `status` enum('brouillon','publie') NOT NULL DEFAULT 'brouillon',
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `gallery_albums`
--

INSERT INTO `gallery_albums` (`id`, `title_fr`, `title_en`, `title_mg`, `description_fr`, `description_en`, `description_mg`, `cover_image`, `category_id`, `event_date`, `location`, `author`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 'Rentrée universitaire 2025-2026', 'Academic year opening 2025-2026', 'Fanokafana ny taom-pianarana 2025-2026', 'Cérémonie officielle de rentrée universitaire à l\'ISSTM, en présence des enseignants, du personnel administratif et des nouveaux étudiants.', NULL, NULL, 'images/slide1.jpg', 2, '2025-09-15', 'Campus ISSTM, Mahajanga', 'Service Communication ISSTM', 'publie', '2026-08-07 23:53:54', '2026-08-07 23:53:54', '2026-08-11 17:51:51'),
(2, 'Tournoi de football inter-mentions', 'Inter-department football tournament', 'Fifaninanana baolina kitra', 'Match amical annuel entre les différentes mentions de l\'ISSTM, organisé par le club sportif étudiant.', NULL, NULL, 'images/portal_assoc_4.jpg', 7, '2025-03-10', 'Terrain de sport de l\'ISSTM', 'Club Sportif ISSTM', 'publie', '2026-08-07 23:53:54', '2026-08-07 23:53:54', '2026-08-07 23:53:54'),
(3, 'Vie sur le campus', 'Campus life', 'Fiainana eny amin\'ny kaompaosy', 'Moments capturés au quotidien sur le campus de l\'ISSTM : bâtiments, espaces verts et vie étudiante.', NULL, NULL, 'images/portal_assoc_6.jpg', 4, '2024-09-01', 'Campus ISSTM, Mahajanga', 'Service Communication ISSTM', 'publie', '2026-08-07 23:53:54', '2026-08-07 23:53:54', '2026-08-13 01:21:48'),
(4, 'Conférence sur l\'innovation numérique', 'Digital innovation conference', 'Kaonferansa momba ny fanavaozana nomerika', 'Conférence-débat avec des professionnels du secteur numérique malgache, organisée pour les étudiants en Génie Informatique.', NULL, NULL, 'images/portal_assoc_5.jpg', 9, '2025-05-15', 'Amphithéâtre ISSTM', 'Département Génie Informatique', 'publie', '2026-08-07 23:53:54', '2026-08-07 23:53:54', '2026-08-07 23:53:54'),
(9, 'Reception 2025-2026', 'Acceptance 2025-2026', 'EFA 2025-2026', 'C\'est une vrai plaisir de contribuer à cette évènement crucial', 'It is a real pleasure to contribute to this crucial event', 'Faly ny fandraisana anjara amin\'ity hetsika goavana ity.', 'uploads/album_cover_6a7d2ee27e069.jpeg', 2, '2026-08-08', 'Saint Gabriel', 'Dr Philibert', 'publie', '2026-08-13 05:41:23', '2026-08-13 05:41:23', '2026-08-13 05:41:38'),
(11, 'Titre test', 'Title Test', 'Lohatenin\'ny fitsapana', 'sdtst', '', '', 'uploads/album_cover_6a808b68842c1.jpeg', 7, '2026-08-22', 'ISSTM Ambondrona', 'Dr Philibert', 'publie', '2026-08-15 18:53:12', '2026-08-15 18:53:12', '2026-08-22 01:41:59');

-- --------------------------------------------------------

--
-- Structure de la table `gallery_categories`
--

CREATE TABLE `gallery_categories` (
  `id` int(11) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `name_fr` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_mg` varchar(100) NOT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'fa-images',
  `display_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `gallery_categories`
--

INSERT INTO `gallery_categories` (`id`, `slug`, `name_fr`, `name_en`, `name_mg`, `icon`, `display_order`) VALUES
(1, 'vie-academique', 'Vie académique', 'Academic life', 'Fiainana akademika', 'fa-graduation-cap', 1),
(2, 'evenements', 'Événements', 'Events', 'Hetsika', 'fa-calendar-days', 2),
(3, 'vie-etudiante', 'Vie étudiante', 'Student life', 'Fiainan\'ny mpianatra', 'fa-users', 3),
(4, 'campus', 'Campus & infrastructures', 'Campus & facilities', 'Kaompaosy sy fotodrafitrasa', 'fa-building-columns', 4),
(5, 'enseignants-admin', 'Enseignants & administration', 'Faculty & staff', 'Mpampianatra sy fitantanana', 'fa-chalkboard-user', 5),
(6, 'partenariats', 'Partenariats', 'Partnerships', 'Fiaraha-miasa', 'fa-handshake', 6),
(7, 'sports', 'Sports', 'Sports', 'Fanatanjahan-tena', 'fa-futbol', 7),
(8, 'ceremonies', 'Cérémonies', 'Ceremonies', 'Lanonana', 'fa-award', 8),
(9, 'conferences', 'Conférences', 'Conferences', 'Kaonferansa', 'fa-microphone-lines', 9),
(10, 'sorties-voyages', 'Sorties / voyages', 'Trips / outings', 'Fitsangatsanganana', 'fa-plane-departure', 10);

-- --------------------------------------------------------

--
-- Structure de la table `gallery_photos`
--

CREATE TABLE `gallery_photos` (
  `id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `media_type` enum('photo','video') NOT NULL DEFAULT 'photo',
  `video_url` varchar(500) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `taken_at` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `photographer` varchar(150) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `gallery_photos`
--

INSERT INTO `gallery_photos` (`id`, `album_id`, `image_path`, `media_type`, `video_url`, `title`, `description`, `taken_at`, `location`, `photographer`, `tags`, `alt_text`, `display_order`, `created_at`) VALUES
(1, 1, 'images/slide1.jpg', 'photo', NULL, 'Entrée de l\'ISSTM décorée', NULL, NULL, NULL, NULL, 'entree, rentree, campus', 'Entrée de l\'ISSTM décorée', 1, '2026-08-07 23:53:54'),
(2, 1, 'images/slide2.jpg', 'photo', NULL, 'Allocution du Directeur', NULL, NULL, NULL, NULL, 'directeur, discours', 'Allocution du Directeur', 2, '2026-08-07 23:53:54'),
(3, 1, 'images/portal_assoc_4.jpg', 'photo', NULL, 'Étudiants pendant la cérémonie', NULL, NULL, NULL, NULL, 'etudiants, ceremonie', 'Étudiants pendant la cérémonie', 3, '2026-08-07 23:53:54'),
(4, 2, 'images/portal_assoc_4.jpg', 'photo', NULL, 'Coup d\'envoi du match', NULL, NULL, NULL, NULL, 'football, sport, match', 'Coup d\'envoi du match', 1, '2026-08-07 23:53:54'),
(5, 2, 'images/portal_assoc_5.jpg', 'photo', NULL, 'Ambiance dans les gradins', NULL, NULL, NULL, NULL, 'supporters, ambiance', 'Ambiance dans les gradins', 2, '2026-08-07 23:53:54'),
(6, 3, 'images/slide2.jpg', 'photo', NULL, 'Bâtiments de l\'ISSTM', NULL, NULL, NULL, NULL, 'batiment, campus', 'Bâtiments de l\'ISSTM', 1, '2026-08-07 23:53:54'),
(7, 3, 'images/slide3.jpg', 'photo', NULL, 'Logo ISSTM sur le bâtiment', NULL, NULL, NULL, NULL, 'logo, facade', 'Logo ISSTM sur le bâtiment', 2, '2026-08-07 23:53:54'),
(8, 3, 'images/portal_assoc_6.jpg', 'photo', NULL, 'Ambiance sur le campus', NULL, NULL, NULL, NULL, 'campus, etudiants', 'Ambiance sur le campus', 3, '2026-08-07 23:53:54'),
(9, 4, 'images/portal_assoc_5.jpg', 'photo', NULL, 'Intervenant en pleine présentation', NULL, NULL, NULL, NULL, 'conference, intervenant', 'Intervenant en pleine présentation', 1, '2026-08-07 23:53:54'),
(28, 9, 'uploads/photo_6a808a7b4ac1a_0.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at f', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at f', 1, '2026-08-15 18:49:15'),
(29, 9, 'uploads/photo_6a808a7b4d12e_1.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 20.04.50', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 20.04.50', 2, '2026-08-15 18:49:15'),
(30, 9, 'uploads/photo_6a808a7b4dc28_2.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 20.04.49', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 20.04.49', 3, '2026-08-15 18:49:15'),
(31, 9, 'uploads/photo_6a808a7b4eb63_3.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 20.04.48njh,uj', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 20.04.48njh,uj', 4, '2026-08-15 18:49:15'),
(32, 9, 'uploads/photo_6a808a7b514b4_4.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 20.04.48', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 20.04.48', 5, '2026-08-15 18:49:15'),
(33, 9, 'uploads/photo_6a808a7b51ff3_5.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 20.04.46nn', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 20.04.46nn', 6, '2026-08-15 18:49:15'),
(34, 9, 'uploads/photo_6a808a7b52ec2_6.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 20.04.46kjk', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 20.04.46kjk', 7, '2026-08-15 18:49:15'),
(35, 9, 'uploads/photo_6a808a7b54cc0_7.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 20.04.46', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 20.04.46', 8, '2026-08-15 18:49:15'),
(36, 9, 'uploads/photo_6a808a7b55991_8.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 20.03.50', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 20.03.50', 9, '2026-08-15 18:49:15'),
(37, 9, 'uploads/photo_6a808a7b564a2_9.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 20.03.49', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 20.03.49', 10, '2026-08-15 18:49:15'),
(38, 9, 'uploads/photo_6a808a7b56f52_10.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 20.03.47', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 20.03.47', 11, '2026-08-15 18:49:15'),
(39, 11, '', 'video', 'uploads/video_6a808ba248877_0.mp4', 'ISSTM ONIVERSITE -  Mahajanga  Mpianatra 109 no no', NULL, NULL, NULL, NULL, NULL, 'ISSTM ONIVERSITE -  Mahajanga  Mpianatra 109 no no', 1, '2026-08-15 18:54:10'),
(40, 9, 'uploads/photo_6a80d5311bcc8_0.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.12', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.12', 12, '2026-08-16 00:08:01'),
(41, 9, 'uploads/photo_6a80d5311deb0_1.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.12 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.12 (1)', 13, '2026-08-16 00:08:01'),
(42, 9, 'uploads/photo_6a80d5311f314_2.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.11', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.11', 14, '2026-08-16 00:08:01'),
(43, 9, 'uploads/photo_6a80d53122505_3.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.11 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.11 (1)', 15, '2026-08-16 00:08:01'),
(44, 9, 'uploads/photo_6a80d531358e8_4.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.11 (2)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.11 (2)', 16, '2026-08-16 00:08:01'),
(45, 9, 'uploads/photo_6a80d5313a8a5_5.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.09', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.09', 17, '2026-08-16 00:08:01'),
(46, 9, 'uploads/photo_6a80d5313ba0a_6.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.10', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.10', 18, '2026-08-16 00:08:01'),
(47, 9, 'uploads/photo_6a80d5313c785_7.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.10 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.10 (1)', 19, '2026-08-16 00:08:01'),
(48, 9, 'uploads/photo_6a80d5313fc20_8.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.09 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.09 (1)', 20, '2026-08-16 00:08:01'),
(49, 9, 'uploads/photo_6a80d53140bea_9.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.09 (2)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.09 (2)', 21, '2026-08-16 00:08:01'),
(50, 9, 'uploads/photo_6a80d531424d3_10.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.08', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.08', 22, '2026-08-16 00:08:01'),
(51, 9, 'uploads/photo_6a80d53143189_11.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.07', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.07', 23, '2026-08-16 00:08:01'),
(52, 9, 'uploads/photo_6a80d5314416d_12.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.07 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.07 (1)', 24, '2026-08-16 00:08:01'),
(53, 9, 'uploads/photo_6a80d53147465_13.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.08 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.08 (1)', 25, '2026-08-16 00:08:01'),
(54, 9, 'uploads/photo_6a80d53148898_14.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.06', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.06', 26, '2026-08-16 00:08:01'),
(55, 9, 'uploads/photo_6a80d5314c456_15.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.07 (2)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.07 (2)', 27, '2026-08-16 00:08:01'),
(56, 9, 'uploads/photo_6a80d5314e0f1_16.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.06 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.06 (1)', 28, '2026-08-16 00:08:01'),
(57, 9, 'uploads/photo_6a80d5314fbd6_17.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.05', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.05', 29, '2026-08-16 00:08:01'),
(58, 9, 'uploads/photo_6a80d53152076_18.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.05 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.05 (1)', 30, '2026-08-16 00:08:01'),
(59, 9, 'uploads/photo_6a80d531534b5_19.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.05 (2)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.05 (2)', 31, '2026-08-16 00:08:01'),
(60, 9, 'uploads/photo_6a80d5315435b_20.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.04', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.04', 32, '2026-08-16 00:08:01'),
(61, 9, 'uploads/photo_6a80d5315507f_21.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.04 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.04 (1)', 33, '2026-08-16 00:08:01'),
(62, 9, 'uploads/photo_6a80d53157a26_22.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.03', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.03', 34, '2026-08-16 00:08:01'),
(63, 9, 'uploads/photo_6a80d5315ae3e_23.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.02', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.02', 35, '2026-08-16 00:08:01'),
(64, 9, 'uploads/photo_6a80d5315bbe8_24.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.03 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.03 (1)', 36, '2026-08-16 00:08:01'),
(65, 9, 'uploads/photo_6a80d5315ce3d_25.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.03 (2)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.03 (2)', 37, '2026-08-16 00:08:01'),
(66, 9, 'uploads/photo_6a80d5315f2e1_26.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.02 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.02 (1)', 38, '2026-08-16 00:08:01'),
(67, 9, 'uploads/photo_6a80d5315fee9_27.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.01', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.01', 39, '2026-08-16 00:08:01'),
(68, 9, 'uploads/photo_6a80d53160f3f_28.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.01 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.01 (1)', 40, '2026-08-16 00:08:01'),
(69, 9, 'uploads/photo_6a80d53163cb2_29.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.00', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.00', 41, '2026-08-16 00:08:01'),
(70, 9, 'uploads/photo_6a80d53164c0f_30.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.00 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.00 (1)', 42, '2026-08-16 00:08:01'),
(71, 9, 'uploads/photo_6a80d531671f3_31.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.01 (2)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.01 (2)', 43, '2026-08-16 00:08:01'),
(72, 9, 'uploads/photo_6a80d53168032_32.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.59', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.59', 44, '2026-08-16 00:08:01'),
(73, 9, 'uploads/photo_6a80d5316c416_33.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.59 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.59 (1)', 45, '2026-08-16 00:08:01'),
(74, 9, 'uploads/photo_6a80d5316f0f3_34.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.58', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.58', 46, '2026-08-16 00:08:01'),
(75, 9, 'uploads/photo_6a80d53170006_35.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.57', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.57', 47, '2026-08-16 00:08:01'),
(76, 9, 'uploads/photo_6a80d53173f74_36.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.58 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.58 (1)', 48, '2026-08-16 00:08:01'),
(77, 9, 'uploads/photo_6a80d53174b7a_37.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.58 (2)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.58 (2)', 49, '2026-08-16 00:08:01'),
(78, 9, 'uploads/photo_6a80d53176cab_38.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.57 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.57 (1)', 50, '2026-08-16 00:08:01'),
(79, 9, 'uploads/photo_6a80d53178a27_39.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.22', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.22', 51, '2026-08-16 00:08:01'),
(80, 9, 'uploads/photo_6a80d531796a6_40.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.20', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.20', 52, '2026-08-16 00:08:01'),
(81, 9, 'uploads/photo_6a80d5317d283_41.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.21', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.21', 53, '2026-08-16 00:08:01'),
(82, 9, 'uploads/photo_6a80d5317f710_42.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.22 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.22 (1)', 54, '2026-08-16 00:08:01'),
(83, 9, 'uploads/photo_6a80d531803b4_43.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.48', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.48', 55, '2026-08-16 00:08:01'),
(84, 9, 'uploads/photo_6a80d5318172b_44.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.47', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.47', 56, '2026-08-16 00:08:01'),
(85, 9, 'uploads/photo_6a80d53184735_45.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.45', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.45', 57, '2026-08-16 00:08:01'),
(86, 9, 'uploads/photo_6a80d53186231_46.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.46', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.46', 58, '2026-08-16 00:08:01'),
(87, 9, 'uploads/photo_6a80d53188569_47.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.46 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.46 (1)', 59, '2026-08-16 00:08:01'),
(88, 9, 'uploads/photo_6a80d53189515_48.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.44', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.44', 60, '2026-08-16 00:08:01'),
(89, 9, 'uploads/photo_6a80d5318b893_49.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.44 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.44 (1)', 61, '2026-08-16 00:08:01'),
(90, 9, 'uploads/photo_6a80d5318ce24_50.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.43', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.43', 62, '2026-08-16 00:08:01'),
(91, 9, 'uploads/photo_6a80d5318f010_51.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.42', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.42', 63, '2026-08-16 00:08:01'),
(92, 9, 'uploads/photo_6a80d531904f5_52.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.42 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.42 (1)', 64, '2026-08-16 00:08:01'),
(93, 9, 'uploads/photo_6a80d53191b47_53.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.43 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.43 (1)', 65, '2026-08-16 00:08:01'),
(94, 9, 'uploads/photo_6a80d531945d0_54.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.41', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.41', 66, '2026-08-16 00:08:01'),
(95, 9, 'uploads/photo_6a80d53195721_55.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.43.17', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.43.17', 67, '2026-08-16 00:08:01'),
(96, 9, 'uploads/photo_6a80d5319772b_56.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.43.13', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.43.13', 68, '2026-08-16 00:08:01'),
(97, 9, 'uploads/photo_6a80d53199ded_57.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.43.14', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.43.14', 69, '2026-08-16 00:08:01'),
(98, 9, 'uploads/photo_6a80d5319c08c_58.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.43.16', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.43.16', 70, '2026-08-16 00:08:01'),
(99, 9, 'uploads/photo_6a80d5319d35f_59.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.43.13 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.43.13 (1)', 71, '2026-08-16 00:08:01'),
(100, 9, 'uploads/photo_6a80d5319e09d_60.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.43.12', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.43.12', 72, '2026-08-16 00:08:01'),
(101, 9, 'uploads/photo_6a80d531a038b_61.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.43.12 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.43.12 (1)', 73, '2026-08-16 00:08:01'),
(102, 9, 'uploads/photo_6a80d53abe292_0.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.05', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.05', 74, '2026-08-16 00:08:10'),
(103, 9, 'uploads/photo_6a80d53ac05fb_1.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.05 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.05 (1)', 75, '2026-08-16 00:08:10'),
(104, 9, 'uploads/photo_6a80d53ac1801_2.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.05 (2)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.05 (2)', 76, '2026-08-16 00:08:10'),
(105, 9, 'uploads/photo_6a80d53ac4729_3.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.04', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.04', 77, '2026-08-16 00:08:10'),
(106, 9, 'uploads/photo_6a80d53ac5ae9_4.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.04 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.04 (1)', 78, '2026-08-16 00:08:10'),
(107, 9, 'uploads/photo_6a80d53ac8815_5.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.03', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.03', 79, '2026-08-16 00:08:10'),
(108, 9, 'uploads/photo_6a80d53accbdb_6.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.02', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.02', 80, '2026-08-16 00:08:10'),
(109, 9, 'uploads/photo_6a80d53acd845_7.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.03 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.03 (1)', 81, '2026-08-16 00:08:10'),
(110, 9, 'uploads/photo_6a80d53ad357f_8.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.03 (2)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.03 (2)', 82, '2026-08-16 00:08:10'),
(111, 9, 'uploads/photo_6a80d53ad4ebd_9.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.02 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.02 (1)', 83, '2026-08-16 00:08:10'),
(112, 9, 'uploads/photo_6a80d53ad64b3_10.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.01', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.01', 84, '2026-08-16 00:08:10'),
(113, 9, 'uploads/photo_6a80d53ad7c26_11.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.01 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.01 (1)', 85, '2026-08-16 00:08:10'),
(114, 9, 'uploads/photo_6a80d53ad93e8_12.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.00', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.00', 86, '2026-08-16 00:08:10'),
(115, 9, 'uploads/photo_6a80d53ada484_13.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.00 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.00 (1)', 87, '2026-08-16 00:08:10'),
(116, 9, 'uploads/photo_6a80d53adde27_14.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.01 (2)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.01 (2)', 88, '2026-08-16 00:08:10'),
(117, 9, 'uploads/photo_6a80d53adec62_15.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.59', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.59', 89, '2026-08-16 00:08:10'),
(118, 9, 'uploads/photo_6a80d53ae0e3c_16.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.59 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.59 (1)', 90, '2026-08-16 00:08:10'),
(119, 9, 'uploads/photo_6a80d53ae2758_17.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.58', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.58', 91, '2026-08-16 00:08:10'),
(120, 9, 'uploads/photo_6a80d53ae455f_18.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.57', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.57', 92, '2026-08-16 00:08:10'),
(121, 9, 'uploads/photo_6a80d53ae5b34_19.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.58 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.58 (1)', 93, '2026-08-16 00:08:10'),
(122, 9, 'uploads/photo_6a80d53ae68f5_20.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.58 (2)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.58 (2)', 94, '2026-08-16 00:08:10'),
(123, 9, 'uploads/photo_6a80d53ae9011_21.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.57 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.57 (1)', 95, '2026-08-16 00:08:10'),
(124, 9, 'uploads/photo_6a80d53ae9c97_22.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.22', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.22', 96, '2026-08-16 00:08:10'),
(125, 9, 'uploads/photo_6a80d53aead10_23.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.20', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.20', 97, '2026-08-16 00:08:10'),
(126, 9, 'uploads/photo_6a80d53aee72d_24.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.21', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.21', 98, '2026-08-16 00:08:10'),
(127, 9, 'uploads/photo_6a80d53af10b1_25.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.10.22 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.10.22 (1)', 99, '2026-08-16 00:08:10'),
(128, 9, 'uploads/photo_6a80d53af1ee6_26.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.48', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.48', 100, '2026-08-16 00:08:10'),
(129, 9, 'uploads/photo_6a80d53af2cb6_27.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.47', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.47', 101, '2026-08-16 00:08:10'),
(130, 9, 'uploads/photo_6a80d53b00915_28.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.45', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.45', 102, '2026-08-16 00:08:11'),
(131, 9, 'uploads/photo_6a80d53b01c99_29.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.46', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.46', 103, '2026-08-16 00:08:11'),
(132, 9, 'uploads/photo_6a80d53b05017_30.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.46 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.46 (1)', 104, '2026-08-16 00:08:11'),
(133, 9, 'uploads/photo_6a80d53b08724_31.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.44', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.44', 105, '2026-08-16 00:08:11'),
(134, 9, 'uploads/photo_6a80d53b09620_32.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.44 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.44 (1)', 106, '2026-08-16 00:08:11'),
(135, 9, 'uploads/photo_6a80d53b0a51c_33.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.43', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.43', 107, '2026-08-16 00:08:11'),
(136, 9, 'uploads/photo_6a80d53b0b03f_34.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.42', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.42', 108, '2026-08-16 00:08:11'),
(137, 9, 'uploads/photo_6a80d53b0cbd8_35.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.42 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.42 (1)', 109, '2026-08-16 00:08:11'),
(138, 9, 'uploads/photo_6a80d53b0dd05_36.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.43 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.43 (1)', 110, '2026-08-16 00:08:11'),
(139, 9, 'uploads/photo_6a80d53b11624_37.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.47.41', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.47.41', 111, '2026-08-16 00:08:11'),
(140, 9, 'uploads/photo_6a80d53b165e9_38.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.43.17', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.43.17', 112, '2026-08-16 00:08:11'),
(141, 9, 'uploads/photo_6a80d53b18dbc_39.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.43.13', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.43.13', 113, '2026-08-16 00:08:11'),
(142, 9, 'uploads/photo_6a80d53b1a47a_40.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.43.14', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.43.14', 114, '2026-08-16 00:08:11'),
(143, 9, 'uploads/photo_6a80d53b1b16b_41.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.43.16', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.43.16', 115, '2026-08-16 00:08:11'),
(144, 9, 'uploads/photo_6a80d53b1d391_42.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.43.13 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.43.13 (1)', 116, '2026-08-16 00:08:11'),
(145, 9, 'uploads/photo_6a80d53b1e33f_43.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.43.12', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.43.12', 117, '2026-08-16 00:08:11'),
(146, 9, 'uploads/photo_6a80d53b1f786_44.jpeg', 'photo', '', 'WhatsApp Image 2026-08-08 at 21.43.12 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-08 at 21.43.12 (1)', 118, '2026-08-16 00:08:11'),
(147, 9, 'uploads/photo_6a80d542908d7_0.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.12', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.12', 119, '2026-08-16 00:08:18'),
(148, 9, 'uploads/photo_6a80d54293765_1.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.12 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.12 (1)', 120, '2026-08-16 00:08:18'),
(149, 9, 'uploads/photo_6a80d54294318_2.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.11', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.11', 121, '2026-08-16 00:08:18'),
(150, 9, 'uploads/photo_6a80d54295063_3.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.11 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.11 (1)', 122, '2026-08-16 00:08:18'),
(151, 9, 'uploads/photo_6a80d542987ca_4.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.11 (2)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.11 (2)', 123, '2026-08-16 00:08:18'),
(152, 9, 'uploads/photo_6a80d5429b53a_5.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.09', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.09', 124, '2026-08-16 00:08:18'),
(153, 9, 'uploads/photo_6a80d5429c63a_6.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.10', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.10', 125, '2026-08-16 00:08:18'),
(154, 9, 'uploads/photo_6a80d5429d2c5_7.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.10 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.10 (1)', 126, '2026-08-16 00:08:18'),
(155, 9, 'uploads/photo_6a80d542a0180_8.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.09 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.09 (1)', 127, '2026-08-16 00:08:18'),
(156, 9, 'uploads/photo_6a80d542a0db0_9.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.09 (2)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.09 (2)', 128, '2026-08-16 00:08:18'),
(157, 9, 'uploads/photo_6a80d542a4009_10.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.08', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.08', 129, '2026-08-16 00:08:18'),
(158, 9, 'uploads/photo_6a80d542a5748_11.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.07', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.07', 130, '2026-08-16 00:08:18'),
(159, 9, 'uploads/photo_6a80d542a78a9_12.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.07 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.07 (1)', 131, '2026-08-16 00:08:18'),
(160, 9, 'uploads/photo_6a80d542a82ed_13.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.08 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.08 (1)', 132, '2026-08-16 00:08:18'),
(161, 9, 'uploads/photo_6a80d542a91c2_14.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.06', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.06', 133, '2026-08-16 00:08:18'),
(162, 9, 'uploads/photo_6a80d542ac25c_15.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.07 (2)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.07 (2)', 134, '2026-08-16 00:08:18'),
(163, 9, 'uploads/photo_6a80d542ad0a0_16.jpeg', 'photo', '', 'WhatsApp Image 2026-08-09 at 10.11.06 (1)', NULL, NULL, NULL, NULL, NULL, 'WhatsApp Image 2026-08-09 at 10.11.06 (1)', 135, '2026-08-16 00:08:18');

-- --------------------------------------------------------

--
-- Structure de la table `groupes_classe`
--

CREATE TABLE `groupes_classe` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `type` enum('classe','enseignants') NOT NULL DEFAULT 'classe',
  `annee` varchar(20) DEFAULT NULL,
  `filiere_id` int(11) DEFAULT NULL,
  `niveau` varchar(10) DEFAULT NULL,
  `code_unique` varchar(10) NOT NULL,
  `enseignant_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `groupes_classe`
--

INSERT INTO `groupes_classe` (`id`, `nom`, `type`, `annee`, `filiere_id`, `niveau`, `code_unique`, `enseignant_id`, `created_at`) VALUES
(2, 'GROUPE TEST', 'classe', '2026', 1, 'L2', '66FQ3G', 23, '2026-08-23 15:37:18'),
(3, 'GROUPE MOISE', 'classe', '2026', 3, 'L1', 'WC32HA', 23, '2026-08-23 15:46:58');

-- --------------------------------------------------------

--
-- Structure de la table `groupes_utilisateurs`
--

CREATE TABLE `groupes_utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `createur_id` int(11) NOT NULL,
  `code_unique` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groupe_annonces`
--

CREATE TABLE `groupe_annonces` (
  `id` int(11) NOT NULL,
  `groupe_id` int(11) NOT NULL,
  `enseignant_id` int(11) NOT NULL,
  `type` enum('examen','resultat','devoir','autre') NOT NULL DEFAULT 'autre',
  `titre` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `date_echeance` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `groupe_annonces`
--

INSERT INTO `groupe_annonces` (`id`, `groupe_id`, `enseignant_id`, `type`, `titre`, `description`, `date_echeance`, `created_at`) VALUES
(2, 3, 23, 'devoir', 'Blabla', 'aaa', '2026-04-12', '2026-08-23 23:33:20');

-- --------------------------------------------------------

--
-- Structure de la table `groupe_membres`
--

CREATE TABLE `groupe_membres` (
  `groupe_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_in_group` enum('enseignant','etudiant') NOT NULL DEFAULT 'etudiant',
  `is_banned` tinyint(1) NOT NULL DEFAULT 0,
  `joined_at` datetime DEFAULT current_timestamp(),
  `last_read_at` datetime DEFAULT NULL,
  `is_delegate` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `groupe_membres`
--

INSERT INTO `groupe_membres` (`groupe_id`, `user_id`, `role_in_group`, `is_banned`, `joined_at`, `last_read_at`, `is_delegate`) VALUES
(2, 10, 'etudiant', 0, '2026-08-23 15:40:33', '2026-08-23 23:34:17', 0),
(2, 23, 'enseignant', 0, '2026-08-23 15:37:18', '2026-08-26 16:11:46', 0),
(3, 10, 'etudiant', 0, '2026-08-23 15:53:49', '2026-08-28 17:41:40', 1),
(3, 23, 'enseignant', 0, '2026-08-23 15:46:58', '2026-09-07 12:00:01', 0);

-- --------------------------------------------------------

--
-- Structure de la table `groupe_messages`
--

CREATE TABLE `groupe_messages` (
  `id` int(11) NOT NULL,
  `groupe_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `deleted_for_everyone_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `groupe_messages`
--

INSERT INTO `groupe_messages` (`id`, `groupe_id`, `sender_id`, `content`, `created_at`, `deleted_for_everyone_at`) VALUES
(3, 2, 23, 'Hi everyone', '2026-08-23 15:37:45', NULL),
(4, 2, 10, 'Salut', '2026-08-23 15:40:51', NULL),
(5, 2, 10, NULL, '2026-08-23 15:41:11', NULL),
(8, 3, 10, 'salut', '2026-08-23 15:54:04', NULL),
(9, 3, 10, 'sdsdsd', '2026-08-23 15:54:09', NULL),
(10, 3, 10, 'sd', '2026-08-23 15:54:10', NULL),
(11, 3, 10, 'sd', '2026-08-23 15:54:10', NULL),
(12, 3, 10, 'd', '2026-08-23 15:54:10', NULL),
(13, 3, 10, 'sd', '2026-08-23 15:54:11', NULL),
(14, 3, 10, 'sd', '2026-08-23 15:54:12', NULL),
(15, 3, 10, '😳😳😳', '2026-08-23 15:54:16', NULL),
(16, 3, 23, 'ytest', '2026-08-23 22:30:37', NULL),
(17, 3, 10, 'okey', '2026-08-23 22:30:41', NULL),
(18, 3, 10, 'http://localhost/ISSTM/groupe_chat.php?id=3', '2026-08-23 22:31:29', NULL),
(19, 3, 10, 'test image', '2026-08-23 22:32:12', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `groupe_message_attachments`
--

CREATE TABLE `groupe_message_attachments` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_type` enum('image','video','file') NOT NULL,
  `mime_type` varchar(150) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `groupe_message_attachments`
--

INSERT INTO `groupe_message_attachments` (`id`, `message_id`, `file_path`, `original_name`, `file_type`, `mime_type`, `file_size`) VALUES
(2, 5, 'uploads/groupes/gmsg_5_6a8aea673cadc.jpg', 'directeur.jpg', 'image', 'image/jpeg', 73165),
(5, 19, 'uploads/groupes/gmsg_19_6a8b4abc6cd30.png', 'fo.png', 'image', 'image/png', 58416);

-- --------------------------------------------------------

--
-- Structure de la table `groupe_message_hides`
--

CREATE TABLE `groupe_message_hides` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `groupe_message_hides`
--

INSERT INTO `groupe_message_hides` (`message_id`, `user_id`) VALUES
(4, 10),
(5, 10);

-- --------------------------------------------------------

--
-- Structure de la table `groupe_presence_marks`
--

CREATE TABLE `groupe_presence_marks` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('present','absent') NOT NULL DEFAULT 'absent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `groupe_presence_marks`
--

INSERT INTO `groupe_presence_marks` (`id`, `session_id`, `user_id`, `status`) VALUES
(1, 1, 10, 'present'),
(14, 10, 10, 'present');

-- --------------------------------------------------------

--
-- Structure de la table `groupe_presence_sessions`
--

CREATE TABLE `groupe_presence_sessions` (
  `id` int(11) NOT NULL,
  `groupe_id` int(11) NOT NULL,
  `session_date` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `groupe_presence_sessions`
--

INSERT INTO `groupe_presence_sessions` (`id`, `groupe_id`, `session_date`, `created_by`, `created_at`) VALUES
(1, 3, '2026-08-23', 23, '2026-08-24 00:21:58'),
(10, 3, '2026-08-18', 23, '2026-08-24 00:39:46');

-- --------------------------------------------------------

--
-- Structure de la table `groupe_utilisateurs_attachments`
--

CREATE TABLE `groupe_utilisateurs_attachments` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(20) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groupe_utilisateurs_hides`
--

CREATE TABLE `groupe_utilisateurs_hides` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groupe_utilisateurs_membres`
--

CREATE TABLE `groupe_utilisateurs_membres` (
  `groupe_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_banned` tinyint(1) NOT NULL DEFAULT 0,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `groupe_utilisateurs_messages`
--

CREATE TABLE `groupe_utilisateurs_messages` (
  `id` int(11) NOT NULL,
  `groupe_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_for_everyone_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `hero_slides`
--

CREATE TABLE `hero_slides` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `media_type` enum('image','video') NOT NULL DEFAULT 'image',
  `display_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `hero_slides`
--

INSERT INTO `hero_slides` (`id`, `image_path`, `media_type`, `display_order`) VALUES
(10, 'images/slide1.jpg', 'image', 1),
(11, 'images/slide2.jpg', 'image', 2),
(12, 'images/slide3.jpg', 'image', 3);

-- --------------------------------------------------------

--
-- Structure de la table `messagerie_attachments`
--

CREATE TABLE `messagerie_attachments` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_type` enum('image','video','file') NOT NULL,
  `mime_type` varchar(150) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messagerie_attachments`
--

INSERT INTO `messagerie_attachments` (`id`, `message_id`, `file_path`, `original_name`, `file_type`, `mime_type`, `file_size`) VALUES
(4, 15, 'uploads/messagerie/msg_15_6a8a3126d6da7.pdf', 'wallet Aout (1).pdf', 'file', 'application/pdf', 1281192),
(5, 21, 'uploads/messagerie/msg_21_6a8a331e2c0a7.xlsx', 'ISSTM UMG_CANEVAS ETUDIANTS_PE_VAC_PAT_RESEX_DIPLOMES_2023-2024.xlsx', 'file', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 532684),
(6, 23, 'uploads/messagerie/msg_23_6a8a3550b76f2.jpg', 'informa.jpg', 'image', 'image/jpeg', 568898),
(7, 23, 'uploads/messagerie/msg_23_6a8a3550b8063.jpg', 'hydraulique.jpg', 'image', 'image/jpeg', 301818);

-- --------------------------------------------------------

--
-- Structure de la table `messagerie_messages`
--

CREATE TABLE `messagerie_messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL,
  `deleted_for_everyone_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messagerie_messages`
--

INSERT INTO `messagerie_messages` (`id`, `sender_id`, `content`, `created_at`, `read_at`, `deleted_for_everyone_at`) VALUES
(8, 6, 'yo', '2026-08-23 02:20:18', '2026-08-23 02:25:34', NULL),
(9, 6, 'yoo', '2026-08-23 02:20:23', '2026-08-23 02:25:34', NULL),
(10, 6, 'ùlmùmù', '2026-08-23 02:20:31', '2026-08-23 02:25:34', NULL),
(11, 7, 'salut kkk', '2026-08-23 02:25:41', '2026-08-23 02:26:09', NULL),
(12, 6, 'yoo', '2026-08-23 02:29:38', '2026-08-23 02:29:41', NULL),
(15, 6, NULL, '2026-08-23 02:30:46', '2026-08-23 02:30:48', NULL),
(21, 7, NULL, '2026-08-23 02:39:10', '2026-08-23 02:39:11', NULL),
(22, 3, 'uiuiu', '2026-08-23 02:47:21', '2026-08-23 02:47:24', NULL),
(23, 3, NULL, '2026-08-23 02:48:32', '2026-08-23 02:48:36', NULL),
(24, 3, NULL, '2026-08-23 02:50:01', '2026-08-23 02:50:06', '2026-08-23 03:01:22'),
(25, 6, NULL, '2026-08-23 02:59:42', '2026-08-23 02:59:43', '2026-08-23 02:59:43'),
(27, 3, '🥶🥶', '2026-08-23 03:06:45', '2026-08-23 03:06:49', NULL),
(28, 6, NULL, '2026-08-23 03:14:48', '2026-08-23 03:14:53', '2026-08-23 03:15:02'),
(30, 6, 'salut', '2026-08-25 23:36:26', '2026-08-25 23:36:32', NULL),
(31, 7, 'ddsf', '2026-09-04 10:34:17', '2026-09-04 23:00:57', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `messagerie_message_hides`
--

CREATE TABLE `messagerie_message_hides` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2019_12_14_000001_create_personal_access_tokens_table', 1);

-- --------------------------------------------------------

--
-- Structure de la table `newsletter_campaigns`
--

CREATE TABLE `newsletter_campaigns` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('programme','envoye') NOT NULL DEFAULT 'programme',
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `recipients_count` int(11) NOT NULL DEFAULT 0,
  `sent_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `newsletter_campaigns`
--

INSERT INTO `newsletter_campaigns` (`id`, `subject`, `message`, `status`, `scheduled_at`, `sent_at`, `recipients_count`, `sent_count`, `created_at`) VALUES
(3, 'libre', 'dsfsdf', 'envoye', NULL, '2026-08-15 19:03:14', 1, 0, '2026-08-15 16:03:11');

-- --------------------------------------------------------

--
-- Structure de la table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(190) NOT NULL,
  `subscribed_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `email`, `subscribed_at`) VALUES
(3, 'mirindraramanana2@gmail.com', '2026-08-10 23:54:52');

-- --------------------------------------------------------

--
-- Structure de la table `news_articles`
--

CREATE TABLE `news_articles` (
  `id` int(11) NOT NULL,
  `title_fr` varchar(255) NOT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `title_mg` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt_fr` varchar(500) DEFAULT NULL,
  `excerpt_en` varchar(500) DEFAULT NULL,
  `excerpt_mg` varchar(500) DEFAULT NULL,
  `content_fr` longtext DEFAULT NULL,
  `content_en` longtext DEFAULT NULL,
  `content_mg` longtext DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `author` varchar(150) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `status` enum('brouillon','publie','archive') NOT NULL DEFAULT 'brouillon',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `news_articles`
--

INSERT INTO `news_articles` (`id`, `title_fr`, `title_en`, `title_mg`, `slug`, `excerpt_fr`, `excerpt_en`, `excerpt_mg`, `content_fr`, `content_en`, `content_mg`, `image_path`, `video_url`, `category_id`, `author`, `tags`, `status`, `is_featured`, `views`, `published_at`, `scheduled_at`, `created_at`, `updated_at`) VALUES
(1, 'Ouverture des inscriptions pour l\'année académique 2026-2027', NULL, NULL, 'ouverture-inscriptions-2026-2027', 'Les inscriptions pour la nouvelle année académique sont désormais ouvertes pour tous les niveaux, de la Licence 1 au Master 2.', NULL, NULL, 'L\'ISSTM a le plaisir d\'annoncer l\'ouverture des inscriptions pour l\'année académique 2026-2027. Les candidats intéressés peuvent déposer leur dossier auprès du service de scolarité à partir du 1er septembre.\n\nLes pièces à fournir ainsi que les frais d\'inscription sont disponibles sur la page Formulaire d\'inscription du site. Pour toute question, le service de scolarité reste à votre disposition du lundi au vendredi.', NULL, NULL, 'images/slide1.jpg', NULL, 13, 'Service Communication ISSTM', 'inscription, rentrée, 2026', 'publie', 1, 22, '2026-08-01 09:00:00', NULL, '2026-08-08 00:55:31', '2026-09-07 11:40:26'),
(2, 'Tournoi sportif inter-mentions : les résultats', NULL, NULL, 'tournoi-sportif-inter-mentions-resultats', 'Retour sur le tournoi de football qui a rassemblé les étudiants des différentes mentions autour d\'une compétition amicale.', NULL, NULL, 'Le tournoi sportif inter-mentions organisé par le club sportif étudiant s\'est déroulé dans une ambiance conviviale. Les équipes de Génie Informatique et de Génie Civil se sont affrontées en finale.\n\nFélicitations à tous les participants pour leur esprit sportif et leur engagement.', NULL, NULL, 'images/portal_assoc_4.jpg', NULL, 9, 'Club Sportif ISSTM', 'sport, football, tournoi', 'publie', 0, 7, '2026-03-12 14:00:00', NULL, '2026-08-08 00:55:31', '2026-08-24 03:42:51'),
(3, 'Conférence sur l\'innovation numérique à Madagascar', NULL, NULL, 'conference-innovation-numerique-madagascar', 'Des professionnels du secteur numérique malgache sont venus échanger avec nos étudiants en Génie Informatique.', NULL, NULL, 'Dans le cadre du renforcement des liens entre le monde académique et professionnel, l\'ISSTM a organisé une conférence-débat sur l\'innovation numérique à Madagascar.\n\nLes intervenants ont partagé leur expérience et répondu aux questions des étudiants sur les opportunités du secteur.', NULL, NULL, 'images/portal_assoc_5.jpg', NULL, 7, 'Département Génie Informatique', 'numérique, conférence, innovation', 'publie', 0, 7, '2026-05-16 10:00:00', NULL, '2026-08-08 00:55:31', '2026-09-04 21:27:27'),
(4, 'Appel à candidatures : bourses d\'études 2026', NULL, NULL, 'appel-candidatures-bourses-2026', 'Les étudiants méritants peuvent désormais déposer leur candidature pour les bourses d\'études de l\'année en cours.', NULL, NULL, 'L\'ISSTM lance un appel à candidatures pour l\'attribution des bourses d\'études au titre de l\'année 2026. Les critères d\'éligibilité ainsi que les documents requis sont détaillés sur la page Bourse du site.\n\nLa date limite de dépôt des dossiers est fixée au 30 septembre.', NULL, NULL, 'images/portal_assoc_6.jpg', NULL, 15, 'Service des Affaires Estudiantines', 'bourse, financement, candidature', 'publie', 0, 11, '2026-07-20 08:30:00', NULL, '2026-08-08 00:55:31', '2026-08-25 21:20:18'),
(5, 'Signature d\'un partenariat avec une entreprise du secteur numérique', NULL, NULL, 'signature-partenariat-entreprise-numerique', 'Un nouveau partenariat a été signé afin de faciliter les stages et l\'insertion professionnelle de nos étudiants.', NULL, NULL, 'L\'ISSTM renforce ses liens avec le monde professionnel à travers la signature d\'une convention de partenariat avec une entreprise locale du secteur numérique. Cette collaboration permettra à nos étudiants d\'accéder à des offres de stage et d\'emploi.', NULL, NULL, 'images/slide2.jpg', NULL, 11, 'Direction ISSTM', 'partenariat, stage, entreprise', 'publie', 0, 3, '2025-11-05 11:00:00', NULL, '2026-08-08 00:55:31', '2026-09-04 21:19:02'),
(6, 'Journée culturelle de l\'ISSTM 2026 (brouillon)', NULL, NULL, 'journee-culturelle-isstm-2026', 'Préparatifs en cours pour la journée culturelle annuelle.', NULL, NULL, 'Article en cours de rédaction sur la journée culturelle à venir.', NULL, NULL, 'images/slide3.jpg', NULL, 10, 'Service Communication ISSTM', 'culture, événement', 'brouillon', 0, 1, NULL, NULL, '2026-08-08 00:55:31', '2026-08-08 01:41:45'),
(8, 'Sortant 2025', '', '', 'sortant-2025', '109 Etudiants sont sorties lors de cette evenement mémoriales', '', '', 'Test articles', '', '', NULL, '', 6, 'Dr Philibert', 'sortie_de_promotion_2026', 'publie', 0, 11, '2025-11-11 01:29:00', NULL, '2026-08-11 01:29:44', '2026-08-25 21:20:23');

-- --------------------------------------------------------

--
-- Structure de la table `news_attachments`
--

CREATE TABLE `news_attachments` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `news_categories`
--

CREATE TABLE `news_categories` (
  `id` int(11) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `name_fr` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_mg` varchar(100) NOT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'fa-newspaper',
  `display_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `news_categories`
--

INSERT INTO `news_categories` (`id`, `slug`, `name_fr`, `name_en`, `name_mg`, `icon`, `display_order`) VALUES
(1, 'vie-universitaire', 'Vie universitaire', 'University life', 'Fiainana anaty oniversite', 'fa-building-columns', 1),
(2, 'vie-etudiante', 'Vie étudiante', 'Student life', 'Fiainan\'ny mpianatra', 'fa-users', 2),
(3, 'education', 'Éducation', 'Education', 'Fanabeazana', 'fa-graduation-cap', 3),
(4, 'formation', 'Formation', 'Training', 'Fiofanana', 'fa-chalkboard-teacher', 4),
(5, 'recherche', 'Recherche', 'Research', 'Fikarohana', 'fa-flask', 5),
(6, 'evenements', 'Événements', 'Events', 'Hetsika', 'fa-calendar-days', 6),
(7, 'conferences', 'Conférences', 'Conferences', 'Kaonferansa', 'fa-microphone-lines', 7),
(8, 'concours', 'Concours', 'Contests', 'Fifaninanana', 'fa-trophy', 8),
(9, 'sports', 'Sports', 'Sports', 'Fanatanjahan-tena', 'fa-futbol', 9),
(10, 'culture', 'Culture', 'Culture', 'Kolontsaina', 'fa-masks-theater', 10),
(11, 'partenariats', 'Partenariats', 'Partnerships', 'Fiaraha-miasa', 'fa-handshake', 11),
(12, 'communiques', 'Communiqués', 'Press releases', 'Filazana', 'fa-bullhorn', 12),
(13, 'annonces', 'Annonces', 'Announcements', 'Fanambarana', 'fa-thumbtack', 13),
(14, 'administration', 'Administration', 'Administration', 'Fitantanana', 'fa-building', 14),
(15, 'opportunites', 'Opportunités', 'Opportunities', 'Vintana tsara', 'fa-lightbulb', 15),
(16, 'stages', 'Stages', 'Internships', 'Stazy', 'fa-briefcase', 16),
(17, 'emploi', 'Emploi', 'Jobs', 'Asa', 'fa-user-tie', 17),
(18, 'resultats', 'Résultats', 'Results', 'Vokatra', 'fa-list-check', 18);

-- --------------------------------------------------------

--
-- Structure de la table `news_photos`
--

CREATE TABLE `news_photos` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL DEFAULT '',
  `media_type` enum('photo','video') NOT NULL DEFAULT 'photo',
  `video_url` varchar(500) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `news_photos`
--

INSERT INTO `news_photos` (`id`, `article_id`, `image_path`, `media_type`, `video_url`, `title`, `display_order`, `created_at`) VALUES
(3, 8, '', 'video', 'uploads/news_video_6a808aaf91658_0.mp4', 'ISSTM ONIVERSITE -  Mahajanga  Mpianatra 109 no no', 1, '2026-08-15 15:50:07');

-- --------------------------------------------------------

--
-- Structure de la table `org_people`
--

CREATE TABLE `org_people` (
  `title_key` varchar(64) NOT NULL,
  `name` varchar(150) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `org_people`
--

INSERT INTO `org_people` (`title_key`, `name`, `photo`, `updated_at`, `sort_order`) VALUES
('agent_affaires', 'Mme. Secrétaire P.', 'secretaire.jpg', '2026-08-22 22:10:26', 27),
('college_enseignants', 'Mme. Nathalie V.', 'nathalie.jpg', '2026-08-22 22:10:26', 5),
('conseil_etablissement', 'Organe collégial', NULL, '2026-08-22 22:10:26', 1),
('conseil_scientifique', 'M. Lovas R.', 'lovas.jpg', '2026-08-22 22:10:09', 4),
('coordo_pedagogique', 'Mme. Nathalie V.', 'nathalie.jpg', '2026-08-22 22:10:26', 9),
('directeur', 'Dr. Hary Tiana R.', 'directeur.jpg', '2026-08-22 22:10:26', 2),
('division_coop', 'Mme. Gestion F.', 'gestion.jpg', '2026-08-22 22:10:26', 23),
('division_labo', 'M. Lovas R.', 'lovas.jpg', '2026-08-22 22:10:26', 24),
('division_logistique', 'M. Chrysostome', 'chrysostome.jpg', '2026-08-22 22:10:26', 37),
('division_relations', 'M. Maxwell A.', 'maxwell.jpg', '2026-08-22 22:10:26', 25),
('division_technique', 'M. Telesphore', 'telesphore.jpg', '2026-08-22 22:10:26', 38),
('mention_gc', 'Dr. Charles R.', 'charles.jpg', '2026-08-22 22:10:26', 18),
('mention_sti', 'M. Telesphore', 'telesphore.jpg', '2026-08-22 22:10:26', 14),
('mention_stnpa', 'M. Hary L.', 'hary.jpg', '2026-08-22 22:10:26', 10),
('parcours_garchi', 'M. Moïse D.', 'moise.jpg', '2026-08-22 22:10:26', 21),
('parcours_gb', 'M. Chrysostome', 'chrysostome.jpg', '2026-08-22 22:10:26', 12),
('parcours_gcivil', 'Dr. Charles R.', 'charles.jpg', '2026-08-22 22:10:26', 19),
('parcours_ge', 'M. Maxwell A.', 'maxwell.jpg', '2026-08-22 22:10:26', 15),
('parcours_gei', 'M. Telesphore', 'telesphore.jpg', '2026-08-22 22:10:26', 13),
('parcours_ghyd', 'M. Moïse D.', 'moise.jpg', '2026-08-22 22:10:09', 20),
('parcours_gi', 'M. Hary L.', 'hary.jpg', '2026-08-22 22:10:26', 11),
('parcours_gind', 'Mme. Gestion F.', 'gestion.jpg', '2026-08-22 22:10:26', 16),
('parcours_gt', 'M. Lovas R.', 'lovas.jpg', '2026-08-22 22:10:26', 17),
('prmp', 'Mme. Gestion F.', 'gestion.jpg', '2026-08-22 22:10:26', 3),
('resp_biblio', 'Mme. Nathalie V.', 'nathalie.jpg', '2026-08-22 22:10:26', 39),
('resp_comm', 'M. Judickael M.', 'judickael.jpg', '2026-08-22 22:10:26', 8),
('resp_diplomes', 'M. Judickael M.', 'judickael.jpg', '2026-08-22 22:10:26', 35),
('resp_qualite', 'M. Maxwell A.', 'maxwell.jpg', '2026-08-22 22:10:26', 7),
('resp_stats_diplomes', 'M. Maxwell A.', 'maxwell.jpg', '2026-08-22 22:10:26', 28),
('secretaire_principal', 'Mme. Secrétaire P.', 'secretaire.jpg', '2026-08-22 22:10:26', 29),
('secretariat_direction', 'Mme. Secrétaire P.', 'secretaire.jpg', '2026-08-22 22:10:26', 6),
('secretariat_licence', 'Mme. Secrétaire P.', 'secretaire.jpg', '2026-08-22 22:10:26', 34),
('secretariat_master', 'Mme. Nathalie V.', 'nathalie.jpg', '2026-08-22 22:10:09', 33),
('service_compta', 'M. Chrysostome', 'chrysostome.jpg', '2026-08-22 22:10:26', 30),
('service_cooperation', 'Mme. Gestion F.', 'gestion.jpg', '2026-08-22 22:10:26', 22),
('service_logistique', 'M. Chrysostome', 'chrysostome.jpg', '2026-08-22 22:10:09', 36),
('service_numerique', 'M. Judickael M.', 'judickael.jpg', '2026-08-22 22:10:26', 31),
('service_scolarite', 'Mme. Secrétaire P.', 'secretaire.jpg', '2026-08-22 22:10:26', 32),
('service_stats', 'M. Maxwell A.', 'maxwell.jpg', '2026-08-22 22:10:26', 26);

-- --------------------------------------------------------

--
-- Structure de la table `page_views`
--

CREATE TABLE `page_views` (
  `id` int(11) NOT NULL,
  `page` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `page_views`
--

INSERT INTO `page_views` (`id`, `page`, `user_id`, `created_at`) VALUES
(1, 'communaute.php', 3, '2026-08-26 13:05:05'),
(2, '404.php', 3, '2026-08-26 13:05:12'),
(3, 'evenements.php', NULL, '2026-08-26 13:05:17'),
(4, 'vie_etudiante.php', NULL, '2026-08-26 13:05:19'),
(5, 'campus.php', NULL, '2026-08-26 13:05:21'),
(6, 'galerie.php', NULL, '2026-08-26 13:05:22'),
(7, 'groupe_chat.php', 23, '2026-08-26 13:06:44'),
(8, 'groupe_chat.php', 23, '2026-08-26 13:07:54'),
(9, 'groupe_chat.php', 23, '2026-08-26 13:08:39'),
(10, 'groupe_chat.php', 23, '2026-08-26 13:09:17'),
(11, 'groupe_chat.php', 23, '2026-08-26 13:09:50'),
(12, 'groupe_chat.php', 23, '2026-08-26 13:10:38'),
(13, 'groupe_chat.php', 23, '2026-08-26 13:11:16'),
(14, 'groupe_chat.php', 23, '2026-08-26 13:11:44'),
(15, 'login.php', NULL, '2026-08-26 13:19:02'),
(16, 'index.php', NULL, '2026-08-26 13:20:06'),
(17, 'login.php', NULL, '2026-08-26 13:20:13'),
(18, 'index.php', NULL, '2026-08-26 13:21:21'),
(19, 'login.php', NULL, '2026-08-26 13:21:37'),
(20, 'mes_groupes.php', 23, '2026-08-26 13:22:10'),
(21, 'index.php', NULL, '2026-08-26 13:26:25'),
(22, 'login.php', NULL, '2026-08-26 13:26:28'),
(23, 'mes_groupes.php', 23, '2026-08-26 13:26:35'),
(24, 'groupe_chat.php', 23, '2026-08-26 13:26:43'),
(25, 'mes_groupes.php', 23, '2026-08-26 13:26:51'),
(26, 'groupe_chat.php', 23, '2026-08-26 13:26:54'),
(27, 'groupe_chat.php', 23, '2026-08-26 13:27:14'),
(28, 'groupe_chat.php', 23, '2026-08-26 13:32:50'),
(29, 'annuaire.php', 65, '2026-08-26 13:38:27'),
(30, 'profil_public.php', 65, '2026-08-26 13:38:32'),
(31, 'mes_amis.php', 66, '2026-08-26 13:38:39'),
(32, 'mes_amis.php', 65, '2026-08-26 13:38:46'),
(33, 'messages_prives.php', 65, '2026-08-26 13:38:47'),
(34, 'messages_prives.php', 66, '2026-08-26 13:38:55'),
(35, 'messages_prives.php', 65, '2026-08-26 13:39:02'),
(36, 'mes_groupes_perso.php', 65, '2026-08-26 13:39:04'),
(37, 'mes_groupes_perso.php', 65, '2026-08-26 13:39:10'),
(38, 'mes_groupes_perso.php', 66, '2026-08-26 13:39:11'),
(39, 'mes_groupes_perso.php', 66, '2026-08-26 13:39:17'),
(40, 'groupe_perso_chat.php', 66, '2026-08-26 13:39:18'),
(41, 'mes_groupes_perso.php', 65, '2026-08-26 13:39:19'),
(42, 'mes_groupes_perso.php', 65, '2026-08-26 13:39:26'),
(43, 'index.php', NULL, '2026-08-26 13:40:04'),
(44, 'login.php', NULL, '2026-08-26 13:40:04'),
(45, 'evenements.php', NULL, '2026-08-26 13:40:05'),
(46, 'vie_etudiante.php', NULL, '2026-08-26 13:40:06'),
(47, 'campus.php', NULL, '2026-08-26 13:40:06'),
(48, 'groupe_chat.php', 23, '2026-08-26 13:40:49'),
(49, 'communaute.php', 23, '2026-08-26 13:41:30'),
(50, 'profil.php', 23, '2026-08-26 13:41:32'),
(51, 'profil.php', 23, '2026-08-26 13:42:02'),
(52, 'mes_amis.php', 23, '2026-08-26 13:42:13'),
(53, 'annuaire.php', 23, '2026-08-26 13:42:19'),
(54, 'annuaire.php', 23, '2026-08-26 13:42:28'),
(55, 'profil_public.php', 23, '2026-08-26 13:42:33'),
(56, 'annuaire.php', 23, '2026-08-26 13:42:45'),
(57, 'messages_prives.php', 23, '2026-08-26 13:42:54'),
(58, 'annuaire.php', 23, '2026-08-26 13:43:04'),
(59, 'index.php', NULL, '2026-08-26 13:43:12'),
(60, 'login.php', NULL, '2026-08-26 13:43:15'),
(61, 'profil.php', 10, '2026-08-26 13:43:24'),
(62, 'mes_amis.php', 10, '2026-08-26 13:43:27'),
(63, 'mes_amis.php', 10, '2026-08-26 13:43:35'),
(64, 'profil_public.php', 10, '2026-08-26 13:43:38'),
(65, 'messages_prives.php', 10, '2026-08-26 13:43:42'),
(66, 'messages_prives.php', 10, '2026-08-26 13:43:53'),
(67, 'mes_amis.php', 10, '2026-08-26 13:44:08'),
(68, 'profil.php', 10, '2026-08-26 13:44:13'),
(69, 'mes_groupes.php', 10, '2026-08-26 13:45:46'),
(70, 'mes_groupes_perso.php', 10, '2026-08-26 13:45:49'),
(71, 'messages_prives.php', 10, '2026-08-26 13:46:30'),
(72, 'communaute.php', 10, '2026-08-26 13:46:34'),
(73, 'messages_prives.php', 10, '2026-08-26 13:47:39'),
(74, 'messages_prives.php', 10, '2026-08-26 13:48:00'),
(75, 'profil.php', 10, '2026-08-26 13:49:58'),
(76, 'messages_prives.php', 10, '2026-08-26 13:50:57'),
(77, 'mes_groupes_perso.php', 10, '2026-08-26 13:51:01'),
(78, 'index.php', NULL, '2026-08-26 13:51:57'),
(79, 'login.php', NULL, '2026-08-26 13:51:59'),
(80, 'login.php', NULL, '2026-08-26 14:01:59'),
(81, 'enseignants.php', NULL, '2026-08-26 14:09:41'),
(82, 'index.php', NULL, '2026-08-26 14:09:47'),
(83, 'login.php', NULL, '2026-08-26 14:09:52'),
(84, 'login.php', NULL, '2026-08-26 14:10:03'),
(85, 'login.php', NULL, '2026-08-26 14:10:12'),
(86, 'mes_groupes.php', 23, '2026-08-26 14:10:23'),
(87, 'mes_amis.php', 23, '2026-08-26 14:10:38'),
(88, 'mes_amis.php', 23, '2026-08-26 14:10:43'),
(89, 'mes_amis.php', 23, '2026-08-26 14:10:53'),
(90, 'mes_amis.php', 23, '2026-08-26 14:10:55'),
(91, 'annuaire.php', 23, '2026-08-26 14:10:58'),
(92, 'mes_amis.php', 23, '2026-08-26 14:11:01'),
(93, 'mes_amis.php', 23, '2026-08-26 14:11:08'),
(94, 'mes_amis.php', 23, '2026-08-26 14:11:14'),
(95, 'communaute.php', 23, '2026-08-26 14:11:17'),
(96, 'mes_groupes.php', 23, '2026-08-26 14:12:34'),
(97, 'mes_amis.php', 23, '2026-08-26 14:12:37'),
(98, 'messages_prives.php', 23, '2026-08-26 14:12:40'),
(99, 'index.php', NULL, '2026-08-26 14:12:58'),
(100, 'login.php', NULL, '2026-08-26 14:13:00'),
(101, 'index.php', NULL, '2026-08-26 14:21:44'),
(102, 'login.php', NULL, '2026-08-26 14:21:45'),
(103, 'login.php', NULL, '2026-08-26 14:21:54'),
(104, 'index.php', NULL, '2026-08-26 14:21:55'),
(105, 'mes_amis.php', 68, '2026-08-26 14:23:45'),
(106, 'mes_amis.php', 68, '2026-08-26 14:23:49'),
(107, 'mes_amis.php', 68, '2026-08-26 14:23:49'),
(108, 'mes_amis.php', 68, '2026-08-26 14:23:50'),
(109, 'mes_amis.php', 68, '2026-08-26 14:23:50'),
(110, 'mes_amis.php', 68, '2026-08-26 14:23:51'),
(111, 'mes_amis.php', 68, '2026-08-26 14:23:51'),
(112, 'annuaire.php', 68, '2026-08-26 14:23:51'),
(113, 'index.php', 68, '2026-08-26 14:23:51'),
(114, 'communaute.php', 68, '2026-08-26 14:23:53'),
(115, 'profil_public.php', 68, '2026-08-26 14:23:55'),
(116, 'mes_amis.php', 69, '2026-08-26 14:24:02'),
(117, 'messages_prives.php', 68, '2026-08-26 14:24:09'),
(118, 'notifications.php', 68, '2026-08-26 14:24:12'),
(119, 'index.php', 68, '2026-08-26 14:24:19'),
(120, 'profil.php', 68, '2026-08-26 14:24:20'),
(121, 'profil.php', 68, '2026-08-26 14:24:26'),
(122, 'profil.php', 68, '2026-08-26 14:24:27'),
(123, 'profil_public.php', 69, '2026-08-26 14:24:27'),
(124, 'login.php', NULL, '2026-08-26 14:24:28'),
(125, 'login.php', NULL, '2026-08-26 14:24:30'),
(126, 'login.php', NULL, '2026-08-26 14:24:31'),
(127, 'mes_amis.php', 68, '2026-08-26 14:25:36'),
(128, 'mes_amis.php', 68, '2026-08-26 14:25:38'),
(129, 'mes_amis.php', 68, '2026-08-26 14:25:38'),
(130, 'mes_amis.php', 68, '2026-08-26 14:25:39'),
(131, 'mes_amis.php', 68, '2026-08-26 14:25:39'),
(132, 'mes_amis.php', 68, '2026-08-26 14:25:39'),
(133, 'mes_amis.php', 68, '2026-08-26 14:25:40'),
(134, 'annuaire.php', 68, '2026-08-26 14:25:40'),
(135, 'index.php', 68, '2026-08-26 14:25:42'),
(136, 'communaute.php', 68, '2026-08-26 14:25:44'),
(137, 'profil_public.php', 68, '2026-08-26 14:25:45'),
(138, 'mes_amis.php', 69, '2026-08-26 14:25:52'),
(139, 'messages_prives.php', 68, '2026-08-26 14:26:00'),
(140, 'mes_amis.php', 3, '2026-08-26 14:26:02'),
(141, 'mes_amis.php', 3, '2026-08-26 14:26:04'),
(142, 'notifications.php', 68, '2026-08-26 14:26:09'),
(143, 'mes_amis.php', 3, '2026-08-26 14:26:14'),
(144, 'mes_amis.php', 3, '2026-08-26 14:26:16'),
(145, 'index.php', 68, '2026-08-26 14:26:18'),
(146, 'mes_amis.php', 3, '2026-08-26 14:26:18'),
(147, 'mes_amis.php', 3, '2026-08-26 14:26:20'),
(148, 'profil.php', 68, '2026-08-26 14:26:24'),
(149, 'profil.php', 68, '2026-08-26 14:26:30'),
(150, 'profil.php', 68, '2026-08-26 14:26:31'),
(151, 'profil_public.php', 69, '2026-08-26 14:26:33'),
(152, 'mes_amis.php', 3, '2026-08-26 14:26:34'),
(153, 'login.php', NULL, '2026-08-26 14:26:35'),
(154, 'messagerie.php', 3, '2026-08-26 14:26:36'),
(155, 'login.php', NULL, '2026-08-26 14:26:38'),
(156, 'login.php', NULL, '2026-08-26 14:26:40'),
(157, 'mes_amis.php', 3, '2026-08-26 14:26:43'),
(158, 'mes_amis.php', 3, '2026-08-26 14:26:47'),
(159, 'index.php', NULL, '2026-08-26 14:27:03'),
(160, 'login.php', NULL, '2026-08-26 14:27:07'),
(161, 'login.php', NULL, '2026-08-26 14:27:20'),
(162, 'mes_amis.php', 68, '2026-08-26 14:27:22'),
(163, 'mes_amis.php', 68, '2026-08-26 14:27:29'),
(164, 'mes_amis.php', 68, '2026-08-26 14:27:34'),
(165, 'index.php', NULL, '2026-08-26 14:28:50'),
(166, 'login.php', NULL, '2026-08-26 14:28:50'),
(167, 'evenements.php', NULL, '2026-08-26 14:28:54'),
(168, 'vie_etudiante.php', NULL, '2026-08-26 14:28:56'),
(169, 'campus.php', NULL, '2026-08-26 14:28:56'),
(170, 'login.php', NULL, '2026-08-26 14:32:58'),
(171, 'login.php', NULL, '2026-08-26 14:33:04'),
(172, 'inscription.php', NULL, '2026-08-26 14:33:35'),
(173, 'preinscription.php', NULL, '2026-08-26 14:33:40'),
(174, 'index.php', NULL, '2026-08-26 14:33:45'),
(175, 'login.php', NULL, '2026-08-26 14:33:47'),
(176, 'index.php', 3, '2026-08-26 14:37:54'),
(177, 'index.php', NULL, '2026-08-26 14:38:02'),
(178, 'login.php', NULL, '2026-08-26 14:38:04'),
(179, 'login.php', NULL, '2026-08-26 15:07:04'),
(180, 'login.php', NULL, '2026-08-26 15:07:08'),
(181, 'login.php', NULL, '2026-08-26 15:09:11'),
(182, 'login.php', NULL, '2026-08-26 15:09:38'),
(183, 'login.php', NULL, '2026-08-26 15:09:56'),
(184, 'login.php', NULL, '2026-08-26 15:10:08'),
(185, 'login.php', NULL, '2026-08-26 15:10:12'),
(186, 'login.php', NULL, '2026-08-26 15:10:15'),
(187, 'login.php', NULL, '2026-08-26 15:10:41'),
(188, 'login.php', NULL, '2026-08-26 15:11:09'),
(189, 'login.php', NULL, '2026-08-26 15:11:47'),
(190, 'login.php', NULL, '2026-08-26 15:11:50'),
(191, 'login.php', NULL, '2026-08-26 15:12:28'),
(192, 'login.php', NULL, '2026-08-26 15:12:57'),
(193, 'login.php', NULL, '2026-08-26 15:14:14'),
(194, 'login.php', NULL, '2026-08-26 15:14:47'),
(195, 'login.php', NULL, '2026-08-26 15:14:49'),
(196, 'login.php', NULL, '2026-08-26 15:15:13'),
(197, 'login.php', NULL, '2026-08-26 15:17:44'),
(198, 'login.php', NULL, '2026-08-26 15:17:48'),
(199, 'login.php', NULL, '2026-08-26 15:17:49'),
(200, 'login.php', NULL, '2026-08-26 15:17:56'),
(201, 'login.php', NULL, '2026-08-26 15:19:57'),
(202, 'login.php', NULL, '2026-08-26 15:20:02'),
(203, 'historique.php', NULL, '2026-08-26 15:20:10'),
(204, 'login.php', NULL, '2026-08-26 15:20:12'),
(205, 'index.php', NULL, '2026-08-26 15:20:13'),
(206, 'login.php', NULL, '2026-08-26 15:20:16'),
(207, 'login.php', NULL, '2026-08-26 15:20:19'),
(208, 'login.php', NULL, '2026-08-26 15:21:02'),
(209, 'login.php', NULL, '2026-08-26 15:21:27'),
(210, 'login.php', NULL, '2026-08-26 15:24:51'),
(211, 'login.php', NULL, '2026-08-26 15:28:31'),
(212, 'login.php', NULL, '2026-08-26 15:28:39'),
(213, 'login.php', NULL, '2026-08-26 15:29:29'),
(214, 'login.php', NULL, '2026-08-26 15:30:26'),
(215, 'login.php', NULL, '2026-08-26 15:30:30'),
(216, 'login.php', NULL, '2026-08-26 15:35:00'),
(217, 'login.php', 3, '2026-08-26 15:39:49'),
(218, 'login.php', NULL, '2026-08-26 15:40:35'),
(219, 'login.php', NULL, '2026-08-26 15:42:13'),
(220, 'login.php', NULL, '2026-08-26 15:49:37'),
(221, 'login.php', NULL, '2026-08-26 15:50:40'),
(222, 'login.php', NULL, '2026-08-26 15:52:30'),
(223, 'login.php', NULL, '2026-08-26 15:53:09'),
(224, 'login.php', NULL, '2026-08-26 15:54:43'),
(225, 'login.php', NULL, '2026-08-26 15:55:35'),
(226, 'login.php', NULL, '2026-08-26 15:56:21'),
(227, 'login.php', NULL, '2026-08-26 15:56:46'),
(228, 'index.php', NULL, '2026-08-26 15:57:06'),
(229, 'login.php', NULL, '2026-08-26 15:57:09'),
(230, 'login.php', NULL, '2026-08-26 16:07:25'),
(231, 'login.php', NULL, '2026-08-26 18:42:50'),
(232, 'index.php', NULL, '2026-08-28 13:02:29'),
(233, 'index.php', NULL, '2026-08-28 13:02:30'),
(234, 'login.php', NULL, '2026-08-28 13:04:19'),
(235, 'index.php', NULL, '2026-08-28 13:36:05'),
(236, 'index.php', NULL, '2026-08-28 13:41:09'),
(237, 'index.php', NULL, '2026-08-28 13:50:18'),
(238, '404.php', NULL, '2026-08-28 13:50:18'),
(239, '404.php', NULL, '2026-08-28 13:50:19'),
(240, 'evenements.php', NULL, '2026-08-28 13:50:25'),
(241, 'index.php', NULL, '2026-08-28 13:50:55'),
(242, 'login.php', NULL, '2026-08-28 13:50:57'),
(243, 'vie_etudiante.php', 3, '2026-08-28 13:52:24'),
(244, 'notifications.php', 3, '2026-08-28 13:53:16'),
(245, 'notifications.php', 3, '2026-08-28 13:59:14'),
(246, 'notifications.php', 3, '2026-08-28 14:02:15'),
(247, 'notifications.php', 3, '2026-08-28 14:04:12'),
(248, 'notifications.php', 3, '2026-08-28 14:04:45'),
(249, 'notifications.php', 3, '2026-08-28 14:05:43'),
(250, 'notifications.php', 3, '2026-08-28 14:06:34'),
(251, 'notifications.php', 3, '2026-08-28 14:06:47'),
(252, 'notifications.php', 3, '2026-08-28 14:09:07'),
(253, 'notifications.php', 3, '2026-08-28 14:09:22'),
(254, 'notifications.php', 3, '2026-08-28 14:09:46'),
(255, 'notifications.php', 3, '2026-08-28 14:12:22'),
(256, 'notifications.php', 3, '2026-08-28 14:12:37'),
(257, 'notifications.php', 3, '2026-08-28 14:12:46'),
(258, 'notifications.php', 3, '2026-08-28 14:12:57'),
(259, 'notifications.php', 3, '2026-08-28 14:13:05'),
(260, 'notifications.php', 3, '2026-08-28 14:13:40'),
(261, 'communaute.php', 3, '2026-08-28 14:13:45'),
(262, 'messagerie.php', 3, '2026-08-28 14:14:01'),
(263, 'mes_amis.php', 3, '2026-08-28 14:14:06'),
(264, 'actualite.php', 3, '2026-08-28 14:14:09'),
(265, 'mes_amis.php', 3, '2026-08-28 14:15:12'),
(266, 'index.php', NULL, '2026-08-28 14:15:18'),
(267, 'login.php', NULL, '2026-08-28 14:15:21'),
(268, 'notifications.php', 3, '2026-08-28 14:18:23'),
(269, 'notifications.php', 3, '2026-08-28 14:18:35'),
(270, 'notifications.php', 3, '2026-08-28 14:18:38'),
(271, 'notifications.php', 3, '2026-08-28 14:19:12'),
(272, 'notifications.php', 3, '2026-08-28 14:21:52'),
(273, 'notifications.php', 3, '2026-08-28 14:22:31'),
(274, 'notifications.php', 3, '2026-08-28 14:23:12'),
(275, 'notifications.php', 3, '2026-08-28 14:23:29'),
(276, 'notifications.php', 3, '2026-08-28 14:23:32'),
(277, 'actualite.php', 3, '2026-08-28 14:27:44'),
(278, 'index.php', 3, '2026-08-28 14:27:48'),
(279, 'documents.php', 3, '2026-08-28 14:28:03'),
(280, 'index.php', 3, '2026-08-28 14:28:14'),
(281, 'filieres.php', 3, '2026-08-28 14:28:31'),
(282, 'index.php', 3, '2026-08-28 14:28:39'),
(283, 'historique.php', 3, '2026-08-28 14:29:41'),
(284, 'parcours.php', 3, '2026-08-28 14:29:58'),
(285, 'filieres.php', 3, '2026-08-28 14:30:25'),
(286, 'filiere_detail.php', 3, '2026-08-28 14:30:31'),
(287, 'filieres.php', 3, '2026-08-28 14:30:44'),
(288, 'enseignants.php', 3, '2026-08-28 14:30:52'),
(289, 'vie_etudiante.php', 3, '2026-08-28 14:31:21'),
(290, 'campus.php', 3, '2026-08-28 14:31:26'),
(291, 'vie_etudiante.php', 3, '2026-08-28 14:31:51'),
(292, 'associations.php', 3, '2026-08-28 14:31:55'),
(293, 'vie_etudiante.php', 3, '2026-08-28 14:32:18'),
(294, 'bourse.php', 3, '2026-08-28 14:32:24'),
(295, 'galerie.php', 3, '2026-08-28 14:32:42'),
(296, 'galerie.php', 3, '2026-08-28 14:32:57'),
(297, 'galerie.php', 3, '2026-08-28 14:32:59'),
(298, 'galerie.php', 3, '2026-08-28 14:33:01'),
(299, 'galerie.php', 3, '2026-08-28 14:33:03'),
(300, 'galerie.php', 3, '2026-08-28 14:33:11'),
(301, 'galerie.php', 3, '2026-08-28 14:33:15'),
(302, 'galerie.php', 3, '2026-08-28 14:33:17'),
(303, 'galerie.php', 3, '2026-08-28 14:33:20'),
(304, 'galerie.php', 3, '2026-08-28 14:33:24'),
(305, 'galerie_album.php', 3, '2026-08-28 14:33:29'),
(306, 'galerie.php', 3, '2026-08-28 14:33:32'),
(307, 'galerie_album.php', 3, '2026-08-28 14:33:33'),
(308, 'galerie.php', 3, '2026-08-28 14:33:56'),
(309, 'galerie_album.php', 3, '2026-08-28 14:33:58'),
(310, 'galerie.php', 3, '2026-08-28 14:34:08'),
(311, 'evenements.php', 3, '2026-08-28 14:34:12'),
(312, 'index.php', 3, '2026-08-28 14:34:38'),
(313, '404.php', 3, '2026-08-28 14:34:38'),
(314, '404.php', 3, '2026-08-28 14:34:38'),
(315, 'index.php', 3, '2026-08-28 14:35:00'),
(316, '404.php', 3, '2026-08-28 14:35:00'),
(317, '404.php', 3, '2026-08-28 14:35:00'),
(318, 'documents.php', 3, '2026-08-28 14:35:03'),
(319, 'profil.php', 3, '2026-08-28 14:35:20'),
(320, 'index.php', NULL, '2026-08-28 14:35:22'),
(321, 'actualite.php', NULL, '2026-08-28 14:35:26'),
(322, 'enseignants.php', NULL, '2026-08-28 14:35:42'),
(323, 'index.php', NULL, '2026-08-28 14:35:44'),
(324, 'galerie.php', NULL, '2026-08-28 14:35:51'),
(325, 'filieres.php', NULL, '2026-08-28 14:35:55'),
(326, 'filieres.php', NULL, '2026-08-28 14:35:59'),
(327, 'index.php', NULL, '2026-08-28 14:36:02'),
(328, 'index.php', NULL, '2026-08-28 14:36:10'),
(329, 'index.php', NULL, '2026-08-28 14:36:18'),
(330, 'login.php', NULL, '2026-08-28 14:36:26'),
(331, 'index.php', 3, '2026-08-28 14:39:00'),
(332, 'index.php', NULL, '2026-08-28 14:39:11'),
(333, 'login.php', NULL, '2026-08-28 14:39:17'),
(334, 'mes_amis.php', 3, '2026-08-28 14:39:22'),
(335, 'mes_amis.php', 3, '2026-08-28 14:39:25'),
(336, 'profil_public.php', 3, '2026-08-28 14:39:29'),
(337, 'mes_amis.php', 3, '2026-08-28 14:39:39'),
(338, 'mes_amis.php', 3, '2026-08-28 14:39:41'),
(339, 'profil_public.php', 3, '2026-08-28 14:39:43'),
(340, 'messagerie.php', 3, '2026-08-28 14:39:51'),
(341, 'messagerie.php', 3, '2026-08-28 14:39:57'),
(342, 'mes_amis.php', 3, '2026-08-28 14:40:01'),
(343, 'communaute.php', 3, '2026-08-28 14:40:03'),
(344, 'profil.php', 3, '2026-08-28 14:40:14'),
(345, 'notifications.php', 3, '2026-08-28 14:40:35'),
(346, 'mes_amis.php', 3, '2026-08-28 14:40:42'),
(347, 'index.php', NULL, '2026-08-28 14:40:45'),
(348, 'login.php', NULL, '2026-08-28 14:40:48'),
(349, 'profil.php', 10, '2026-08-28 14:41:02'),
(350, 'mes_groupes.php', 10, '2026-08-28 14:41:10'),
(351, 'groupe_chat.php', 10, '2026-08-28 14:41:22'),
(352, 'mes_groupes.php', 10, '2026-08-28 14:41:43'),
(353, 'mes_groupes.php', 10, '2026-08-28 14:41:48'),
(354, 'index.php', 10, '2026-08-28 14:41:51'),
(355, 'index.php', 10, '2026-08-28 14:41:52'),
(356, 'login.php', NULL, '2026-08-28 19:29:52'),
(357, 'enseignants.php', NULL, '2026-08-28 19:40:28'),
(358, 'index.php', NULL, '2026-08-28 19:40:33'),
(359, 'login.php', NULL, '2026-08-28 19:40:38'),
(360, 'index.php', NULL, '2026-08-28 19:41:11'),
(361, 'index.php', NULL, '2026-08-28 19:41:12'),
(362, 'login.php', NULL, '2026-08-28 19:41:23'),
(363, 'login.php', NULL, '2026-08-28 19:54:20'),
(364, 'login.php', NULL, '2026-08-28 19:54:48'),
(365, 'login.php', NULL, '2026-08-28 19:55:08'),
(366, 'login.php', NULL, '2026-08-28 19:55:32'),
(367, 'login.php', NULL, '2026-08-28 19:56:18'),
(368, 'login.php', NULL, '2026-08-28 19:58:46'),
(369, 'index.php', NULL, '2026-08-28 19:59:59'),
(370, 'login.php', NULL, '2026-08-28 20:00:03'),
(371, 'index.php', NULL, '2026-08-28 20:00:44'),
(372, 'documents.php', NULL, '2026-08-28 20:01:12'),
(373, 'index.php', NULL, '2026-08-28 20:01:14'),
(374, 'login.php', NULL, '2026-08-28 20:02:09'),
(375, 'index.php', NULL, '2026-08-28 20:02:11'),
(376, 'login.php', NULL, '2026-08-28 20:03:04'),
(377, 'mes_amis.php', 3, '2026-08-28 20:03:12'),
(378, 'mes_amis.php', 3, '2026-08-28 20:03:36'),
(379, 'communaute.php', 3, '2026-08-28 20:03:48'),
(380, 'index.php', NULL, '2026-08-28 20:05:32'),
(381, 'login.php', NULL, '2026-08-28 20:05:34'),
(382, 'login.php', NULL, '2026-08-28 20:05:44'),
(383, 'profil.php', 10, '2026-08-28 20:05:55'),
(384, 'mes_amis.php', 10, '2026-08-28 20:07:19'),
(385, 'mes_amis.php', 10, '2026-08-28 20:07:22'),
(386, 'mes_amis.php', 10, '2026-08-28 20:07:25'),
(387, 'mes_amis.php', 10, '2026-08-28 20:07:27'),
(388, 'mes_amis.php', 10, '2026-08-28 20:07:29'),
(389, 'mes_amis.php', 10, '2026-08-28 20:07:32'),
(390, 'communaute.php', 10, '2026-08-28 20:07:40'),
(391, 'mes_amis.php', 10, '2026-08-28 20:07:45'),
(392, 'mes_amis.php', 10, '2026-08-28 20:07:49'),
(393, 'mes_amis.php', 10, '2026-08-28 20:07:53'),
(394, 'mes_groupes.php', 10, '2026-08-28 20:07:58'),
(395, 'notifications.php', 10, '2026-08-28 20:08:05'),
(396, 'profil.php', 10, '2026-08-28 20:08:16'),
(397, 'index.php', 10, '2026-08-28 20:08:18'),
(398, 'index.php', NULL, '2026-08-28 20:15:08'),
(399, 'login.php', NULL, '2026-08-28 20:15:11'),
(400, 'login.php', NULL, '2026-08-28 20:15:18'),
(401, 'mes_groupes.php', 23, '2026-08-28 20:15:25'),
(402, 'communaute.php', 23, '2026-08-28 20:15:30'),
(403, 'communaute.php', 23, '2026-08-28 20:15:43'),
(404, 'communaute.php', 23, '2026-08-28 20:16:17'),
(405, 'communaute.php', 23, '2026-08-28 20:16:37'),
(406, 'mes_amis.php', 23, '2026-08-28 20:16:40'),
(407, 'profil_public.php', 23, '2026-08-28 20:16:48'),
(408, 'communaute.php', 23, '2026-08-28 20:17:09'),
(409, 'mes_amis.php', 23, '2026-08-28 20:17:11'),
(410, 'index.php', NULL, '2026-08-28 20:17:20'),
(411, 'login.php', NULL, '2026-08-28 20:17:23'),
(412, 'profil.php', 10, '2026-08-28 20:17:32'),
(413, 'communaute.php', 10, '2026-08-28 20:17:38'),
(414, 'messages_prives.php', 10, '2026-08-28 20:18:22'),
(415, 'messages_prives.php', 10, '2026-08-28 20:18:28'),
(416, 'messages_prives.php', 10, '2026-08-28 20:18:30'),
(417, 'messages_prives.php', 10, '2026-08-28 20:18:32'),
(418, 'index.php', NULL, '2026-08-28 20:18:49'),
(419, 'login.php', NULL, '2026-08-28 20:18:53'),
(420, 'login.php', NULL, '2026-08-28 20:19:01'),
(421, 'mes_groupes.php', 23, '2026-08-28 20:19:13'),
(422, 'communaute.php', 23, '2026-08-28 20:19:18'),
(423, 'messages_prives.php', 23, '2026-08-28 20:19:21'),
(424, 'messages_prives.php', 23, '2026-08-28 20:19:25'),
(425, 'messages_prives.php', 23, '2026-08-28 20:19:27'),
(426, 'mes_amis.php', 23, '2026-08-28 20:22:49'),
(427, 'mes_amis.php', 23, '2026-08-28 20:27:32'),
(428, 'profil_public.php', 23, '2026-08-28 20:27:37'),
(429, 'mes_amis.php', 23, '2026-08-28 20:27:43'),
(430, 'mes_amis.php', 23, '2026-08-28 20:27:48'),
(431, 'communaute.php', 23, '2026-08-28 20:27:55'),
(432, 'login.php', NULL, '2026-08-28 20:29:22'),
(433, '404.php', NULL, '2026-08-28 20:29:22'),
(434, 'index.php', NULL, '2026-08-28 20:29:23'),
(435, 'communaute.php', 70, '2026-08-28 20:31:40'),
(436, 'mes_amis.php', 70, '2026-08-28 20:31:52'),
(437, 'mes_amis.php', 70, '2026-08-28 20:31:59'),
(438, 'mes_amis.php', 70, '2026-08-28 20:32:01'),
(439, 'profil.php', 70, '2026-08-28 20:32:03'),
(440, 'login.php', NULL, '2026-08-28 20:32:07'),
(441, 'communaute.php', 23, '2026-08-28 20:35:17'),
(442, 'index.php', NULL, '2026-08-28 20:35:40'),
(443, 'login.php', NULL, '2026-08-28 20:35:46'),
(444, 'communaute.php', 3, '2026-08-28 20:36:00'),
(445, 'profil.php', 3, '2026-08-28 20:36:37'),
(446, 'actualite.php', 3, '2026-08-28 20:36:44'),
(447, 'mes_amis.php', 3, '2026-08-28 20:36:47'),
(448, 'messagerie.php', 3, '2026-08-28 20:37:09'),
(449, 'mes_amis.php', 3, '2026-08-28 20:37:19'),
(450, 'mes_amis.php', 3, '2026-08-28 20:37:22'),
(451, 'mes_amis.php', 3, '2026-08-28 20:37:24'),
(452, 'profil.php', 3, '2026-08-28 20:37:30'),
(453, 'communaute.php', 3, '2026-08-28 20:37:35'),
(454, 'communaute.php', 3, '2026-08-28 20:38:02'),
(455, 'profil.php', 3, '2026-08-28 20:38:07'),
(456, 'communaute.php', 3, '2026-08-28 20:38:08'),
(457, 'actualite.php', 3, '2026-08-28 20:38:45'),
(458, 'index.php', 3, '2026-08-28 20:38:47'),
(459, 'index.php', 3, '2026-08-28 22:01:27'),
(460, 'communaute.php', 3, '2026-08-28 22:01:40'),
(461, 'profil.php', 3, '2026-08-28 22:01:57'),
(462, 'index.php', NULL, '2026-08-28 22:02:29'),
(463, 'login.php', NULL, '2026-08-28 22:02:31'),
(464, 'index.php', 3, '2026-08-28 22:04:51'),
(465, 'mes_amis.php', 3, '2026-08-28 22:04:55'),
(466, 'messagerie.php', 3, '2026-08-28 22:05:02'),
(467, 'index.php', NULL, '2026-08-28 22:05:07'),
(468, 'login.php', NULL, '2026-08-28 22:05:10'),
(469, 'login.php', NULL, '2026-08-28 22:05:21'),
(470, 'profil.php', 10, '2026-08-28 22:05:32'),
(471, 'mes_groupes.php', 10, '2026-08-28 22:06:01'),
(472, 'communaute.php', 10, '2026-08-28 22:06:04'),
(473, 'mes_amis.php', 10, '2026-08-28 22:07:14'),
(474, 'profil_public.php', 10, '2026-08-28 22:07:16'),
(475, 'messages_prives.php', 10, '2026-08-28 22:08:15'),
(476, 'messages_prives.php', 71, '2026-08-28 22:14:46'),
(477, 'messages_prives.php', 71, '2026-08-28 22:15:47'),
(478, 'notifications.php', 10, '2026-08-28 22:16:16'),
(479, 'profil.php', 10, '2026-08-28 22:16:39'),
(480, 'index.php', 10, '2026-08-28 22:16:45'),
(481, 'enseignants.php', 10, '2026-08-28 22:17:24'),
(482, 'parcours.php', 10, '2026-08-28 22:17:30'),
(483, 'index.php', 10, '2026-08-28 22:18:13'),
(484, '404.php', 10, '2026-08-28 22:18:13'),
(485, '404.php', 10, '2026-08-28 22:18:13'),
(486, 'mes_groupes.php', 10, '2026-08-28 22:18:51'),
(487, 'mes_amis.php', 10, '2026-08-28 22:18:55'),
(488, 'profil_public.php', 10, '2026-08-28 22:18:59'),
(489, 'communaute.php', 10, '2026-08-28 22:19:08'),
(490, 'index.php', 10, '2026-08-28 22:22:14'),
(491, 'actualite.php', 10, '2026-08-28 22:22:24'),
(492, 'associations.php', 10, '2026-08-28 22:23:36'),
(493, 'vie_etudiante.php', 10, '2026-08-28 22:24:07'),
(494, 'index.php', 10, '2026-08-28 22:24:31'),
(495, 'actualite.php', 10, '2026-08-28 22:24:46'),
(496, 'index.php', 10, '2026-08-28 22:25:12'),
(497, 'index.php', 10, '2026-08-28 22:25:52'),
(498, 'mes_amis.php', 10, '2026-08-28 22:26:11'),
(499, 'mes_amis.php', 10, '2026-08-28 22:26:28'),
(500, 'mes_amis.php', 10, '2026-08-28 22:26:43'),
(501, 'mes_amis.php', 10, '2026-08-28 22:26:46'),
(502, 'mes_amis.php', 10, '2026-08-28 22:26:47'),
(503, 'mes_amis.php', 10, '2026-08-28 22:26:49'),
(504, 'mes_amis.php', 10, '2026-08-28 22:26:51'),
(505, 'mes_amis.php', 10, '2026-08-28 22:27:42'),
(506, 'mes_amis.php', 10, '2026-08-28 22:27:45'),
(507, 'mes_amis.php', 10, '2026-08-28 22:27:54'),
(508, 'index.php', 10, '2026-08-28 22:28:14'),
(509, 'index.php', NULL, '2026-08-28 22:28:24'),
(510, 'login.php', NULL, '2026-08-28 22:28:27'),
(511, 'login.php', NULL, '2026-08-28 22:28:48'),
(512, 'profil.php', 71, '2026-08-28 22:28:59'),
(513, 'profil_public.php', 71, '2026-08-28 22:29:02'),
(514, 'communaute.php', 71, '2026-08-28 22:29:05'),
(515, 'login.php', NULL, '2026-08-28 22:30:01'),
(516, 'index.php', NULL, '2026-08-28 22:30:02'),
(517, 'index.php', NULL, '2026-08-28 22:30:08'),
(518, 'historique.php', NULL, '2026-08-28 22:32:16'),
(519, 'parcours.php', NULL, '2026-08-28 22:32:26'),
(520, 'filieres.php', NULL, '2026-08-28 22:32:35'),
(521, 'inscription.php', NULL, '2026-08-28 22:32:39'),
(522, 'enseignants.php', NULL, '2026-08-28 22:32:53'),
(523, 'vie_etudiante.php', NULL, '2026-08-28 22:32:57'),
(524, 'bourse.php', NULL, '2026-08-28 22:33:03'),
(525, 'galerie.php', NULL, '2026-08-28 22:33:07'),
(526, 'evenements.php', NULL, '2026-08-28 22:33:12'),
(527, 'index.php', NULL, '2026-08-28 22:37:36'),
(528, 'login.php', NULL, '2026-08-28 22:37:39'),
(529, 'index.php', NULL, '2026-08-28 22:39:42'),
(530, 'notifications.php', 3, '2026-08-28 22:40:27'),
(531, 'communaute.php', 3, '2026-08-28 22:40:31'),
(532, 'messages_prives.php', 3, '2026-08-28 22:41:36'),
(533, 'index.php', NULL, '2026-08-28 22:41:42'),
(534, 'login.php', NULL, '2026-08-28 22:41:45'),
(535, 'profil.php', 10, '2026-08-28 22:41:53'),
(536, 'mes_amis.php', 10, '2026-08-28 22:42:05'),
(537, 'communaute.php', 10, '2026-08-28 22:42:10'),
(538, 'index.php', 10, '2026-08-28 22:42:49'),
(539, 'index.php', 10, '2026-08-28 22:43:53'),
(540, 'communaute.php', 10, '2026-08-28 22:44:12'),
(541, 'communaute.php', 10, '2026-08-28 22:44:13'),
(542, 'mes_amis.php', 10, '2026-08-28 22:44:53'),
(543, 'messages_prives.php', 10, '2026-08-28 22:44:56'),
(544, 'index.php', 10, '2026-08-28 22:45:07'),
(545, 'index.php', NULL, '2026-08-28 22:45:11'),
(546, 'parcours.php', NULL, '2026-08-28 22:45:20'),
(547, 'parcours.php', NULL, '2026-08-28 22:45:39'),
(548, 'parcours.php', NULL, '2026-08-28 22:47:53'),
(549, 'parcours.php', NULL, '2026-08-28 22:48:03'),
(550, 'index.php', NULL, '2026-08-28 22:48:22'),
(551, 'actualite_article.php', NULL, '2026-08-28 22:48:33'),
(552, 'actualite.php', NULL, '2026-08-28 22:48:38'),
(553, 'index.php', NULL, '2026-08-28 22:48:40'),
(554, 'index.php', NULL, '2026-08-28 22:48:54'),
(555, 'actualite_article.php', NULL, '2026-08-28 22:49:13'),
(556, 'actualite.php', NULL, '2026-08-28 22:49:19'),
(557, 'index.php', NULL, '2026-08-28 22:49:24'),
(558, 'login.php', NULL, '2026-08-28 22:50:53'),
(559, 'communaute.php', 3, '2026-08-28 22:51:00'),
(560, 'profil.php', 3, '2026-08-28 22:51:11'),
(561, 'index.php', NULL, '2026-08-28 22:51:20'),
(562, 'login.php', NULL, '2026-08-28 22:51:22'),
(563, 'login.php', NULL, '2026-08-28 22:51:33'),
(564, 'mes_groupes.php', 23, '2026-08-28 22:51:44'),
(565, 'groupe_chat.php', 23, '2026-08-28 22:51:51'),
(566, 'index.php', 3, '2026-08-28 22:51:54'),
(567, 'communaute.php', 23, '2026-08-28 22:52:01'),
(568, 'index.php', 3, '2026-08-28 22:52:02'),
(569, 'messages_prives.php', 3, '2026-08-28 22:52:05'),
(570, 'index.php', NULL, '2026-08-28 22:52:07'),
(571, 'index.php', NULL, '2026-08-28 22:52:10'),
(572, 'profil_public.php', 23, '2026-08-28 22:52:12'),
(573, 'index.php', NULL, '2026-08-28 22:52:13'),
(574, 'login.php', NULL, '2026-08-28 22:52:20'),
(575, 'parcours.php', NULL, '2026-08-28 22:52:22'),
(576, 'parcours.php', 74, '2026-08-28 22:52:26'),
(577, 'evenements.php', NULL, '2026-08-28 22:52:28'),
(578, 'communaute.php', 23, '2026-08-28 22:52:32'),
(579, 'communaute.php', 74, '2026-08-28 22:52:37'),
(580, 'communaute.php', 23, '2026-08-28 22:52:41'),
(581, 'messages_prives.php', 23, '2026-08-28 22:52:46'),
(582, 'communaute.php', 23, '2026-08-28 22:52:51'),
(583, 'mes_amis.php', 23, '2026-08-28 22:53:03'),
(584, 'mes_amis.php', 23, '2026-08-28 22:53:10'),
(585, 'mes_amis.php', 23, '2026-08-28 22:53:11'),
(586, 'messages_prives.php', 23, '2026-08-28 22:53:16'),
(587, 'index.php', 23, '2026-08-28 22:53:36'),
(588, 'bourse.php', 23, '2026-08-28 22:53:45'),
(589, 'index.php', NULL, '2026-08-28 22:53:50'),
(590, 'login.php', NULL, '2026-08-28 22:53:50'),
(591, 'parcours.php', NULL, '2026-08-28 22:53:50'),
(592, 'evenements.php', NULL, '2026-08-28 22:53:50'),
(593, 'header.php', NULL, '2026-08-28 22:53:51'),
(594, 'bourse.php', 23, '2026-08-28 22:54:06'),
(595, 'mes_amis.php', 23, '2026-08-28 22:54:25'),
(596, 'communaute.php', 23, '2026-08-28 22:54:31'),
(597, 'index.php', 23, '2026-08-28 22:54:36'),
(598, 'bourse.php', 23, '2026-08-28 22:54:39'),
(599, 'index.php', 23, '2026-08-28 22:55:08'),
(600, 'evenements.php', 23, '2026-08-28 22:55:21'),
(601, 'index.php', NULL, '2026-08-28 22:56:14'),
(602, 'login.php', NULL, '2026-08-28 22:56:16'),
(603, 'login.php', NULL, '2026-08-28 22:56:24'),
(604, 'index.php', NULL, '2026-08-28 22:57:24'),
(605, 'login.php', NULL, '2026-08-28 22:57:51'),
(606, 'login.php', NULL, '2026-08-28 22:58:26'),
(607, 'login.php', NULL, '2026-08-28 22:58:43'),
(608, 'login.php', NULL, '2026-08-28 23:00:15'),
(609, 'index.php', 3, '2026-08-28 23:00:19'),
(610, 'messagerie.php', 3, '2026-08-28 23:00:30'),
(611, 'messagerie.php', 3, '2026-08-28 23:00:35'),
(612, 'login.php', NULL, '2026-08-28 23:00:53'),
(613, 'login.php', NULL, '2026-08-28 23:01:27'),
(614, 'evenements.php', 3, '2026-08-28 23:01:27'),
(615, 'evenements.php', 3, '2026-08-28 23:01:42'),
(616, 'index.php', NULL, '2026-08-28 23:02:01'),
(617, 'login.php', NULL, '2026-08-28 23:02:04'),
(618, 'login.php', NULL, '2026-08-28 23:02:13'),
(619, 'filieres.php', NULL, '2026-08-28 23:02:31'),
(620, 'index.php', NULL, '2026-08-28 23:02:40'),
(621, '404.php', NULL, '2026-08-28 23:02:40'),
(622, '404.php', NULL, '2026-08-28 23:02:40'),
(623, 'index.php', NULL, '2026-08-28 23:02:52'),
(624, 'historique.php', NULL, '2026-08-28 23:02:54'),
(625, 'documents.php', NULL, '2026-08-28 23:03:05'),
(626, 'actualite.php', NULL, '2026-08-28 23:03:08'),
(627, 'galerie.php', NULL, '2026-08-28 23:03:11'),
(628, 'galerie_album.php', NULL, '2026-08-28 23:03:24'),
(629, 'galerie.php', NULL, '2026-08-28 23:03:30'),
(630, 'galerie.php', NULL, '2026-08-28 23:03:39'),
(631, 'galerie_album.php', NULL, '2026-08-28 23:03:41'),
(632, 'galerie.php', NULL, '2026-08-28 23:03:45'),
(633, 'galerie_album.php', NULL, '2026-08-28 23:03:47'),
(634, 'galerie.php', NULL, '2026-08-28 23:03:50'),
(635, 'galerie_album.php', NULL, '2026-08-28 23:03:55'),
(636, 'galerie.php', NULL, '2026-08-28 23:03:58'),
(637, 'index.php', NULL, '2026-08-28 23:04:00'),
(638, 'actualite_article.php', NULL, '2026-08-28 23:04:04'),
(639, 'actualite.php', NULL, '2026-08-28 23:04:14'),
(640, 'index.php', NULL, '2026-08-28 23:04:35'),
(641, 'login.php', NULL, '2026-08-28 23:05:02'),
(642, 'login.php', NULL, '2026-08-28 23:05:09'),
(643, 'index.php', NULL, '2026-08-28 23:05:25'),
(644, 'login.php', NULL, '2026-08-28 23:05:40'),
(645, 'evenements.php', NULL, '2026-08-28 23:05:53'),
(646, 'actualite.php', NULL, '2026-08-28 23:07:09'),
(647, 'index.php', NULL, '2026-08-28 23:08:32'),
(648, 'parcours.php', NULL, '2026-08-28 23:08:36'),
(649, 'filieres.php', NULL, '2026-08-28 23:08:46'),
(650, 'inscription.php', NULL, '2026-08-28 23:08:49'),
(651, 'inscription.php', NULL, '2026-08-28 23:08:49'),
(652, 'index.php', NULL, '2026-08-28 23:09:06'),
(653, 'login.php', NULL, '2026-08-28 23:09:11'),
(654, 'index.php', NULL, '2026-08-28 23:10:43'),
(655, 'index.php', 3, '2026-08-28 23:10:53'),
(656, 'historique.php', 3, '2026-08-28 23:11:09'),
(657, 'inscription.php', 3, '2026-08-28 23:11:15'),
(658, 'index.php', 3, '2026-08-28 23:11:17'),
(659, '404.php', 3, '2026-08-28 23:11:18'),
(660, '404.php', 3, '2026-08-28 23:11:19'),
(661, 'messagerie.php', 3, '2026-08-28 23:11:21'),
(662, 'actualite.php', 3, '2026-08-28 23:11:28'),
(663, 'index.php', NULL, '2026-08-28 23:11:41'),
(664, 'index.php', NULL, '2026-08-28 23:11:49'),
(665, 'parcours.php', NULL, '2026-08-28 23:11:56'),
(666, 'index.php', NULL, '2026-08-28 23:12:25'),
(667, 'index.php', NULL, '2026-08-28 23:12:43'),
(668, 'index.php', NULL, '2026-08-28 23:12:55'),
(669, 'parcours.php', NULL, '2026-08-28 23:13:04'),
(670, 'index.php', NULL, '2026-08-28 23:13:48'),
(671, 'parcours.php', NULL, '2026-08-28 23:13:49'),
(672, 'evenements.php', NULL, '2026-08-28 23:13:49'),
(673, 'historique.php', NULL, '2026-08-28 23:13:50'),
(674, 'bourse.php', NULL, '2026-08-28 23:13:51'),
(675, 'galerie.php', NULL, '2026-08-28 23:13:51'),
(676, 'index.php', NULL, '2026-08-28 23:14:28'),
(677, 'index.php', NULL, '2026-08-28 23:51:42'),
(678, 'index.php', NULL, '2026-08-28 23:51:42'),
(679, 'login.php', NULL, '2026-08-29 00:07:45'),
(680, 'vie_etudiante.php', 3, '2026-08-29 00:39:11'),
(681, 'index.php', 3, '2026-08-29 00:39:16'),
(682, 'historique.php', 3, '2026-08-29 07:08:31'),
(683, 'index.php', NULL, '2026-08-29 07:08:37'),
(684, 'index.php', NULL, '2026-08-31 19:02:40'),
(685, 'index.php', NULL, '2026-08-31 19:04:29'),
(686, 'historique.php', NULL, '2026-08-31 19:04:54'),
(687, 'vie_etudiante.php', NULL, '2026-08-31 19:05:03'),
(688, 'vie_etudiante.php', NULL, '2026-08-31 19:07:16'),
(689, 'index.php', NULL, '2026-08-31 19:07:55'),
(690, 'login.php', NULL, '2026-08-31 19:08:21'),
(691, 'communaute.php', 3, '2026-08-31 19:59:39'),
(692, 'index.php', NULL, '2026-08-31 19:59:58'),
(693, 'login.php', NULL, '2026-08-31 20:00:03'),
(694, 'profil.php', 10, '2026-08-31 20:00:15'),
(695, 'communaute.php', 10, '2026-08-31 20:00:21'),
(696, 'index.php', NULL, '2026-08-31 20:01:13'),
(697, 'login.php', NULL, '2026-09-04 07:23:06'),
(698, 'index.php', NULL, '2026-09-04 07:23:40'),
(699, 'index.php', NULL, '2026-09-04 07:24:18'),
(700, 'historique.php', NULL, '2026-09-04 07:24:56'),
(701, 'historique.php', NULL, '2026-09-04 07:24:57'),
(702, 'parcours.php', NULL, '2026-09-04 07:25:07'),
(703, 'inscription.php', NULL, '2026-09-04 07:25:21'),
(704, 'enseignants.php', NULL, '2026-09-04 07:25:34'),
(705, 'vie_etudiante.php', NULL, '2026-09-04 07:25:44'),
(706, 'campus.php', NULL, '2026-09-04 07:25:50'),
(707, 'vie_etudiante.php', NULL, '2026-09-04 07:26:22'),
(708, 'associations.php', NULL, '2026-09-04 07:26:25'),
(709, 'associations.php', NULL, '2026-09-04 07:26:26'),
(710, 'galerie.php', NULL, '2026-09-04 07:26:33'),
(711, 'galerie_album.php', NULL, '2026-09-04 07:26:39'),
(712, 'galerie.php', NULL, '2026-09-04 07:27:12'),
(713, 'evenements.php', NULL, '2026-09-04 07:27:19'),
(714, 'index.php', NULL, '2026-09-04 07:27:26'),
(715, '404.php', NULL, '2026-09-04 07:27:26'),
(716, '404.php', NULL, '2026-09-04 07:27:27'),
(717, 'documents.php', NULL, '2026-09-04 07:27:34'),
(718, 'index.php', NULL, '2026-09-04 07:27:37'),
(719, 'login.php', NULL, '2026-09-04 07:27:42'),
(720, 'index.php', NULL, '2026-09-04 07:28:43'),
(721, 'login.php', NULL, '2026-09-04 07:28:46'),
(722, 'mes_groupes.php', 23, '2026-09-04 07:28:58'),
(723, 'groupe_chat.php', 23, '2026-09-04 07:29:16'),
(724, 'communaute.php', 23, '2026-09-04 07:30:01'),
(725, 'profil.php', 23, '2026-09-04 07:30:25'),
(726, 'profil.php', 23, '2026-09-04 07:30:53'),
(727, 'profil.php', 23, '2026-09-04 07:30:53'),
(728, 'index.php', 23, '2026-09-04 07:30:58'),
(729, 'actualite_article.php', 23, '2026-09-04 07:31:03'),
(730, 'actualite_article.php', 23, '2026-09-04 07:31:14'),
(731, 'index.php', 23, '2026-09-04 07:31:18'),
(732, 'mes_amis.php', 23, '2026-09-04 07:31:20'),
(733, 'mes_amis.php', 23, '2026-09-04 07:31:30'),
(734, 'mes_amis.php', 23, '2026-09-04 07:31:46'),
(735, 'index.php', NULL, '2026-09-04 07:31:50'),
(736, 'login.php', NULL, '2026-09-04 07:31:53'),
(737, 'index.php', NULL, '2026-09-04 07:32:17'),
(738, 'inscription.php', NULL, '2026-09-04 07:32:22'),
(739, 'preinscription.php', NULL, '2026-09-04 07:32:39'),
(740, 'index.php', NULL, '2026-09-04 07:32:49'),
(741, 'login.php', NULL, '2026-09-04 07:32:54'),
(742, 'index.php', 7, '2026-09-04 07:33:53'),
(743, 'messagerie.php', 7, '2026-09-04 07:33:58'),
(744, 'index.php', 7, '2026-09-04 07:34:33'),
(745, 'filieres.php', 7, '2026-09-04 07:35:10'),
(746, 'filiere_detail.php', 7, '2026-09-04 07:35:24'),
(747, 'filieres.php', 7, '2026-09-04 07:35:46'),
(748, 'filieres.php', 7, '2026-09-04 07:36:08'),
(749, 'index.php', 7, '2026-09-04 07:36:12'),
(750, 'enseignants.php', 7, '2026-09-04 07:36:49'),
(751, 'index.php', NULL, '2026-09-04 07:37:30'),
(752, 'index.php', NULL, '2026-09-04 07:37:35'),
(753, 'index.php', NULL, '2026-09-04 07:37:43'),
(754, '404.php', NULL, '2026-09-04 07:37:43'),
(755, '404.php', NULL, '2026-09-04 07:37:43'),
(756, 'documents.php', NULL, '2026-09-04 07:37:59'),
(757, 'bourse.php', NULL, '2026-09-04 07:38:13'),
(758, 'index.php', NULL, '2026-09-04 07:39:54'),
(759, 'inscription.php', NULL, '2026-09-04 07:41:15'),
(760, 'index.php', NULL, '2026-09-04 07:45:44'),
(761, 'index.php', NULL, '2026-09-04 07:48:18'),
(762, 'index.php', NULL, '2026-09-04 07:48:45'),
(763, 'index.php', NULL, '2026-09-04 08:22:24'),
(764, 'index.php', NULL, '2026-09-04 08:24:14'),
(765, 'inscription.php', NULL, '2026-09-04 08:41:34'),
(766, 'index.php', NULL, '2026-09-04 08:42:46'),
(767, 'login.php', NULL, '2026-09-04 08:42:53'),
(768, 'inscription.php', NULL, '2026-09-04 08:52:23'),
(769, 'inscription.php', NULL, '2026-09-04 08:52:30'),
(770, 'inscription.php', NULL, '2026-09-04 08:52:37'),
(771, 'inscription.php', NULL, '2026-09-04 08:52:38'),
(772, 'inscription.php', 3, '2026-09-04 08:54:27'),
(773, 'index.php', 3, '2026-09-04 08:55:07'),
(774, 'index.php', 3, '2026-09-04 08:57:42'),
(775, 'index.php', NULL, '2026-09-04 08:58:09'),
(776, 'index.php', 3, '2026-09-04 08:58:51'),
(777, 'actualite_article.php', 3, '2026-09-04 08:59:01'),
(778, 'actualite.php', 3, '2026-09-04 08:59:07'),
(779, 'actualite_article.php', 3, '2026-09-04 08:59:13'),
(780, 'actualite.php', 3, '2026-09-04 08:59:18'),
(781, 'index.php', 3, '2026-09-04 08:59:29'),
(782, 'mes_amis.php', 3, '2026-09-04 08:59:51'),
(783, 'communaute.php', 3, '2026-09-04 08:59:55'),
(784, 'index.php', 3, '2026-09-04 09:01:20'),
(785, 'actualite.php', 3, '2026-09-04 09:01:26'),
(786, 'actualite.php', 3, '2026-09-04 09:01:29'),
(787, 'notifications.php', 3, '2026-09-04 09:01:38'),
(788, 'actualite.php', 3, '2026-09-04 09:01:54'),
(789, 'index.php', 3, '2026-09-04 09:01:58'),
(790, '404.php', 3, '2026-09-04 09:01:58'),
(791, '404.php', 3, '2026-09-04 09:01:58'),
(792, 'index.php', 3, '2026-09-04 09:02:05'),
(793, 'index.php', 3, '2026-09-04 09:05:33'),
(794, 'evenements.php', 3, '2026-09-04 09:05:50'),
(795, 'evenements.php', 3, '2026-09-04 09:05:54'),
(796, 'actualite.php', 3, '2026-09-04 09:06:05'),
(797, 'evenements.php', 3, '2026-09-04 09:07:55'),
(798, 'index.php', 3, '2026-09-04 09:08:23'),
(799, 'index.php', 3, '2026-09-04 09:23:33'),
(800, 'index.php', NULL, '2026-09-04 09:24:09'),
(801, 'evenements.php', NULL, '2026-09-04 09:24:20'),
(802, 'index.php', NULL, '2026-09-04 09:24:26'),
(803, 'login.php', NULL, '2026-09-04 09:39:50'),
(804, 'index.php', NULL, '2026-09-04 09:39:57'),
(805, 'index.php', NULL, '2026-09-04 10:14:57'),
(806, 'index.php', NULL, '2026-09-04 10:18:20'),
(807, 'index.php', NULL, '2026-09-04 10:18:21'),
(808, 'index.php', NULL, '2026-09-04 10:18:23'),
(809, 'enseignants.php', NULL, '2026-09-04 10:18:24'),
(810, 'actualite.php', NULL, '2026-09-04 10:18:26'),
(811, 'index.php', NULL, '2026-09-04 10:46:57'),
(812, 'login.php', NULL, '2026-09-04 10:47:02'),
(813, 'index.php', NULL, '2026-09-04 10:49:20'),
(814, 'documents.php', NULL, '2026-09-04 10:49:25'),
(815, 'login.php', NULL, '2026-09-04 10:49:29'),
(816, 'index.php', NULL, '2026-09-04 10:49:34'),
(817, 'index.php', NULL, '2026-09-04 10:49:34'),
(818, 'login.php', NULL, '2026-09-04 11:07:18'),
(819, 'login.php', NULL, '2026-09-04 11:07:18'),
(820, 'index.php', 3, '2026-09-04 11:08:15'),
(821, 'index.php', NULL, '2026-09-04 11:15:06'),
(822, 'login.php', NULL, '2026-09-04 11:15:09'),
(823, 'dashboard.php', NULL, '2026-09-04 11:15:15'),
(824, '404.php', NULL, '2026-09-04 11:15:16'),
(825, '404.php', NULL, '2026-09-04 11:15:16'),
(826, 'index.php', NULL, '2026-09-04 11:15:42'),
(827, 'login.php', NULL, '2026-09-04 11:15:45'),
(828, 'profil.php', 10, '2026-09-04 11:15:54'),
(829, 'mes_amis.php', 10, '2026-09-04 11:16:29'),
(830, 'communaute.php', 10, '2026-09-04 11:16:35'),
(831, 'communaute.php', 10, '2026-09-04 11:16:35'),
(832, 'index.php', NULL, '2026-09-04 11:16:44'),
(833, 'login.php', NULL, '2026-09-04 11:16:52'),
(834, 'login.php', NULL, '2026-09-04 11:17:03'),
(835, 'index.php', NULL, '2026-09-04 11:17:23'),
(836, 'login.php', NULL, '2026-09-04 11:17:26'),
(837, 'profil.php', 63, '2026-09-04 11:17:41'),
(838, 'index.php', NULL, '2026-09-04 11:27:32'),
(839, 'index.php', NULL, '2026-09-04 19:34:53'),
(840, 'login.php', NULL, '2026-09-04 19:35:12'),
(841, 'index.php', NULL, '2026-09-04 19:35:18'),
(842, 'index.php', NULL, '2026-09-04 19:35:20'),
(843, 'index.php', NULL, '2026-09-04 19:35:22'),
(844, 'index.php', NULL, '2026-09-04 19:35:37'),
(845, 'index.php', NULL, '2026-09-04 19:35:37'),
(846, 'index.php', NULL, '2026-09-04 19:35:38'),
(847, 'index.php', NULL, '2026-09-04 19:35:39'),
(848, 'index.php', NULL, '2026-09-04 19:35:39'),
(849, 'index.php', 3, '2026-09-04 19:36:43'),
(850, 'historique.php', 3, '2026-09-04 19:37:05'),
(851, 'parcours.php', 3, '2026-09-04 19:37:11'),
(852, 'galerie.php', 3, '2026-09-04 19:37:15'),
(853, 'index.php', 3, '2026-09-04 19:37:21'),
(854, '404.php', 3, '2026-09-04 19:37:21'),
(855, '404.php', 3, '2026-09-04 19:37:21'),
(856, 'index.php', 3, '2026-09-04 19:37:28'),
(857, 'index.php', 3, '2026-09-04 19:43:01'),
(858, 'index.php', 3, '2026-09-04 19:47:33'),
(859, 'communaute.php', 3, '2026-09-04 20:00:48'),
(860, 'mes_amis.php', 3, '2026-09-04 20:00:51'),
(861, 'messagerie.php', 3, '2026-09-04 20:00:57'),
(862, 'messagerie.php', 3, '2026-09-04 20:00:58'),
(863, 'index.php', NULL, '2026-09-04 20:01:05'),
(864, 'login.php', NULL, '2026-09-04 20:01:09'),
(865, 'mes_groupes.php', 23, '2026-09-04 20:01:40'),
(866, 'communaute.php', 23, '2026-09-04 20:01:53'),
(867, 'mes_amis.php', 23, '2026-09-04 20:01:57'),
(868, 'mes_amis.php', 23, '2026-09-04 20:02:03'),
(869, 'index.php', 23, '2026-09-04 20:02:07'),
(870, 'index.php', 23, '2026-09-04 20:08:28'),
(871, 'index.php', NULL, '2026-09-04 20:08:40'),
(872, 'index.php', NULL, '2026-09-04 20:08:44'),
(873, 'evenements.php', NULL, '2026-09-04 20:14:19'),
(874, 'index.php', NULL, '2026-09-04 20:22:50'),
(875, 'index.php', NULL, '2026-09-04 20:27:43'),
(876, 'login.php', NULL, '2026-09-04 20:27:51'),
(877, 'login.php', NULL, '2026-09-04 20:28:03'),
(878, 'login.php', NULL, '2026-09-04 20:28:57'),
(879, 'login.php', NULL, '2026-09-04 20:29:04'),
(880, 'index.php', NULL, '2026-09-04 20:29:13'),
(881, 'index.php', NULL, '2026-09-04 20:29:34'),
(882, 'index.php', NULL, '2026-09-04 20:41:20'),
(883, 'index.php', NULL, '2026-09-04 20:42:34'),
(884, 'login.php', NULL, '2026-09-04 20:49:23'),
(885, 'index.php', 3, '2026-09-04 20:56:30'),
(886, 'parcours.php', 3, '2026-09-04 21:25:22'),
(887, 'index.php', 3, '2026-09-04 21:25:56'),
(888, 'index.php', NULL, '2026-09-04 21:26:14'),
(889, 'index.php', NULL, '2026-09-04 21:26:19'),
(890, 'index.php', NULL, '2026-09-04 21:26:22'),
(891, 'index.php', NULL, '2026-09-04 21:39:47'),
(892, 'login.php', NULL, '2026-09-04 21:39:52'),
(893, 'historique.php', 3, '2026-09-04 21:50:09'),
(894, 'login.php', 3, '2026-09-04 21:50:30'),
(895, 'login.php', 3, '2026-09-04 21:50:36'),
(896, 'inscription.php', 3, '2026-09-04 21:50:39'),
(897, 'index.php', 3, '2026-09-04 22:01:38'),
(898, 'login.php', 3, '2026-09-04 22:09:22'),
(899, 'inscription.php', 3, '2026-09-04 22:09:33'),
(900, 'index.php', 3, '2026-09-04 22:10:08'),
(901, '404.php', 3, '2026-09-04 22:10:08'),
(902, '404.php', 3, '2026-09-04 22:10:08'),
(903, 'evenements.php', 3, '2026-09-04 22:12:14'),
(904, 'galerie.php', 3, '2026-09-04 22:12:18'),
(905, 'index.php', 3, '2026-09-04 22:12:21'),
(906, 'index.php', 3, '2026-09-04 22:12:22'),
(907, '404.php', 3, '2026-09-04 22:12:22'),
(908, '404.php', 3, '2026-09-04 22:12:22'),
(909, 'index.php', 3, '2026-09-04 22:12:25'),
(910, 'index.php', 3, '2026-09-04 22:12:25'),
(911, 'index.php', 3, '2026-09-04 22:12:37'),
(912, 'index.php', NULL, '2026-09-06 14:08:24'),
(913, 'index.php', NULL, '2026-09-06 14:08:25'),
(914, 'index.php', NULL, '2026-09-06 14:09:44'),
(915, 'index.php', NULL, '2026-09-06 14:10:01'),
(916, '404.php', NULL, '2026-09-06 14:10:02'),
(917, '404.php', NULL, '2026-09-06 14:10:02'),
(918, 'evenements.php', NULL, '2026-09-06 14:10:19'),
(919, 'index.php', NULL, '2026-09-06 14:10:23'),
(920, 'login.php', NULL, '2026-09-06 14:10:25'),
(921, 'profil.php', 3, '2026-09-06 14:13:34'),
(922, 'messagerie.php', 3, '2026-09-06 14:13:46'),
(923, 'index.php', NULL, '2026-09-06 14:13:50'),
(924, 'login.php', NULL, '2026-09-06 14:13:53'),
(925, 'mes_groupes.php', 23, '2026-09-06 14:14:06'),
(926, 'mes_groupes.php', 23, '2026-09-06 14:14:08'),
(927, 'mes_amis.php', 23, '2026-09-06 14:14:17'),
(928, 'profil_public.php', 23, '2026-09-06 14:14:23'),
(929, 'communaute.php', 23, '2026-09-06 14:14:35'),
(930, 'communaute.php', 23, '2026-09-06 14:15:25'),
(931, 'communaute.php', 23, '2026-09-06 14:15:45'),
(932, 'communaute.php', 23, '2026-09-06 14:16:02'),
(933, 'index.php', 23, '2026-09-06 14:16:05'),
(934, 'inscription.php', 23, '2026-09-06 14:19:49'),
(935, 'inscription.php', 23, '2026-09-06 14:20:02'),
(936, 'index.php', NULL, '2026-09-06 14:21:26'),
(937, 'associations.php', NULL, '2026-09-06 14:21:39'),
(938, 'index.php', NULL, '2026-09-07 08:33:18'),
(939, 'historique.php', NULL, '2026-09-07 08:34:11'),
(940, 'index.php', NULL, '2026-09-07 08:34:19'),
(941, 'index.php', NULL, '2026-09-07 08:34:26'),
(942, 'enseignants.php', NULL, '2026-09-07 08:36:05'),
(943, 'evenements.php', NULL, '2026-09-07 08:36:41'),
(944, 'index.php', NULL, '2026-09-07 08:36:51'),
(945, '404.php', NULL, '2026-09-07 08:36:51'),
(946, '404.php', NULL, '2026-09-07 08:36:51'),
(947, 'galerie.php', NULL, '2026-09-07 08:37:07'),
(948, 'galerie_album.php', NULL, '2026-09-07 08:37:10'),
(949, 'galerie.php', NULL, '2026-09-07 08:37:24'),
(950, 'index.php', NULL, '2026-09-07 08:37:27'),
(951, 'login.php', NULL, '2026-09-07 08:37:41'),
(952, 'profil.php', 3, '2026-09-07 08:38:04'),
(953, 'mes_amis.php', 3, '2026-09-07 08:38:12'),
(954, 'communaute.php', 3, '2026-09-07 08:38:16'),
(955, 'communaute.php', 3, '2026-09-07 08:38:30'),
(956, 'index.php', 3, '2026-09-07 08:38:34'),
(957, 'index.php', 3, '2026-09-07 08:38:37'),
(958, 'index.php', 3, '2026-09-07 08:39:02'),
(959, 'index.php', NULL, '2026-09-07 08:39:14'),
(960, 'actualite.php', NULL, '2026-09-07 08:40:17'),
(961, 'actualite_article.php', NULL, '2026-09-07 08:40:26'),
(962, 'index.php', NULL, '2026-09-07 08:40:34'),
(963, 'historique.php', NULL, '2026-09-07 08:40:36'),
(964, 'filiere_detail.php', NULL, '2026-09-07 08:40:48'),
(965, 'parcours.php', NULL, '2026-09-07 08:41:01'),
(966, 'filieres.php', NULL, '2026-09-07 08:41:38'),
(967, 'inscription.php', NULL, '2026-09-07 08:41:53'),
(968, 'preinscription.php', NULL, '2026-09-07 08:43:33'),
(969, 'enseignants.php', NULL, '2026-09-07 08:44:07'),
(970, 'vie_etudiante.php', NULL, '2026-09-07 08:44:36'),
(971, 'vie_etudiante.php', NULL, '2026-09-07 08:44:36'),
(972, 'campus.php', NULL, '2026-09-07 08:44:43'),
(973, 'vie_etudiante.php', NULL, '2026-09-07 08:44:53'),
(974, 'associations.php', NULL, '2026-09-07 08:44:57'),
(975, 'bourse.php', NULL, '2026-09-07 08:45:11'),
(976, 'galerie.php', NULL, '2026-09-07 08:46:15'),
(977, 'galerie_album.php', NULL, '2026-09-07 08:46:21'),
(978, 'galerie.php', NULL, '2026-09-07 08:46:30'),
(979, 'evenements.php', NULL, '2026-09-07 08:46:36'),
(980, 'index.php', NULL, '2026-09-07 08:47:04'),
(981, 'index.php', NULL, '2026-09-07 08:47:04'),
(982, '404.php', NULL, '2026-09-07 08:47:04'),
(983, '404.php', NULL, '2026-09-07 08:47:04'),
(984, 'memoires.php', NULL, '2026-09-07 08:47:50'),
(985, '404.php', NULL, '2026-09-07 08:47:50'),
(986, '404.php', NULL, '2026-09-07 08:47:50'),
(987, 'documents.php', NULL, '2026-09-07 08:47:58'),
(988, 'actualite.php', NULL, '2026-09-07 08:48:17'),
(989, 'index.php', NULL, '2026-09-07 08:48:20'),
(990, 'index.php', NULL, '2026-09-07 08:50:01'),
(991, 'index.php', NULL, '2026-09-07 08:50:17'),
(992, 'index.php', NULL, '2026-09-07 08:50:25'),
(993, 'index.php', NULL, '2026-09-07 08:50:35'),
(994, 'login.php', NULL, '2026-09-07 08:50:39'),
(995, 'communaute.php', 3, '2026-09-07 08:57:16'),
(996, 'mes_amis.php', 3, '2026-09-07 08:57:35'),
(997, 'index.php', NULL, '2026-09-07 08:58:00'),
(998, 'login.php', NULL, '2026-09-07 08:58:03'),
(999, 'mes_groupes.php', 23, '2026-09-07 08:58:12'),
(1000, 'groupe_chat.php', 23, '2026-09-07 08:58:48'),
(1001, 'mes_groupes.php', 23, '2026-09-07 09:00:02'),
(1002, 'mes_amis.php', 23, '2026-09-07 09:00:11'),
(1003, 'profil.php', 23, '2026-09-07 09:00:15'),
(1004, 'index.php', 23, '2026-09-07 09:00:19'),
(1005, 'index.php', 23, '2026-09-07 09:00:27'),
(1006, '404.php', 23, '2026-09-07 09:00:27'),
(1007, '404.php', 23, '2026-09-07 09:00:27'),
(1008, '404.php', 23, '2026-09-07 09:00:52'),
(1009, '404.php', 23, '2026-09-07 09:00:54'),
(1010, 'index.php', 23, '2026-09-07 09:01:02'),
(1011, 'index.php', NULL, '2026-09-07 09:01:10'),
(1012, 'login.php', NULL, '2026-09-07 09:01:13'),
(1013, 'profil.php', 6, '2026-09-07 09:01:22'),
(1014, 'index.php', 6, '2026-09-07 09:01:31'),
(1015, 'index.php', 6, '2026-09-07 09:01:44'),
(1016, 'index.php', 6, '2026-09-07 09:02:10'),
(1017, 'index.php', 6, '2026-09-07 09:02:30'),
(1018, 'index.php', 6, '2026-09-07 09:02:34'),
(1019, 'index.php', NULL, '2026-09-07 09:02:52'),
(1020, 'login.php', NULL, '2026-09-07 09:02:55'),
(1021, 'profil.php', 6, '2026-09-07 09:03:03'),
(1022, 'index.php', 6, '2026-09-07 09:03:07'),
(1023, 'index.php', 6, '2026-09-07 09:03:10'),
(1024, 'profil.php', 6, '2026-09-07 09:03:15'),
(1025, 'historique.php', 6, '2026-09-07 09:03:37'),
(1026, 'index.php', 6, '2026-09-07 09:05:33'),
(1027, 'enseignants.php', 6, '2026-09-07 09:05:45'),
(1028, 'index.php', NULL, '2026-09-07 19:17:04');

-- --------------------------------------------------------

--
-- Structure de la table `partenaires`
--

CREATE TABLE `partenaires` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `logo_path` varchar(255) NOT NULL,
  `site_url` varchar(500) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `partenaires`
--

INSERT INTO `partenaires` (`id`, `nom`, `logo_path`, `site_url`, `display_order`, `created_at`) VALUES
(1, 'Université de Laval', 'images/partenariat/laval.png', 'https://www.ulaval.ca/', 1, '2026-08-10 23:06:30'),
(2, 'Département de physique de l\'Université d\'Antananarivo', 'images/partenariat/Université_Antananarivo.jpg', 'https://univ-antananarivo.mg/', 2, '2026-08-10 23:06:30'),
(3, 'Département de physique de l\'Université d\'Antsiranana', 'images/partenariat/logo-una_antsiranana.png', 'https://univants.mg/', 3, '2026-08-10 23:06:30'),
(4, 'Département de physique de l\'Université de Fianarantsoa', 'images/partenariat/fianarantsoa.png', 'https://www.univ-fianarantsoa.mg/', 4, '2026-08-10 23:06:30'),
(5, 'Département de physique de l\'Université de Tuléar', 'images/partenariat/LogoUnivTulear.jpg', 'https://www.univ-toliara.mg/', 5, '2026-08-10 23:06:30'),
(6, 'Institut pour la maîtrise de l\'Energie', 'images/partenariat/energie.png', 'https://www.univ-antananarivo.mg/institut-pour-la-maitrise-de-l-energie', 6, '2026-08-10 23:06:30'),
(7, 'Centre Don Bosco Mahajanga', 'images/partenariat/bosco.png', 'https://evbb.eu/members/centre-de-formation-professionnelle-don-bosco-antanimasaja-mahajanga/', 7, '2026-08-10 23:06:30'),
(8, 'Chambre de Commerce International de Mahajanga', 'images/partenariat/industrie.png', 'https://cpccaf.org/cci-de-mahajanga/', 8, '2026-08-10 23:06:30'),
(10, 'Université de Mahajanga', 'uploads/partenaire_6a7cf6995306a.png', 'https://www.mahajanga-univ.mg/', 9, '2026-08-12 22:41:29'),
(11, 'Direction Générale du trésor', 'uploads/partenaire_6a7d0659d76a8.png', 'http://www.tresorpublic.mg/', 10, '2026-08-12 23:48:41');

-- --------------------------------------------------------

--
-- Structure de la table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `preinscriptions`
--

CREATE TABLE `preinscriptions` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenoms` varchar(150) NOT NULL,
  `sexe` enum('M','F') NOT NULL,
  `date_naissance` date NOT NULL,
  `lieu_naissance` varchar(150) NOT NULL,
  `cin` varchar(30) DEFAULT NULL,
  `nationalite` varchar(100) NOT NULL,
  `annee_bacc` varchar(10) NOT NULL,
  `serie_bacc` varchar(50) NOT NULL,
  `serie_bacc_autre` varchar(150) DEFAULT NULL,
  `mention_bacc` varchar(30) NOT NULL,
  `code_redoublement` enum('N','R') NOT NULL,
  `adresse` varchar(255) NOT NULL,
  `telephone` varchar(30) NOT NULL,
  `email` varchar(150) NOT NULL,
  `nom_pere` varchar(150) DEFAULT NULL,
  `profession_pere` varchar(150) DEFAULT NULL,
  `nom_mere` varchar(150) DEFAULT NULL,
  `profession_mere` varchar(150) DEFAULT NULL,
  `adresse_parents` varchar(255) DEFAULT NULL,
  `contact_parents` varchar(50) DEFAULT NULL,
  `contact_parents_2` varchar(50) DEFAULT NULL,
  `pays` varchar(100) NOT NULL,
  `filiere_id` int(11) DEFAULT NULL,
  `niveau` varchar(10) DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `status` enum('en_attente','approuve') NOT NULL DEFAULT 'en_attente',
  `user_id` int(11) DEFAULT NULL,
  `dernier_mdp_genere` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `preinscriptions`
--

INSERT INTO `preinscriptions` (`id`, `nom`, `prenoms`, `sexe`, `date_naissance`, `lieu_naissance`, `cin`, `nationalite`, `annee_bacc`, `serie_bacc`, `serie_bacc_autre`, `mention_bacc`, `code_redoublement`, `adresse`, `telephone`, `email`, `nom_pere`, `profession_pere`, `nom_mere`, `profession_mere`, `adresse_parents`, `contact_parents`, `contact_parents_2`, `pays`, `filiere_id`, `niveau`, `photo_path`, `status`, `user_id`, `dernier_mdp_genere`, `created_at`) VALUES
(5, 'RAMANANA', 'Mirindra Michel', 'M', '2005-04-12', 'Moramanga', '31407794563127', 'Malgache', '2022', 'S', '', 'Assez Bien', 'N', '32 E1 Sect III Mahavoy Atsimo', '0380746987', 'mirindraramanana2@gmail.com', 'RAMANANA Nirina Michel', 'Infirmier', 'JOHARILALAINA Nirina', 'Boucher', 'Lot A 160 Camps des mariées', '0348277763', '', 'Madagascar', 1, 'L1', 'uploads/preinscriptions/photo_6a8a6c4369dc7.jpeg', 'approuve', 10, NULL, '2026-08-23 06:42:59'),
(7, 'RAZAFINDRABARY', 'Doleen Heather Jameelah', 'F', '2006-05-13', 'Mahajanga', '654233215623256', 'Malgache', '2022', 'S', 'S', 'Passable', 'N', 'amalavao', '0331255588', 'jamee@gmail.com', 'BARY', 'Avocat', 'Dollen', 'Institutrice', 'mahajanga', '0332211178', '', 'Madagascar', 3, 'L1', 'uploads/preinscriptions/photo_6a8ac55aa3c20.jpg', 'approuve', 14, 'zgxBBd2gNP', '2026-08-23 13:03:06');

-- --------------------------------------------------------

--
-- Structure de la table `preinscription_cta_media`
--

CREATE TABLE `preinscription_cta_media` (
  `id` int(11) NOT NULL,
  `media_type` enum('image','video') NOT NULL,
  `media_path` varchar(255) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `preinscription_cta_media`
--

INSERT INTO `preinscription_cta_media` (`id`, `media_type`, `media_path`, `display_order`, `created_at`) VALUES
(2, 'image', 'uploads/preinscription_cta_img_6a8ad22ba2302_0.gif', 1, '2026-08-23 13:57:47');

-- --------------------------------------------------------

--
-- Structure de la table `security_log`
--

CREATE TABLE `security_log` (
  `id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `identifiant` varchar(150) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `security_log`
--

INSERT INTO `security_log` (`id`, `event_type`, `identifiant`, `ip_address`, `details`, `created_at`) VALUES
(15, 'otp_mismatch', 'mirindraramanana2@gmail.com', '::1', '', '2026-08-26 01:11:01');

-- --------------------------------------------------------

--
-- Structure de la table `site_banners`
--

CREATE TABLE `site_banners` (
  `id` int(11) NOT NULL,
  `page_key` varchar(60) NOT NULL DEFAULT '',
  `media_type` enum('image','video') NOT NULL DEFAULT 'image',
  `media_path` varchar(500) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `site_banners`
--

INSERT INTO `site_banners` (`id`, `page_key`, `media_type`, `media_path`, `title`, `is_active`, `display_order`, `created_at`) VALUES
(5, 'confidentialite.php', 'image', 'uploads/banner_img_6a88da8f76285_0.gif', '222700', 1, 1, '2026-08-21 23:09:03'),
(6, 'mentions_legales.php', 'image', 'uploads/banner_img_6a88dae97a60c_0.gif', '222700', 1, 1, '2026-08-21 23:10:33'),
(7, 'profil.php', 'image', 'uploads/banner_img_6a88db2c6730e_0.png', '1224149', 1, 1, '2026-08-21 23:11:40'),
(8, 'login.php', 'image', 'uploads/banner_img_6a88db878ef05_0.gif', '224314', 0, 1, '2026-08-21 23:13:11'),
(9, 'recherche.php', 'video', 'uploads/banner_video_6a88dbbbd065b_0.mp4', '223071', 1, 1, '2026-08-21 23:14:03'),
(11, 'administrateur.php', 'image', 'uploads/banner_img_6a8a221d64448_0.gif', '222700', 1, 2, '2026-08-22 22:26:37'),
(12, 'admin_documents.php', 'image', 'uploads/banner_img_6a8df8fb3190c_0.jpg', '1190968', 1, 1, '2026-08-25 20:20:11');

-- --------------------------------------------------------

--
-- Structure de la table `site_content`
--

CREATE TABLE `site_content` (
  `id` int(11) NOT NULL,
  `content_key` varchar(100) NOT NULL,
  `content_value_fr` text NOT NULL,
  `content_value_en` text NOT NULL,
  `content_value_mg` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `site_content`
--

INSERT INTO `site_content` (`id`, `content_key`, `content_value_fr`, `content_value_en`, `content_value_mg`) VALUES
(1, 'directeur_nom', 'MANASINA Ruffin', 'MANASINA Ruffin', 'MANASINA Ruffin'),
(2, 'mot_directeur_contenu', 'C\'est avec une immense fierté que je vous accueille à l\'ISSTM, un lieu où l\'excellence académique rencontre l\'innovation et la discipline. Notre mission est de former les leaders de demain, des professionnels compétents et des citoyens responsables, prêts à relever les défis de notre temps. Nous nous engageons à vous offrir un environnement d\'apprentissage stimulant, soutenu par un corps enseignant dévoué. Rejoignez-nous pour construire ensemble votre avenir.', 'It is with immense pride that I welcome you to the ISSTM, a place where academic excellence meets innovation and discipline. Our mission is to develop the leaders of tomorrow, competent professionals and responsible citizens, ready to meet the challenges of our time. We are committed to providing you with a challenging learning environment, supported by dedicated faculty. Join us in building your future together.', 'Amim-pireharehana goavana no handraisako anareo ao amin\'ny ISSTM, toerana iray hihaonan\'ny manam-pahaizana tsara ny fanavaozana sy ny fitsipi-pifehezana. Ny asa fitoriana dia ny hanana ny mpitarika ny rahampitso, matihanina mahay sy ny tompon\'andraikitra olom-pirenena, vonona ny hihaona amin\'ny zava-tsarotra amin\'izao fotoana izao. Isika manolo-tena hanome anareo amin\'ny sarotra ny fianarana tontolo iainana, tohanan\'ny nanolo-tena sampam-pianarana. Miaraha aminay hanorina ny ho avy.'),
(3, 'mission_contenu', 'Former des techniciens et ingénieurs d\'élite, dotés de compétences pratiques et d\'un esprit d\'innovation, capables de contribuer activement au développement technologique et économique de Madagascar.', 'To train elite technicians and engineers, equipped with practical skills and a spirit of innovation, capable of actively contributing to the technological and economic development of Madagascar.', 'Mamolavola teknisiana sy injeniera sangany, manana fahaiza-manao azo ampiharina sy saina tia karokaroka, afaka mandray anjara mavitrika amin\'ny fampandrosoana ara-teknolojia sy ara-toekaren\'i Madagasikara.'),
(4, 'vision_contenu', 'Devenir un pôle d\'excellence et une référence nationale et régionale dans l\'enseignement supérieur technique et technologique, reconnu pour la qualité de ses diplômés et son impact sur la société.', 'To become a center of excellence and a national and regional benchmark in technical and technological higher education, recognized for the quality of its graduates and its impact on society.', 'Ho lasa ivon-toerana sangany sy ohatra nasionaly sy isam-paritra eo amin\'ny fampianarana ambony teknika sy teknolojia, ekena noho ny kalitaon\'ireo nahazo diplaoma sy ny fiantraikany eo amin\'ny fiaraha-monina.'),
(5, 'directeur_image_path', 'images/directeur.jpg', 'images/directeur.jpg', 'images/directeur.jpg'),
(9, 'mission_image_path', 'images/mission.jpg', 'images/mission.jpg', 'images/mission.jpg'),
(10, 'vision_image_path', 'images/vision.jpg', 'images/vision.jpg', 'images/vision.jpg'),
(11, 'logo_image_path', 'uploads/logo_6a7d103d4e3bd.PNG', 'uploads/logo_6a7d103d4e3bd.PNG', 'uploads/logo_6a7d103d4e3bd.PNG'),
(12, 'stat_students', '2500', '2500', '2500'),
(13, 'stat_teachers', '73', '73', '73'),
(14, 'stat_majors', '18', '18', '18'),
(31, 'footer_horaires', 'Lundi - Vendredi : 8h00 - 17h00', 'Monday - Friday: 8:00 AM - 5:00 PM', 'Alatsinainy - Zoma: 8ora - 17ora'),
(32, 'header_bg_image_path', 'uploads/header_bg_6a8b6f56c4b0a.PNG', 'uploads/header_bg_6a8b6f56c4b0a.PNG', 'uploads/header_bg_6a8b6f56c4b0a.PNG'),
(44, 'contact_email', 'isstm.umg@gmail.com', 'isstm.umg@gmail.com', 'isstm.umg@gmail.com'),
(45, 'contact_telephone', '+261 38 15 439 77', '+261 38 15 439 77', '+261 38 15 439 77'),
(46, 'contact_facebook', 'https://web.facebook.com/isstm.umg', 'https://web.facebook.com/isstm.umg', 'https://web.facebook.com/isstm.umg'),
(47, 'contact_adresse', 'Mahajanga, Madagascar', 'Mahajanga, Madagascar', 'Mahajanga, Madagasikara'),
(48, 'contact_adresse_detail', 'Bâtiment Ex-Lolo, en face de Leader Price, Majunga be', 'Ex-Lolo Building, opposite Leader Price, Majunga be', 'Trano Ex-Lolo, tandrifin\'i Leader Price, Majunga be'),
(51, 'inscription_annee_universitaire', '2026', '2026', '2026'),
(52, 'inscription_date_limite', '2026-10-09', '2026-10-09', '2026-10-09'),
(53, 'inscription_adresse_bloc', 'Mme le Chef de Service de la Scolarité Centrale\r\nUniversité de Mahajanga, BP 652, Mahajanga (401)\r\nTél : 034 44 889 86', 'Mme le Chef de Service de la Scolarité Centrale\r\nUniversité de Mahajanga, BP 652, Mahajanga (401)\r\nTél : 034 44 889 86', 'Mme le Chef de Service de la Scolarité Centrale\r\nUniversité de Mahajanga, BP 652, Mahajanga (401)\r\nTél : 034 44 889 86'),
(54, 'inscription_compte_bancaire', '00650 05004012981-07', '00650 05004012981-07', '00650 05004012981-07'),
(55, 'frais_nat_lic_droit', '750 000 Ar', '750 000 Ar', '750 000 Ar'),
(56, 'frais_nat_lic_v1', '250 000 Ar', '250 000 Ar', '250 000 Ar'),
(57, 'frais_nat_lic_v2', '250 000 Ar', '250 000 Ar', '250 000 Ar'),
(58, 'frais_nat_lic_v3', '250 000 Ar', '250 000 Ar', '250 000 Ar'),
(59, 'frais_nat_mas_droit', '1 050 000 Ar', '1 050 000 Ar', '1 050 000 Ar'),
(60, 'frais_nat_mas_v1', '550 000 Ar', '550 000 Ar', '550 000 Ar'),
(61, 'frais_nat_mas_v2', '250 000 Ar', '250 000 Ar', '250 000 Ar'),
(62, 'frais_nat_mas_v3', '250 000 Ar', '250 000 Ar', '250 000 Ar'),
(63, 'frais_nat_tenue', '20 000 Ar', '20 000 Ar', '20 000 Ar'),
(64, 'frais_etr_lic_droit', '1 050 000 Ar', '1 050 000 Ar', '1 050 000 Ar'),
(65, 'frais_etr_lic_v1', '350 000 Ar', '350 000 Ar', '350 000 Ar'),
(66, 'frais_etr_lic_v2', '350 000 Ar', '350 000 Ar', '350 000 Ar'),
(67, 'frais_etr_lic_v3', '350 000 Ar', '350 000 Ar', '350 000 Ar'),
(68, 'frais_etr_mas_droit', '1 500 000 Ar', '1 500 000 Ar', '1 500 000 Ar'),
(69, 'frais_etr_mas_v1', '750 000 Ar', '750 000 Ar', '750 000 Ar'),
(70, 'frais_etr_mas_v2', '375 000 Ar', '375 000 Ar', '375 000 Ar'),
(71, 'frais_etr_mas_v3', '375 000 Ar', '375 000 Ar', '375 000 Ar'),
(72, 'frais_etr_tenue', '20 000 Ar', '20 000 Ar', '20 000 Ar');

-- --------------------------------------------------------

--
-- Structure de la table `site_stats`
--

CREATE TABLE `site_stats` (
  `stat_key` varchar(50) NOT NULL,
  `stat_value` bigint(20) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `site_stats`
--

INSERT INTO `site_stats` (`stat_key`, `stat_value`) VALUES
('homepage_views', 50);

-- --------------------------------------------------------

--
-- Structure de la table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `categorie` enum('permanent','vacataire') NOT NULL,
  `specialite_fr` varchar(255) NOT NULL,
  `specialite_en` varchar(255) DEFAULT NULL,
  `specialite_mg` varchar(255) DEFAULT NULL,
  `description_fr` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `description_mg` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `teachers`
--

INSERT INTO `teachers` (`id`, `nom`, `categorie`, `specialite_fr`, `specialite_en`, `specialite_mg`, `description_fr`, `description_en`, `description_mg`, `photo`, `email`, `display_order`) VALUES
(1, 'RAKOTOVELO Geoslin', 'permanent', 'Physique', 'Physics', NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Physique, au service de la réussite des étudiants de l\'ISSTM.', 'Permanent teacher specializing in Physics, serving the success of ISSTM students.', NULL, NULL, 'rakotovelo.geoslin@isstm.mg', 1),
(2, 'AMBEONDAHY', 'permanent', 'Mathématiques appliquées', 'Applied mathematics', NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Mathématiques appliquées, au service de la réussite des étudiants de l\'ISSTM.', 'Permanent teacher specializing in Applied Mathematics, serving the success of ISSTM students.', NULL, NULL, 'ambeondahy@isstm.mg', 2),
(3, 'JOHANESA Fernand', 'permanent', 'BTP', 'BPW', NULL, 'Enseignant(e) permanent(e) spécialisé(e) en BTP, au service de la réussite des étudiants de l\'ISSTM.', 'Permanent teacher specializing in construction, serving the success of ISSTM students.', NULL, NULL, 'johanesa.fernand@isstm.mg', 3),
(4, 'MANASINA Ruffin', 'permanent', 'Electricité', 'Electricity', NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Electricité, au service de la réussite des étudiants de l\'ISSTM.', 'Permanent teacher specializing in Electricity, serving the success of ISSTM students.', NULL, NULL, 'manasina.ruffin@isstm.mg', 4),
(5, 'RAMAROJAONA Hubert', 'permanent', 'Génie nucléaire et automatique', 'Nuclear and automatic engineering', NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Génie nucléaire et automatique, au service de la réussite des étudiants de l\'ISSTM.', 'Permanent teacher specializing in Nuclear and Automatic Engineering, serving the success of ISSTM students.', NULL, NULL, 'ramarojaona.hubert@isstm.mg', 5),
(6, 'RAVOHITRA Juvence', 'permanent', 'Mécanique', 'Mechanics', NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Mécanique, au service de la réussite des étudiants de l\'ISSTM.', 'Permanent teacher specializing in Mechanics, serving the success of ISSTM students.', NULL, NULL, 'ravohitra.juvence@isstm.mg', 6),
(7, 'MAXWELL Djaffard', 'permanent', 'Physique', 'Physics', NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Physique, au service de la réussite des étudiants de l\'ISSTM.', 'Permanent teacher specializing in Physics, serving the success of ISSTM students.', NULL, NULL, 'maxwell.djaffard@isstm.mg', 7),
(8, 'HARY Jean', 'permanent', 'Physique', 'Physics', NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Physique, au service de la réussite des étudiants de l\'ISSTM.', 'Permanent teacher specializing in Physics, serving the success of ISSTM students.', NULL, NULL, 'hary.jean@isstm.mg', 8),
(9, 'RANDRIAMAITSO Télesphore', 'permanent', 'Energétique', 'Energising', NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Energétique, au service de la réussite des étudiants de l\'ISSTM.', 'Permanent teacher specializing in Energy, at the service of the success of ISSTM students.', NULL, NULL, 'randriamaitso.telesphore@isstm.mg', 9),
(10, 'TSANGANDRAZANA Annicet Judicael', 'permanent', 'Physique', NULL, NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Physique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'tsangandrazana.annicet.judicael@isstm.mg', 10),
(11, 'RAJAONASY Iantara', 'permanent', 'Génie Civil', NULL, NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Génie Civil, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'rajaonasy.iantara@isstm.mg', 11),
(12, 'RAZAFIARISON Ignace Abel J', 'permanent', 'Energétique', NULL, NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Energétique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'razafiarison.ignace.abel.j@isstm.mg', 12),
(13, 'ANDRIANIRINA Charles Bernard', 'permanent', 'Electronique Industrielle', NULL, NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Electronique Industrielle, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'andrianirina.charles.bernard@isstm.mg', 13),
(14, 'RAKOTOMALALA Noelimihaja S', 'permanent', 'Energétique et Génie Electrique', NULL, NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Energétique et Génie Electrique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'rakotomalala.noelimihaja.s@isstm.mg', 14),
(15, 'RALINAVALONA Jhonson Jemi', 'permanent', 'Anglaises/Education', NULL, NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Anglaises/Education, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'ralinavalona.jhonson.jemi@isstm.mg', 15),
(16, 'ABDULHAMID Asma', 'permanent', 'Réseaux/Télécommunications', NULL, NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Réseaux/Télécommunications, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'abdulhamid.asma@isstm.mg', 16),
(17, 'ANDRIANANTENAINA Chrysostome', 'permanent', 'Electronique et informatique', NULL, NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Electronique et informatique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'andrianantenaina.chrysostome@isstm.mg', 17),
(18, 'HANITRANIRINA Eloddy', 'permanent', 'Conversion des énergies', NULL, NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Conversion des énergies, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'hanitranirina.eloddy@isstm.mg', 18),
(19, 'HANTA Tina Olga', 'permanent', 'Physique', NULL, NULL, 'Enseignant(e) permanent(e) spécialisé(e) en Physique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'hanta.tina.olga@isstm.mg', 19),
(20, 'ANDRIANASOLONIRINA Ravoarimalala Naivosaona', 'vacataire', 'Agro-Management', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Agro-Management, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'andrianasolonirina.ravoarimalala.naivosaona@isstm.mg', 20),
(21, 'ANDRIANONY Mandimby Vonifandeferana', 'vacataire', 'ISEA', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en ISEA, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'andrianony.mandimby.vonifandeferana@isstm.mg', 21),
(22, 'ANDRINIRINIAIMALAZA Fanambinantsoa Philibert', 'vacataire', 'Electronique et informatique industrielles', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Electronique et informatique industrielles, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'andriniriniaimalaza.fanambinantsoa.philibert@isstm.mg', 22),
(23, 'FREDERIC Moise', 'vacataire', 'Génie Logiciel', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Génie Logiciel, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'frederic.moise@isstm.mg', 23),
(24, 'HABIB Nouraly', 'vacataire', 'Imagerie Médicale', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Imagerie Médicale, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'habib.nouraly@isstm.mg', 24),
(25, 'BEZARA Florent', 'vacataire', 'Informatique', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Informatique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'bezara.florent@isstm.mg', 25),
(26, 'HERIPINOANARIMANANA Fenomora Evariste', 'vacataire', 'Aménagement et Génie urbain', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Aménagement et Génie urbain, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'heripinoanarimanana.fenomora.evariste@isstm.mg', 26),
(27, 'HOUSSEN Fils Auguste', 'vacataire', 'Energie', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Energie, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'houssen.fils.auguste@isstm.mg', 27),
(28, 'JAONA Romain', 'vacataire', 'Bâtiment et Travaux publics', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Bâtiment et Travaux publics, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'jaona.romain@isstm.mg', 28),
(29, 'JAONARANA Eric', 'vacataire', 'Ingénierie des matériaux et des matières premières', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Ingénierie des matériaux et des matières premières, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'jaonarana.eric@isstm.mg', 29),
(30, 'LUCIEN FIDELE François d\'Assise', 'vacataire', 'Electronique Médicale', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Electronique Médicale, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'lucien.fidele.francois.d.assise@isstm.mg', 30),
(31, 'MANANTSAINA Antoine Frédo', 'vacataire', 'Physique', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Physique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'manantsaina.antoine.fredo@isstm.mg', 31),
(32, 'MANIGNIAVY Sergio Andrew', 'vacataire', 'Génie Civil', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Génie Civil, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'manigniavy.sergio.andrew@isstm.mg', 32),
(33, 'MELRAK Nykaise', 'vacataire', 'Biomédical', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Biomédical, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'melrak.nykaise@isstm.mg', 33),
(34, 'PETERA Benjamin', 'vacataire', 'Chimie des Substances Naturelles', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Chimie des Substances Naturelles, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'petera.benjamin@isstm.mg', 34),
(35, 'RABENIAINA Anjara Davio Ulrick', 'vacataire', 'Physique du Globe', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Physique du Globe, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'rabeniaina.anjara.davio.ulrick@isstm.mg', 35),
(36, 'RAHARIVOLOLONA Ando Lalaina', 'vacataire', 'Bâtiment et Travaux publics', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Bâtiment et Travaux publics, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'raharivololona.ando.lalaina@isstm.mg', 36),
(37, 'RAJAONASY Prosperia Riwoldek', 'vacataire', 'Droit', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Droit, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'rajaonasy.prosperia.riwoldek@isstm.mg', 37),
(38, 'RAKOTONDRAZAFY Florent', 'vacataire', 'Génie Mécanique', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Génie Mécanique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'rakotondrazafy.florent@isstm.mg', 38),
(39, 'RALAINANDRASANA Heri-Zo', 'vacataire', 'Electromécanique et Informatique industriel', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Electromécanique et Informatique industriel, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'ralainandrasana.heri.zo@isstm.mg', 39),
(40, 'RAMANAMPAMONJY Jean Claude', 'vacataire', 'Architecte', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Architecte, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'ramanampamonjy.jean.claude@isstm.mg', 40),
(41, 'RAMIANDRA Aina Clarc', 'vacataire', 'Physique', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Physique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'ramiandra.aina.clarc@isstm.mg', 41),
(42, 'RANAIVOSON Tahirisoa', 'vacataire', 'Hydrogéologie', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Hydrogéologie, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'ranaivoson.tahirisoa@isstm.mg', 42),
(43, 'RANDRIAMANAFANA Alain', 'vacataire', 'ISEA', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en ISEA, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'randriamanafana.alain@isstm.mg', 43),
(44, 'RANDRIANA Laurence Vanina', 'vacataire', 'Finance et Comptabilité', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Finance et Comptabilité, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'randriana.laurence.vanina@isstm.mg', 44),
(45, 'RANDRIANANDRASANARIVO Raphaëlson Jacques', 'vacataire', 'Physique', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Physique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'randrianandrasanarivo.raphaelson.jacques@isstm.mg', 45),
(46, 'RANDRIANANTENAINA Todihasina Roselin', 'vacataire', 'Physique et applications', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Physique et applications, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'randrianantenaina.todihasina.roselin@isstm.mg', 46),
(47, 'RANDRIANARISOA Ernest', 'vacataire', 'ISEA', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en ISEA, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'randrianarisoa.ernest@isstm.mg', 47),
(48, 'RANDRIANARIVELO Eddy Flocaudel', 'vacataire', 'Physique', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Physique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'randrianarivelo.eddy.flocaudel@isstm.mg', 48),
(49, 'RAPATSALAHY Miary Andrianjaka', 'vacataire', 'Ingénierie logicielle', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Ingénierie logicielle, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'rapatsalahy.miary.andrianjaka@isstm.mg', 49),
(50, 'RASOAHANITRINIAINA Théphile', 'vacataire', 'Economico-Gestion', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Economico-Gestion, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'rasoahanitriniaina.thephile@isstm.mg', 50),
(51, 'RASOANANDRASANA Marizia Roberta', 'vacataire', 'Physique', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Physique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'rasoanandrasana.marizia.roberta@isstm.mg', 51),
(52, 'RASOLOHARISOA Marie Claudia', 'vacataire', 'Gestion', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Gestion, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'rasoloharisoa.marie.claudia@isstm.mg', 52),
(53, 'RAVELOMIARINA François', 'vacataire', 'Conversion d\'Energie', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Conversion d\'Energie, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'ravelomiarina.francois@isstm.mg', 53),
(54, 'RAZAFIMEVA Marie Odine', 'vacataire', 'Science de la Gestion', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Science de la Gestion, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'razafimeva.marie.odine@isstm.mg', 54),
(55, 'RAZAFINDRABEHITA Lwanga Albert', 'vacataire', 'Mathématiques informatiques', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Mathématiques informatiques, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'razafindrabehita.lwanga.albert@isstm.mg', 55),
(56, 'RHEVIHAJA Solo Njara', 'vacataire', 'Conversion d\'Energie', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Conversion d\'Energie, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'rhevihaja.solo.njara@isstm.mg', 56),
(57, 'ROGER Andriantsitoha Romuald', 'vacataire', 'Physique du Globe', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Physique du Globe, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'roger.andriantsitoha.romuald@isstm.mg', 57),
(58, 'RUINO Randriamihaja', 'vacataire', 'Physique et applications', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Physique et applications, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'ruino.randriamihaja@isstm.mg', 58),
(59, 'SOAFARA Erilà Franclin', 'vacataire', 'Mécanique Productique', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Mécanique Productique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'soafara.erila.franclin@isstm.mg', 59),
(60, 'TOTOZAFINY Théodore', 'vacataire', 'Informatique et Electronique', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Informatique et Electronique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'totozafiny.theodore@isstm.mg', 60),
(61, 'VAVIZARA Sylvie', 'vacataire', 'Energétique', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Energétique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'vavizara.sylvie@isstm.mg', 61),
(62, 'ZAFINTSALAMA Manohinaina Minontsoa Gabriel', 'vacataire', 'Electronique Médicale', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Electronique Médicale, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'zafintsalama.manohinaina.minontsoa.gabriel@isstm.mg', 62),
(63, 'ZANAMASY Avatiana Augustin', 'vacataire', 'Bâtiment et Travaux publics', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Bâtiment et Travaux publics, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'zanamasy.avatiana.augustin@isstm.mg', 63),
(64, 'RAPATSALAHY Miary', 'vacataire', 'Génie Logiciel', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Génie Logiciel, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'rapatsalahy.miary@isstm.mg', 64),
(65, 'RAZANAMANITRA Ranjasoanandrianina', 'vacataire', 'Gestion', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Gestion, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'razanamanitra.ranjasoanandrianina@isstm.mg', 65),
(66, 'RANDRIANALY Fetra', 'vacataire', 'Génie Electrique', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Génie Electrique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'randrianaly.fetra@isstm.mg', 66),
(67, 'RANDRIANASOLOMANGA N. R. Aurélie', 'vacataire', 'Langue', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Langue, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'randrianasolomanga.n.r.aurelie@isstm.mg', 67),
(68, 'RAKOTOMANGA Anjatiana', 'vacataire', 'Langue', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Langue, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'rakotomanga.anjatiana@isstm.mg', 68),
(69, 'TOTOZANDRY Jacquot', 'vacataire', 'Télécommunications et Réseaux', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Télécommunications et Réseaux, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'totozandry.jacquot@isstm.mg', 69),
(70, 'RANDRIAMAHEFA Alido Soidry', 'vacataire', 'Génie Electrique', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Génie Electrique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'randriamahefa.alido.soidry@isstm.mg', 70),
(71, 'RANDRIA Amédé William', 'vacataire', 'Electronique et Informatique Industrielle', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Electronique et Informatique Industrielle, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'randria.amede.william@isstm.mg', 71),
(72, 'RANDIMBISON Herizo', 'vacataire', 'Génie Civil', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Génie Civil, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'randimbison.herizo@isstm.mg', 72),
(73, 'RAMAHALAZA Hardis', 'vacataire', 'Génie Electrique', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Génie Electrique, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'ramahalaza.hardis@isstm.mg', 73),
(74, 'DONAZY Fanomezantsoa Trésor', 'vacataire', 'Génie Logiciel et Base de données', NULL, NULL, 'Enseignant(e) vacataire spécialisé(e) en Génie Logiciel et Base de données, au service de la réussite des étudiants de l\'ISSTM.', NULL, NULL, NULL, 'donazy.fanomezantsoa.tresor@isstm.mg', 74);

-- --------------------------------------------------------

--
-- Structure de la table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `author_name` varchar(255) NOT NULL,
  `program` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `quote_fr` text NOT NULL,
  `quote_en` text NOT NULL,
  `quote_mg` text NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `testimonials`
--

INSERT INTO `testimonials` (`id`, `author_name`, `program`, `image_path`, `quote_fr`, `quote_en`, `quote_mg`, `display_order`) VALUES
(1, 'Tanael Jaosoa', 'Génie Informatique', 'images/etudiant/tanael.jpg', 'L\'ISSTM m\'a donné les outils pour transformer mes idées en projets concrets. Les enseignants sont de vrais mentors.', 'ISSTM gave me the tools to turn my ideas into concrete projects. The teachers are true mentors.', 'Ny ISSTM no nanome ahy fitaovana hamadihana ny hevitro ho tetikasa mivaingana. Tena mpanoro hevitra ny mpampianatra.', 1),
(2, 'Mirindra Ramanana', 'Génie Informatique', 'images/etudiant/mirindra.jpeg', 'La formation pratique et les stages en entreprise m\'ont permis d\'être opérationnelle dès la sortie de l\'école.', 'The practical training and internships allowed me to be operational right out of school.', 'Ny fiofanana azo ampiharina sy ny fianarana asa tany amin\'ny orinasa no nahatonga ahy ho afaka niasa avy hatrany rehefa nivoaka ny sekoly.', 2),
(3, 'Safidy Thierry', 'Génie Civil', 'images/etudiant/safidy.jpg', 'J\'ai pu développer ma créativité et ma rigueur technique grâce à des projets stimulants et un encadrement de qualité.', 'I was able to develop my creativity and technical rigor thanks to stimulating projects and quality supervision.', 'Afaka nampivelatra ny fahaizako mamorona sy ny fahaizako ara-teknika aho noho ny tetikasa mandrisika sy ny fanaraha-maso kalitao.', 3),
(4, 'Heather Jameelah', 'Génie Biomédical', 'images/etudiant/jameelah.jpg', 'L\'ambiance d\'entraide et la richesse des cours m\'ont poussée à me dépasser. C\'est plus qu\'une école, c\'est une famille.', 'The atmosphere of mutual support and the richness of the courses pushed me to surpass myself. It\'s more than a school, it\'s a family.', 'Ny rivo-piainana mifampitsimbina sy ny harenan\'ny fampianarana no nanosika ahy hihoatra ny tenako. Mihoatra ny sekoly izy io, fianakaviana.', 4);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `dernier_mdp_genere` varchar(20) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `role` enum('admin','user','etudiant','enseignant','bibliotheque') DEFAULT 'user',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_messagerie` tinyint(1) NOT NULL DEFAULT 0,
  `is_scolarite` tinyint(1) NOT NULL DEFAULT 0,
  `is_bibliotheque` tinyint(1) NOT NULL DEFAULT 0,
  `last_activity` datetime DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `otp_code` varchar(255) DEFAULT NULL,
  `otp_expires` datetime DEFAULT NULL,
  `otp_attempts` tinyint(4) NOT NULL DEFAULT 0,
  `otp_requested_at` datetime DEFAULT NULL,
  `otp_request_count` tinyint(4) NOT NULL DEFAULT 0,
  `bio` varchar(500) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `centres_interet` varchar(255) DEFAULT NULL,
  `lien_facebook` varchar(255) DEFAULT NULL,
  `lien_linkedin` varchar(255) DEFAULT NULL,
  `site_web` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `email`, `mot_de_passe`, `dernier_mdp_genere`, `avatar_path`, `role`, `date_creation`, `is_messagerie`, `is_scolarite`, `is_bibliotheque`, `last_activity`, `reset_token`, `reset_token_expires`, `telephone`, `otp_code`, `otp_expires`, `otp_attempts`, `otp_requested_at`, `otp_request_count`, `bio`, `date_naissance`, `ville`, `centres_interet`, `lien_facebook`, `lien_linkedin`, `site_web`) VALUES
(3, 'Mirindra RAMANANA', 'mirindra@gmail.com', '$2y$10$xnOVEMvHhwiQmmUwX34QMOo3pYAZBpNJjx0HNmUgjQN2hUOxuWihO', NULL, 'uploads/avatar_3_6a7a46ce2bdb2.jpg', 'admin', '2026-07-17 10:04:02', 1, 0, 0, '2026-09-07 11:57:35', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'Kakal', 'kakal', '$2y$10$M8VyhBEBHYYjo/3GIXdVJ.lnvgxmxijhnQD4kJK6R7c3skCQinOs2', NULL, 'uploads/avatar_6_6a8a2e9d5d87a.jpg', 'user', '2026-08-22 23:03:14', 1, 0, 0, '2026-09-07 12:05:45', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'Scolarité', 'scolarite', '$2y$10$UIf0uuahoKSyQNXFBadgYeKfSTbvUjQUh8tMxGI.QNruHV6fcRoD6', NULL, 'uploads/avatar_7_6a8a2d774da20.jpg', 'user', '2026-08-22 23:03:14', 1, 1, 0, '2026-09-04 10:36:49', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'RAMANANA Mirindra Michel', 'MIRINDRA', '$2y$10$NzvEF2RCFC62Qkvm7VBC3OzSAE35SuuOOm0m0ImFKWAcllPTKY2pS', NULL, 'uploads/preinscriptions/photo_6a8a6c4369dc7.jpeg', 'etudiant', '2026-08-23 03:44:41', 0, 0, 0, '2026-09-04 14:16:35', '7ee23ab9cc1f5ccaf61345645921583daadc97b897c25454a649b5ad9de85933', '2026-08-26 04:04:47', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'RAZAFINDRABARY Doleen Heather Jameelah', 'USER_ISSTM_7', '$2y$10$681Q3UDC/TAOw.FLZPr9QuBc8RuzcvrAT/W4wQUZH1gy9EV1UxMTK', NULL, 'uploads/preinscriptions/photo_6a8ac55aa3c20.jpg', 'etudiant', '2026-08-23 10:04:01', 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 'FREDERIC Moise', 'moise@gmail.com', '$2y$10$AgVSAnLt8jcfsWESCdxdFuQ.UVW5Qb.MS2WAqSSyJseXmIAi0iUsO', '8AyKBp2gFr', 'uploads/avatar_23_6a8ae920ba229.jpg', 'enseignant', '2026-08-23 12:34:32', 0, 0, 0, '2026-09-07 12:01:02', NULL, NULL, '0380746984', NULL, NULL, 0, NULL, 0, 'MMMM', NULL, NULL, NULL, NULL, NULL, NULL),
(63, 'MANASINA Ruffin', 'Directeur', '$2y$10$HJbnt.ngHNvMMqXIx7RH0.talnqZA/dzoInnWd0mJONu2FHmJjF.2', NULL, 'uploads/avatar_63_6a8e3b0759ac8.jpg', 'admin', '2026-08-26 00:59:27', 1, 1, 1, '2026-09-04 14:17:41', NULL, NULL, '032 05 579 95', NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin_notes`
--
ALTER TABLE `admin_notes`
  ADD PRIMARY KEY (`user_id`);

--
-- Index pour la table `amis_demandes`
--
ALTER TABLE `amis_demandes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `paire` (`demandeur_id`,`destinataire_id`),
  ADD KEY `destinataire_id` (`destinataire_id`);

--
-- Index pour la table `campus_blocs`
--
ALTER TABLE `campus_blocs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bloc_key` (`bloc_key`);

--
-- Index pour la table `communaute_comments`
--
ALTER TABLE `communaute_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `communaute_notifications`
--
ALTER TABLE `communaute_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Index pour la table `communaute_posts`
--
ALTER TABLE `communaute_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `auteur_id` (`auteur_id`);

--
-- Index pour la table `communaute_post_media`
--
ALTER TABLE `communaute_post_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`);

--
-- Index pour la table `communaute_reactions`
--
ALTER TABLE `communaute_reactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `post_user` (`post_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `dm_attachments`
--
ALTER TABLE `dm_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Index pour la table `dm_conversations`
--
ALTER TABLE `dm_conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `paire` (`user_a_id`,`user_b_id`),
  ADD KEY `user_b_id` (`user_b_id`);

--
-- Index pour la table `dm_messages`
--
ALTER TABLE `dm_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversation_id` (`conversation_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Index pour la table `dm_message_hides`
--
ALTER TABLE `dm_message_hides`
  ADD PRIMARY KEY (`message_id`,`user_id`);

--
-- Index pour la table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `evenements`
--
ALTER TABLE `evenements`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `filieres`
--
ALTER TABLE `filieres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Index pour la table `filiere_blocks`
--
ALTER TABLE `filiere_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_filiere` (`filiere_id`);

--
-- Index pour la table `gallery_albums`
--
ALTER TABLE `gallery_albums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Index pour la table `gallery_categories`
--
ALTER TABLE `gallery_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Index pour la table `gallery_photos`
--
ALTER TABLE `gallery_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `album_id` (`album_id`);

--
-- Index pour la table `groupes_classe`
--
ALTER TABLE `groupes_classe`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code_unique` (`code_unique`),
  ADD KEY `enseignant_id` (`enseignant_id`);

--
-- Index pour la table `groupes_utilisateurs`
--
ALTER TABLE `groupes_utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code_unique` (`code_unique`),
  ADD KEY `createur_id` (`createur_id`);

--
-- Index pour la table `groupe_annonces`
--
ALTER TABLE `groupe_annonces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groupe_id` (`groupe_id`),
  ADD KEY `enseignant_id` (`enseignant_id`);

--
-- Index pour la table `groupe_membres`
--
ALTER TABLE `groupe_membres`
  ADD PRIMARY KEY (`groupe_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `groupe_messages`
--
ALTER TABLE `groupe_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groupe_id` (`groupe_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Index pour la table `groupe_message_attachments`
--
ALTER TABLE `groupe_message_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Index pour la table `groupe_message_hides`
--
ALTER TABLE `groupe_message_hides`
  ADD PRIMARY KEY (`message_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `groupe_presence_marks`
--
ALTER TABLE `groupe_presence_marks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_user` (`session_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `groupe_presence_sessions`
--
ALTER TABLE `groupe_presence_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `groupe_date` (`groupe_id`,`session_date`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `groupe_utilisateurs_attachments`
--
ALTER TABLE `groupe_utilisateurs_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Index pour la table `groupe_utilisateurs_hides`
--
ALTER TABLE `groupe_utilisateurs_hides`
  ADD PRIMARY KEY (`message_id`,`user_id`);

--
-- Index pour la table `groupe_utilisateurs_membres`
--
ALTER TABLE `groupe_utilisateurs_membres`
  ADD PRIMARY KEY (`groupe_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `groupe_utilisateurs_messages`
--
ALTER TABLE `groupe_utilisateurs_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groupe_id` (`groupe_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Index pour la table `hero_slides`
--
ALTER TABLE `hero_slides`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `messagerie_attachments`
--
ALTER TABLE `messagerie_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_msg_attach` (`message_id`);

--
-- Index pour la table `messagerie_messages`
--
ALTER TABLE `messagerie_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sender` (`sender_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Index pour la table `messagerie_message_hides`
--
ALTER TABLE `messagerie_message_hides`
  ADD PRIMARY KEY (`message_id`,`user_id`);

--
-- Index pour la table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `newsletter_campaigns`
--
ALTER TABLE `newsletter_campaigns`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `news_articles`
--
ALTER TABLE `news_articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Index pour la table `news_attachments`
--
ALTER TABLE `news_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `news_categories`
--
ALTER TABLE `news_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Index pour la table `news_photos`
--
ALTER TABLE `news_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_article` (`article_id`);

--
-- Index pour la table `org_people`
--
ALTER TABLE `org_people`
  ADD PRIMARY KEY (`title_key`);

--
-- Index pour la table `page_views`
--
ALTER TABLE `page_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Index pour la table `partenaires`
--
ALTER TABLE `partenaires`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Index pour la table `preinscriptions`
--
ALTER TABLE `preinscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `preinscription_cta_media`
--
ALTER TABLE `preinscription_cta_media`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `security_log`
--
ALTER TABLE `security_log`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `site_banners`
--
ALTER TABLE `site_banners`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_page_key` (`page_key`);

--
-- Index pour la table `site_content`
--
ALTER TABLE `site_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `content_key` (`content_key`);

--
-- Index pour la table `site_stats`
--
ALTER TABLE `site_stats`
  ADD PRIMARY KEY (`stat_key`);

--
-- Index pour la table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `amis_demandes`
--
ALTER TABLE `amis_demandes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `campus_blocs`
--
ALTER TABLE `campus_blocs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pour la table `communaute_comments`
--
ALTER TABLE `communaute_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT pour la table `communaute_notifications`
--
ALTER TABLE `communaute_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT pour la table `communaute_posts`
--
ALTER TABLE `communaute_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `communaute_post_media`
--
ALTER TABLE `communaute_post_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `communaute_reactions`
--
ALTER TABLE `communaute_reactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `dm_attachments`
--
ALTER TABLE `dm_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `dm_conversations`
--
ALTER TABLE `dm_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `dm_messages`
--
ALTER TABLE `dm_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `evenements`
--
ALTER TABLE `evenements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `filieres`
--
ALTER TABLE `filieres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `filiere_blocks`
--
ALTER TABLE `filiere_blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=363;

--
-- AUTO_INCREMENT pour la table `gallery_albums`
--
ALTER TABLE `gallery_albums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `gallery_categories`
--
ALTER TABLE `gallery_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT pour la table `gallery_photos`
--
ALTER TABLE `gallery_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

--
-- AUTO_INCREMENT pour la table `groupes_classe`
--
ALTER TABLE `groupes_classe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `groupes_utilisateurs`
--
ALTER TABLE `groupes_utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `groupe_annonces`
--
ALTER TABLE `groupe_annonces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `groupe_messages`
--
ALTER TABLE `groupe_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `groupe_message_attachments`
--
ALTER TABLE `groupe_message_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `groupe_presence_marks`
--
ALTER TABLE `groupe_presence_marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `groupe_presence_sessions`
--
ALTER TABLE `groupe_presence_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `groupe_utilisateurs_attachments`
--
ALTER TABLE `groupe_utilisateurs_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `groupe_utilisateurs_messages`
--
ALTER TABLE `groupe_utilisateurs_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `hero_slides`
--
ALTER TABLE `hero_slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `messagerie_attachments`
--
ALTER TABLE `messagerie_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `messagerie_messages`
--
ALTER TABLE `messagerie_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT pour la table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `newsletter_campaigns`
--
ALTER TABLE `newsletter_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `news_articles`
--
ALTER TABLE `news_articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `news_attachments`
--
ALTER TABLE `news_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `news_categories`
--
ALTER TABLE `news_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `news_photos`
--
ALTER TABLE `news_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `page_views`
--
ALTER TABLE `page_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1029;

--
-- AUTO_INCREMENT pour la table `partenaires`
--
ALTER TABLE `partenaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `preinscriptions`
--
ALTER TABLE `preinscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `preinscription_cta_media`
--
ALTER TABLE `preinscription_cta_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `security_log`
--
ALTER TABLE `security_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `site_banners`
--
ALTER TABLE `site_banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `site_content`
--
ALTER TABLE `site_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT pour la table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT pour la table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `admin_notes`
--
ALTER TABLE `admin_notes`
  ADD CONSTRAINT `admin_notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `amis_demandes`
--
ALTER TABLE `amis_demandes`
  ADD CONSTRAINT `amis_demandes_ibfk_1` FOREIGN KEY (`demandeur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `amis_demandes_ibfk_2` FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `communaute_comments`
--
ALTER TABLE `communaute_comments`
  ADD CONSTRAINT `communaute_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `communaute_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `communaute_comments_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `communaute_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `communaute_comments_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `communaute_notifications`
--
ALTER TABLE `communaute_notifications`
  ADD CONSTRAINT `communaute_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `communaute_notifications_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `communaute_posts` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `communaute_posts`
--
ALTER TABLE `communaute_posts`
  ADD CONSTRAINT `communaute_posts_ibfk_1` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `communaute_post_media`
--
ALTER TABLE `communaute_post_media`
  ADD CONSTRAINT `communaute_post_media_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `communaute_posts` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `communaute_reactions`
--
ALTER TABLE `communaute_reactions`
  ADD CONSTRAINT `communaute_reactions_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `communaute_posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `communaute_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `dm_attachments`
--
ALTER TABLE `dm_attachments`
  ADD CONSTRAINT `dm_attachments_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `dm_messages` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `dm_conversations`
--
ALTER TABLE `dm_conversations`
  ADD CONSTRAINT `dm_conversations_ibfk_1` FOREIGN KEY (`user_a_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dm_conversations_ibfk_2` FOREIGN KEY (`user_b_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `dm_messages`
--
ALTER TABLE `dm_messages`
  ADD CONSTRAINT `dm_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `dm_conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dm_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `gallery_albums`
--
ALTER TABLE `gallery_albums`
  ADD CONSTRAINT `gallery_albums_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `gallery_categories` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `gallery_photos`
--
ALTER TABLE `gallery_photos`
  ADD CONSTRAINT `gallery_photos_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `gallery_albums` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groupes_classe`
--
ALTER TABLE `groupes_classe`
  ADD CONSTRAINT `groupes_classe_ibfk_1` FOREIGN KEY (`enseignant_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groupes_utilisateurs`
--
ALTER TABLE `groupes_utilisateurs`
  ADD CONSTRAINT `groupes_utilisateurs_ibfk_1` FOREIGN KEY (`createur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groupe_annonces`
--
ALTER TABLE `groupe_annonces`
  ADD CONSTRAINT `groupe_annonces_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `groupes_classe` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `groupe_annonces_ibfk_2` FOREIGN KEY (`enseignant_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groupe_membres`
--
ALTER TABLE `groupe_membres`
  ADD CONSTRAINT `groupe_membres_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `groupes_classe` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `groupe_membres_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groupe_messages`
--
ALTER TABLE `groupe_messages`
  ADD CONSTRAINT `groupe_messages_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `groupes_classe` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `groupe_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groupe_message_attachments`
--
ALTER TABLE `groupe_message_attachments`
  ADD CONSTRAINT `groupe_message_attachments_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `groupe_messages` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groupe_message_hides`
--
ALTER TABLE `groupe_message_hides`
  ADD CONSTRAINT `groupe_message_hides_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `groupe_messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `groupe_message_hides_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groupe_presence_marks`
--
ALTER TABLE `groupe_presence_marks`
  ADD CONSTRAINT `groupe_presence_marks_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `groupe_presence_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `groupe_presence_marks_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groupe_presence_sessions`
--
ALTER TABLE `groupe_presence_sessions`
  ADD CONSTRAINT `groupe_presence_sessions_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `groupes_classe` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `groupe_presence_sessions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groupe_utilisateurs_attachments`
--
ALTER TABLE `groupe_utilisateurs_attachments`
  ADD CONSTRAINT `groupe_utilisateurs_attachments_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `groupe_utilisateurs_messages` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groupe_utilisateurs_membres`
--
ALTER TABLE `groupe_utilisateurs_membres`
  ADD CONSTRAINT `groupe_utilisateurs_membres_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `groupes_utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `groupe_utilisateurs_membres_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `groupe_utilisateurs_messages`
--
ALTER TABLE `groupe_utilisateurs_messages`
  ADD CONSTRAINT `groupe_utilisateurs_messages_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `groupes_utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `groupe_utilisateurs_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messagerie_attachments`
--
ALTER TABLE `messagerie_attachments`
  ADD CONSTRAINT `fk_msg_attach` FOREIGN KEY (`message_id`) REFERENCES `messagerie_messages` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messagerie_message_hides`
--
ALTER TABLE `messagerie_message_hides`
  ADD CONSTRAINT `fk_hide_message` FOREIGN KEY (`message_id`) REFERENCES `messagerie_messages` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `news_articles`
--
ALTER TABLE `news_articles`
  ADD CONSTRAINT `news_articles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `news_categories` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `news_attachments`
--
ALTER TABLE `news_attachments`
  ADD CONSTRAINT `news_attachments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `news_articles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
