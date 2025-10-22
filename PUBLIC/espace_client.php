<?php
session_start(); // Démarre la session

// --- IMPORTANT : Afficher les erreurs PHP pour le débogage (À retirer en production !) ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// --- FIN IMPORTANT ---

// Ajustez le chemin vers votre fichier de connexion à la base de données
include('../INCLUDE/data.php'); // Assurez-vous que ce chemin est correct

// --- Sécurité : Vérifier si l'utilisateur est connecté ---
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Rediriger vers la page de connexion
    exit();
}

// Récupérer les informations de l'utilisateur connecté depuis la session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'];

// Vérifier si l'utilisateur est un client ou un admin, sinon rediriger
if ($user_role !== 'client' && $user_role !== 'admin') {
    header('Location: login.php');
    exit();
}

// Variables pour les chemins d'accès aux ressources
$base_css = '../RESOURCE/CSS/style.css';
$base_logo = '../RESOURCE/SITE_IMAGE/logo.jpeg';
$message_status = ''; // Pour afficher les messages de succès ou d'erreur d'envoi de message

// --- Traitement de l'envoi de nouveau message ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $new_message = trim($_POST['new_message'] ?? '');
    $sujet_message = "Conversation client/admin"; // Sujet par défaut pour ces messages

    if (empty($new_message)) {
        $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Le message ne peut pas être vide.</div>';
    } else {
        // 1. Récupérer tous les IDs des administrateurs
        $admin_ids = [];
        $stmt_admins = $conn->prepare("SELECT id FROM utilisateurs WHERE role = 'admin'");
        $stmt_admins->execute();
        $result_admins = $stmt_admins->get_result();
        while ($row = $result_admins->fetch_assoc()) {
            $admin_ids[] = $row['id'];
        }
        $stmt_admins->close();

        if (empty($admin_ids)) {
            $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Aucun administrateur trouvé pour recevoir le message.</div>';
        } else {
            // 2. Insérer le message pour chaque administrateur trouvé
            $insert_success = true;
            $stmt_insert = $conn->prepare("INSERT INTO conversations_messages (expediteur_id, destinataire_id, sujet, contenu) VALUES (?, ?, ?, ?)");
            foreach ($admin_ids as $admin_id) {
                $stmt_insert->bind_param("iiss", $user_id, $admin_id, $sujet_message, $new_message);
                if (!$stmt_insert->execute()) {
                    $insert_success = false;
                    // Enregistrer l'erreur pour le débogage si nécessaire
                    error_log("Erreur d'insertion de message pour admin ID $admin_id: " . $stmt_insert->error);
                }
            }
            $stmt_insert->close();

            if ($insert_success) {
                $message_status = '<div class="message message-succes"><i class="fas fa-check-circle"></i> Message envoyé avec succès à l\'administration !</div>';
                $_POST['new_message'] = ''; // Vider le champ
            } else {
                $message_status = '<div class="message message-erreur"><i class="fas fa-times-circle"></i> Erreur lors de l\'envoi de certains messages à l\'administration.</div>';
            }
        }
    }
}

// --- Récupération des messages de conversation pour cet utilisateur ---
// On récupère tous les messages où l'utilisateur connecté ($user_id) est impliqué,
// que ce soit en tant qu'expéditeur ou destinataire, et où l'autre partie est un admin.
$conversations = [];
$stmt_conv = $conn->prepare("
    SELECT cm.expediteur_id, cm.contenu, cm.date_envoi
    FROM conversations_messages cm
    WHERE (cm.expediteur_id = ?) -- Messages envoyés par le client
       OR (cm.destinataire_id = ? AND cm.expediteur_id IN (SELECT id FROM utilisateurs WHERE role = 'admin')) -- Messages reçus du client par un admin
    ORDER BY cm.date_envoi ASC
");
// On lie les paramètres : l'ID du client est utilisé pour les deux cas.
// Pour le deuxième OR, on filtre les expéditeurs par ceux qui ont le rôle 'admin'.
$stmt_conv->bind_param("ii", $user_id, $user_id);
$stmt_conv->execute();
$result_conv = $stmt_conv->get_result();

while ($row = $result_conv->fetch_assoc()) {
    $conversations[] = $row;
}
$stmt_conv->close();

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
    <link rel="stylesheet" href="<?php echo $base_css; ?>">
    <title>Isabelle Event's | Espace Client</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --rose-fuchsia:#E91E89;
            --noir:#000000;
            --gris-fonce:#4b4949;
            --gris-clair: #cccccc;
            --gris-fonce1: hsla(328, 82%, 52%, 0.5);
            --vert-vif:#8CC63F;
            --blanc:#FFFFFF;
            --bleu-facebook: #3b5998;
            --rouge-google: #dd4b39;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background-color: #f4f6f8;
            color: var(--gris-fonce);
            align-items: center;
            justify-content: flex-start;
            padding-top: 50px;
        }

        .conteneur-client {
            background-color: var(--blanc);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            width: 90%;
            max-width: 900px;
            text-align: center;
            box-sizing: border-box;
            margin-bottom: 30px;
        }

        .entete-client {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--gris-clair);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .entete-client h1 {
            color: var(--rose-fuchsia);
            margin: 0;
            font-size: 2.5em;
            flex-grow: 1;
            text-align: left;
        }

        .entete-client .bouton-deconnexion {
            background-color: var(--gris-fonce);
            color: var(--blanc);
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s ease;
        }

        .entete-client .bouton-deconnexion:hover {
            background-color: var(--rose-fuchsia);
        }

        .message-bienvenue {
            font-size: 1.2em;
            line-height: 1.6;
            margin-bottom: 30px;
            color: var(--gris-fonce);
        }

        .message-bienvenue span {
            color: var(--rose-fuchsia);
            font-weight: bold;
        }

        .fonctionnalites {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            margin-top: 40px;
            margin-bottom: 40px;
        }

        .carte-fonctionnalite {
            background-color: #f9f9f9;
            border: 1px solid var(--gris-clair);
            border-radius: 10px;
            padding: 25px;
            width: 280px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .carte-fonctionnalite:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }

        .carte-fonctionnalite i {
            font-size: 3em;
            color: var(--rose-fuchsia);
            margin-bottom: 15px;
        }

        .carte-fonctionnalite h3 {
            color: var(--gris-fonce);
            margin-top: 0;
            font-size: 1.3em;
        }

        .carte-fonctionnalite p {
            font-size: 0.9em;
            color: var(--gris-fonce);
        }

        /* Section Conversation */
        .section-conversation {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid var(--gris-clair);
            text-align: left;
        }

        .section-conversation h2 {
            color: var(--rose-fuchsia);
            font-size: 2em;
            margin-bottom: 25px;
            text-align: center;
        }

        .boite-messages {
            border: 1px solid var(--gris-clair);
            border-radius: 8px;
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
            background-color: #fdfdfd;
            box-shadow: inset 0 1px 5px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .message-wrapper {
            display: flex;
            flex-direction: column;
            max-width: 70%;
        }

        .message-bulle {
            padding: 12px 18px;
            border-radius: 18px;
            line-height: 1.5;
            word-wrap: break-word;
            font-size: 0.95em;
        }

        .message-client .message-bulle {
            background-color: var(--rose-fuchsia);
            color: var(--blanc);
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .message-admin .message-bulle {
            background-color: var(--gris-fonce);
            color: var(--blanc);
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }

        .message-client {
            align-self: flex-end;
        }

        .message-admin {
            align-self: flex-start;
        }


        .message-info {
            font-size: 0.8em;
            color: #888;
            margin-top: 5px;
        }
        .message-client .message-info {
             text-align: right;
        }
        .message-admin .message-info {
             text-align: left;
        }


        .form-message {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .form-message textarea {
            flex-grow: 1;
            padding: 12px;
            border: 1px solid var(--gris-clair);
            border-radius: 8px;
            resize: vertical;
            min-height: 60px;
            font-size: 1em;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-message textarea:focus {
            border-color: var(--rose-fuchsia);
            box-shadow: 0 0 0 3px rgba(233, 30, 137, 0.2);
            outline: none;
        }

        .form-message button {
            background-color: var(--rose-fuchsia);
            color: var(--blanc);
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease-in-out;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-message button:hover {
            background-color: #d11a76;
            transform: translateY(-2px);
        }

        /* Messages de notification (réutilisés) */
        .message {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
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

        .message i {
            font-size: 1.2em;
        }


        /* Pied de page simple */
        .pied-page {
            margin-top: auto;
            padding: 20px;
            text-align: center;
            color: var(--gris-fonce);
            font-size: 0.85em;
            border-top: 1px solid var(--gris-clair);
            width: 100%;
            box-sizing: border-box;
        }


        /* Media Queries */
        @media (max-width: 768px) {
            .conteneur-client {
                padding: 30px;
                width: 95%;
            }
            .entete-client {
                flex-direction: column;
                align-items: flex-start;
            }
            .entete-client h1 {
                font-size: 2em;
                text-align: center;
                width: 100%;
            }
            .entete-client .bouton-deconnexion {
                width: 100%;
                justify-content: center;
            }
            .message-bienvenue {
                font-size: 1em;
            }
            .carte-fonctionnalite {
                width: 100%;
            }
            .form-message {
                flex-direction: column;
            }
            .form-message button {
                width: 100%;
                justify-content: center;
            }
            .message-wrapper {
                max-width: 90%;
            }
        }

        @media (max-width: 480px) {
            .conteneur-client {
                padding: 20px;
            }
            .entete-client h1 {
                font-size: 1.8em;
            }
            .carte-fonctionnalite i {
                font-size: 2.5em;
            }
            .carte-fonctionnalite h3 {
                font-size: 1.1em;
            }
            .section-conversation h2 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <div class="conteneur-client">
        <header class="entete-client">
            <h1>Bienvenue, <?php echo htmlspecialchars($user_name); ?> !</h1>
            <a href="logout.php" class="bouton-deconnexion">
                <i class="fas fa-sign-out-alt"></i> Se Déconnecter
            </a>
        </header>

        <p class="message-bienvenue">
            Nous sommes ravis de vous retrouver sur votre espace client. Ici, vous pouvez gérer vos demandes, consulter les informations de votre compte et découvrir nos dernières décorations et services.
        </p>

        <div class="fonctionnalites">
            <a href="#" class="carte-fonctionnalite">
                <i class="fas fa-calendar-alt"></i>
                <h3>Mes Réservations</h3>
                <p>Consultez et gérez vos réservations et événements à venir.</p>
            </a>
            <a href="#" class="carte-fonctionnalite">
                <i class="fas fa-user-circle"></i>
                <h3>Mon Profil</h3>
                <p>Mettez à jour vos informations personnelles et votre mot de passe.</p>
            </a>
            <a href="#" class="carte-fonctionnalite">
                <i class="fas fa-palette"></i>
                <h3>Nos Décorations</h3>
                <p>Découvrez notre catalogue complet de décorations disponibles.</p>
            </a>
            <a href="#" class="carte-fonctionnalite">
                <i class="fas fa-question-circle"></i>
                <h3>Aide & Support</h3>
                <p>Trouvez des réponses à vos questions et contactez-nous.</p>
            </a>
        </div>

        ---

        <section class="section-conversation">
            <h2><i class="fas fa-comments"></i> Votre Conversation avec l'Administration</h2>

            <?php if ($message_status): ?>
                <?php echo $message_status; ?>
            <?php endif; ?>

            <div class="boite-messages">
                <?php if (empty($conversations)): ?>
                    <p style="text-align: center; color: #888;">Aucun message pour le moment. Envoyez-nous votre première question !</p>
                <?php else: ?>
                    <?php foreach ($conversations as $msg): ?>
                        <div class="message-wrapper <?php echo ($msg['expediteur_id'] == $user_id) ? 'message-client' : 'message-admin'; ?>">
                            <div class="message-bulle">
                                <?php echo htmlspecialchars($msg['contenu']); ?>
                            </div>
                            <div class="message-info">
                                <?php
                                $sender_display_name = ($msg['expediteur_id'] == $user_id) ? 'Vous' : 'Admin';
                                echo $sender_display_name . ' - ' . date('d/m/Y H:i', strtotime($msg['date_envoi']));
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form action="espace_client.php" method="POST" class="form-message">
                <textarea name="new_message" placeholder="Écrivez votre message ici..." required><?php echo htmlspecialchars($_POST['new_message'] ?? ''); ?></textarea>
                <button type="submit" name="send_message"><i class="fas fa-paper-plane"></i> Envoyer</button>
            </form>
        </section>

    </div>

    <footer class="pied-page">
        <p>&copy; <?php echo date("Y"); ?> Isabelle Event's. Tous droits réservés.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var messageBox = document.querySelector('.boite-messages');
            if (messageBox) {
                messageBox.scrollTop = messageBox.scrollHeight;
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>