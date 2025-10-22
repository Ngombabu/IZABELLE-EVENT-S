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
    header('Location: ../PUBLIC/login.php'); // Rediriger vers la page de connexion publique
    exit();
}

// Variables pour les chemins d'accès aux ressources
$base_css = '../RESOURCE/CSS/style.css';
$base_logo = '../RESOURCE/SITE_IMAGE/logo.jpeg';

// Initialisation du message de statut
$message_status = '';

// --- Traitement du formulaire d'ajout de réalisation ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre_realisation = trim($_POST['titre_realisation'] ?? '');
    $description_realisation = trim($_POST['description_realisation'] ?? '');
    $date_realisation = trim($_POST['date_realisation'] ?? ''); // Champ pour la date de réalisation

    // Validation basique
    if (empty($titre_realisation) || empty($description_realisation) || empty($date_realisation)) {
        $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Veuillez remplir tous les champs obligatoires pour la réalisation.</div>';
    } else {
        // Traitement de l'upload de l'image
        $image_name = $_FILES['image_realisation']['name'] ?? '';
        $image_tmp_name = $_FILES['image_realisation']['tmp_name'] ?? '';
        $image_size = $_FILES['image_realisation']['size'] ?? 0;
        $image_error = $_FILES['image_realisation']['error'] ?? UPLOAD_ERR_NO_FILE;

        $upload_dir = '../../RESOURCE/USER_IMAGE/'; // Dossier où stocker les images des réalisations
        // Assurez-vous que ce dossier existe et est accessible en écriture par le serveur web
        $image_path = '';

        if ($image_error === UPLOAD_ERR_OK) {
            $image_extension = pathinfo($image_name, PATHINFO_EXTENSION);
            $new_image_name = uniqid('realisation_', true) . '.' . $image_extension; // Nom unique pour l'image
            $image_path = $upload_dir . $new_image_name;

            if (move_uploaded_file($image_tmp_name, $image_path)) {
                // L'image a été téléchargée avec succès
            } else {
                $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors du téléchargement de l\'image de la réalisation.</div>';
            }
        } elseif ($image_error === UPLOAD_ERR_NO_FILE) {
             $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Veuillez sélectionner une image pour la réalisation.</div>';
        } else {
            $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Une erreur inconnue est survenue lors du téléchargement de l\'image de la réalisation. Code: ' . $image_error . '</div>';
        }

        if (empty($message_status)) { // Si aucune erreur d'upload
            // Insérer les données dans la base de données
            // Assurez-vous que votre table s'appelle 'realisations' et a les colonnes correspondantes
            $stmt = $conn->prepare("INSERT INTO realisations (titre, description, date_realisation, image_url) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $titre_realisation, $description_realisation, $date_realisation, $image_path);

            if ($stmt->execute()) {
                $message_status = '<div class="message message-succes"><i class="fas fa-check-circle"></i> Réalisation ajoutée avec succès !</div>';
                // Optionnel : Réinitialiser les champs du formulaire après un ajout réussi
                // $titre_realisation = $description_realisation = $date_realisation = '';
            } else {
                $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors de l\'ajout de la réalisation : ' . $stmt->error . '</div>';
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
    <link rel="icon" type="image/x-icon" href="">
    <link rel="stylesheet" href="../../RESOURCE/CSS/admin_add_realisation.css">
    <title>Isabelle Event's | Admin - Ajouter Réalisation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Ajouter une Nouvelle Réalisation</h1>
            <div>
                <a href="../dashboart.php#realisations" class="btn-back"><i class="fas fa-arrow-left"></i> Retour au Dashboard</a>
            </div>
        </header>

        <?php
        // Afficher les messages de statut (succès/erreur) pour l'ajout de réalisation
        if (!empty($message_status)) {
            echo $message_status;
        }
        ?>

        <form action="admin_add_realisation.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titre_realisation">Titre de la réalisation :</label>
                <input type="text" id="titre_realisation" name="titre_realisation" required>
            </div>
            <div class="form-group">
                <label for="description_realisation">Description :</label>
                <textarea id="description_realisation" name="description_realisation" required></textarea>
            </div>
            <div class="form-group">
                <label for="date_realisation">Date de la réalisation :</label>
                <input type="date" id="date_realisation" name="date_realisation" required>
            </div>
            <div class="form-group">
                <label for="image_realisation">Image de la réalisation :</label>
                <input type="file" id="image_realisation" name="image_realisation" accept="image/*" required>
            </div>
            <div class="form-group">
                <button type="submit">Ajouter la Réalisation</button>
            </div>
        </form>
    </div>
</body>
</html>
<?php
$conn->close(); // Ferme la connexion à la base de données
?>