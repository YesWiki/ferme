<?php

//ATTENTION CE FICHIER DOIT ETRE ENCODE EN ISO-8859-1 sous peine de
//voir des caractï¿½re bizarre dans les wikis.

/***********************************************************************
 * Pour gï¿½nï¿½rer ce fichier en cas de nouvelle version de wikini,
 * 1. Exporter la base d'un wiki fraichement installï¿½
 * 2. Remplacer tous les " par \"
 * 3. Remplacer le prï¿½fixe des tables par ".$tablePrefix."
 * 4. Mettre chaque requette dans une cellule du
 * tableau listQuery
 * 5. Remplacer les dates par " . $date . "
 * 6. Remplacer le mot de passe a la fin du fichier par : " . $WikiAdminPasswordMD5 . "
 */

$date = date("Y-m-d H:i:s");

$listQuery = array(

    "CREATE TABLE `" . $tablePrefix . "acls` (
      `page_tag` varchar(50) NOT NULL DEFAULT '',
      `privilege` varchar(20) NOT NULL DEFAULT '',
      `list` text NOT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;",

    "CREATE TABLE `" . $tablePrefix . "links` (
      `from_tag` char(50) NOT NULL DEFAULT '',
      `to_tag` char(50) NOT NULL DEFAULT ''
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;",

    "INSERT INTO `" . $tablePrefix . "links` (`from_tag`, `to_tag`) VALUES
    ('AidE', 'AidE'),
    ('AidE', 'CoursUtilisationYesWiki'),
    ('AidE', 'DerniersChangements'),
    ('AidE', 'DerniersCommentaires'),
    ('AidE', 'PagePrincipale'),
    ('AidE', 'ParametresUtilisateur'),
    ('AidE', 'ReglesDeFormatage'),
    ('AidE', 'WikiAdmin'),
    ('BacASable', 'BacASable'),
    ('BacASable', 'DerniersChangements'),
    ('BacASable', 'DerniersCommentaires'),
    ('BacASable', 'PagePrincipale'),
    ('BacASable', 'ParametresUtilisateur'),
    ('BacASable', 'WikiAdmin'),
    ('BazaR', 'BazaR'),
    ('BazaR', 'DerniersChangements'),
    ('BazaR', 'DerniersCommentaires'),
    ('BazaR', 'PagePrincipale'),
    ('BazaR', 'ParametresUtilisateur'),
    ('BazaR', 'WikiAdmin'),
    ('CoursUtilisationYesWiki', 'AccueiL'),
    ('CoursUtilisationYesWiki', 'CoursUtilisationYesWiki'),
    ('CoursUtilisationYesWiki', 'DerniersChangements'),
    ('CoursUtilisationYesWiki', 'DerniersChangementsRSS'),
    ('CoursUtilisationYesWiki', 'DerniersCommentaires'),
    ('CoursUtilisationYesWiki', 'JamesBond'),
    ('CoursUtilisationYesWiki', 'ListeDesActionsWikini'),
    ('CoursUtilisationYesWiki', 'PageFooter'),
    ('CoursUtilisationYesWiki', 'PageHeader'),
    ('CoursUtilisationYesWiki', 'PageMenu'),
    ('CoursUtilisationYesWiki', 'PageMenuHaut'),
    ('CoursUtilisationYesWiki', 'PagePrincipale'),
    ('CoursUtilisationYesWiki', 'PageRapideHaut'),
    ('CoursUtilisationYesWiki', 'PagesOrphelines'),
    ('CoursUtilisationYesWiki', 'ParametresUtilisateur'),
    ('CoursUtilisationYesWiki', 'ReglesDeFormatage'),
    ('CoursUtilisationYesWiki', 'TableauDeBordDeCeWiki'),
    ('CoursUtilisationYesWiki', 'WikiAdmin'),
    ('CoursUtilisationYesWiki', 'YesWiki'),
    ('DerniersChangements', 'DerniersChangements'),
    ('DerniersChangements', 'DerniersCommentaires'),
    ('DerniersChangements', 'PagePrincipale'),
    ('DerniersChangements', 'ParametresUtilisateur'),
    ('DerniersChangements', 'WikiAdmin'),
    ('DerniersChangementsRSS', 'DerniersChangements'),
    ('DerniersChangementsRSS', 'DerniersChangementsRSS'),
    ('DerniersChangementsRSS', 'DerniersCommentaires'),
    ('DerniersChangementsRSS', 'PagePrincipale'),
    ('DerniersChangementsRSS', 'ParametresUtilisateur'),
    ('DerniersChangementsRSS', 'WikiAdmin'),
    ('DerniersCommentaires', 'DerniersChangements'),
    ('DerniersCommentaires', 'DerniersCommentaires'),
    ('DerniersCommentaires', 'PagePrincipale'),
    ('DerniersCommentaires', 'ParametresUtilisateur'),
    ('DerniersCommentaires', 'WikiAdmin'),
    ('MotDePassePerdu', 'DerniersChangements'),
    ('MotDePassePerdu', 'DerniersCommentaires'),
    ('MotDePassePerdu', 'MotDePassePerdu'),
    ('MotDePassePerdu', 'PagePrincipale'),
    ('MotDePassePerdu', 'ParametresUtilisateur'),
    ('MotDePassePerdu', 'WikiAdmin'),
    ('PageFooter', 'DerniersChangements'),
    ('PageFooter', 'DerniersCommentaires'),
    ('PageFooter', 'PageFooter'),
    ('PageFooter', 'PagePrincipale'),
    ('PageFooter', 'ParametresUtilisateur'),
    ('PageFooter', 'WikiAdmin'),
    ('PageHeader', 'DerniersChangements'),
    ('PageHeader', 'DerniersCommentaires'),
    ('PageHeader', 'PageHeader'),
    ('PageHeader', 'PagePrincipale'),
    ('PageHeader', 'ParametresUtilisateur'),
    ('PageHeader', 'WikiAdmin'),
    ('PageMenu', 'DerniersChangements'),
    ('PageMenu', 'DerniersCommentaires'),
    ('PageMenu', 'PageMenu'),
    ('PageMenu', 'PagePrincipale'),
    ('PageMenu', 'ParametresUtilisateur'),
    ('PageMenu', 'WikiAdmin'),
    ('PageMenuHaut', 'DerniersChangements'),
    ('PageMenuHaut', 'DerniersCommentaires'),
    ('PageMenuHaut', 'PageMenuHaut'),
    ('PageMenuHaut', 'PagePrincipale'),
    ('PageMenuHaut', 'ParametresUtilisateur'),
    ('PageMenuHaut', 'WikiAdmin'),
    ('PagePrincipale', 'BazaR'),
    ('PagePrincipale', 'DerniersChangements'),
    ('PagePrincipale', 'DerniersCommentaires'),
    ('PagePrincipale', 'PagePrincipale'),
    ('PagePrincipale', 'ParametresUtilisateur'),
    ('PagePrincipale', 'TableauDeBord'),
    ('PagePrincipale', 'WikiAdmin'),
    ('PageRapideHaut', 'DerniersChangements'),
    ('PageRapideHaut', 'DerniersCommentaires'),
    ('PageRapideHaut', 'PagePrincipale'),
    ('PageRapideHaut', 'PageRapideHaut'),
    ('PageRapideHaut', 'ParametresUtilisateur'),
    ('PageRapideHaut', 'WikiAdmin'),
    ('PagesOrphelines', 'DerniersChangements'),
    ('PagesOrphelines', 'DerniersCommentaires'),
    ('PagesOrphelines', 'PagePrincipale'),
    ('PagesOrphelines', 'PagesOrphelines'),
    ('PagesOrphelines', 'ParametresUtilisateur'),
    ('PagesOrphelines', 'WikiAdmin'),
    ('PageTitre', 'DerniersChangements'),
    ('PageTitre', 'DerniersCommentaires'),
    ('PageTitre', 'PagePrincipale'),
    ('PageTitre', 'PageTitre'),
    ('PageTitre', 'ParametresUtilisateur'),
    ('PageTitre', 'WikiAdmin'),
    ('ParametresUtilisateur', 'DerniersChangements'),
    ('ParametresUtilisateur', 'DerniersCommentaires'),
    ('ParametresUtilisateur', 'PagePrincipale'),
    ('ParametresUtilisateur', 'ParametresUtilisateur'),
    ('ParametresUtilisateur', 'WikiAdmin'),
    ('RechercheTexte', 'DerniersChangements'),
    ('RechercheTexte', 'DerniersCommentaires'),
    ('RechercheTexte', 'PagePrincipale'),
    ('RechercheTexte', 'ParametresUtilisateur'),
    ('RechercheTexte', 'RechercheTexte'),
    ('RechercheTexte', 'WikiAdmin'),
    ('ReglesDeFormatage', 'DerniersChangements'),
    ('ReglesDeFormatage', 'DerniersCommentaires'),
    ('ReglesDeFormatage', 'PagePrincipale'),
    ('ReglesDeFormatage', 'ParametresUtilisateur'),
    ('ReglesDeFormatage', 'ReglesDeFormatage'),
    ('ReglesDeFormatage', 'WikiAdmin'),
    ('TableauDeBord', 'DerniersChangements'),
    ('TableauDeBord', 'DerniersCommentaires'),
    ('TableauDeBord', 'PagePrincipale'),
    ('TableauDeBord', 'ParametresUtilisateur'),
    ('TableauDeBord', 'TableauDeBord'),
    ('TableauDeBord', 'WikiAdmin'),
    ('WikiAdmin', 'DerniersChangements'),
    ('WikiAdmin', 'DerniersCommentaires'),
    ('WikiAdmin', 'PageFooter'),
    ('WikiAdmin', 'PageHeader'),
    ('WikiAdmin', 'PageMenuHaut'),
    ('WikiAdmin', 'PagePrincipale'),
    ('WikiAdmin', 'PageRapideHaut'),
    ('WikiAdmin', 'PageTitre'),
    ('WikiAdmin', 'ParametresUtilisateur'),
    ('WikiAdmin', 'WikiAdmin');",

    "CREATE TABLE `" . $tablePrefix . "nature` (
      `bn_id_nature` int(10) UNSIGNED NOT NULL DEFAULT '0',
      `bn_label_nature` varchar(255) DEFAULT NULL,
      `bn_description` text,
      `bn_condition` text,
      `bn_ce_id_menu` int(3) UNSIGNED NOT NULL DEFAULT '0',
      `bn_commentaire` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
      `bn_appropriation` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
      `bn_image_titre` varchar(255) NOT NULL DEFAULT '',
      `bn_image_logo` varchar(255) NOT NULL DEFAULT '',
      `bn_couleur_calendrier` varchar(255) NOT NULL DEFAULT '',
      `bn_picto_calendrier` varchar(255) NOT NULL DEFAULT '',
      `bn_template` text NOT NULL,
      `bn_ce_i18n` varchar(5) NOT NULL DEFAULT '',
      `bn_type_fiche` varchar(255) NOT NULL,
      `bn_label_class` varchar(255) NOT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;",

    "CREATE TABLE `" . $tablePrefix . "pages` (
      `id` int(10) UNSIGNED NOT NULL,
      `tag` varchar(50) NOT NULL DEFAULT '',
      `time` datetime NOT NULL,
      `body` longtext NOT NULL,
      `body_r` text NOT NULL,
      `owner` varchar(50) NOT NULL DEFAULT '',
      `user` varchar(50) NOT NULL DEFAULT '',
      `latest` enum('Y','N') NOT NULL DEFAULT 'N',
      `handler` varchar(30) NOT NULL DEFAULT 'page',
      `comment_on` varchar(50) NOT NULL DEFAULT ''
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;",

    "INSERT INTO `" . $tablePrefix . "pages` (`id`, `tag`, `time`, `body`, `body_r`, `owner`, `user`, `latest`, `handler`, `comment_on`) VALUES
    (1, 'WikiAdmin', '" . $date . "', '{{grid}}\n{{col size=\"6\"}}\n===Gérer les menus de ce wiki===\n - [[PageMenuHaut Editer menu horizontal d\'en haut]]\n - [[PageTitre Editer le titre]]\n - [[PageRapideHaut Editer le menu roue crantée]]\n - [[PageHeader Editer le bandeau]]\n - [[PageFooter Editer le footer]]\n------\n===Gérer les droits des pages===\n{{gererdroits}}\n------\n===Gérer les thèmes des pages===\n{{gererthemes}}\n------\n{{end elem=\"col\"}}\n{{col size=\"6\"}}\n===Gérer les groupes d\'utilisateurs===\nnécessite une connexion admin\n{{editgroups}}\n------\n===Gestion des tags ===\n{{admintag}}\n------\n===Gestion des commentaires ===\n{{erasespamedcomments}}\n------\n{{end elem=\"col\"}}\n{{end elem=\"grid\"}}\n', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (2, 'CoursUtilisationYesWiki', '" . $date . "', '======Cours sur l\'utilisation de YesWiki======\n====Le principe \"Wiki\"====\nWiki Wiki signifie rapide, en Hawaïen. \n==N\'importe qui peut modifier la page==\n[[http://the.honoluluadvertiser.com/dailypix/2002/Aug/19/ln07a_b.jpg le bus wiki wiki]]\n\n**Les Wiki sont des dispositifs permettant la modification de pages Web de façon simple, rapide et interactive.**\nYesWiki fait partie de la famille des wiki. Il a la particularité d\'être très facile à installer.\n\n=====Mettre du contenu=====\n====Écrire ou coller du texte====\n - Dans chaque page du site, un double clic sur la page ou un clic sur le lien \"Éditer cette page\" en bas de page permet de passer en mode \"Édition\".\n - On peut alors écrire ou coller du texte\n - On peut voir un aperçu des modifications ou sauver directement la page modifiée en cliquant sur les boutons en bas de page.\n\n====Écrire un commentaire (optionnel)====\nSi la configuration de la page permet d\'ajouter des commentaires, on peut cliquer sur : Afficher commentaires/formulaire en bas de chaque page.\nUn formulaire apparaitra et vous permettra de rajouter votre commentaire.\n\n\n=====Mise en forme : Titres et traits=====\n--> Voir la page ReglesDeFormatage\n\n====Faire un titre====\n======Très gros titre====== \ns\'écrit en syntaxe wiki : \"\"======Très gros titre======\"\"\n\n\n==Petit titre== \ns\'écrit en syntaxe wiki : \"\"==Petit titre==\"\" \n\n\n//On peut mettre entre 2 et 6 = de chaque coté du titre pour qu\'il soit plus petit ou plus grand//\n\n====Faire un trait de séparation====\nPour faire apparaitre un trait de séparation\n----\ns\'écrit en syntaxe wiki : \"\"----\"\"\n\n=====Mise en forme : formatage texte=====\n====Mettre le texte en gras====\n**texte en gras**\ns\'écrit en syntaxe wiki : \"\"**texte en gras**\"\" \n\n====Mettre le texte en italique====\n//texte en italique//\ns\'écrit en syntaxe wiki : \"\"//texte en italique//\"\"\n\n====Mettre le texte en souligné====\n__texte en souligné__\ns\'écrit en syntaxe wiki : \"\"__texte en souligné__\"\"\n\n=====Mise en forme : listes=====\n====Faire une liste à puce====\n - point 1\n - point 2\n\ns\'écrit en syntaxe wiki : \n\"\" - point 1\"\"\n\"\" - point 2\"\"\n\nAttention : de bien mettre un espace devant le tiret pour que l\'élément soit reconnu comme liste\n\n\n====Faire une liste numérotée====\n 1) point 1\n 2) point 2\n\ns\'écrit en syntaxe wiki : \n\"\" 1) point 1\"\"\n\"\" 2) point 2\"\"\n\n=====Les liens : le concept des \"\"ChatMots\"\"=====\n====Créer une page YesWiki : ====\nLa caractéristique qui permet de reconnaitre un lien dans un wiki : son nom avec un mot contenant au moins deux majuscules non consécutives (un \"\"ChatMot\"\", un mot avec deux bosses). \n\n==== Lien interne====\n - On écrit le \"\"ChatMot\"\" de la page YesWiki vers laquelle on veut pointer.\n  - Si la page existe, un lien est automatiquement créé\n  - Si la page n\'existe pas, apparait un lien avec crayon. En cliquant dessus on arrive vers la nouvelle page en mode \"Édition\".\n\n=====Les liens : personnaliser le texte=====\n====Personnaliser le texte du lien internet====\nentre double crochets : \"\"[[AccueiL aller à la page d\'accueil]]\"\", apparaitra ainsi : [[AccueiL aller à la page d\'accueil]].\n\n====Liens vers d\'autres sites Internet====\nentre double crochets : \"\"[[http://outils-reseaux.org aller sur le site d\'Outils-Réseaux]]\"\", apparaitra ainsi : [[http://outils-reseaux.org aller sur le site d\'Outils-Réseaux]].\n\n\n=====Télécharger une image, un document=====\n====On dispose d\'un lien vers l\'image ou le fichier====\nentre double crochets :\n - \"\"[[http://mondomaine.ext/image.jpg texte de remplacement de l\'image]]\"\" pour les images.\n - \"\"[[http://mondomaine.ext/document.pdf texte du lien vers le téléchargement]]\"\" pour les documents.\n\n====L\'action \"attach\"====\nEn cliquant sur le pictogramme représentant une image dans la barre d\'édition, on voit apparaître la ligne de code suivante :\n\"\"{{attach file=\" \" desc=\" \" class=\"left\" }} \"\"\n\nEntre les premières guillemets, on indique le nom du document (ne pas oublier son extension (.jpg, .pdf, .zip).\nEntre les secondes, on donne quelques éléments de description qui deviendront le texte du lien vers le document\nLes troisièmes guillemets, permettent, pour les images, de positionner l\'image à gauche (left), ou à droite (right) ou au centre (center)\n\"\"{{attach file=\"nom-document.doc\" desc=\"mon document\" class=\"left\" }} \"\"\n\nQuand on sauve la page, un lien en point d\'interrogation apparait. En cliquant dessus, on arrive sur une page avec un système pour aller chercher le document sur sa machine (bouton \"parcourir\"), le sélectionner et le télécharger.\n\n=====Intégrer du html=====\nSi on veut faire une mise en page plus compliquée, ou intégrer un widget, il faut écrire en html. Pour cela, il faut mettre notre code html entre double guillemets.\nPar exemple : \"\"<textarea style=\"width:100%;\">&quot;&quot;<span style=\"color:#0000EE;\">texte coloré</span>&quot;&quot;</textarea>\"\"\ndonnera :\n\"\"<span style=\"color:#0000EE;\">texte coloré</span>\"\"\n\n\n=====Les pages spéciales=====\n - PageHeader\n - PageFooter\n - PageMenuHaut\n - PageMenu\n - PageRapideHaut\n\n - PagesOrphelines\n - TableauDeBordDeCeWiki\n \n\n=====Les actions disponibles=====\nVoir la page spéciale : ListeDesActionsWikini\n\n**les actions à ajouter dans la barre d\'adresse:**\nrajouter dans la barre d\'adresse :\n/edit : pour passer en mode Edition\n/slide_show : pour transformer la texte en diaporama\n\n===La barre du bas de page permet d\'effectuer diverses action sur la page===\n - voir l\'historique\n - partager sur les réseaux sociaux\n...\n\n=====Suivre la vie du site=====\n - Dans chaque page, en cliquant sur la date en bas de page on accède à **l\'historique** et on peut comparer les différentes versions de la page.\n\n - **Le TableauDeBordDeCeWiki : ** pointe vers toutes les pages utiles à l\'analyse et à l\'animation du site.\n\n - **La page DerniersChangements** permet de visualiser les modifications qui ont été apportées sur l\'ensemble du site, et voir les versions antérieures. Pour l\'avoir en flux RSS DerniersChangementsRSS\n\n - **Les lecteurs de flux RSS** :  offrent une façon simple, de produire et lire, de façon standardisée (via des fichiers XML), des fils d\'actualité sur internet. On récupère les dernières informations publiées. On peut ainsi s\'abonner à différents fils pour mener une veille technologique par exemple.\n[[http://www.wikini.net/wakka.php?wiki=LecteursDeFilsRSS Différents lecteurs de flux RSS]]\n\n\n\n=====L\'identification=====\n====Première identification = création d\'un compte YesWiki====\n    - aller sur la page spéciale ParametresUtilisateur, \n    - choisir un nom YesWiki qui comprend 2 majuscules. //Exemple// : JamesBond\n    - choisir un mot de passe et donner un mail\n    - cliquer sur s\'inscrire\n\n====Identifications suivantes====\n    - aller sur ParametresUtilisateur, \n    - remplir le formulaire avec son nom YesWiki et son mot de passe\n    - cliquer sur \"connexion\"\n\n\n\n=====Gérer les droits d\'accès aux pages=====\n - **Chaque page possède trois niveaux de contrÉ´le d\'accès :**\n     - lecture de la page\n     - écriture/modification de la page\n     - commentaire de la page\n\n - **Les contrÉ´les d\'accès ne peuvent être modifiés que par le propriétaire de la page**\nOn est propriétaire des pages que l\'ont créent en étant identifié. Pour devenir \"propriétaire\" d\'une page, il faut cliquer sur Appropriation. \n\n - Le propriétaire d\'une page voit apparaître, dans la page dont il est propriétaire, l\'option \"**Éditer permissions**\" : cette option lui permet de **modifier les contrÉ´les d\'accès**.\nCes contrÉ´les sont matérialisés par des colonnes où le propriétaire va ajouter ou supprimer des informations.\nLe propriétaire peut compléter ces colonnes par les informations suivantes, séparées par des espaces :\n     - le nom d\'un ou plusieurs utilisateurs : par exemple \"\"JamesBond\"\" \n     - le caractère ***** désignant tous les utilisateurs\n     - le caractère **+** désignant les utilisateurs enregistrés\n     - le caractère **!** signifiant la négation : par exemple !\"\"JamesBond\"\" signifie que \"\"JamesBond\"\" **ne doit pas** avoir accès à cette page\n\n - **Droits d\'accès par défaut** : pour toute nouvelle page créée, YesWiki applique des droits d\'accès par défaut : sur ce YesWiki, les droits en lecture et écriture sont ouverts à tout internaute.\n\n=====Supprimer une page=====\n\n - **2 conditions :**\n    - **on doit être propriétaire** de la page et **identifié** (voir plus haut),\n    - **la page doit être \"orpheline\"**, c\'est-à-dire qu\'aucune page ne pointe vers elle (pas de lien vers cette page sur le YesWiki), on peut voir toutes les pages orphelines en visitant la page : PagesOrphelines\n\n - **On peut alors cliquer sur l\'\'option \"Supprimer\"** en bas de page.\n\n\n\n=====Changer le look et la disposition=====\nEn mode édition, si on est propriétaire de la page, ou que les droits sont ouverts, on peut changer la structure et la présentation du site, en jouant avec les listes déroulantes en bas de page : Thème, Squelette, Style.\n\n', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (3, 'PageMenu', '" . $date . "', ' - [[PagePrincipale Accueil]]\n', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (4, 'TableauDeBord', '" . $date . "', '======Tableau de bord======\n{{grid}}\n{{col size=\"6\"}} \n==== 8 derniers comptes utilisateurs ====\n{{Listusers last=\"8\"}}\n------\n==== 8 dernières pages modifiées ====\n{{recentchanges max=\"8\"}}\n------\n==== 5 dernières pages commentées ====\n{{RecentlyCommented max=\"5\"}}\n------\n{{end elem=\"col\"}} \n{{col size=\"6\"}} \n==== Index des pages ====\n{{pageindex}}\n------\n==== Pages orphelines ====\n{{OrphanedPages}}\n------\n{{end elem=\"col\"}}\n{{end elem=\"grid\"}}\n', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (5, 'PageMenuHaut', '" . $date . "', ' - [[PagePrincipale Accueil]]\n', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (6, 'BazaR', '" . $date . "', '{{bazar}}\n', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (7, 'BacASable', '" . $date . "', ' - si vous cliquez sur \"éditer cette page\"\n - vous pourrez écrire dans cette page comme bon vous semble\n - puis en cliquant sur \"sauver\" vous pourrez enregistrer vos modifications', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (8, 'PagesOrphelines', '" . $date . "', '{{OrphanedPages}}', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (9, 'PagePrincipale', '" . $date . "', '======Félicitations, votre wiki est installé ! ======\n\nPour vous approprier votre nouvel outil, voici quelques éléments pour pour démarrer :\n\n - pour modifier une page de votre Yeswiki, le double-clic est votre ami !\n  - essayez ainsi de modifier la page présente (page d\'accueil). Un double clic n\'importe ou dans la partie centrale de la page vous permettra d\'atteindre le mode édition\n  - vous souhaitez modifier le menu horizontal général ? Double cliquez gauche sur ce menu (en dehors des onglets), et vous aurez accès à l\'édition de ce menu. Utiliser les tirets (\" - \") pour créer de nouvelles entrées.\n\n - le menu d\'administration en haut à droite, accessible depuis la roue crantée (clic gauche) vous permettra :\n  - de [[WikiAdmin gérer le site (pages, comptes et groupes utilisateurs,..)]]\n  - d\'administrer la [[BazaR base de données Bazar]]\n  - de consulter les [[TableauDeBord dernières modifications sur le wiki]]\n', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (10, 'ParametresUtilisateur', '" . $date . "', '{{UserSettings}}\n\n**Mot de passe perdu**\n{{lostpassword}}\n', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (11, 'ReglesDeFormatage', '" . $date . "', '{{grid}}\n{{col size=\"6\"}}\n=====Règles de formatage=====\n===Accentuation===\n\"\"<pre>\"\"**\"\"**Gras**\"\"**\n//\"\"//Italique//\"\"//\n__\"\"__Souligné__\"\"__\n@@\"\"@@Barré@@\"\"@@\"\"</pre>\"\"\n\n===Titres===\n\"\"<pre>\"\"======\"\"======Titre 1======\"\"======\n=====\"\"=====Titre 2=====\"\"=====\n====\"\"====Titre 3====\"\"====\n===\"\"===Titre 4===\"\"===\n==\"\"==Titre 5==\"\"==\"\"</pre>\"\"\n\n===Listes===\n\"\"<pre> - Liste à puce niveau 1\n - Liste à puce niveau 1\n  - Liste à puce niveau 2\n  - Liste à puce niveau 2\n - Liste à puce niveau 1\n\n 1. Liste énumérée\n 2. Liste énumérée\n 3. Liste énumérée</pre>\"\"\n\n===Liens===\n\"\"<pre>[[http://www.exemple.com Texte à afficher pour le lien externe]]\"\"\n\"\"[[PageDeCeWiki Texte à afficher pour le lien interne]]</pre>\"\"\n\n===Images===\n//Pour télécharger une image, utiliser l\'action \"\"{{attach file=\"image.jpg\" ...}}\"\".//\n\"\"<pre>[[http://www.exemple.com/image.jpg Texte de remplacement pour l\'image]]</pre>\"\"\n\n===Tableaux===\n\"\"<pre>| Colonne 1 | Colonne 2 | Colonne 3 |\n| John     | Doe      | Male     |\n| Mary     | Smith    | Female   |\n</pre>\"\"\n\n===Code source===\n\"\"<pre>\"\"##\"\"##var example = \"hello!\";\nalert(example);\n##\"\"##\"\"</pre>\"\"\n\n===Ecrire en html===\n\"\"<pre>&quot;&quot;\"\"<strong>\"\"Hello !\"\"</strong>\"\"&quot;&quot;</pre>\"\"\n{{end elem=\"col\"}}\n{{col size=\"6\"}}\n=====Code exemples=====\n===Lien qui force l\'ouverture vers une page extérieure===\n%%\"\"<a href=\"http://exemple.com\" target=\"_blank\">ton texte</a>\"\"%%\n\n===Insérer un iframe===\n//Inclure un autre site, ou un pad, ou une vidéo youtube, etc...//\n%%\"\"<iframe width=100% height=\"1250\" src=\"http://exemple.com\" frameborder=\"0\" allowfullscreen></iframe>\"\"%%\n\n===Texte en couleur===\n//Voir les codes hexa des couleurs : [[http://fr.wikipedia.org/wiki/Liste_de_couleurs http://fr.wikipedia.org/wiki/Liste_de_couleurs]]//\n\"\"<pre><span style=\"color:#446611;\">\"\"<span style=\"color:#446611;\">texte coloré</span>\"\"</span></pre>\"\"\n\n===Message d\'alerte===\n//Avec une croix pour le fermer.//\n%%\"\"<div class=\"alert\">\n<button type=\"button\" class=\"close\" data-dismiss=\"alert\">Éâ</button>\nAttention ! Voici votre message.\n</div>\"\"%%\n\n===Label \"important\" ou \"info\"===\n\"\"<span class=\"label label-danger\">Important</span>\"\" et \"\"<span class=\"label label-info\">Info</span>\"\"\n%%\"\"<span class=\"label label-danger\">Important</span>\"\" et \"\"<span class=\"label label-info\">Info</span>\"\"%%\n\n===Placer du code en commentaire sur la page===\n%%\"\"<!-- en utilisant ce code on peut mettre du texte qui n\'apparait pas sur la page... ce qui permet de laisser des explications par exemple ou même d\'écrire du texte en prépa d\'une publication future -->\"\"%%\n\n\n====Les actions====\n===Mise en page par colonne===\n%%{{grid}}\n{{col size=\"6\"}}\n===Titre de la colonne 1===\nTexte colonne 1\n{{end elem=\"col\"}}\n{{col size=\"6\"}}\n===Titre de la colonne 2===\nTexte colonne 2\n{{end elem=\"col\"}}\n{{end elem=\"grid\"}}%%\n\n===Boutons===\n\"\"<pre>{{button}}</pre>\"\"\n\n===Formulaires de contact===\n\"\"<pre>{{contact mail=\"adresse.mail@exemple.com\"}}</pre>\"\"\n\n===Bases de données===\n\"\"<pre>{{bazar}}</pre>\"\"\n\n===Inclure une page dans une autre===\n%%{{include page=\"NomPageAInclure\"}} %%\nPour inclure une page d\'un autre yeswiki : ( Noter le pipe \"\"|\"\" après les premiers \"\"[[\"\" ) %%[[|http://lesite.org/nomduwiki PageAInclure]]%%\n\n===Image de fond===\n//Avec possibilité de mettre du texte par dessus//\n%%{{backgroundimage height=\"150\" file=\"monbandeau.jpg\" class=\"white text-center doubletitlesize\"}}\n\n=====Texte du titre=====\ndescription\n\n{{endbackgroundimage}}%%\n{{end elem=\"col\"}}\n{{end elem=\"grid\"}}\n', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (12, 'DerniersCommentaires', '" . $date . "', '{{RecentlyCommented}}', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (13, 'PageRapideHaut', '" . $date . "', '{{moteurrecherche template=\"moteurrecherche_button.tpl.html\"}}\n{{buttondropdown icon=\"glyphicon glyphicon-cog\" caret=\"0\"}}\n - {{login template=\"modal.tpl.html\" nobtn=\"1\"}}\n - ------\n - {{button nobtn=\"1\" icon=\"glyphicon glyphicon-question-sign\" text=\"Aide\" link=\"AidE\"}}\n - ------\n - {{button nobtn=\"1\" icon=\"glyphicon glyphicon-wrench\" text=\"Gestion du site\" link=\"WikiAdmin\"}}\n - {{button nobtn=\"1\" icon=\"glyphicon glyphicon-dashboard\" text=\"Tableau de bord\" link=\"TableauDeBord\"}}\n - {{button nobtn=\"1\" icon=\"glyphicon glyphicon-briefcase\" text=\"Base de données\" link=\"BazaR\"}}\n{{end elem=\"buttondropdown\"}}\n', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (14, 'RechercheTexte', '" . $date . "', '{{TextSearch}}', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (15, 'DerniersChangements', '" . $date . "', '{{RecentChanges}}', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (16, 'PageTitre', '" . $date . "', '{{configuration param=\"wakka_name\"}}', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (17, 'AidE', '" . $date . "', '=====Les pages d\'aide=====\n\n	- [[CoursUtilisationYesWiki Cours sur l\'utilisation de YesWiki]]\n	- ReglesDeFormatage : résumé des syntaxes permettant la mise en forme du texte.\n\n\"\"<a onclick=\"var iframe = document.getElementById(\'yeswiki-doc\');iframe.src = \'http://yeswiki.net/wakka.php?wiki=DocumentatioN/iframe\';\" class=\"btn btn-default\"><i class=\"glyphicon glyphicon-home\"></i> Accueil de la documentation</a><iframe id=\"yeswiki-doc\" width=\"100%\" height=\"1000\" frameborder=\"0\" src=\"http://yeswiki.net/wakka.php?wiki=DocumentatioN/iframe\"></iframe>\"\"\n', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (18, 'DerniersChangementsRSS', '" . $date . "', '{{recentchangesrss}}', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (19, 'PageFooter', '" . $date . "', '\"\"<div class=\"text-center\">\"\"(>^_^)> Galope sous [[http://www.yeswiki.net YesWiki]] <(^_^<)\"\"</div>\"\"\n', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (20, 'PageHeader', '" . $date . "', '======Description de mon wiki======\nDouble cliquer ici pour changer le texte.\n', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', ''),
    (21, 'MotDePassePerdu', '" . $date . "', '{{lostpassword}}\n', '', 'WikiAdmin', 'WikiAdmin', 'Y', 'page', '');",

    "CREATE TABLE `" . $tablePrefix . "referrers` (
      `page_tag` char(50) NOT NULL DEFAULT '',
      `referrer` char(150) NOT NULL DEFAULT '',
      `time` datetime NOT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;",

    "INSERT INTO `" . $tablePrefix . "referrers` (`page_tag`, `referrer`, `time`) VALUES
    ('PagePrincipale', 'http://florestan.cdrflorac.fr/', '" . $date . "');",

    "CREATE TABLE `" . $tablePrefix . "triples` (
      `id` int(10) UNSIGNED NOT NULL,
      `resource` varchar(255) NOT NULL DEFAULT '',
      `property` varchar(255) NOT NULL DEFAULT '',
      `value` text NOT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;",

    "INSERT INTO `" . $tablePrefix . "triples` (`id`, `resource`, `property`, `value`) VALUES
    (1, 'ThisWikiGroup:admins', 'http://www.wikini.net/_vocabulary/acls', 'WikiAdmin');",

    "CREATE TABLE `" . $tablePrefix . "users` (
      `name` varchar(80) NOT NULL DEFAULT '',
      `password` varchar(32) NOT NULL DEFAULT '',
      `email` varchar(50) NOT NULL DEFAULT '',
      `motto` text,
      `revisioncount` int(10) UNSIGNED NOT NULL DEFAULT '20',
      `changescount` int(10) UNSIGNED NOT NULL DEFAULT '50',
      `doubleclickedit` enum('Y','N') NOT NULL DEFAULT 'Y',
      `signuptime` datetime NOT NULL,
      `show_comments` enum('Y','N') NOT NULL DEFAULT 'N'
    ) ENGINE=MyISAM DEFAULT CHARSET=latin1;",

    "INSERT INTO `" . $tablePrefix . "users` (`name`, `password`, `email`, `motto`, `revisioncount`, `changescount`, `doubleclickedit`, `signuptime`, `show_comments`) VALUES
    ('WikiAdmin', '" . $WikiAdminPasswordMD5 . "', 'contact@cdrflorac.fr', '', 20, 50, 'Y', '" . $date . "', 'N');",

    "ALTER TABLE `" . $tablePrefix . "acls`
      ADD PRIMARY KEY (`page_tag`,`privilege`);",

    "ALTER TABLE `" . $tablePrefix . "links`
      ADD UNIQUE KEY `from_tag` (`from_tag`,`to_tag`),
      ADD KEY `idx_from` (`from_tag`),
      ADD KEY `idx_to` (`to_tag`);",

    "ALTER TABLE `" . $tablePrefix . "nature`
      ADD PRIMARY KEY (`bn_id_nature`);",

    "ALTER TABLE `" . $tablePrefix . "pages`
      ADD PRIMARY KEY (`id`),
      ADD KEY `idx_tag` (`tag`),
      ADD KEY `idx_time` (`time`),
      ADD KEY `idx_latest` (`latest`),
      ADD KEY `idx_comment_on` (`comment_on`);
    ALTER TABLE `" . $tablePrefix . "pages` ADD FULLTEXT KEY `tag` (`tag`,`body`);",

    "ALTER TABLE `" . $tablePrefix . "referrers`
      ADD KEY `idx_page_tag` (`page_tag`),
      ADD KEY `idx_time` (`time`);",

    "ALTER TABLE `" . $tablePrefix . "triples`
      ADD PRIMARY KEY (`id`),
      ADD KEY `resource` (`resource`),
      ADD KEY `property` (`property`);",

    "ALTER TABLE `" . $tablePrefix . "users`
      ADD PRIMARY KEY (`name`),
      ADD KEY `idx_name` (`name`),
      ADD KEY `idx_signuptime` (`signuptime`);",

    "ALTER TABLE `" . $tablePrefix . "pages`
      MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;",

    "ALTER TABLE `" . $tablePrefix . "triples`
      MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;",
);
