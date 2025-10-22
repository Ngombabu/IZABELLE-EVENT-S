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

$message = ''; // Pour afficher les messages de succès ou d'erreur
$user_data = null; // Pour stocker les données de l'utilisateur à modifier

// --- Récupération des données de l'utilisateur à modifier ---
if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];

    $stmt = $conn->prepare("SELECT id, nom_complet, email, telephone, role FROM utilisateurs WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
    } else {
        $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Utilisateur non trouvé.</div>';
        $user_id = null; // Invalide l'ID si non trouvé
    }
    $stmt->close();
} else {
    $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> ID utilisateur manquant.</div>';
    $user_id = null;
}

// --- Traitement du formulaire de modification d'utilisateur ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_data) {
    // On utilise l'ID de l'utilisateur récupéré initialement, pas de $_POST['id'] pour plus de sécurité
    $id_a_modifier = $user_data['id'];

    $nom_complet = trim($_POST['nom_complet'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? ''; // Peut être vide si non modifié
    $role = $_POST['role'] ?? 'client';

    // Validation basique des entrées
    if (empty($nom_complet) || empty($email) || empty($role)) {
        $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Tous les champs obligatoires (Nom, Email, Rôle) doivent être remplis.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Adresse email invalide.</div>';
    } elseif (!empty($mot_de_passe) && strlen($mot_de_passe) < 6) {
        $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Le nouveau mot de passe doit contenir au moins 6 caractères.</div>';
    } else {
        // Vérifier si l'email existe déjà pour un *autre* utilisateur
        $stmt_check = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $stmt_check->bind_param("si", $email, $id_a_modifier);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Cet email est déjà utilisé par un autre utilisateur.</div>';
        } else {
            // Construire la requête de mise à jour
            $sql_update = "UPDATE utilisateurs SET nom_complet = ?, email = ?, telephone = ?, role = ? ";
            $params = [$nom_complet, $email, $telephone, $role];
            $types = "ssss";

            if (!empty($mot_de_passe)) {
                $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                $sql_update .= ", mot_de_passe = ? ";
                $params[] = $mot_de_passe_hash;
                $types .= "s";
            }
            $sql_update .= "WHERE id = ?";
            $params[] = $id_a_modifier;
            $types .= "i";

            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param($types, ...$params); // Utilisation de l'opérateur spread pour les paramètres

            if ($stmt_update->execute()) {
                $message = '<div class="message message-succes"><i class="fas fa-check-circle"></i> Utilisateur mis à jour avec succès !</div>';
                // Mettre à jour les données affichées après la modification réussie
                $user_data['nom_complet'] = $nom_complet;
                $user_data['email'] = $email;
                $user_data['telephone'] = $telephone;
                $user_data['role'] = $role;
            } else {
                $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors de la mise à jour de l\'utilisateur : ' . $stmt_update->error . '</div>';
            }
            $stmt_update->close();
        }
        $stmt_check->close();
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
    <link rel="stylesheet" href="../../RESOURCE/CSS/admin_edit_user.css">
    <title>Isabelle Event's | Modifier Utilisateur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="conteneur-formulaire">
        <header class="entete-formulaire">
            <h1>Modifier l'Utilisateur</h1>
            <a href="../dashboart.php#utilisateurs" class="bouton-retour">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </header>

        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <?php if ($user_data): // Afficher le formulaire seulement si un utilisateur est trouvé ?>
            <form action="admin_edit_user.php?id=<?php echo htmlspecialchars($user_data['id']); ?>" method="POST">
                <div class="form-groupe">
                    <label for="nom_complet">Nom Complet :</label>
                    <input type="text" id="nom_complet" name="nom_complet" value="<?php echo htmlspecialchars($user_data['nom_complet']); ?>" required>
                </div>

                <div class="form-groupe">
                    <label for="email">Email :</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                </div>

                <div class="form-groupe">
                    <label for="telephone">Téléphone :</label>
                    <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($user_data['telephone'] ?? ''); ?>">
                </div>

                <div class="form-groupe">
                    <label for="mot_de_passe">Nouveau Mot de Passe (laisser vide si inchangé) :</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe">
                    <small>Minimum 6 caractères si vous le modifiez.</small>
                </div>

                <div class="form-groupe">
                    <label for="role">Rôle :</label>
                    <select id="role" name="role" required>
                        <option value="client" <?php echo ($user_data['role'] === 'client') ? 'selected' : ''; ?>>Client</option>
                        <option value="admin" <?php echo ($user_data['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <button type="submit" class="bouton-soumettre"><i class="fas fa-save"></i> Enregistrer les Modifications</button>
            </form>
        <?php elseif (!$message): // Si aucun message d'erreur spécifique mais pas d'utilisateur trouvé ?>
             <p class="message message-erreur">Impossible de charger les données de l'utilisateur.</p>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
// Fermer la connexion à la base de données
$conn->close();
?>