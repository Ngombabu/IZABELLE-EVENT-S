<?php
session_start(); // Démarre la session

// --- IMPORTANT : Afficher les erreurs PHP pour le débogage (À retirer en production !) ---
// À retirer une fois le site en production pour des raisons de sécurité et de propreté.
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
$base_logo = '../../RESOURCE/SITE_IMAGE/logo.jpeg';
$upload_dir_server = '../../RESOURCE/USER_IMAGE/'; // Dossier où stocker les images (chemin sur le serveur)

// Initialisation du message de statut
$message_status = '';
$service = null; // Variable pour stocker les données du service à éditer

// --- Récupérer l'ID du service à modifier depuis l'URL ---
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $service_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($service_id === false) {
        $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> ID de service invalide.</div>';
    } else {
        // Préparer et exécuter la requête pour récupérer les informations du service
        // Cible la table 'services_beaute'
        $stmt = $conn->prepare("SELECT * FROM services_beaute WHERE id = ?");
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $service = $result->fetch_assoc();
        $stmt->close();

        if (!$service) {
            $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Service non trouvé.</div>';
        }
    }
} else {
    $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Aucun ID de service spécifié pour la modification.</div>';
}

// --- Traitement du formulaire de modification de service ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $service) {
    // Utilisation de l'opérateur de coalescence ?? pour éviter les avertissements "Undefined array key"
    $nom_service = trim($_POST['nom_service'] ?? '');
    $description_service = trim($_POST['description_service'] ?? '');
    // Le prix est un VARCHAR dans votre DB, donc on le traite comme une chaîne
    $prix_service = trim($_POST['prix_service'] ?? '');

    // La colonne 'disponible' n'existe pas, donc nous ne la traitons pas ici.

    // Par défaut, le nom de fichier de l'image reste celui qui est déjà en DB
    $image_filename_to_db = $service['image_url'];

    // Validation basique des champs requis
    if (empty($nom_service) || empty($description_service) || empty($prix_service)) {
        $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Veuillez remplir tous les champs obligatoires (Nom, Description, Prix).</div>';
    } else {
        // Traitement de l'upload de la nouvelle image (si présente)
        $image_name_original = $_FILES['image_service']['name'] ?? '';
        $image_tmp_name = $_FILES['image_service']['tmp_name'] ?? '';
        $image_error = $_FILES['image_service']['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($image_error === UPLOAD_ERR_OK) {
            $image_extension = pathinfo($image_name_original, PATHINFO_EXTENSION);
            $new_image_name_unique = uniqid('service_', true) . '.' . $image_extension; // Nom unique pour la nouvelle image
            $destination_path_on_server = $upload_dir_server . $new_image_name_unique;

            // Vérifier le type MIME du fichier pour une meilleure sécurité
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $image_tmp_name);
            finfo_close($finfo);

            if (in_array($mime_type, $allowed_types)) {
                // Supprimer l'ancienne image si elle existe et n'est pas vide
                $old_image_path_on_server = $upload_dir_server . $service['image_url'];
                if (!empty($service['image_url']) && file_exists($old_image_path_on_server)) {
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
            // Gérer les erreurs d'upload autres que l'absence de fichier
            $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur d\'upload de la nouvelle image : ' . $image_error . '</div>';
        }

        if (empty($message_status)) { // Si aucune erreur d'upload ou de validation
            // Préparer et exécuter la requête de mise à jour pour 'services_beaute'
            // NOTE : La colonne 'disponible' a été supprimée de la requête et de bind_param
            $stmt = $conn->prepare("UPDATE services_beaute SET nom = ?, description = ?, prix = ?, image_url = ? WHERE id = ?");
            // Les types : s (nom), s (description), s (prix - car VARCHAR), s (image_url), i (id)
            $stmt->bind_param("ssssi", $nom_service, $description_service, $prix_service, $image_filename_to_db, $service_id);

            if ($stmt->execute()) {
                $message_status = '<div class="message message-succes"><i class="fas fa-check-circle"></i> Service de beauté mis à jour avec succès !</div>';
                // Recharger les données du service pour afficher les modifications
                $stmt_reload = $conn->prepare("SELECT * FROM services_beaute WHERE id = ?");
                $stmt_reload->bind_param("i", $service_id);
                $stmt_reload->execute();
                $result_reload = $stmt_reload->get_result();
                $service = $result_reload->fetch_assoc(); // Met à jour la variable $service avec les nouvelles données
                $stmt_reload->close();
            } else {
                $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors de la mise à jour du service de beauté : ' . $stmt->error . '</div>';
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
    <link rel="stylesheet" href="../../RESOURCE/CSS/admin_add_service_beaute.css">
    <title>Isabelle Event's | Admin - Modifier Service Beauté</title>
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
        .form-group select { /* Gardé 'select' au cas où vous auriez d'autres select, mais 'disponibilite_service' n'est plus là */
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
            <h1>Modifier un Service de Beauté</h1>
            <div>
                <a href="../dashboart.php#services-beaute" class="btn-back"><i class="fas fa-arrow-left"></i> Retour au Dashboard</a>
            </div>
        </header>

        <?php
        if (!empty($message_status)) {
            echo $message_status;
        }
        ?>

        <?php if ($service): // Afficher le formulaire seulement si le service a été trouvé ?>
            <form action="admin_edit_service_beaute.php?id=<?php echo htmlspecialchars($service['id']); ?>" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nom_service">Nom du service :</label>
                    <input type="text" id="nom_service" name="nom_service" value="<?php echo htmlspecialchars($service['nom'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description_service">Description :</label>
                    <textarea id="description_service" name="description_service" required><?php echo htmlspecialchars($service['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="prix_service">Prix du service :</label>
                    <input type="text" id="prix_service" name="prix_service" value="<?php echo htmlspecialchars($service['prix'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Image actuelle :</label>
                    <?php
                    // Chemin d'accès web au dossier des images, relatif à admin_edit_service_beaute.php
                    $web_image_path = '../../RESOURCE/USER_IMAGE/';
                    if (!empty($service['image_url']) && file_exists($upload_dir_server . $service['image_url'])):
                    ?>
                        <img src="<?php echo htmlspecialchars($web_image_path . $service['image_url']); ?>" alt="Image actuelle du service" class="apercu-image">
                    <?php else: ?>
                        <p>Aucune image actuelle ou image introuvable.</p>
                    <?php endif; ?>
                    <label for="image_service" style="margin-top: 15px;">Nouvelle image (laisser vide pour conserver l'actuelle) :</label>
                    <input type="file" id="image_service" name="image_service" accept="image/*">
                </div>
                <div class="form-group">
                    <button type="submit"><i class="fas fa-save"></i> Mettre à jour le Service</button>
                </div>
            </form>
        <?php elseif (empty($message_status)): ?>
            <p class="message message-erreur"><i class="fas fa-exclamation-triangle"></i> Impossible de charger le service. Veuillez spécifier un ID valide.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$conn->close(); // Ferme la connexion à la base de données
?>