<?php
/*
backgroundimage.php

2014 Florian Schmitt 

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/


// Get the action's parameters :

// image's filename
$file = $this->GetParameter('file');
if (empty($file)) {
		echo '<div class="alert alert-danger"><strong>'._t('ATTACH_ACTION_BACKGROUNDIMAGE').'</strong> : '._t('ATTACH_PARAM_FILE_NOT_FOUND').'.</div>'."\n";
		return;
}

// test of image extension 
$supported_image_extensions = array('gif', 'jpg', 'jpeg', 'png');
$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION)); // Using strtolower to overcome case sensitive
if (!in_array($ext, $supported_image_extensions)) {
   	echo '<div class="alert alert-danger"><strong>'._t('ATTACH_ACTION_BACKGROUNDIMAGE').'</strong> : '._t('ATTACH_PARAM_FILE_MUST_BE_IMAGE').'.</div>'."\n";
	return;
}

// image size
$height = $this->GetParameter('height');
$width = $this->GetParameter('width');
if (empty($width)) $width = 1920;

if (!class_exists('attach')){
	include('tools/attach/actions/attach.class.php');
}
$att = new attach($this);

//recuperation des parametres necessaire
$att->file = $file;
$att->desc = 'background image '.$file;
$att->height = $height;
$att->width = $width;
$fullFilename = $att->GetFullFilename();
//test d'existance du fichier
if((!file_exists($fullFilename))||($fullFilename=='')){
    $att->showFileNotExits();
    //return;
}

echo '<div class="background-image" style="'.(!empty($height) ? 'height:'.$height.'px; ' : '').'background-image:url('.$fullFilename.');">'."\n";
$nocontainer = $this->GetParameter('nocontainer');
if (empty($nocontainer)) echo '<div class="container">'."\n"; else echo '<div>';