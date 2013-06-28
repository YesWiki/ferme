ferme
=====

Permet de gérer une ferme de wiki avec une interface ouverte permettant la création à la volée.

Installation
------------

Renomer le fichier ferme.config.sample.php en ferme.config.php et le renseigner.
Protéger le repertoire admin avec un .htaccess
Donner les droits en écriture sur :
 - wikis/
 - admin/tmp/
 - admin/archives/

Note
----

Nécessite l'accès au shell (commandes cp, mv, rm, tar, mysql, mysqldump)
testé avec php 5.3

Changelog : 
-----------
28/06/2013 : 
 - Gestion des thèmes selectionnable via les paquets
 - Prise en charge des wikis sans wakka.infos.php
 - Ignore les wikis dans wakka.config.php
 - Correction bug dans le calcul des espaces disque de chaque wiki

06/05/2013 : 
 - Ajout de l'interface d'administration.
 - Nouveau thème basé sur bootstrap

