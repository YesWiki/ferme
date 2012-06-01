<?php
/*
attach.php
Code original de ce fichier : Eric FELDSTEIN
Copyright (c) 2002, Hendrik Mans <hendrik@mans.de>
Copyright 2002, 2003 David DELON
Copyright 2002, 2003 Charles NEPOTE
Copyright  2003,2004  Eric FELDSTEIN
Copyright  2003  Jean-Pascal MILCENT
All rights reserved.
Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:
1. Redistributions of source code must retain the above copyright
notice, this list of conditions and the following disclaimer.
2. Redistributions in binary form must reproduce the above copyright
notice, this list of conditions and the following disclaimer in the
documentation and/or other materials provided with the distribution.
3. The name of the author may not be used to endorse or promote products
derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/
/******************************************************************************
*			DOCUMENTATION
*******************************************************************************
	RESUME
L'action {{attach}} permet de lier un fichier � une page, d'uploader ce fichier
et de downloader ce fichier. Si le fichier est une image, elle est affich�e
dans la page. Lorsque le fichier est sur le serveur, il est possible de faire
une mise � jour de celui-ci.

	PARAMETRES DE L'ACTION
L'action {{attach}} prend les param�tres suivants :
 - file ou attachfile: nom du fichier tel qu'il sera sur le serveur. Les
   espaces sont remplac� par des "_". (OBLIGATOIRE)
 - desc ou attachdesc: description du fichier. C'est le texte qui sera affich�
   comme lien vers le fichier ou dans l'attribut alt de la balise <img>. Ce
   param�tre est obligatoire pour les images pour �tre conforme au XHTML.
 - delete ou attachdelete: Si ce param�tre est non vide alors le fichier sera
   effac� sur le serveur.
 - link ou attachlink: URL de lien pour une image sensible. le lien peut �tre
   un nom de page WikiNi, un lien interwiki ou une adresse http
 - class: indique le nom de la ou les classes de style � utiliser pour afficher
   l'image. les noms des classes sont s�par�s par un espace.

	EXEMPLES
 - Attacher un fichier archive:
 		{{attach file="archive.zip"}}
 - Attacher un fichier archive avec une description
 		{{attach file="archive.zip" desc="Code source de l'application"}}
 - Supprimer un fichier:
 		{{attach file="archive.zip" delete="y"}}
 - Afficher une image:
 		{{attach file="image.png" desc="voici une image"}}
 - Afficher une image sensible:
 		{{attach file="image.png" desc="voici une image" link="PagePrincipale"}}
 		{{attach file="image.png" desc="voici une image" link="WikiNi:PagePrincipale"}}
 		{{attach file="image.png" desc="voici une image" link="http://www.wikini.net"}}
 - Afficher une image coll� sur le bord droit et sans contour:
 		{{attach file="image.png" desc="voici une image" class="right noborder"}}

	INSTALLATION
1) Copiez le fichier attach.php dans le r�pertoire des actions (/actions)
2) Copiez le fichier attach.class.php dans le r�pertoire des actions (/actions)
3) Copiez le fichier attachfm.php dans le repertoire des actions (/actions)
4) Copiez le fichier filamanager.php dans le r�pertoire des handlers (/handlers/page)
5) Copiez le fichier upload.php dans le r�pertoire des handlers (/handlers/page)
6) Copiez le fichier download.php dans le r�pertoire des handlers (/handlers/page)
7) Cr�ez le r�pertoire racine des uploads sur le site du wiki. Si le SAFE_MODE
	de PHP est activ�, vous devez cr�er vous m�me ce r�pertoire et autoriser
	l'�criture dans ce r�pertoire pour l'utilisateur et le groupe.
8) Ouvrez le fichier wakka.config.php et ajoutez la configuration de l'action.
	Tous les param�tres de configuration ont une valeur par d�faut.
	Le configuration par d�faut est:

	$wakkaConfig["attach_config"] = array(
			"upload_path" => 'files',				//repertoire racine des uploads
			"ext_images" => 'gif|jpeg|png|jpg',	//extension des fichiers images
			"ext_script" => 'php|php3|asp|asx|vb|vbs|js',	//extension des script(non utilis�)
			"update_symbole" => '*',				//symbole pour faire un update du fichier
			"max_file_size" => 1024*2000,			//taille maximum du fichier en octer (2M par d�faut)
			"fmDelete_symbole" => 'Supr',			//symbole a afficher pour le lien "supprimer" dans le gestionnaire de fichier
			"fmRestore_symbole" => 'Rest',		//symbole a afficher pour le lien "restaurer" dans le gestionnaire de fichier
			"fmTrash_symbole" => 'Poubelle')		//symbole a afficher pour le lien "Poubelle" dans le gestionnaire de fichier

9) Ajoutez les classes de style au fichier wakka.css. Exemple de style :
.attach_margin05em { margin: 0.5em;}
.attach_margin1em { margin: 1em;}
.attach_left {float: left;}
.attach_right {float: right;}
.attach_noborder {border-width: 0px;}
.attach_vmiddle {vertical-align: text-bottom;}

10)Pour configurer l'aspect du gestionnnaire de fichier utiliser les classes de style .tableFM
tableFMCol1 et tableFMCol2
Exemple :
.tableFM {border: thin solid Black; width: 100%;  }
.tableFM THEAD { background-color: Silver; font-weight: bold; text-align: center;   }
.tableFM TFOOT { background-color: Silver; font-weight: bold; text-align: left;   }
.tableFM TBODY TR { text-align: center;  }
.tableFMCol1 { background-color: Aqua; }
.tableFMCol2 { background-color: Yellow; }
*******************************************************************************/

if (!defined("WIKINI_VERSION"))
{
        die ("acc&egrave;s direct interdit");
}

if (!class_exists('attach')){
	include('tools/attach/actions/attach.class.php');
}

$att = new attach($this);
$att->doAttach();
unset($att);
?>
