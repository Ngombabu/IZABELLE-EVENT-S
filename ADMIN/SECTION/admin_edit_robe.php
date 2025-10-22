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

// Définir le répertoire d'upload pour les fichiers (chemin sur le serveur)
// Ce chemin est relatif au fichier PHP (admin_edit_robe.php)
$upload_dir_server = '../../RESOURCE/USER_IMAGE/'; // Assurez-vous qu'il se termine par un slash !

// Initialisation du message de statut
$message_status = '';
$robe = null; // Variable pour stocker les données de la robe à éditer

// --- Récupérer l'ID de la robe à modifier depuis l'URL ---
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $robe_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($robe_id === false) {
        $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> ID de robe invalide.</div>';
    } else {
        // Préparer et exécuter la requête pour récupérer les informations de la robe
        $stmt = $conn->prepare("SELECT * FROM robes WHERE id = ?");
        $stmt->bind_param("i", $robe_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $robe = $result->fetch_assoc();
        $stmt->close();

        if (!$robe) {
            $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Robe non trouvée.</div>';
        }
    }
} else {
    $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Aucun ID de robe spécifié pour la modification.</div>';
}

// --- Traitement du formulaire de modification de robe ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $robe) {
    $nom_robe = trim($_POST['nom_robe'] ?? '');
    $description_robe = trim($_POST['description_robe'] ?? '');
    $prix_location = floatval($_POST['prix_location'] ?? 0);
    // Nouveau : Récupération de prix_vente, le convertir en float ou null si vide
    $prix_vente = !empty($_POST['prix_vente']) ? floatval($_POST['prix_vente']) : null;
    $disponibilite = $_POST['disponibilite'] ?? 'non_disponible';

    // Par défaut, le nom de fichier de l'image reste celui qui est déjà en DB
    $image_filename_to_db = $robe['image_url'];

    // Validation basique
    if (empty($nom_robe) || empty($description_robe) || $prix_location <= 0) {
        $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Veuillez remplir tous les champs obligatoires et assurez-vous que le prix de location est valide.</div>';
    } else {
        // Traitement de l'upload de la nouvelle image (si présente)
        $image_name_original = $_FILES['image_robe']['name'] ?? '';
        $image_tmp_name = $_FILES['image_robe']['tmp_name'] ?? '';
        $image_error = $_FILES['image_robe']['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($image_error === UPLOAD_ERR_OK) {
            $image_extension = pathinfo($image_name_original, PATHINFO_EXTENSION);
            $new_image_name_unique = uniqid('robe_', true) . '.' . $image_extension; // Nom unique pour la nouvelle image
            $destination_path_on_server = $upload_dir_server . $new_image_name_unique;

            // Vérifier le type MIME du fichier pour une meilleure sécurité
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $image_tmp_name);
            finfo_close($finfo);

            if (in_array($mime_type, $allowed_types)) {
                // Supprimer l'ancienne image si elle existe et n'est pas vide
                // Construire le chemin complet de l'ancienne image sur le serveur
                $old_image_path_on_server = $upload_dir_server . $robe['image_url'];
                if (!empty($robe['image_url']) && file_exists($old_image_path_on_server)) {
                    unlink($old_image_path_on_server); // Supprime l'ancien fichier
                }

                if (move_uploaded_file($image_tmp_name, $destination_path_on_server)) {
                    $image_filename_to_db = $new_image_name_unique; // Nouveau nom de fichier pour la base de données
                } else {
                    $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors du déplacement du nouveau fichier téléchargé.</div>';
                }
            } else {
                $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Type de nouveau fichier non autorisé. Seules les images JPEG, PNG, GIF et WEBP sont acceptées.</div>';
            }
        } elseif ($image_error !== UPLOAD_ERR_NO_FILE) {
            // Gérer les erreurs d'upload autres que l'absence de fichier (si le champ n'était pas obligatoire)
            $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur d\'upload de la nouvelle image : ' . $image_error . '</div>';
        }

        if (empty($message_status)) { // Si aucune erreur d'upload ou de validation
            // Préparer et exécuter la requête de mise à jour
            // NOUVEAU : Ajout de prix_vente dans la requête SQL et bind_param
            $stmt = $conn->prepare("UPDATE robes SET nom = ?, description = ?, prix_location = ?, prix_vente = ?, disponible = ?, image_url = ? WHERE id = ?");
            // Les types : ssddssi (s: nom, s: desc, d: prix_loc, d: prix_vente, s: dispo, s: image_url, i: id)
            $stmt->bind_param("ssddssi", $nom_robe, $description_robe, $prix_location, $prix_vente, $disponibilite, $image_filename_to_db, $robe_id);

            if ($stmt->execute()) {
                $message_status = '<div class="message message-succes"><i class="fas fa-check-circle"></i> Robe mise à jour avec succès !</div>';
                // Recharger les données de la robe pour afficher les modifications
                $stmt_reload = $conn->prepare("SELECT * FROM robes WHERE id = ?");
                $stmt_reload->bind_param("i", $robe_id);
                $stmt_reload->execute();
                $result_reload = $stmt_reload->get_result();
                $robe = $result_reload->fetch_assoc();
                $stmt_reload->close();
            } else {
                $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors de la mise à jour de la robe : ' . $stmt->error . '</div>';
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
    <link rel="stylesheet" href="../../RESOURCE/CSS/admin_add_robe.css">
    <title>Isabelle Event's | Admin - Modifier Robe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Styles spécifiques pour cette page */
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
        .form-group textarea,
        .form-group select {
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
            <h1>Modifier une Robe</h1>
            <div>
                <a href="../dashboart.php#robes" class="btn-back"><i class="fas fa-arrow-left"></i> Retour au Dashboard</a>
            </div>
        </header>

        <?php
        if (!empty($message_status)) {
            echo $message_status;
        }
        ?>

        <?php if ($robe): ?>
            <form action="admin_edit_robe.php?id=<?php echo htmlspecialchars($robe['id']); ?>" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nom_robe">Nom de la robe :</label>
                    <input type="text" id="nom_robe" name="nom_robe" value="<?php echo htmlspecialchars($robe['nom'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description_robe">Description :</label>
                    <textarea id="description_robe" name="description_robe" required><?php echo htmlspecialchars($robe['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="prix_location">Prix de location :</label>
                    <input type="number" id="prix_location" name="prix_location" step="0.01" min="0" value="<?php echo htmlspecialchars($robe['prix_location'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="prix_vente">Prix de vente (optionnel) :</label>
                    <input type="number" id="prix_vente" name="prix_vente" step="0.01" min="0" value="<?php echo htmlspecialchars($robe['prix_vente'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="disponibilite">Disponibilité :</label>
                    <select id="disponibilite" name="disponibilite">
                        <option value="disponible" <?php echo ($robe['disponible'] === 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                        <option value="non_disponible" <?php echo ($robe['disponible'] === 'non_disponible') ? 'selected' : ''; ?>>Non Disponible</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Image actuelle :</label>
                    <?php
                    // Chemin d'accès web au dossier des images, relatif à admin_edit_robe.php
                    $web_image_path = '../../RESOURCE/USER_IMAGE/';
                    if (!empty($robe['image_url']) && file_exists($upload_dir_server . $robe['image_url'])):
                    ?>
                        <img src="<?php echo htmlspecialchars($web_image_path . $robe['image_url']); ?>" alt="Image actuelle de la robe" class="apercu-image">
                    <?php else: ?>
                        <p>Aucune image actuelle ou image introuvable.</p>
                    <?php endif; ?>
                    <label for="image_robe" style="margin-top: 15px;">Nouvelle image (laisser vide pour conserver l'actuelle) :</label>
                    <input type="file" id="image_robe" name="image_robe" accept="image/*">
                </div>
                <div class="form-group">
                    <button type="submit"><i class="fas fa-save"></i> Mettre à jour la Robe</button>
                </div>
            </form>
        <?php elseif (empty($message_status)): ?>
            <p class="message message-erreur"><i class="fas fa-exclamation-triangle"></i> Impossible de charger la robe. Veuillez spécifier un ID valide.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$conn->close(); // Ferme la connexion à la base de données
?>