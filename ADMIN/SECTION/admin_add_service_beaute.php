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
$base_logo = '../../RESOURCE/SITE_IMAGE/logo.jpeg'; // Chemin corrigé pour le logo

// Définir le répertoire d'upload pour les fichiers (chemin sur le serveur)
// Ce chemin est relatif au fichier PHP (admin_add_service_beaute.php)
$upload_dir_server = '../../RESOURCE/USER_IMAGE/'; // Assurez-vous qu'il se termine par un slash !

// Initialisation du message de statut
$message_status = '';

// --- Traitement du formulaire d'ajout de service de beauté ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_service = trim($_POST['nom_service'] ?? '');
    $description_service = trim($_POST['description_service'] ?? '');
    $prix_service = floatval($_POST['prix_service'] ?? 0);

    // Validation basique
    if (empty($nom_service) || empty($description_service) || $prix_service <= 0) {
        $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Veuillez remplir tous les champs obligatoires et assurez-vous que le prix est valide.</div>';
    } else {
        // Traitement de l'upload de l'image
        $image_name_original = $_FILES['image_service']['name'] ?? '';
        $image_tmp_name = $_FILES['image_service']['tmp_name'] ?? '';
        $image_error = $_FILES['image_service']['error'] ?? UPLOAD_ERR_NO_FILE;

        $image_filename_to_db = ''; // Variable pour stocker le nom du fichier à mettre en DB

        if ($image_error === UPLOAD_ERR_OK) {
            $image_extension = pathinfo($image_name_original, PATHINFO_EXTENSION);
            $new_image_name_unique = uniqid('service_', true) . '.' . $image_extension; // Nom unique pour l'image
            $destination_path_on_server = $upload_dir_server . $new_image_name_unique;

            // Vérifier le type MIME du fichier pour une meilleure sécurité
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $image_tmp_name);
            finfo_close($finfo);

            if (in_array($mime_type, $allowed_types)) {
                if (move_uploaded_file($image_tmp_name, $destination_path_on_server)) {
                    // L'image a été téléchargée avec succès
                    // **Correction ici :** Stocker SEULEMENT le nom du fichier
                    $image_filename_to_db = $new_image_name_unique;
                } else {
                    $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors du déplacement du fichier téléchargé.</div>';
                }
            } else {
                $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Type de fichier non autorisé. Seules les images JPEG, PNG, GIF et WEBP sont acceptées.</div>';
            }
        } elseif ($image_error === UPLOAD_ERR_NO_FILE) {
             $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Veuillez sélectionner une image pour le service.</div>';
        } else {
            $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Une erreur inconnue est survenue lors du téléchargement de l\'image du service. Code: ' . $image_error . '</div>';
        }

        if (empty($message_status)) { // Si aucune erreur d'upload ou de validation
            // Insérer les données dans la base de données
            // Assurez-vous que votre table s'appelle 'services_beaute' et a les colonnes correspondantes
            // NOUVEAU : Ajout de 'disponible' dans l'INSERT et dans bind_param
            $stmt = $conn->prepare("INSERT INTO services_beaute (nom, description, prix, image_url) VALUES (?, ?, ?, ?)");
            // Correction des types : 's' pour nom, 's' pour description, 'd' pour prix, 's' pour disponibilité, 's' pour image_url
            $stmt->bind_param("ssds", $nom_service, $description_service, $prix_service, $image_filename_to_db);

            if ($stmt->execute()) {
                $message_status = '<div class="message message-succes"><i class="fas fa-check-circle"></i> Service de beauté ajouté avec succès !</div>';
                // Optionnel : Réinitialiser les champs du formulaire après un ajout réussi
                // $nom_service = $description_service = ''; $prix_service = 0; $disponibilite_service = 'disponible';
            } else {
                $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors de l\'ajout du service de beauté : ' . $stmt->error . '</div>';
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
    <link rel="stylesheet" href="../../RESOURCE/CSS/admin_add_service_beaute.css">
    <title>Isabelle Event's | Admin - Ajouter Service Beauté</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Ajouter un Nouveau Service de Beauté</h1>
            <div>
                <a href="../dashboart.php#services-beaute" class="btn-back"><i class="fas fa-arrow-left"></i> Retour au Dashboard</a>
            </div>
        </header>

        <?php
        // Afficher les messages de statut (succès/erreur) pour l'ajout de service
        if (!empty($message_status)) {
            echo $message_status;
        }
        ?>

        <form action="admin_add_service_beaute.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nom_service">Nom du service :</label>
                <input type="text" id="nom_service" name="nom_service" required>
            </div>
            <div class="form-group">
                <label for="description_service">Description :</label>
                <textarea id="description_service" name="description_service" required></textarea>
            </div>
            <div class="form-group">
                <label for="prix_service">Prix du service :</label>
                <input type="number" id="prix_service" name="prix_service" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label for="image_service">Image du service :</label>
                <input type="file" id="image_service" name="image_service" accept="image/*" required>
            </div>
            <div class="form-group">
                <button type="submit">Ajouter le Service</button>
            </div>
        </form>
    </div>
</body>
</html>
<?php
$conn->close(); // Ferme la connexion à la base de données
?>