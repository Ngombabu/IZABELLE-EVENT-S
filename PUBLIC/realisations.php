<?php
// Chemins de base pour la navigation et les ressources
$url_page_1 = 'https://isabelleevens.rf.gd/ADMIN/index.php'; // Remplacez par l'URL exacte de votre page 1
$url_page_2 = 'https://isabelleevens.rf.gd/index.php'; // Remplacez par l'URL exacte de votre page 2

// Les variables d'accueil peuvent être simplifiées ou gérées directement dans header.php si ce dernier est bien structuré.
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

// Correction des chemins pour qu'ils soient relatifs au fichier qui inclut header.php,
// en supposant que realisations.php est dans PUBLIC/
$decoration = 'decoration.php';
$locationV = 'location_&_vente.php';
$coiffureM = 'coiffure_&_makup.php';
$realisation = 'realisations.php'; // Lien de la page actuelle
$login = "login.php"; // Chemin corrigé
$sign = "connexion.php"; // Chemin corrigé
$logo = "../RESOURCE/SITE_IMAGE/logo.jpeg";

// Chemin vers l'image placeholder pour les réalisations
$placeholder_image_realisation = '../RESOURCE/SITE_IMAGE/placeholder-realisation.jpeg';

// Inclure le fichier de connexion à la base de données
include(__DIR__ . '/../INCLUDE/data.php');

// Récupérer les données des réalisations depuis la base de données
$realisations_data = [];
$sql_realisations = "SELECT id, titre, description, image_url, date_realisation, date_publication FROM realisations ORDER BY date_realisation DESC, date_publication DESC";
$result_realisations = $conn->query($sql_realisations);

if ($result_realisations && $result_realisations->num_rows > 0) {
    while ($row = $result_realisations->fetch_assoc()) {
        $realisations_data[] = $row;
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
    <title>Isabelle Event's | Nos Réalisations</title>
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
            background-color: #f8f8f8; /* Assure un fond léger par défaut */
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

        /* Styles spécifiques au Hero Section Réalisations */
        .hero-realisations {
            background: linear-gradient(45deg, var(--rose-fuchsia), var(--vert-vif));
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: var(--blanc);
            padding: 100px 20px;
            min-height: 60vh; /* Hauteur ajustée pour être cohérente avec les autres pages dynamiques */
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

        .hero-realisations h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.8em;
            margin-bottom: 25px;
            text-shadow: 3px 3px 7px rgba(0,0,0,0.3);
            color: var(--blanc);
        }

        .hero-realisations p {
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

        /* Section Galerie Réalisations */
        #galerie {
            padding-top: 80px;
        }
        .galerie-realisations {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 35px;
            margin-top: 40px;
        }

        .realisation-item {
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
        .realisation-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .realisation-item img {
            width: 100%;
            height: 280px; /* Hauteur fixe pour toutes les images de réalisation */
            object-fit: cover;
            border-radius: 12px 12px 0 0;
            transition: transform 0.3s ease;
        }
        .realisation-item:hover img {
            transform: scale(1.05);
        }

        .realisation-info {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .realisation-info h3 {
            font-family: 'Playfair Display', serif;
            color: var(--gris-fonce);
            font-size: 2em;
            margin-bottom: 12px;
            line-height: 1.2;
        }

        .realisation-info p {
            color: var(--gris-fonce);
            font-size: 1.05em;
            line-height: 1.6;
            margin-bottom: 20px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 5; /* Limite la description à 5 lignes */
            -webkit-box-orient: vertical;
        }

        .realisation-info .date {
            font-size: 0.9em;
            color: var(--gris-clair);
            margin-top: 10px;
        }


        /* Section Témoignages */
        .temoignages {
            background-color: #f0f0f0;
            padding: 80px 20px;
            margin-top: 30px;
            border-radius: 8px;
            box-shadow: inset 0 0 15px rgba(0,0,0,0.05);
        }

        .temoignage-carousel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 35px;
            margin-top: 40px;
        }

        .temoignage-card {
            background-color: var(--blanc);
            border: 1px solid var(--gris-clair);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            padding: 30px;
            text-align: center;
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .temoignage-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .quote-icon {
            font-size: 3em;
            color: var(--vert-vif);
            margin-bottom: 20px;
        }

        .temoignage-card p {
            font-style: italic;
            color: var(--gris-fonce);
            margin-bottom: 20px;
            font-size: 1.1em;
        }

        .temoignage-card .auteur {
            font-weight: bold;
            color: var(--rose-fuchsia);
            font-size: 1.05em;
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
            .hero-realisations h1 {
                font-size: 3.2em;
            }
            .hero-realisations p {
                font-size: 1.2em;
            }
            section h2 {
                font-size: 2.5em;
            }
            .galerie-realisations, .temoignage-carousel {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 25px;
            }
            .realisation-item img {
                height: 250px;
            }
            .realisation-info h3 {
                font-size: 1.8em;
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
            .hero-realisations {
                min-height: 45vh;
                padding: 60px 15px;
            }
            .hero-realisations h1 {
                font-size: 2.5em;
            }
            .hero-realisations p {
                font-size: 1em;
                margin-bottom: 30px;
            }
            .btn-principal {
                padding: 12px 25px;
                font-size: 1em;
            }
            .galerie-realisations, .temoignage-carousel {
                grid-template-columns: 1fr; /* Une seule colonne sur mobile */
                gap: 20px;
            }
            .realisation-item img {
                height: 220px;
            }
            .realisation-info {
                padding: 20px;
            }
            .realisation-info p {
                font-size: 0.95em;
            }
            .quote-icon {
                font-size: 2.5em;
            }
            .temoignage-card p {
                font-size: 1em;
            }
        }

        @media (max-width: 480px) {
            .hero-realisations h1 {
                font-size: 2em;
            }
            .hero-realisations p {
                font-size: 0.9em;
            }
            .realisation-info h3 {
                font-size: 1.6em;
            }
            .realisation-item img {
                height: 180px;
            }
        }
    </style>
</head>
<body>
    <?php $active4="style='border-bottom:2px solid #E91E89'";include('../INCLUDE/header.php')?>

    <main class="corps">
        <section class="hero-realisations">
            <h1>Vos Rêves, Nos Réalisations</h1>
            <p>Découvrez en images la magie des événements qu'Isabelle Event's a eu le privilège d'orchestrer. Chaque cliché est le témoignage de moments inoubliables, conçus avec passion et souci du détail.</p>
        </section>

        <section id="galerie">
            <div class="container">
                <h2>Notre Portfolio Magnifique</h2>
                <p>Inspirez-vous de nos créations pour imaginer votre propre événement d'exception.</p>
                <div class="galerie-realisations">
                    <?php if (!empty($realisations_data)): ?>
                        <?php foreach ($realisations_data as $realisation_item): ?>
                            <div class="realisation-item">
                                <?php
                                $image_src = $placeholder_image_realisation;
                                // Construit le chemin complet de l'image sur le système de fichiers
                                $image_path_fs = __DIR__ . '/../RESOURCE/USER_IMAGE/' . $realisation_item['image_url'];

                                // Vérifie si l'URL de l'image est non vide et si le fichier existe
                                if (!empty($realisation_item['image_url']) && file_exists($image_path_fs)) {
                                    $image_src = '../RESOURCE/USER_IMAGE/' . htmlspecialchars($realisation_item['image_url']);
                                }
                                ?>
                                <img src="<?php echo $image_src; ?>"
                                     alt="Image de réalisation : <?php echo htmlspecialchars($realisation_item['titre']); ?>"
                                     onerror="this.onerror=null;this.src='<?php echo htmlspecialchars($placeholder_image_realisation); ?>';">
                                <div class="realisation-info">
                                    <h3><?php echo htmlspecialchars($realisation_item['titre']); ?></h3>
                                    <p><?php
                                        // Décode les entités HTML, puis coupe la chaîne et ajoute des points de suspension
                                        $description = html_entity_decode($realisation_item['description'], ENT_QUOTES, 'UTF-8');
                                        echo nl2br(htmlspecialchars(mb_strimwidth($description, 0, 200, '...', 'UTF-8')));
                                    ?></p>
                                    <?php if (!empty($realisation_item['date_realisation'])): ?>
                                        <p class="date">Date de réalisation : <?php echo date('d/m/Y', strtotime($realisation_item['date_realisation'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="grid-column: 1 / -1; text-align: center; color: var(--gris-fonce); font-style: italic; padding: 30px;">
                            Aucune réalisation trouvée pour le moment. Revenez bientôt pour découvrir nos derniers projets !
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="temoignages">
            <div class="container">
                <h2>Ce Que Nos Clients Disent</h2>
                <div class="temoignage-carousel">
                    <div class="temoignage-card">
                        <i class="fas fa-quote-left quote-icon"></i>
                        <p>"Isabelle Event's a transformé notre mariage en un rêve éveillé ! La décoration était au-delà de nos attentes, chaque détail était parfait. Un immense merci à toute l'équipe pour leur professionnalisme et leur créativité."</p>
                        <div class="auteur">- David & Carole</div>
                    </div>
                    <div class="temoignage-card">
                        <i class="fas fa-quote-left quote-icon"></i>
                        <p>"Nous avons été bluffés par la qualité du service et la beauté de la mise en scène. Isabelle a su capter exactement ce que nous voulions et a créé une atmosphère magique. Nous recommandons à 100% !"</p>
                        <div class="auteur">- esther.</div>
                    </div>
                    <div class="temoignage-card">
                        <i class="fas fa-quote-left quote-icon"></i>
                        <p>"Grâce à Isabelle Event's, notre grand jour a été absolument parfait. La gestion de la décoration et la coordination étaient impeccables. Nous n'avons eu qu'à profiter de chaque instant."</p>
                        <div class="auteur">- Celestin & Chloé</div>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact">
            <div class="container">
                <h2>Prêt(e) à créer votre propre événement inoubliable ?</h2>
                <p style="margin-bottom: 30px;">Contactez Isabelle Event's dès aujourd'hui pour donner vie à vos projets et faire de votre occasion spéciale un moment exceptionnel.</p>
                <a href="https://wa.me/+243900334139" target="_blank" class="btn-principal" style="background-color: var(--vert-vif); color: var(--blanc);">Discuter de Votre Projet</a>
            </div>
        </section>

    </main>

    <?php include('../INCLUDE/footer.php')?>
</body>
</html>