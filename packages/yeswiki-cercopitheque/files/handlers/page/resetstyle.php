<?php

// Handler resetstyle.php version 0.3 du 05/07/2004
// pour WikiNi 0.4.1rc (=> � la version du 200403xx) et sup�rieurs
// Par Charles N�pote (c) 2004
// Licence GPL


// Fonctionnement
//
// Cet handler permet � l'utilisateur de revenir � la feuille de style par d�faut du site.
// Techniquement :


// Usage :
// http://example.org/PageTest/resetstyle


// A compl�ter (peut-�tre un jour) :
//
// -- d�tecter le fichier par d�faut via une variable de configuration
//

// V�rification de s�curit�
if (!defined("WIKINI_VERSION"))
{
	die ("acc&egrave;s direct interdit");
}

$this->SetPersistentCookie('sitestyle','wakka',1);
header("Location: ".$this->href());

?>