# TAI ETU

Structure du projet PHP + MySQL pour la gestion de recettes et utilisateurs.

- assets/ : ressources statiques (CSS, JS, images)
- includes/ : fichiers PHP réutilisables (connexion, fonctions)
- templates/ : fragments HTML communs (header, footer)
- pages/ : pages principales (login, signup, dashboard, etc.)
- index.php : point d'entrée du site
- .htaccess : sécurité

To create the data base use SQL files in ./script/

- First tai_etu_schema.slq (default baseline configuaration)
- add_roles.sql (roles table for users)
- seed_data.sql (some data)
