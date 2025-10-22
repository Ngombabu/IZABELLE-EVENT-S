  // Fait défiler la boîte de messages vers le bas au chargement et après envoi
        document.addEventListener('DOMContentLoaded', function() {
            var messageBox = document.querySelector('.boite-messages-admin');
            if (messageBox) {
                messageBox.scrollTop = messageBox.scrollHeight;
            }
        });
        document.querySelectorAll('.barre-laterale ul li a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });

                // Optionnel : Ajouter/retirer la classe 'actif' pour le lien cliqué
                document.querySelectorAll('.barre-laterale ul li a').forEach(link => {
                    link.classList.remove('actif');
                });
                this.classList.add('actif');
            });
        });

        // Optionnel : Mettre à jour la classe 'actif' lors du défilement
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('section[id]');
            const scrollY = window.pageYOffset;

            sections.forEach(current => {
                const sectionHeight = current.offsetHeight;
                const sectionTop = current.offsetTop - 50; // Ajustez la valeur de décalage si votre en-tête est fixe

                if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                    document.querySelectorAll('.barre-laterale ul li a').forEach(link => {
                        link.classList.remove('actif');
                    });
                    document.querySelector('.barre-laterale ul li a[href*=' + current.id + ']').classList.add('actif');
                }
            });
        });