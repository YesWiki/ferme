<?php
/*
*/
if (!defined("WIKINI_VERSION"))
{
            die ("acc&egrave;s direct interdit");
}

// on remplace les liens vers les NomWikis n'existant pas
$plugin_output_new = replace_missingpage_links($plugin_output_new);

// on efface des �v�nements javascript issus de wikini
$plugin_output_new = str_replace('ondblclick="doubleClickEdit(event);"', '', $plugin_output_new );

// on efface aussi le message sur la non-modification d'une page, car contradictoire avec le changement de theme, et in�fficace pour l'exp�rience utilisateur
$plugin_output_new = str_replace('onload="alert(\'Cette page n\\\'a pas &eacute;t&eacute; enregistr&eacute;e car elle n\\\'a subi aucune modification.\');"', '', $plugin_output_new );

if (isset($GLOBALS['template-error']) && $GLOBALS['template-error'] != '') {
	// on affiche le message d'erreur des templates inexistants
	$plugin_output_new = str_replace('<div class="page" >', '<div class="page">'."\n".$GLOBALS['template-error'], $plugin_output_new );
	$GLOBALS['template-error'] = '';
}

?>
