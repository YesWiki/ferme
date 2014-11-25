<?php
/**
* valeur : permet d'extraire le contenu d'une valeur de fiche bazar à partir d'une url
*
*
* @package  Bazar
* @author   Florian SCHMITT <florian@outils-reseaux.org>
*
*/
// +------------------------------------------------------------------------------------------------------+
// |                                            ENTETE du PROGRAMME                                           |
// +------------------------------------------------------------------------------------------------------+
// test de sécurité pour vérifier si on passe par wiki
if (!defined("WIKINI_VERSION")) {
        die ("acc&egrave;s direct interdit");
}

// url de la fiche bazar
$url = $this->GetParameter("url"); 
if (empty($url) && isset($this->config['source_url']) && !empty($this->config['source_url'])) $url = $this->config['source_url'];
if (!empty($url)) {
    // parameter of this action
    $champ = $this->GetParameter("champ");
    $image = $this->GetParameter('image');
    $texte = $this->GetParameter('texte');
    $defaut = $this->GetParameter('defaut');
    $target = $this->GetParameter('target');
    if (!empty($target)) $target = 'target="'.$target.'" ';


    if (!empty($champ)) {
        // on charge dans une variable globale pour le cas ou l'action est appelée plusieurs fois
        if (!isset($GLOBALS['externalpage'][$url])) $GLOBALS['externalpage'][$url] = @file_get_contents($url.'/html');
        if (!$GLOBALS['externalpage'][$url] === FALSE) {

            // le titre est un cas particulier
            if ($champ == 'bf_titre') {
                $regexp = '/<h1 class="BAZ_fiche_titre">(.*)<\/h1>/Uis';
            }
            // l'id est un cas particulier
            else if ($champ == 'id_fiche') {
                $urlparsed = parse_url($url);
                echo preg_replace('/(.*?)wiki=(.*?)/Ui', '$2', $urlparsed["query"]);
                return;
            }
            // cas des images
            else if (!empty($image) && ($image == 'lien' || $image == '1')) {
                $regexp = '/<a.*href="(.*'.$champ.'.*)".*>\s*<img.*<\/a>/Uis';
            }
            else {
                $regexp = '/<div.*data-id="'.$champ.'".*>\s*<span class="BAZ_label.*">.*<\/span>\s*<span class="BAZ_texte">\s*(.*)\s*<\/span>\s*<\/div> <!-- \/.BAZ_rubrique -->/Uis';
                //echo '<br><br>'.htmlspecialchars($regexp);
            }
            preg_match_all($regexp, $GLOBALS['externalpage'][$url], $matches);
            
            if (isset($matches[1]) && count($matches[1])>0) {
                if (!empty($texte) && $texte!="lien") {
                    echo preg_replace('/<a.*href="(.*)".*>.*<\/a>/Ui', '<a '.$target.'href="$1">'.trim($texte).'</a>', trim(array_shift($matches[1])));
                } 
                else if (!empty($texte) && $texte=="lien") {
                    echo preg_replace('/<a.*href="(.*)".*>.*<\/a>/Ui', '$1', trim(array_shift($matches[1])));
                }
                else if ($image == '1') {
                    echo '<img class="img-responsive" src="'.trim(array_shift($matches[1])).'" alt="image '.$champ.'">';
                }
                else {
                    echo trim(array_shift($matches[1]));
                }
            }
            else {
                if (isset($defaut) && !empty($defaut)) echo $defaut;
            }
        }
        else {
           echo '<div class="alert alert-danger alert-error"><strong>'._t('BAZAR_ACTION_VALEUR').'</strong> : '._t('BAZAR_URL_ERROR').' : '.$url.'.</div>'."\n"; 
        }
   }
   else { 
        echo '<div class="alert alert-danger alert-error"><strong>'._t('BAZAR_ACTION_VALEUR').'</strong> : '._t('BAZAR_PARAM_CHAMP_REQUIRED').'.</div>'."\n";
    }
}
else {
    echo '<div class="alert alert-danger alert-error"><strong>'._t('BAZAR_ACTION_VALEUR').'</strong> : '._t('BAZAR_PARAM_URL_REQUIRED').'.</div>'."\n";
}