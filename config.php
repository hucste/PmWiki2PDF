<?php
/*
 * PDF Output destination
 * 	I (to set a pdf name; or false), D,
 * 	F (to write on server), S (to obtain content pdf; or true)
 *
 * */
$cfg['output']['dest'] = 'I';

/*
 * PDF Displaying
 * <http://wiki.spipu.net/doku.php?id=html2pdf:en:v4:display>
 *
 * */

//Layout: SinglePage, OneColumn, TwoColumnLeft, TwoColumnRight, TwoPageLeft or TwoPageRight
$cfg['display']['layout'] = 'SinglePage';

// Mode: UseNone, UseOutlines, UseThumbs, FullScreen, UseOC or UseAttachments
$cfg['display']['mode'] = 'UseNone';

// Zoom: fullpage, fullwidth, real or default
$cfg['display']['zoom'] = 'fullpage';

/*
 * PDF Definitions
 * <http://wiki.spipu.net/doku.php?id=html2pdf:en:v4:Accueil>
 *
 * */

// Sens: landscape or portrait orientation - L or P
$cfg['page']['orientation'] = 'P';

// Format: A4, A5, LETTER, width x height (100×200, ...), ...
$cfg['page']['format'] = 'A4';

// Marge
$cfg['marge']['page']['top'] = 0;
$cfg['marge']['page']['right'] = 0;
$cfg['marge']['page']['bottom'] = 0;
$cfg['marge']['page']['left'] = 0;
/* *** */
$cfg['marge']['back']['top'] = 14;
$cfg['marge']['back']['right'] = 10;
$cfg['marge']['back']['bottom'] = 14;
$cfg['marge']['back']['left'] = 10;

/*
 * Managing
 *
 * */

//$cfg['$page']['backcolor'] = ''; // hexadecimal code HTML!
$cfg['page']['header']['include'] = 1;
$cfg['page']['footer']['include'] = 1;
$cfg['page']['style']['include'] = 1;
$cfg['page']['header']['name'] = 'page_header.html';
$cfg['page']['footer']['name'] = 'page_footer.html';
$cfg['page']['style']['name'] = 'style_css.html';

$cfg['page']['backimg']['url'] = ''; // define url: relative url
$cfg['page']['backimg']['x'] = 'left';	// left, center, right or number (mm, px, pt, % )
$cfg['page']['backimg']['y'] = 'top'; 	// top, middle, bottom or number (mm, px, pt, % )
$cfg['page']['backimg']['w'] = '100%';	// number (mm, px, pt, % )

// PDF compression
$cfg['zip'] = 1;
// PDF Lang
$cfg['lang'] = 'fr';
// PDF set Default font
$cfg['default']['font'] = 'Arial';
/*
// PDF encoding
$cfg['encoding'] = 'UTF-8';
// PDF Unicode
$cfg['unicode'] = false;
*/

// PDF Protection ( 0 : no ; 1 : yes )
$cfg['protect']['set'] = 1;	// to active protection
$cfg['protect']['annot'] = 0;
$cfg['protect']['copy'] = 1;
$cfg['protect']['modify'] = 1;
$cfg['protect']['print'] = 1;
$cfg['protect']['owner_psswd'] = '';	// string to define owner's password

/*
 * Manage to display rights
 *
 * */
// To display rights in pdf ( 0 : no ; 1 : yes )
$cfg['rights']['display'] = 1;
// Choose your licence:
/*
; 'GPL', 'FDL', 'LGPL', 'LAL'
; 'CC-BY', 'CC-BY-NC', 'CC-BY-ND', 'CC-BY-NC-ND', 'CC-BY-NC-SA', 'CC-BY-SA',
; default : CPI (alls rights author !)
;
* */
$cfg['rights']['licence'] = 'CPI';
// Choose language page: default 'en'.
$cfg['rights']['lang'] = 'en';
// Choose encoding: default 'ISO-8859-15'
// 	ISO-8859-1, ISO-8859-15, UTF-8...
$cfg['rights']['charset'] = 'ISO-8859-15';

/*
 * transform code HTML - because HTML2PDF not support DL
 * */
// change dl by p, or li: 'dl2p', 'dl2li' <= this second choice is bugged!
$cfg['transformer']['dl'] = 'dl2p';

// Tags HTML Unsupported, separated by comma
$cfg['skip'] = ''
?>
