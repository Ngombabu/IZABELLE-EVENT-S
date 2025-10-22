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

// --- Traitement du formulaire d'ajout d'utilisateur ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_complet = trim($_POST['nom_complet'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $role = $_POST['role'] ?? 'client'; // Rôle par défaut

    // Validation basique des entrées
    if (empty($nom_complet) || empty($email) || empty($mot_de_passe) || empty($role)) {
        $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Tous les champs obligatoires doivent être remplis.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Adresse email invalide.</div>';
    } elseif (strlen($mot_de_passe) < 6) {
        $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Le mot de passe doit contenir au moins 6 caractères.</div>';
    } else {
        // Vérifier si l'email existe déjà
        $stmt_check = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Cet email est déjà utilisé.</div>';
        } else {
            // Hacher le mot de passe avant de l'insérer dans la base de données
            $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

            // Préparer et exécuter la requête d'insertion
            $stmt_insert = $conn->prepare("INSERT INTO utilisateurs (nom_complet, email, telephone, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("sssss", $nom_complet, $email, $telephone, $mot_de_passe_hash, $role);

            if ($stmt_insert->execute()) {
                $message = '<div class="message message-succes"><i class="fas fa-check-circle"></i> Utilisateur ajouté avec succès !</div>';
                // Optionnel: Réinitialiser les champs du formulaire après succès
                $_POST = array(); // Vide le tableau POST
            } else {
                $message = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors de l\'ajout de l\'utilisateur : ' . $stmt_insert->error . '</div>';
            }
            $stmt_insert->close();
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
    <link rel="stylesheet" href="../../RESOURCE/CSS/admin_add_user.css">
    <title>Isabelle Event's | Ajouter un Utilisateur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="conteneur-formulaire">
        <header class="entete-formulaire">
            <h1>Ajouter un Nouvel Utilisateur</h1>
            <a href="../dashboart.php" class="bouton-retour">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </header>

        <?php if ($message): ?>
            <?php echo $message; // Affiche le message de succès/erreur ?>
        <?php endif; ?>

        <form action="admin_add_user.php" method="POST">
            <div class="form-groupe">
                <label for="nom_complet">Nom Complet :</label>
                <input type="text" id="nom_complet" name="nom_complet" value="<?php echo htmlspecialchars($_POST['nom_complet'] ?? ''); ?>" required>
            </div>

            <div class="form-groupe">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>

            <div class="form-groupe">
                <label for="telephone">Téléphone :</label>
                <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>">
            </div>

            <div class="form-groupe">
                <label for="mot_de_passe">Mot de Passe :</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>

            <div class="form-groupe">
                <label for="role">Rôle :</label>
                <select id="role" name="role" required>
                    <option value="client" <?php echo (($_POST['role'] ?? '') === 'client') ? 'selected' : ''; ?>>Client</option>
                    <option value="admin" <?php echo (($_POST['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

            <button type="submit" class="bouton-soumettre"><i class="fas fa-user-plus"></i> Ajouter l'Utilisateur</button>
        </form>
    </div>
</body>
</html>
<?php
// Fermer la connexion à la base de données
$conn->close();
?>