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

Changelog : 
-----------
06/05/2013 : Affichage de la taille de la base de donnée et de fichiers, du poids des archives.

03/05/2013 : Ajout de la gestion des archives, suppression et restauration d'un wiki archivé.

28/04/2013 : Ajout de l'interface d'administration, Suppression et archivage des wikis.

