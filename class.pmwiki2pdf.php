<?php 
if(!defined('P2PDF_CFG_NAME')) define('P2PDF_CFG_NAME', 'config.php');
if(!defined('P2PDF_CFG_ERRORS')) 	define('P2PDF_CFG_ERRORS', 'errors.en.cfg');

define('P2PDF_DEBUG', 0);
define('P2PDF_DEBUG_HTML', 0);

define('P2PDF_HTML2PDF_CLASS', 'html2pdf.class.php');
define('P2PDF_HTML2PDF_VERSION', 'html2pdf_v4.03');
define('P2PDF_VERSION', 'pmwiki2pdf-v2');

define('P2PDF_ROOT', $_SERVER['DOCUMENT_ROOT']);
define('P2PDF_HOST', $_SERVER['HTTP_HOST']);
define('P2PDF_URI', $_SERVER['REQUEST_URI']);
define('P2PDF_LIMIT_MEM', ini_get('memory_limit') );
define('P2PDF_LIMIT_MEM_INTVAL', intval(P2PDF_LIMIT_MEM) );
define('P2PDF_EXEC_MAX_TIME', ini_get('max_execution_time') );

define('P2PDF_ROOT_PLUGIN', dirname(__FILE__));
define('P2PDF_CFG_FILE', P2PDF_ROOT_PLUGIN.'/'.P2PDF_CFG_NAME);
define('P2PDF_DIR_LIC', P2PDF_ROOT_PLUGIN.'/licence/');
define('P2PDF_DIR_LANG', P2PDF_ROOT_PLUGIN.'/lang/');

class pmwiki2pdf {
	
	// Define variables
	private $cfg;
	private $flux;
	private $info;
	private $html;
	
	private $wiki_page;
	private $wiki_page_href;
	private $wiki_page_name;
	private $wiki_page_title;
	private $wiki_page_title_entity_decode;
	private $wiki_page_url;
	
	private $debug = array();
	private $origin = array();
	
	public function __construct() {
		
		$this->file_errors = P2PDF_DIR_LANG.P2PDF_CFG_ERRORS;
		if(!$this->verif_file($this->file_errors)) {
			die('<p style="font-weight: bold; font-size: 2em;">Not possible to obtain file config for errors messages: <strong style="color: red;">'.P2PDF_CFG_ERRORS.'</strong></p>');
		}
		else { 
			$this->file_object_fgets($this->file_errors);
			$this->errors_mssg = $this->lines;
		}
		
		$this->file_class_html2pdf = P2PDF_ROOT_PLUGIN.'/'.P2PDF_HTML2PDF_VERSION.'/'.P2PDF_HTML2PDF_CLASS; 
		if($this->verif_file($this->file_class_html2pdf)) require($this->file_class_html2pdf);
		else die($this->get_mssg('Error_File_HTML2PDF'));
		
		$this->arrays_needed();
		
	}
	
	public function __destruct() {
		
		unset($this->cfg, $this->info, $this->flux, $this->html);
		unset($this->html2pdf, $this->file_class_html2pdf);
		// set server's initial value
		ini_set('memory_limit', P2PDF_LIMIT_MEM);
		ini_set('max_execution_time', P2PDF_EXEC_MAX_TIME);
		
	}
	
	private function arrays_needed() {
		try {
			$this->origin['default']['font'] = 'helvetica';
			
			$this->origin['display']['layout'] = array (
				'SinglePage', 'OneColumn', 
				'TwoColumnLeft', 'TwoColumnRight', 
				'TwoPageLeft', 'TwoPageRight',
			);
			$this->origin['display']['mode'] = array (
				'UseNone', 'UseOutlines', 'UseThumbs', 
				'FullScreen', 'UseOC', 'UseAttachments',
			);
			$this->origin['display']['zoom'] = array (
				'fullpage', 'fullwidth', 'real', 'default',
			);
			
			$this->origin['marge'] = array (
				'back' => array (
					'top' => 0,
					'right' => 0,
					'bottom' => 0,
					'left' => 0,
				),
				'page' => array (
					'top' => 0,
					'right' => 0,
					'bottom' => 0,
					'left' => 0,
				),
			);
			
			$this->origin['output']['dest'] = array (
				'I', 'D', 'F', 'S',
			);
			
			$this->origin['page']['backimg'] = array (
				'x' => array ( 'left', 'center', 'right',),
				'y' => array ( 'top', 'middle', 'bottom',),
			);
			
			$this->origin['page']['orientation'] = array (
				'P', 'L',
			);
			
			$this->origin['measure_unit'] = array (
				'mm', 'px', 'pt', '%',
			);
			
			$this->manage['rights'] = array (
				'charset' => array (
					'ISO-8859-1',
					'ISO-8859-15',
					'UTF-8',
				),
				
				'licence' => array (
					'CPI', 
					'GPL', 'FDL', 'LGPL', 
					'LAL', 
					'CC-BY', 'CC-BY-SA', 
					'CC-BY-NC', 'CC-BY-ND', 
					'CC-BY-NC-ND', 'CC-BY-NC-SA', 
				),
				
				'url' => array (
					'GPL' => 'http://www.gnu.org/licenses/gpl.html',
					'FDL' => 'http://www.gnu.org/licenses/fdl.html',
					'LGPL' => 'http://www.gnu.org/licenses/lgpl.html',
					'LAL' => 'http://artlibre.org/licence/lal',
					'CC-BY' => 'http://creativecommons.org/licenses/by/2.5/',
					'CC-BY-NC' => 'http://creativecommons.org/licenses/by-nc/2.5/',
					'CC-BY-ND' => 'http://creativecommons.org/licenses/by-nd/2.5/',
					'CC-BY-NC-ND' => 'http://creativecommons.org/licenses/by-nc-nd/2.5/',
					'CC-BY-NC-SA' => 'http://creativecommons.org/licenses/by-nc-sa/2.5/',
					'CC-BY-SA' => 'http://creativecommons.org/licenses/by-sa/2.5/',
				),
			);
			
			$this->manage['transformer'] = array (
				
				'caption' => 1,
				'dl' => array (
					'dl2p', 'dl2li',
				),
				'specials_characters' => 0,
				
			);
			
			$this->boo = array (
				'html' => array ( 'dl', ),
				'pmwiki' => array ( 'breakpage', 'indent', ),
			);
			
			if(P2PDF_DEBUG == 1) {
				$this->debug['origin'] = $this->origin;
				$this->debug['manage'] = $this->manage;
				$this->debug['boo'] = $this->boo;
			}
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function build_flux() {
		try {
			
			ob_start();
			
			// include css file if exists
			if(!empty($this->cfg['page']['style']['include'])) {
				$file = P2PDF_ROOT_PLUGIN.'/'.$this->cfg['page']['style']['name'];
				if($this->verif_file($file)) require($file);
				unset($file);
			}
			// get open page
			$this->get_open_page();
			// include page header
			if(!empty($this->cfg['page']['header']['include'])) {
				$file = P2PDF_ROOT_PLUGIN.'/'.$this->cfg['page']['header']['name'];
				if($this->verif_file($file)) require($file);
				unset($file);
			}
			// include page footer
			if(!empty($this->cfg['page']['footer']['include'])) {
				$file = P2PDF_ROOT_PLUGIN.'/'.$this->cfg['page']['footer']['name'];
				if($this->verif_file($file)) require($file);
				unset($file);
			}
			// include html
			echo $this->html;
			// close page		
			echo '</page>';
			// to display rights
			$this->display_rights();
			
			$flux = ob_get_clean(); 
			if(!empty($flux)) $this->flux = $flux;
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function build_html() {
		try {
			
			$html = MarkupToHTML($this->wiki_page_name, $this->wiki_page['text']); 
			$this->html = '<div class="main">'.$html.'</div>'; 
			$this->change_code_html();
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	public function build_variables_needed() {
		try {
			$this->get_config();
			$this->get_info();
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function change_code_html() {
		try {
			$array = explode("\n", $this->html); 
			
			$array = $this->preg_replace_html_code($array); 
			
			if(P2PDF_DEBUG_HTML == true) var_dump($array);
			
			$this->html = implode("\n", $array);
			//$this->html = html_entity_decode($this->html, ENT_QUOTES, $this->wiki_page['charset']);
			
			if(P2PDF_DEBUG_HTML == true) var_dump($this->html);
			
			// transform dl because not support by HTML2PDF
			if(!empty($this->cfg['transformer']['dl'])) {

				$this->preg_replace_html_dl($this->cfg['transformer']['dl']);
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function change_pmwiki_code() {
		try {
			
			$array = explode("\n", $this->wiki_page['text']);
			
			$array = $this->preg_replace_pmwiki_code($array);
			
			$this->wiki_page['text'] = implode("\n", $array);
			
			unset($array);
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function display_rights() {
		try {
			if(!empty($this->cfg['rights']['display'])) {
				$html = '<page pageset="old">';
				
				$html .= '<div class="copyright">';
				$html .= '<h2><strong>Copyright: '.$this->wiki_page['author'].' - '.$this->info['pdf']['time'].'</strong></h2>';
				if($this->cfg['rights']['licence'] != 'CPI') {
				
					$file = P2PDF_DIR_LIC.$this->cfg['rights']['licence'].'_'.$this->cfg['rights']['lang'].'.txt';
					if($this->verif_file($file)) $this->file_object_fgets($file);
					else {
						$file = P2PDF_DIR_LIC.$this->cfg['rights']['licence'].'_en.txt';
						if($this->verif_file($file)) $this->file_object_fgets($file);
					}
					unset($file);
					
					if(!empty($this->lines)) {
						foreach($this->lines as $key => $value) {
							$html .= MarkupToHTML($this->wiki_page_name, $value);
						}
						unset($key,$value);
					}
					unset($this->lines);
					
					$url = $this->manage['rights']['url'][$this->cfg['rights']['licence']];
					$html .= '<p><a href="'.$url.'">'.$url.'</a></p>';
					unset($url);
				}
				
				$html .= '</div>';
				$html .= '</page>';
			}
			
			if(!empty($html)) echo $html;
			unset($html);
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e->getMessage()); }
	}

	private function file_object_fgets($file) {
		try {
			$file = new SplFileObject($file);
			if($file->isFile() && $file->isReadable() && $file->getOwner() == P2PDF_OWNER ) {
			
				while(!$file->eof()) { $lines[] = trim($file->fgets()); }
				
				$this->lines = $lines;
			}
			unset($file);
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e->getMessage()); }
	}
	
	public function get_flux() {
		try {
			$this->build_flux();
			
			if(P2PDF_DEBUG == 1) $this->origin['flux'] = $this->flux;
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function get_config() {	
		try {
			
			if($this->verif_file(P2PDF_CFG_FILE)) require(P2PDF_CFG_FILE);
			else die($this->get_mssg('Error_File_config'));
			
			if(!empty($cfg)) {
				$this->cfg = $cfg; 
				$this->verif_config();
			}
			unset($cfg);
			
			if(P2PDF_DEBUG == 1) $this->debug['config'] = $this->cfg;
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}

	private function get_info() { 
		try { 
			// define variable 
			$info['author'] = 'by '.$this->wiki_page['author']; // pdf author
			
			if(!empty($this->wiki_page['description'])) {
				$info['pdf']['subject'] = $this->wiki_page['description'];
			}
			else $info['pdf']['subject'] = '';
			
			if(!empty($this->wiki_page['keywords'])) {
				$info['pdf']['keywords'] = $this->wiki_page['keywords'];
			}
			else $info['pdf']['keywords'] = '';
			
			$info['page']['name'] = str_replace('.', '_', $this->wiki_page_name); // page name
			
			$info['pdf']['name'] = str_replace(' ', '_',$this->remove_accents($this->wiki_page_title_entity_decode, $this->wiki_page['charset'])).'_'.$info['page']['name'].'.pdf'; // pdf name
			$info['pdf']['title'] = $this->wiki_page_title_entity_decode.' : page '.$info['page']['name']; // pdf title
			
			if(!empty($this->wiki_page['time'])) {
				setlocale(LC_ALL, $this->cfg['lang'].'_'.strtoupper($this->cfg['lang']));
				$info['pdf']['time'] = strftime('%x',$this->wiki_page['time']);
			}
			
			if(!empty($info)) $this->info = $info;
			unset($info);
			
			if(P2PDF_DEBUG == 1) $this->debug['info'] = $this->info;
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function get_open_page() {
		try {
			if(!empty($this->cfg['marge']['back'])) {
				$back[0] = 'backtop="'.$this->cfg['marge']['back']['top'].'"';
				$back[1] = 'backright="'.$this->cfg['marge']['back']['right'].'"';
				$back[2] = 'backbottom="'.$this->cfg['marge']['back']['bottom'].'"';
				$back[3] = 'backleft="'.$this->cfg['marge']['back']['left'].'"';
			}
			if(!empty($this->cfg['page']['backim']['url'])) {
				$backimg[0] = 'backimg="'.$this->cfg['page']['backim']['url'].'"';
				$backimg[1] = 'backimgx="'.$this->cfg['page']['backim']['x'].'"';
				$backimg[2] = 'backimgy="'.$this->cfg['page']['backim']['y'].'"';
				$backimg[3] = 'backimgw="'.$this->cfg['page']['backim']['w'].'"';
			}
			
			$page = '<page';
			if(!empty($back) && is_array($back)) {
				$page .= ' ';
				foreach($back as $value) { $page .= $value.' '; }
				unset($value,$back);
			}
			if(!empty($backimg) && is_array($backimg)) {
				$page .= ' ';
				foreach($backimg as $value) { $page .= $value.' '; }
				unset($value,$backimg);
			}
			$page .= '>';
			
			if(!empty($page)) echo $page;
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}

	private function get_mssg($char) {
		try {
			
			if(!empty($this->errors_mssg)) {
				foreach($this->errors_mssg as $key => $value) {
							
					if(!empty($value)) {
						if(preg_match('!^#!', $value)) unset($this->errors_mssg[$key]);
						
						$x = explode('=',$value);
						unset($this->errors_mssg[$key]);
						
						$k = trim($x[0]);
						$v = trim($x[1]);
						$this->errors_mssg[$k] = $v;
						unset($x,$k,$v);
					}
					else unset($this->errors_mssg[$key]);
					
				}
				
				if(!empty($char)) $this->mssg = $this->errors_mssg[$char];
				
			}
		
			if(!empty($this->mssg)) { 
				echo '<p style="color: red; font-weight: bold; font-size: 2em;">'.$this->mssg.'</p>';
				unset($this->mssg);
			}
			
		}	
		catch(Exception $e) { echo $e; }
	}
	
	private function get_page($what) {
		try {
			if(!empty($this->cfg['page'][$what]['include'])) {
				$this->file_object_fgets(P2PDF_ROOT_PLUGIN.'/'.$this->cfg['page'][$what]['name']);
				
				if(!empty($this->lines) && is_array($this->lines)) {
					
					$line = '';
					
					$count = count($this->lines); 
					if(empty($this->lines[$count - 1])) unset($this->lines[$count - 1]);
					
					foreach($this->lines as $key => $value) {
						$line .= $value;
						if ($key < $count ) {
							$line .= "\n";
						}
					}
					unset($count, $key, $value);
				}
			}
			
			if(!empty($line)) $this->page = $line;
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$what.' '.$e); }
	}

	public function get_pdf() {
		try { 
			
			if(P2PDF_DEBUG == true) var_dump($this->debug);
			
			//  declare a new object pdf
			$html2pdf = new HTML2PDF (
				$this->cfg['page']['orientation'], 
				$this->cfg['page']['format'], 
				$this->cfg['lang'], 
				$this->cfg['unicode'], 
				$this->cfg['encoding'], 
				$this->cfg['marges']['page']
			); 
	
			// specify HTML2PDF functions
			$html2pdf->pdf->SetAuthor($this->info['author']);			
			$html2pdf->pdf->SetCompression($this->cfg['zip']);
			
			$html2pdf->pdf->SetDisplayMode(
				$this->cfg['display']['zoom'], 
				$this->cfg['display']['layout'], 
				$this->cfg['display']['mode']
			);
			
			$html2pdf->pdf->SetKeywords($this->info['pdf']['keywords']);

				// SetProtection
			if(!empty($this->cfg['protect']['set']) && !empty($this->cfg['protect']['permission']) ) {
				$html2pdf->pdf->SetProtection($this->cfg['protect']['permission'], '', $this->cfg['protect']['owner_psswd']);
			}
			
			$html2pdf->pdf->SetSubject($this->info['pdf']['subject']);			
			$html2pdf->pdf->SetTitle($this->info['pdf']['title']);
	
			// specify HTML2PDF methods
			$html2pdf->getHtmlFromPage($this->flux);			
			$html2pdf->setDefaultFont($this->cfg['default']['font']);

			// build the page PDF
			$html2pdf->writeHTML($this->flux, isset($_GET['vuehtml']));
			
			$html2pdf->Output($this->info['pdf']['name'], $this->cfg['output']['dest']);
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' get_pdf '.$e); }
	}
	
	public function get_wiki_vars($var) {
		try { 
			
			if(P2PDF_DEBUG == 1) $this->debug['pmwiki'] = $var;
			
			// wiki page variables
			if(!empty($var['name'])) {
				$this->wiki_page_name = $var['name'];
				$this->wiki_page_url = str_replace('.','/',$var['name']);
				$this->wiki_page_href = 'http://'.P2PDF_HOST.'/'.$this->wiki_page_url;
			}
			else die($this->get_mssg('Error_wiki_page_name'));
			// wiki page variable segun pmwiki page informations
			if(!empty($var['page'])) {
				$this->wiki_page = $var['page']; 
				//replace pmwiki code in text!
				$this->change_pmwiki_code(); 
			}
			else die($this->get_mssg('Error_wiki_page'));
			// wiki title variables
			if(!empty($var['title'])) {
				$this->wiki_page_title = $var['title']; 
				$this->wiki_page_title_entity_decode = html_entity_decode($this->wiki_page_title, ENT_QUOTES, $this->wiki_page['charset']);
			}
			else die($this->get_mssg('Error_wiki_page_title'));
			
			// declare this wiki_url_domain
			$this->wiki_url_domain = P2PDF_HOST;
			
			unset($var);
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	public function initialize_server() {
		try {
			//out pass memory server
			if(P2PDF_LIMIT_MEM_INTVAL < '24') ini_set('memory_limit', '24M');
			ini_set('max_execution_time', 0);
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function preg_replace_html_breakpage() {
		try {
			
			//var_dump($this->search);
			foreach($this->search['breakpage'] as $k1 => $v1) { 
				if(preg_match($v1, $this->value)) {
					
					$this->replacement = $this->replace['breakpage'][$k1];
					$this->array[$this->key] = preg_replace($v1, $this->replacement, $this->value);
					unset($this->replacement);
					
					$key1 = $this->key + 1;
					if(!empty($this->array[$key1])) {
						$this->array[$key1] = preg_replace('!^<\/p>(.*)!Usi', '$1', $this->array[$key1]);
					}
					unset($key1);
					
				}
			}
			unset($k1, $v1);
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function preg_replace_html_caption() {
		try {
			
			if(!empty($this->manage['transformer']['caption'])) {
				foreach($this->search['caption'] as $k1 => $v1) {
					
					if(preg_match($v1, $this->value)) {
						$this->replacement = $this->replace['caption'][$k1];
						$this->array[$this->key] = preg_replace($v1, $this->replacement, $this->value);
						unset($this->replacement);
					}
					
				}
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function preg_replace_html_indent() {
		try {
			
			foreach($this->search['indent'] as $k1 => $v1) {
				
				if(preg_match_all($v1, $this->value, $match)) {
					
					$this->match = $match;
					$this->value1 = $v1;
					
					switch($k1) {
						
						case 0:
							$this->preg_replace_html_indent_symbol('gt');
						break;
						
						case 1:
							$this->preg_replace_html_indent_symbol('lt');
						break;
						
						case 2: 
							$this->preg_replace_html_indent_div();
						break;
					}
					
					unset($this->match, $this->value1);
				}
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function preg_replace_html_indent_div() {
		try {
			
			foreach($this->match[1] as $key1 => $value1) {
				if(!empty($value1)) {
					$this->replacement = '<div class="'.$value1.'dent';
						
					if(!empty($this->match[2][$key1])) $this->replacement .= $this->match[2][$key1];
						
					$this->replacement .= '">';
				}
										
				$this->array[$this->key] = preg_replace($this->value1, $this->replacement, $this->value);
				unset($this->replacement);
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function preg_replace_html_indent_symbol($symbol) {
		try {
			
			switch($symbol) {
				case 'gt': $this->symbol = '-&gt;'; break;
				case 'lt': $this->symbol = '-&lt;'; break;
			}
			
			$i=0;
			
			foreach($this->match[1] as $key1 => $value1) {
				if(!empty($value1)) { 
					
					while($i < $value1) { 
						$signe .= '-';
						$i++;
					}
							
					$this->replacement = $signe.$this->symbol; 
					unset($i,$signe);
				}
				else $this->replacement = $this->symbol;
				unset($this->symbol);

				$this->array[$this->key] = preg_replace($this->value1, $this->replacement, $this->value);
				unset($this->replacement);
			}
			unset($key1, $value1);
			
			unset($i);
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function preg_replace_html_code($a) {
		try {
			
			$this->search = array (
				'breakpage' => array (
					"!<p class='vspace'>\(:break_page:\)!",
				),
				// replace caption, because HTML2PDF <= 4.01 not support!
				'caption' => array (
					'!<table(.*)><caption(.*)>(.*)</caption>!',
				),
				// replace indent, outdent
				'indent' => array (
					'!-&gt;\{(.*){1}\}!',
					'!-&lt;\{(.*){1}\}!',
					'!<div class=\'(in|out)dent\'>\{(.*){1}\}!',
				),
			);
			
			$this->replace = array (
				'breakpage' => array (
					'</div></page><page pageset="old"><div class="main">',
				),
				'caption' => array (
					'<p$1><strong>'.trim('$3').'</strong></p><table$1>',
				),
			);
			
			if(!empty($a) && is_array($a)) {
				$this->array = $a;
				
				foreach($this->array as $key => $value) {
					$this->key = $key;
					$this->value = $value;
					
					// replace breakpage
					$this->preg_replace_html_breakpage();
					
					// replace caption
					$this->preg_replace_html_caption();
					
					// replace indent, outdent
					$this->preg_replace_html_indent();
					
					// replace special characters
					$this->str_replace_specials_characters();
					
					unset($this->key, $this->value);
				}
				unset($key, $value);
				
				return($this->array);
			}
			
			unset($this->search, $this->replace);
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}

	private function preg_replace_html_dl($var) {
		try {
			$search = array (
				// need because HTML2PDF <= 4.01 no support DL!
				'dl2li' => array (
					'!<dl>(.*)<\/dl>!Usm',
					'!<dt>(.*)<\/dt>!Usm',
					'!<dd>(.*)<\/dd>!Usm',
					"!<div class='(.*)'>(.*)<\/div>!Usm",
				),
				'dl2p' => array (
					'!<dl>(.*)<\/dl>!Usm',
					'!<dt>(.*)<\/dt>!Usm',
					'!<dd>(.*)<\/dd>!Usm',
				),
			);
			
			$replace = array (
				'dl2li' => array (
					'<ul class="dl2li">'.trim('$1').'</ul>',
					'<li class="dl2li"><strong>'.trim('$1').'</strong>: ',
					trim('$1').'</li>',
					'<p class="$1">'.trim('$2').'</p>',
				),
				'dl2p' => array (
					'<div class="dl2p">'.trim('$1').'</div>',
					'<p><strong>'.trim('$1').'</strong></p>',
					'<p>'.trim('$1').'</p>',
				),
			);
			
			while(preg_match($search[$var][0], $this->html)) {
				$this->html = preg_replace($search[$var], $replace[$var], $this->html);
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function preg_replace_pmwiki_breakpage() {
		try {
			
			if(in_array($this->value, $this->search['breakpage'])) { 
				$key = array_search($this->value, $this->search['breakpage']); 
				
				$pattern = '!'.$this->search['breakpage'][$key].'!'; 
				$replacement = $this->replace['breakpage']; 
				
				$this->array[$this->key] = preg_replace($pattern, $replacement, $this->value);
				unset($key,$pattern,$replacement);
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function preg_replace_pmwiki_code($a) {
		try {
			
			$this->search = array (
				'breakpage' => array (
					'(:breakpage:)', /* original version */
					'____', /* version 2.0.7 */
					'====', /* version for PmWiki 2.0.beta54 and > */
				),
				'indent' => array (
					'!^\-(.*)>!',
					'!^\-(.*)<!',
				),
			);
			
			$this->replace = array (
				'breakpage' => ':break_page:',
				'indent' => array (
					'->',
					'-<',
				),
			);
			
			if(!empty($a) && is_array($a)) {
				$this->array = $a;
				
				foreach($this->array as $key => $value) {
					
					$this->key = $key;
					$this->value = $value;
					
					// replace breakpage
					$this->preg_replace_pmwiki_breakpage();
					
					// replace indent, outdent
					$this->preg_replace_pmwiki_indent();
					
					unset($this->key, $this->value);
				}
				unset($key, $value);
			}
			
			if(!empty($this->array)) {
				return($this->array);
				unset($this->array);
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function preg_replace_pmwiki_indent() {
		try {
			
			foreach($this->search['indent'] as $key1 => $value1) {
				if(preg_match($value1, $this->value, $count)) {
					$nb = strlen($count[1]);
				
					$this->replacement = $this->replace['indent'][$key1].'{'.$nb.'}';
				
					$a[$key] = preg_replace($value1, $this->replacement, $this->value);
					
					unset($nb, $this->replacement);
				}
				unset($count);
			}
			unset($key1, $value1);
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
		
	private function remove_accents($buffer) {
		try {
			$str = htmlentities($buffer, ENT_NOQUOTES, $this->wiki_page['charset']);
		
			$str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
			$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
			$str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
    
			if(!empty($str)) return $str;
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e);  }
	}
	
	private function str_replace_specials_characters() {
		try {
			
			if(!empty($this->manage['transformer']['specials_characters'])) {
				$search = array (
					'&#8482;',
					'&rarr;',
				);
			
				$replace = array (
					'™',
					'→',
				);
			
				foreach($search as $key => $value) { 
					if(preg_match('!'.$value.'!', $this->value)) { 
						$this->array[$this->key] = str_replace($value, $replace[$key], $this->value); 
						//$this->array[$this->key] = html_entity_decode($this->value, ENT_QUOTES, $this->wiki_page['charset']);
						//$this->array[$this->key] = html_entity_decode($this->value, ENT_QUOTES, 'UTF-8');
					}
				}
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e);  }
	}

	public function transform_text() {
		try {
			$this->build_html();
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}

	private function verif_config() {
		try {

			// verif default font
			$this->verif_config_default_font();
			
			// verif display
			$this->verif_config_display();
			
			// verif encoding
			$this->verif_config_encoding();
			
			// verif lang
			$this->verif_config_lang();
			
			// verif marge
			$this->verif_config_marge();
			
			// verif output dest
			$this->verif_config_output_dest();
			
			// verif page
			$this->verif_config_page();
			
			// verif protection
			$this->verif_config_protection();
			
			// verif rights
			$this->verif_config_rights();

			// verif config to transformer code dl
			$this->verif_config_transformer();
			
			// verif unicode <=> charset
			$this->verif_config_unicode();
			
			// verif compression
			$this->verif_config_zip();
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_display() {
		try {
			// verif display layout
			$this->verif_config_display_layout();
			
			// verif display mode
			$this->verif_config_display_mode();
			
			// verif display zoom
			$this->verif_config_display_zoom();
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_default_font() {
		try {
			
			if( empty($this->cfg['default']['font']) || 
				( !empty($this->cfg['default']['font']) && 
					!is_string($this->cfg['default']['font']) ) ) 
			{	
				$this->cfg['default']['font'] = $this->origin['default']['font'];
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_display_layout() {
		try {
			
			if( empty($this->cfg['display']['layout']) || 
				( !empty($this->cfg['display']['layout']) && 
					!in_array($this->cfg['display']['layout'], $this->origin['display']['layout']) ) )
			{						
				$this->cfg['display']['layout'] = $this->origin['display']['layout'][0];
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_display_mode() {
		try {
			
			if( empty($this->cfg['display']['mode']) || 
				( !empty($this->cfg['display']['mode']) && 
					!in_array($this->cfg['display']['mode'], $this->origin['display']['mode']) ) )
			{
				$this->cfg['display']['mode'] = $this->origin['display']['mode'][0];
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_display_zoom() {
		try {
			
			if( empty($this->cfg['display']['zoom']) || 
				( !empty($this->cfg['display']['zoom']) && 
					!in_array($this->cfg['display']['zoom'], $this->origin['display']['zoom']) ) )
			{
				$this->cfg['display']['zoom'] = $this->origin['display']['zoom'][0];
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_encoding() {
		try {
			
			if(!empty($this->wiki_page['charset'])) $this->cfg['encoding'] = $this->wiki_page['charset'];
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_lang() {
		try {
			
			if( empty($this->cfg['lang']) || 
				( !empty($this->cfg['lang']) && !is_string($this->cfg['lang']) ) ) 
			{
				$this->cfg['lang'] = 'en';
			}	
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_marge() {
		try {
			
			if(!empty($this->cfg['marge']) && is_array($this->cfg['marge'])) { 
				foreach($this->origin['marge'] as $key => $value) { 
						
					if(is_array($this->cfg['marge'][$key])) {
						foreach($this->origin['marge'][$key] as $key1 => $value1) {
								
							if( !array_key_exists($key1, $this->cfg['marge'][$key]) &&
								array_key_exists($key1, $this->origin['marge'][$key]) ) 
							{
								$this->cfg['marge'][$key][$key1] = $this->origin['marge'][$key][$key1];
							}
																
							if(!is_int($this->cfg['marge'][$key][$key1])) {
								$this->cfg['marge'][$key][$key1] = 0;
							}
						}
						unset($key1, $value1);
					}
						
				}
				unset($key, $value);
			}
			else {
				foreach($this->origin['marge'] as $key => $value) {
					if(is_array($this->origin['marge'][$key])) {
													
						foreach($this->origin['marge'][$key] as $key1 => $value1) {
							$this->cfg['marge'][$key][$key1] = $value1;
						}
						unset($key1, $value1);
					}
				}
				unset($key, $value);
			}
			
			if( !empty($this->cfg['marge']['page']) && is_array($this->cfg['marge']['page']) ) 
			{
				$this->cfg['marges']['page'] = array (
					$this->cfg['marge']['page']['left'],
					$this->cfg['marge']['page']['top'],
					$this->cfg['marge']['page']['right'],
					$this->cfg['marge']['page']['bottom']
				);
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_output_dest() {
		try {
			
			if( empty($this->cfg['output']['dest']) || 
				( !empty($this->cfg['output']['dest']) && 
					( !in_array($this->cfg['output']['dest'], $this->origin['output']['dest']) ||
						$this->cfg['output']['dest'] != false || 
						$this->cfg['output']['dest'] != true ) ) ) 
			{	
				$this->cfg['output']['dest'] = $this->origin['output']['dest'][0];
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_page() {
		try {
			
			// verif page format
			$this->verif_config_page_format();
			
			// verif page header
			$this->verif_config_page_header();
			
			// verif page footer 
			$this->verif_config_page_footer();
			
			// verif page style
			$this->verif_config_page_style();
			
			// verif page orientation
			$this->verif_config_page_orientation();
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_page_format() {
		try {
			
			if( empty($this->cfg['page']['format']) || 
				( !empty($this->cfg['page']['format']) && 
					!is_string($this->cfg['page']['format']) ) ) 
			{
				$this->cfg['page']['format'] = $this->origin['page']['format'][0];
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_page_header() {
		try {
			 
			//	=> page_header include
			if( empty($this->cfg['page']['header']['include']) || 
				( !empty($this->cfg['page']['header']['include']) && 
					!is_int($this->cfg['page']['header']['include']) ) ) 
			{
				$this->cfg['page']['header']['include'] = 0;
			}
			
			//	=> page header name
			if( empty($this->cfg['page']['header']['name']) || 
				( !empty($this->cfg['page']['header']['name']) && 
					!is_string($this->cfg['page']['header']['name']) ) ) 
			{
				$this->cfg['page']['header']['name'] = 'page_header.html';
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_page_footer() {
		try {
			
			//	=> page_footer include
			if( empty($this->cfg['page']['footer']['include']) || 
				( !empty($this->cfg['page']['footer']['include']) && 
					!is_int($this->cfg['page']['footer']['include']) ) ) 
			{
				$this->cfg['page']['footer']['include'] = 0;
			}
			
			//	=> page footer name
			if( empty($this->cfg['page']['footer']['name']) || 
				( !empty($this->cfg['page']['footer']['name']) && 
					!is_string($this->cfg['page']['footer']['name']) ) ) 
			{
				$this->cfg['page']['footer']['name'] = 'page_footer.html';
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_page_orientation() {
		try {
			
			if( empty($this->cfg['page']['orientation']) || 
				( !empty($this->cfg['page']['orientation']) && 
					!in_array($this->cfg['page']['orientation'], $this->origin['page']['orientation']) ) )
			{
				$this->cfg['page']['orientation'] = $this->origin['page']['orientation'][0];
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_page_style() {
		try {
			
			//	=> page_style include
			if( empty($this->cfg['page']['style']['include']) || 
				( !empty($this->cfg['page']['style']['include']) && 
					!is_int($this->cfg['page']['style']['include']) ) ) 
			{
				$this->cfg['page']['style']['include'] = 0;
			}
			
			//	=> page style name
			if( empty($this->cfg['page']['style']['name']) || 
				( !empty($this->cfg['page']['style']['name']) && 
					!is_string($this->cfg['page']['style']['name']) ) ) 
			{
				$this->cfg['page']['style']['name'] = 'css.html';
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_protection() {
		try{
			
			if( !empty($this->cfg['protect']['set']) && !is_int($this->cfg['protect']['set']) ) {
				$this->cfg['protect']['set'] = 0;
			}
			
			if( $this->cfg['protect']['set'] > 0 ) {
				
				$this->cfg['protect']['permission'] = array();
				
				if( !empty($this->cfg['protect']['annot']) && is_int($this->cfg['protect']['annot'])) {
					$this->cfg['protect']['permission'][] = 'annot'; 
				}
				
				if( !empty($this->cfg['protect']['copy']) && is_int($this->cfg['protect']['copy'])) {
					$this->cfg['protect']['permission'][] = 'copy'; 
				}
				
				if( !empty($this->cfg['protect']['modify']) && is_int($this->cfg['protect']['modify'])) {
					$this->cfg['protect']['permission'][] = 'modify'; 
				}
				
				if( !empty($this->cfg['protect']['print']) && is_int($this->cfg['protect']['print'])) {
					$this->cfg['protect']['permission'][] = 'print'; 
				}
				
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_rights() {
		try {
			
			if(!empty($this->cfg['rights']['display'])) {
				// encoding
				$this->verif_config_rights_encoding();
				
				// lang
				$this->verif_config_rights_lang();
				
				// licence
				$this->verif_config_rights_license();
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_rights_encoding() {
		try {
			
			if( empty($this->cfg['rights']['charset']) ||
					( !empty($this->cfg['rights']['charset']) &&
						!in_array($this->cfg['rights']['charset'], $this->manage['rights']['charset']) ) )
				{
					$this->cfg['rights']['charset'] = $this->manage['rights']['charset'][0];
				}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_rights_lang() {
		try {
			
			if( empty($this->cfg['rights']['lang']) || 
					( !empty($this->cfg['rights']['lang']) && 
						!is_string($this->cfg['rights']['lang']) ) )
				{
					$this->cfg['rights']['lang'] = 'en';
				}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_rights_license() {
		try {
			
			if( empty($this->cfg['rights']['licence']) ||
					( !empty($this->cfg['rights']['licence']) &&
						!in_array($this->cfg['rights']['licence'], $this->manage['rights']['licence']) ) )
				{
					$this->cfg['rights']['licence'] = $this->manage['rights']['licence'][0];
				}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_transformer() {
		try {
			
			if( empty($this->cfg['transformer']['dl']) ||
				( !empty($this->cfg['transformer']['dl']) &&
					!in_array($this->cfg['transformer']['dl'], $this->manage['transformer']['dl']) ) )
			{
				$this->cfg['transformer']['dl'] = $this->manage['transformer']['dl'][0];
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_unicode() {
		try {
			
			if($this->wiki_page['charset'] == 'UTF-8') $this->cfg['unicode'] = true;
			elseif($this->wiki_page['unicode'] == true) $this->cfg['unicode'] = true;
			else $this->cfg['unicode'] = false;
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_config_zip() {
		try {
			
			if( empty($this->cfg['zip']) || 
				( !empty($this->cfg['zip']) && $this->cfg['zip'] != 1 ) ) 
			{
				$this->cfg['zip'] = 0;
			}
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method').' '.__METHOD__.' '.$e); }
	}
	
	private function verif_file($file) {
		try {
			
			$file = new SplFileInfo($file);
			if( !$file->isFile() || ( $file->isFile() && !$file->isReadable() )
				|| ( $file->getOwner() != fileowner(__FILE__) ) ) return FALSE;
			else return TRUE;
			
		}
		catch(Exception $e) { die($this->get_mssg('Error_method_verif_file').$e->getMessage()); }
	}

}

?>
