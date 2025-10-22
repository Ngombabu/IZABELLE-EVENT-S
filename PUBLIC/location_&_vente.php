<?php
// Chemins de base pour la navigation et les ressources
$url_page_1 = 'https://isabelleevens.rf.gd/ADMIN/index.php'; // Remplacez par l'URL exacte de votre page 1
$url_page_2 = 'https://isabelleevens.rf.gd/index.php'; // Remplacez par l'URL exacte de votre page 2

// Les variables d'accueil peuvent être simplifiées ou gérées directement dans header.php si ce dernier est bien structuré.
// Ici, je garde la logique pour la cohérence avec votre code initial, même si elle n'est pas directement utilisée pour $accueil sur cette page.
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
$locationV = '../PUBLIC/location_&_vente.php'; // Lien de la page actuelle
$coiffureM = '../PUBLIC/coiffure_&_makup.php';
$realisation = '../PUBLIC/realisations.php';
$login = "../PUBLIC/login.php";
$sign = "../PUBLIC/connexion.php";
$logo = "../RESOURCE/SITE_IMAGE/logo.jpeg";

// Chemin vers l'image placeholder pour les robes
$placeholder_image_robe = '../RESOURCE/SITE_IMAGE/placeholder-robe.jpeg';

// Inclure le fichier de connexion à la base de données
include(__DIR__ . '/../INCLUDE/data.php');

// Récupérer les données des robes depuis la base de données
$robes = [];
$sql_robes = "SELECT id, nom, description, prix_location, prix_vente, image_url, disponible FROM robes ORDER BY date_ajout DESC"; // Tri par date d'ajout pour voir les dernières robes
$result_robes = $conn->query($sql_robes);

if ($result_robes && $result_robes->num_rows > 0) {
    while ($row = $result_robes->fetch_assoc()) {
        $robes[] = $row;
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
    <title>Isabelle Event's | Location & Vente de Robes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
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
            font-family: 'Open Sans', sans-serif;
            color: var(--noir);
            line-height: 1.6;
            background-color: #f8f8f8; /* Un fond léger pour le corps */
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

        /* Styles spécifiques au Hero Section Robes */
        .hero-robes {
            background: linear-gradient(45deg, var(--rose-fuchsia), var(--vert-vif));
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: var(--blanc);
            padding: 100px 20px;
            min-height: 60vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-radius: 0;
            box-shadow: none;
        }

        .hero-robes h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.8em;
            margin-bottom: 25px;
            text-shadow: 3px 3px 7px rgba(0,0,0,0.3);
            color: var(--blanc);
        }

        .hero-robes p {
            font-size: 1.4em;
            max-width: 900px;
            margin-bottom: 40px;
            text-shadow: 1px 1px 4px rgba(0,0,0,0.2);
        }

        /* Animation de dégradé */
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

        /* Section Collection de Robes */
        #collection {
            padding-top: 80px;
        }
        .grille-robes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); /* Similaire à services */
            gap: 35px; /* Espacement plus grand */
            margin-top: 40px;
        }

        .robe-item {
            background-color: var(--blanc);
            border: 1px solid var(--gris-clair);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            text-align: left;
            display: flex;
            flex-direction: column;
        }
        .robe-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .robe-item img {
            width: 100%;
            height: 300px; /* Hauteur fixe pour toutes les images de robe */
            object-fit: cover;
            border-radius: 12px 12px 0 0;
            transition: transform 0.3s ease;
        }
        .robe-item:hover img {
            transform: scale(1.05);
        }

        .robe-details {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .robe-details h3 {
            font-family: 'Playfair Display', serif;
            color: var(--gris-fonce);
            font-size: 2em;
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .robe-details p {
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

        .robe-details .prix {
            font-size: 1.3em;
            font-weight: bold;
            color: var(--rose-fuchsia);
            margin-bottom: 15px;
        }
        .robe-details .prix.location {
            color: var(--vert-vif); /* Différencier le prix de location */
        }
        .robe-details .prix.vente {
            color: var(--rose-fuchsia); /* Différencier le prix de vente */
        }
        .robe-details .disponibilite {
            font-size: 0.95em;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        .robe-details .disponibilite.available {
            color: var(--vert-vif);
        }
        .robe-details .disponibilite.unavailable {
            color: var(--noir); /* Couleur plus sombre pour non disponible */
        }

        .btn-detail {
            display: block; /* Change to block for full width */
            background-color: var(--rose-fuchsia);
            color: var(--blanc);
            padding: 14px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.05em;
            transition: background-color 0.3s ease, transform 0.3s ease;
            text-align: center;
            width: 100%; /* Bouton pleine largeur dans la carte */
            margin-top: auto; /* Pousse le bouton vers le bas de la carte */
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-detail:hover {
            background-color: #d11c7b;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        /* Section FAQ (réutilise les styles de coiffure_&_makup.php) */
        .section-faq {
            background-color: #f0f0f0; /* Fond légèrement grisé pour la section FAQ */
            padding: 80px 20px;
            margin-top: 30px;
            border-radius: 8px;
            box-shadow: inset 0 0 15px rgba(0,0,0,0.05);
        }

        .faq-container {
            max-width: 800px;
            margin: 40px auto 0;
            text-align: left;
        }

        .faq-item {
            background-color: var(--blanc);
            border: 1px solid var(--gris-clair);
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .faq-question {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            font-weight: bold;
            color: var(--gris-fonce);
            font-size: 1.1em;
            cursor: pointer;
            background-color: var(--blanc);
            border-bottom: 1px solid var(--gris-clair);
            transition: background-color 0.3s ease;
        }
        .faq-question:hover {
            background-color: #f0f0f0;
        }

        .faq-question i {
            font-size: 1em;
            transition: transform 0.3s ease;
            color: var(--vert-vif);
        }

        .faq-item.active .faq-question i {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, padding 0.3s ease-out;
            color: var(--gris-fonce);
            font-size: 0.95em;
        }

        .faq-item.active .faq-answer {
            max-height: 200px;
            padding: 20px;
        }

        /* Section de Contact finale */
        #contact {
            background: linear-gradient(135deg, var(--vert-vif), #6aa02a);
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
            .hero-robes h1 {
                font-size: 3.2em;
            }
            .hero-robes p {
                font-size: 1.2em;
            }
            section h2 {
                font-size: 2.5em;
            }
            .grille-robes {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 25px;
            }
            .robe-item img {
                height: 250px;
            }
            .robe-details h3 {
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
            .hero-robes {
                min-height: 45vh;
                padding: 60px 15px;
            }
            .hero-robes h1 {
                font-size: 2.5em;
            }
            .hero-robes p {
                font-size: 1em;
                margin-bottom: 30px;
            }
            .btn-principal {
                padding: 12px 25px;
                font-size: 1em;
            }
            .grille-robes {
                grid-template-columns: 1fr; /* Une seule colonne sur mobile */
                gap: 20px;
            }
            .robe-item img {
                height: 220px;
            }
            .robe-details {
                padding: 20px;
            }
            .robe-details h3 {
                font-size: 1.6em;
            }
            .robe-details p {
                font-size: 0.95em;
            }
            .btn-detail {
                padding: 12px 20px;
                font-size: 0.95em;
            }
            .faq-question, .faq-answer {
                padding: 15px;
                font-size: 1em;
            }
        }

        @media (max-width: 480px) {
            .hero-robes h1 {
                font-size: 2em;
            }
            .hero-robes p {
                font-size: 0.9em;
            }
            .robe-details h3 {
                font-size: 1.4em;
            }
            .robe-details .prix {
                font-size: 1.1em;
            }
        }
    </style>
</head>
<body>
    <?php $active2="style='border-bottom:2px solid #E91E89'";include('../INCLUDE/header.php')?>

     <main class="corps">
        <section class="hero-robes">
            <h1>Trouvez la Robe de Mariée de Vos Rêves</h1>
            <p>Découvrez notre collection exquise de robes de mariée disponibles à la location et à la vente. Chaque robe est sélectionnée avec soin pour vous offrir élégance, confort et un style inoubliable pour votre grand jour.</p>
            <a href="#collection" class="btn-principal">Voir la Collection</a>
        </section>

        <section id="collection">
            <div class="container">
                <h2>Notre Collection de Robes</h2>
                <p>Que vous cherchiez la location pour un jour spécial ou l'achat d'une pièce unique, nous avons la robe parfaite pour vous.</p>
                <div class="grille-robes">
                    <?php if (!empty($robes)): ?>
                        <?php foreach ($robes as $robe_item): ?>
                            <div class="robe-item">
                                <?php
                                $image_src = $placeholder_image_robe;
                                $image_path_fs = __DIR__ . '/../RESOURCE/USER_IMAGE/' . $robe_item['image_url'];

                                if (!empty($robe_item['image_url']) && file_exists($image_path_fs)) {
                                    $image_src = '../RESOURCE/USER_IMAGE/' . htmlspecialchars($robe_item['image_url']);
                                }
                                ?>
                                <img src="<?php echo $image_src; ?>"
                                     alt="Image de la robe : <?php echo htmlspecialchars($robe_item['nom']); ?>"
                                     onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($placeholder_image_robe); ?>';">
                                <div class="robe-details">
                                    <h3><?php echo htmlspecialchars($robe_item['nom']); ?></h3>
                                    <p><?php
                                        // Décode les entités HTML, puis coupe la chaîne et ajoute des points de suspension
                                        $description = html_entity_decode($robe_item['description'], ENT_QUOTES, 'UTF-8');
                                        echo nl2br(htmlspecialchars(mb_strimwidth($description, 0, 180, '...', 'UTF-8')));
                                    ?></p>

                                    <?php if ($robe_item['prix_location'] !== null && $robe_item['prix_location'] !== '0.00'): ?>
                                        <p class="prix location">Location : <?php echo number_format($robe_item['prix_location'], 2, ',', ' '); ?>$</p>
                                    <?php endif; ?>
                                    <?php if ($robe_item['prix_vente'] !== null && $robe_item['prix_vente'] !== '0.00'): ?>
                                        <p class="prix vente">Vente : <?php echo number_format($robe_item['prix_vente'], 2, ',', ' '); ?>$</p>
                                    <?php endif; ?>

                                    <p class="disponibilite <?php echo ($robe_item['disponible'] ? 'available' : 'unavailable'); ?>">
                                        <?php echo ($robe_item['disponible'] ? 'Disponible' : 'Non disponible actuellement'); ?>
                                    </p>
                                    <a href="https://wa.me/+243900334139" target="_blank" class="btn-detail">Contactez-nous</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="grid-column: 1 / -1; text-align: center; color: var(--gris-fonce); font-style: italic; padding: 30px;">
                            Aucune robe trouvée pour le moment. Revenez bientôt pour découvrir nos nouvelles collections !
                        </p>
                    <?php endif; ?>
                </div>
                <a href="#contact" class="btn-principal" style="margin-top: 50px;">Demandez un Essai</a>
            </div>
        </section>

        <section class="section-faq">
            <h2>Questions Fréquentes</h2>
            <div class="faq-container">
                <div class="faq-item">
                    <div class="faq-question">
                        <span>Comment fonctionne la location de robes ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>La location de robes se fait pour une durée définie (généralement 3 à 5 jours) incluant le jour de l'événement. Un dépôt de garantie est requis et restitué au retour de la robe en bon état. Les retouches mineures sont incluses.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Puis-je essayer les robes avant de décider ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Oui, nous proposons des rendez-vous d'essayage personnalisés dans notre showroom. Contactez-nous pour réserver votre créneau.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Quelles sont les options de paiement ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Nous acceptons les paiements par carte bancaire, virement et espèces. Des facilités de paiement peuvent être envisagées pour la vente.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Proposez-vous des retouches sur mesure ?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Des retouches simples sont incluses dans nos forfaits location et vente. Pour des modifications plus complexes, un devis additionnel sera établi.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact">
            <div class="container">
                <h2>Prête à dire "Oui" à la robe parfaite ?</h2>
                <p style="margin-bottom: 30px;">Contactez-nous dès aujourd'hui pour un rendez-vous personnalisé et laissez-nous vous aider à trouver la robe qui fera de vous la plus belle des mariées.</p>
                <a href="https://wa.me/+243900334139" target="_blank" class="btn-principal" style="background-color: var(--rose-fuchsia); color: var(--blanc);">Prendre Rendez-vous</a>
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

                    // Fermer toutes les autres FAQ ouvertes (optionnel, mais souvent préférable)
                    document.querySelectorAll('.faq-item.active').forEach(item => {
                        if (item !== faqItem) {
                            item.classList.remove('active');
                        }
                    });

                    // Ouvrir ou fermer la FAQ cliquée
                    faqItem.classList.toggle('active');
                });
            });
        });
    </script>
</body>
</html>