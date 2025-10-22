<?php
session_start(); // Démarre la session

// --- IMPORTANT : Afficher les erreurs PHP pour le débogage (À retirer en production !) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN IMPORTANT ---

// Ajustez le chemin vers votre fichier de connexion à la base de données
include('../../INCLUDE/data.php'); // Assurez-vous que ce chemin est correct

// --- Sécurité : Vérifier si l'utilisateur est connecté et a le rôle admin ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../PUBLIC/login.php'); // Rediriger vers la page de connexion si non connecté ou non admin
    exit();
}

// Variables pour les chemins d'accès aux ressources depuis le dossier ADMIN
$base_css = '../RESOURCE/CSS/style.css';
$base_logo = '../RESOURCE/SITE_IMAGE/logo.jpeg'; // Assurez-vous que ce chemin est correct
$upload_dir = '../../RESOURCE/USER_IMAGE/'; // Dossier où les images seront stockées

$message = ''; // Pour afficher les messages de succès ou d'erreur

// --- Traitement du formulaire d'ajout de décoration ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $style = trim($_POST['style'] ?? '');
    $image_url = null; // Sera défini si une image est téléchargée

    // Validation basique des entrées
    if (empty($titre) || empty($description) || empty($style)) {
        $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Tous les champs obligatoires (Titre, Description, Style) doivent être remplis.</div>';
    } else {
        // --- Gestion de l'upload de l'image ---
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_name = $_FILES['image']['tmp_name'];
            $file_name = uniqid() . '_' . basename($_FILES['image']['name']); // Nom de fichier unique
            $file_destination = $upload_dir . $file_name;
            $file_destination1= $file_name;

            // Vérifier le type de fichier (optionnel mais recommandé)
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file_tmp_name);
            finfo_close($finfo);

            if (in_array($mime_type, $allowed_types)) {
                if (move_uploaded_file($file_tmp_name, $file_destination)) {
                    $image_url = $file_destination1; // Chemin relatif pour la base de données
                } else {
                    $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors du déplacement du fichier téléchargé.</div>';
                }
            } else {
                $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Type de fichier non autorisé. Seules les images JPEG, PNG, GIF et WEBP sont acceptées.</div>';
            }
        } else if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
             // Gérer les erreurs d'upload autres que l'absence de fichier
            $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur d\'upload : ' . $_FILES['image']['error'] . '</div>';
        }


        // Si pas d'erreur d'upload (ou si aucune image n'était obligatoire)
        if (empty($message)) {
            // Préparer et exécuter la requête d'insertion
            // Note: date_ajout sera automatiquement gérée par la base de données si définie comme CURRENT_TIMESTAMP
            $stmt = $conn->prepare("INSERT INTO decorations (titre, description, image_url, style) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $titre, $description, $image_url, $style);

            if ($stmt->execute()) {
                $message = '<div class="message message-succes"><i class="fas fa-check-circle"></i> Décoration ajoutée avec succès !</div>';
                // Optionnel: Réinitialiser les champs du formulaire après succès
                $_POST = array();
                $_FILES = array(); // Important pour les fichiers
            } else {
                $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors de l\'ajout de la décoration : ' . $stmt->error . '</div>';
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
    <link rel="stylesheet" href="../../RESOURCE/CSS/admin_add_decoration.css">
    <title>Isabelle Event's | Ajouter Décoration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="conteneur-formulaire">
        <header class="entete-formulaire">
            <h1>Ajouter une Nouvelle Décoration</h1>
            <a href="../dashboart.php#decorations" class="bouton-retour">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </header>

        <?php if ($message): ?>
            <?php echo $message; // Affiche le message de succès/erreur ?>
        <?php endif; ?>

        <form action="admin_add_decoration.php" method="POST" enctype="multipart/form-data">
            <div class="form-groupe">
                <label for="titre">Titre de la Décoration :</label>
                <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($_POST['titre'] ?? ''); ?>" required>
            </div>

            <div class="form-groupe">
                <label for="description">Description :</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-groupe">
                <label for="image">Image de la Décoration :</label>
                <input type="file" id="image" name="image" accept="image/jpeg, image/png, image/gif, image/webp" required>
                <small>Formats acceptés : JPG, PNG, GIF, WEBP.</small>
            </div>

            <div class="form-groupe">
                <label for="style">Style de Décoration :</label>
                <input type="text" id="style" name="style" value="<?php echo htmlspecialchars($_POST['style'] ?? ''); ?>" placeholder="Ex: Bohème, Classique, Rustique" required>
            </div>

            <button type="submit" class="bouton-soumettre"><i class="fas fa-plus-circle"></i> Ajouter la Décoration</button>
        </form>
    </div>
</body>
</html>
<?php
// Fermer la connexion à la base de données
$conn->close();
?>