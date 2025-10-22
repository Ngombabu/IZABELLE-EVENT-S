<?php
// Utilisation de $_SERVER['HTTP_REFERER'] pour adapter l'URL d'accueil
$url_page_1 = 'https://isabelleevens.rf.gd/ADMIN/index.php';
$url_page_2 = 'https://isabelleevens.rf.gd/index.php';

if (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    if ($referer === $url_page_1) {
        $accueil = '../ADMIN/index.php';
    } else {
        $accueil = '../index.php';
    }
} else {
    $accueil = '../index.php';
}

$decoration = '../PUBLIC/decoration.php';
$locationV = '../PUBLIC/location_&_vente.php';
$coiffureM = '../PUBLIC/coiffure_&_makup.php'; // Lien de la page actuelle
$realisation = '../PUBLIC/realisations.php';
$login = "../PUBLIC/login.php";
$sign = "../PUBLIC/connexion.php";
$logo = "../RESOURCE/SITE_IMAGE/logo.jpeg";

// Chemin vers l'image placeholder
$placeholder_image = '../RESOURCE/SITE_IMAGE/placeholder-beauty.jpeg';

// Inclure le fichier de connexion à la base de données
include(__DIR__ . '/../INCLUDE/data.php');

// Récupérer les services de coiffure et maquillage depuis la base de données
$services = [];
$sql_services = "SELECT id, nom, description, prix, image_url FROM services_beaute ORDER BY nom ASC";
$result_services = $conn->query($sql_services);

if ($result_services && $result_services->num_rows > 0) {
    while ($row = $result_services->fetch_assoc()) {
        $services[] = $row;
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
    <title>Isabelle Event's | Coiffure & Maquillage</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* Définition des variables de couleurs */
        :root {
            --rose-fuchsia:#E91E89;
            --noir:#000000;
            --gris-fonce:#4b4949;
            --gris-clair: #cccccc;
            --vert-vif:#8CC63F;
            --blanc:#FFFFFF;
            --bleu-facebook: #3b5998;
            --rouge-google: #dd4b39;
        }

        /* Réinitialisation de base */
        *{
            margin:0;
            padding:0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            color: var(--noir);
            line-height: 1.6;
            background-color: #f8f8f8;
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
            font-family: 'Playfair Display', serif;
            color: var(--rose-fuchsia);
            font-size: 2.8em;
            margin-bottom: 40px;
            position: relative;
        }
        section h2::after {
            content: '';
            display: block;
            width: 90px;
            height: 4px;
            background-color: var(--vert-vif);
            margin: 15px auto 0;
            border-radius: 2px;
        }

        /* Styles spécifiques au Hero Section Coiffure & Maquillage */
        .hero-beauty {
            /* Retirez le background-gradient ici, il sera remplacé par la vidéo et son overlay */
            background: var(--noir); /* Fallback si vidéo ne charge pas */
            color: var(--blanc);
            padding: 100px 20px;
            min-height: 60vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative; /* Important pour positionner les vidéos et le contenu */
            overflow: hidden;
            border-radius: 0;
            box-shadow: none;
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
        .hero-beauty::before {
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
        .hero-beauty .container {
            position: relative;
            z-index: 3; /* Place le contenu au-dessus de l'overlay */
        }

        .hero-beauty h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.8em;
            margin-bottom: 25px;
            text-shadow: 3px 3px 7px rgba(0,0,0,0.3);
            color: var(--blanc);
        }

        .hero-beauty p {
            font-size: 1.4em;
            max-width: 900px;
            margin-bottom: 40px;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.2);
        }

        /* Animation de dégradé (si vous voulez garder un fallback coloré pour d'autres sections ou en cas de non chargement vidéo) */
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Boutons principaux */
        .btn-principal {
            display: inline-block;
            background-color: var(--vert-vif);
            color: var(--blanc);
            padding: 18px 35px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2em;
            transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .btn-principal:hover {
            background-color: #72a333;
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        /* Section Services Beauté */
        #services {
            padding-top: 80px;
        }
        .grille-services {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 35px;
            margin-top: 40px;
        }

        .service-item {
            background-color: var(--blanc);
            border: 1px solid var(--gris-clair);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            text-align: left;
            display: flex;
            flex-direction: column;
            cursor: pointer;
        }
        .service-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .service-item img {
            width: 100%;
            height: 280px;
            object-fit: cover;
            border-radius: 12px 12px 0 0;
            transition: transform 0.3s ease;
        }
        .service-item:hover img {
            transform: scale(1.05);
        }

        .service-details {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .service-details h3 {
            font-family: 'Playfair Display', serif;
            color: var(--gris-fonce);
            font-size: 2em;
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .service-details p {
            color: var(--gris-fonce);
            font-size: 1.05em;
            line-height: 1.6;
            margin-bottom: 20px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
        }

        .service-details .prix {
            font-size: 1.5em;
            font-weight: bold;
            color: var(--rose-fuchsia);
            margin-top: auto;
            margin-bottom: 20px;
        }

        .btn-detail {
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

        .btn-detail:hover {
            background-color: #d11c7b;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        /* Section FAQ */
        .section-faq {
            background-color: #f0f0f0;
            padding: 80px 20px;
            margin-top: 30px;
            border-radius: 8px;
            box-shadow: inset 0 0 15px rgba(0,0,0,0.05);
        }

        .faq-container {
            max-width: 900px;
            margin: 50px auto 0;
            text-align: left;
        }

        .faq-item {
            background-color: var(--blanc);
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        .faq-item:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }

        .faq-question {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 25px;
            font-weight: bold;
            color: var(--gris-fonce);
            font-size: 1.2em;
            cursor: pointer;
            background-color: var(--blanc);
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .faq-question:hover {
            background-color: #f5f5f5;
            color: var(--rose-fuchsia);
        }

        .faq-question i {
            font-size: 1.1em;
            transition: transform 0.3s ease, color 0.3s ease;
            color: var(--vert-vif);
        }

        .faq-item.active .faq-question {
            background-color: var(--rose-fuchsia);
            color: var(--blanc);
            border-bottom-color: var(--rose-fuchsia);
        }
        .faq-item.active .faq-question i {
            transform: rotate(180deg);
            color: var(--blanc);
        }

        .faq-answer {
            padding: 0 25px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-out, padding 0.4s ease-out;
            color: var(--gris-fonce);
            font-size: 1em;
            line-height: 1.7;
        }

        .faq-item.active .faq-answer {
            max-height: 300px;
            padding: 25px;
        }

        /* Section de Contact (demande de rendez-vous) */
        #contact {
            background: linear-gradient(135deg, var(--rose-fuchsia), #d11c7b);
            color: var(--blanc);
            padding: 80px 20px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        #contact h2, #contact p {
            color: var(--blanc);
        }
        #contact h2::after {
            background-color: var(--blanc);
        }

        /* Footer */
        footer {
            background-color: var(--noir);
            color: var(--blanc);
            text-align: center;
            padding: 25px;
            margin-top: 50px;
            font-size: 0.95em;
        }

        /* --- MEDIA QUERIES (Réactivité) --- */
        @media (max-width: 992px) {
            .hero-beauty h1 {
                font-size: 3.2em;
            }
            .hero-beauty p {
                font-size: 1.2em;
            }
            section h2 {
                font-size: 2.5em;
            }
            .grille-services {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 25px;
            }
            .service-item img {
                height: 250px;
            }
            .service-details h3 {
                font-size: 1.8em;
            }
            .service-details .prix {
                font-size: 1.3em;
            }
            .faq-question {
                font-size: 1.1em;
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
            .hero-beauty {
                min-height: 45vh;
                padding: 60px 15px;
                /* Conservez le dégradé en arrière-plan pour le mobile si la vidéo ne se charge pas automatiquement
                   ou pour un effet si elle est masquée */
                background: linear-gradient(45deg, var(--rose-fuchsia), var(--vert-vif));
                background-size: 400% 400%;
                animation: gradientAnimation 15s ease infinite;
            }
            .hero-beauty h1 {
                font-size: 2.8em;
            }
            .hero-beauty p {
                font-size: 1.1em;
                margin-bottom: 30px;
            }
            .btn-principal {
                padding: 14px 28px;
                font-size: 1.05em;
            }
            .grille-services {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            .service-item img {
                height: 220px;
            }
            .service-details {
                padding: 20px;
            }
            .service-details h3 {
                font-size: 1.6em;
            }
            .service-details p {
                font-size: 0.95em;
            }
            .service-details .prix {
                font-size: 1.2em;
            }
            .btn-detail {
                padding: 12px 20px;
                font-size: 0.95em;
            }
            .faq-question, .faq-answer {
                padding: 18px;
                font-size: 1em;
            }
            .faq-item.active .faq-answer {
                padding: 18px;
            }
            /* La ligne suivante a été supprimée pour permettre l'affichage des vidéos sur mobile */
            /* .hero-video-background { display: none; } */
        }

        @media (max-width: 480px) {
            .hero-beauty h1 {
                font-size: 2.2em;
            }
            .hero-beauty p {
                font-size: 0.95em;
            }
            .btn-principal {
                padding: 10px 20px;
                font-size: 0.95em;
            }
            section h2 {
                font-size: 1.8em;
            }
            .service-item img {
                height: 180px;
            }
            .service-details h3 {
                font-size: 1.4em;
            }
            .service-details p {
                font-size: 0.9em;
                -webkit-line-clamp: 5;
            }
            .service-details .prix {
                font-size: 1.1em;
            }
            .faq-question {
                font-size: 0.95em;
            }
            .faq-answer {
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <?php $active3="style='border-bottom:2px solid #E91E89'";include('../INCLUDE/header.php')?>

    <main class="corps">
        <section class="hero-beauty">
            <div class="hero-video-background">
                <video autoplay loop muted playsinline poster="../RESOURCE/SITE_IMAGE/hero_video_poster_1.jpg">
                    <source src="../RESOURCE/VIDEO/2.mp4" type="video/mp4">
                    <source src="../RESOURCE/VIDEO/2.webm" type="video/webm"> Votre navigateur ne supporte pas les vidéos HTML5.
                </video>
                <video autoplay loop muted playsinline poster="../RESOURCE/SITE_IMAGE/hero_video_poster_2.jpg" style="display: none;">
                    <source src="../RESOURCE/VIDEO/1.mp4" type="video/mp4">
                    <source src="../RESOURCE/VIDEO/1.webm" type="video/webm"> Votre navigateur ne supporte pas les vidéos HTML5.
                </video>
            </div>
            <div class="container">
                <h1>Sublimez Votre Beauté pour le Grand Jour</h1>
                <p>Découvrez nos services experts en coiffure et maquillage, conçus pour vous révéler et vous faire rayonner lors de vos événements spéciaux. Laissez nos professionnels transformer votre vision en réalité et faire de chaque moment une célébration de votre élégance.</p>
                <a href="#services" class="btn-principal">Découvrir nos Services</a>
            </div>
        </section>

        <section id="services">
            <div class="container">
                <h2>Nos Prestations Beauté</h2>
                <p>Que ce soit pour un mariage, une soirée spéciale ou une séance photo, nos artistes maquilleurs et coiffeurs sont dédiés à créer le look parfait qui mettra en valeur votre style unique.</p>
                <div class="grille-services">
                    <?php if (!empty($services)): ?>
                        <?php foreach ($services as $service): ?>
                            <div class="service-item">
                                <?php
                                $image_src = $placeholder_image;
                                $image_path_fs = __DIR__ . '/../RESOURCE/USER_IMAGE/' . $service['image_url'];

                                if (!empty($service['image_url']) && file_exists($image_path_fs)) {
                                    $image_src = '../RESOURCE/USER_IMAGE/' . htmlspecialchars($service['image_url']);
                                }
                                ?>
                                <img src="<?php echo $image_src; ?>"
                                     alt="Image pour le service : <?php echo htmlspecialchars($service['nom']); ?>"
                                     onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($placeholder_image); ?>';">
                                <div class="service-details">
                                    <h3><?php echo htmlspecialchars($service['nom']); ?></h3>
                                    <p><?php
                                        $description = html_entity_decode($service['description'], ENT_QUOTES, 'UTF-8');
                                        echo nl2br(htmlspecialchars(mb_strimwidth($description, 0, 180, '...', 'UTF-8')));
                                    ?></p>
                                    <p class="prix"><?php echo htmlspecialchars($service['prix'] ? $service['prix'] . ' $' : 'Sur devis'); ?></p>
                                    <a href="#contact" class="btn-detail">Demander un rendez-vous</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="grid-column: 1 / -1; text-align: center; color: var(--gris-fonce); font-style: italic; padding: 30px;">
                            Aucun service beauté trouvé pour le moment. Revenez bientôt pour découvrir nos nouvelles prestations !
                        </p>
                    <?php endif; ?>
                </div>
                <a href="#contact" class="btn-principal" style="margin-top: 60px;">Réservez Votre Séance Beauté</a>
            </div>
        </section>

        <section class="section-faq">
            <div class="container">
                <h2>Questions Fréquentes</h2>
                <div class="faq-container">
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Où se déroulent les prestations de coiffure et maquillage ?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Nos services peuvent être réalisés soit dans notre salon, soit sur le lieu de votre événement (à domicile, hôtel, etc.) pour plus de confort. Des frais de déplacement peuvent s'appliquer en fonction de la distance, discutés lors de l'établissement du devis.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Proposez-vous des essais pour le maquillage et la coiffure ?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Oui, nous recommandons vivement un **essai préalable** pour la mariée afin de définir et de perfectionner le look souhaité. Cela garantit que le jour J, tout se déroule sans accroc et que vous soyez entièrement satisfaite du résultat. Les essais sont généralement planifiés quelques semaines avant l'événement.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Quels types de produits utilisez-vous ?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Nous utilisons une gamme de **produits professionnels de haute qualité**, reconnus pour leur tenue longue durée et leur respect de la peau. Nous privilégions les marques hypoallergéniques et adaptons les produits à votre type de peau et à vos préférences. N'hésitez pas à nous faire part de vos éventuelles allergies ou sensibilités.</p>
                        </div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Puis-je réserver des services pour mon cortège (demoiselles d'honneur, mère de la mariée) ?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Absolument ! Nous proposons des **forfaits et des tarifs spéciaux** pour le cortège nuptial et les proches. Il est conseillé de nous informer du nombre de personnes et des services souhaités lors de votre demande de devis afin que nous puissions organiser au mieux les prestations le jour J.</p>
                        </div>
                    </div>
                    <div class="faq-item">
                        <div class="faq-question">
                            <span>Comment puis-je prendre rendez-vous ou demander un devis ?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Vous pouvez nous contacter directement via le formulaire de contact sur notre site, par téléphone au **+243 900 334 139**, ou en cliquant sur le bouton "Prendre Rendez-vous" ci-dessous. Nous serons ravis de discuter de vos besoins et de vous proposer une solution adaptée et un devis personnalisé.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact">
            <div class="container">
                <h2>Prête à rayonner ?</h2>
                <p style="margin-bottom: 30px;">Ne laissez aucun détail au hasard. Contactez-nous dès aujourd'hui pour planifier votre séance beauté personnalisée et faire de votre événement un moment inoubliable, où votre éclat sera au centre de toutes les attentions.</p>
                <a href="https://wa.me/+243900334139" target="_blank" class="btn-principal" style="background-color: var(--blanc); color: var(--rose-fuchsia);">Prendre Rendez-vous <i class="fab fa-whatsapp" style="margin-left: 10px;"></i></a>
            </div>
        </section>

    </main>

    <?php include('../INCLUDE/footer.php')?>
    <script>
        // Script pour la fonctionnalité FAQ
        document.addEventListener('DOMContentLoaded', () => {
            const faqQuestions = document.querySelectorAll('.faq-question');

            faqQuestions.forEach(question => {
                question.addEventListener('click', () => {
                    const faqItem = question.closest('.faq-item');

                    // Fermer toutes les autres FAQ ouvertes
                    document.querySelectorAll('.faq-item.active').forEach(item => {
                        if (item !== faqItem) {
                            item.classList.remove('active');
                        }
                    });

                    // Ouvrir ou fermer la FAQ cliquée
                    faqItem.classList.toggle('active');
                });
            });

            // Gérer la lecture des vidéos
            const videos = document.querySelectorAll('.hero-video-background video');
            videos.forEach(video => {
                video.addEventListener('loadeddata', () => {
                    video.play().catch(error => {
                        console.log('Autoplay was prevented:', error);
                        // Fallback: If autoplay is prevented, try to play on user interaction
                        document.body.addEventListener('click', () => {
                            video.play().catch(e => console.log('Click play failed:', e));
                        }, { once: true });
                    });
                });
            });
        });
    </script>
</body>
</html>