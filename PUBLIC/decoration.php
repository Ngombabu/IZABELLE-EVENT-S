<?php
// Utilisation de $_SERVER['HTTP_REFERER'] pour adapter l'URL d'accueil
$url_page_1 = 'https://isabelleevens.rf.gd/ADMIN/index.php'; // Remplacez par l'URL exacte de votre page 1
$url_page_2 = 'https://isabelleevens.rf.gd/index.php'; // Remplacez par l'URL exacte de votre page 2

$decoration = '../PUBLIC/decoration.php';
$locationV = '../PUBLIC/location_&_vente.php';
$coiffureM = '../PUBLIC/coiffure_&_makup.php';
$realisation = '../PUBLIC/realisations.php';
$login = "../PUBLIC/login.php";
$sign = "../PUBLIC/connexion.php";
$logo = "../RESOURCE/SITE_IMAGE/logo.jpeg";
$accueil="../index.php";
// Chemin vers l'image placeholder
$placeholder_image = '../RESOURCE/SITE_IMAGE/placeholder-decoration.jpeg'; // Ajouté un placeholder spécifique pour la déco

// Inclure le fichier de connexion à la base de données
include(__DIR__ . '/../INCLUDE/data.php');

// Récupérer les données de décoration depuis la base de données
$decorations = [];
$sql_decorations = "SELECT id, titre, description, image_url, style FROM decorations ORDER BY date_ajout DESC";
$result_decorations = $conn->query($sql_decorations);

if ($result_decorations && $result_decorations->num_rows > 0) {
    while ($row = $result_decorations->fetch_assoc()) {
        $decorations[] = $row;
    }
}

// Fermer la connexion à la base de données
$conn->close();
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
    <link rel="stylesheet" href="../RESOURCE/CSS/index.css">
    <link rel="icon" type="image/x-icon" href="../RESOURCE/SITE_IMAGE/logo.jpeg">
    <title>Isabelle Event's | Décoration de Mariage</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* Définition des variables de couleurs comme dans index.php */
        :root {
            --rose-fuchsia:#E91E89;
            --noir:#000000;
            --gris-fonce:#4b4949;
            --gris-clair: #cccccc;
            --gris-fonce1: hsla(328, 82%, 52%, 0.5);
            --vert-vif:#8CC63F;
            --blanc:#FFFFFF;
            /* Couleurs pour les boutons sociaux - si besoin ici */
            --bleu-facebook: #3b5998;
            --rouge-google: #dd4b39;
        }

        /* Réinitialisation de base (si non déjà dans index.css) */
        *{
            margin:0;
            padding:0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif; /* Utilisé la même police que coiffure_&_makup.php */
            color: var(--noir);
            line-height: 1.6;
            background-color: #f8f8f8; /* Un léger fond pour le corps */
        }

        /* --- Global Container --- */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* --- HEADER (Copie de votre header si non inclus via un fichier) --- */
        header {
            background-color: var(--blanc);
            border-bottom: 1px solid var(--gris-clair);
            padding: 10px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        header .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        header img.logo {
            width: 70px;
            height:auto;
            margin-right: 20px;
        }
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
        header nav a{
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
        /* --- FIN HEADER --- */

        /* Styles généraux pour les sections */
        section {
            padding: 60px 20px;
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            background-color: var(--blanc);
            box-shadow: 0 2px 15px rgba(0,0,0,0.03);
            border-radius: 8px;
            margin-bottom: 30px;
        }

        section:last-of-type {
            margin-bottom: 0;
        }

        section h2 {
            font-family: 'Playfair Display', serif; /* Utilise la police Playfair Display */
            color: var(--rose-fuchsia);
            font-size: 2.8em; /* Augmenté pour l'impact */
            margin-bottom: 40px;
            position: relative;
        }
        section h2::after {
            content: '';
            display: block;
            width: 90px; /* Plus large */
            height: 4px; /* Plus épais */
            background-color: var(--vert-vif);
            margin: 15px auto 0;
            border-radius: 2px;
        }

        /* Styles spécifiques au Hero Section de Décoration */
        .hero-decoration {
            /* Retirez le background-gradient ici, il sera remplacé par la vidéo et son overlay */
            background: var(--noir); /* Fallback si vidéo ne charge pas */
            color: var(--blanc);
            padding: 100px 20px; /* Augmenté le padding pour le hero */
            min-height: 60vh; /* Hauteur similaire à la page coiffure */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-radius: 0; /* Pas de border-radius pour cette section */
            box-shadow: none; /* Pas d'ombre portée pour cette section */
        }

        /* Styles pour les vidéos en arrière-plan */
        .hero-video-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden; /* Important pour masquer le dépassement de la vidéo */
            z-index: 1; /* Place la vidéo derrière le contenu */
        }

        .hero-video-background video {
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            object-fit: cover; /* Assure que la vidéo couvre l'intégralité de la zone */
            filter: grayscale(50%) brightness(0.6); /* Effet pour mieux faire ressortir le texte */
        }
        
        /* Ajouter un overlay pour améliorer la lisibilité du texte sur la vidéo */
        .hero-decoration::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4); /* Overlay noir semi-transparent */
            z-index: 2; /* Au-dessus de la vidéo, sous le contenu */
        }

        /* Le contenu de la section hero doit être au-dessus de la vidéo et de l'overlay */
        .hero-decoration .container {
            position: relative;
            z-index: 3; /* Place le contenu au-dessus de l'overlay */
        }

        .hero-decoration h1 {
            font-family: 'Playfair Display', serif; /* Cohérence des polices */
            font-size: 3.8em; /* Plus grand pour le titre principal */
            margin-bottom: 25px;
            text-shadow: 3px 3px 7px rgba(0,0,0,0.3);
            color: var(--blanc);
        }

        .hero-decoration p {
            font-size: 1.4em;
            max-width: 900px;
            margin-bottom: 40px;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.2);
        }

        /* Animation de dégradé (copiée de index.css) - peut servir de fallback ou si vous retirez la vidéo sur mobile */
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Boutons principaux (similaire à "Commencer ici" ou aux boutons du header) */
        .btn-principal {
            display: inline-block;
            background-color: var(--vert-vif);
            color: var(--blanc);
            padding: 18px 35px; /* Plus de padding */
            border-radius: 8px; /* Coins plus arrondis */
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2em; /* Plus grande taille de police */
            transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .btn-principal:hover {
            background-color: #72a333; /* Vert plus foncé */
            transform: translateY(-5px); /* Plus grand déplacement */
            box-shadow: 0 8px 20px rgba(0,0,0,0.2); /* Ombre plus prononcée */
        }

        /* Section Styles de Décoration (affichage des données de la BDD) */
        #all-decorations { /* Renommé l'ID pour mieux refléter son contenu */
            padding-top: 80px; /* Plus d'espace en haut */
        }

        .grille-decorations { /* Nouveau nom de classe pour le grid des éléments de la BDD */
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); /* Similaire à services */
            gap: 35px; /* Espacement plus grand */
            margin-top: 40px;
        }

        .decoration-item { /* Nouvelle classe pour chaque élément de la BDD */
            background-color: var(--blanc);
            border: 1px solid var(--gris-clair);
            border-radius: 12px; /* Plus arrondi */
            box-shadow: 0 8px 25px rgba(0,0,0,0.1); /* Ombre plus prononcée */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            text-align: left;
            display: flex;
            flex-direction: column;
        }
        .decoration-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .decoration-item img {
            width: 100%;
            height: 280px; /* Hauteur fixe pour toutes les images */
            object-fit: cover;
            border-radius: 12px 12px 0 0;
            transition: transform 0.3s ease;
        }
        .decoration-item:hover img {
            transform: scale(1.05);
        }

        .decoration-details { /* Détails des éléments de la BDD */
            padding: 25px; /* Plus de padding */
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .decoration-details h3 {
            font-family: 'Playfair Display', serif;
            color: var(--gris-fonce);
            font-size: 2em; /* Plus grand */
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .decoration-details p {
            color: var(--gris-fonce);
            font-size: 1.05em;
            line-height: 1.6;
            margin-bottom: 20px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 4; /* Limite la description à 4 lignes */
            -webkit-box-orient: vertical;
        }
        
        .decoration-details .style-tag { /* Style pour l'attribut 'style' de la BDD */
            font-size: 0.95em;
            font-weight: bold;
            color: var(--rose-fuchsia);
            margin-top: auto; /* Pousse vers le bas */
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .btn-detail-deco { /* Bouton de détail pour les décorations */
            display: block;
            background-color: var(--rose-fuchsia);
            color: var(--blanc);
            padding: 14px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.05em;
            transition: background-color 0.3s ease, transform 0.3s ease;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-detail-deco:hover {
            background-color: #d11c7b;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }


        /* Section Personnalisation */
        .personnalisation {
            background-color: #f0f0f0; /* Un fond léger pour la section */
            padding: 80px 20px; /* Plus de padding */
            margin-top: 30px;
            border-radius: 8px;
            box-shadow: inset 0 0 15px rgba(0,0,0,0.05);
        }

        .personnalisation h3 {
            font-family: 'Playfair Display', serif;
            color: var(--gris-fonce); /* Couleur plus neutre */
            font-size: 2.2em; /* Taille ajustée */
            margin-top: 40px;
            margin-bottom: 30px;
            position: relative;
        }
        .personnalisation h3::after { /* Ligne sous le h3 de personnalisation */
            content: '';
            display: block;
            width: 70px;
            height: 3px;
            background-color: var(--rose-fuchsia);
            margin: 10px auto 0;
            border-radius: 2px;
        }

        .liste-elements {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px; /* Espacement ajusté */
            margin-top: 30px;
        }

        .element-item {
            background-color: var(--blanc);
            border: 1px solid var(--gris-clair);
            border-radius: 10px; /* Plus arrondi */
            padding: 25px; /* Plus de padding */
            box-shadow: 0 4px 15px rgba(0,0,0,0.08); /* Ombre plus prononcée */
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            font-weight: bold;
            color: var(--gris-fonce);
            font-size: 1.1em;
            transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            height: 140px; /* Hauteur fixe légèrement augmentée */
        }
        .element-item:hover {
            background-color: var(--rose-fuchsia);
            color: var(--blanc);
            transform: translateY(-8px); /* Plus grand déplacement */
            box-shadow: 0 8px 20px rgba(0,0,0,0.12); /* Ombre plus prononcée */
        }
        .element-item:hover i {
            color: var(--blanc);
        }

        .element-item i {
            font-size: 3em; /* Plus grande icône */
            color: var(--vert-vif);
            margin-bottom: 15px;
            transition: color 0.3s ease;
        }

        /* Section de Contact (demande de devis déco) */
        #contact {
            background: linear-gradient(135deg, var(--vert-vif), #6aa02a); /* Dégradé pour le contact */
            color: var(--blanc);
            padding: 80px 20px; /* Plus de padding */
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        #contact h2, #contact p {
            color: var(--blanc);
        }
        #contact h2::after {
            background-color: var(--blanc); /* Ligne blanche pour contraster */
        }

        /* Footer */
        footer {
            background-color: var(--noir);
            color: var(--blanc);
            text-align: center;
            padding: 25px; /* Plus de padding */
            margin-top: 50px; /* Espacement avec la section du dessus */
            font-size: 0.95em;
        }

        /* --- MEDIA QUERIES (Réactivité) --- */
        @media (max-width: 992px) {
            .hero-decoration h1 {
                font-size: 3.2em;
            }
            .hero-decoration p {
                font-size: 1.2em;
            }
            section h2 {
                font-size: 2.5em;
            }
            .grille-decorations {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 25px;
            }
            .decoration-item img {
                height: 250px;
            }
            .decoration-details h3 {
                font-size: 1.8em;
            }
            .liste-elements {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 20px;
            }
            .element-item {
                height: 120px;
            }
            .element-item i {
                font-size: 2.5em;
            }
        }

        @media (max-width: 768px) {
            /* Header adjustments */
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
            /* End Header adjustments */

            section {
                padding: 40px 15px;
            }
            section h2 {
                font-size: 2em;
            }
            .hero-decoration {
                min-height: 45vh;
                padding: 60px 15px;
                /* Sur mobile, on peut cacher la vidéo si elle cause des problèmes de performance,
                   et revenir à un dégradé. Ou la laisser si elle est légère.
                   Pour l'instant, elle sera affichée. */
                background: linear-gradient(45deg, var(--rose-fuchsia), var(--vert-vif)); /* Fallback au dégradé */
                background-size: 400% 400%;
                animation: gradientAnimation 15s ease infinite;
            }
            /* Si vous voulez masquer la vidéo sur mobile, décommenter ce qui suit: */
            /* .hero-video-background {
                display: none;
            } */

            .hero-decoration h1 {
                font-size: 2.8em;
            }
            .hero-decoration p {
                font-size: 1.1em;
                margin-bottom: 30px;
            }
            .btn-principal {
                padding: 14px 28px;
                font-size: 1.05em;
            }
            .grille-decorations {
                grid-template-columns: 1fr; /* Une seule colonne sur mobile */
                gap: 20px;
            }
            .decoration-item img {
                height: 220px;
            }
            .decoration-details {
                padding: 20px;
            }
            .decoration-details h3 {
                font-size: 1.6em;
            }
            .decoration-details p {
                font-size: 0.95em;
            }
            .btn-detail-deco {
                padding: 12px 20px;
                font-size: 0.95em;
            }
            .personnalisation h3 {
                font-size: 1.8em;
            }
            .liste-elements {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            .element-item {
                height: auto;
                padding: 15px;
            }
            .element-item i {
                font-size: 2.2em;
            }
        }

        @media (max-width: 480px) {
            .hero-decoration h1 {
                font-size: 2.2em;
            }
            .hero-decoration p {
                font-size: 0.95em;
            }
            .btn-principal {
                padding: 10px 20px;
                font-size: 0.95em;
            }
            section h2 {
                font-size: 1.8em;
            }
            .decoration-item img {
                height: 180px;
            }
            .decoration-details h3 {
                font-size: 1.4em;
            }
            .decoration-details p {
                font-size: 0.9em;
                -webkit-line-clamp: 5;
            }
        }
    </style>
</head>
<body>
    <?php $active1="style='border-bottom:2px solid #E91E89'";include('../INCLUDE/header.php')?>
    <main class="corps">
        <section class="hero-decoration">
            <div class="hero-video-background">
                <video autoplay loop muted playsinline poster="../RESOURCE/SITE_IMAGE/hero_deco_poster.jpg">
                    <source src="../RESOURCE/VIDEO/3.mp4" type="video/mp4">
                    <source src="../RESOURCE/VIDEO/3.webm" type="video/webm">
                    Votre navigateur ne supporte pas les vidéos HTML5.
                </video>
            </div>
            <div class="container">
                <h1>Décoration de Mariage : Votre Rêve Prend Vie</h1>
                <p>De la cérémonie à la réception, nous transformons chaque espace en un décor féerique qui raconte votre histoire d'amour. Découvrez notre expertise et laissez-nous créer une atmosphère inoubliable pour votre grand jour.</p>
                <a href="#all-decorations" class="btn-principal">Voir nos Réalisations</a>
            </div>
        </section>

        <section id="all-decorations">
            <div class="container">
                <h2>Nos Créations de Décoration</h2>
                <p>Découvrez un aperçu de nos réalisations précédentes et laissez-vous inspirer pour votre événement.</p>
                <div class="grille-decorations">
                    <?php if (!empty($decorations)): ?>
                        <?php foreach ($decorations as $deco_item): ?>
                            <div class="decoration-item">
                                <?php
                                $image_src = $placeholder_image;
                                $image_path_fs = __DIR__ . '/../RESOURCE/USER_IMAGE/' . $deco_item['image_url'];

                                if (!empty($deco_item['image_url']) && file_exists($image_path_fs)) {
                                    $image_src = '../RESOURCE/USER_IMAGE/' . htmlspecialchars($deco_item['image_url']);
                                }
                                ?>
                                <img src="<?php echo $image_src; ?>"
                                     alt="Image pour la décoration : <?php echo htmlspecialchars($deco_item['titre']); ?>"
                                     onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($placeholder_image); ?>';">
                                <div class="decoration-details">
                                    <h3><?php echo htmlspecialchars($deco_item['titre']); ?></h3>
                                    <p><?php
                                        // Décode les entités HTML, puis coupe la chaîne et ajoute des points de suspension
                                        $description = html_entity_decode($deco_item['description'], ENT_QUOTES, 'UTF-8');
                                        echo nl2br(htmlspecialchars(mb_strimwidth($description, 0, 180, '...', 'UTF-8')));
                                    ?></p>
                                    <?php if (!empty($deco_item['style'])): ?>
                                        <p class="style-tag">Style : <?php echo htmlspecialchars($deco_item['style']); ?></p>
                                    <?php endif; ?>
                                    <a href="#contact" class="btn-detail-deco">Demander plus d'informations</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="grid-column: 1 / -1; text-align: center; color: var(--gris-fonce); font-style: italic; padding: 30px;">
                            Aucune décoration trouvée pour le moment. Revenez bientôt pour découvrir nos nouvelles créations !
                        </p>
                    <?php endif; ?>
                </div>
                <a href="#contact" class="btn-principal" style="margin-top: 60px;">Commencez Votre Projet de Décoration</a>
            </div>
        </section>

        <section class="personnalisation">
            <div class="container">
                <h2>Votre Décoration, Notre Création Sur Mesure</h2>
                <p>Chez Isabelle Event's, nous croyons que chaque couple est unique. C'est pourquoi nous offrons un service de décoration entièrement personnalisé pour que votre mariage soit le reflet parfait de votre personnalité et de votre amour.</p>
                <h3>Nous prenons en charge chaque élément :</h3>
                <div class="liste-elements">
                    <div class="element-item"><i class="fa-solid fa-flower"></i> Arrangements Floraux</div>
                    <div class="element-item"><i class="fa-solid fa-lightbulb"></i> Éclairage Ambiant</div>
                    <div class="element-item"><i class="fa-solid fa-chair"></i> Mobilier & Linge de Table</div>
                    <div class="element-item"><i class="fa-solid fa-cake-candles"></i> Candy Bar & Buffet</div>
                    <div class="element-item"><i class="fa-solid fa-camera"></i> Coin Photobooth</div>
                    <div class="element-item"><i class="fa-solid fa-sign-hanging"></i> Signalétique Personnalisée</div>
                </div>
                <p style="margin-top: 30px;">Laissez libre cours à votre imagination, nous nous occupons de tout. Contactez-nous pour discuter de votre projet de décoration.</p>
            </div>
        </section>

        <section id="contact">
            <div class="container">
                <h2>Prêt(e) à embellir votre mariage ?</h2>
                <p style="margin-bottom: 30px;">Demandez un devis personnalisé pour une décoration de mariage qui vous ressemble et émerveillera vos invités.</p>
                <a href="https://wa.me/+243900334139" target="_blank" class="btn-principal" style="background-color: var(--rose-fuchsia); color: var(--blanc);">Demander un Devis Déco</a>
            </div>
        </section>

    </main>

    <?php include('../INCLUDE/footer.php')?>

    <script>
        // Gérer la lecture des vidéos pour le hero de décoration
        document.addEventListener('DOMContentLoaded', () => {
            const video = document.querySelector('.hero-video-background video');
            if (video) {
                video.addEventListener('loadeddata', () => {
                    video.play().catch(error => {
                        console.log('Autoplay was prevented for decoration video:', error);
                        // Fallback: If autoplay is prevented, try to play on user interaction
                        document.body.addEventListener('click', () => {
                            video.play().catch(e => console.log('Click play failed for decoration video:', e));
                        }, { once: true });
                    });
                });
            }
        });
    </script>
</body>
</html>