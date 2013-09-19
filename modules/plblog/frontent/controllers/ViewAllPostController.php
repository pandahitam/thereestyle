<?php
class ViewAllPostController extends FrontController
{
	private $_leng_default = 10;
	
	public function __construct()
	{
		//$this->php_self = 'modules/plblog/frontent/list-post.php';
	
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
		$id_pl_blog_category = (int) Tools::getValue('plidc');
		if ($id_pl_blog_category != null)
		{
			$category = $this->getMetaCategoryById($id_pl_blog_category);
			$meta_title = $category['category_meta_title'];
			$meta_description = $category['category_meta_description'];
			$meta_keywords = $category['category_meta_keywords'];
			if ($meta_title != null)
				self::$smarty->assign('meta_title', $meta_title.' - Blog ');
			if ($meta_description != null)
				self::$smarty->assign('meta_description',$meta_description);
			if ($meta_keywords != null)
				self::$smarty->assign('meta_keywords', $meta_keywords);
		}
		else 
		{
			$category_name = 'List post';
			self::$smarty->assign('meta_title', $category_name.' - Blog ');
		}
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
				$url = $link->getPageLink('blog/'.Tools::getValue('plcn').'.'.Tools::getValue('plidc').'.html');
				// return $url;
			}
			elseif (Tools::getValue('plidt'))
			{
				$url = $link->getPageLink('').'blog/'.Tools::getValue('plidt').'.'.Tools::getValue('pltn').'.html';		
				// return $url;
			}
			else
			{
				$url = $link->getPageLink('').'blog/all-post.html';
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
		
		$name = 'Blog';
		$url_rewrite = Configuration::get('PS_REWRITING_SETTINGS');
		$pl_path[0]['link'] = 'http://';
		$pl_path[0]['name'] = $name;
		$this->setPath($name);
		
		$smarty->assign('pl_path', $pl_path);
		$smarty->display(_PS_MODULE_DIR_.'plblog/frontent/tpl/breadcrumb.tpl');

		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_post a
				LEFT JOIN '._DB_PREFIX_.'pl_blog_post_lang b
				ON (a.id_pl_blog_post= b.id_pl_blog_post)
				WHERE (a.id_pl_blog_category != 1) AND (a.post_status=1) AND (b.id_lang='.(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get("PS_LANG_DEFAULT")).')
				';
				
		$count = count(Db::getInstance()->ExecuteS($sql));
		
		$this->pagination($count);
	
		/* load javascript, css */
		$smarty->assign('_MODULE_DIR_', _MODULE_DIR_);
		/* -load javascript, css */
		
		$page = Tools::getValue('p');
		if ($page == null)
			$page = 0;
			
		$postes = $this->getPostes($page);
		
		/* display title */
		//$this->displayTitle();
		/* -display title */
		
		
		/* display all post */
		if ($postes != null)
		{
			$smarty->assign('pl_postes_empty', 0);
			$smarty->assign('pl_post_list', $postes);
        }
		else
		{
			$smarty->assign('pl_postes_empty', 1);
		}
		/* -display all post */
		
		require_once _PS_MODULE_DIR_.'plblog/frontent/tools/PlTools.php';		
		$plTools = new PlTools();		
		$smarty->assign('plTools', $plTools);
		$smarty->assign('pl_b_summary_character_count', Configuration::get('PL_B_SUMMARY_CHARACTER_COUNT'));
		$smarty->assign('pl_b_summary_character_count', Configuration::get('PL_B_SUMMARY_CHARACTER_COUNT'));
		$smarty->display(_PS_MODULE_DIR_.'plblog/frontent/tpl/post-all.tpl');
		$smarty->display(_PS_MODULE_DIR_.'plblog/frontent/tpl/pagination.tpl');
	}
	
	function getPostes($page = 0)
	{
		global $cookie;

		$leng = (Tools::getValue('n') ? Tools::getValue('n') : 10);
		$start = $leng * ($page == 0 ? 0 : $page-1);

		$end = $leng;

		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_post a
				LEFT JOIN '._DB_PREFIX_.'pl_blog_post_lang b
				ON (a.id_pl_blog_post= b.id_pl_blog_post)
				WHERE (a.id_pl_blog_category != 1) AND (a.post_status=1) AND (b.id_lang='.(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get("PS_LANG_DEFAULT")).')
				ORDER BY a.post_date_create DESC
				LIMIT '.$start.','.$end;
		return Db::getInstance()->ExecuteS($sql);
	}
	
	function getCountComment($id_pl_blog_post = null)
	{
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_comment a
		WHERE a.comment_status=2 AND a.id_pl_blog_post='.$id_pl_blog_post;
		
		return count(Db::getInstance()->ExecuteS($sql));
	}
	
	function stringOnLink($str)
	{
		$str = str_replace(" ", "-", str_replace("  ", " ", strtolower($str)));

		$arr = array("~", "`", "!", "@", "#","$", "%", "^", "&", "*", "(", ")","_", "+", "=", "|", "\\", '{', '}', '[', ']', "'", '"', ':', ';','<',",",'>',".",'/','?');		
		for ($i = 0; $i < count($arr); $i++)
		{	
			$str = str_replace($arr[$i], '', $str);
		}
		
		return $str;
	}
	
	public function getPostLink($post_title, $id_pl_blog_post)
	{
		global $link;
		if (Configuration::get('PS_REWRITING_SETTINGS') == 1)
		{
			$_link = "blog/".$post_title."_".$id_pl_blog_post.".html";		
			$l = $link->getPageLink($_link);
		}
		else
		{
			$_link = 'modules/plblog/frontent/details.php?plpn='.$post_title.'&plidp='.$id_pl_blog_post;
			$l = $link->getPageLink($_link);
		}
		
		return $l;
	}
	
	function substr($str, $start = 0, $end = null)
	{
		if ($end == null)
			$end = strlen($str);
		$str = substr(strip_tags($str), $start, $end);
		return $str;
	}
	
	function getCategoryName($id_pl_blog_category)
	{
		global $cookie;
		return Db::getInstance()->getValue("
			SELECT category_name FROM "._DB_PREFIX_."pl_blog_category_lang WHERE id_lang=".(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get("PS_LANG_DEFAULT"))." AND id_pl_blog_category=".$id_pl_blog_category."
		");
	}
	
	function getMetaCategoryById($id_pl_blog_category)
	{
		global $cookie;
		$sql = 'SELECT category_meta_title, category_meta_description, category_meta_keywords FROM '._DB_PREFIX_.'pl_blog_category_lang a
				WHERE a.id_lang='.(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT')).' AND a.id_pl_blog_category='.$id_pl_blog_category.'
			   ';
		$category = Db::getInstance()->getRow($sql);
		
		return $category;
	}
}