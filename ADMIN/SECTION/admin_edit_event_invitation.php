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
    // Rediriger vers la page de connexion publique (ajustez le chemin si nécessaire)
    header('Location: ../PUBLIC/login.php'); 
    exit();
}

// Variables pour les chemins d'accès aux ressources
$base_css = '../../RESOURCE/CSS/style.css';
$base_logo = '../../RESOURCE/SITE_IMAGE/logo.jpeg';

// Définir le répertoire d'upload pour les fichiers (chemin sur le serveur)
$upload_dir_server = '../../RESOURCE/USER_IMAGE/'; // Assurez-vous qu'il se termine par un slash !

// Initialisation du message de statut
$message_status = '';
$event_invitation = null; // Variable pour stocker les données de l'invitation à éditer

// --- Récupérer l'ID de l'événement à modifier depuis l'URL ---
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $event_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($event_id === false) {
        $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> ID d\'événement/invitation invalide ou manquant.</div>';
    } else {
        // Préparer et exécuter la requête pour récupérer les informations de l'invitation
        $stmt = $conn->prepare("SELECT * FROM invitations_evenements WHERE id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $event_invitation = $result->fetch_assoc();
        $stmt->close();

        if (!$event_invitation) {
            $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Événement/invitation non trouvé avec l\'ID spécifié.</div>';
        }
    }
} else {
    $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Aucun ID d\'événement/invitation spécifié pour la modification.</div>';
}

// --- Traitement du formulaire de modification d'événement/invitation ---
// S'assurer que le formulaire est soumis et que l'invitation a été chargée
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $event_invitation) {
    // Récupérer et nettoyer les données du formulaire
    $id_client = !empty($_POST['id_client']) ? intval($_POST['id_client']) : NULL;
    $noms_maries = trim($_POST['noms_maries'] ?? '');
    $date_mariage = trim($_POST['date_mariage'] ?? '');
    $heure_ceremonie = trim($_POST['heure_ceremonie'] ?? '');
    $lieu_ceremonie = trim($_POST['lieu_ceremonie'] ?? '');
    $heure_reception = trim($_POST['heure_reception'] ?? NULL);
    $lieu_reception = trim($_POST['lieu_reception'] ?? NULL);
    $message_perso = trim($_POST['message_perso'] ?? NULL);
    $rsvp_contact = trim($_POST['rsvp_contact'] ?? NULL);

    // Par défaut, le nom de fichier de l'image reste celui qui est déjà en DB
    $fond_invitation_filename_to_db = $event_invitation['fond_invitation_url'];

    // Validation des champs obligatoires
    if (empty($noms_maries) || empty($date_mariage) || empty($heure_ceremonie) || empty($lieu_ceremonie)) {
        $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Veuillez remplir tous les champs obligatoires (Noms mariés, Date et Heure Cérémonie, Lieu Cérémonie).</div>';
    } else {
        // Traitement de l'upload de la nouvelle image de fond (si présente)
        if (isset($_FILES['fond_invitation_file']) && $_FILES['fond_invitation_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $image_name_original = $_FILES['fond_invitation_file']['name'];
            $image_tmp_name = $_FILES['fond_invitation_file']['tmp_name'];
            $image_error = $_FILES['fond_invitation_file']['error'];
            $image_size = $_FILES['fond_invitation_file']['size'];

            if ($image_error === UPLOAD_ERR_OK) {
                // Validation de la taille (exemple: max 5MB)
                $max_size = 5 * 1024 * 1024; // 5 MB
                if ($image_size > $max_size) {
                    $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> L\'image est trop grande. Taille maximale : ' . ($max_size / (1024 * 1024)) . ' MB.</div>';
                } else {
                    $image_extension = pathinfo($image_name_original, PATHINFO_EXTENSION);
                    $new_image_name_unique = uniqid('fond_', true) . '.' . $image_extension; // Nom unique
                    $destination_path_on_server = $upload_dir_server . $new_image_name_unique;

                    // Vérifier le type MIME du fichier pour une meilleure sécurité
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $image_tmp_name);
                    finfo_close($finfo);

                    if (in_array($mime_type, $allowed_types)) {
                        // Supprimer l'ancienne image si elle existe et n'est pas vide
                        $old_image_path_on_server = $upload_dir_server . $event_invitation['fond_invitation_url'];
                        if (!empty($event_invitation['fond_invitation_url']) && file_exists($old_image_path_on_server)) {
                            if (!unlink($old_image_path_on_server)) {
                                error_log("Erreur: Impossible de supprimer l'ancienne image de fond: " . $old_image_path_on_server);
                            }
                        }

                        if (move_uploaded_file($image_tmp_name, $destination_path_on_server)) {
                            $fond_invitation_filename_to_db = $new_image_name_unique; // Nouveau nom de fichier pour la base de données
                        } else {
                            $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors du déplacement du nouveau fichier de fond téléchargé sur le serveur.</div>';
                            error_log("move_uploaded_file failed for: " . $image_tmp_name . " to " . $destination_path_on_server . " - PHP Error: " . error_get_last()['message']);
                        }
                    } else {
                        $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Type de nouveau fichier non autorisé pour l\'image de fond. Seules les images JPEG, PNG, GIF et WEBP sont acceptées.</div>';
                    }
                }
            } else {
                // Gérer les erreurs d'upload PHP spécifiques
                $upload_errors = [
                    UPLOAD_ERR_INI_SIZE   => 'Le fichier téléchargé dépasse la taille maximale autorisée par le serveur (php.ini).',
                    UPLOAD_ERR_FORM_SIZE  => 'Le fichier téléchargé dépasse la taille maximale spécifiée dans le formulaire HTML.',
                    UPLOAD_ERR_PARTIAL    => 'Le fichier n\'a été que partiellement téléchargé.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Un dossier temporaire est manquant.',
                    UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier sur le disque.',
                    UPLOAD_ERR_EXTENSION  => 'Une extension PHP a arrêté le téléchargement du fichier.'
                ];
                $error_message = $upload_errors[$image_error] ?? 'Une erreur inconnue est survenue lors du téléchargement de l\'image de fond (Code: ' . $image_error . ').';
                $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> ' . $error_message . '</div>';
                error_log("Upload error for fond_invitation_file: " . $image_error . " - " . $error_message);
            }
        }

        // Si aucune erreur jusqu'à présent (incluant les uploads)
        if (empty($message_status)) {
            // Préparer et exécuter la requête de mise à jour
            $stmt = $conn->prepare("UPDATE invitations_evenements SET id_client = ?, noms_maries = ?, date_mariage = ?, heure_ceremonie = ?, lieu_ceremonie = ?, heure_reception = ?, lieu_reception = ?, message_perso = ?, rsvp_contact = ?, fond_invitation_url = ? WHERE id = ?");
            
            // Les types de bind_param : i=integer, s=string. Pour NULL, on passe NULL avec 's'.
            $stmt->bind_param("isssssssssi", 
                $id_client, 
                $noms_maries, 
                $date_mariage, 
                $heure_ceremonie, 
                $lieu_ceremonie, 
                $heure_reception, 
                $lieu_reception, 
                $message_perso, 
                $rsvp_contact, 
                $fond_invitation_filename_to_db,
                $event_id // ID pour la clause WHERE
            );

            if ($stmt->execute()) {
                $message_status = '<div class="message message-succes"><i class="fas fa-check-circle"></i> Événement/invitation mis à jour avec succès !</div>';
                // Recharger les données de l'invitation pour afficher les modifications
                $stmt_reload = $conn->prepare("SELECT * FROM invitations_evenements WHERE id = ?");
                $stmt_reload->bind_param("i", $event_id);
                $stmt_reload->execute();
                $result_reload = $stmt_reload->get_result();
                $event_invitation = $result_reload->fetch_assoc();
                $stmt_reload->close();
            } else {
                $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors de la mise à jour de l\'événement/invitation : ' . $stmt->error . '</div>';
                error_log("Database update error: " . $stmt->error);
            }
            $stmt->close(); // Fermer le statement de mise à jour
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
    <link rel="stylesheet" href="../../RESOURCE/CSS/admin_add_event_invitation.css"> <title>Isabelle Event's | Admin - Modifier Événement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Styles spécifiques pour cette page (si non couverts par admin_add_event_invitation.css) */
        .apercu-image {
            max-width: 200px;
            height: auto;
            display: block;
            margin-top: 10px;
            border: 1px solid #ddd;
            padding: 5px;
            background-color: #f9f9f9;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            align-items: center;
        }
        .message i {
            margin-right: 10px;
            font-size: 1.2em;
        }
        .message-succes {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message-erreur {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group input[type="time"],
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .form-group button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-group button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Modifier un Événement / Invitation</h1>
            <div>
                <a href="../dashboart.php#invitations" class="btn-back"><i class="fas fa-arrow-left"></i> Retour au Dashboard</a>
            </div>
        </header>

        <?php
        if (!empty($message_status)) {
            echo $message_status;
        }
        ?>

        <?php if ($event_invitation): // Afficher le formulaire seulement si l'invitation a été trouvée ?>
            <form action="admin_edit_event_invitation.php?id=<?php echo htmlspecialchars($event_invitation['id']); ?>" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="id_client">ID Client (Optionnel) :</label>
                    <input type="number" id="id_client" name="id_client" value="<?php echo htmlspecialchars($event_invitation['id_client'] ?? ''); ?>" placeholder="Ex: 123">
                    <small>Laissez vide si non lié à un client spécifique pour l'instant.</small>
                </div>
                <div class="form-group">
                    <label for="noms_maries">Noms des mariés / Nom de l'événement :</label>
                    <input type="text" id="noms_maries" name="noms_maries" value="<?php echo htmlspecialchars($event_invitation['noms_maries'] ?? ''); ?>" required placeholder="Ex: Jean & Marie Dupont">
                </div>
                <div class="form-group">
                    <label for="date_mariage">Date de l'événement :</label>
                    <input type="date" id="date_mariage" name="date_mariage" value="<?php echo htmlspecialchars($event_invitation['date_mariage'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="heure_ceremonie">Heure de la cérémonie :</label>
                    <input type="time" id="heure_ceremonie" name="heure_ceremonie" value="<?php echo htmlspecialchars($event_invitation['heure_ceremonie'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="lieu_ceremonie">Lieu de la cérémonie :</label>
                    <input type="text" id="lieu_ceremonie" name="lieu_ceremonie" value="<?php echo htmlspecialchars($event_invitation['lieu_ceremonie'] ?? ''); ?>" required placeholder="Ex: Église Saint-Pierre, Paris">
                </div>
                <div class="form-group">
                    <label for="heure_reception">Heure de la réception (Optionnel) :</label>
                    <input type="time" id="heure_reception" name="heure_reception" value="<?php echo htmlspecialchars($event_invitation['heure_reception'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="lieu_reception">Lieu de la réception (Optionnel) :</label>
                    <input type="text" id="lieu_reception" name="lieu_reception" value="<?php echo htmlspecialchars($event_invitation['lieu_reception'] ?? ''); ?>" placeholder="Ex: Salon des Lumières, Versailles">
                </div>
                <div class="form-group">
                    <label for="message_perso">Message personnel (Optionnel) :</label>
                    <textarea id="message_perso" name="message_perso" rows="5" placeholder="Un petit mot pour les invités..."><?php echo htmlspecialchars($event_invitation['message_perso'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="rsvp_contact">Contact RSVP (Optionnel - email ou numéro de téléphone) :</label>
                    <input type="text" id="rsvp_contact" name="rsvp_contact" value="<?php echo htmlspecialchars($event_invitation['rsvp_contact'] ?? ''); ?>" placeholder="Ex: rsvp@exemple.com ou 0612345678">
                </div>
                <div class="form-group">
                    <label>Image de fond actuelle :</label>
                    <?php
                    // Chemin d'accès web au dossier des images, relatif à admin_edit_event_invitation.php
                    $web_image_path = '../../RESOURCE/USER_IMAGE/';
                    if (!empty($event_invitation['fond_invitation_url']) && file_exists($upload_dir_server . $event_invitation['fond_invitation_url'])):
                    ?>
                        <img src="<?php echo htmlspecialchars($web_image_path . $event_invitation['fond_invitation_url']); ?>" alt="Image de fond actuelle" class="apercu-image">
                    <?php else: ?>
                        <p>Aucune image de fond actuelle ou image introuvable.</p>
                    <?php endif; ?>
                    <label for="fond_invitation_file" style="margin-top: 15px;">Nouvelle image de fond (laisser vide pour conserver l'actuelle) :</label>
                    <input type="file" id="fond_invitation_file" name="fond_invitation_file" accept="image/*">
                </div>
                <div class="form-group">
                    <button type="submit"><i class="fas fa-save"></i> Mettre à jour l'Événement</button>
                </div>
            </form>
        <?php elseif (empty($message_status)): ?>
            <p class="message message-erreur"><i class="fas fa-exclamation-triangle"></i> Impossible de charger l'événement. Veuillez spécifier un ID valide.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$conn->close(); // Ferme la connexion à la base de données
?>