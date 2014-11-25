<?php
/*vim: set expandtab tabstop=4 shiftwidth=4: */
// +------------------------------------------------------------------------------------------------------+
// | PHP version 4.1                                                                                      |
// +------------------------------------------------------------------------------------------------------+
// | Copyright (C) 2004 Tela Botanica (accueil@tela-botanica.org)                                         |
// +------------------------------------------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or                                        |
// | modify it under the terms of the GNU Lesser General Public                                           |
// | License as published by the Free Software Foundation; either                                         |
// | version 2.1 of the License, or (at your option) any later version.                                   |
// |                                                                                                      |
// | This library is distributed in the hope that it will be useful,                                      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of                                       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU                                    |
// | Lesser General Public License for more details.                                                      |
// |                                                                                                      |
// | You should have received a copy of the GNU Lesser General Public                                     |
// | License along with this library; if not, write to the Free Software                                  |
// | Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                            |
// +------------------------------------------------------------------------------------------------------+
// CVS : $Id: formulaire.fonct.inc.php,v 1.25 2010-12-15 10:45:43 mrflos Exp $
/**
 * Formulaire
 *
 * Les fonctions de mise en page des formulaire
 *
 * @package bazar
 *  Auteur original :
 * @author        Florian SCHMITT <florian@outils-reseaux.org>
 *  Autres auteurs :
 * @author        Aleandre GRANIER <alexandre@tela-botanica.org>
 * @copyright     Tela-Botanica 2000-2004
 * @version       $Revision: 1.25 $ $Date: 2010-12-15 10:45:43 $
 * +------------------------------------------------------------------------------------------------------+
 */

//comptatibilite avec PHP4...
if (version_compare(phpversion(), '5.0') < 0) {
    eval('
            function clone($object)
            {
            return $object;
            }
            ');
}

/** afficher_image() - genere une image en cache (gestion taille et vignettes) et l'affiche comme il faut
 *
 * @param    string	nom du fichier image
 * @param	string	label pour l'image
 * @param    string	classes html supplementaires
 * @param    int		largeur en pixel de la vignette
 * @param    int		hauteur en pixel de la vignette
 * @param    int		largeur en pixel de l'image redimensionnee
 * @param    int		hauteur en pixel de l'image redimensionnee
 * @return   void
 */
function afficher_image($nom_image, $label, $class, $largeur_vignette, $hauteur_vignette, $largeur_image, $hauteur_image)
{
    //faut il creer la vignette?
    if ($hauteur_vignette!='' && $largeur_vignette!='') {
        //la vignette n'existe pas, on la genere
        if (!file_exists('cache/vignette_'.$nom_image) || (isset($_GET['regenerate']) && $_GET['regenerate'] == 1)) {
            $adr_img = redimensionner_image(BAZ_CHEMIN_UPLOAD.$nom_image, 'cache/vignette_'.$nom_image, $largeur_vignette, $hauteur_vignette);
        }
        list($width, $height, $type, $attr) = getimagesize('cache/vignette_'.$nom_image);
        //faut il redimensionner l'image?
        if ($hauteur_image!='' && $largeur_image!='') {
            //l'image redimensionnee n'existe pas, on la genere
            if (!file_exists('cache/image_'.$nom_image) || (isset($_GET['regenerate']) && $_GET['regenerate'] == 1)) {
                $adr_img = redimensionner_image(BAZ_CHEMIN_UPLOAD.$nom_image, 'cache/image_'.$nom_image, $largeur_image, $hauteur_image);
            }
            //on renvoit l'image en vignette, avec quand on clique, l'image redimensionnee
            $url_base = str_replace('wakka.php?wiki=','',$GLOBALS['wiki']->config['base_url']);

            return 	'<a class="triggerimage '.$class.'" href="'.$url_base.'cache/image_'.$nom_image.'">'."\n".
                    '<img src="'.$url_base.'cache/vignette_'.$nom_image.'" alt="'.$nom_image.'"'.' />'."\n".'</a>'."\n";

        } else {
            //on renvoit l'image en vignette, avec quand on clique, l'image originale
            return  '<a class="triggerimage '.$class.'" rel="#overlay-link" href="'.$url_base.BAZ_CHEMIN_UPLOAD.$nom_image.'">'."\n".
                    '<img class="img-responsive" src="'.$url_base.'cache/vignette_'.$nom_image.'" alt="'.$nom_image.'"'.' rel="'.$url_base.'cache/image_'.$nom_image.'" />'."\n".
                    '</a>'."\n";
        }
    }
    //pas de vignette, mais faut il redimensionner l'image?
    else if ($hauteur_image!='' && $largeur_image!='') {
        //l'image redimensionnee n'existe pas, on la genere
        if (!file_exists('cache/image_'.$nom_image) || (isset($_GET['regenerate']) && $_GET['regenerate'] == 1)) {
            $adr_img = redimensionner_image(BAZ_CHEMIN_UPLOAD.$nom_image, 'cache/image_'.$nom_image, $largeur_image, $hauteur_image);
        }
        //on renvoit l'image redimensionnee
        list($width, $height, $type, $attr) = getimagesize('cache/image_'.$nom_image);

        return  '<img src="cache/image_'.$nom_image.'" class="img-responsive '.$class.'" alt="'.$nom_image.'"'.' />'."\n";

    }
    //on affiche l'image originale sinon
    else {
        list($width, $height, $type, $attr) = getimagesize(BAZ_CHEMIN_UPLOAD.$nom_image);

        return  '<img src="'.BAZ_CHEMIN_UPLOAD.$nom_image.'" class="img-responsive '.$class.'" alt="'.$nom_image.'"'.' />'."\n";
    }
}

function redimensionner_image($image_src, $image_dest, $largeur, $hauteur)
{
    if (file_exists($image_dest)) unlink($image_dest);
    require_once 'tools'.DIRECTORY_SEPARATOR.'bazar'.DIRECTORY_SEPARATOR.'libs'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'class.imagetransform.php';
    $imgTrans = new imageTransform();
    $imgTrans->sourceFile = $image_src;
    $imgTrans->targetFile = $image_dest;
    $imgTrans->resizeToWidth = $largeur;
    $imgTrans->resizeToHeight = $hauteur;
    if (!$imgTrans->resize()) {
        // in case of error, show error code
        return $imgTrans->error;
        // if there were no errors
    } else {
        return $imgTrans->targetFile;
    }
}

//-------------------FONCTIONS DE TRAITEMENT DU TEMPLATE DU FORMULAIRE

/** formulaire_valeurs_template_champs() - Decoupe le template et renvoie un tableau structure
 *
 * @param    string  Template du formulaire
 * @param    mixed   Le tableau des valeurs des differentes option pour l'element liste
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par defaut
 * @return   void
 */
function formulaire_valeurs_template_champs($template)
{
    //Parcours du template, pour mettre les champs du formulaire avec leurs valeurs specifiques
    $tableau_template= array();
    $nblignes=0;
    //on traite le template ligne par ligne
    $chaine = explode ("\n", $template);
    foreach ($chaine as $ligne) {
        if ($ligne!='') {
            //on decoupe chaque ligne par le separateur *** (c'est historique)
            $tablignechampsformulaire = array_map("trim", explode ("***", $ligne));
            if (count($tablignechampsformulaire) > 3) {
                $tableau_template[$nblignes] = $tablignechampsformulaire;
                if (!isset($tableau_template[$nblignes][9])) $tableau_template[$nblignes][9] = '';
                if (!isset($tableau_template[$nblignes][10])) $tableau_template[$nblignes][10] = '';
                $nblignes++;
            }
        }
    }

    return $tableau_template;
}



//-------------------FONCTIONS DE MISE EN PAGE DES FORMULAIRES

/** radio() - Ajoute un element de type liste deroulante au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des differentes option pour l'element liste
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par daefaut
 * @return   void
 */
function radio(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    if ($mode == 'saisie') {
        $bulledaide = '';
        if (isset($tableau_template[10]) && $tableau_template[10]!='') {
            $bulledaide .= ' &nbsp;&nbsp;<img class="tooltip_aide" title="'.htmlentities($tableau_template[10], ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'" src="tools/bazar/presentation/images/aide.png" width="16" height="16" alt="image aide" />';
        }
        $ob = ''; $optionrequired = '';
        if (isset($tableau_template[8]) && $tableau_template[8]==1) {
            $ob .= '<span class="symbole_obligatoire">*&nbsp;</span>'."\n";
            $optionrequired .= ' radio_required';
        }
        if (isset($valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]) && $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]!='') {
            $def =	$valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]];
        } else {
            $def = $tableau_template[5];
        }


        $radio_html = '<fieldset class="bazar_fieldset'.$optionrequired.'"><legend>'.$ob.$tableau_template[2].$bulledaide.'</legend>';

        $valliste = baz_valeurs_liste($tableau_template[1]);
        if (is_array($valliste['label'])) {
            foreach ($valliste['label'] as $key => $label) {
                $radio_html .= '<div class="bazar_radio">';
                $radio_html .= '<input type="radio" id="'.$tableau_template[0].$tableau_template[1].$tableau_template[6].$key.'" value="'.$key.'" name="'.$tableau_template[0].$tableau_template[1].$tableau_template[6].'" class="element_radio"';
                if ($def != '' && strstr($key, $def)) {
                    $radio_html .= ' checked';
                }
                $radio_html .= ' /><label for="'.$tableau_template[0].$tableau_template[1].$tableau_template[6].$key.'">'.$label.'</label>';
                $radio_html .= '</div>';


            }
        }
        $radio_html .= '</fieldset>';

        $formtemplate->addElement('html', $radio_html) ;


    } elseif ($mode == 'requete') {
    } elseif ($mode == 'formulaire_recherche') {
    } elseif ($mode == 'html') {
        $html = '';
        if (isset($valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]) && $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]!='') {
            $valliste = baz_valeurs_liste($tableau_template[1]);

            $tabresult = explode(',', $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]);
            if (is_array($tabresult)) {
                $labels_result = '';
                foreach ($tabresult as $id)
                    if (isset($valliste["label"][$id])) {
                        if ($labels_result == '') $labels_result = $valliste["label"][$id];
                        else $labels_result .= ', '.$valliste["label"][$id];
                    }
            }

            {
                $html = '<div class="BAZ_rubrique" data-id="'.$tableau_template[0].$tableau_template[1].$tableau_template[6].'">'."\n".
                    '<span class="BAZ_label">'.$tableau_template[2].'&nbsp;:</span>'."\n".
                    '<span class="BAZ_texte">'."\n".
                    $labels_result."\n".
                    '</span>'."\n".
                    '</div> <!-- /.BAZ_rubrique -->'."\n";
            }
        }

        return $html;
    }
}




/** liste() - Ajoute un élément de type liste déroulante au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour l'élément liste
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @return   void
 */
function liste(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    if ($mode=='saisie') {
        $valliste = baz_valeurs_liste($tableau_template[1]);
        if ($valliste) {
            $bulledaide = '';
            if (isset($tableau_template[10]) && $tableau_template[10]!='') {
                $bulledaide = ' &nbsp;&nbsp;<img class="tooltip_aide" title="'.htmlentities($tableau_template[10], ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'" src="tools/bazar/presentation/images/aide.png" width="16" height="16" alt="image aide" />';
            }

            $select_html = '<div class="control-group form-group">'."\n".'<div class="control-label col-xs-3">'."\n";
            if (isset($tableau_template[8]) && $tableau_template[8]==1) {
                $select_html .= '<span class="symbole_obligatoire">*&nbsp;</span>'."\n";
            }
            $select_html .= $tableau_template[2].$bulledaide.' : </div>'."\n".'<div class="controls col-xs-8">'."\n".'<select';

            $select_attributes = '';

            if ($tableau_template[4] != '' && $tableau_template[4] > 1) {
                $select_attributes .= ' multiple="multiple" size="'.$tableau_template[4].'"';
                $selectnametab = '[]';
            } else {
                $selectnametab = '';
            }

            $select_attributes .= ' class="form-control" id="'.$tableau_template[0].$tableau_template[1].$tableau_template[6].'" name="'.$tableau_template[0].$tableau_template[1].$tableau_template[6].$selectnametab.'"';

            if (isset($tableau_template[8]) && $tableau_template[8]==1) {
                $select_attributes .= ' required="required"';
            }
            $select_html .= $select_attributes.'>'."\n";

            if (isset($valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]) && $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]!='') {
                $def =	$valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]];
            } else {
                $def = $tableau_template[5];
            }

            
            if ($def=='' && ($tableau_template[4] == '' || $tableau_template[4] <= 1 ) || $def==0) {
                $select_html .= '<option value="" selected="selected">'._t('BAZ_CHOISIR').'</option>'."\n";
            }
            if (is_array($valliste['label'])) {
                foreach ($valliste['label'] as $key => $label) {
                    $select_html .= '<option value="'.$key.'"';
                    if ($def != '' && $key==$def) $select_html .= ' selected="selected"';
                    $select_html .= '>'.$label.'</option>'."\n";
                }

            }

            $select_html .= "</select>\n</div>\n</div>\n";

            $formtemplate->addElement('html', $select_html) ;
        }

    } elseif ($mode == 'requete') {

    } elseif ($mode == 'formulaire_recherche') {
        //on affiche la liste sous forme de liste deroulante
        if ($tableau_template[9]==1) {
            $valliste = baz_valeurs_liste($tableau_template[1]);
            $select[0] = _t('BAZ_INDIFFERENT');
            if (is_array($valliste['label'])) {
                $select = $select + $valliste['label'];
            }

            $option = array('id' => $tableau_template[0].$tableau_template[1].$tableau_template[6], 'class' => 'form-control');
            require_once BAZ_CHEMIN.'libs'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'HTML/QuickForm/select.php';
            $select= new HTML_QuickForm_select($tableau_template[0].$tableau_template[1].$tableau_template[6], $tableau_template[2], $select, $option);
            if ($tableau_template[4] != '') $select->setSize($tableau_template[4]);
            $select->setMultiple(0);
            $nb = (isset($_REQUEST[$tableau_template[0].$tableau_template[1].$tableau_template[6]])? $_REQUEST[$tableau_template[0].$tableau_template[1].$tableau_template[6]] : 0);
            $select->setValue($nb);
            $formtemplate->addElement($select) ;
        }
        //on affiche la liste sous forme de checkbox
        if ($tableau_template[9]==2) {
            $valliste = baz_valeurs_liste($tableau_template[1]);
            require_once BAZ_CHEMIN.'libs'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'HTML/QuickForm/checkbox.php';
            $i=0;
            $optioncheckbox = array('class' => 'checkbox');

            foreach ($valliste['label'] as $id => $label) {
                if ($i==0) $tab_chkbox = $tableau_template[2] ; else $tab_chkbox='&nbsp;';
                $checkbox[$i]= & HTML_QuickForm::createElement('checkbox', $id, $tab_chkbox, $label, $optioncheckbox) ;
                $i++;
            }

            $squelette_checkbox =& $formtemplate->defaultRenderer();
            $squelette_checkbox->setElementTemplate( '<fieldset class="bazar_fieldset">'."\n".'<legend>{label}'.
                                                    '<!-- BEGIN required --><span class="symbole_obligatoire">&nbsp;*</span><!-- END required -->'."\n".
                                                    '</legend>'."\n".'{element}'."\n".'</fieldset> '."\n"."\n", $tableau_template[0].$tableau_template[1].$tableau_template[6]);
            $squelette_checkbox->setGroupElementTemplate( "\n".'<div class="checkbox">'."\n".'{element}'."\n".'</div>'."\n", $tableau_template[0].$tableau_template[1].$tableau_template[6]);

            $formtemplate->addGroup($checkbox, $tableau_template[0].$tableau_template[1].$tableau_template[6], $tableau_template[2], "\n");
        }
    } elseif ($mode == 'requete_recherche') {
        if ($tableau_template[9]==1 && isset($_REQUEST[$tableau_template[0].$tableau_template[1].$tableau_template[6]]) && $_REQUEST[$tableau_template[0].$tableau_template[1].$tableau_template[6]] != 0) {
            /*return ' AND bf_id_fiche IN (SELECT bfvt_ce_fiche FROM '.BAZ_PREFIXE.'fiche_valeur_texte WHERE bfvt_id_element_form="'.$tableau_template[0].$tableau_template[1].$tableau_template[6].'" AND bfvt_texte="'.$_REQUEST[$tableau_template[0].$tableau_template[1].$tableau_template[6]].'") ';*/
        }
    } elseif ($mode == 'html') {
        $html = '';
        if (isset($valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]) && $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]!='') {
            $valliste = baz_valeurs_liste($tableau_template[1]);

            if (isset($valliste["label"][$valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]])) {
                $html = '<div class="BAZ_rubrique" data-id="'.$tableau_template[0].$tableau_template[1].$tableau_template[6].'">'."\n".
                        '<span class="BAZ_label">'.$tableau_template[2].'&nbsp;:</span>'."\n".
                        '<span class="BAZ_texte">'."\n".
                        $valliste["label"][$valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]]."\n".
                        '</span>'."\n".
                        '</div> <!-- /.BAZ_rubrique -->'."\n";
            }
        }

        return $html;
    }
}




/** checkbox() - Ajoute un element de type case a cocher au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des differentes option pour l'element case a cocher
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par defaut
 * @return   void
 */
function checkbox(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    if ($mode == 'saisie') {
        $bulledaide = '';
        if (isset($tableau_template[10]) && $tableau_template[10]!='') $bulledaide = ' &nbsp;&nbsp;<img class="tooltip_aide" title="'.htmlentities($tableau_template[10], ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'" src="tools/bazar/presentation/images/aide.png" width="16" height="16" alt="image aide" />';
        $valliste = baz_valeurs_liste($tableau_template[1]);
        
        if ($valliste) {

            $choixcheckbox = $valliste['label'];

            require_once BAZ_CHEMIN.'libs'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'HTML/QuickForm/checkbox.php';
            $i=0;
            $optioncheckbox = array('class' => 'element_checkbox');

            //valeurs par defauts
            if (isset($valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]])) {
                $tab = explode( ',', $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]] );
            } else {
                $tab = explode( ',', $tableau_template[5] );
            }

            foreach ($choixcheckbox as $id => $label) {
                if ($i==0) {
                    $tab_chkbox = $tableau_template[2] ;
                } else {
                    $tab_chkbox='&nbsp;';
                }

                //teste si la valeur de la liste doit etre cochee par defaut
                if (in_array($id,$tab)) {
                    $defaultValues[$tableau_template[0].$tableau_template[1].$tableau_template[6].'['.$id.']'] = true;
                    //echo 'a cocher '.$tableau_template[0].$tableau_template[1].$tableau_template[6].'['.$id.']'.'<br />';
                } else {
                    $defaultValues[$tableau_template[0].$tableau_template[1].$tableau_template[6].'['.$id.']'] = false;
                }

                $checkbox[$i] = $formtemplate->createElement($tableau_template[0], $id, $tab_chkbox, $label, $optioncheckbox);
                $i++;
            }

            $squelette_checkbox = & $formtemplate->defaultRenderer();
            $classrequired=''; $req = '';
            if (isset($tableau_template[8]) && $tableau_template[8]==1) {
                $classrequired .= ' chk_required';
                $req = '<span class="symbole_obligatoire">&nbsp;*</span> ';
            }
            //$squelette_checkbox->setElementTemplate( '<fieldset class="bazar_fieldset'.$classrequired.'">'."\n".'<legend>'."\n".'{label}'."\n".
            //        '</legend>'."\n".'{element}'."\n".'</fieldset> '."\n"."\n", $tableau_template[0].$tableau_template[1].$tableau_template[6]);
            $squelette_checkbox->setGroupElementTemplate( "\n".'<div class="checkbox">'."\n".'{element}'."\n".'</div>'."\n", $tableau_template[0].$tableau_template[1].$tableau_template[6]);
            $formtemplate->addGroup($checkbox, $tableau_template[0].$tableau_template[1].$tableau_template[6], $req.$tableau_template[2].$bulledaide, "\n");

            $formtemplate->setDefaults($defaultValues);
        }
    } elseif ($mode == 'requete') {
        return array($tableau_template[0].$tableau_template[1].$tableau_template[6] => $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]);
    } elseif ($mode == 'formulaire_recherche') {
        if ($tableau_template[9]==1) {
            $valliste = baz_valeurs_liste($tableau_template[1]);
            require_once BAZ_CHEMIN.'libs'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'HTML/QuickForm/checkbox.php';
            $i=0;
            $optioncheckbox = array('class' => 'element_checkbox');

            foreach ($valliste['label'] as $id => $label) {
                if ($i==0) $tab_chkbox = $tableau_template[2] ; else $tab_chkbox='&nbsp;';

                if (isset($_REQUEST[$tableau_template[0].$tableau_template[1].$tableau_template[6]]) && array_key_exists($id, $_REQUEST[$tableau_template[0].$tableau_template[1].$tableau_template[6]])) {
                    $optioncheckbox['checked']='checked';
                } else {
                    unset($optioncheckbox['checked']);
                }

                $checkbox[$i]= & HTML_QuickForm::createElement('checkbox', $id, $tab_chkbox, $label, $optioncheckbox) ;

                $i++;
            }

            $squelette_checkbox =& $formtemplate->defaultRenderer();
            $squelette_checkbox->setElementTemplate( '<fieldset class="bazar_fieldset">'."\n".'<legend>{label}'.
                    '<!-- BEGIN required --><span class="symbole_obligatoire">&nbsp;*</span><!-- END required -->'."\n".
                    '</legend>'."\n".'{element}'."\n".'</fieldset> '."\n"."\n", $tableau_template[0].$tableau_template[1].$tableau_template[6]);
            $squelette_checkbox->setGroupElementTemplate( "\n".'<div class="checkbox">'."\n".'{element}'."\n".'</div>'."\n", $tableau_template[0].$tableau_template[1].$tableau_template[6]);

            $formtemplate->addGroup($checkbox, $tableau_template[0].$tableau_template[1].$tableau_template[6], $tableau_template[2], "\n");
        }
    } elseif ($mode == 'html') {
        $html = '';
        if (isset($valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]) && $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]!='') {
            $valliste = baz_valeurs_liste($tableau_template[1]);

            $tabresult = explode(',', $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]);
            if (is_array($tabresult)) {
                $labels_result = '';
                foreach ($tabresult as $id)
                    if (isset($valliste["label"][$id])) {
                        if ($labels_result == '') $labels_result = $valliste["label"][$id];
                        else $labels_result .= ', '.$valliste["label"][$id];
                    }
            }

            {
                $html = '<div class="BAZ_rubrique" data-id="'.$tableau_template[0].$tableau_template[1].$tableau_template[6].'">'."\n".
                    '<span class="BAZ_label">'.$tableau_template[2].'&nbsp;:</span>'."\n".
                    '<span class="BAZ_texte">'."\n".
                    $labels_result."\n".
                    '</span>'."\n".
                    '</div> <!-- /.BAZ_rubrique -->'."\n";
            }
        }

        return $html;
    }
}

/** jour() - Ajoute un élément de type date au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour l'élément date
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @return   void
 */
function jour(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    if ($mode == 'saisie') {
        $bulledaide = '';
        if (isset($tableau_template[10]) && $tableau_template[10]!='') {
            $bulledaide = ' &nbsp;&nbsp;<img class="tooltip_aide" title="'.htmlentities($tableau_template[10], ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'" src="tools/bazar/presentation/images/aide.png" width="16" height="16" alt="image aide" />';
        }

        $date_html = '<div class="control-group form-group">'."\n".'<div class="control-label col-xs-3">'."\n";
        if (isset($tableau_template[8]) && $tableau_template[8]==1) {
            $date_html .= '<span class="symbole_obligatoire">*&nbsp;</span>'."\n";
        }
        $date_html .= $tableau_template[2].$bulledaide.' : </div>'."\n".'<div class="controls col-xs-8">'."\n".'<div class="input-prepend input-group">
<span class="add-on input-group-addon"><i class="icon-calendar glyphicon glyphicon-calendar"></i></span><input type="date" name="'.$tableau_template[1].'" ';

        $date_html .= ' class="form-control bazar-date" id="'.$tableau_template[1].'"';

        if (isset($tableau_template[8]) && $tableau_template[8]==1) {
            $date_html .= ' required="required"';
        }

        $hashour = false; $tabtime = array(0 => '00', 1 => '00');
        //gestion des valeurs par defaut pour modification
        if (isset($valeurs_fiche[$tableau_template[1]])) {
            $date_html .= ' value="'.date("Y-m-d", strtotime($valeurs_fiche[$tableau_template[1]])).'" />';
            $hashour = (strlen($valeurs_fiche[$tableau_template[1]])>10);
            if ($hashour) {
                $tab = explode('T', $valeurs_fiche[$tableau_template[1]]);
                $tabtime = explode(':', $tab[1]);

            }
        } else {
            //gestion des valeurs par defaut (date du jour)
            if (isset($tableau_template[5]) && $tableau_template[5]!='') {
                // si la valeur par defaut est today, on ajoute la date du jour
                if ($tableau_template[5]='today') {
                    $date_html .= ' value="'.date("Y-m-d").'" />';
                }
                else $date_html .= ' value="'.date("Y-m-d", strtotime($tableau_template[5])).'" />';
            } else {
                $date_html .= ' value="" />';
            }
        }

        $date_html .= '<select class="form-control select-allday" name="'.$tableau_template[1].'_allday">
        <option value="1"'.(($hashour) ? '':' selected').'>'._t('BAZ_ALL_DAY').'</option>
        <option value="0"'.(($hashour) ? ' selected':'').'>'._t('BAZ_ENTER_HOUR').'</option>
        </select></div> 
        <div class="select-time'.(($hashour) ? '':' hide').' input-prepend input-group">
        <span class="add-on input-group-addon">
        <i class="icon-time glyphicon glyphicon-time"></i></span>
        <select class="form-control select-hour" name="'.$tableau_template[1].'_hour">
        <option value="00"'.(($hashour) ? '':' selected').(($tabtime[0]== '00') ? 'selected':'').'>00</option>
        <option value="01"'.(($tabtime[0]== '01') ? 'selected':'').'>01</option>
        <option value="02"'.(($tabtime[0]== '02') ? 'selected':'').'>02</option>
        <option value="03"'.(($tabtime[0]== '03') ? 'selected':'').'>03</option>
        <option value="04"'.(($tabtime[0]== '04') ? 'selected':'').'>04</option>
        <option value="05"'.(($tabtime[0]== '05') ? 'selected':'').'>05</option>
        <option value="06"'.(($tabtime[0]== '06') ? 'selected':'').'>06</option>
        <option value="07"'.(($tabtime[0]== '07') ? 'selected':'').'>07</option>
        <option value="08"'.(($tabtime[0]== '08') ? 'selected':'').'>08</option>
        <option value="09"'.(($tabtime[0]== '09') ? 'selected':'').'>09</option>
        <option value="10"'.(($tabtime[0]== '10') ? 'selected':'').'>10</option>
        <option value="11"'.(($tabtime[0]== '11') ? 'selected':'').'>11</option>
        <option value="12"'.(($tabtime[0]== '12') ? 'selected':'').'>12</option>
        <option value="13"'.(($tabtime[0]== '13') ? 'selected':'').'>13</option>
        <option value="14"'.(($tabtime[0]== '14') ? 'selected':'').'>14</option>
        <option value="15"'.(($tabtime[0]== '15') ? 'selected':'').'>15</option>
        <option value="16"'.(($tabtime[0]== '16') ? 'selected':'').'>16</option>
        <option value="17"'.(($tabtime[0]== '17') ? 'selected':'').'>17</option>
        <option value="18"'.(($tabtime[0]== '18') ? 'selected':'').'>18</option>
        <option value="19"'.(($tabtime[0]== '19') ? 'selected':'').'>19</option>
        <option value="20"'.(($tabtime[0]== '20') ? 'selected':'').'>20</option>
        <option value="21"'.(($tabtime[0]== '21') ? 'selected':'').'>21</option>
        <option value="22"'.(($tabtime[0]== '22') ? 'selected':'').'>22</option>
        <option value="23"'.(($tabtime[0]== '23') ? 'selected':'').'>23</option>
        </select>
        <select class="form-control select-minutes" name="'.$tableau_template[1].'_minutes">
        <option value="00"'.(($hashour) ? ' ':' selected').(($tabtime[1]== '00') ? 'selected':'').'>00</option>
        <option value="05"'.(($tabtime[1]== '05') ? 'selected':'').'>05</option>
        <option value="10"'.(($tabtime[1]== '10') ? 'selected':'').'>10</option>
        <option value="15"'.(($tabtime[1]== '15') ? 'selected':'').'>15</option>
        <option value="20"'.(($tabtime[1]== '20') ? 'selected':'').'>20</option>
        <option value="25"'.(($tabtime[1]== '25') ? 'selected':'').'>25</option>
        <option value="30"'.(($tabtime[1]== '30') ? 'selected':'').'>30</option>
        <option value="35"'.(($tabtime[1]== '35') ? 'selected':'').'>35</option>
        <option value="40"'.(($tabtime[1]== '40') ? 'selected':'').'>40</option>
        <option value="45"'.(($tabtime[1]== '45') ? 'selected':'').'>45</option>
        <option value="50"'.(($tabtime[1]== '50') ? 'selected':'').'>50</option>
        <option value="55"'.(($tabtime[1]== '55') ? 'selected':'').'>55</option>
        </select>
        </div>
        </div>'."\n".'</div>'."\n";

        $formtemplate->addElement('html', $date_html) ;

    } elseif ($mode == 'requete') {
        if (isset($_POST[$tableau_template[1].'_allday']) && $_POST[$tableau_template[1].'_allday'] == 0) {
            if (isset($_POST[$tableau_template[1].'_hour']) && isset($_POST[$tableau_template[1].'_minutes'])) {
                 return array($tableau_template[1] => date("c", strtotime($_POST[$tableau_template[1]].' '.$_POST[$tableau_template[1].'_hour'].':'.$_POST[$tableau_template[1].'_minutes'])));
            }
            else {
                return array($tableau_template[1] => $_POST[$tableau_template[1]]);
            }
        } 
        else {
            return array($tableau_template[1] => $_POST[$tableau_template[1]]);
        }
    } elseif ($mode == 'recherche') {

    } elseif ($mode == 'html') {
        if ($valeurs_fiche[$tableau_template[1]]!="") {
            $res = '<div class="BAZ_rubrique" data-id="'.$tableau_template[1].'">'."\n".
            '<span class="BAZ_label">'.$tableau_template[2].'&nbsp;:</span>'."\n";
            if (strlen($valeurs_fiche[$tableau_template[1]])>10) {
                $res .= '<span class="BAZ_texte">'.strftime('%d.%m.%Y - %H:%M',strtotime($valeurs_fiche[$tableau_template[1]])).'</span>'."\n".'</div> <!-- /.BAZ_rubrique -->'."\n";
            }
            else {
                $res .= '<span class="BAZ_texte">'.strftime('%d.%m.%Y',strtotime($valeurs_fiche[$tableau_template[1]])).'</span>'."\n".'</div> <!-- /.BAZ_rubrique -->'."\n";
            }
        }

        return $res;
    }
}

/** listedatedeb() - voir date()
 *
 */
function listedatedeb(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    return jour($formtemplate, $tableau_template , $mode, $valeurs_fiche);
}

/** listedatefin() - voir date()
 *
 */
function listedatefin(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    return jour($formtemplate, $tableau_template , $mode, $valeurs_fiche);
}

/** tags() - Ajoute un élément de type mot clés (tags)
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour l'élément texte
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @param    mixed   valeur par défaut du champs
 * @return   void
 */
function tags(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    if ($mode == 'saisie') {
        $tags_javascript = '';
        //gestion des mots cles deja entres
        if (isset($valeurs_fiche[$tableau_template[1]])) {
            $tags = explode(",", mysql_real_escape_string($valeurs_fiche[$tableau_template[1]]));
            if (is_array($tags)) {
                sort($tags);
                foreach ($tags as $tag) {
                    $tags_javascript .= 't.add(\''.$tag.'\');'."\n";
                }
            }
        }

        // on recupere tous les tags du site
        $response = array();
        $tab_tous_les_tags = $GLOBALS['wiki']->GetAllTags();
        if (is_array($tab_tous_les_tags)) {
            foreach ($tab_tous_les_tags as $tab_les_tags) {
                $response[] = _convert($tab_les_tags['value'], 'ISO-8859-15');
            }
        }
        sort($response);
        $tagsexistants = '\''.implode('\',\'', $response).'\'';

        $script = '$(function(){
    var tagsexistants = ['.$tagsexistants.'];
    var pagetag = $(\'#formulaire .yeswiki-input-pagetag\');
    pagetag.tagsinput({
        typeahead: {
            source: tagsexistants
        },
        confirmKeys: [13, 188]
    });
    
    //bidouille antispam
    $(".antispam").attr(\'value\', \'1\');

    $("#formulaire").on(\'submit\', function() {
        pagetag.tagsinput(\'add\', pagetag.tagsinput(\'input\').val());
    });
});'."\n";
  $GLOBALS['wiki']->AddJavascriptFile('tools/tags/libs/vendor/bootstrap-tagsinput.min.js');
  $GLOBALS['wiki']->AddJavascript($script);

//gestion des valeurs par defaut : d'abord on regarde s'il y a une valeur a modifier,
//puis s'il y a une variable passee en GET,
//enfin on prend la valeur par defaut du formulaire sinon
if (isset($valeurs_fiche[$tableau_template[1]])) {
    $defauts = $valeurs_fiche[$tableau_template[1]];
} elseif (isset($_GET[$tableau_template[1]])) {
    $defauts = stripslashes($_GET[$tableau_template[1]]);
} else {
    $defauts = stripslashes($tableau_template[5]);
}

$option=array('size'=>$tableau_template[3],'maxlength'=>$tableau_template[4], 'id' => $tableau_template[1], 'value' => $defauts, 'class' => 'form-control yeswiki-input-pagetag');
$bulledaide = '';
if (isset($tableau_template[10]) && $tableau_template[10]!='') $bulledaide = ' &nbsp;&nbsp;<img class="tooltip_aide" title="'.htmlentities($tableau_template[10], ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'" src="tools/bazar/presentation/images/aide.png" width="16" height="16" alt="image aide" />';
$formtemplate->addElement('text', $tableau_template[1], $tableau_template[2].$bulledaide, $option) ;

} elseif ($mode == 'requete') {
    //on supprime les tags existants
    if (!isset($GLOBALS['delete_tags'])) {
        $GLOBALS['wiki']->DeleteTriple($GLOBALS['_BAZAR_']['id_fiche'], 'http://outils-reseaux.org/_vocabulary/tag', NULL, '', '');
        $GLOBALS['delete_tags'] = true;    
    }
    //on decoupe les tags pour les mettre dans un tableau
    $tags = explode(",", mysql_real_escape_string($valeurs_fiche[$tableau_template[1]]));

    //on ajoute les tags postés
    foreach ($tags as $tag) {
        trim($tag);
        if ($tag!='') {
            $GLOBALS['wiki']->InsertTriple($GLOBALS['_BAZAR_']['id_fiche'], 'http://outils-reseaux.org/_vocabulary/tag', _convert($tag, TEMPLATES_DEFAULT_CHARSET, true), '', '');
        }
    }
    //on copie tout de meme dans les metadonnees
    //return formulaire_insertion_texte($tableau_template[1], $valeurs_fiche[$tableau_template[1]]);
    return array($tableau_template[1] => $valeurs_fiche[$tableau_template[1]]);
} elseif ($mode == 'html') {
    $html = '';
    if (isset($valeurs_fiche[$tableau_template[1]]) && $valeurs_fiche[$tableau_template[1]]!='') {
        $html = '<div class="BAZ_rubrique tags_'.$tableau_template[1].'" data-id="'.$tableau_template[1].'">'."\n".
            '<span class="BAZ_label">'.$tableau_template[2].'&nbsp;:</span>'."\n";
        $html .= '<div class="BAZ_texte"> ';
        $tabtagsexistants = explode(',', $valeurs_fiche[$tableau_template[1]]);

        if (count($tabtagsexistants)>0) {
            sort($tabtagsexistants);
            $tagsexistants = '';
            foreach ($tabtagsexistants as $tag) {
                $tagsexistants .= '<a class="tag-label label label-info" href="'.$GLOBALS['wiki']->href('listpages',$GLOBALS['wiki']->GetPageTag(),'tags='.urlencode(trim($tag))).'" title="'._t('TAGS_SEE_ALL_PAGES_WITH_THIS_TAGS').'">'.$tag.'</a>
                    </li>'."\n";
            }
            $html .= $tagsexistants."\n";
        }

        $html .= '</div>'."\n".'</div> <!-- /.BAZ_rubrique -->'."\n";
    }

    return $html;
}
}




/** texte() - Ajoute un element de type texte au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour l'élément texte
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @return   void
 */
function texte(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    list($type, $identifiant, $label, $nb_min_car, $nb_max_car, $valeur_par_defaut, $regexp, $type_input , $obligatoire, , $bulle_d_aide) = $tableau_template;
    if ($mode == 'saisie') {
        // on prepare le html de la bulle d'aide, si elle existe
        if ($bulle_d_aide != '') {
            $bulledaide = '&nbsp;&nbsp;<img class="tooltip_aide" title="'.htmlentities($bulle_d_aide, ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'" src="tools/bazar/presentation/images/aide.png" width="16" height="16" alt="image aide" />';
        } else {
                $bulledaide = '';
        }

        //gestion des valeurs par defaut : d'abord on regarde s'il y a une valeur a modifier,
        //puis s'il y a une variable passee en GET,
        //enfin on prend la valeur par defaut du formulaire sinon
        if (isset($valeurs_fiche[$identifiant])) {
                $defauts = $valeurs_fiche[$identifiant];
        } elseif (isset($_GET[$identifiant])) {
            $defauts = stripslashes($_GET[$identifiant]);
        } else {
            $defauts = stripslashes($valeur_par_defaut);
        }


        //si la valeur de nb_max_car est vide, on la mets au maximum
        if ($nb_max_car == '') $nb_max_car = 255;

        //par defaut il s'agit d'input html de type "text" (on precise si cela n'a pas ete entre)
        if ($type_input == '') $type_input = 'text';

        $input_html  = '<div class="control-group form-group">'."\n".'<div class="control-label col-xs-3">';
        $input_html .= ($obligatoire == 1) ? '<span class="symbole_obligatoire">*&nbsp;</span>' : '';
        $input_html .= $label.$bulledaide.' : </div>'."\n";
        $input_html .= '<div class="controls col-xs-8">'."\n";
        $input_html .= '<input type="'.$type_input.'"';
        $input_html .= ($defauts != '') ? ' value="'.htmlspecialchars($defauts, ENT_COMPAT | ENT_HTML401, TEMPLATES_DEFAULT_CHARSET).'"' : '';
        $input_html .= ' name="'.$identifiant.'" class="form-control input-xxlarge" id="'.$identifiant.'"';
        $input_html .= ' maxlength="'.$nb_max_car.'" size="'.$nb_max_car.'"';
        $input_html .= ($type_input == 'number' && $nb_min_car != '') ? ' min="'.$nb_min_car.'"' : '';
        $input_html .= ($type_input == 'number') ? ' max="'.$nb_max_car.'"' : '';
        $input_html .= ($regexp != '') ? ' pattern="'.$regexp.'"' : '';
        $input_html .= ($obligatoire == 1) ? ' required="required"' : '';
        $input_html .= '>'."\n".'</div>'."\n".'</div>'."\n";

        $formtemplate->addElement('html', $input_html) ;

    } elseif ($mode == 'requete') {
    // TODO tester
            return array($tableau_template[1] => $valeurs_fiche[$tableau_template[1]]);
    } elseif ($mode == 'html') {
    // TODO tester
            $html = '';
            if (isset($valeurs_fiche[$tableau_template[1]]) && $valeurs_fiche[$tableau_template[1]]!='') {
                if ($tableau_template[1] == 'bf_titre') {
                    // Le titre
                    $html .= '<h1 class="BAZ_fiche_titre">'.$valeurs_fiche[$tableau_template[1]].'</h1>'."\n";
                } else {
                    $html = '<div class="BAZ_rubrique" data-id="'.$tableau_template[1].'">'."\n".
                            '<span class="BAZ_label">'.$tableau_template[2].'&nbsp;:</span>'."\n";
                    $html .= '<span class="BAZ_texte"> ';
                    $html .= $valeurs_fiche[$tableau_template[1]].'</span>'."\n".'</div> <!-- /.BAZ_rubrique -->'."\n";
                }
            }
        return $html;
    }
}




/** utilisateur_wikini() - Ajoute un élément de type texte pour créer un utilisateur wikini au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour l'élément texte
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @return   void
 */
// TODO : ne pas enregistrer le mot de passe dans la fiche bazar ?
function utilisateur_wikini(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    if ($mode == 'saisie') {
        $option=array('size'=>$tableau_template[3],'maxlength'=>$tableau_template[4], 'id' => 'nomwiki');
        if (!isset($valeurs_fiche['nomwiki'])) {
                //mot de passe
                $bulledaide = '';
                if (isset($tableau_template[10]) && $tableau_template[10]!='') $bulledaide = ' &nbsp;&nbsp;<img class="tooltip_aide" title="'.htmlentities($tableau_template[10], ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'" src="tools/bazar/presentation/images/aide.png" width="16" height="16" alt="image aide" />';
                $option = array('size' => $tableau_template[3], 'class' => 'form-control');
                $formtemplate->addElement('password', 'mot_de_passe_wikini', _t('BAZ_MOT_DE_PASSE').$bulledaide, $option) ;
                $formtemplate->addElement('password', 'mot_de_passe_repete_wikini', _t('BAZ_MOT_DE_PASSE').' ('._t('BAZ_VERIFICATION').')', $option) ;
        } 
        else {
            $formtemplate->addElement('hidden', 'nomwiki', $valeurs_fiche['nomwiki']) ;
        }
    } elseif ($mode == 'requete') {

//	    if (!isset($valeurs_fiche['nomwiki'])) {

	  if ( isset($GLOBALS['_BAZAR_']['provenance'])  &&  $GLOBALS['_BAZAR_']['provenance']=='import') {
		  $nomwiki = genere_nom_wiki($valeurs_fiche['nomwiki']);
	  }
	  else {
           	 if ($GLOBALS['wiki']->IsWikiName($valeurs_fiche[$tableau_template[1]])) {
                	$nomwiki = $valeurs_fiche[$tableau_template[1]];
	            } else {
        	        $nomwiki = genere_nom_wiki($valeurs_fiche[$tableau_template[1]]);
	            }
	  }
	    if (!$GLOBALS['wiki']->LoadUser($nomwiki)) { // Pour eviter les doublons
	    // 
	    // 
	            $requeteinsertionuserwikini = 'INSERT INTO '.$GLOBALS['wiki']->config["table_prefix"]."users SET ".
        	    "signuptime = now(), ".
	            "name = '".mysql_real_escape_string($nomwiki)."', ".
	            "email = '".mysql_real_escape_string($valeurs_fiche[$tableau_template[2]])."', ".
        	    "password = md5('".mysql_real_escape_string($valeurs_fiche['mot_de_passe_wikini'])."')";
	            $resultat = $GLOBALS['wiki']->query($requeteinsertionuserwikini) ;
	            
	   // On s'identifie de facon a attribuer la propriete de la fiche a l'utilisateur qui vient d etre cree
	   	   $GLOBALS['wiki']->SetUser($GLOBALS['wiki']->LoadUser($nomwiki));
	   // indicateur pour la gestion des droits associee a la fiche.
		  $GLOBALS['utilisateur_wikini']=true;
	    }


	/*		//envoi mail nouveau mot de passe : il vaut mieux ne pas envoyer de mots de passe en clair.
	 * 
            $lien = str_replace("/wakka.php?wiki=","",$GLOBALS['wiki']->config["base_url"]);
            $objetmail = '['.str_replace("http://","",$lien).'] Vos nouveaux identifiants sur le site '.$GLOBALS['wiki']->config["wakka_name"];
            $messagemail = "Bonjour!\n\nVotre inscription sur le site a ete finalisee, dorenavant vous pouvez vous identifier avec les informations suivantes :\n\nVotre identifiant NomWiki : ".$nomwiki."\n\nVotre mot de passe : ". $valeurs_fiche['mot_de_passe_wikini'] . "\n\nA tres bientot ! \n\n";
            $headers =   'From: '.BAZ_ADRESSE_MAIL_ADMIN . "\r\n" .
                'Reply-To: '.BAZ_ADRESSE_MAIL_ADMIN . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
	    print_r($valeurs_fiche);
	    print $valeurs_fiche[$tableau_template[2]];
	    exit;
            mail($valeurs_fiche[$tableau_template[2]], remove_accents($objetmail), $messagemail, $headers);
*/
            // ajout dans la liste de mail
            if (isset($valeurs_fiche[$tableau_template[5]]) && $valeurs_fiche[$tableau_template[5]]!='') {
                $headers =   'From: '.$valeurs_fiche[$tableau_template[2]] . "\r\n" .
                'Reply-To: '. $valeurs_fiche[$tableau_template[2]] . "\r\n" .
                'X-Mailer: PHP/' . phpversion();
                mail($valeurs_fiche[$tableau_template[5]], 'inscription a la liste de discussion', 'inscription', $headers);
            }
            return array('nomwiki' => $nomwiki);
       // } else {
         //   return array('nomwiki' => $valeurs_fiche['nomwiki']);
       // }
    } elseif ($mode == 'recherche') {

    } elseif ($mode == 'html') {

    }
}


/** inscriptionliste() - Permet de s'isncrire à une liste
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour l'élément texte
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @return   void
 */
function inscriptionliste(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
	//Remplacer champ par subscribe / unsubscribe et ne pas faire le test
    $id = str_replace(array('@','.'), array('',''),$tableau_template[1]);
    $valsub = str_replace('@', '-subscribe@', $tableau_template[1]);
    $valunsub = str_replace('@', '-unsubscribe@', $tableau_template[1]);
    if ($mode == 'saisie') {
        $input_html = '<div class="control-group form-group">
                    <div class="controls col-xs-8"> 
                        <div class="checkbox">
                          <input id="'.$id.'" type="checkbox"'.(($valeurs_fiche[$id]==$valsub) ? ' checked="checked"' : '').' value="'.$tableau_template[1].'" name="'.$id.'" class="element_checkbox">
                          <label for="'.$id.'">'.$tableau_template[2].'</label>
                        </div>
                    </div>
                </div>';
        $formtemplate->addElement('html', $input_html) ;   
    } elseif ($mode == 'requete') {
        //var_dump($valeurs_fiche);

	
	if (!class_exists("Mail")) {
	        include_once 'tools/contact/libs/contact.functions.php';
	}

	if ( isset($GLOBALS['_BAZAR_']['provenance'])  &&  $GLOBALS['_BAZAR_']['provenance']=='import') {
		if ($valeurs_fiche[$id]==$valsub) { 
		    send_mail($valeurs_fiche[$tableau_template[3]], $valeurs_fiche['bf_titre'], $valsub, 'subscribe', 'subscribe', 'subscribe');
		    return array($id => $valeurs_fiche[$id]);
		} 
		else {
			if ($valeurs_fiche[$id]==$valunsub) { 
				// On n'envoit pas de message dans ce cas la, car ca n'a pas de sens ...
//			    send_mail($valeurs_fiche[$tableau_template[3]], $valeurs_fiche['bf_titre'], $valunsub, 'unsubscribe', 'unsubscribe', 'unsubscribe');
		          return array($id => $valeurs_fiche[$id]);
			}
		}


	}
	else {
		if (isset($_POST[$id])) { 
		    send_mail($valeurs_fiche[$tableau_template[3]], $valeurs_fiche['bf_titre'], $valsub, 'subscribe', 'subscribe', 'subscribe');
		    $valeurs_fiche[$tableau_template[1]] = $valsub;
		    return array($id => $valeurs_fiche[$tableau_template[1]]);
		} 
		else {
			// on ne desabonne que si abonne precedement  
		    if (isset($valeurs_fiche[$id])) {
			    send_mail($valeurs_fiche[$tableau_template[3]], $valeurs_fiche['bf_titre'], $valunsub, 'unsubscribe', 'unsubscribe', 'unsubscribe');
			    $valeurs_fiche[$tableau_template[1]] = $valunsub; 
			    return array($id => $valeurs_fiche[$tableau_template[1]]);
		    }
		}
	}
     } elseif ($mode == 'recherche') {

    } elseif ($mode == 'html') {

    }
}



/** champs_cache() - Ajoute un élément caché au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour l'élément caché
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @param    mixed   Le tableau des valeurs de la fiche
 *
 * @return   void
 */
function champs_cache(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    if ($mode == 'saisie') {
        $formtemplate->addElement('hidden', $tableau_template[1], $tableau_template[2], array ('id' => $tableau_template[1])) ;
        //gestion des valeurs par défaut
        $defs=array($tableau_template[1]=>$tableau_template[5]);
        $formtemplate->setDefaults($defs);
    } elseif ($mode == 'requete') {
        return array($tableau_template[1] => $valeurs_fiche[$tableau_template[1]]);
    } elseif ($mode == 'recherche') {

    } elseif ($mode == 'html') {

    }
}

/** champs_mail() - Ajoute un élément texte formaté comme un mail au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour l'élément texte
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @return   void
 */
function champs_mail(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    list($type, $identifiant, $label, $nb_min_car, $nb_max_car, $valeur_par_defaut, $regexp, $type_input , $obligatoire, $sendmail, $bulle_d_aide) = $tableau_template;
    if ($mode == 'saisie') {
                // on prepare le html de la bulle d'aide, si elle existe
        if ($bulle_d_aide != '') {
            $bulledaide = '&nbsp;&nbsp;<img class="tooltip_aide" title="'.htmlentities($bulle_d_aide, ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'" src="tools/bazar/presentation/images/aide.png" width="16" height="16" alt="image aide" />';
        } else {
            $bulledaide = '';
        }

        //gestion des valeurs par defaut : d'abord on regarde s'il y a une valeur a modifier,
        //puis s'il y a une variable passee en GET,
        //enfin on prend la valeur par defaut du formulaire sinon
        if (isset($valeurs_fiche[$identifiant])) {
            $defauts = $valeurs_fiche[$identifiant];
        } elseif (isset($_GET[$identifiant])) {
            $defauts = stripslashes($_GET[$identifiant]);
        } else {
            $defauts = stripslashes($valeur_par_defaut);
        }

        //si la valeur de nb_max_car est vide, on la mets au maximum
        if ($nb_max_car == '') $nb_max_car = 255;

        //par defaut il s'agit d'input html de type "text" (on precise si cela n'a pas ete entre)
        if ($type_input == '') $type_input = 'email';

        $input_html  = '<div class="control-group form-group">'."\n".'<div class="control-label col-xs-3">';
        $input_html .= ($obligatoire == 1) ? '<span class="symbole_obligatoire">*&nbsp;</span>' : '';
        $input_html .= $label.$bulledaide.' : </div>'."\n";
        $input_html .= '<div class="controls col-xs-8">'."\n";
        $input_html .= '<input type="'.$type_input.'"';
        $input_html .= ($defauts != '') ? ' value="'.$defauts.'"' : '';
        $input_html .= ' name="'.$identifiant.'" class="form-control" id="'.$identifiant.'"';
        $input_html .= ' maxlength="'.$nb_max_car.'" size="'.$nb_max_car.'"';
        $input_html .= ($obligatoire == 1) ? ' required="required"' : '';
        $input_html .= '>'."\n".'</div>'."\n".'</div>'."\n";
        if ($sendmail == 1) {
            $formtemplate->addElement('hidden', 'sendmail', $identifiant);
        }
        $formtemplate->addElement('html', $input_html) ;
    } elseif ($mode == 'requete') {
	if ($sendmail == 1) {
            $valeurs_fiche['sendmail']=$identifiant;
        }
        return array($tableau_template[1] => $valeurs_fiche[$tableau_template[1]]);

    } elseif ($mode == 'recherche') {

    } elseif ($mode == 'html') {
        $html = '';
        if (isset($valeurs_fiche[$tableau_template[1]]) && $valeurs_fiche[$tableau_template[1]]!='') {
            $html = '<div class="BAZ_rubrique" data-id="'.$tableau_template[1].'">'."\n".
                    '<span class="BAZ_label">'.$tableau_template[2].'&nbsp;:</span>'."\n";
            $html .= '<span class="BAZ_texte"><a href="mailto:'.$valeurs_fiche[$tableau_template[1]].'" class="BAZ_lien_mail">';
            $html .= $valeurs_fiche[$tableau_template[1]].'</a></span>'."\n".'</div> <!-- /.BAZ_rubrique -->'."\n";
        }

        return $html;
    }
}

/** mot_de_passe() - Ajoute un element de type mot de passe au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour l'élément mot de passe
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @return   void
 */
function mot_de_passe(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    if ($mode == 'saisie') {
        $formtemplate->addElement('password', 'mot_de_passe', $tableau_template[2], array('size' => $tableau_template[3])) ;
        $formtemplate->addElement('password', 'mot_de_passe_repete', $tableau_template[7], array('size' => $tableau_template[3])) ;
        /*$formtemplate->addRule('mot_de_passe', $tableau_template[5], 'required', '', 'client') ;
          $formtemplate->addRule('mot_de_passe_repete', $tableau_template[5], 'required', '', 'client') ;
          $formtemplate->addRule(array ('mot_de_passe', 'mot_de_passe_repete'), $tableau_template[5], 'compare', '', 'client') ;*/
    } elseif ($mode == 'requete') {
        return array($tableau_template[1] => md5($valeurs_fiche['mot_de_passe'])) ;
    } elseif ($mode == 'recherche') {

    } elseif ($mode == 'html') {

    }
}


/** textelong() - Ajoute un élément de type texte long (textarea) au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour l'élément texte long
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @return   void
 */
function textelong(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    list($type, $identifiant, $label, $nb_colonnes, $nb_lignes, $valeur_par_defaut, $longueurmax, $formatage , $obligatoire, $apparait_recherche, $bulle_d_aide) = $tableau_template;
    if (empty($formatage) || $formatage == 'wiki') $formatage = 'wiki-textarea';
    if ($mode == 'saisie') {
        $longueurmaxlabel = ($longueurmax ? ' (<span class="charsRemaining">'.$longueurmax.'</span> caract&egrave;res restants)' : '' );
        $bulledaide = '';
        if ($bulle_d_aide!='') $bulledaide = ' &nbsp;&nbsp;<img class="tooltip_aide" title="'.htmlentities($bulle_d_aide, ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'" src="tools/bazar/presentation/images/aide.png" width="16" height="16" alt="image aide" />';

        $options = array('id' => $identifiant, 'class' => 'form-control input-xxlarge '.$formatage);
        if ($longueurmax != '') $options['maxlength'] = $longueurmax;
        //gestion du champs obligatoire
        $symb = '';
        if (isset($obligatoire) && $obligatoire==1) {
            $options['required'] = 'required' ;
            $symb .= '<span class="symbole_obligatoire">*&nbsp;</span>';
        }

        $formtexte= new HTML_QuickForm_textarea($identifiant, $symb.$label.$longueurmaxlabel.$bulledaide, $options);
        $formtexte->setCols($nb_colonnes);
        $formtexte->setRows($nb_lignes);
        $formtemplate->addElement($formtexte) ;

        //gestion des valeurs par defaut : d'abord on regarde s'il y a une valeur a modifier,
        //puis s'il y a une variable passee en GET,
        //enfin on prend la valeur par defaut du formulaire sinon
        if (isset($valeurs_fiche[$identifiant])) {
            $defauts = array( $identifiant => $valeurs_fiche[$identifiant] );
        } elseif (isset($_GET[$identifiant])) {
            $defauts = array( $identifiant => stripslashes($_GET[$identifiant]) );
        } else {
            $defauts = array( $identifiant => stripslashes($tableau_template[5]) );
        }
        $formtemplate->setDefaults($defauts);

        //$formtemplate->applyFilter($identifiant, 'addslashes') ;

        //gestion du champs obligatoire
        if (isset($obligatoire) && $obligatoire==1) {
            /*$formtemplate->addRule($identifiant,  $label.' obligatoire', 'required', '', 'client') ;*/
        }
    } elseif ($mode == 'requete') {
        return array($identifiant => $valeurs_fiche[$identifiant]);
    } elseif ($mode == 'html') {
        $html = '';
        if (isset($valeurs_fiche[$identifiant]) && $valeurs_fiche[$identifiant]!='') {
            $html = '<div class="BAZ_rubrique" data-id="'.$identifiant.'">'."\n".
                    '<span class="BAZ_label">'.$label.'&nbsp;:</span>'."\n";
            $html .= '<span class="BAZ_texte"> ';
            if ($formatage == 'wiki-textarea') {
                $containsattach = (strpos($valeurs_fiche[$identifiant],'{{attach') !== false);
                if ($containsattach) {
                    $oldpage = $GLOBALS['wiki']->GetPageTag();
                    $oldpagearray = $GLOBALS['wiki']->page;
                    $GLOBALS['wiki']->tag = $valeurs_fiche['id_fiche'];
                    $GLOBALS['wiki']->page = $GLOBALS['wiki']->LoadPage($GLOBALS['wiki']->tag);       
                }
                $html .= $GLOBALS['wiki']->Format($valeurs_fiche[$identifiant]);
                if ($containsattach) {
                    $GLOBALS['wiki']->tag = $oldpage;
                    $GLOBALS['wiki']->page = $oldpagearray;
                }
            } elseif ($formatage == 'nohtml') {
                $html .= htmlentities($valeurs_fiche[$identifiant], ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET);
            } else {
                $html .= nl2br($valeurs_fiche[$identifiant]);
            }
            $html .= '</span>'."\n".'</div> <!-- /.BAZ_rubrique -->'."\n";
        }

        return $html;
    }
}



/** url() - Ajoute un élément de type url internet au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour l'élément url internet
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @return   void
 */

/** lien_internet() - Ajoute un élément de type texte contenant une URL au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour l'élément texte url
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @return   void
 */
function lien_internet(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{

    list($type, $identifiant, $label, $nb_min_car, $nb_max_car, $valeur_par_defaut, $regexp, $type_input , $obligatoire, , $bulle_d_aide) = $tableau_template;
    if ($mode == 'saisie') {
                // on prepare le html de la bulle d'aide, si elle existe
        if ($bulle_d_aide != '') {
            $bulledaide = '&nbsp;&nbsp;<img class="tooltip_aide" title="'.htmlentities($bulle_d_aide, ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'" src="tools/bazar/presentation/images/aide.png" width="16" height="16" alt="image aide" />';
        } else {
            $bulledaide = '';
        }

        //gestion des valeurs par defaut : d'abord on regarde s'il y a une valeur a modifier,
        //puis s'il y a une variable passee en GET,
        //enfin on prend la valeur par defaut du formulaire sinon
        if (isset($valeurs_fiche[$identifiant])) {
            $defauts = $valeurs_fiche[$identifiant];
        } elseif (isset($_GET[$identifiant])) {
            $defauts = stripslashes($_GET[$identifiant]);
        } else {
            $defauts = stripslashes($valeur_par_defaut);
        }

        //si la valeur de nb_max_car est vide, on la mets au maximum
        if ($nb_max_car == '') $nb_max_car = 255;

        //par defaut il s'agit d'input html de type "text" (on precise si cela n'a pas ete entre)
        if ($type_input == '') $type_input = 'url';

        $input_html  = '<div class="control-group form-group">'."\n".'<div class="control-label col-xs-3">';
        $input_html .= ($obligatoire == 1) ? '<span class="symbole_obligatoire">*&nbsp;</span>' : '';
        $input_html .= $label.$bulledaide.' : </div>'."\n";
        $input_html .= '<div class="controls col-xs-8">'."\n";
        $input_html .= '<input type="'.$type_input.'"';
        $input_html .= ($defauts != 'http://') ? ' value="'.$defauts.'"' : ' placeholder="'.$defauts.'"';
        $input_html .= ' name="'.$identifiant.'" class="form-control input-xxlarge" id="'.$identifiant.'"';
        $input_html .= ($obligatoire == 1) ? ' required="required"' : '';
        $input_html .= '>'."\n".'</div>'."\n".'</div>'."\n";


        $formtemplate->addElement('html', $input_html) ;
    } elseif ($mode == 'requete') {
        //on supprime la valeur, si elle est restée par défaut
        if ($valeurs_fiche[$tableau_template[1]]!='http://') return array($tableau_template[1] => $valeurs_fiche[$tableau_template[1]]);
        else return;
    } elseif ($mode == 'html') {
        $html = '';
        if (isset($valeurs_fiche[$tableau_template[1]]) && $valeurs_fiche[$tableau_template[1]]!='') {
            $html .= '<div class="BAZ_rubrique" data-id="'.$tableau_template[1].'">'."\n".
                     '<span class="BAZ_label">'.$tableau_template[2].'&nbsp;:</span>'."\n";
            $html .= '<span class="BAZ_texte">'."\n".
                     '<a href="'.$valeurs_fiche[$tableau_template[1]].'" class="BAZ_lien" target="_blank">';
            $html .= $valeurs_fiche[$tableau_template[1]].'</a></span>'."\n".'</div> <!-- /.BAZ_rubrique -->'."\n";
        }

        return $html;
    }
}

/** fichier() - Ajoute un element de type fichier au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour l'élément fichier
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @return   void
 */
function fichier(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    list($type, $identifiant, $label, $taille_maxi, $taille_maxi2, $hauteur, $largeur, $alignement, $obligatoire, $apparait_recherche, $bulle_d_aide) = $tableau_template;
    $option = array();
    if ($mode == 'saisie') {
        $label = ($obligatoire == 1) ? '<span class="symbole_obligatoire">*&nbsp;</span>'.$label : $label;
        if (isset($valeurs_fiche[$type.$identifiant]) && $valeurs_fiche[$type.$identifiant] != '') {
            if (isset($_GET['delete_file']) && $_GET['delete_file'] == $valeurs_fiche[$type.$identifiant] ) {
                if (baz_a_le_droit('supp_fiche', (isset($valeurs_fiche['createur']) ? $valeurs_fiche['createur'] : ''))) {
                    if (file_exists(BAZ_CHEMIN_UPLOAD.$valeurs_fiche[$type.$identifiant])) {
                        unlink(BAZ_CHEMIN_UPLOAD.$valeurs_fiche[$type.$identifiant]);
                    }
                } else {
                    $info = '<div class="alert alert-info">'._t('BAZ_DROIT_INSUFFISANT').'</div>'."\n";
                    require_once BAZ_CHEMIN.'libs'.DIRECTORY_SEPARATOR.'vendor/HTML/QuickForm/html.php';
                    $formtemplate->addElement(new HTML_QuickForm_html("\n".$info."\n")) ;
                }
            }
            if (file_exists(BAZ_CHEMIN_UPLOAD.$valeurs_fiche[$type.$identifiant])) {
                $lien_supprimer = $GLOBALS['wiki']->href( 'edit', $GLOBALS['wiki']->GetPageTag() );
                $lien_supprimer .= ($GLOBALS['wiki']->config["rewrite_mode"] ? "?" : "&").'delete_file='.$valeurs_fiche[$type.$identifiant];



                $html = '<div class="control-group form-group">
                    <div class="control-label col-xs-3">'.$label.' : </div>
                    <div class="controls col-xs-8">
                    <a href="'.BAZ_CHEMIN_UPLOAD.$valeurs_fiche[$type.$identifiant].'" target="_blank">'.$valeurs_fiche[$type.$identifiant].'</a>'."\n".
                    '<a href="'.str_replace('&', '&amp;', $lien_supprimer).'" onclick="javascript:return confirm(\''._t('BAZ_CONFIRMATION_SUPPRESSION_FICHIER').'\');" >'._t('BAZ_SUPPRIMER').'</a><br />
                    </div>
                    </div>';
                $formtemplate->addElement('html', $html) ;
                $formtemplate->addElement('hidden', $type.$identifiant, $valeurs_fiche[$type.$identifiant]);
            } else {
                if ($bulle_d_aide!='') $label = $label.' &nbsp;&nbsp;<img class="tooltip_aide" title="'.htmlentities($bulle_d_aide, ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'" src="tools/bazar/presentation/images/aide.png" width="16" height="16" alt="image aide" />';

                //gestion du champs obligatoire
                if (isset($obligatoire) && $obligatoire==1) {
                    $option = array('required' =>'required') ;
                }

                $formtemplate->addElement('file', $type.$identifiant, $label, $option) ;
            }
        } else {
            if ($bulle_d_aide!='') $label = $label.' &nbsp;&nbsp;<img class="tooltip_aide" title="'.htmlentities($bulle_d_aide, ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'" src="tools/bazar/presentation/images/aide.png" width="16" height="16" alt="image aide" />';

            //gestion du champs obligatoire
            if (isset($obligatoire) && $obligatoire==1) {
                $option = array('required' =>'required') ;
            }

            $formtemplate->addElement('file', $type.$identifiant, $label, $option) ;
        }
    } elseif ($mode == 'requete') {
        if (isset($_FILES[$type.$identifiant]['name']) && $_FILES[$type.$identifiant]['name']!='') {
            //on enleve les accents sur les noms de fichiers, et les espaces
            $nomfichier = preg_replace("/&([a-z])[a-z]+;/i","$1", htmlentities($identifiant.'_'.$_FILES[$type.$identifiant]['name'], ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET));
            $nomfichier = str_replace(' ', '_', $nomfichier);
            $chemin_destination=BAZ_CHEMIN_UPLOAD.$nomfichier;
            //verification de la presence de ce fichier
            $extension=obtenir_extension($nomfichier);
            if ($extension!='' && extension_autorisee($extension)==true) {
                if (!file_exists($chemin_destination)) {
                    move_uploaded_file($_FILES[$type.$identifiant]['tmp_name'], $chemin_destination);
                    chmod ($chemin_destination, 0755);
                } else echo 'fichier déja existant<br />';
            } else {
                echo 'fichier non autorise<br />';

                return array($type.$identifiant => '');
            }

            return array($type.$identifiant => $nomfichier);
        } elseif (isset($_POST[$type.$identifiant]) && file_exists(BAZ_CHEMIN_UPLOAD.$_POST[$type.$identifiant]) ) {
            return array($type.$identifiant => $_POST[$type.$identifiant]);
        } else {
            return array($type.$identifiant => '');
        }





    } elseif ($mode == 'recherche') {

    } elseif ($mode == 'html') {
        $html = '';
        if (isset($valeurs_fiche[$type.$identifiant]) && $valeurs_fiche[$type.$identifiant]!='') {
            $html = '<div class="BAZ_fichier">T&eacute;l&eacute;charger le fichier : <a href="'.BAZ_CHEMIN_UPLOAD.$valeurs_fiche[$type.$identifiant].'" target="_blank">'.$valeurs_fiche[$type.$identifiant].'</a>'."\n";
        }
        if ($html!='') $html .= '</div>'."\n";

        return $html;
    }
}


/** image() - Ajoute un element de type image au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des differentes option pour l'element image
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @return   void
 */
function image(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    list($type, $identifiant, $label, $hauteur_vignette, $largeur_vignette, $hauteur_image, $largeur_image, $class, $obligatoire, $apparait_recherche, $bulle_d_aide) = $tableau_template;

    if ($mode == 'saisie') {
        $label = ($obligatoire == 1) ? '<span class="symbole_obligatoire">*&nbsp;</span>'.$label : $label;
        //on verifie qu'il ne faut supprimer l'image
        if (isset($_GET['suppr_image']) && isset($valeurs_fiche[$type.$identifiant]) && $valeurs_fiche[$type.$identifiant]==$_GET['suppr_image']) {
            if (baz_a_le_droit('supp_fiche', (isset($valeurs_fiche['createur']) ? $valeurs_fiche['createur'] : ''))) {
                //on efface le fichier s'il existe
                if (file_exists(BAZ_CHEMIN_UPLOAD.$valeurs_fiche[$type.$identifiant])) {
                    unlink(BAZ_CHEMIN_UPLOAD.$valeurs_fiche[$type.$identifiant]);
                }
                $nomimg = $valeurs_fiche[$type.$identifiant];
                //on efface une entrée de la base de données
                unset($valeurs_fiche[$type.$identifiant]);
                $valeur = $valeurs_fiche;
                $valeur['date_maj_fiche'] = date( 'Y-m-d H:i:s', time() );
                $valeur['id_fiche'] = $GLOBALS['_BAZAR_']['id_fiche'];
                $valeur = json_encode(array_map("utf8_encode", $valeur));
                //on sauve les valeurs d'une fiche dans une PageWiki, pour garder l'historique
                $GLOBALS["wiki"]->SavePage($GLOBALS['_BAZAR_']['id_fiche'], $valeur);

                //on affiche les infos sur l'effacement du fichier, et on reinitialise la variable pour le fichier pour faire apparaitre le formulaire d'ajout par la suite
                $info = '<div class="alert alert-info">'._t('BAZ_FICHIER').$nomimg._t('BAZ_A_ETE_EFFACE').'</div>'."\n";
                require_once BAZ_CHEMIN.'libs'.DIRECTORY_SEPARATOR.'vendor/HTML/QuickForm/html.php';
                $formtemplate->addElement(new HTML_QuickForm_html("\n".$info."\n")) ;
                $valeurs_fiche[$type.$identifiant] = '';
            } else {
                $info = '<div class="alert">'._t('BAZ_DROIT_INSUFFISANT').'</div>'."\n";
                require_once BAZ_CHEMIN.'libs'.DIRECTORY_SEPARATOR.'vendor/HTML/QuickForm/html.php';
                $formtemplate->addElement(new HTML_QuickForm_html("\n".$info."\n")) ;
            }
        }

        if ($bulle_d_aide!='') $label = $label.' &nbsp;&nbsp;<img class="tooltip_aide" title="'.htmlentities($bulle_d_aide, ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'" src="tools/bazar/presentation/images/aide.png" width="16" height="16" alt="image aide" />';

        //cas ou il y a une image dans la base de donnees
        if (isset($valeurs_fiche[$type.$identifiant]) && $valeurs_fiche[$type.$identifiant] != '') {

            //il y a bien le fichier image, on affiche l'image, avec possibilite de la supprimer ou de la modifier
            if (file_exists(BAZ_CHEMIN_UPLOAD.$valeurs_fiche[$type.$identifiant])) {

                require_once BAZ_CHEMIN.'libs'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'HTML/QuickForm/html.php';
                $formtemplate->addElement(new HTML_QuickForm_html("\n".'<fieldset class="bazar_fieldset">'."\n".'<legend>'.$label.'</legend>'."\n")) ;

                $lien_supprimer = $GLOBALS['wiki']->href( 'edit', $GLOBALS['wiki']->GetPageTag() );
                $lien_supprimer .= ($GLOBALS['wiki']->config["rewrite_mode"] ? "?" : "&").'suppr_image='.$valeurs_fiche[$type.$identifiant];

                $html_image = afficher_image($valeurs_fiche[$type.$identifiant], $label, '', $largeur_vignette, $hauteur_vignette, $largeur_image, $hauteur_image);
                $lien_supprimer_image = '<a class="btn btn-danger btn-mini" href="'.str_replace('&', '&amp;', $lien_supprimer).'" onclick="javascript:return confirm(\''.
                    _t('BAZ_CONFIRMATION_SUPPRESSION_IMAGE').'\');" ><i class="icon-trash icon-white"></i>&nbsp;'._t('BAZ_SUPPRIMER_IMAGE').'</a>'."\n";
                if ($html_image!='') $formtemplate->addElement('html', $html_image) ;
                //gestion du champs obligatoire
                $option = '';
                $formtemplate->addElement('file', $type.$identifiant, $lien_supprimer_image._t('BAZ_MODIFIER_IMAGE'), $option) ;
                $formtemplate->addElement('hidden', 'oldimage_'.$type.$identifiant, $valeurs_fiche[$type.$identifiant]) ;
                $formtemplate->addElement(new HTML_QuickForm_html("\n".'</fieldset>'."\n")) ;
            }

            //le fichier image n'existe pas, du coup on efface l'entree dans la base de donnees
            else {
                echo '<div class="alert alert-danger">'._t('BAZ_FICHIER').$valeurs_fiche[$type.$identifiant]._t('BAZ_FICHIER_IMAGE_INEXISTANT').'</div>'."\n";
                //on efface une entrée de la base de données
                unset($valeurs_fiche[$type.$identifiant]);
                $valeur = $valeurs_fiche;
                $valeur['date_maj_fiche'] = date( 'Y-m-d H:i:s', time() );
                $valeur['id_fiche'] = $GLOBALS['_BAZAR_']['id_fiche'];
                $valeur = json_encode(array_map("utf8_encode", $valeur));
                //on sauve les valeurs d'une fiche dans une PageWiki, pour garder l'historique
                $GLOBALS["wiki"]->SavePage($GLOBALS['_BAZAR_']['id_fiche'], $valeur);
            }
        }
        //cas ou il n'y a pas d'image dans la base de donnees, on affiche le formulaire d'envoi d'image
        else {
            //gestion du champs obligatoire
            $option = '';
            if (isset($obligatoire) && $obligatoire==1) {
                $option = array('required' =>'required') ;
            }
            $formtemplate->addElement('file', $type.$identifiant, $label, $option) ;
            //gestion du champs obligatoire
            if (isset($obligatoire) && $obligatoire==1) {
                /*$formtemplate->addRule('image', IMAGE_VALIDE_REQUIS, 'required', '', 'client') ;*/
            }

            //TODO: la verification du type de fichier ne marche pas
            $tabmime = array ('gif' => 'image/gif', 'jpg' => 'image/jpeg', 'png' => 'image/png');
            /*$formtemplate->addRule($type.$identifiant, 'Vous devez choisir une fichier de type image gif, jpg ou png', 'mimetype', $tabmime );*/
        }
    } elseif ($mode == 'requete') {
        if (isset($_FILES[$type.$identifiant]['name']) && $_FILES[$type.$identifiant]['name']!='') {

            //on enleve les accents sur les noms de fichiers, et les espaces
            $nomimage = preg_replace("/&([a-z])[a-z]+;/i","$1", htmlentities($identifiant.$_FILES[$type.$identifiant]['name'], ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET));
            $nomimage = str_replace(' ', '_', $nomimage);
            if (preg_match("/(gif|jpeg|png|jpg)$/i",$nomimage)) {
                $chemin_destination = BAZ_CHEMIN_UPLOAD.$nomimage;
                //verification de la presence de ce fichier
                if (!file_exists($chemin_destination)) {
                    move_uploaded_file($_FILES[$type.$identifiant]['tmp_name'], $chemin_destination);
                    chmod ($chemin_destination, 0755);
                    //generation des vignettes
                    if ($hauteur_vignette!='' && $largeur_vignette!='' && !file_exists('cache/vignette_'.$nomimage)) {
                        $adr_img = redimensionner_image($chemin_destination, 'cache/vignette_'.$nomimage, $largeur_vignette, $hauteur_vignette);
                    }
                    //generation des images
                    if ($hauteur_image!='' && $largeur_image!='' && !file_exists('cache/image_'.'_'.$nomimage)) {
                        $adr_img = redimensionner_image($chemin_destination, 'cache/image_'.$nomimage, $largeur_image, $hauteur_image);
                    }
                } else {
                    echo '<div class="alert alert-danger">L\'image '.$nomimage.' existait d&eacute;ja, elle n\'a pas &eacute;t&eacute; remplac&eacute;e.</div>';
                }
            } else {
                echo '<div class="alert alert-danger">Fichier non autoris&eacute;.</div>';
            }

            return array($type.$identifiant => $nomimage);
        } 
        elseif (isset($valeurs_fiche['oldimage_'.$type.$identifiant]) && $valeurs_fiche['oldimage_'.$type.$identifiant] != '') {
            return array($type.$identifiant => $valeurs_fiche['oldimage_'.$type.$identifiant]);
        } 
    } elseif ($mode == 'recherche') {

    } elseif ($mode == 'html') {
        if (isset($valeurs_fiche[$type.$identifiant]) && $valeurs_fiche[$type.$identifiant]!='' && file_exists(BAZ_CHEMIN_UPLOAD.$valeurs_fiche[$type.$identifiant]) ) {
            return afficher_image($valeurs_fiche[$type.$identifiant], $label, $class, $largeur_vignette, $hauteur_vignette, $largeur_image, $hauteur_image);
        }
    }
}

/** labelhtml() - Ajoute du texte HTML au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour le texte HTML
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @return   void
 */
function labelhtml(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    list($type, $texte_saisie, $texte_recherche, $texte_fiche) = $tableau_template;

    if ($mode == 'saisie') {
        require_once BAZ_CHEMIN.'libs'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'HTML/QuickForm/html.php';
        $formtemplate->addElement(new HTML_QuickForm_html("\n".$texte_saisie."\n")) ;
    } elseif ($mode == 'requete') {
        return;
    } elseif ($mode == 'formulaire_recherche') {
        $formtemplate->addElement('html', $texte_recherche);
    } elseif ($mode == 'html') {
        return $texte_fiche."\n";
    }
}

/** titre() - Action qui camouffle le titre et le génére a  partir d'autres champs au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour le texte HTML
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @return   void
 */
function titre(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    list($type, $template) = $tableau_template;

    if ($mode == 'saisie') {
        $formtemplate->addElement('hidden', 'bf_titre', $template, array ('id' => 'bf_titre')) ;
    } 
    elseif ($mode == 'requete') {

	    if ( isset($GLOBALS['_BAZAR_']['provenance'])  &&  $GLOBALS['_BAZAR_']['provenance']=='import') {
		        $GLOBALS['_BAZAR_']['id_fiche'] = (isset($valeurs_fiche['id_fiche']) ? $valeurs_fiche['id_fiche'] : genere_nom_wiki($valeurs_fiche['bf_titre']));
		        return array('bf_titre' => $valeurs_fiche['bf_titre'], 'id_fiche' => $GLOBALS['_BAZAR_']['id_fiche']);
		}

        preg_match_all  ('#{{(.*)}}#U'  , $_POST['bf_titre']  , $matches);
        $tab = array();
        foreach ($matches[1] as $var) {
            if (isset($_POST[$var])) {
                //pour une listefiche ou une checkboxfiche on cherche le titre de la fiche
                if ( preg_match('#^listefiche#',$var)!=false || preg_match('#^checkboxfiche#',$var)!=false ) {
                    $tab_fiche = baz_valeurs_fiche($_POST[$var]);
                    $_POST['bf_titre'] = str_replace('{{'.$var.'}}', ($tab_fiche['bf_titre']!=null) ? $tab_fiche['bf_titre'] : '', $_POST['bf_titre']);
                }
                //sinon on prend le label de la liste
                elseif ( preg_match('#^liste#',$var)!=false || preg_match('#^checkbox#',$var)!=false ) {
                    //on récupere le premier chiffre (l'identifiant de la liste)
                    preg_match_all('/[0-9]{1,4}/', $var, $matches);
                    $req = 'SELECT blv_label FROM '.BAZ_PREFIXE.'liste_valeurs WHERE blv_ce_liste='.$matches[0][0].' AND blv_valeur='.$_POST[$var].' AND blv_ce_i18n="fr-FR"';
                    $label = $GLOBALS['wiki']->LoadSingle($req) ;
                    $_POST['bf_titre'] = str_replace('{{'.$var.'}}', ($label[0]!=null) ? $label[0] : '', $_POST['bf_titre']);
                } else {
                    $_POST['bf_titre'] = str_replace('{{'.$var.'}}', $_POST[$var], $_POST['bf_titre']);
                }
            }
        }
        $GLOBALS['_BAZAR_']['id_fiche'] = (isset($valeurs_fiche['id_fiche']) ? $valeurs_fiche['id_fiche'] : genere_nom_wiki($_POST['bf_titre']));
        return array('bf_titre' => $_POST['bf_titre'], 'id_fiche' => $GLOBALS['_BAZAR_']['id_fiche']);
    } elseif ($mode == 'html') {
        // Le titre
        return '<h1 class="BAZ_fiche_titre">'.htmlentities($valeurs_fiche['bf_titre'], ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'</h1>'."\n";
    } elseif ($mode == 'formulaire_recherche') {
        return;
    }
}

/** carte_google() - Ajoute un élément de carte google au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour la carte google
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @return   void
 */
function carte_google(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    list($type, $lat, $lon, $classe, $obligatoire) = $tableau_template;

    if ($mode == 'saisie') {
        $scriptgoogle = '
//-----------------------------------------------------------------------------------------------------------
//--------------------TODO : ATTENTION CODE FACTORISABLE-----------------------------------------------------
//-----------------------------------------------------------------------------------------------------------
var geocoder;
var map;
var marker;
var infowindow;

function initialize()
{
    geocoder = new google.maps.Geocoder();
    var myLatlng = new google.maps.LatLng('.BAZ_GOOGLE_CENTRE_LAT.', '.BAZ_GOOGLE_CENTRE_LON.');
    var myOptions = {
      zoom: '.BAZ_GOOGLE_ALTITUDE.',
      center: myLatlng,
      mapTypeId: google.maps.MapTypeId.'.BAZ_TYPE_CARTO.',
      navigationControl: '.BAZ_AFFICHER_NAVIGATION.',
      navigationControlOptions: {style: google.maps.NavigationControlStyle.'.BAZ_STYLE_NAVIGATION.'},
      mapTypeControl: '.BAZ_AFFICHER_CHOIX_CARTE.',
      mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.'.BAZ_STYLE_CHOIX_CARTE.'},
      scaleControl: '.BAZ_AFFICHER_ECHELLE.' ,
      scrollwheel: '.BAZ_PERMETTRE_ZOOM_MOLETTE.'
    }
    map = new google.maps.Map(document.getElementById("map"), myOptions);

    //on pose un point si les coordonnées existent déja (cas d\'une modification de fiche)
    if (document.getElementById("latitude") && document.getElementById("latitude").value != \'\' &&
        document.getElementById("longitude") && document.getElementById("longitude").value != \'\' ) {
        var lat = document.getElementById("latitude").value;
        var lon = document.getElementById("longitude").value;
        latlngclient = new google.maps.LatLng(lat,lon);
        map.setCenter(latlngclient);
        infowindow = new google.maps.InfoWindow({
        content: "<h4>'._t('YOUR_POSITION').'<\/h4>'._t('TEXTE_POINT_DEPLACABLE').'",
        maxWidth: 250
        });
        //image du marqueur
        var image = new google.maps.MarkerImage(\''.BAZ_IMAGE_MARQUEUR.'\',
        //taille, point d\'origine, point d\'arrivee de l\'image
        new google.maps.Size('.BAZ_DIMENSIONS_IMAGE_MARQUEUR.'),
        new google.maps.Point('.BAZ_COORD_ORIGINE_IMAGE_MARQUEUR.'),
        new google.maps.Point('.BAZ_COORD_ARRIVEE_IMAGE_MARQUEUR.'));

        //ombre du marqueur
        var shadow = new google.maps.MarkerImage(\''.BAZ_IMAGE_OMBRE_MARQUEUR.'\',
        // taille, point d\'origine, point d\'arrivee de l\'image de l\'ombre
        new google.maps.Size('.BAZ_DIMENSIONS_IMAGE_OMBRE_MARQUEUR.'),
        new google.maps.Point('.BAZ_COORD_ORIGINE_IMAGE_OMBRE_MARQUEUR.'),
        new google.maps.Point('.BAZ_COORD_ARRIVEE_IMAGE_OMBRE_MARQUEUR.'));

    marker = new google.maps.Marker({
    position: latlngclient,
    map: map,
    icon: image,
    shadow: shadow,
    title: \'Votre emplacement\',
    draggable: true
    });
    infowindow.open(map,marker);
    google.maps.event.addListener(marker, \'click\', function() {
            infowindow.open(map,marker);
            });
    google.maps.event.addListener(marker, "dragend", function () {
            var lat = document.getElementById("latitude");lat.value = marker.getPosition().lat();
            var lon = document.getElementById("longitude");lon.value = marker.getPosition().lng();
            map.setCenter(marker.getPosition());
            });
    }
};

function showClientAddress()
{
    // If ClientLocation was filled in by the loader, use that info instead
    if (google.loader.ClientLocation) {
        latlngclient = new google.maps.LatLng(google.loader.ClientLocation.latitude, google.loader.ClientLocation.longitude);
        if (infowindow) {
            infowindow.close();
        }
        if (marker) {
            marker.setMap(null);
        }
        map.setCenter(latlngclient);
        var lat = document.getElementById("latitude");lat.value = map.getCenter().lat();
        var lon = document.getElementById("longitude");lon.value = map.getCenter().lng();

        infowindow = new google.maps.InfoWindow({
content: "<h4>'._t('YOUR_POSITION').'<\/h4>'._t('TEXTE_POINT_DEPLACABLE').'",
maxWidth: 250
});
//image du marqueur
var image = new google.maps.MarkerImage(\''.BAZ_IMAGE_MARQUEUR.'\',
        //taille, point d\'origine, point d\'arrivee de l\'image
        new google.maps.Size('.BAZ_DIMENSIONS_IMAGE_MARQUEUR.'),
        new google.maps.Point('.BAZ_COORD_ORIGINE_IMAGE_MARQUEUR.'),
        new google.maps.Point('.BAZ_COORD_ARRIVEE_IMAGE_MARQUEUR.'));

//ombre du marqueur
var shadow = new google.maps.MarkerImage(\''.BAZ_IMAGE_OMBRE_MARQUEUR.'\',
        // taille, point d\'origine, point d\'arrivee de l\'image de l\'ombre
        new google.maps.Size('.BAZ_DIMENSIONS_IMAGE_OMBRE_MARQUEUR.'),
        new google.maps.Point('.BAZ_COORD_ORIGINE_IMAGE_OMBRE_MARQUEUR.'),
        new google.maps.Point('.BAZ_COORD_ARRIVEE_IMAGE_OMBRE_MARQUEUR.'));

marker = new google.maps.Marker({
position: latlngclient,
map: map,
icon: image,
shadow: shadow,
title: "\''._t('YOUR_POSITION').'\'",
draggable: true
});
infowindow.open(map,marker);
google.maps.event.addListener(marker, \'click\', function() {
        infowindow.open(map,marker);
        });
google.maps.event.addListener(marker, "dragend", function () {
        var lat = document.getElementById("latitude");lat.value = marker.getPosition().lat();
        var lon = document.getElementById("longitude");lon.value = marker.getPosition().lng();
        map.setCenter(marker.getPosition());
        });
} else {alert("Localisation par votre acces Internet impossible...");}
};


function showAddress()
{
    if (document.getElementById("bf_adresse1")) 	var adress_1 = document.getElementById("bf_adresse1").value ; else var adress_1 = "";
    if (document.getElementById("bf_adresse2")) 	var adress_2 = document.getElementById("bf_adresse2").value ; else var adress_2 = "";
    if (document.getElementById("bf_ville")) 	var ville = document.getElementById("bf_ville").value ; else var ville = "";
    if (document.getElementById("bf_code_postal")) var cp = document.getElementById("bf_code_postal").value ; else var cp = "";
    if (document.getElementById("listeListePays")) var pays = document.getElementById("listeListePays").value ; else
        if (document.getElementById("liste3")) {
            var selectIndex=document.getElementById("liste3").selectedIndex;
            var pays = document.getElementById("liste3").options[selectIndex].text ;
        } else {
            var pays = "";
        };



    var address = adress_1 + \' \' + adress_2 + \' \'  + cp + \' \' + ville + \' \' +pays ;
    
    address = address.replace(/\\("|\'|\\)/g, " ");
    if (geocoder) {
        geocoder.geocode( { \'address\': address}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                if (status != google.maps.GeocoderStatus.ZERO_RESULTS) {
                if (infowindow) {
                infowindow.close();
                }
                if (marker) {
                marker.setMap(null);
                }
                map.setCenter(results[0].geometry.location);
                var lat = document.getElementById("latitude");lat.value = map.getCenter().lat();
                var lon = document.getElementById("longitude");lon.value = map.getCenter().lng();

                infowindow = new google.maps.InfoWindow({
                    content: "<h4>Votre emplacement<\/h4>'._t("TEXTE_POINT_DEPLACABLE").'",
                    maxWidth: 250
                });
                //image du marqueur
                var image = new google.maps.MarkerImage(\''.BAZ_IMAGE_MARQUEUR.'\',
                    //taille, point d\'origine, point d\'arrivee de l\'image
                    new google.maps.Size('.BAZ_DIMENSIONS_IMAGE_MARQUEUR.'),
                    new google.maps.Point('.BAZ_COORD_ORIGINE_IMAGE_MARQUEUR.'),
                    new google.maps.Point('.BAZ_COORD_ARRIVEE_IMAGE_MARQUEUR.'));

        //ombre du marqueur
        var shadow = new google.maps.MarkerImage(\''.BAZ_IMAGE_OMBRE_MARQUEUR.'\',
        // taille, point d\'origine, point d\'arrivee de l\'image de l\'ombre
        new google.maps.Size('.BAZ_DIMENSIONS_IMAGE_OMBRE_MARQUEUR.'),
        new google.maps.Point('.BAZ_COORD_ORIGINE_IMAGE_OMBRE_MARQUEUR.'),
        new google.maps.Point('.BAZ_COORD_ARRIVEE_IMAGE_OMBRE_MARQUEUR.'));

        marker = new google.maps.Marker({
            position: results[0].geometry.location,
            map: map,
            icon: image,
            shadow: shadow,
            title: \'Votre emplacement\',
            draggable: true
        });
        infowindow.open(map,marker);
        google.maps.event.addListener(marker, \'click\', function() {
            infowindow.open(map,marker);
        });
        google.maps.event.addListener(marker, "dragend", function () {
            var lat = document.getElementById("latitude");lat.value = marker.getPosition().lat();
            var lon = document.getElementById("longitude");lon.value = marker.getPosition().lng();
            map.setCenter(marker.getPosition());
        });
} else {
    alert("Pas de resultats pour cette adresse: " + address);
}
} else {
    alert("Pas de resultats pour la raison suivante: " + status + ", rechargez la page.");
}
});
}
};';
if ( defined('BAZ_JS_INIT_MAP') && BAZ_JS_INIT_MAP != '' && file_exists(BAZ_JS_INIT_MAP) ) {
    $handle = fopen(BAZ_JS_INIT_MAP, "r");
    $scriptgoogle .= fread($handle, filesize(BAZ_JS_INIT_MAP));
    fclose($handle);
    $scriptgoogle .= 'var poly = createPolygon( Coords, "#002F0F");
    poly.setMap(map);

    ';
};
$GLOBALS['wiki']->AddJavascriptFile('http://maps.google.com/maps/api/js?v=3&amp;sensor=false');
$GLOBALS['wiki']->AddJavascriptFile('http://www.google.com/jsapi');
$GLOBALS['wiki']->AddJavascript($scriptgoogle);
    $deflat = ''; $deflon = '';
    if (isset($valeurs_fiche['carte_google'])) {
        $tab = explode('|', $valeurs_fiche['carte_google']);
        if (count($tab)>1) {
            $deflat = ' value="'.$tab[0].'"';
            $deflon = ' value="'.$tab[1].'"';
        }
    }
    $required = (($obligatoire == 1) ? ' required="required"' : '' );
    $symbole_obligatoire = ($obligatoire == 1) ? '<span class="symbole_obligatoire">*&nbsp;</span>' : '';

    $formtemplate->addElement('html', 
        $symbole_obligatoire.'
        <input class="btn btn-primary btn_adresse" onclick="showAddress();" name="chercher_sur_carte" value="'._t('VERIFIER_MON_ADRESSE').'" type="button" />
        <div class="form-inline pull-right">'."\n".
            'Lat : <input type="text" name="'.$lat.'" class="input-mini" id="latitude"'.$deflat.$required.' />'."\n".
            'Lon : <input type="text" name="'.$lon.'" class="input-mini" id="longitude"'.$deflon.$required.' />'."\n".
        '</div>'."\n".
        '<div id="map" style="clear:right; margin-top:8px; width: '.BAZ_GOOGLE_IMAGE_LARGEUR.'; height: '.BAZ_GOOGLE_IMAGE_HAUTEUR.';"></div>');

    } elseif ($mode == 'requete') {
        return array('carte_google' => $valeurs_fiche[$lat].'|'.$valeurs_fiche[$lon]);
    } elseif ($mode == 'recherche') {

    } elseif ($mode == 'html') {

    }

}

/** listefiche() - Ajoute un element de type liste deroulante correspondant a un autre type de fiche au formulaire
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour l'element liste
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par defaut
 * @return   void
 */
function listefiche(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    if ($mode=='saisie' || ($mode == 'formulaire_recherche' && $tableau_template[9]==1) ) {
        $bulledaide = '';
        if ($mode=='saisie' && isset($tableau_template[10]) && $tableau_template[10]!='') {
            $bulledaide = ' &nbsp;&nbsp;<img class="tooltip_aide" title="'.htmlentities($tableau_template[10], ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'" src="tools/bazar/presentation/images/aide.png" width="16" height="16" alt="image aide" />';
        }

        $select_html = '<div class="control-group form-group">'."\n".'<div class="control-label col-xs-3">'."\n";
        if ($mode=='saisie' && isset($tableau_template[8]) && $tableau_template[8]==1) {
            $select_html .= '<span class="symbole_obligatoire">*&nbsp;</span>'."\n";
        }
        $select_html .= $tableau_template[2].$bulledaide.' : </div>'."\n".'<div class="controls col-xs-8">'."\n".'<select';

        $select_attributes = '';

        if ($mode=='saisie' && $tableau_template[4] != '' && $tableau_template[4] > 1) {
            $select_attributes .= ' multiple="multiple" size="'.$tableau_template[4].'"';
            $selectnametab = '[]';
        } else {
            $selectnametab = '';
        }

        $select_attributes .= ' class="form-control" id="'.$tableau_template[0].$tableau_template[1].$tableau_template[6].'" name="'.$tableau_template[0].$tableau_template[1].$tableau_template[6].$selectnametab.'"';


        if ($mode=='saisie' && isset($tableau_template[8]) && $tableau_template[8]==1) {
            $select_attributes .= ' required="required"';
        }
        $select_html .= $select_attributes.'>'."\n";

        if (isset($valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]) && $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]!='') {
            $def =	$valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]];
        } elseif (isset($_REQUEST[$tableau_template[0].$tableau_template[1].$tableau_template[6]]) && $_REQUEST[$tableau_template[0].$tableau_template[1].$tableau_template[6]]!='') {
            $def = $_REQUEST[$tableau_template[0].$tableau_template[1].$tableau_template[6]];
        } 
        else {
            $def = $tableau_template[5];
        }

        /*$valliste = baz_valeurs_liste($tableau_template[1]);*/
        if ($def=='' && ($tableau_template[4] == '' || $tableau_template[4] <= 1 ) || $def==0) {
            $select_html .= '<option value="" selected="selected">'._t('BAZ_CHOISIR').'</option>'."\n";
        }
        $val_type = baz_valeurs_type_de_fiche($tableau_template[1]);
        $tabquery = array();
        if (!empty($tableau_template[12])) {
            $tableau = array();
            $tab = explode('|', $tableau_template[12]); //découpe la requete autour des |
            foreach ($tab as $req) {
                $tabdecoup = explode('=', $req, 2);
                $tableau[$tabdecoup[0]] = trim($tabdecoup[1]);
            }
            $tabquery = array_merge($tabquery, $tableau);
        } else {
            $tabquery = '';
        }
        $tab_result = baz_requete_recherche_fiches($tabquery, 'alphabetique', $tableau_template[1], $val_type["bn_type_fiche"], 1, '', '', false, (!empty($tableau_template[13])) ? $tableau_template[13] : ''  );
        $select = '';
        foreach ($tab_result as $fiche) {
            $valeurs_fiche_liste = json_decode($fiche["body"], true);
            if (TEMPLATES_DEFAULT_CHARSET != 'UTF-8') $valeurs_fiche_liste = array_map('utf8_decode', $valeurs_fiche_liste);
            $select[$valeurs_fiche_liste['id_fiche']] = $valeurs_fiche_liste['bf_titre'] ;
        }
        if (is_array($select)) {
            foreach ($select as $key => $label) {
                $select_html .= '<option value="'.$key.'"';
                if ($def != '' && strstr($key, $def)) $select_html .= ' selected="selected"';
                $select_html .= '>'.$label.'</option>'."\n";
            }

        }

        $select_html .= "</select>\n</div>\n</div>\n";

        $formtemplate->addElement('html', $select_html) ;
    } elseif ($mode == 'requete') {
        if (isset($valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]) && ($valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]!=0)) {
            return array($tableau_template[0].$tableau_template[1].$tableau_template[6] => $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]);
        }
    } elseif ($mode == 'html') {
        $html = '';
        if (isset($valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]) && $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]!='') {

            if ($tableau_template[3] == 'fiche') {
                $html = baz_voir_fiche(0, $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]);
            } else {
                $html = '<div class="BAZ_rubrique" data-id="'.$tableau_template[0].$tableau_template[1].$tableau_template[6].'">'."\n".
                        '<span class="BAZ_label">'.$tableau_template[2].'&nbsp;:</span>'."\n";
                $html .= '<span class="BAZ_texte">';
                $val_fiche = baz_valeurs_fiche($valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]);
                $html .= '<a href="'.str_replace('&', '&amp;', $GLOBALS['wiki']->href('', $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]])).'" class="voir_fiche ouvrir_overlay" title="Voir la fiche '.
                        $val_fiche['bf_titre'].'" rel="#overlay-link">'.
                        $val_fiche['bf_titre'].'</a></span>'."\n".
                        '</div> <!-- /.BAZ_rubrique -->'."\n";

            }
        }

        return $html;
    }
} //fin listefiche()


/** checkboxfiche() - permet d'aller saisir et modifier un autre type de fiche
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour le texte HTML
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @param    mixed	Tableau des valeurs par défauts (pour modification)
 *
 * @return   void
 */
function checkboxfiche(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    //on teste la presence de filtres pour les valeurs
    $tabquery = array();
    if (isset($_GET["query"])) {
        $tableau = array();
        $tab = explode('|', $_GET["query"]); //découpe la requete autour des |
        foreach ($tab as $req) {
            $tabdecoup = explode('=', $req, 2);
            $tableau[$tabdecoup[0]] = trim($tabdecoup[1]);
        }
        $tabquery = array_merge($tabquery, $tableau);
    }
    

    if ($mode=='saisie' || ($mode == 'formulaire_recherche' && $tableau_template[9]==1) ) {
        $bulledaide = '';
        if ($mode=='saisie' && isset($tableau_template[10]) && $tableau_template[10]!='') {
            $bulledaide = ' &nbsp;&nbsp;<img class="tooltip_aide" title="'.htmlentities($tableau_template[10], ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'" src="tools/bazar/presentation/images/aide.png" width="16" height="16" alt="image aide" />';
        }

        $checkbox_html = '<div class="control-group form-group">'."\n".'<div class="control-label col-xs-3">'."\n";
        if ($mode=='saisie' && isset($tableau_template[8]) && $tableau_template[8]==1) {
            $checkbox_html .= '<span class="symbole_obligatoire">*&nbsp;</span>'."\n";
        }
        $checkbox_html .= $tableau_template[2].$bulledaide.' : </div>'."\n".'<div class="controls col-xs-8"';
        if ($mode=='saisie' && isset($tableau_template[8]) && $tableau_template[8]==1) {
            $checkbox_html .= ' required="required"';
        }
        $checkbox_html .= '>'."\n";

        

        if (isset($valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]) && $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]!='') {
            $def = explode( ',', $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]);
        } elseif (isset($_REQUEST[$tableau_template[0].$tableau_template[1].$tableau_template[6]]) && $_REQUEST[$tableau_template[0].$tableau_template[1].$tableau_template[6]]!='') {
            $def = explode( ',', $_REQUEST[$tableau_template[0].$tableau_template[1].$tableau_template[6]]);
        } 
        else {
            $def = explode( ',', $tableau_template[5]);
        }

        $val_type = baz_valeurs_type_de_fiche($tableau_template[1]);

        //on recupere les parameres pour une requete specifique
        if (isset($_GET['query'])) {
            $query = $tableau_template[12];
            if (!empty($query)) $query .= '|'.$_GET['query'];
            else $query = $_GET['query'];
        }
        else $query = $tableau_template[12];
        if (!empty($query)) {
            $tabquery = array();
            $tableau = array();
            $tab = explode('|', $query); //découpe la requete autour des |
            foreach ($tab as $req) {
                $tabdecoup = explode('=', $req, 2);
                $tableau[$tabdecoup[0]] = trim($tabdecoup[1]);
            }
            $tabquery = array_merge($tabquery, $tableau);
        } else {
            $tabquery = '';
        }
        $tab_result = baz_requete_recherche_fiches($tabquery, 'alphabetique', $tableau_template[1], $val_type["bn_type_fiche"], 1, '', '', true, (!empty($tableau_template[13])) ? $tableau_template[13] : ''  );
        $checkboxtab = '';
        foreach ($tab_result as $fiche) {
            $valeurs_fiche_liste = json_decode($fiche["body"], true);
            if (TEMPLATES_DEFAULT_CHARSET != 'UTF-8') $valeurs_fiche_liste = array_map('utf8_decode', $valeurs_fiche_liste);
            $checkboxtab[$valeurs_fiche_liste['id_fiche']] = $valeurs_fiche_liste['bf_titre'] ;
        }
        if (is_array($checkboxtab)) {
            $checkbox_html .= '<input type="text" class="pull-left filter-entries" value="" placeholder="Filtrer.."><label class="pull-right"><input type="checkbox" class="selectall" /> '._t('BAZAR_CHECKALL').'</label>'."\n".'<div class="clearfix"></div>'."\n".'<ul class="list-bazar-entries list-unstyled">';
            foreach ($checkboxtab as $key => $label) {
                $checkbox_html .= '<div class="yeswiki-checkbox checkbox">
<input type="checkbox" id="ckbx_'.$key.'" value="1" name="'.$tableau_template[0].$tableau_template[1].$tableau_template[6].'['.$key.']"';
if ($def != '' && in_array($key, $def)) $checkbox_html .= ' checked="checked"';
$checkbox_html .= ' class="element_checkbox"><label for="ckbx_'.$key.'">'.$label.'</label>
</div>'."\n";
            }
            $checkbox_html .= '</ul>'."\n";
        }

        $checkbox_html .= "</div>\n</div>\n";

        // javascript additions
        $GLOBALS['wiki']->AddJavascriptFile('tools/bazar/libs/vendor/jquery.fastLiveFilter.js');
        $script = "$(function() {
            $('.filter-entries').each(function() { $(this).fastLiveFilter($(this).siblings('.list-bazar-entries')); });
        });";
        $GLOBALS['wiki']->AddJavascript($script);
        $formtemplate->addElement('html', $checkbox_html) ;

    } 
    elseif ($mode == 'requete') {
        if (isset($valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]) && ($valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]!=0)) {
            return array($tableau_template[0].$tableau_template[1].$tableau_template[6] => $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]);
        }
    } 
    elseif ($mode == 'html') {
        $html = '';
        if (isset($valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]) && $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]!='') {
            $html .= '<div class="BAZ_rubrique" data-id="'.$tableau_template[0].$tableau_template[1].$tableau_template[6].'">'."\n".
                    '<span class="BAZ_label">'.$tableau_template[2].'&nbsp;:</span>'."\n";
            $html .= '<span class="BAZ_texte">'."\n";
            $tab_fiche = explode(',', $valeurs_fiche[$tableau_template[0].$tableau_template[1].$tableau_template[6]]);

            $first = true;
            foreach ($tab_fiche as $fiche) {
                if ($tableau_template[3] == 'fiche') {
                    $html .= baz_voir_fiche(0, $fiche);
                } else {
                    $val_fiche = baz_valeurs_fiche($fiche);

                    // il y a des filtres à faire sur les fiches
                    if (count($tabquery)>0) {
                        $match=false;
                        foreach ($tabquery as $key => $value) {
                            if (strstr($val_fiche[$key], $value) ) $match=true;
                            else { $match=false; break;}
                        }
                    }
                    if (!isset($match) || $match == true) {
                        if (!$first) {$html .= ', ';}
                        else {$first = false;}
                        $html .= '<a href="'.str_replace('&', '&amp;', $GLOBALS['wiki']->href('', $fiche)).'" class="modalbox" title="Voir la fiche '.$val_fiche['bf_titre'].'">'.$val_fiche['bf_titre'].'</a>'."\n";
                    }
                    
                }
            }
            $html .= '</span>'."\n".'</div> <!-- /.BAZ_rubrique -->'."\n";
        }

        return $html;
    }
}

/** listefiches() - permet d'aller saisir et modifier un autre type de fiche
 *
 * @param    mixed   L'objet QuickForm du formulaire
 * @param    mixed   Le tableau des valeurs des différentes option pour le texte HTML
 * @param    string  Type d'action pour le formulaire : saisie, modification, vue,... saisie par défaut
 * @param    mixed	Tableau des valeurs par défauts (pour modification)
 *
 * @return   void
 */
function listefiches(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    if (!isset($tableau_template[1])) {
        return $GLOBALS['wiki']->Format('//Erreur sur listefiches : pas d\'identifiant de type de fiche passé...//');
    }
    if (isset($tableau_template[2]) && $tableau_template[2] != '' ) {
        $query = $tableau_template[2].'|listefiche'.$valeurs_fiche['id_typeannonce'].'='.$valeurs_fiche['id_fiche'];
    } elseif (isset($valeurs_fiche) && $valeurs_fiche != '') {
        $query = 'listefiche'.$valeurs_fiche['id_typeannonce'].'='.$valeurs_fiche['id_fiche'];
    }
    if (isset($tableau_template[3])) {
        $ordre = $tableau_template[3];
    } else {
        $ordre = 'alphabetique';
    }
    if (isset($tableau_template[5])) {
        $template = $tableau_template[5];
    } else {
        $template = BAZ_TEMPLATE_LISTE_DEFAUT;
    }
    if (isset($valeurs_fiche['id_fiche']) && $mode == 'saisie' ) {
        $actionbazarliste = '{{bazarliste idtypeannonce="'.$tableau_template[1].'" query="'.$query.'" ordre="'.$ordre.'" template="'.$template.'"}}';
        $html = $GLOBALS['wiki']->Format($actionbazarliste);
        //ajout lien nouvelle saisie
        $url_checkboxfiche = clone($GLOBALS['_BAZAR_']['url']);
        $url_checkboxfiche->removeQueryString('id_fiche');
        $url_checkboxfiche->addQueryString('vue', BAZ_VOIR_SAISIR);
        $url_checkboxfiche->addQueryString('action', BAZ_ACTION_NOUVEAU);
        $url_checkboxfiche->addQueryString('wiki', $_GET['wiki'].'/iframe');
        $url_checkboxfiche->addQueryString('id_typeannonce', $tableau_template[1]);
        $url_checkboxfiche->addQueryString('ce_fiche_liee', $_GET['id_fiche']);
        $html .= '<a class="ajout_fiche ouvrir_overlay" href="'.str_replace('&', '&amp;', $url_checkboxfiche->getUrl()).'" rel="#overlay-link" title="'.htmlentities($tableau_template[4], ENT_QUOTES, TEMPLATES_DEFAULT_CHARSET).'">'.$tableau_template[4].'</a>'."\n";
        $formtemplate->addElement('html', $html);
    } elseif ($mode == 'requete') {
    } elseif ($mode == 'formulaire_recherche') {
        if ($tableau_template[9]==1) {
            $requete =  'SELECT * FROM '.BAZ_PREFIXE.'liste_valeurs WHERE blv_ce_liste='.$tableau_template[1].
                ' AND blv_ce_i18n like "'.$GLOBALS['_BAZAR_']['langue'].'%" ORDER BY blv_label';
            $resultat = $GLOBALS['wiki'] -> query($requete) ;
            
            require_once 'vendor/HTML/QuickForm/checkbox.php';
            $i=0;
            $optioncheckbox = array('class' => 'element_checkbox');

            while ($ligne = $resultat->fetchRow()) {
                if ($i==0) $tab_chkbox=$tableau_template[2] ; else $tab_chkbox='&nbsp;';
                $checkbox[$i]= & HTML_QuickForm::createElement($tableau_template[0], $ligne[1], $tab_chkbox, $ligne[2], $optioncheckbox) ;
                $i++;
            }

            $squelette_checkbox =& $formtemplate->defaultRenderer();
            $squelette_checkbox->setElementTemplate( '<fieldset class="bazar_fieldset">'."\n".'<legend>{label}'.
                    '<!-- BEGIN required --><span class="symbole_obligatoire">&nbsp;*</span><!-- END required -->'."\n".
                    '</legend>'."\n".'{element}'."\n".'</fieldset> '."\n"."\n", $tableau_template[0].$tableau_template[1].$tableau_template[6]);
            $squelette_checkbox->setGroupElementTemplate( "\n".'<div class="checkbox">'."\n".'{element}'."\n".'</div>'."\n", $tableau_template[0].$tableau_template[1].$tableau_template[6]);
            $formtemplate->addGroup($checkbox, $tableau_template[0].$tableau_template[1].$tableau_template[6], $tableau_template[2].$bulledaide, "\n");
        }
    } elseif ($mode == 'html') {
        $actionbazarliste = '{{bazarliste idtypeannonce="'.$tableau_template[1].'" query="'.$query.'" ordre="'.$ordre.'" template="'.$template.'"}}';
        $html = $GLOBALS['wiki']->Format($actionbazarliste);

        return $html;
    }
}

function bookmarklet(&$formtemplate, $tableau_template, $mode, $valeurs_fiche)
{
    if ($mode == 'html') {
        if ($GLOBALS['wiki']->GetMethod()=='iframe') {
            return '<a class="btn btn-danger pull-right" href="javascript:window.close();"><i class="icon-remove icon-white"></i>&nbsp;Fermer cette fen&ecirc;tre</a>';
        }
    } elseif ($mode == 'saisie') {
        if ($GLOBALS['wiki']->GetMethod()!='iframe') {
            $url_bookmarklet = clone($GLOBALS['_BAZAR_']['url']);
            $url_bookmarklet->removeQueryString('id_fiche');
            $url_bookmarklet->addQueryString('vue', BAZ_VOIR_SAISIR);
            $url_bookmarklet->addQueryString('action', BAZ_ACTION_NOUVEAU);
            $url_bookmarklet->addQueryString('wiki', $GLOBALS['_BAZAR_']['pagewiki'].'/iframe');
            $url_bookmarklet->addQueryString('id_typeannonce', $GLOBALS['_BAZAR_']['id_typeannonce']);
            $htmlbookmarklet = "<div class=\"BAZ_info\">
                <a href=\"javascript:var wleft = (screen.width-700)/2; var wtop=(screen.height-530)/2 ;window.open('".str_replace('&', '&amp;', $url_bookmarklet->getUrl())."&amp;bf_titre='+escape(document.title)+'&amp;url='+encodeURIComponent(location.href)+'&amp;description='+escape(document.getSelection()), '".$tableau_template[1]."', 'height=530,width=700,left='+wleft+',top='+wtop+',toolbar=no,location=no,directories=no,status=no,scrollbars=yes,resizable=yes,menubar=no');void 0;\">".$tableau_template[1]."</a> << ".$tableau_template[2]."</div>";
            $formtemplate->addElement('html', $htmlbookmarklet);
        }
    }
}

// Code provenant de spip :
function extension_autorisee($ext)
{
    $tables_images = array(
            // Images reconnues par PHP
            'jpg' => 'JPEG',
            'png' => 'PNG',
            'gif' =>'GIF',
            'jpeg' =>'JPEG',

            // Autres images (peuvent utiliser le tag <img>)
            'bmp' => 'BMP',
            'tif' => 'TIFF'
            );

    $tables_sequences = array(
            'aiff' => 'AIFF',
            'anx' => 'Annodex',
            'axa' => 'Annodex Audio',
            'axv' => 'Annodex Video',
            'asf' => 'Windows Media',
            'avi' => 'AVI',
            'flac' => 'Free Lossless Audio Codec',
            'flv' => 'Flash Video',
            'mid' => 'Midi',
            'mng' => 'MNG',
            'mka' => 'Matroska Audio',
            'mkv' => 'Matroska Video',
            'mov' => 'QuickTime',
            'mp3' => 'MP3',
            'mp4' => 'MPEG4',
            'mpg' => 'MPEG',
            'oga' => 'Ogg Audio',
            'ogg' => 'Ogg Vorbis',
            'ogv' => 'Ogg Video',
            'ogx' => 'Ogg Multiplex',
            'qt' => 'QuickTime',
            'ra' => 'RealAudio',
            'ram' => 'RealAudio',
            'rm' => 'RealAudio',
            'spx' => 'Ogg Speex',
            'svg' => 'Scalable Vector Graphics',
            'swf' => 'Flash',
            'wav' => 'WAV',
            'wmv' => 'Windows Media',
            '3gp' => '3rd Generation Partnership Project'
                );

    $tables_documents = array(
            'abw' => 'Abiword',
            'ai' => 'Adobe Illustrator',
            'bz2' => 'BZip',
            'bin' => 'Binary Data',
            'blend' => 'Blender',
            'c' => 'C source',
            'cls' => 'LaTeX Class',
            'css' => 'Cascading Style Sheet',
            'csv' => 'Comma Separated Values',
            'deb' => 'Debian',
            'doc' => 'Word',
            'docx' => 'Word',
            'djvu' => 'DjVu',
            'dvi' => 'LaTeX DVI',
            'eps' => 'PostScript',
            'gz' => 'GZ',
            'h' => 'C header',
            'html' => 'HTML',
            'kml' => 'Keyhole Markup Language',
            'kmz' => 'Google Earth Placemark File',
            'pas' => 'Pascal',
            'pdf' => 'PDF',
            'pgn' => 'Portable Game Notation',
            'ppt' => 'PowerPoint',
            'pptx' => 'PowerPoint',
            'ps' => 'PostScript',
            'psd' => 'Photoshop',
            'rpm' => 'RedHat/Mandrake/SuSE',
            'rtf' => 'RTF',
            'sdd' => 'StarOffice',
            'sdw' => 'StarOffice',
            'sit' => 'Stuffit',
            'sty' => 'LaTeX Style Sheet',
            'sxc' => 'OpenOffice.org Calc',
            'sxi' => 'OpenOffice.org Impress',
            'sxw' => 'OpenOffice.org',
            'tex' => 'LaTeX',
            'tgz' => 'TGZ',
            'torrent' => 'BitTorrent',
            'ttf' => 'TTF Font',
            'txt' => 'texte',
            'xcf' => 'GIMP multi-layer',
            'xspf' => 'XSPF',
            'xls' => 'Excel',
            'xlsx' => 'Excel',
            'xml' => 'XML',
            'zip' => 'Zip',

            // open document format
            'odt' => 'opendocument text',
            'ods' => 'opendocument spreadsheet',
            'odp' => 'opendocument presentation',
            'odg' => 'opendocument graphics',
            'odc' => 'opendocument chart',
            'odf' => 'opendocument formula',
            'odb' => 'opendocument database',
            'odi' => 'opendocument image',
            'odm' => 'opendocument text-master',
            'ott' => 'opendocument text-template',
            'ots' => 'opendocument spreadsheet-template',
            'otp' => 'opendocument presentation-template',
            'otg' => 'opendocument graphics-template',

            );

    if (array_key_exists($ext,$tables_images)) {
        return true;
    } else {

        if (array_key_exists($ext,$tables_sequences)) {
            return true;
        } else {
            if (array_key_exists($ext,$tables_documents)) {
                return true;
            } else {
                return false;
            }
        }

    }

}
function obtenir_extension($filename)
{
    $pos = strrpos($filename, '.');
    if ($pos === false) { // dot is not found in the filename

        return ''; // no extension
    } else {
        $extension = substr($filename, $pos+1);

        return  $extension;
    }
}
