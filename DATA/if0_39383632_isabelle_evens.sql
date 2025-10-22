-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Hôte : sql313.infinityfree.com
-- Généré le :  mer. 22 oct. 2025 à 10:28
-- Version du serveur :  11.4.7-MariaDB
-- Version de PHP :  7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `if0_39383632_isabelle_evens`
--

-- --------------------------------------------------------

--
-- Structure de la table `conversations_messages`
--

CREATE TABLE `conversations_messages` (
  `id` int(11) UNSIGNED NOT NULL,
  `expediteur_id` int(11) UNSIGNED NOT NULL COMMENT 'ID de l''utilisateur qui envoie le message',
  `destinataire_id` int(11) UNSIGNED NOT NULL COMMENT 'ID de l''utilisateur qui reçoit le message',
  `sujet` varchar(255) DEFAULT NULL,
  `contenu` text NOT NULL,
  `date_envoi` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut_expediteur` enum('envoye','archive') NOT NULL DEFAULT 'envoye',
  `statut_destinataire` enum('non_lu','lu','archive') NOT NULL DEFAULT 'non_lu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `conversations_messages`
--

INSERT INTO `conversations_messages` (`id`, `expediteur_id`, `destinataire_id`, `sujet`, `contenu`, `date_envoi`, `statut_expediteur`, `statut_destinataire`) VALUES
(12, 9, 9, 'Conversation client/admin', 'hsdbcjhsbhhch', '2025-07-23 02:36:59', 'envoye', 'non_lu'),
(14, 9, 9, 'Conversation client/admin', 'je suis pas sur de comprendre', '2025-07-23 02:37:34', 'envoye', 'non_lu');

-- --------------------------------------------------------

--
-- Structure de la table `decorations`
--

CREATE TABLE `decorations` (
  `id` int(11) UNSIGNED NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL COMMENT 'Chemin vers le fichier image',
  `style` text NOT NULL,
  `date_ajout` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `decorations`
--

INSERT INTO `decorations` (`id`, `titre`, `description`, `image_url`, `style`, `date_ajout`) VALUES
(7, 'Decoration', 'âœ¨ Isabelle Eventâ€™s â€“ Lâ€™Art dâ€™Illuminer Vos Noces avec Ã‰lÃ©gance âœ¨  \r\n\r\nChez Isabelle Eventâ€™s, nous transformons vos rÃªves de mariage en une rÃ©alitÃ© Ã©tincelante. Notre Ã©quipe passionnÃ©e allie crÃ©ativitÃ© et expertise pour concevoir des dÃ©cors uniques, oÃ¹ chaque dÃ©tail raconte votre histoire dâ€™amour.', '68859e522f278_deco mariage .jpg', 'classique', '2025-07-23 18:37:09'),
(8, 'mariage coutumier', 'chaises tables housses structure pagnes tapis, chaises traditionnelle', '6894369e48fcf_1001158356.jpg', 'traditionnel', '2025-08-07 05:16:14');

-- --------------------------------------------------------

--
-- Structure de la table `demandes_devis`
--

CREATE TABLE `demandes_devis` (
  `id` int(11) UNSIGNED NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `date_soumission` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `invitations_evenements`
--

CREATE TABLE `invitations_evenements` (
  `id` int(11) UNSIGNED NOT NULL,
  `id_client` int(11) UNSIGNED DEFAULT NULL,
  `noms_maries` varchar(255) NOT NULL,
  `date_mariage` date NOT NULL,
  `heure_ceremonie` time NOT NULL,
  `lieu_ceremonie` varchar(255) NOT NULL,
  `heure_reception` time DEFAULT NULL,
  `lieu_reception` varchar(255) DEFAULT NULL,
  `message_perso` text DEFAULT NULL,
  `rsvp_contact` varchar(255) DEFAULT NULL,
  `fond_invitation_url` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `invitations_evenements`
--

INSERT INTO `invitations_evenements` (`id`, `id_client`, `noms_maries`, `date_mariage`, `heure_ceremonie`, `lieu_ceremonie`, `heure_reception`, `lieu_reception`, `message_perso`, `rsvp_contact`, `fond_invitation_url`, `date_creation`) VALUES
(3, NULL, 'Chris Ngombabu & Elle', '2025-08-30', '20:00:00', 'n\'importe ou tant que c\'est pas chers', '19:30:00', 'n\'importe ou', 'biento mon mariage sera consacre dans mes reve', 'chrisngombabu@gmail.com', 'fond_6885c27db138f1.50798843.jpg', '2025-07-23 19:25:51');

-- --------------------------------------------------------

--
-- Structure de la table `invites`
--

CREATE TABLE `invites` (
  `id` int(11) UNSIGNED NOT NULL,
  `id_invitation_evenement` int(11) UNSIGNED NOT NULL,
  `nom_invite` varchar(255) DEFAULT NULL COMMENT 'Nom de l''invité principal pour cette carte',
  `table_assignee` varchar(100) DEFAULT NULL,
  `nombre_personnes` int(11) NOT NULL DEFAULT 1,
  `qr_code_data` text DEFAULT NULL COMMENT 'Données encodées dans le QR code, souvent en JSON',
  `statut_rsvp` enum('en_attente','confirme','refuse') NOT NULL DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) UNSIGNED NOT NULL,
  `nom_expediteur` varchar(255) NOT NULL,
  `email_expediteur` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `statut` enum('non_lu','lu','archive') NOT NULL DEFAULT 'non_lu',
  `date_reception` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `realisations`
--

CREATE TABLE `realisations` (
  `id` int(11) UNSIGNED NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `date_realisation` date DEFAULT NULL,
  `date_publication` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `realisations`
--

INSERT INTO `realisations` (`id`, `titre`, `description`, `image_url`, `date_realisation`, `date_publication`) VALUES
(4, 'Coiffure & Maquillage pour le mariage de Elle & Chris Ngombabu', 'Domaine des Roses - Juin 2024\r\n\r\nLook crÃ©Ã© :\r\n\r\nCoiffure : Chignon bas dÃ©structurÃ© avec mÃ¨ches ondulÃ©es et accessoires perles\r\n\r\nMaquillage : Teint lumineux, smokey eyes nuancÃ© et lÃ¨vres nude rosÃ©\r\n\r\nProduits utilisÃ©s :\r\nðŸ”¸ Base longue tenue EstÃ©e Lauder\r\nðŸ”¸ Palette Charlotte Tilbury pour les yeux\r\nðŸ”¸ Laque L\'OrÃ©al Professionnel pour une fixation lÃ©gÃ¨re et naturelle\r\n\r\nTÃ©moignage du client elle :\r\nJe me suis sentie magnifique et moi-mÃªme ! Mon maquillage a tenu toute la journÃ©e et mes cheveux sont restÃ©s parfaits jusqu\'Ã  la derniÃ¨re danse. Merci !\"\r\n\r\nâž¡ï¸ Un look sur-mesure pour vous ? Parlons-en !', 'realisation_688911022312e5.74074911.jpg', '2024-01-18', '2025-07-23 18:35:37');

-- --------------------------------------------------------

--
-- Structure de la table `robes`
--

CREATE TABLE `robes` (
  `id` int(11) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `prix_location` decimal(10,2) DEFAULT NULL,
  `prix_vente` decimal(10,2) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `disponible` tinyint(1) NOT NULL DEFAULT 1,
  `date_ajout` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `robes`
--

INSERT INTO `robes` (`id`, `nom`, `description`, `prix_location`, `prix_vente`, `image_url`, `disponible`, `date_ajout`) VALUES
(7, 'Luna', 'âœ¨ Trouvez la Robe de MariÃ©e de Vos RÃªves chez Isabelle Eventâ€™s âœ¨ \r\n\r\nChez Isabelle Eventâ€™s, nous savons que votre robe de mariÃ©e est bien plus quâ€™une tenue â€“ câ€™est lâ€™incarnation de votre fÃ©minitÃ©, de votre style et de votre histoire dâ€™amour. Que vous recherchiez une robe romantique en dentelle, une silhouette moderne et Ã©purÃ©e, ou une crÃ©ation luxueuse et scintillante, notre collection exclusive allie Ã©lÃ©gance et qualitÃ©.  \r\n\r\nOptez pour la location dâ€™une robe haut de gamme Ã  prix doux, ou offrez-vous lâ€™achat dâ€™une piÃ¨ce unique, taillÃ©e pour vous. Nos conseillÃ¨res expertes vous guident avec bienveillance pour trouver LA robe qui vous fera briller le jour J.  \r\n\r\nParce que chaque future mariÃ©e mÃ©rite de se sentir sublimeâ€¦ ðŸŒ¸ðŸ’  \r\n\r\n(PrÃªte Ã  trouver votre robe idÃ©ale ? Contactez Isabelle Eventâ€™s dÃ¨s maintenant !)\r\n\r\nPourquoi nous choisir ?\r\nâœ… Robes de crÃ©ateurs & piÃ¨ces exclusives  \r\nâœ… Location flexible ou achat sur mesure  \r\nâœ… Essayage personnalisÃ© dans un cadre intimiste  \r\nâœ… Service sur-mesure pour un shopping sans stress', '100.00', NULL, 'robe_6885b3ea22aee0.82348841.jpg', 0, '2025-07-23 18:39:31');

-- --------------------------------------------------------

--
-- Structure de la table `services_beaute`
--

CREATE TABLE `services_beaute` (
  `id` int(11) UNSIGNED NOT NULL,
  `nom` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` varchar(100) DEFAULT NULL COMMENT 'Peut contenir du texte comme "sur devis"',
  `image_url` varchar(255) DEFAULT NULL,
  `date_ajout` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `services_beaute`
--

INSERT INTO `services_beaute` (`id`, `nom`, `description`, `prix`, `image_url`, `date_ajout`) VALUES
(6, 'coiffure & makup', 'ðŸ’‡â€â™€ï¸âœ¨ Coiffure & Maquillage de Mariage - Sublimez Votre BeautÃ© ! âœ¨ðŸ’„ \r\n\r\nChez Isabelle Eventâ€™s, nos experts crÃ©ent le look parfait pour votre grand jour : chignons Ã©lÃ©gants, boucles romantiques ou maquillage naturel/glamour. Essai possible avant le mariage.  \r\n\r\nVous mÃ©ritez d\'Ãªtre rayonnante ! ðŸŒŸ (Demandez votre devis !)  \r\n\r\nVersion ultra-concise mais gardant l\'essentiel : professionnalisme, services clÃ©s et call-to-action.', '10', 'service_6885b8f14934b2.00184900.jpg', '2025-07-27 05:28:17'),
(7, 'pose perruque, coiffure & make-up', 'customisation, pose pro, coiffure mariÃ©e et make-up nude', '120$', 'service_688910e8cfbbd4.36824595.jpg', '2025-07-29 17:54:00');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) UNSIGNED NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` text NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL COMMENT 'Doit être stocké haché (ex: password_hash)',
  `role` enum('client','admin','partenaire') NOT NULL DEFAULT 'client',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom_complet`, `email`, `telephone`, `mot_de_passe`, `role`, `date_creation`) VALUES
(9, 'Chris Ngombabu', 'chrisngombabu2@gmail.com', '+243974437984', '$2y$10$odOmkAlq0py/QoAGFK7bVepefjsPnLsjw/iTa.cW011aYkmEbjrZa', 'admin', '2025-07-23 01:36:59'),
(12, 'Isabelle Mundadi', 'ntumbanaturel@gmail.com', '+243828143023', '$2y$10$zTUdqXTRu.MBXBkE4U1tZ.xU83grsN1CLCf0Ob84MVxPG6eULy3Oa', 'admin', '2025-07-29 12:05:53');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `conversations_messages`
--
ALTER TABLE `conversations_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expediteur_id` (`expediteur_id`),
  ADD KEY `destinataire_id` (`destinataire_id`);

--
-- Index pour la table `decorations`
--
ALTER TABLE `decorations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `demandes_devis`
--
ALTER TABLE `demandes_devis`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `invitations_evenements`
--
ALTER TABLE `invitations_evenements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_client` (`id_client`);

--
-- Index pour la table `invites`
--
ALTER TABLE `invites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_invitation_evenement` (`id_invitation_evenement`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `realisations`
--
ALTER TABLE `realisations`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `robes`
--
ALTER TABLE `robes`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `services_beaute`
--
ALTER TABLE `services_beaute`
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
-- AUTO_INCREMENT pour la table `conversations_messages`
--
ALTER TABLE `conversations_messages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `decorations`
--
ALTER TABLE `decorations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `demandes_devis`
--
ALTER TABLE `demandes_devis`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `invitations_evenements`
--
ALTER TABLE `invitations_evenements`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `invites`
--
ALTER TABLE `invites`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `realisations`
--
ALTER TABLE `realisations`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `robes`
--
ALTER TABLE `robes`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `services_beaute`
--
ALTER TABLE `services_beaute`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `conversations_messages`
--
ALTER TABLE `conversations_messages`
  ADD CONSTRAINT `fk_msg_destinataire` FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_msg_expediteur` FOREIGN KEY (`expediteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `invitations_evenements`
--
ALTER TABLE `invitations_evenements`
  ADD CONSTRAINT `fk_invitation_client` FOREIGN KEY (`id_client`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `invites`
--
ALTER TABLE `invites`
  ADD CONSTRAINT `fk_invite_evenement` FOREIGN KEY (`id_invitation_evenement`) REFERENCES `invitations_evenements` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
