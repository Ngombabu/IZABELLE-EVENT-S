<?php
session_start();
$_SESSION['admine_user'] = 'true'; // Attention : Ceci rend l'utilisateur "admin" à chaque visite. À ajuster pour une gestion réelle des sessions.

$accueil = 'index.php';
$decoration = '../PUBLIC/decoration.php'; // Chemin relatif depuis index.php (si index.php est dans un sous-dossier, ex: 'pages/index.php')
$locationV = '../PUBLIC/location_&_vente.php';
$coiffureM = '../PUBLIC/coiffure_&_makup.php';
$realisation = '../PUBLIC/realisations.php';
$login = 'login.php'; // Si login.php est au même niveau que index.php
$logo = '../RESOURCE/SITE_IMAGE/logo.jpeg'; // Chemin relatif pour le logo

// Inclure le fichier de connexion à la base de données
// Assurez-vous que le chemin est correct par rapport à index.php
include(__DIR__ . '/../INCLUDE/data.php'); // Utilisez __DIR__ pour un chemin absolu fiable

// --- Traitement de la soumission du formulaire de devis ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_complet = htmlspecialchars($_POST['nom_prenom']);
    $telephone = htmlspecialchars($_POST['phone']);
    $email = htmlspecialchars($_POST['email']);

    // Préparez et liez les paramètres pour la sécurité (prévention des injections SQL)
    $stmt = $conn->prepare("INSERT INTO demandes_devis (nom_complet, telephone, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nom_complet, $telephone, $email);

    if ($stmt->execute()) {
        echo "<script>alert('Votre demande de devis a été envoyée avec succès ! Nous vous recontacterons bientôt.');</script>";
    } else {
        echo "<script>alert('Erreur lors de l\'envoi de votre demande. Veuillez réessayer. Erreur: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// --- Chemin de l'image de substitution (placeholder) ---
// Utilise le chemin relatif pour le HTML
$placeholder_path_html = '../RESOURCE/SITE_IMAGE/ggtransparente.png';
// Utilise le chemin absolu pour file_exists()
$placeholder_path_fs = __DIR__ . '/../RESOURCE/SITE_IMAGE/ggtransparente.png';


// --- Récupération des données pour "Nos Services" (Mélange de décorations, robes, beauté) ---
$services = []; // Cette variable contiendra tous les services combinés

// Récupérer les 3 dernières décorations
$sql_decorations_all = "SELECT titre, description, image_url FROM decorations ORDER BY date_ajout DESC LIMIT 3";
$result_decorations_all = $conn->query($sql_decorations_all);
if ($result_decorations_all && $result_decorations_all->num_rows > 0) {
    while($row = $result_decorations_all->fetch_assoc()) {
        $services[] = [
            'title' => $row['titre'],
            'description' => $row['description'],
            'image_url' => $row['image_url'],
            'type' => 'decoration'
        ];
    }
}

// Récupérer les 3 dernières robes
$sql_robes_all = "SELECT nom AS titre, description, image_url FROM robes ORDER BY date_ajout DESC LIMIT 3";
$result_robes_all = $conn->query($sql_robes_all);
if ($result_robes_all && $result_robes_all->num_rows > 0) {
    while($row = $result_robes_all->fetch_assoc()) {
        $services[] = [
            'title' => "Robe: " . $row['titre'],
            'description' => substr($row['description'], 0, 150) . (strlen($row['description']) > 150 ? '...' : '') ?? "Découvrez notre sélection de robes.",
            'image_url' => $row['image_url'],
            'type' => 'robe'
        ];
    }
}

// Récupérer les 3 derniers services beauté
$sql_beaute_all = "SELECT nom AS titre, description, image_url FROM services_beaute ORDER BY date_ajout DESC LIMIT 3";
$result_beaute_all = $conn->query($sql_beaute_all);
if ($result_beaute_all && $result_beaute_all->num_rows > 0) {
    while($row = $result_beaute_all->fetch_assoc()) {
        $services[] = [
            'title' => $row['titre'],
            'description' => $row['description'],
            'image_url' => $row['image_url'],
            'type' => 'beaute'
        ];
    }
}

// Mélanger et prendre un sous-ensemble pour $display_services (4 éléments)
shuffle($services);
$display_services = array_slice($services, 0, 4);

// Ajouter des services génériques si moins de 4 sont disponibles pour l'affichage principal
/*while (count($display_services) < 4) {
    $display_services[] = [
        'title' => "Service Personnalisé",
        'description' => "Un accompagnement sur mesure pour réaliser vos rêves, de la conception à la coordination.",
        'image_url' => "../RESOURCE/SITE_IMAGE/ggtransparente.png",
        'type' => 'generique'
    ];
}*/


// --- Récupération des données pour les sections spécifiques (Décorations, Robes, Services Beauté) ---
// Ces requêtes sont distinctes de celles utilisées pour $display_services, car elles alimentent leurs propres sections.

// Récupérer les 3 dernières Décorations
$decorations_data = [];
$sql_decorations = "SELECT id, titre, description, image_url, style FROM decorations ORDER BY date_ajout DESC LIMIT 3";
$result_decorations = $conn->query($sql_decorations);
if ($result_decorations && $result_decorations->num_rows > 0) {
    while($row = $result_decorations->fetch_assoc()) {
        $decorations_data[] = $row;
    }
}

// Récupérer les 3 dernières Robes
$robes_data = [];
$sql_robes = "SELECT id, nom, description, prix_location, prix_vente, image_url, disponible FROM robes ORDER BY date_ajout DESC LIMIT 3";
$result_robes = $conn->query($sql_robes);
if ($result_robes && $result_robes->num_rows > 0) {
    while($row = $result_robes->fetch_assoc()) {
        $robes_data[] = $row;
    }
}

// Récupérer les 3 derniers Services Beauté
$services_beaute_data = [];
$sql_services_beaute = "SELECT id, nom, description, prix, image_url FROM services_beaute ORDER BY date_ajout DESC LIMIT 3";
$result_services_beaute = $conn->query($sql_services_beaute);
if ($result_services_beaute && $result_services_beaute->num_rows > 0) {
    while($row = $result_services_beaute->fetch_assoc()) {
        $services_beaute_data[] = $row;
    }
}

// --- Récupération des données pour les Réalisations (3 dernières) ---
$realisations_data = [];
$sql_realisations = "SELECT titre, description, image_url FROM realisations ORDER BY date_realisation DESC, date_publication DESC LIMIT 3";
$result_realisations = $conn->query($sql_realisations);
if ($result_realisations && $result_realisations->num_rows > 0) {
    while($row = $result_realisations->fetch_assoc()) {
        $realisations_data[] = $row;
    }
}

$conn->close(); // Fermez la connexion à la base de données après toutes les requêtes
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
    <link rel="icon" type="image/x-icon" href="../RESOURCE/SITE_IMAGE/logo.jpeg">
    <link rel="stylesheet" href="../RESOURCE/CSS/index.css"> <title>Isabelle Event's | Accueil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Variables CSS */
        :root {
            --rose-fuchsia: #E91E89;
            --noir: #000000;
            --gris-fonce: #4b4949;
            --gris-clair: #cccccc;
            --gris-fonce1: hsla(328, 82%, 52%, 0.5);
            --vert-vif: #8CC63F;
            --blanc: #FFFFFF;
            --bleu-facebook: #3b5998;
            --rouge-google: #dd4b39;
        }

        /* Réinitialisation de base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            color: var(--noir);
            line-height: 1.6;
            background-color: #f8f8f8; /* Fond léger pour le corps */
        }

        /* --- Global Container --- */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* --- HEADER --- */
        header {
            background-color: var(--blanc);
            border-bottom: 1px solid var(--gris-clair);
            padding: 10px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        header img.logo {
            width: 70px;
            height: auto;
            margin-right: 20px;
        }

        /* --- NAVIGATION --- */
        header nav {
            flex-grow: 1;
            text-align: center;
        }
        header nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            gap: 30px;
        }
        header nav a {
            color: var(--gris-fonce);
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
            padding: 5px 0;
            position: relative;
        }
        header nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -3px;
            left: 0;
            background-color: var(--rose-fuchsia);
            transition: width 0.3s ease-in-out;
        }
        header nav a:hover::after {
            width: 100%;
        }

        /* --- Boutons Contact et Authentification --- */
        .chris {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .bouton-contact {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--gris-fonce);
            font-weight: bold;
            font-size: 1.1em;
            padding: 8px 15px;
            border: 1px solid var(--gris-clair);
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
        .bouton-contact i {
            margin-right: 8px;
            color: var(--vert-vif);
        }
        .bouton-contact:hover {
            background-color: var(--vert-vif);
            color: var(--blanc);
            border-color: var(--vert-vif);
        }
        .bouton-contact:hover i {
            color: var(--blanc);
        }
        .groupe-auth {
            display: flex;
            gap: 10px;
        }
        .bouton-auth {
            display: flex;
            align-items: center;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
        .bouton-auth i {
            margin-right: 8px;
        }
        .bouton-auth.connexion {
            color: var(--rose-fuchsia);
            border: 1px solid var(--rose-fuchsia);
            background-color: transparent;
        }
        .bouton-auth.connexion i {
            color: var(--rose-fuchsia);
        }
        .bouton-auth.connexion:hover {
            background-color: var(--rose-fuchsia);
            color: var(--blanc);
        }
        .bouton-auth.connexion:hover i {
            color: var(--blanc);
        }
        .bouton-auth.inscription {
            background-color: var(--rose-fuchsia);
            color: var(--blanc);
            border: 1px solid var(--rose-fuchsia);
        }
        .bouton-auth.inscription i {
            color: var(--blanc);
        }
        .bouton-auth.inscription:hover {
            background-color: #d11c7b;
            border-color: #d11c7b;
        }

        /* --- Section d'accueil (Niveaux1) --- */
        .niveaux1 {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 90vh;
            background: linear-gradient(45deg, var(--rose-fuchsia), var(--vert-vif));
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            padding: 50px 20px;
            position: relative;
            overflow: hidden;
            flex-wrap: wrap; /* Permet aux éléments de passer à la ligne sur petits écrans */
        }
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .niveaux1 form {
            background-color: var(--blanc);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            box-sizing: border-box;
            text-align: left;
            position: relative;
            z-index: 2;
        }
        .niveaux1 form h1 {
            font-size: 2.5em;
            color: var(--rose-fuchsia);
            margin-bottom: 20px;
            text-align: center;
        }
        .niveaux1 form .para p {
            margin-bottom: 25px;
            color: var(--gris-fonce);
            text-align: center;
            font-size: 1.1em;
        }
        .niveaux1 form .i {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }
        .niveaux1 form .i div {
            display: flex;
            align-items: center;
            width: 100%;
            position: relative;
        }
        .niveaux1 form .i i {
            position: absolute;
            right: 10px;
            color: var(--gris-fonce);
            font-size: 1.2em;
        }
        .niveaux1 form input[type="text"],
        .niveaux1 form input[type="email"],
        .niveaux1 form input[type="tel"] {
            width: 100%;
            padding: 12px 15px;
            padding-right: 40px;
            border: 1px solid var(--gris-clair);
            border-radius: 5px;
            font-size: 1em;
            color: var(--noir);
            background-color: var(--blanc);
        }
        .niveaux1 form input[type="text"]:focus,
        .niveaux1 form input[type="email"]:focus,
        .niveaux1 form input[type="tel"]:focus {
            border-color: var(--rose-fuchsia);
            outline: none;
            box-shadow: 0 0 5px rgba(233, 30, 137, 0.2);
        }
        .niveaux1 form button[type="submit"] {
            background-color: var(--vert-vif);
            color: var(--blanc);
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.2em;
            width: 100%;
            transition: background-color 0.3s ease, transform 0.3s ease;
            margin-top: 15px;
        }
        .niveaux1 form button[type="submit"]:hover {
            background-color: #72a333;
            transform: translateY(-2px);
        }
        .niveaux1 .image-container {
            position: absolute; /* Reste absolu pour l'effet de filigrane */
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1;
            overflow: hidden;
        }
        .niveaux1 .image-accueil {
            width: 60%;
            height: auto;
            max-width: 800px;
            opacity: 0.1;
            pointer-events: none;
        }

        /* --- Sections Générales --- */
        section {
            padding: 60px 20px;
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            background-color: var(--blanc);
            border-bottom: 1px solid #eee; /* Séparateur subtil entre sections */
        }
        section:last-of-type {
            border-bottom: none; /* Pas de bordure pour la dernière section */
        }
        section h2 {
            color: var(--rose-fuchsia);
            font-size: 2.5em;
            margin-bottom: 40px;
            position: relative;
        }
        section h2::after {
            content: '';
            display: block;
            width: 80px;
            height: 3px;
            background-color: var(--vert-vif);
            margin: 10px auto 0;
        }
        section p {
            color: var(--gris-fonce);
            font-size: 1.1em;
            margin-bottom: 30px;
        }

        /* --- Styles des Grilles (Applicables à toutes les grilles de service/réalisation) --- */
        .grille-services,
        .grille-decorations,
        .grille-robes,
        .grille-services-beaute,
        .galerie-realisations {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        /* --- Styles des Éléments de Grille --- */
        .service,
        .decoration-item,
        .robe-item,
        .service-beaute-item,
        .realisation-item {
            background-color: var(--blanc);
            border: 1px solid var(--gris-clair);
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: center;
        }
        .service:hover,
        .decoration-item:hover,
        .robe-item:hover,
        .service-beaute-item:hover,
        .realisation-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        /* --- Images dans les Éléments de Grille --- */
        .service img,
        .decoration-item img,
        .robe-item img,
        .service-beaute-item img,
        .realisation-item img {
            width: 100%;
            height: 200px; /* Hauteur fixe pour la cohérence visuelle */
            object-fit: cover; /* Recadre l'image pour remplir le cadre */
            border-radius: 8px;
            margin-bottom: 15px;
            display: block;
        }

        /* --- Titres et Descriptions des Éléments de Grille --- */
        .service h3,
        .decoration-item h3,
        .robe-item h3,
        .service-beaute-item h3,
        .realisation-item h3 {
            color: var(--gris-fonce);
            font-size: 1.6em; /* Légèrement ajusté */
            margin-bottom: 10px;
        }
        .service p,
        .decoration-item p,
        .robe-item p,
        .service-beaute-item p,
        .realisation-item p {
            color: var(--gris-fonce);
            font-size: 0.95em; /* Légèrement ajusté */
            flex-grow: 1; /* Permet aux descriptions de prendre l'espace disponible */
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 4; /* Limite à 4 lignes pour les descriptions */
            -webkit-box-orient: vertical;
        }

        /* --- Styles Spécifiques aux Robes --- */
        .robe-item .price {
            font-weight: bold;
            color: var(--rose-fuchsia); /* Utilisez la couleur accent pour les prix */
            margin-top: 10px;
            font-size: 1.1em;
        }
        .robe-item .disponibilite {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85em;
            font-weight: bold;
            margin-top: 10px;
        }
        .robe-item .disponibilite.available {
            background-color: #d4edda;
            color: #155724;
        }
        .robe-item .disponibilite.unavailable {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* --- Styles Spécifiques aux Décorations --- */
        .decoration-item .style-decoration {
            font-style: italic;
            color: #888;
            font-size: 0.9em;
            margin-top: 5px;
        }

        /* --- Boutons "Voir plus" --- */
        .bouton-soumettre.bouton-voir-plus {
            display: inline-block; /* Permet le centrage avec margin: auto */
            background-color: var(--rose-fuchsia);
            color: var(--blanc);
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 40px; /* Espace au-dessus du bouton */
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .bouton-soumettre.bouton-voir-plus:hover {
            background-color: #d11c7b;
            transform: translateY(-2px);
        }

        /* --- Pied de page --- */
        footer {
            background-color: var(--noir);
            color: var(--blanc);
            text-align: center;
            padding: 25px;
            margin-top: 0; /* Plus de marge-top ici si les sections ont déjà des bordures */
            font-size: 0.9em;
        }

        /* --- MEDIA QUERIES (Responsivité) --- */
        @media (max-width: 992px) { /* Pour les tablettes et petits laptops */
            .niveaux1 form {
                max-width: 500px; /* Réduit la largeur du formulaire */
            }
            .niveaux1 .image-accueil {
                width: 70%; /* L'image de fond un peu plus grande */
            }
            .grille-services,
            .grille-decorations,
            .grille-robes,
            .grille-services-beaute,
            .galerie-realisations {
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* 2-3 colonnes */
                gap: 20px;
            }
            .service img,
            .decoration-item img,
            .robe-item img,
            .service-beaute-item img,
            .realisation-item img {
                height: 180px; /* Ajuste la hauteur des images */
            }
            section h2 {
                font-size: 2.2em;
            }
        }

        @media (max-width: 768px) { /* Pour les tablettes en mode portrait et grands mobiles */
            header .container {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            header nav ul {
                flex-wrap: wrap;
                justify-content: center;
                margin-top: 10px;
                gap: 15px;
            }
            header img.logo {
                margin-bottom: 10px;
            }
            .chris {
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }
            .bouton-contact, .groupe-auth, .bouton-auth {
                width: 100%;
            }
            .groupe-auth {
                flex-direction: column;
            }

            .niveaux1 {
                flex-direction: column;
                padding: 40px 15px;
                min-height: auto;
            }
            .niveaux1 form {
                margin-top: 0;
                padding: 25px;
                order: 2; /* Place le formulaire en dessous de l'image (visuellement) */
            }
            .niveaux1 form h1 {
                font-size: 2em;
            }
            .niveaux1 .image-container {
                position: static; /* Retire l'absolu, l'image devient partie du flux */
                width: 100%;
                margin-top: 30px; /* Espace après le formulaire (si order 1) */
                order: 1; /* Place l'image en haut */
            }
            .niveaux1 .image-accueil {
                width: 90%;
                max-width: 350px;
                opacity: 0.2; /* Un peu plus visible sur mobile */
            }
            section {
                padding: 40px 15px;
            }
            section h2 {
                font-size: 2em;
            }
            .grille-services,
            .grille-decorations,
            .grille-robes,
            .grille-services-beaute,
            .galerie-realisations {
                grid-template-columns: 1fr; /* Une seule colonne sur mobile */
                gap: 20px;
            }
            .service,
            .decoration-item,
            .robe-item,
            .service-beaute-item,
            .realisation-item {
                padding: 20px;
            }
            .service img,
            .decoration-item img,
            .robe-item img,
            .service-beaute-item img,
            .realisation-item img {
                height: 160px; /* Hauteur ajustée pour mobile */
            }
            .service h3,
            .decoration-item h3,
            .robe-item h3,
            .service-beaute-item h3,
            .realisation-item h3 {
                font-size: 1.4em;
            }
        }

        @media (max-width: 480px) { /* Très petits mobiles */
            .niveaux1 form h1 {
                font-size: 1.8em;
            }
            .niveaux1 form .para p {
                font-size: 1em;
            }
            .niveaux1 form input,
            .niveaux1 form button {
                font-size: 1em;
            }
            .service h3,
            .decoration-item h3,
            .robe-item h3,
            .service-beaute-item h3,
            .realisation-item h3 {
                font-size: 1.3em;
            }
        }
    </style>
</head>
<body>
    <?php $active1="style='border-bottom:2px solid #E91E89'" // include('header.php') // Le code du header est déjà dans ce fichier, pas besoin de l'inclure à nouveau ?>
    <header>
        <div class="container">
            <img src="<?php echo htmlspecialchars($logo); ?>" alt="Logo Isabelle Event's" class="logo">
            <nav>
                <ul>
                    <li><a <?=$active1 ?>href="<?php echo htmlspecialchars($accueil); ?>">Accueil</a></li>
                    <li><a href="<?php echo htmlspecialchars($decoration); ?>">Décoration</a></li>
                    <li><a href="<?php echo htmlspecialchars($locationV); ?>">Location & Vente</a></li>
                    <li><a href="<?php echo htmlspecialchars($coiffureM); ?>">Coiffure & Maquillage</a></li>
                    <li><a href="<?php echo htmlspecialchars($realisation); ?>">Réalisations</a></li>
                </ul>
            </nav>
            <div class="chris">
                <a href="https://wa.me/votre_numero" class="bouton-contact" target="_blank">
                    <i class="fab fa-whatsapp"></i> Contactez-nous
                </a>
                <div class="groupe-auth">
                    <a href="<?php echo htmlspecialchars($login); ?>" class="bouton-auth connexion">
                        <i class="fa-solid fa-right-to-bracket"></i> Connexion
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="corps">
        <div class="niveaux1">
            <form action="index.php" method="post">
                <div class="para">
                    <h1>Site de wedding planner</h1>
                    <p>
                        Confiez l’organisation de votre mariage à une <strong>wedding planner</strong> passionnée, à l’écoute de vos rêves et de vos envies. De la décoration à la coordination du jour J, en passant par la location, la mise en beauté et l’accueil de vos invités, je mets tout mon cœur à créer un événement unique, à votre image. Ensemble, faisons de votre histoire d’amour un moment inoubliable, rempli d’émotions et de magie. <br>
                        <strong>Demandez votre devis personnalisé, et commençons à écrire le plus beau jour de votre vie !</strong>
                    </p>
                </div>
                <div class="i">
                    <div>
                        <input type="text" id="nom" name="nom_prenom" placeholder="Nom et Prénom" required>
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <div>
                        <input type="tel" id="phone" name="phone" placeholder="Téléphone" required>
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <div>
                        <input type="email" id="email" name="email" placeholder="Email" required>
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                </div>
                <button type="submit">Commencer ici</button>
            </form>

            <div class="image-container">
                <img src="../RESOURCE/SITE_IMAGE/ggtransparente.png" alt="Image d'accueil - Mariage" class="image-accueil">
            </div>
        </div>

    
        <section id="nos-services">
            <div class="container">
                <h2>Nos Services</h2>
                <p>Découvrez nos services complets pour faire de votre mariage un événement inoubliable, allant de la conception à la réalisation.</p>
                <div class="grille-services">
                    <?php if (!empty($display_services)): ?>
                        <?php foreach ($display_services as $service): ?>
                            <div class="service">
                                <img src="../RESOURCE/USER_IMAGE/<?php echo htmlspecialchars($service['image_url'] && file_exists(__DIR__ . '/../RESOURCE/USER_IMAGE/' . $service['image_url']) ? $service['image_url'] : $placeholder_path_html); ?>" alt="Service : <?php echo htmlspecialchars($service['title']); ?>">
                                <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                                <p><?php echo htmlspecialchars(mb_strlen($service['description']) > 150 ? mb_substr($service['description'], 0, 150) . '...' : $service['description']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun service disponible pour le moment.</p>
                    <?php endif; ?>
                </div>
                <a href="<?php echo htmlspecialchars($realisation); ?>" class="bouton-soumettre bouton-voir-plus">Voir tous nos services</a>
            </div>
        </section>

        
        <section id="nos-decorations">
            <div class="container">
                <h2>Nos Décorations</h2>
                <p>Explorez nos collections d'articles de décoration pour créer l'ambiance parfaite qui reflète votre style et vos rêves.</p>
                <div class="grille-decorations">
                    <?php if (!empty($decorations_data)): ?>
                        <?php foreach ($decorations_data as $decoration): ?>
                            <div class="decoration-item">
                                <img src="../RESOURCE/USER_IMAGE/<?php echo htmlspecialchars($decoration['image_url'] && file_exists(__DIR__ . '/../RESOURCE/USER_IMAGE/' . $decoration['image_url']) ? $decoration['image_url'] : $placeholder_path_html); ?>" alt="Décoration : <?php echo htmlspecialchars($decoration['titre']); ?>">
                                <h3><?php echo htmlspecialchars($decoration['titre']); ?></h3>
                                <p><?php echo htmlspecialchars(mb_strlen($decoration['description']) > 150 ? mb_substr($decoration['description'], 0, 150) . '...' : $decoration['description']); ?></p>
                                <span class="style-decoration">Style: <?php echo htmlspecialchars($decoration['style']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucune décoration à afficher pour le moment.</p>
                    <?php endif; ?>
                </div>
                <a href="<?php echo htmlspecialchars($decoration); ?>" class="bouton-soumettre bouton-voir-plus">Voir toutes nos décorations</a>
            </div>
        </section>

    
        <section id="nos-robes">
            <div class="container">
                <h2>Nos Robes</h2>
                <p>Découvrez notre sélection exquise de robes de mariée et de soirée, disponibles à la location ou à la vente pour votre grand jour.</p>
                <div class="grille-robes">
                    <?php if (!empty($robes_data)): ?>
                        <?php foreach ($robes_data as $robe): ?>
                            <div class="robe-item">
                                <img src="../RESOURCE/USER_IMAGE/<?php echo htmlspecialchars($robe['image_url'] && file_exists(__DIR__ . '/../RESOURCE/USER_IMAGE/' . $robe['image_url']) ? $robe['image_url'] : $placeholder_path_html); ?>" alt="Robe : <?php echo htmlspecialchars($robe['nom']); ?>">
                                <h3><?php echo htmlspecialchars($robe['nom']); ?></h3>
                                <p><?php echo htmlspecialchars(mb_strlen($robe['description']) > 150 ? mb_substr($robe['description'], 0, 150) . '...' : $robe['description']); ?></p>
                                <?php if ($robe['prix_location']): ?>
                                    <p class="price">Location: <?php echo htmlspecialchars($robe['prix_location']); ?> $</p>
                                <?php endif; ?>
                                <?php if ($robe['prix_vente']): ?>
                                    <p class="price">Vente: <?php echo htmlspecialchars($robe['prix_vente']); ?> $</p>
                                <?php endif; ?>
                                <span class="disponibilite <?php echo ($robe['disponible'] === 'disponible' ? 'available' : 'unavailable'); ?>">
                                    <?php echo ($robe['disponible'] === 'disponible' ? 'Disponible' : 'Non Disponible'); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucune robe à afficher pour le moment.</p>
                    <?php endif; ?>
                </div>
                <a href="<?php echo htmlspecialchars($locationV); ?>" class="bouton-soumettre bouton-voir-plus">Voir toutes nos robes</a>
            </div>
        </section>

        
        <section id="nos-services-beaute">
            <div class="container">
                <h2>Nos Services Beauté</h2>
                <p>Sublimez votre beauté naturelle avec nos services de coiffure et maquillage, conçus pour vous faire rayonner lors de chaque instant spécial.</p>
                <div class="grille-services-beaute">
                    <?php if (!empty($services_beaute_data)): ?>
                        <?php foreach ($services_beaute_data as $service_beaute): ?>
                            <div class="service-beaute-item">
                                <img src="../RESOURCE/USER_IMAGE/<?php echo htmlspecialchars($service_beaute['image_url'] && file_exists(__DIR__ . '/../RESOURCE/USER_IMAGE/' . $service_beaute['image_url']) ? $service_beaute['image_url'] : $placeholder_path_html); ?>" alt="Service Beauté : <?php echo htmlspecialchars($service_beaute['nom']); ?>">
                                <h3><?php echo htmlspecialchars($service_beaute['nom']); ?></h3>
                                <p><?php echo htmlspecialchars(mb_strlen($service_beaute['description']) > 150 ? mb_substr($service_beaute['description'], 0, 150) . '...' : $service_beaute['description']); ?></p>
                                <?php if ($service_beaute['prix']): ?>
                                    <p class="price">Prix: <?php echo htmlspecialchars($service_beaute['prix']); ?> $</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun service beauté à afficher pour le moment.</p>
                    <?php endif; ?>
                </div>
                <a href="<?php echo htmlspecialchars($coiffureM); ?>" class="bouton-soumettre bouton-voir-plus">Voir tous nos services beauté</a>
            </div>
        </section>

    
        <section id="nos-realisations">
            <div class="container">
                <h2>Nos Réalisations</h2>
                <p>Découvrez un aperçu des mariages et événements magiques que nous avons eu le plaisir d'organiser et de décorer, témoignages de notre passion.</p>
                <div class="galerie-realisations">
                    <?php if (!empty($realisations_data)): ?>
                        <?php foreach ($realisations_data as $realisation): ?>
                            <div class="realisation-item">
                                <img src="../RESOURCE/USER_IMAGE/<?php echo htmlspecialchars($realisation['image_url'] && file_exists(__DIR__ . '/../RESOURCE/USER_IMAGE/' . $realisation['image_url']) ? $realisation['image_url'] : $placeholder_path_html); ?>" alt="Réalisation : <?php echo htmlspecialchars($realisation['titre']); ?>">
                                <h3><?php echo htmlspecialchars($realisation['titre']); ?></h3>
                                <p><?php echo htmlspecialchars(mb_strlen($realisation['description']) > 150 ? mb_substr($realisation['description'], 0, 150) . '...' : ($realisation['description'] ?? '')); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucune réalisation à afficher pour le moment.</p>
                    <?php endif; ?>
                </div>
                <a href="<?php echo htmlspecialchars($realisation); ?>" class="bouton-soumettre bouton-voir-plus">Voir toutes nos réalisations</a>
            </div>
        </section>

    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Isabelle Event's. Tous droits réservés.</p>
    </footer>
</body>
</html>