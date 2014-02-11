<?php
/**
  PmWiki module to generate a PDF file from the ?action=pdf page view.
  with the HTML2PDF PHP Class ( http://html2pdf.fr/ )
  Hacked by Stephane HUC for PmWiki !
  Copyright (C) 2011 Stephane HUC <devs@stephane-huc.net>
  This is GPL, only it seems odd to include a license in a program
  that has twice the size of the license.

  Modifications to work with PmWiki 2.2.x, 
  Copyright (c) 2005 by Patrick R. Michaud <pmichaud@pobox.com>
*/
if (!defined('PmWiki')) exit();
if($_SERVER['DOCUMENT_ROOT'] == 'pmwiki.dev.stephane-huc.net') error_reporting(E_ALL);

// to define file config name : config.php, config...
define('P2PDF_CFG_NAME', 'config.php');

/*** Don't touch below! ***/
define('P2PDF_OWNER', fileowner(dirname(__FILE__).'/pmwiki2pdf.php') );

$file = new SplFileInfo(dirname(__FILE__).'/class.pmwiki2pdf.php');
if($file->isFile() && $file->isReadable() && $file->getOwner() == P2PDF_OWNER ) require($file);
else die('<p style="color: red; font-weight: bold;">Failed to require the pmwiki2pdf class!</p>');

#
#  add the $HandleActions PDF variable array ...
#
$HandleActions['pdf'] = 'HandlePDF';

function HandlePDF($pagename) {
	global $WikiTitle;
	
	try {
		$pmwiki['name'] = $pagename;
		$pmwiki['page'] = RetrieveAuthPage($pmwiki['name'], 'read', true, READPAGE_CURRENT);
		$pmwiki['title'] = $WikiTitle;
				
		// declare new instance
		$pmwiki2pdf = new pmwiki2pdf();
		
		$pmwiki2pdf->get_wiki_vars($pmwiki); 
		$pmwiki2pdf->build_variables_needed();
		$pmwiki2pdf->transform_text();
		$pmwiki2pdf->initialize_server();
		
		$pmwiki2pdf->get_flux();
		$pmwiki2pdf->get_pdf();
		
		unset($pmwiki, $pmwiki2pdf);		
	}
	catch(Exception $e) { echo $e; }
}

?>
