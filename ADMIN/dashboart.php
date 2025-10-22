<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../INCLUDE/data.php'); // Assurez-vous que ce fichier contient la connexion à la base de données ($conn)

// --- Sécurité : Vérifier si l'utilisateur est connecté et a le rôle admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../PUBLIC/login.php'); // Rediriger vers la page de connexion si non connecté ou non admin
    exit();
}

$base_logo = '../RESOURCE/SITE_IMAGE/logo.jpeg';
$message_global = ''; // Pour les messages de succès/erreur généraux (suppressions, etc.)

// --- Gestion des actions de suppression (factorisée pour la clarté) ---
$delete_actions = [
    'delete_user' => ['table' => 'utilisateurs', 'confirm_msg' => 'Êtes-vous sûr de vouloir supprimer cet utilisateur ?', 'success_msg' => 'Utilisateur supprimé avec succès.', 'error_msg' => 'Erreur lors de la suppression de l\'utilisateur'],
    'delete_devis' => ['table' => 'demandes_devis', 'confirm_msg' => 'Êtes-vous sûr de vouloir supprimer cette demande de devis ?', 'success_msg' => 'Demande de devis supprimée avec succès.', 'error_msg' => 'Erreur lors de la suppression de la demande de devis'],
    'delete_decoration' => ['table' => 'decorations', 'confirm_msg' => 'Êtes-vous sûr de vouloir supprimer cette décoration ?', 'success_msg' => 'Décoration supprimée avec succès.', 'error_msg' => 'Erreur lors de la suppression de la décoration'],
    'delete_robe' => ['table' => 'robes', 'confirm_msg' => 'Êtes-vous sûr de vouloir supprimer cette robe ?', 'success_msg' => 'Robe supprimée avec succès.', 'error_msg' => 'Erreur lors de la suppression de la robe'],
    'delete_service_beaute' => ['table' => 'services_beaute', 'confirm_msg' => 'Êtes-vous sûr de vouloir supprimer ce service beauté ?', 'success_msg' => 'Service beauté supprimé avec succès.', 'error_msg' => 'Erreur lors de la suppression du service beauté'],
    'delete_realisation' => ['table' => 'realisations', 'confirm_msg' => 'Êtes-vous sûr de vouloir supprimer cette réalisation ?', 'success_msg' => 'Réalisation supprimée avec succès.', 'error_msg' => 'Erreur lors de la suppression de la réalisation'],
    'delete_event_invitation' => ['table' => 'invitations_evenements', 'confirm_msg' => 'Êtes-vous sûr de vouloir supprimer cet événement et TOUS ses invités associés ?', 'success_msg' => 'Événement et ses invités supprimés avec succès.', 'error_msg' => 'Erreur lors de la suppression de l\'événement'],
    'delete_invite' => ['table' => 'invites', 'confirm_msg' => 'Êtes-vous sûr de vouloir supprimer cet invité ?', 'success_msg' => 'Invité supprimé avec succès.', 'error_msg' => 'Erreur lors de la suppression de l\'invité'],
    'delete_message' => ['table' => 'messages', 'confirm_msg' => 'Êtes-vous sûr de vouloir supprimer ce message de contact ?', 'success_msg' => 'Message de contact supprimé avec succès.', 'error_msg' => 'Erreur lors de la suppression du message de contact']
];

foreach ($delete_actions as $action_name => $details) {
    if (isset($_GET['action']) && $_GET['action'] == $action_name && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        if ($action_name === 'delete_user' && $id === $_SESSION['user_id']) {
            $message_global = '<div class="message message-erreur"><i class="fas fa-exclamation-circle"></i> Vous ne pouvez pas supprimer votre propre compte administrateur.</div>';
        } else {
            // Pour 'delete_event_invitation', il faut aussi supprimer les invités liés
            if ($action_name === 'delete_event_invitation') {
                $stmt_delete_invites = $conn->prepare("DELETE FROM invites WHERE id_invitation_evenement = ?");
                $stmt_delete_invites->bind_param("i", $id);
                $stmt_delete_invites->execute();
                $stmt_delete_invites->close();
            }

            $stmt = $conn->prepare("DELETE FROM " . $details['table'] . " WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message_global = '<div class="message message-succes"><i class="fas fa-check-circle"></i> ' . $details['success_msg'] . '</div>';
            } else {
                $message_global = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> ' . $details['error_msg'] . ' : ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
    }
}

// Gestion des messages de contact (marquer comme lu)
if (isset($_GET['action']) && $_GET['action'] == 'mark_read_message' && isset($_GET['id'])) {
    $message_id = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE messages SET statut = 'lu' WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    if ($stmt->execute()) {
        $message_global = '<div class="message message-succes"><i class="fas fa-check-circle"></i> Message marqué comme lu.</div>';
    } else {
        $message_global = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors de la mise à jour du message : ' . $stmt->error . '</div>';
    }
    $stmt->close();
}


// --- Récupération des données pour l'aperçu du dashboard ---
$total_users = $conn->query("SELECT COUNT(*) AS total FROM utilisateurs")->fetch_assoc()['total'] ?? 0;
$total_devis = $conn->query("SELECT COUNT(*) AS total FROM demandes_devis")->fetch_assoc()['total'] ?? 0;
$total_decorations = $conn->query("SELECT COUNT(*) AS total FROM decorations")->fetch_assoc()['total'] ?? 0;
$total_robes = $conn->query("SELECT COUNT(*) AS total FROM robes")->fetch_assoc()['total'] ?? 0;
$total_services_beaute = $conn->query("SELECT COUNT(*) AS total FROM services_beaute")->fetch_assoc()['total'] ?? 0;
$total_realisations = $conn->query("SELECT COUNT(*) AS total FROM realisations")->fetch_assoc()['total'] ?? 0;
$total_invitations = $conn->query("SELECT COUNT(*) AS total FROM invitations_evenements")->fetch_assoc()['total'] ?? 0;
$total_invites = $conn->query("SELECT COUNT(*) AS total FROM invites")->fetch_assoc()['total'] ?? 0; // Ajout du total des invités
$total_messages_non_lus_contact = $conn->query("SELECT COUNT(*) AS total FROM messages WHERE statut = 'non_lu'")->fetch_assoc()['total'] ?? 0;
// Compter le nombre de conversations avec des messages non lus par l'admin
$total_conversations_non_lues = $conn->query("SELECT COUNT(DISTINCT expediteur_id) AS total FROM conversations_messages WHERE statut_destinataire = 'non_lu' AND destinataire_id = " . $_SESSION['user_id'])->fetch_assoc()['total'] ?? 0;


// Récupérer les informations de l'administrateur connecté
$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['user_name'] ?? 'Administrateur';

// Variables pour les chemins d'accès aux ressources
$base_css = '../RESOURCE/CSS/dashboart.css';
$base_logo = '../RESOURCE/SITE_IMAGE/logo.jpeg';
$message_status_conv = ''; // Pour afficher les messages de succès ou d'erreur d'envoi de message de conversation

// ID du client actuellement sélectionné pour la conversation (via GET)
$selected_client_id = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;
$selected_client_name = 'Sélectionnez un client'; // Nom par défaut pour l'affichage

// --- Traitement de l'envoi de nouveau message par l'admin ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message_admin'])) {
    $new_message = trim($_POST['new_message'] ?? '');
    $target_client_id = (int)$_POST['target_client_id']; // ID du client à qui répondre
    $sujet_message = "Réponse de l'administration"; // Sujet pour la réponse admin

    if ($target_client_id <= 0) {
        $message_status_conv = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur : Client destinataire non spécifié.</div>';
    } elseif (empty($new_message)) {
        $message_status_conv = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Le message ne peut pas être vide.</div>';
    } else {
        // L'admin envoie un message au client
        // Nous marquerons le message comme 'lu' pour l'expéditeur (admin) et 'non_lu' pour le destinataire (client)
        $stmt = $conn->prepare("INSERT INTO conversations_messages (expediteur_id, destinataire_id, sujet, contenu, statut_expediteur, statut_destinataire) VALUES (?, ?, ?, ?, 'lu', 'non_lu')");
        $stmt->bind_param("iiss", $admin_id, $target_client_id, $sujet_message, $new_message);

        if ($stmt->execute()) {
            // Important: Rediriger pour éviter la soumission multiple du formulaire
            header('Location: dashboart.php?section=conversations&client_id=' . $target_client_id . '&status=message_sent');
            exit();
        } else {
            $message_status_conv = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors de l\'envoi du message : ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

// Afficher le statut de redirection si présent
if (isset($_GET['status']) && $_GET['status'] == 'message_sent') {
    $message_status_conv = '<div class="message message-succes"><i class="fas fa-check-circle"></i> Message envoyé au client avec succès !</div>';
}

// --- Récupération des clients avec qui l'admin a des conversations actives ---
$active_conversations_clients = [];
$stmt_clients = $conn->prepare("
    SELECT DISTINCT u.id AS client_id, u.nom_complet,
           (SELECT MAX(cm.date_envoi) FROM conversations_messages cm WHERE (cm.expediteur_id = u.id AND cm.destinataire_id = ?) OR (cm.destinataire_id = u.id AND cm.expediteur_id = ?)) as last_message_date,
           (SELECT COUNT(*) FROM conversations_messages cm WHERE cm.expediteur_id = u.id AND cm.destinataire_id = ? AND cm.statut_destinataire = 'non_lu') AS unread_count_by_client
    FROM utilisateurs u
    JOIN conversations_messages cm ON (u.id = cm.expediteur_id OR u.id = cm.destinataire_id)
    WHERE u.role = 'client'
    AND (cm.expediteur_id = ? OR cm.destinataire_id = ?)
    ORDER BY last_message_date DESC
");
$stmt_clients->bind_param("iiiii", $admin_id, $admin_id, $admin_id, $admin_id, $admin_id);
$stmt_clients->execute();
$result_clients = $stmt_clients->get_result();

while ($row = $result_clients->fetch_assoc()) {
    $active_conversations_clients[] = $row;
    // Si un client est sélectionné, récupérer son nom
    if ($row['client_id'] == $selected_client_id) {
        $selected_client_name = htmlspecialchars($row['nom_complet']);
    }
}
$stmt_clients->close();

// --- Récupération des messages pour le client sélectionné ---
$current_conversation_messages = [];
if ($selected_client_id > 0) {
    // Récupérer les messages pour la conversation et marquer ceux envoyés par le client comme lus pour l'admin
    $stmt_current_conv = $conn->prepare("
        SELECT cm.id, cm.expediteur_id, cm.contenu, cm.date_envoi, u.nom_complet as sender_nom_complet, u.role as sender_role
        FROM conversations_messages cm
        JOIN utilisateurs u ON cm.expediteur_id = u.id
        WHERE (cm.expediteur_id = ? AND cm.destinataire_id = ?)
           OR (cm.destinataire_id = ? AND cm.expediteur_id = ?)
        ORDER BY cm.date_envoi ASC
    ");
    $stmt_current_conv->bind_param("iiii", $selected_client_id, $admin_id, $selected_client_id, $admin_id);
    $stmt_current_conv->execute();
    $result_current_conv = $stmt_current_conv->get_result();
    while ($row = $result_current_conv->fetch_assoc()) {
        $current_conversation_messages[] = $row;
    }
    $stmt_current_conv->close();

    // Marquer les messages entrants du client comme "lus" pour l'admin
    $stmt_mark_read_client_messages = $conn->prepare("UPDATE conversations_messages SET statut_destinataire = 'lu' WHERE expediteur_id = ? AND destinataire_id = ? AND statut_destinataire = 'non_lu'");
    $stmt_mark_read_client_messages->bind_param("ii", $selected_client_id, $admin_id);
    $stmt_mark_read_client_messages->execute();
    $stmt_mark_read_client_messages->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
     <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-RYX6HF99ZS"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-RYX6HF99ZS');
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="<?php echo $base_logo; ?>" >
    <link rel="stylesheet" href="<?php echo $base_css; ?>">
    <title>Isabelle Event's | Tableau de Bord</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="barre-laterale">
        <div class="conteneur-logo">
            <a href="dashboart.php" class="lien-logo">
                <img src="<?php echo $base_logo; ?>" alt="Logo Isabelle Event's" class="logo">
                <h2>Isabelle Event's</h2>
            </a>
        </div>
        <nav class="navigation-principale">
            <ul>
                <li><a href="#apercu" class="actif"><i class="fas fa-tachometer-alt"></i> Aperçu</a></li>
                <li><a href="#utilisateurs"><i class="fas fa-users"></i> Utilisateurs</a></li>
                <li><a href="#demandes-devis"><i class="fas fa-file-invoice"></i> Demandes de Devis</a></li>
                <li><a href="#decorations"><i class="fas fa-palette"></i> Décorations</a></li>
                <li><a href="#robes"><i class="fas fa-tshirt"></i> Robes</a></li>
                <li><a href="#services-beaute"><i class="fas fa-spa"></i> Services Beauté</a></li>
                <li><a href="#realisations"><i class="fas fa-images"></i> Réalisations</a></li>
                <li><a href="#invitations-evenements"><i class="fas fa-calendar-check"></i> Événements & Invitations</a></li>
                <li><a href="#invites"><i class="fas fa-user-friends"></i> Invités d'Événements</a></li>
                <li><a href="#messages-contact"><i class="fas fa-inbox"></i> Messages de Contact
                    <?php if ($total_messages_non_lus_contact > 0): ?>
                        <span class="compteur-non-lu"><?php echo $total_messages_non_lus_contact; ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="#conversations"><i class="fas fa-comments"></i> Conversations Clients
                    <?php if ($total_conversations_non_lues > 0): ?>
                        <span class="compteur-non-lu"><?php echo $total_conversations_non_lues; ?></span>
                    <?php endif; ?>
                </a></li>
                <li><a href="logout.php" class="deconnexion-btn"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </nav>
    </div>

    <div class="contenu-principal">
        <header class="entete-tableau-bord">
            <h1 style="color:var(--primary-color)">Tableau de Bord Administration</h1>
            <div class="texte-bienvenue">
                Bienvenue, <?php echo htmlspecialchars($admin_name); ?>!
            </div>
        </header>

        <?php if ($message_global): ?>
            <?php echo $message_global; ?>
        <?php endif; ?>

        <section id="apercu" class="section-tableau-bord">
            <h2 class="titre-section">Aperçu Général</h2>
            <div class="grille-statistiques">
                <div class="carte-statistique">
                    <h3>Utilisateurs Enregistrés</h3>
                    <p style="color:var(--primary-color)"><?php echo $total_users; ?></p>
                </div>
                <div class="carte-statistique">
                    <h3>Demandes de Devis</h3>
                    <p style="color:var(--primary-color)"><?php echo $total_devis; ?></p>
                </div>
                <div class="carte-statistique">
                    <h3>Décorations</h3>
                    <p style="color:var(--primary-color)"><?php echo $total_decorations; ?></p>
                </div>
                <div class="carte-statistique">
                    <h3>Robes</h3>
                    <p style="color:var(--primary-color)"><?php echo $total_robes; ?></p>
                </div>
                <div class="carte-statistique">
                    <h3>Services Beauté</h3>
                    <p style="color:var(--primary-color)"><?php echo $total_services_beaute; ?></p>
                </div>
                <div class="carte-statistique">
                    <h3>Réalisations</h3>
                    <p style="color:var(--primary-color)"><?php echo $total_realisations; ?></p>
                </div>
                <div class="carte-statistique">
                    <h3>Événements Créés</h3>
                    <p style="color:var(--primary-color)"><?php echo $total_invitations; ?></p>
                 </div>
                 <div class="carte-statistique">
                    <h3>Invités Totaux</h3>
                    <p style="color:var(--primary-color)"><?php echo $total_invites; ?></p>
                </div>
                <div class="carte-statistique">
                    <h3>Messages Contact Non Lus</h3>
                    <p style="color:var(--primary-color)"><?php echo $total_messages_non_lus_contact; ?></p>
                </div>
                 <div class="carte-statistique">
                    <h3>Conversations Non Lues</h3>
                    <p style="color:var(--primary-color)"><?php echo $total_conversations_non_lues; ?></p>
                </div>
            </div>
        </section>

        <hr>
        <section id="utilisateurs" class="section-tableau-bord">
            <h2 class="titre-section">Gestion des Utilisateurs</h2>
            <a href="SECTION/admin_add_user.php" class="bouton-ajout"><i class="fas fa-plus-circle"></i> Ajouter un Utilisateur</a>
            <p class="description-section">
                Gérez les comptes des utilisateurs enregistrés sur votre plateforme (clients, autres administrateurs).
            </p>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom Complet</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Rôle</th>
                            <th>Date de Création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql_users = "SELECT * FROM utilisateurs ORDER BY date_creation DESC";
                        $result_users = $conn->query($sql_users);
                        if ($result_users && $result_users->num_rows > 0) {
                            while($row = $result_users->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['nom_complet']) . '</td>';
                                echo '<td><a href="mailto:' . htmlspecialchars($row['email']) . '">' . htmlspecialchars($row['email']) . '</a></td>';
                                echo '<td>' . htmlspecialchars($row['telephone']) . '</td>';
                                echo '<td><span class="role-tag role-' . htmlspecialchars($row['role']) . '">' . htmlspecialchars(ucfirst($row['role'])) . '</span></td>';
                                echo '<td>' . htmlspecialchars(date('d/m/Y H:i', strtotime($row['date_creation']))) . '</td>';
                                echo '<td class="actions">';
                                echo '<a href="SECTION/admin_edit_user.php?id=' . htmlspecialchars($row['id']) . '" title="Modifier" class="action-btn modifier-btn"><i class="fas fa-edit"></i> Modifier</a>';
                                echo '<a href="dashboart.php?action=delete_user&id=' . htmlspecialchars($row['id']) . '" class="action-btn supprimer-btn" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cet utilisateur ?\');" title="Supprimer"><i class="fas fa-trash-alt"></i> Supprimer</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="7">Aucun utilisateur trouvé.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <hr>
        <section id="demandes-devis" class="section-tableau-bord">
            <h2 class="titre-section">Gestion des Demandes de Devis</h2>
            <p class="description-section">
                Suivez toutes les demandes de devis soumises par les visiteurs de votre site.
            </p>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom Complet</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Date de Soumission</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql_devis = "SELECT * FROM demandes_devis ORDER BY date_soumission DESC";
                        $result_devis = $conn->query($sql_devis);
                        if ($result_devis && $result_devis->num_rows > 0) {
                            while($row = $result_devis->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['nom_complet']) . '</td>';
                                echo '<td><a href="mailto:' . htmlspecialchars($row['email']) . '">' . htmlspecialchars($row['email']) . '</a></td>';
                                echo '<td>' . htmlspecialchars($row['telephone']) . '</td>';
                                echo '<td>' . htmlspecialchars(date('d/m/Y H:i', strtotime($row['date_soumission']))) . '</td>';
                                echo '<td class="actions">';
                                // Optionnellement, ajouter un bouton pour voir les détails du devis si une page dédiée existe
                                // echo '<a href="SECTION/view_devis_details.php?id=' . htmlspecialchars($row['id']) . '" title="Voir les détails" class="action-btn voir-btn"><i class="fas fa-eye"></i> Voir</a>';
                                echo '<a href="dashboart.php?action=delete_devis&id=' . htmlspecialchars($row['id']) . '" class="action-btn supprimer-btn" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cette demande ?\');" title="Supprimer"><i class="fas fa-trash-alt"></i> Supprimer</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6">Aucune demande de devis trouvée.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <hr>
        <section id="decorations" class="section-tableau-bord">
            <h2 class="titre-section">Gestion des Décorations</h2>
            <a href="SECTION/admin_add_decoration.php" class="bouton-ajout"><i class="fas fa-plus-circle"></i> Ajouter une Décoration</a>
            <p class="description-section">
                Gérez les articles de décoration disponibles pour les événements (chaises, tables, fleurs, etc.).
            </p>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Style</th>
                            <th>Date Ajout</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $upload_dir = '../RESOURCE/USER_IMAGE/';
                        $sql_decorations = "SELECT * FROM decorations ORDER BY date_ajout DESC";
                        $result_decorations = $conn->query($sql_decorations);
                        if ($result_decorations && $result_decorations->num_rows > 0) {
                            while($row = $result_decorations->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['titre']) . '</td>';
                                echo '<td>' . (strlen($row['description']) > 100 ? substr(htmlspecialchars($row['description']), 0, 100) . '...' : htmlspecialchars($row['description'])) . '</td>';
                                echo '<td class="table-image-cell">';
                                if (!empty($row['image_url'])) {
echo '<img src="' . htmlspecialchars($upload_dir . $row['image_url']) . '" alt="Décoration" onerror="this.onerror=null;this.src=\'../RESOURCE/SITE_IMAGE/placeholder.png\';">';
                                } else {
                                    echo 'N/A';
                                }
                                echo '</td>';
                                echo '<td>' . htmlspecialchars($row['style']) . '</td>';
                                echo '<td>' . htmlspecialchars(date('d/m/Y H:i', strtotime($row['date_ajout']))) . '</td>';
                                echo '<td class="actions">';
                                echo '<a href="SECTION/admin_edit_decoration.php?id=' . htmlspecialchars($row['id']) . '" title="Modifier" class="action-btn modifier-btn"><i class="fas fa-edit"></i> Modifier</a>';
                                echo '<a href="dashboart.php?action=delete_decoration&id=' . htmlspecialchars($row['id']) . '" class="action-btn supprimer-btn" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cette décoration ?\');" title="Supprimer"><i class="fas fa-trash-alt"></i> Supprimer</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="7">Aucune décoration trouvée.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <hr>
       <section id="robes" class="section-tableau-bord">
    <h2 class="titre-section">Gestion des Robes</h2>
    <a href="SECTION/admin_add_robe.php" class="bouton-ajout"><i class="fas fa-plus-circle"></i> Ajouter une Robe</a>
    <p class="description-section">
        Gérez le catalogue des robes disponibles à la location ou à la vente.
    </p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Prix Location</th>
                    <th>Prix Vente</th>
                    <th>Image</th>
                    <th>Disponible</th>
                    <th>Date Ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Définir le chemin d'accès web au dossier des images, relatif à dashboart.php
                $web_image_path_for_display = '../RESOURCE/USER_IMAGE/';
                // Chemin pour l'image de substitution (placeholder)
                $placeholder_path = '../RESOURCE/SITE_IMAGE/placeholder.png';

                $sql_robes = "SELECT * FROM robes ORDER BY date_ajout DESC";
                $result_robes = $conn->query($sql_robes);
                if ($result_robes && $result_robes->num_rows > 0) {
                    while($row = $result_robes->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['nom']) . '</td>';
                        echo '<td>' . (strlen($row['description']) > 100 ? substr(htmlspecialchars($row['description']), 0, 100) . '...' : htmlspecialchars($row['description'])) . '</td>';
                        echo '<td>' . htmlspecialchars($row['prix_location'] ? $row['prix_location'] . ' $' : 'N/A') . '</td>';
                        echo '<td>' . htmlspecialchars($row['prix_vente'] ? $row['prix_vente'] . ' $' : 'N/A') . '</td>';
                        echo '<td class="table-image-cell">';
                        if (!empty($row['image_url'])) {
                            // Concaténer le chemin web avec le nom du fichier stocké en DB
                            echo '<img src="' . htmlspecialchars($web_image_path_for_display . $row['image_url']) . '" alt="Robe" onerror="this.onerror=null;this.src=\'' . $placeholder_path . '\';">';
                        } else {
                            echo 'N/A';
                        }
                        echo '</td>';
                        // Correction pour le statut de disponibilité
                        echo '<td>' . ($row['disponible'] === 'disponible' ? '<span class="statut-disponible">Oui</span>' : '<span class="statut-non-disponible">Non</span>') . '</td>';
                        echo '<td>' . htmlspecialchars(date('d/m/Y H:i', strtotime($row['date_ajout']))) . '</td>';
                        echo '<td class="actions">';
                        echo '<a href="SECTION/admin_edit_robe.php?id=' . htmlspecialchars($row['id']) . '" title="Modifier" class="action-btn modifier-btn"><i class="fas fa-edit"></i> Modifier</a>';
                        echo '<a href="dashboart.php?action=delete_robe&id=' . htmlspecialchars($row['id']) . '" class="action-btn supprimer-btn" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cette robe ?\');" title="Supprimer"><i class="fas fa-trash-alt"></i> Supprimer</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="9">Aucune robe trouvée.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

        <hr>
       <section id="services-beaute" class="section-tableau-bord">
            <h2 class="titre-section">Gestion des Services Beauté</h2>
            <a href="SECTION/admin_add_service_beaute.php" class="bouton-ajout"><i class="fas fa-plus-circle"></i> Ajouter un Service Beauté</a>
            <p class="description-section">
                Ajoutez, modifiez ou supprimez les services de coiffure, maquillage et autres prestations beauté.
            </p>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>Description</th>
                            <th>Prix</th>
                            <th>Image</th>
                            <th>Date Ajout</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Définir le chemin d'accès web au dossier des images, relatif à dashboart.php
                        $web_image_path_for_display_services = '../RESOURCE/USER_IMAGE/';
                        // Chemin pour l'image de substitution (placeholder)
                        $placeholder_path_services = '../RESOURCE/SITE_IMAGE/placeholder.png'; // Utilisez le même placeholder ou un spécifique si besoin

                        $sql_services_beaute = "SELECT * FROM services_beaute ORDER BY date_ajout DESC";
                        $result_services_beaute = $conn->query($sql_services_beaute);
                        if ($result_services_beaute && $result_services_beaute->num_rows > 0) {
                            while($row = $result_services_beaute->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['nom']) . '</td>';
                                echo '<td>' . (strlen($row['description']) > 100 ? substr(htmlspecialchars($row['description']), 0, 100) . '...' : htmlspecialchars($row['description'])) . '</td>';
                                // Remplacement de '€' par '$' et gestion du N/A
                                echo '<td>' . htmlspecialchars($row['prix'] ? $row['prix'] . ' $' : 'N/A') . '</td>';
                                // Affichage du statut de disponibilité
                                echo '<td class="table-image-cell">';
                                if (!empty($row['image_url'])) {
                                    // Utilisation du chemin préfixé pour l'affichage de l'image
                                    echo '<img src="' . htmlspecialchars($web_image_path_for_display_services . $row['image_url']) . '" alt="Service Beauté" onerror="this.onerror=null;this.src=\'' . $placeholder_path_services . '\';">';
                                } else {
                                    echo 'N/A';
                                }
                                echo '</td>';
                                echo '<td>' . htmlspecialchars(date('d/m/Y H:i', strtotime($row['date_ajout']))) . '</td>';
                                echo '<td class="actions">';
                                echo '<a href="SECTION/admin_edit_service_beaute.php?id=' . htmlspecialchars($row['id']) . '" title="Modifier" class="action-btn modifier-btn"><i class="fas fa-edit"></i> Modifier</a>';
                                echo '<a href="dashboart.php?action=delete_service_beaute&id=' . htmlspecialchars($row['id']) . '" class="action-btn supprimer-btn" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer ce service ?\');" title="Supprimer"><i class="fas fa-trash-alt"></i> Supprimer</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            // Mise à jour de la colspan pour inclure la nouvelle colonne 'Disponible'
                            echo '<tr><td colspan="8">Aucun service beauté trouvé.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <hr>
       <section id="realisations" class="section-tableau-bord">
            <h2 class="titre-section">Gestion des Réalisations</h2>
            <a href="SECTION/admin_add_realisation.php" class="bouton-ajout"><i class="fas fa-plus-circle"></i> Ajouter une Réalisation</a>
            <p class="description-section">
                Présentez les événements que vous avez organisés avec succès dans votre portfolio.
            </p>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Image</th>
                            <th>Date Réalisation</th>
                            <th>Date Publication</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Définir le chemin d'accès web au dossier des images, relatif à dashboart.php
                        // Votre dashboart.php est à la racine de ADMIN/, donc pour atteindre ../../RESOURCE/USER_IMAGE/,
                        // le chemin web relatif est '../RESOURCE/USER_IMAGE/'.
                        $web_image_path_for_display_realisations = '../RESOURCE/USER_IMAGE/';
                        // Chemin pour l'image de substitution (placeholder)
                        $placeholder_path_realisations = '../RESOURCE/SITE_IMAGE/placeholder.png'; 

                        $sql_realisations = "SELECT * FROM realisations ORDER BY date_publication DESC";
                        $result_realisations = $conn->query($sql_realisations);
                        if ($result_realisations && $result_realisations->num_rows > 0) {
                            while($row = $result_realisations->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['titre']) . '</td>';
                                echo '<td>' . (strlen($row['description']) > 100 ? substr(htmlspecialchars($row['description']), 0, 100) . '...' : htmlspecialchars($row['description'])) . '</td>';
                                echo '<td class="table-image-cell">';
                                if (!empty($row['image_url'])) {
                                    // CONCATÉNER LE CHEMIN DE BASE AVEC LE NOM DU FICHIER STOCKÉ EN DB
                                    echo '<img src="' . htmlspecialchars($web_image_path_for_display_realisations . $row['image_url']) . '" alt="Réalisation" onerror="this.onerror=null;this.src=\'' . $placeholder_path_realisations . '\';">';
                                } else {
                                    echo '<img src="' . htmlspecialchars($placeholder_path_realisations) . '" alt="Pas d\'image" class="placeholder-image">';
                                }
                                echo '</td>';
                                echo '<td>' . htmlspecialchars($row['date_realisation'] ?? 'N/A') . '</td>';
                                echo '<td>' . htmlspecialchars(date('d/m/Y H:i', strtotime($row['date_publication']))) . '</td>';
                                echo '<td class="actions">';
                                echo '<a href="SECTION/admin_edit_realisation.php?id=' . htmlspecialchars($row['id']) . '" title="Modifier" class="action-btn modifier-btn"><i class="fas fa-edit"></i> Modifier</a>';
                                echo '<a href="dashboart.php?action=delete_realisation&id=' . htmlspecialchars($row['id']) . '" class="action-btn supprimer-btn" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cette réalisation ?\');" title="Supprimer"><i class="fas fa-trash-alt"></i> Supprimer</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="7">Aucune réalisation trouvée.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        
        <hr>
      <section id="invitations-evenements" class="section-tableau-bord" style="margin-bottom: 40px; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);">
            <h2 class="titre-section">Gestion des Événements & Invitations</h2>
            <a href="SECTION/admin_add_event_invitation.php" class="bouton-ajout" style="display: inline-block; padding: 10px 15px; color: white; border-radius: 5px; text-decoration: none; font-size: 1em; margin-bottom: 20px;"><i class="fas fa-plus-circle"></i> Créer un Nouvel Événement</a>
            <p class="description-section">
                Créez et gérez les événements pour lesquels vous envoyez des invitations personnalisées.
            </p>
            <div class="table-responsive" style="max-width: 1200px; margin-left: auto; margin-right: auto; overflow-x: auto; padding-bottom: 10px;">
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em;">
                    <thead>
                        <tr  style="background-color:#E91E89;">
                            <th style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: left; vertical-align: top; background-color: #f2f2f2; font-weight: bold; color: #333; width: 5%; min-width: 40px;">ID</th>
                            <th style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: left; vertical-align: top; background-color: #f2f2f2; font-weight: bold; color: #333; width: 15%; min-width: 150px;">Noms des Mariés</th>
                            <th style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: left; vertical-align: top; background-color: #f2f2f2; font-weight: bold; color: #333; width: 10%; min-width: 100px;">Date du Mariage</th>
                            <th style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: left; vertical-align: top; background-color: #f2f2f2; font-weight: bold; color: #333; width: 15%; min-width: 150px;">Lieu Cérémonie</th>
                            <th style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: left; vertical-align: top; background-color: #f2f2f2; font-weight: bold; color: #333; width: 10%; min-width: 80px; text-align: center;">Image</th>
                            <th style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: left; vertical-align: top; background-color: #f2f2f2; font-weight: bold; color: #333; width: 20%; min-width: 200px;">Message Personnalisé</th>
                            <th style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: left; vertical-align: top; background-color: #f2f2f2; font-weight: bold; color: #333; width: 10%; min-width: 100px;">Date Création BD</th>
                            <th style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: left; vertical-align: top; background-color: #f2f2f2; font-weight: bold; color: #333; width: 15%; min-width: 150px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Définir le chemin d'accès web au dossier des images, relatif à dashboart.php
                        $web_image_path_for_events = '../RESOURCE/USER_IMAGE/';
                        // Chemin pour l'image de substitution (placeholder)
                        $placeholder_path_events = '../RESOURCE/SITE_IMAGE/placeholder.png';

                        // Requête SQL corrigée pour sélectionner les vraies colonnes de votre table
                        $sql_events = "SELECT id, noms_maries, date_mariage, lieu_ceremonie, message_perso, date_creation, fond_invitation_url FROM invitations_evenements ORDER BY date_mariage DESC";
                        $result_events = $conn->query($sql_events);

                        if ($result_events && $result_events->num_rows > 0) {
                            while($row = $result_events->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: left; vertical-align: top; white-space: normal;">' . htmlspecialchars($row['id']) . '</td>';
                                echo '<td style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: left; vertical-align: top; white-space: normal;">' . htmlspecialchars($row['noms_maries'] ?? 'N/A') . '</td>';
                                echo '<td style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: left; vertical-align: top; white-space: normal;">' . htmlspecialchars(date('d/m/Y', strtotime($row['date_mariage'] ?? ''))) . '</td>';
                                echo '<td style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: left; vertical-align: top; white-space: normal;">' . htmlspecialchars($row['lieu_ceremonie'] ?? 'N/A') . '</td>';

                                echo '<td style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: center; vertical-align: top; white-space: normal;">';
                                if (!empty($row['fond_invitation_url'])) {
                                    echo '<img src="' . htmlspecialchars($web_image_path_for_events . $row['fond_invitation_url']) . '" alt="Fond d\'invitation" style="max-width: 70px; height: auto; display: block; margin: 0 auto; border-radius: 4px; object-fit: cover;" onerror="this.onerror=null;this.src=\'' . $placeholder_path_events . '\';">';
                                } else {
                                    echo '<img src="' . htmlspecialchars($placeholder_path_events) . '" alt="Pas d\'image" style="max-width: 70px; height: auto; display: block; margin: 0 auto; border-radius: 4px; object-fit: cover;">';
                                }
                                echo '</td>';

                                $message_perso = $row['message_perso'] ?? '';
                                echo '<td style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: left; vertical-align: top; white-space: normal;">' . (mb_strlen($message_perso) > 100 ? htmlspecialchars(mb_substr($message_perso, 0, 100)) . '...' : htmlspecialchars($message_perso)) . '</td>';

                                echo '<td style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: left; vertical-align: top; white-space: normal;">' . htmlspecialchars(date('d/m/Y H:i', strtotime($row['date_creation'] ?? ''))) . '</td>';
                                echo '<td class="actions" style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: center; vertical-align: top; white-space: nowrap;">';
                                echo '<a href="SECTION/admin_edit_event_invitation.php?id=' . htmlspecialchars($row['id']) . '" title="Modifier" style="display: inline-block; padding: 5px 10px; margin: 3px 2px; border-radius: 4px; text-decoration: none; font-size: 0.85em; transition: background-color 0.2s ease; white-space: nowrap; background-color: #007bff; color: white;"><i class="fas fa-edit"></i> Modifier</a>';
                                echo '<a href="SECTION/admin_manage_guests.php?event_id=' . htmlspecialchars($row['id']) . '" title="Gérer les invités" style="display: inline-block; padding: 5px 10px; margin: 3px 2px; border-radius: 4px; text-decoration: none; font-size: 0.85em; transition: background-color 0.2s ease; white-space: nowrap; background-color: #28a745; color: white;"><i class="fas fa-user-friends"></i> Gérer Invités</a>';
                                echo '<a href="dashboart.php?action=delete_event_invitation&id=' . htmlspecialchars($row['id']) . '" class="action-btn supprimer-btn" onclick="return confirm(\'ATTENTION : Supprimer cet événement supprimera aussi tous les invités associés. Êtes-vous sûr ?\');" title="Supprimer cet événement" style="display: inline-block; padding: 5px 10px; margin: 3px 2px; border-radius: 4px; text-decoration: none; font-size: 0.85em; transition: background-color 0.2s ease; white-space: nowrap; background-color: #dc3545; color: white;"><i class="fas fa-trash-alt"></i> Supprimer</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="8" style="border: 1px solid #e0e0e0; padding: 10px 8px; text-align: left; vertical-align: top; white-space: normal;">Aucun événement trouvé.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <hr>
        <section id="invites" class="section-tableau-bord">
            <h2 class="titre-section">Gestion des Invités d'Événements</h2>
            <p class="description-section">
                Visualisez et gérez la liste des invités pour chaque événement. Utilisez la section "Événements & Invitations" pour ajouter des invités à un événement spécifique.
            </p>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID Invité</th>
                            <th>Nom Invité</th>
                            <th>Table Assignée</th> <!-- Nouvelle colonne -->
                            <th>Nombre de Personnes</th> <!-- Nouvelle colonne -->
                            <th>Événement Associé</th>
                            <th>Statut RSVP</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Requête SQL corrigée : nombre_personnes et table_assignee viennent de 'i' (invites)
                       $sql_invites = "SELECT i.id, i.nom_invite, i.table_assignee, i.nombre_personnes, i.statut_rsvp, ie.noms_maries AS titre_evenement, ie.date_mariage FROM invites i JOIN invitations_evenements ie ON i.id_invitation_evenement = ie.id ORDER BY ie.date_mariage DESC, i.nom_invite ASC";
                        $result_invites = $conn->query($sql_invites);
                        if ($result_invites && $result_invites->num_rows > 0) {
                            while($row = $result_invites->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['nom_invite']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['table_assignee'] ?? 'N/A') . '</td>'; // Afficher la table assignée
                                echo '<td>' . htmlspecialchars($row['nombre_personnes']) . '</td>'; // Afficher le nombre de personnes
                                echo '<td>' . htmlspecialchars($row['titre_evenement']) . '</td>';
                                echo '<td><span class="statut-rsvp statut-rsvp-' . strtolower(str_replace(' ', '-', $row['statut_rsvp'])) . '">' . htmlspecialchars(ucfirst($row['statut_rsvp'])) . '</span></td>';
                                echo '<td class="actions">';
                                // Pas de modification directe d'invité ici, la gestion se fait via l'événement
                                echo '<a href="dashboart.php?action=delete_invite&id=' . htmlspecialchars($row['id']) . '" class="action-btn supprimer-btn" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cet invité ?\');" title="Supprimer"><i class="fas fa-trash-alt"></i> Supprimer</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="7">Aucun invité trouvé.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <hr>
        <section id="messages-contact" class="section-tableau-bord">
            <h2 class="titre-section">Messages de Contact Général</h2>
            <p class="description-section">
                Consultez et gérez les messages envoyés via le formulaire de contact général de votre site web.
            </p>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Expéditeur</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Statut</th>
                            <th>Date Réception</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql_messages = "SELECT * FROM messages ORDER BY date_reception DESC";
                        $result_messages = $conn->query($sql_messages);
                        if ($result_messages && $result_messages->num_rows > 0) {
                            while($row = $result_messages->fetch_assoc()) {
                                $message_statut_class = ($row['statut'] == 'non_lu') ? 'statut-non-lu' : 'statut-lu';
                                echo '<tr class="' . ($row['statut'] == 'non_lu' ? 'message-non-lu-row' : '') . '">';
                                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                                echo '<td>' . htmlspecialchars($row['nom_complet']) . '</td>'; // Assuming nom_expediteur is nom_complet in your messages table
                                echo '<td><a href="mailto:' . htmlspecialchars($row['email']) . '">' . htmlspecialchars($row['email']) . '</a></td>'; // Assuming email_expediteur is email
                                echo '<td>' . (strlen($row['message']) > 150 ? substr(htmlspecialchars($row['message']), 0, 150) . '...' : htmlspecialchars($row['message'])) . '</td>';
                                echo '<td><span class="statut-message statut-' . htmlspecialchars($row['statut']) . '">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $row['statut']))) . '</span></td>';
                                echo '<td>' . htmlspecialchars(date('d/m/Y H:i', strtotime($row['date_reception']))) . '</td>';
                                echo '<td class="actions">';
                                if ($row['statut'] == 'non_lu') {
                                    echo '<a href="dashboart.php?action=mark_read_message&id=' . htmlspecialchars($row['id']) . '#messages-contact" class="action-btn lire-btn" title="Marquer comme lu"><i class="fas fa-check-circle"></i> Marquer lu</a>';
                                }
                                echo '<a href="dashboart.php?action=delete_message&id=' . htmlspecialchars($row['id']) . '" class="action-btn supprimer-btn" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer ce message ?\');" title="Supprimer"><i class="fas fa-trash-alt"></i> Supprimer</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="7">Aucun message de contact trouvé.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <hr>
        <section id="conversations" class="section-tableau-bord">
            <h2 class="titre-section">Conversations Clients</h2>
            <p class="description-section">
                Gérez les discussions en temps réel avec vos clients enregistrés. Sélectionnez un client pour afficher et répondre à la conversation.
            </p>
            <?php if ($message_status_conv): ?>
                <?php echo $message_status_conv; // Affiche les messages spécifiques aux conversations ?>
            <?php endif; ?>
            <div class="conversation-container">
                <div class="liste-clients"> <!-- Changed from liste-clients-conversation -->
                    <h3>Clients en Conversation</h3>
                    <?php if (empty($active_conversations_clients)): ?>
                        <p class="message-info">Aucune conversation active pour le moment.</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($active_conversations_clients as $client_conv): ?>
                                <li>
                                    <a href="dashboart.php?section=conversations&client_id=<?php echo htmlspecialchars($client_conv['client_id']); ?>"
                                       class="<?php echo ($selected_client_id == $client_conv['client_id']) ? 'selected' : ''; ?>"> <!-- Changed 'actif' to 'selected' for conversation list -->
                                        <div class="client-info">
                                            <span><?php echo htmlspecialchars($client_conv['nom_complet']); ?></span>
                                            <?php if ($client_conv['unread_count_by_client'] > 0): ?>
                                                <span class="compteur-non-lu"><?php echo $client_conv['unread_count_by_client']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <small><?php echo $client_conv['last_message_date'] ? date('d/m/Y H:i', strtotime($client_conv['last_message_date'])) : 'Aucun message'; ?></small>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="zone-discussion"> <!-- Changed from fenetre-conversation -->
                    <div class="entete-conversation">
                        Conversation avec :<?php echo $selected_client_name; ?>
                    </div>
                    <div class="messages-historique"> <!-- Changed from corps-conversation -->
                        <?php if ($selected_client_id == 0): ?>
                            <p class="message-info">Sélectionnez un client dans la liste pour afficher la conversation.</p>
                        <?php elseif (empty($current_conversation_messages)): ?>
                            <p class="message-info">Aucun message dans cette conversation. Envoyez un message au client.</p>
                        <?php else: ?>
                            <?php foreach ($current_conversation_messages as $msg): ?>
                                <div class="message-bulle <?php echo ($msg['expediteur_id'] == $admin_id) ? 'message-moi' : 'message-autre'; ?>">
                                    <p><?php echo nl2br(htmlspecialchars($msg['contenu'])); ?></p>
                                    <span class="message-date">
                                        <?php
                                        $sender_display_name = ($msg['expediteur_id'] == $admin_id) ? 'Vous' : htmlspecialchars($msg['sender_nom_complet']);
                                        echo $sender_display_name . ' - ' . date('d/m/Y H:i', strtotime($msg['date_envoi']));
                                        ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php if ($selected_client_id > 0): ?>
                        <form action="dashboart.php?section=conversations&client_id=<?php echo htmlspecialchars($selected_client_id); ?>" method="POST" class="form-envoyer-message"> <!-- Changed from formulaire-envoi-message -->
                            <input type="hidden" name="target_client_id" value="<?php echo htmlspecialchars($selected_client_id); ?>">
                            <textarea name="new_message" placeholder="Écrivez votre message ici..." required></textarea>
                            <button type="submit" name="send_message_admin" class="bouton-envoyer"><i class="fas fa-paper-plane"></i> Envoyer</button> <!-- Added bouton-envoyer class -->
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Script pour le défilement fluide vers les sections
            document.querySelectorAll('.barre-laterale ul li a').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    // Empêche le comportement de lien par default si l'URL contient un fragment
                    if (this.hash) {
                        e.preventDefault();
                    }

                    document.querySelectorAll('.barre-laterale ul li a').forEach(link => {
                        link.classList.remove('actif');
                    });
                    this.classList.add('actif');

                    let targetId = this.getAttribute('href').substring(1);
                    if (targetId) {
                        let targetElement = document.getElementById(targetId);
                        if (targetElement) {
                            window.scrollTo({
                                top: targetElement.offsetTop - 20, // Ajuste le défilement pour un peu de marge
                                behavior: 'smooth'
                            });
                        }
                    }
                });
            });

            // Gérer la section active au chargement de la page et au rafraîchissement
            const currentHash = window.location.hash.substring(1);
            const currentSectionParam = new URLSearchParams(window.location.search).get('section');

            if (currentSectionParam) {
                // Si 'section' est dans l'URL (ex: pour les conversations après envoi)
                const activeLink = document.querySelector(`.barre-laterale ul li a[href="#${currentSectionParam}"]`);
                if (activeLink) {
                    document.querySelectorAll('.barre-laterale ul li a').forEach(link => link.classList.remove('actif'));
                    activeLink.classList.add('actif');
                    setTimeout(() => {
                        const targetElement = document.getElementById(currentSectionParam);
                        if (targetElement) {
                            window.scrollTo({
                                top: targetElement.offsetTop - 20,
                                behavior: 'smooth'
                            });
                        }
                    }, 100);
                }
            } else if (currentHash) {
                // Si c'est un hash simple
                const activeLink = document.querySelector(`.barre-laterale ul li a[href="#${currentHash}"]`);
                if (activeLink) {
                    document.querySelectorAll('.barre-laterale ul li a').forEach(link => link.classList.remove('actif'));
                    activeLink.classList.add('actif');
                    setTimeout(() => {
                        const targetElement = document.getElementById(currentHash);
                        if (targetElement) {
                            window.scrollTo({
                                top: targetElement.offsetTop - 20,
                                behavior: 'smooth'
                            });
                        }
                    }, 100);
                }
            } else {
                 // Par défaut, activer "Aperçu"
                const defaultLink = document.querySelector('.barre-laterale ul li a[href="#apercu"]');
                if (defaultLink) {
                    defaultLink.classList.add('actif');
                }
            }

            // Faire défiler automatiquement la conversation vers le bas
            const conversationBody = document.querySelector('.messages-historique'); // Corrected class name
            if (conversationBody) {
                conversationBody.scrollTop = conversationBody.scrollHeight;
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>