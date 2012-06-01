<?php
/*
* $Id: highlighter.class.php 797 2007-07-23 22:31:56Z lordfarquaad $
*
* Souligneur g�n�rique pour colorier la syntaxe de langage de programmation
*
* copyright Eric Feldstein 2004 mailto:garfield_fr@tiscali.fr
*
* Licence : la meme que wikini(voir le fichier LICENCE).
* Vous �tes libre d'utiliser et de modifier ce code � condition de laisser le copyright 
* d'origine. Vous pouvez  bien sur vous ajouter � la liste des auteurs.
*
* INSTALLATION : copier le fichier dans le repertoire "formatters" de WikiNi
* UTILISATION : importer la classe dans le script de coloration
* ATTRIBUTS DE LA CLASSE :
*   - isCaseSensitiv : booleen - indique si la syntaxe est sensible a la casse
*   - comment : array - tableau d'expressions regulieres definissant les commentaires multiligne
*				ex : array('({[^$][^}]*})',	//commentaires: { ... }
*						'(\(\*[^$](.*)\*\))'		//commentaires: (* ... *)
*					);
*	- commentLine : array tableau d'expressions regulieres definissant les commentaires monoligne
*				ex : array('(//.*\n)'); 		//commentaire //
*	- commentStyle : string - style CSS inline a utiliser pour la coloration(utilis� dans une
*			balise <SPAN style="..."></SPAN>)
*	- directive : array - tableau d'expression reguliere pour definir les directive de
*			compilation
*	- directiveStyle : string - style CSS inline a utiliser pour la coloration
*	- string : array - tableau d'expression reguliere pour definir les chaine de caracteres
*	- stringStyle : string - style CSS inline a utiliser pour la coloration
*	- number : array - tableau d'expression reguliere pour definir les nombres
*	- numberStyle : string - style CSS inline a utiliser pour la coloration
*	- keywords : array - tableau asociatif contenant des tableaux de liste de mots avec leur style. Ex :
*          $oHighlighter->keywords['Liste1']['words'] = array('liste1mot1','liste1mot2','liste1mot3');
*          $oHighlighter->keywords['Liste1']['style'] = 'color: red';
*          $oHighlighter->keywords['Liste2']['words'] = array('liste2mot1','liste2mot2');
*          $oHighlighter->keywords['Liste2']['style'] = 'color: yellow';
*    chaque tableau keywords['...'] DOIT posseder les 2 cl� 'words' et 'style'.
*	- symboles : array - tableau conteant la liste des symboles
*	- symbolesStyle : string - style CSS inline a utiliser pour la coloration
*	- identifier : array - tableau d'expression reguliere pour definir les identifiants
*	- identStyle : string - style CSS inline a utiliser pour la coloration
* METHODE PUBLIQUE DE LA CLASSE :
*	- Analyse($text) : $text string Chaine a analyser
*		renvoie le texte colori�.
*
* NOTES IMPORTANTES
*  - Les expressions reguliere doivent �tre entre parenth�se capturante pour etre utilis�
* dans une fonction de remplacement. Voir le fichier coloration_delphi.php pour un exemple
*  - Lorsque un style est defini � vide, l'expression reguliere n'est pas prise en compte dans
* l'analyse.
*  - L'option de recherche est msU : multiligne, le . peut �tre un \n et la recherche
* est 'not greedy' qui inverse la tendance � la gourmandise des expressions r�guli�res.
*/

class Highlighter{
	//sensibilite majuscule/minuscule
	var $isCaseSensitiv = false;
	//commentaires
	var $comment = array();	//commentaire multiligne
	var $commentLine = array();	//commentaire monoligne
	var $commentStyle = '';//'color: red';
	//directives de compilation
	var $directive = array();
	var $directiveStyle = '';//'color: green';
	//chaine de caracteres
	var $string = array();
	var $stringStyle = '';
	//nombre
	var $number = array();
	var $numberStyle = '';
	//mots cl�
	var $keywords = array();
	//s�parateurs
	var $symboles = array();
	var $symbolesStyle = '';
	//identifiant
	var $identifier = array();
	var $identStyle = '';
	//*******************************************************
	// Variable priv�es
	//*******************************************************
	var $_patOpt = 'msU';		//option de recherche
	var $_pattern = '';			//modele complet
	var $_commentPattern = '';	//modele des commentaires
	var $_directivePattern = '';//modele des directives
	var $_numberPattern = '';	//modele des nombres
	var $_stringPattern = '';	//modele des chaine de caracteres
	var $_keywordPattern = '';	//modele pour le mots cle
	var $_symbolesPattern = '';	//modele pour les symbole
	var $_separatorPattern = '';//modele pour les sparateurs
	var $_identPattern = '';	//modele pour les identifiants
	/********************************************************
	Methodes de la classe
	*********************************************************/
	/**
	* Renvoie le pattern pour les commentaires
	*/
	function _getCommentPattern(){
		$a = array_merge($this->commentLine,$this->comment);
		return implode('|',$a);
	}
	/**
	* Renvoie le pattern pour les directives de compilation
	*/
	function _getDirectivePattern(){
		return implode('|',$this->directive);
	}
	/**
	* Renvoie le pattern pour les chaine de caracteres
	*/
	function _getStringPattern(){
		return implode('|',$this->string);
	}
	/**
	* Renvoie le pattern pour les nombre
	*/
	function _getNumberPattern(){
		return implode('|',$this->number);
	}
	/**
	* Renvoie le pattern pour les mots cl�
	*/
	function _getKeywordPattern(){
      $aResult = array();
      foreach($this->keywords as $key=>$keyword){
         $aResult = array_merge($aResult, $keyword['words']);
         $this->keywords[$key]['pattern'] = '\b'.implode('\b|\b',$keyword['words']).'\b';
      }
		return '\b'.implode('\b|\b',$aResult).'\b';
	}
	/**
	* Renvoie le pattern pour les symboles
	*/
	function _getSymbolesPattern(){
		$a = array();
		foreach($this->symboles as $s){
			$a[] = preg_quote($s,'`');
		}
		return implode('|',$a);
	}
	/**
	* Renvoie le pattern pour les identifiants
	*/
	function _getIdentifierPattern(){
		return implode('|',$this->identifier);
	}
	/**
	* Liste des separateur d'apres la liste des symboles
	*/
	function _getSeparatorPattern(){
		$a = array_unique(preg_split('//', implode('',$this->symboles), -1, PREG_SPLIT_NO_EMPTY));
		$pattern = '['.preg_quote(implode('',$a),'`').'\s]+';
		return $pattern;
	}
	/**
	* Renvoie le modele a utiliser dans l'expression reguli�re
	*
	* @return string Modele de l'expression r�guli�re
	*/
	function _getPattern(){
		$this->_separatorPattern = $this->_getSeparatorPattern();
		$this->_symbolesPattern = $this->_getSymbolesPattern();
		$this->_commentPattern = $this->_getCommentPattern();
		$this->_directivePattern = $this->_getDirectivePattern();
		$this->_stringPattern = $this->_getStringPattern();
		$this->_numberPattern = $this->_getNumberPattern();
		$this->_keywordPattern = $this->_getKeywordPattern();
		$this->_identPattern = $this->_getIdentifierPattern();
		//construction du modele globale en fonction de l'existance d'un style(optimisation)
		if($this->commentStyle){ $a[] = $this->_commentPattern; }
		if($this->directiveStyle){ $a[] = $this->_directivePattern; }
		if($this->stringStyle){ $a[] = $this->_stringPattern; }
		if($this->numberStyle){ $a[] = $this->_numberPattern; }
      if(count($this->keywords)>0){ $a[] = $this->_keywordPattern; }
		if($this->symbolesStyle){ $a[] = $this->_symbolesPattern; }
		if($this->identStyle){ $a[] = $this->_identPattern; }
		$this->_pattern = implode('|',$a);
    	return $this->_pattern;
	}
	/**
	* Fonction de remplacement de chaque �lement avec leur style.
	*/
	function replacecallback($match){
		$text = $match[0];
		$pcreOpt = $this->_patOpt;
		$pcreOpt .= ($this->isCaseSensitiv)?'':'i';
		//commentaires
      if($this->commentStyle){
         if (preg_match('`'.$this->_commentPattern."`$pcreOpt",$text,$m)){
            return "<span style=\"$this->commentStyle\">".$match[0].'</span>';
         }
      }
		//directive de compilation
      if ($this->directiveStyle){
         if (preg_match('`'.$this->_directivePattern."`$pcreOpt",$text,$m)){
            return "<span style=\"$this->directiveStyle\">".$match[0].'</span>';
         }
      }
		//chaine de caracteres
      if ($this->stringStyle){
   		if (preg_match('`'.$this->_stringPattern."`$pcreOpt",$text,$m)){
   			return "<span style=\"$this->stringStyle\">".$match[0].'</span>';
   		}
      }
		//nombres
      if ($this->numberStyle){
   		if (preg_match('`'.$this->_numberPattern."`$pcreOpt",$text,$m)){
   			return "<span style=\"$this->numberStyle\">".$match[0].'</span>';
   		}
      }
		//mot cl�
      if (count($this->keywords)>0){
         foreach($this->keywords as $key=>$keywords){
            if ($keywords['style']){
               if(preg_match('`'.$keywords['pattern']."`$pcreOpt",$text,$m)){
                  return "<span style=\"".$keywords['style']."\">".$match[0].'</span>';
               }
            }
         }
      }
		//symboles
      if ($this->symbolesStyle){
   		if (preg_match('`'.$this->_symbolesPattern."`$pcreOpt",$text,$m)){
   			return "<span style=\"$this->symbolesStyle\">".$match[0].'</span>';
   		}
      }
		//identifiants
      if ($this->identStyle){
   		if (preg_match('`'.$this->_identPattern."`$pcreOpt",$text,$m)){
   			return "<span style=\"$this->identStyle\">".$match[0].'</span>';
   		}
      }
		return $match[0];
	}
	/**
	* renvois le code colori�
	*
	* @param $text string Texte a analyser
	* @return string texte colori�
	*/
	function Analyse($text){
		$pattern = '`'.$this->_getPattern()."`$this->_patOpt";
		if (!$this->isCaseSensitiv){
			$pattern .= 'i';
		}
		$text = preg_replace_callback($pattern,array($this,'replacecallback'),$text);
		return $text;
	}
}	//class Highlighter
?>