<?php
/**
 * Plugin blogroll w/ favicon
 *
 * @package	PLX
 * @version	1.2
 * @date	12/03/2013
 * @author	i M@N
 * @based on	Rockyhorror Blogroll 0.5
 **/
 

class Blogroll extends plxPlugin {

	public $blogList = array(); # Tableau des blogs
	
	/**
	 * Constructeur de la classe blogroll
	 *
	 * @param	default_lang	langue par défaut utilisée par PluXml
	 * @return	null
	 * @author	Rockyhorror
	 **/
	public function __construct($default_lang) {

		# Appel du constructeur de la classe plxPlugin (obligatoire)
		parent::__construct($default_lang);
		
		# Autorisation d'acces à la configuration du plugin
		$this->setConfigProfil(PROFIL_ADMIN, PROFIL_MANAGER);

		# Autorisation d'accès à l'administration du plugin
		$this->setAdminProfil(PROFIL_ADMIN, PROFIL_MANAGER);

		# Déclarations des hooks
		$this->addHook('showBlogrollHead', 'showBlogrollHead');
		$this->addHook('showBlogroll','showBlogroll');
	}

	public function OnActivate() {
		$plxMotor = plxMotor::getInstance();
		if (version_compare($plxMotor->version, "5.1.7", ">=")) {
			if (!file_exists(PLX_ROOT."data/configuration/plugins/Blogroll.xml")) {
				if (!copy(PLX_PLUGINS."Blogroll/parameters.xml", PLX_ROOT."data/configuration/plugins/Blogroll.xml")) {
					return plxMsg::Error(L_SAVE_ERR.' '.PLX_PLUGINS."Blogroll/parameters.xml");
				}
			}
		}
	}

	public function getBlogroll($filename) {
		
		if(!is_file($filename)) return;
		
		# Mise en place du parseur XML
		$data = implode('',file($filename));
		$parser = xml_parser_create(PLX_CHARSET);
		xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,0);
		xml_parse_into_struct($parser,$data,$values,$iTags);
		xml_parser_free($parser);
		if(isset($iTags['blogroll']) AND isset($iTags['title'])) {
			$nb = sizeof($iTags['title']);
			$size=ceil(sizeof($iTags['blogroll'])/$nb);
			for($i=0;$i<$nb;$i++) {
				$attributes = $values[$iTags['blogroll'][$i*$size]]['attributes'];
				$number = $attributes['number'];
				# Recuperation du titre
				$this->blogList[$number]['title']=plxUtils::getValue($values[$iTags['title'][$i]]['value']);
				# Recuperation du nom de la description
				$this->blogList[$number]['description']=plxUtils::getValue($values[$iTags['description'][$i]]['value']);
				# Recuperation de l'url
				$this->blogList[$number]['url']=plxUtils::getValue($values[$iTags['url'][$i]]['value']);
				# Recuperation de la langue
				$this->blogList[$number]['langue']=plxUtils::getValue($values[$iTags['langue'][$i]]['value']);
				
			}
		}
		
	}
	
	/**
	 * Méthode qui édite le fichier XML du blogroll selon le tableau $content
	 *
	 * @param	content	tableau multidimensionnel du blogroll
	 * @param	action	permet de forcer la mise àjour du fichier
	 * @return	string
	 * @author	Stephane F
	 **/
	public function editBloglist($content, $action=false) {

		$save = $this->blogList;
		
		# suppression
		if(!empty($content['selection']) AND $content['selection']=='delete' AND isset($content['idBlogroll'])) {
			foreach($content['idBlogroll'] as $blogroll_id) {
				unset($this->blogList[$blogroll_id]);
				$action = true;
			}
		}
		
		# mise à jour de la liste des catégories
		elseif(!empty($content['update'])) {
			foreach($content['blogNum'] as $blog_id) {
				$blog_name = $content[$blog_id.'_title'];
				if($blog_name!='') {
					$this->blogList[$blog_id]['title'] = $blog_name;
					$this->blogList[$blog_id]['url'] = $content[$blog_id.'_url'];
					$this->blogList[$blog_id]['description'] = $content[$blog_id.'_description'];
					$this->blogList[$blog_id]['langue'] = $content[$blog_id.'_langue'];
					$this->blogList[$blog_id]['ordre'] = intval($content[$blog_id.'_ordre']);
					$action = true;
				}
			}

		}
		# On va trier les clés selon l'ordre choisi
		if(sizeof($this->blogList)>0) uasort($this->blogList, create_function('$a, $b', 'return $a["ordre"]>$b["ordre"];'));
		
		# sauvegarde
		if($action) {
			# On génére le fichier XML
			$xml = "<?xml version=\"1.0\" encoding=\"".PLX_CHARSET."\"?>\n";
			$xml .= "<document>\n";
			foreach($this->blogList as $blog_id => $blog) {

				$xml .= "\t<blogroll number=\"".$blog_id."\">";
				$xml .= "<title><![CDATA[".plxUtils::cdataCheck($blog['title'])."]]></title>";
				$xml .= "<description><![CDATA[".plxUtils::cdataCheck($blog['description'])."]]></description>";
				$xml .= "<url><![CDATA[".plxUtils::cdataCheck($blog['url'])."]]></url>";
				$xml .= "<langue><![CDATA[".plxUtils::cdataCheck($blog['langue'])."]]></langue>";
				$xml .= "</blogroll>\n";
			}
			$xml .= "</document>";
			
			# On écrit le fichier
			if(plxUtils::write($xml, PLX_ROOT.$this->getParam('blogroll')))
				return plxMsg::Info(L_SAVE_SUCCESSFUL);
			else {
				$this->blogList = $save;
				return plxMsg::Error(L_SAVE_ERR.' '.$filename);
			}			
		}
	}

	public function showBlogrollHead () {
		$title = plxUtils::strCheck($this->getParam('pub_title'));
		echo $title;
	}

	/**
	 * Méthode qui récupère le favicon de l'url et le met en cache
	 *
	 * @param	url	url du favicon à récupérer
	 * @param	saveto	nom du favicon = md5(url)
	 * @return	string
	 * @author	i M@N
	 **/
	public function grab_image($url,$saveto) {
/*favicon dir check*/
	if (!is_dir(PLX_PLUGINS."Blogroll/favicon/")) {
	mkdir(PLX_PLUGINS."Blogroll/favicon/");
	}
/*grab favicon*/
	if(!file_exists(PLX_PLUGINS."Blogroll/favicon/".$saveto)){
	$ch = curl_init ($url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
	$raw=curl_exec($ch);
	curl_close ($ch);
	$fp = fopen(PLX_PLUGINS."Blogroll/favicon/".$saveto,'x');
	fwrite($fp, $raw);
	fclose($fp);
	}
}

	public function showBlogroll($format) {		
		
if (extension_loaded('curl')) {
/*check for curl*/
	$curl = 1;
	#echo 'curl : '.$curl;//yeah that's just 4 debug ; )
}
		$this->getBlogroll(PLX_ROOT.$this->getParam('blogroll'));
		if(!$this->blogList) { return; }
		
#		if(!isset($format)) { $format = '<li><a href="#url" hreflang="#langue" title="#description">#title</a></li>'; }
		if(!isset($format)) { $format = '<li style="background:url(\'#icon\') no-repeat scroll 0 0 transparent;padding-left:20px;background-size:16px 16px;"><a target="_blank" href="#url" hreflang="#langue" title="#description">#title</a></li>'; }
		foreach($this->blogList as $link) {
/*get favicon*/
$this->grab_image('http://g.etfv.co/'.$link['url'],md5($link['url']).'.ico');
##			$row = str_replace('"#url"','"#url" onclick="window.open(this.href);return false;"',$format);
			$row = str_replace('"#url"','"#url"',$format);
			$row = str_replace('#url',$link['url'],$row);
if ($curl == 1) {
			$row = str_replace('#icon',PLX_PLUGINS.'Blogroll/favicon/'.md5($link['url']).'.ico',$row);
}
else {
			$row = str_replace('#icon','http://g.etfv.co/'.$link['url'],$row);
}
			$row = str_replace('#description',plxUtils::strCheck($link['description']),$row);
			$row = str_replace('#title',plxUtils::strCheck($link['title']),$row);
			$row = str_replace('#langue',plxUtils::strCheck($link['langue']),$row);
			echo $row;
		}
		
	}
}
	
?>
