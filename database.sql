-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: isstm_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `isstm_db`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `isstm_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `isstm_db`;

--
-- Table structure for table `admin_notes`
--

DROP TABLE IF EXISTS `admin_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin_notes` (
  `user_id` int(11) NOT NULL,
  `contenu` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  CONSTRAINT `admin_notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `amis_demandes`
--

DROP TABLE IF EXISTS `amis_demandes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `amis_demandes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `demandeur_id` int(11) NOT NULL,
  `destinataire_id` int(11) NOT NULL,
  `statut` enum('en_attente','acceptee','refusee') NOT NULL DEFAULT 'en_attente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paire` (`demandeur_id`,`destinataire_id`),
  KEY `destinataire_id` (`destinataire_id`),
  CONSTRAINT `amis_demandes_ibfk_1` FOREIGN KEY (`demandeur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `amis_demandes_ibfk_2` FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `campus_blocs`
--

DROP TABLE IF EXISTS `campus_blocs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campus_blocs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `bloc_key` (`bloc_key`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `communaute_comments`
--

DROP TABLE IF EXISTS `communaute_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `communaute_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `parent_id` (`parent_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `communaute_comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `communaute_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `communaute_comments_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `communaute_comments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `communaute_comments_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `communaute_notifications`
--

DROP TABLE IF EXISTS `communaute_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `communaute_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('nouvelle_publication','reponse_commentaire') NOT NULL,
  `post_id` int(11) NOT NULL,
  `comment_id` int(11) DEFAULT NULL,
  `actor_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `post_id` (`post_id`),
  CONSTRAINT `communaute_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `communaute_notifications_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `communaute_posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `communaute_post_media`
--

DROP TABLE IF EXISTS `communaute_post_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `communaute_post_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `media_path` varchar(255) NOT NULL,
  `media_type` enum('image','video','pdf') NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  CONSTRAINT `communaute_post_media_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `communaute_posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `communaute_posts`
--

DROP TABLE IF EXISTS `communaute_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `communaute_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auteur_id` int(11) NOT NULL,
  `type` enum('actualite','resultat','emploi_du_temps','examen','media','autre') NOT NULL DEFAULT 'autre',
  `contenu` text DEFAULT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `media_type` enum('image','video','pdf','none') NOT NULL DEFAULT 'none',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `auteur_id` (`auteur_id`),
  CONSTRAINT `communaute_posts_ibfk_1` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `communaute_reactions`
--

DROP TABLE IF EXISTS `communaute_reactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `communaute_reactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('like','love') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_user` (`post_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `communaute_reactions_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `communaute_posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `communaute_reactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dm_attachments`
--

DROP TABLE IF EXISTS `dm_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dm_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(20) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`),
  CONSTRAINT `dm_attachments_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `dm_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dm_conversations`
--

DROP TABLE IF EXISTS `dm_conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dm_conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_a_id` int(11) NOT NULL,
  `user_b_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `paire` (`user_a_id`,`user_b_id`),
  KEY `user_b_id` (`user_b_id`),
  CONSTRAINT `dm_conversations_ibfk_1` FOREIGN KEY (`user_a_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dm_conversations_ibfk_2` FOREIGN KEY (`user_b_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dm_message_hides`
--

DROP TABLE IF EXISTS `dm_message_hides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dm_message_hides` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`message_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dm_messages`
--

DROP TABLE IF EXISTS `dm_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dm_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL,
  `deleted_for_everyone_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conversation_id` (`conversation_id`),
  KEY `sender_id` (`sender_id`),
  CONSTRAINT `dm_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `dm_conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `dm_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `category` varchar(20) NOT NULL DEFAULT 'public',
  `file_path` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `evenements`
--

DROP TABLE IF EXISTS `evenements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `evenements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `filiere_blocks`
--

DROP TABLE IF EXISTS `filiere_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filiere_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_filiere` (`filiere_id`)
) ENGINE=InnoDB AUTO_INCREMENT=363 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `filieres`
--

DROP TABLE IF EXISTS `filieres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filieres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `avantages_mg` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gallery_albums`
--

DROP TABLE IF EXISTS `gallery_albums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gallery_albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `gallery_albums_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `gallery_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gallery_categories`
--

DROP TABLE IF EXISTS `gallery_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gallery_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) NOT NULL,
  `name_fr` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_mg` varchar(100) NOT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'fa-images',
  `display_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `gallery_photos`
--

DROP TABLE IF EXISTS `gallery_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gallery_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `album_id` (`album_id`),
  CONSTRAINT `gallery_photos_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `gallery_albums` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=164 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupe_annonces`
--

DROP TABLE IF EXISTS `groupe_annonces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupe_annonces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupe_id` int(11) NOT NULL,
  `enseignant_id` int(11) NOT NULL,
  `type` enum('examen','resultat','devoir','autre') NOT NULL DEFAULT 'autre',
  `titre` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `date_echeance` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `groupe_id` (`groupe_id`),
  KEY `enseignant_id` (`enseignant_id`),
  CONSTRAINT `groupe_annonces_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `groupes_classe` (`id`) ON DELETE CASCADE,
  CONSTRAINT `groupe_annonces_ibfk_2` FOREIGN KEY (`enseignant_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupe_membres`
--

DROP TABLE IF EXISTS `groupe_membres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupe_membres` (
  `groupe_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role_in_group` enum('enseignant','etudiant') NOT NULL DEFAULT 'etudiant',
  `is_banned` tinyint(1) NOT NULL DEFAULT 0,
  `joined_at` datetime DEFAULT current_timestamp(),
  `last_read_at` datetime DEFAULT NULL,
  `is_delegate` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`groupe_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `groupe_membres_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `groupes_classe` (`id`) ON DELETE CASCADE,
  CONSTRAINT `groupe_membres_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupe_message_attachments`
--

DROP TABLE IF EXISTS `groupe_message_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupe_message_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_type` enum('image','video','file') NOT NULL,
  `mime_type` varchar(150) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`),
  CONSTRAINT `groupe_message_attachments_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `groupe_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupe_message_hides`
--

DROP TABLE IF EXISTS `groupe_message_hides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupe_message_hides` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`message_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `groupe_message_hides_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `groupe_messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `groupe_message_hides_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupe_messages`
--

DROP TABLE IF EXISTS `groupe_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupe_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupe_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `deleted_for_everyone_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `groupe_id` (`groupe_id`),
  KEY `sender_id` (`sender_id`),
  CONSTRAINT `groupe_messages_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `groupes_classe` (`id`) ON DELETE CASCADE,
  CONSTRAINT `groupe_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupe_presence_marks`
--

DROP TABLE IF EXISTS `groupe_presence_marks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupe_presence_marks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('present','absent') NOT NULL DEFAULT 'absent',
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_user` (`session_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `groupe_presence_marks_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `groupe_presence_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `groupe_presence_marks_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupe_presence_sessions`
--

DROP TABLE IF EXISTS `groupe_presence_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupe_presence_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupe_id` int(11) NOT NULL,
  `session_date` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupe_date` (`groupe_id`,`session_date`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `groupe_presence_sessions_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `groupes_classe` (`id`) ON DELETE CASCADE,
  CONSTRAINT `groupe_presence_sessions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupe_utilisateurs_attachments`
--

DROP TABLE IF EXISTS `groupe_utilisateurs_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupe_utilisateurs_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `file_type` varchar(20) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`),
  CONSTRAINT `groupe_utilisateurs_attachments_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `groupe_utilisateurs_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupe_utilisateurs_hides`
--

DROP TABLE IF EXISTS `groupe_utilisateurs_hides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupe_utilisateurs_hides` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`message_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupe_utilisateurs_membres`
--

DROP TABLE IF EXISTS `groupe_utilisateurs_membres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupe_utilisateurs_membres` (
  `groupe_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_banned` tinyint(1) NOT NULL DEFAULT 0,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_read_at` datetime DEFAULT NULL,
  PRIMARY KEY (`groupe_id`,`user_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `groupe_utilisateurs_membres_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `groupes_utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `groupe_utilisateurs_membres_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupe_utilisateurs_messages`
--

DROP TABLE IF EXISTS `groupe_utilisateurs_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupe_utilisateurs_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupe_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_for_everyone_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `groupe_id` (`groupe_id`),
  KEY `sender_id` (`sender_id`),
  CONSTRAINT `groupe_utilisateurs_messages_ibfk_1` FOREIGN KEY (`groupe_id`) REFERENCES `groupes_utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `groupe_utilisateurs_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupes_classe`
--

DROP TABLE IF EXISTS `groupes_classe`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupes_classe` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(150) NOT NULL,
  `type` enum('classe','enseignants') NOT NULL DEFAULT 'classe',
  `annee` varchar(20) DEFAULT NULL,
  `filiere_id` int(11) DEFAULT NULL,
  `niveau` varchar(10) DEFAULT NULL,
  `code_unique` varchar(10) NOT NULL,
  `enseignant_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_unique` (`code_unique`),
  KEY `enseignant_id` (`enseignant_id`),
  CONSTRAINT `groupes_classe_ibfk_1` FOREIGN KEY (`enseignant_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groupes_utilisateurs`
--

DROP TABLE IF EXISTS `groupes_utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groupes_utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `createur_id` int(11) NOT NULL,
  `code_unique` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_unique` (`code_unique`),
  KEY `createur_id` (`createur_id`),
  CONSTRAINT `groupes_utilisateurs_ibfk_1` FOREIGN KEY (`createur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `hero_slides`
--

DROP TABLE IF EXISTS `hero_slides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hero_slides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) NOT NULL,
  `media_type` enum('image','video') NOT NULL DEFAULT 'image',
  `display_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messagerie_attachments`
--

DROP TABLE IF EXISTS `messagerie_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messagerie_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_type` enum('image','video','file') NOT NULL,
  `mime_type` varchar(150) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_msg_attach` (`message_id`),
  CONSTRAINT `fk_msg_attach` FOREIGN KEY (`message_id`) REFERENCES `messagerie_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messagerie_message_hides`
--

DROP TABLE IF EXISTS `messagerie_message_hides`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messagerie_message_hides` (
  `message_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`message_id`,`user_id`),
  CONSTRAINT `fk_hide_message` FOREIGN KEY (`message_id`) REFERENCES `messagerie_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messagerie_messages`
--

DROP TABLE IF EXISTS `messagerie_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messagerie_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL,
  `deleted_for_everyone_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sender` (`sender_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news_articles`
--

DROP TABLE IF EXISTS `news_articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `news_articles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `news_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news_attachments`
--

DROP TABLE IF EXISTS `news_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`),
  CONSTRAINT `news_attachments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `news_articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news_categories`
--

DROP TABLE IF EXISTS `news_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) NOT NULL,
  `name_fr` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `name_mg` varchar(100) NOT NULL,
  `icon` varchar(50) NOT NULL DEFAULT 'fa-newspaper',
  `display_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news_photos`
--

DROP TABLE IF EXISTS `news_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL DEFAULT '',
  `media_type` enum('photo','video') NOT NULL DEFAULT 'photo',
  `video_url` varchar(500) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_article` (`article_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `newsletter_campaigns`
--

DROP TABLE IF EXISTS `newsletter_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletter_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('programme','envoye') NOT NULL DEFAULT 'programme',
  `scheduled_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `recipients_count` int(11) NOT NULL DEFAULT 0,
  `sent_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `newsletter_subscribers`
--

DROP TABLE IF EXISTS `newsletter_subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(190) NOT NULL,
  `subscribed_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `org_people`
--

DROP TABLE IF EXISTS `org_people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `org_people` (
  `title_key` varchar(64) NOT NULL,
  `name` varchar(150) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`title_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `page_views`
--

DROP TABLE IF EXISTS `page_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page` varchar(100) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=803 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `partenaires`
--

DROP TABLE IF EXISTS `partenaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partenaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `logo_path` varchar(255) NOT NULL,
  `site_url` varchar(500) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `preinscription_cta_media`
--

DROP TABLE IF EXISTS `preinscription_cta_media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preinscription_cta_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_type` enum('image','video') NOT NULL,
  `media_path` varchar(255) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `preinscriptions`
--

DROP TABLE IF EXISTS `preinscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `preinscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `security_log`
--

DROP TABLE IF EXISTS `security_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `security_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` varchar(50) NOT NULL,
  `identifiant` varchar(150) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `site_banners`
--

DROP TABLE IF EXISTS `site_banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_key` varchar(60) NOT NULL DEFAULT '',
  `media_type` enum('image','video') NOT NULL DEFAULT 'image',
  `media_path` varchar(500) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_page_key` (`page_key`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `site_content`
--

DROP TABLE IF EXISTS `site_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content_key` varchar(100) NOT NULL,
  `content_value_fr` text NOT NULL,
  `content_value_en` text NOT NULL,
  `content_value_mg` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `content_key` (`content_key`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `site_stats`
--

DROP TABLE IF EXISTS `site_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_stats` (
  `stat_key` varchar(50) NOT NULL,
  `stat_value` bigint(20) DEFAULT 0,
  PRIMARY KEY (`stat_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `teachers`
--

DROP TABLE IF EXISTS `teachers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teachers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `display_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_name` varchar(255) NOT NULL,
  `program` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `quote_fr` text NOT NULL,
  `quote_en` text NOT NULL,
  `quote_mg` text NOT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `site_web` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'isstm_db'
--

--
-- Current Database: `bibliotheque`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `bibliotheque` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `bibliotheque`;

--
-- Table structure for table `actualite_images`
--

DROP TABLE IF EXISTS `actualite_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actualite_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actualite_id` int(11) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_ajout` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `actualite_id` (`actualite_id`),
  CONSTRAINT `actualite_images_ibfk_1` FOREIGN KEY (`actualite_id`) REFERENCES `actualites_horaire` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `actualite_photos`
--

DROP TABLE IF EXISTS `actualite_photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actualite_photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `actualite_id` int(11) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_ajout` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `actualite_id` (`actualite_id`),
  CONSTRAINT `actualite_photos_ibfk_1` FOREIGN KEY (`actualite_id`) REFERENCES `actualites_horaire` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `actualites_horaire`
--

DROP TABLE IF EXISTS `actualites_horaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `actualites_horaire` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` varchar(255) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date DEFAULT NULL,
  `date_publication` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `role` enum('admin','super_admin') NOT NULL DEFAULT 'admin',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `annees_universitaires`
--

DROP TABLE IF EXISTS `annees_universitaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `annees_universitaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `libelle` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `libelle` (`libelle`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bannieres`
--

DROP TABLE IF EXISTS `bannieres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bannieres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chemin_image` varchar(255) NOT NULL,
  `legende` varchar(200) DEFAULT NULL,
  `ordre` int(11) DEFAULT 0,
  `date_ajout` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `canevas`
--

DROP TABLE IF EXISTS `canevas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `canevas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) NOT NULL,
  `niveau` enum('Licence','Master') NOT NULL,
  `annee_id` int(11) NOT NULL,
  `type_fichier` enum('word','pdf','pptx') NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_ajout` datetime DEFAULT current_timestamp(),
  `contenu_texte` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `annee_id` (`annee_id`),
  KEY `idx_canevas_niveau` (`niveau`),
  CONSTRAINT `canevas_ibfk_1` FOREIGN KEY (`annee_id`) REFERENCES `annees_universitaires` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `carousel_images`
--

DROP TABLE IF EXISTS `carousel_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carousel_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chemin_fichier` varchar(255) NOT NULL,
  `legende` varchar(200) DEFAULT NULL,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `date_ajout` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `filieres`
--

DROP TABLE IF EXISTS `filieres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filieres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(150) NOT NULL,
  `abreviation` varchar(20) NOT NULL,
  `niveau` enum('Licence','Master') NOT NULL,
  `mention_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_nom_niveau_mention` (`nom`,`niveau`,`mention_id`),
  KEY `mention_id` (`mention_id`),
  CONSTRAINT `filieres_ibfk_1` FOREIGN KEY (`mention_id`) REFERENCES `mentions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `horaires`
--

DROP TABLE IF EXISTS `horaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `horaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jour` enum('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche') NOT NULL,
  `heure_ouverture` time NOT NULL,
  `heure_fermeture` time NOT NULL,
  `actif` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jour` (`jour`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `memoires`
--

DROP TABLE IF EXISTS `memoires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `memoires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(200) NOT NULL,
  `auteur` varchar(150) NOT NULL,
  `encadreur` varchar(150) DEFAULT NULL,
  `categorie` enum('M├®moire','Projet') NOT NULL DEFAULT 'M├®moire',
  `filiere_id` int(11) NOT NULL,
  `annee_id` int(11) NOT NULL,
  `resume` text DEFAULT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_ajout` datetime DEFAULT current_timestamp(),
  `contenu_texte` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `filiere_id` (`filiere_id`),
  KEY `annee_id` (`annee_id`),
  KEY `idx_memoires_categorie` (`categorie`),
  CONSTRAINT `memoires_ibfk_1` FOREIGN KEY (`filiere_id`) REFERENCES `filieres` (`id`) ON DELETE CASCADE,
  CONSTRAINT `memoires_ibfk_2` FOREIGN KEY (`annee_id`) REFERENCES `annees_universitaires` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mentions`
--

DROP TABLE IF EXISTS `mentions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mentions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(200) NOT NULL,
  `abreviation` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'bibliotheque'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-04 12:26:56
