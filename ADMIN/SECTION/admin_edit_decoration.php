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
$decoration = null; // Variable pour stocker les données de la décoration à éditer

// --- Récupérer l'ID de la décoration à modifier depuis l'URL ---
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $decoration_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($decoration_id === false) {
        $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> ID de décoration invalide.</div>';
    } else {
        // Préparer et exécuter la requête pour récupérer les informations de la décoration
        $stmt = $conn->prepare("SELECT * FROM decorations WHERE id = ?");
        $stmt->bind_param("i", $decoration_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $decoration = $result->fetch_assoc();
        $stmt->close();

        if (!$decoration) {
            $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Décoration non trouvée.</div>';
        }
    }
} else {
    $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Aucun ID de décoration spécifié pour la modification.</div>';
}

// --- Traitement du formulaire de modification de décoration ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $decoration) {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $style = trim($_POST['style'] ?? '');
    $image_url = $decoration['image_url']; // Par défaut, conserver l'ancienne image

    // Validation basique des entrées
    if (empty($titre) || empty($description) || empty($style)) {
        $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Tous les champs obligatoires (Titre, Description, Style) doivent être remplis.</div>';
    } else {
        // --- Gestion de l'upload de la nouvelle image (si présente) ---
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_name = $_FILES['image']['tmp_name'];
            $file_name = uniqid() . '_' . basename($_FILES['image']['name']); // Nom de fichier unique
            $file_destination = $upload_dir . $file_name;
            $file_destination1 = $file_name;

            // Vérifier le type de fichier
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file_tmp_name);
            finfo_close($finfo);

            if (in_array($mime_type, $allowed_types)) {
                // Supprimer l'ancienne image si elle existe et n'est pas vide
                if (!empty($decoration['image_url'])) {
                    $old_image_path = $upload_dir . $decoration['image_url'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path); // Supprime l'ancien fichier
                    }
                }

                if (move_uploaded_file($file_tmp_name, $file_destination)) {
                    $image_url = $file_destination1; // Nouveau chemin relatif pour la base de données
                } else {
                    $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors du déplacement du nouveau fichier téléchargé.</div>';
                }
            } else {
                $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Type de nouveau fichier non autorisé. Seules les images JPEG, PNG, GIF et WEBP sont acceptées.</div>';
            }
        } else if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
             // Gérer les erreurs d'upload autres que l'absence de fichier
            $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur d\'upload de la nouvelle image : ' . $_FILES['image']['error'] . '</div>';
        }


        // Si pas d'erreur lors du traitement de l'image (ou si aucune nouvelle image n'a été fournie)
        if (empty($message)) {
            // Préparer et exécuter la requête de mise à jour
            $stmt = $conn->prepare("UPDATE decorations SET titre = ?, description = ?, image_url = ?, style = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $titre, $description, $image_url, $style, $decoration_id);

            if ($stmt->execute()) {
                $message = '<div class="message message-succes"><i class="fas fa-check-circle"></i> Décoration mise à jour avec succès !</div>';
                // Recharger les données de la décoration pour afficher les modifications
                $stmt_reload = $conn->prepare("SELECT * FROM decorations WHERE id = ?");
                $stmt_reload->bind_param("i", $decoration_id);
                $stmt_reload->execute();
                $result_reload = $stmt_reload->get_result();
                $decoration = $result_reload->fetch_assoc();
                $stmt_reload->close();
            } else {
                $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors de la mise à jour de la décoration : ' . $stmt->error . '</div>';
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
    <link rel="stylesheet" href="../../RESOURCE/CSS/admin_add_decoration.css"> <title>Isabelle Event's | Modifier Décoration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Styles spécifiques à cette page si nécessaire */
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
    </style>
</head>
<body>
    <div class="conteneur-formulaire">
        <header class="entete-formulaire">
            <h1>Modifier une Décoration</h1>
            <a href="../dashboart.php#decorations" class="bouton-retour">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </header>

        <?php if ($message): ?>
            <?php echo $message; // Affiche le message de succès/erreur ?>
        <?php endif; ?>

        <?php if ($decoration): // Afficher le formulaire seulement si la décoration a été trouvée ?>
            <form action="admin_edit_decoration.php?id=<?php echo htmlspecialchars($decoration['id']); ?>" method="POST" enctype="multipart/form-data">
                <div class="form-groupe">
                    <label for="titre">Titre de la Décoration :</label>
                    <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($decoration['titre'] ?? ''); ?>" required>
                </div>

                <div class="form-groupe">
                    <label for="description">Description :</label>
                    <textarea id="description" name="description" required><?php echo htmlspecialchars($decoration['description'] ?? ''); ?></textarea>
                </div>

                <div class="form-groupe">
                    <label>Image actuelle :</label>
                    <?php if (!empty($decoration['image_url'])): ?>
                        <img src="<?php echo htmlspecialchars($upload_dir . $decoration['image_url']); ?>" alt="Image actuelle" class="apercu-image">
                    <?php else: ?>
                        <p>Aucune image définie.</p>
                    <?php endif; ?>
                    <label for="image" style="margin-top: 15px;">Nouvelle image (laisser vide pour conserver l'actuelle) :</label>
                    <input type="file" id="image" name="image" accept="image/jpeg, image/png, image/gif, image/webp">
                    <small>Formats acceptés : JPG, PNG, GIF, WEBP.</small>
                </div>

                <div class="form-groupe">
                    <label for="style">Style de Décoration :</label>
                    <input type="text" id="style" name="style" value="<?php echo htmlspecialchars($decoration['style'] ?? ''); ?>" placeholder="Ex: Bohème, Classique, Rustique" required>
                </div>

                <button type="submit" class="bouton-soumettre"><i class="fas fa-save"></i> Mettre à jour la Décoration</button>
            </form>
        <?php elseif (empty($message)): ?>
            <p class="message message-erreur"><i class="fas fa-exclamation-triangle"></i> Impossible de charger la décoration. Veuillez vérifier l'ID fourni.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
// Fermer la connexion à la base de données
$conn->close();
?>