ferme
=====
Permet de gérer une ferme de wiki avec une interface ouverte permettant la création à la volée.

Installation
------------
Renomer le fichier ferme.config.sample.php en ferme.config.php et le renseigner.
Protéger le fichier admin.php avec un .htaccess
créer les dossiers avec les droits écritures : 
 - wikis/
 - archives/

Utiliser composer avec 'composer install' pour ajouter les dependances

Note
----
Nécessite l'accès au shell (commandes cp, du, mv, mysql, mysqldump, rm, tar)
testé avec php 5.6

Changelog : 
-----------
28/10/2015 :
 - Ajout mecanisme d'authentification pour l'interface d'administration
 - Fusion de index.php et admin.php au profit d'une entrée unique et d'un controleur.

27/10/2015 :
 - Utilisation de Twig pour les templates
 - Utilisation de 'du' pour calculer l'espace occupé par les wikis afin d'optimiser la vitesse d'affichage 

09/10/2015 : 
 - Autoload PSR-4 avec composer
 - optimisation de la fonction d'archivage

07/10/2015 :
 - Interface admin : La suppression de wiki est à nouveau pleinement fonctionnel

06/10/2015 :
 - Mise à jour du code pour respecter les PSR

28/06/2013 : 
 - Gestion des thèmes selectionnable via les paquets
 - Prise en charge des wikis sans wakka.infos.php
 - Ignore les wikis dans wakka.config.php
 - Correction bug dans le calcul des espaces disque de chaque wiki

06/05/2013 : 
 - Ajout de l'interface d'administration.
 - Nouveau thème basé sur bootstrap



