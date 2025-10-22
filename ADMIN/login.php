<?php
session_start();
require_once '../INCLUDE/data.php';
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['utilisateur']));
    $password = htmlspecialchars(trim($_POST['mot-de-passe']));
    if (empty($username) || empty($password)) {
        $error_message = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $conn->prepare("SELECT id, nom_complet, mot_de_passe, role FROM utilisateurs WHERE email = ? OR nom_complet = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['mot_de_passe'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nom_complet'];
                    $_SESSION['user_role'] = $user['role'];

                    if ($_SESSION['user_role'] === 'admin') {
                        header('Location: ../ADMIN/dashboart.php');
                        exit();
                    } else {
                        header('Location: ../PUBLIC/espace_client.php');
                        exit();
                    }
                } else {
                    $error_message = "Nom d'utilisateur ou mot de passe incorrect.";
                }
            } else {
                $error_message = "Nom d'utilisateur ou mot de passe incorrect.";
            }
            $stmt->close();
        } else {
            $error_message = "Erreur interne du serveur. Veuillez réessayer.";
            error_log("Erreur de préparation de la requête de connexion : " . $conn->error);
        }
    }
    $conn->close();
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
        <link rel="stylesheet" href="../RESOURCE/CSS/login.css">
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <title>Isabelle Event's|Connexion</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>
    <body>
        <div class="conteneur-connexion">
            <h2 class="titre-connexion">Connexion</h2>
            <img src="../RESOURCE/SITE_IMAGE/logo.jpeg" alt="logo_isabelle_events" style="width: 400px; height: 400px; position: absolute; left: 0; top: 50%; transform: translateY(-50%); opacity: 0.05; z-index: 0;">

            <?php if (!empty($error_message)): ?>
                <div class="message-erreur"><?= $error_message ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="groupe-formulaire">
                    <label for="utilisateur" class="etiquette-formulaire">Nom d'utilisateur ou Email</label>
                    <input type="text" id="utilisateur" name="utilisateur" class="champ-saisie" required value="<?= htmlspecialchars($_POST['utilisateur'] ?? '') ?>">
                </div>
                <div class="groupe-formulaire">
                    <label for="mot-de-passe" class="etiquette-formulaire">Mot de passe</label>
                    <input type="password" id="mot-de-passe" name="mot-de-passe" class="champ-saisie" required>
                </div>
                <button type="submit" class="bouton-soumettre">Se connecter</button>
            </form>
        </div>
    </body>
</html>