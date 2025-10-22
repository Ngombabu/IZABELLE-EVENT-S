<?php
session_start(); // Start the session

// --- IMPORTANT: Display PHP errors for debugging (Remove in production!) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- END IMPORTANT ---

// Adjust the path to your database connection file
// Note: Path adapted for a file located in 'ADMIN/SECTION/'
include('../../INCLUDE/data.php'); // Ensure this path is correct

// --- Security: Check if the user is logged in and is an admin ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../../PUBLIC/login.php'); // Redirect to the public login page
    exit();
}

// Variables for resource paths
// Note: Paths adapted for a file located in 'ADMIN/SECTION/'
$base_css = '../../RESOURCE/CSS/style.css';
$base_logo = '../../RESOURCE/SITE_IMAGE/logo.jpeg';

// Initialize status message
$message_status = '';

// Get the event ID from the URL
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$event_name = "Événement inconnu"; // Default value if ID not found or invalid

// Check if event_id is valid and retrieve the event name
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
        $event_id = 0; // Invalidate ID if not found
    }
    $stmt_event->close();
} else {
    $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Aucun ID d\'événement spécifié. Impossible d\'ajouter un invité sans un événement lié.</div>';
}

// --- Form processing for adding a guest ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $event_id > 0) { // Only process if a valid event is selected
    $id_invitation_evenement = $event_id; // The event ID is linked from the URL
    $nom_invite = trim($_POST['nom_invite'] ?? '');
    $table_assignee = trim($_POST['table_assignee'] ?? NULL); // Can be NULL
    $nombre_personnes = intval($_POST['nombre_personnes'] ?? 1);
    $statut_rsvp = $_POST['statut_rsvp'] ?? 'en_attente';
    $qr_code_data = NULL; // QR code data will be generated if needed, or left null initially

    // Basic validation
    if (empty($nom_invite) || $nombre_personnes <= 0) {
        $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Veuillez fournir un nom pour l\'invité et un nombre de personnes valide.</div>';
    } else {
        // Generate QR Code data (example - you might integrate a QR code library here)
        // For simplicity, let's just create a string like "eventID_guestName_timestamp"
        $qr_code_data_string = "event_{$event_id}_guest_" . str_replace(' ', '_', $nom_invite) . "_" . time();
        $qr_code_data = $qr_code_data_string; // Store this string in the database

        // Insert data into the `invites` table
        // Ensure your table is named 'invites' and has the corresponding columns
        $stmt = $conn->prepare("INSERT INTO invites (id_invitation_evenement, nom_invite, table_assignee, nombre_personnes, qr_code_data, statut_rsvp) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ississ", $id_invitation_evenement, $nom_invite, $table_assignee, $nombre_personnes, $qr_code_data, $statut_rsvp);

        if ($stmt->execute()) {
            $message_status = '<div class="message message-succes"><i class="fas fa-check-circle"></i> Invité ajouté avec succès pour l\'événement "' . $event_name . '" !</div>';
            // Optional: Reset form fields after successful addition
            $_POST = array(); // Clears $_POST to reset the form fields
        } else {
            $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors de l\'ajout de l\'invité : ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
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
    <link rel="icon" type="image/x-icon" href="">
    <link rel="stylesheet" href="../../RESOURCE/CSS/admin_add_guest.css">
    <title>Isabelle Event's | Admin - Ajouter Invité</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Ajouter un Invité pour l'événement : <?php echo $event_name; ?></h1>
            <div>
                <a href="admin_manage_guests.php?event_id=<?php echo $event_id; ?>" class="btn-back"><i class="fas fa-arrow-left"></i> Retour à la gestion des invités</a>
            </div>
        </header>

        <?php
        // Display status messages (success/error)
        if (!empty($message_status)) {
            echo $message_status;
        }

        // Display the form only if a valid event is selected
        if ($event_id > 0):
        ?>
        <form action="admin_add_guest.php?event_id=<?php echo $event_id; ?>" method="POST">
            <div class="form-group">
                <label for="nom_invite">Nom de l'invité principal :</label>
                <input type="text" id="nom_invite" name="nom_invite" required placeholder="Ex: M. et Mme Dupont">
            </div>
            <div class="form-group">
                <label for="nombre_personnes">Nombre de personnes (incluant l'invité principal) :</label>
                <input type="number" id="nombre_personnes" name="nombre_personnes" min="1" value="1" required>
            </div>
            <div class="form-group">
                <label for="table_assignee">Table assignée (Optionnel) :</label>
                <input type="text" id="table_assignee" name="table_assignee" placeholder="Ex: Table 5 ou Famille">
            </div>
            <div class="form-group">
                <label for="statut_rsvp">Statut RSVP initial :</label>
                <select id="statut_rsvp" name="statut_rsvp">
                    <option value="en_attente">En attente</option>
                    <option value="confirme">Confirmé</option>
                    <option value="refuse">Refusé</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit">Ajouter l'Invité</button>
            </div>
        </form>
        <?php else: ?>
            <p>Impossible d'ajouter un invité. Veuillez sélectionner un événement valide depuis le <a href="../dashboart.php">Dashboard</a>.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$conn->close(); // Close the database connection
?>