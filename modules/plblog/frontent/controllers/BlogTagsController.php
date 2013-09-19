<?php
class BlogTagsController extends FrontController
{
	private $_leng_default = 10;
	
	public function __construct()
	{
		if (Configuration::get("PS_REWRITING_SETTINGS") == 1)
			$this->php_self = 'blog/tag/'.Tools::getValue('pltn').'_'.Tools::getValue('plidt').'.html';
		parent::__construct();
	}
	
	public function displayContent()
	{
		parent::displayContent();
		
		$this->display();
	}
	
	public function displayHeader()
	{
		global $css_files, $js_files;

		if (!self::$initialized)
			$this->init();

		// P3P Policies (http://www.w3.org/TR/2002/REC-P3P-20020416/#compact_policies)
		header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

		/* Hooks are volontary out the initialize array (need those variables already assigned) */
		self::$smarty->assign(array(
			'time' => time(),
			'img_update_time' => Configuration::get('PS_IMG_UPDATE_TIME'),
			'static_token' => Tools::getToken(false),
			'token' => Tools::getToken(),
			'logo_image_width' => Configuration::get('SHOP_LOGO_WIDTH'),
			'logo_image_height' => Configuration::get('SHOP_LOGO_HEIGHT'),
			'priceDisplayPrecision' => _PS_PRICE_DISPLAY_PRECISION_,
			'content_only' => (int)Tools::getValue('content_only')
		));
		self::$smarty->assign(array(
			'HOOK_HEADER' => Module::hookExec('header'),
			'HOOK_TOP' => Module::hookExec('top'),
			'HOOK_LEFT_COLUMN' => Module::hookExec('leftColumn')
		));

		if ((Configuration::get('PS_CSS_THEME_CACHE') OR Configuration::get('PS_JS_THEME_CACHE')) AND is_writable(_PS_THEME_DIR_.'cache'))
		{
			// CSS compressor management
			if (Configuration::get('PS_CSS_THEME_CACHE'))
				Tools::cccCss();

			//JS compressor management
			if (Configuration::get('PS_JS_THEME_CACHE'))
				Tools::cccJs();
		}
		/*pl*/
		$tags_name = Tools::getValue('pltn');
		self::$smarty->assign('meta_title', $tags_name.' - Blog ');
		
		// $id_pl_blog_category = (int) Tools::getValue('plidc');
		// if ($id_pl_blog_category != null)
		// {
			// $category = $this->getMetaCategoryById($id_pl_blog_category);
			// $meta_title = $category['category_meta_title'];
			// $meta_description = $category['category_meta_description'];
			// $meta_keywords = $category['category_meta_keywords'];
			// if ($meta_title != null)
				// self::$smarty->assign('meta_title', $meta_title.' - Blog ');
			// if ($meta_description != null)
				// self::$smarty->assign('meta_description',$meta_description);
			// if ($meta_keywords != null)
				// self::$smarty->assign('meta_keywords', $meta_keywords);
		// }
		// else 
		// {
			// $category_name = 'List post';
			// self::$smarty->assign('meta_title', $category_name.' - Blog ');
		// }
		/* -pl */
		self::$smarty->assign('css_files', $css_files);
		self::$smarty->assign('js_files', array_unique($js_files));
		self::$smarty->display(_PS_THEME_DIR_.'header.tpl');
	}
	
	function getCurrentURL()
	{
		$url = 'http';
		if ($_SERVER["HTTPS"] == "on") {$url .= "s";}
		$url .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		}
		else {
			$url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		
		//return $url;	

		// replace url
		$start1 = strpos($url, 'plpage')+7;
		$new_url = substr($url, $start1);
		$start2 = strpos($url, '&');
		$new_url = substr($url, $start2);
		
		$url = substr($url, 0, $start1).'0'.substr($url, $start2);
		return $url;
	}
	
	function setPath($name = null)
	{
		$url = '';
		if (Configuration::get("PS_REWRITING_SETTINGS") == 0)
		{
			$url = 'http';
			
			if ($_SERVER["HTTPS"] == "on") 
				$url .= "s";
				
			$url .= "://";
			if ($_SERVER["SERVER_PORT"] != "80")
				$url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			else
				$url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

			// return $url;
		}
		else
		{
			global $link;
            if (Tools::getValue('plidc'))
			{
				//$url = $link->getPageLink('blog/'.Tools::getValue('plcn').'.'.Tools::getValue('plidc').'.html');
				$url = $link->getPageLink('blog/'.Tools::getValue('plidc').'_'.Tools::getValue('plcn').'.html');
				// return $url;
			}
			elseif (Tools::getValue('plidt'))
			{
				//$url = $link->getPageLink('').'blog/'.Tools::getValue('plidt').'.'.Tools::getValue('pltn').'.html';		
				$url = $link->getPageLink('blog/tag/'.Tools::getValue('pltn').'_'.Tools::getValue('plidt').'.html');
				// return $url;
			}
		}
		
		session_start();
		$_SESSION['pl_a_path'] = $url;
		$_SESSION['pl_a_name'] = $name;
	}
	
	function display()
	{
		global $smarty, $link, $cookie;		
		$pl_path = array();
		
		$url_rewrite = Configuration::get('PS_REWRITING_SETTINGS');
		$pl_path[0]['link'] = 'http://';
		$name = Db::getInstance()->getValue("
			SELECT tags_name FROM "._DB_PREFIX_."pl_blog_tags_lang
			WHERE id_lang = ".$cookie->id_lang." AND id_pl_blog_tags = ".Tools::getValue("plidt")."
		");
		$pl_path[0]['name'] = $name;
		$this->setPath($name);
		
		$smarty->assign('pl_path', $pl_path);
		$smarty->display(_PS_MODULE_DIR_.'plblog/frontent/tpl/breadcrumb.tpl');
		
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_post a
				LEFT JOIN '._DB_PREFIX_.'pl_blog_post_lang b
				ON (a.id_pl_blog_post= b.id_pl_blog_post)
				LEFT JOIN '._DB_PREFIX_.'pl_blog_post_tag c
				ON (a.id_pl_blog_post = c.id_pl_blog_post)
				WHERE (a.post_status=1) AND (b.id_lang='.(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT')).')  AND c.id_pl_tag='.Tools::getValue('plidt');		
		$result = Db::getInstance()->ExecuteS($sql);
		$this->pagination(count($result));
		
		/* load javascript, css */
		$smarty->assign('_MODULE_DIR_', _MODULE_DIR_);
		/* -load javascript, css */
		
		/* display title */
		$smarty->assign('pl_tag_name', $this->getTagName(Tools::getValue('plidt')));
		/* -display title */
		
		$page = (int)Tools::getValue('p');
		if ($page == null)
			$page = 0;
			
		$postes = $this->getPostes($page);
		
		
		/* display post list */
		if ($postes != null)
		{
			$smarty->assign('pl_postes_empty', 0);
			$smarty->assign('pl_post_list', $postes);
        }
		else
		{
			$smarty->assign('pl_postes_empty', 1);
		}
		/* -display post list */
		
		require_once _PS_MODULE_DIR_.'plblog/frontent/tools/PlTools.php';		
		$plTools = new PlTools();		$smarty->assign('plTools', $plTools);
		
		$smarty->display(_PS_MODULE_DIR_.'plblog/frontent/tpl/post-tag.tpl');
		$smarty->display(_PS_MODULE_DIR_.'plblog/frontent/tpl/pagination.tpl');
	}

	protected function canonicalRedirection()
	{
		global $link, $cookie;

		if (Configuration::get('PS_CANONICAL_REDIRECT'))
		{
			// Automatically redirect to the canonical URL if needed
			if (isset($this->php_self) AND !empty($this->php_self))
			{
				// $_SERVER['HTTP_HOST'] must be replaced by the real canonical domain
				$canonicalURL = $link->getPageLink($this->php_self, $this->ssl, $cookie->id_lang);
				if (!preg_match('/^'.Tools::pRegexp($canonicalURL, '/').'([&?].*)?$/', (($this->ssl AND Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://').$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']))
				{
					header('HTTP/1.0 301 Moved');
					if (defined('_PS_MODE_DEV_') AND _PS_MODE_DEV_ AND $_SERVER['REQUEST_URI'] != __PS_BASE_URI__)
						die('[Debug] This page has moved<br />Please use the following URL instead: <a href="'.$canonicalURL.'">'.$canonicalURL.'</a>');
					Tools::redirectLink($canonicalURL);
				}
			}
		}
	}

	function getPostes($page = 0)
	{
		global $cookie;
		$id_pl_blog_tags = (int)Tools::getValue('plidt');

		$leng = (Tools::getValue('n') ? Tools::getValue('n') : 10);
		$start = $leng * ($page == 0 ? 0 : $page-1);
		$end = $leng;		
		
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_post a
				LEFT JOIN '._DB_PREFIX_.'pl_blog_post_lang b
				ON (a.id_pl_blog_post= b.id_pl_blog_post)
				LEFT JOIN '._DB_PREFIX_.'pl_blog_post_tag c
				ON (a.id_pl_blog_post = c.id_pl_blog_post)
				WHERE (a.post_status=1) AND (b.id_lang='.(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get("PS_LANG_DEFAULT")).')  AND c.id_pl_tag='.$id_pl_blog_tags.'
				LIMIT '.$start.','.$end.'
				';		
		return Db::getInstance()->ExecuteS($sql);
	}
	
	function getCountComment($id_pl_blog_post = null)
	{
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_comment a
		WHERE a.comment_status=2 AND a.id_pl_blog_post='.$id_pl_blog_post;
		
		return count(Db::getInstance()->ExecuteS($sql));
	}
	function stringOnLink($str)	{		$str = str_replace("-", "", $str);				$str = str_replace(" ", "-", str_replace("  ", " ", strtolower($str)));		$arr = array("~", "`", "!", "@", "#","$", "%", "^", "&", "*", "(", ")","_", "+", "=", "|", "\\", '{', '}', '[', ']', "'", '"', ':', ';','<',",",'>',".",'/','?');				for ($i = 0; $i < count($arr); $i++)		{				$str = str_replace($arr[$i], '', $str);		}				return $str;	}	
	
	function substr($str, $start = 0, $end = null)
	{
		if ($end == null)
			$end = strlen($str);
		$str = substr(strip_tags($str), $start, $end);
		return $str;
	}
	
	function getTagName($id_pl_blog_tags)
	{
		global $cookie;
		return Db::getInstance()->getValue("
			SELECT tags_name FROM "._DB_PREFIX_."pl_blog_tags_lang WHERE id_lang=".(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get("PS_LANG_DEFAULT"))." AND id_pl_blog_tags=".$id_pl_blog_tags."
		");
	}
	
	function getMetaCategoryById($id_pl_blog_category)
	{
		$sql = 'SELECT category_meta_title, category_meta_description, category_meta_keywords FROM '._DB_PREFIX_.'pl_blog_category a
				WHERE a.id_pl_blog_category='.$id_pl_blog_category.'
			   ';
		$category = Db::getInstance()->getRow($sql);
		
		return $category;
	}
}