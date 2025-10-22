<?php
session_start(); // Démarre la session

// --- IMPORTANT : Afficher les erreurs PHP pour le débogage (À retirer en production !) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN IMPORTANT ---

// Ajustez le chemin vers votre fichier de connexion à la base de données
// Note : Le chemin est adapté pour être dans un sous-dossier 'SECTION'
include('../../INCLUDE/data.php'); // Assurez-vous que ce chemin est correct

// --- Sécurité : Vérifier si l'utilisateur est connecté et est un admin ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location:login.php'); // Rediriger vers la page de connexion publique
    exit();
}

// Variables pour les chemins d'accès aux ressources
// Note : Les chemins sont adaptés pour être dans un sous-dossier 'SECTION'
$base_css = '../../RESOURCE/CSS/style.css';
$base_logo = '../../RESOURCE/SITE_IMAGE/logo.jpeg';

// Initialisation du message de statut
$message_status = '';

// --- Traitement du formulaire d'ajout d'événement/invitation principale ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données du formulaire
    $id_client = !empty($_POST['id_client']) ? intval($_POST['id_client']) : NULL; // Peut être NULL si non fourni
    $noms_maries = trim($_POST['noms_maries'] ?? '');
    $date_mariage = trim($_POST['date_mariage'] ?? '');
    $heure_ceremonie = trim($_POST['heure_ceremonie'] ?? '');
    $lieu_ceremonie = trim($_POST['lieu_ceremonie'] ?? '');
    $heure_reception = trim($_POST['heure_reception'] ?? NULL); // Peut être NULL
    $lieu_reception = trim($_POST['lieu_reception'] ?? NULL);   // Peut être NULL
    $message_perso = trim($_POST['message_perso'] ?? NULL);     // Peut être NULL
    $rsvp_contact = trim($_POST['rsvp_contact'] ?? NULL);       // Peut être NULL

    // --- Traitement de l'upload de l'image de fond (si applicable) ---
    // Votre table `invitations_evenements` a `fond_invitation_url`. 
    // Si vous permettez l'upload, voici la logique.
    // Si l'URL est juste un lien externe ou un nom prédéfini, ajustez cette partie.
    $fond_invitation_url = NULL; // Initialise à NULL

    if (isset($_FILES['fond_invitation_file']) && $_FILES['fond_invitation_file']['error'] === UPLOAD_ERR_OK) {
        $image_name = $_FILES['fond_invitation_file']['name'];
        $image_tmp_name = $_FILES['fond_invitation_file']['tmp_name'];
        $upload_dir = '../../RESOURCE/USER_IMAGE/'; // Chemin vers le dossier de fonds d'invitation
        // Assurez-vous que ce dossier existe et est accessible en écriture par le serveur web
        
        $image_extension = pathinfo($image_name, PATHINFO_EXTENSION);
        $new_image_name = uniqid('fond_', true) . '.' . $image_extension; // Nom unique
        $destination_path = $upload_dir . $new_image_name;

        if (move_uploaded_file($image_tmp_name, $destination_path)) {
            $fond_invitation_url = $destination_path; // Stocke le chemin relatif dans la BDD
        } else {
            $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors du téléchargement du fichier de fond.</div>';
        }
    } else if (isset($_FILES['fond_invitation_file']) && $_FILES['fond_invitation_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Une erreur est survenue lors du téléchargement de l\'image de fond. Code: ' . $_FILES['fond_invitation_file']['error'] . '</div>';
    }


    // Validation des champs obligatoires
    if (empty($noms_maries) || empty($date_mariage) || empty($heure_ceremonie) || empty($lieu_ceremonie)) {
        $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Veuillez remplir tous les champs obligatoires (Noms mariés, Date et Heure Cérémonie, Lieu Cérémonie).</div>';
    } else {
        // Préparer et exécuter la requête d'insertion
        if (empty($message_status)) { // Procéder si pas d'erreur d'upload ou autre validation
            $stmt = $conn->prepare("INSERT INTO invitations_evenements (id_client, noms_maries, date_mariage, heure_ceremonie, lieu_ceremonie, heure_reception, lieu_reception, message_perso, rsvp_contact, fond_invitation_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            // Le type de bind_param dépend des types SQL : i=integer, s=string, d=double, b=blob
            // Pour les champs qui peuvent être NULL, utilisez 's' et passez NULL.
            $stmt->bind_param("isssssssss", 
                $id_client, 
                $noms_maries, 
                $date_mariage, 
                $heure_ceremonie, 
                $lieu_ceremonie, 
                $heure_reception, 
                $lieu_reception, 
                $message_perso, 
                $rsvp_contact, 
                $fond_invitation_url
            );

            if ($stmt->execute()) {
                $message_status = '<div class="message message-succes"><i class="fas fa-check-circle"></i> Nouvel événement ajouté avec succès !</div>';
                // Optionnel : Réinitialiser les champs du formulaire ou rediriger
            } else {
                $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors de l\'ajout de l\'événement : ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
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
    <link rel="icon" type="image/x-icon" href="<?php echo $base_logo; ?>">
    <link rel="stylesheet" href="../../RESOURCE/CSS/admin_add_event_invitation.css">
    <title>Isabelle Event's | Admin - Créer Événement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Créer un Nouvel Événement</h1>
            <div>
                <a href="../dashboart.php#invitations" class="btn-back"><i class="fas fa-arrow-left"></i> Retour au Dashboard</a>
            </div>
        </header>

        <?php
        // Afficher les messages de statut (succès/erreur)
        if (!empty($message_status)) {
            echo $message_status;
        }
        ?>

        <form action="admin_add_event_invitation.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="id_client">ID Client (Optionnel) :</label>
                <input type="number" id="id_client" name="id_client" placeholder="Ex: 123">
                <small>Laissez vide si non lié à un client spécifique pour l'instant.</small>
            </div>
            <div class="form-group">
                <label for="noms_maries">Noms des mariés / Nom de l'événement :</label>
                <input type="text" id="noms_maries" name="noms_maries" required placeholder="Ex: Jean & Marie Dupont">
            </div>
            <div class="form-group">
                <label for="date_mariage">Date de l'événement :</label>
                <input type="date" id="date_mariage" name="date_mariage" required>
            </div>
            <div class="form-group">
                <label for="heure_ceremonie">Heure de la cérémonie :</label>
                <input type="time" id="heure_ceremonie" name="heure_ceremonie" required>
            </div>
            <div class="form-group">
                <label for="lieu_ceremonie">Lieu de la cérémonie :</label>
                <input type="text" id="lieu_ceremonie" name="lieu_ceremonie" required placeholder="Ex: Église Saint-Pierre, Paris">
            </div>
            <div class="form-group">
                <label for="heure_reception">Heure de la réception (Optionnel) :</label>
                <input type="time" id="heure_reception" name="heure_reception">
            </div>
            <div class="form-group">
                <label for="lieu_reception">Lieu de la réception (Optionnel) :</label>
                <input type="text" id="lieu_reception" name="lieu_reception" placeholder="Ex: Salon des Lumières, Versailles">
            </div>
            <div class="form-group">
                <label for="message_perso">Message personnel (Optionnel) :</label>
                <textarea id="message_perso" name="message_perso" rows="5" placeholder="Un petit mot pour les invités..."></textarea>
            </div>
            <div class="form-group">
                <label for="rsvp_contact">Contact RSVP (Optionnel - email ou numéro de téléphone) :</label>
                <input type="text" id="rsvp_contact" name="rsvp_contact" placeholder="Ex: rsvp@exemple.com ou 0612345678">
            </div>
            <div class="form-group">
                <label for="fond_invitation_file">Image de fond de l'invitation (Optionnel) :</label>
                <input type="file" id="fond_invitation_file" name="fond_invitation_file" accept="image/*">
                <small>Téléchargez une image pour le fond visuel de l'invitation.</small>
            </div>
            <div class="form-group">
                <button type="submit">Ajouter l'Événement</button>
            </div>
        </form>
    </div>
</body>
</html>
<?php
$conn->close(); // Ferme la connexion à la base de données
?>