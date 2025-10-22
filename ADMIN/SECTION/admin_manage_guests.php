<?php
session_start(); // Démarre la session

// --- IMPORTANT : Afficher les erreurs PHP pour le débogage (À retirer en production !) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN IMPORTANT ---

// Ajustez le chemin vers votre fichier de connexion à la base de données
include('../../INCLUDE/data.php'); // Assurez-vous que ce chemin est correct

// --- Sécurité : Vérifier si l'utilisateur est connecté et est un admin ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../PUBLIC/login.php'); // Rediriger vers la page de connexion publique
    exit();
}

// Variables pour les chemins d'accès aux ressources
$base_css = '../../RESOURCE/CSS/style.css';
$base_logo = '../../RESOURCE/SITE_IMAGE/logo.jpeg';

// Initialisation du message de statut
$message_status = '';

// Récupérer l'ID de l'événement depuis l'URL
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$event_name = "Événement inconnu"; // Valeur par défaut si l'ID n'est pas trouvé ou valide

// Vérifier si l'event_id est valide et récupérer le nom de l'événement
if ($event_id > 0) {
    $stmt_event = $conn->prepare("SELECT noms_maries FROM invitations_evenements WHERE id = ?");
    $stmt_event->bind_param("i", $event_id);
    $stmt_event->execute();
    $result_event = $stmt_event->get_result();
    if ($result_event->num_rows > 0) {
        $event_row = $result_event->fetch_assoc();
        $event_name = htmlspecialchars($event_row['noms_maries']);
    } else {
        $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> ID d\'événement non valide.</div>';
        $event_id = 0; // Invalider l'ID si non trouvé
    }
    $stmt_event->close();
} else {
    $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Aucun ID d\'événement spécifié. Veuillez sélectionner un événement depuis le tableau de bord.</div>';
}

// --- Traitement des actions (suppression d'invité) ---
if (isset($_GET['action']) && $_GET['action'] === 'delete_guest' && isset($_GET['guest_id']) && $event_id > 0) {
    $guest_id_to_delete = intval($_GET['guest_id']);

    // Sécurité : S'assurer que l'invité appartient bien à cet événement
    $check_stmt = $conn->prepare("SELECT id FROM invites WHERE id = ? AND id_invitation_evenement = ?");
    $check_stmt->bind_param("ii", $guest_id_to_delete, $event_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $delete_stmt = $conn->prepare("DELETE FROM invites WHERE id = ?");
        $delete_stmt->bind_param("i", $guest_id_to_delete);
        if ($delete_stmt->execute()) {
            $message_status = '<div class="message message-succes"><i class="fas fa-check-circle"></i> Invité supprimé avec succès.</div>';
        } else {
            $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors de la suppression de l\'invité : ' . $delete_stmt->error . '</div>';
        }
        $delete_stmt->close();
    } else {
        $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Invité non trouvé ou n\'appartient pas à cet événement.</div>';
    }
    $check_stmt->close();
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
    <link rel="icon" type="image/x-icon" href="<?php echo $base_logo; ?>">
    <link rel="stylesheet" href="../../RESOURCE/CSS/admin_manage_guests.css">
    <title>Isabelle Event's | Admin - Gérer Invités pour <?php echo $event_name; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Gestion des Invités pour l'événement : <?php echo $event_name; ?></h1>
            <div>
                <a href="../dashboart.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour au Dashboard</a>
                <?php if ($event_id > 0): ?>
                    <a href="admin_add_guest.php?event_id=<?php echo $event_id; ?>" class="btn-add"><i class="fas fa-user-plus"></i> Ajouter un Invité</a>
                <?php endif; ?>
            </div>
        </header>

        <?php
        // Afficher les messages de statut (succès/erreur)
        if (!empty($message_status)) {
            echo $message_status;
        }

        // Afficher le tableau des invités seulement si un événement valide est sélectionné
        if ($event_id > 0):
        ?>
        <table>
            <thead>
                <tr>
                    <th>ID Invité</th>
                    <th>Nom Invité</th>
                    <th>Table Assignée</th>
                    <th>Nb Personnes</th>
                    <th>Statut RSVP</th>
                    <th>QR Code Data</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql_guests = "SELECT * FROM invites WHERE id_invitation_evenement = ? ORDER BY nom_invite ASC";
                $stmt_guests = $conn->prepare($sql_guests);
                $stmt_guests->bind_param("i", $event_id);
                $stmt_guests->execute();
                $result_guests = $stmt_guests->get_result();

                if ($result_guests && $result_guests->num_rows > 0) {
                    while($guest_row = $result_guests->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td data-label="ID Invité">' . htmlspecialchars($guest_row['id']) . '</td>';
                        echo '<td data-label="Nom Invité">' . htmlspecialchars($guest_row['nom_invite']) . '</td>';
                        echo '<td data-label="Table Assignée">' . htmlspecialchars($guest_row['table_assignee'] ?? 'N/A') . '</td>';
                        echo '<td data-label="Nb Personnes">' . htmlspecialchars($guest_row['nombre_personnes']) . '</td>';
                        echo '<td data-label="Statut RSVP">' . htmlspecialchars($guest_row['statut_rsvp']) . '</td>';
                        echo '<td data-label="QR Code Data">' . htmlspecialchars(substr($guest_row['qr_code_data'] ?? 'N/A', 0, 30)) . (strlen($guest_row['qr_code_data'] ?? '') > 30 ? '...' : '') . '</td>';
                        echo '<td class="actions" data-label="Actions">';
                        // Lien de modification d'invité (à créer : admin_edit_guest.php)
                        echo '<a href="admin_edit_guest.php?guest_id=' . htmlspecialchars($guest_row['id']) . '&event_id=' . $event_id . '" title="Modifier cet invité"><i class="fas fa-edit"></i> Modifier</a>';
                        // Lien de suppression d'invité
                        echo '<a href="admin_manage_guests.php?action=delete_guest&guest_id=' . htmlspecialchars($guest_row['id']) . '&event_id=' . $event_id . '" class="supprimer" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cet invité ?\');" title="Supprimer cet invité"><i class="fas fa-trash-alt"></i> Supprimer</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="7">Aucun invité trouvé pour cet événement.</td></tr>';
                }
                $stmt_guests->close();
                ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>Veuillez sélectionner un événement valide depuis le <a href="../dashboart.php">Dashboard</a> pour gérer les invités.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$conn->close(); // Ferme la connexion à la base de données
?>