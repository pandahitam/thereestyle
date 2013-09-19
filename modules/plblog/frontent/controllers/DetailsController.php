<?php
class DetailsController extends FrontController
{
	private $comments = null;
	
	public function __construct()
	{
		//$this->php_self = 'modules/plblog/frontent/details.php';
		if (Configuration::get("PS_REWRITING_SETTINGS") == 1)
			$this->php_self = "blog/".Tools::getValue('plcn').'/'.Tools::getValue('plidp').'-'.Tools::getValue('plpn').".html";
		parent::__construct();
	}
	
	public function displayContent()
	{
		parent::displayContent();
		
		$this->display();
	}
	
	public function getCategoryNameRewrite($id_pl_blog_category)
	{
		global $cookie;
		return Db::getInstance()->getValue('
			SELECT link_rewrite FROM '._DB_PREFIX_.'pl_blog_category_lang
			WHERE id_lang = '.$cookie->id_lang.' AND id_pl_blog_category = '.$id_pl_blog_category.'
		');
	}
	
	function display()
	{
		global $smarty, $link, $cookie;		
		$pl_path = array();				
		session_start();		
		$pl_path[0]['link'] = $_SESSION['pl_a_path'] == null ? ($link->getPageLink('').'blog/all-post.html') : $_SESSION['pl_a_path'];		
		$pl_path[0]['name'] = $_SESSION['pl_a_name'] == null ? 'View all post' : $_SESSION['pl_a_name'];				
		$pl_path[1]['link'] = "http://";		
		$pl_path[1]['name'] = Db::getInstance()->getValue("SELECT post_title FROM "._DB_PREFIX_."pl_blog_post_lang WHERE id_lang = ".(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get("PS_LANG_DEFAULT"))." AND id_pl_blog_post = ".Tools::getValue("plidp")."		");
		$smarty->assign('pl_path', $pl_path);
		$smarty->display(_PS_MODULE_DIR_.'plblog/frontent/tpl/breadcrumb.tpl');
		
		$id_pl_blog_post = Tools::getValue('plidp');		
		$this->
		ents = $this->getCommentBy($id_pl_blog_post);
		
		/* load javascript, css*/
		$smarty->assign('pl_blog_post_detail_path_tinymce', __PS_BASE_URI__.'modules/plblog/frontent/js/jscripts/tiny_mce/tiny_mce.js');
		$smarty->assign('pl_blog_post_detail_path_css', __PS_BASE_URI__.'modules/plblog/frontent/css/style.css');
		/* -load javascript, css*/
		
		$data = false; 
		$plmsg = null;

		$smarty->assign('pl_blog_post_detail_display_message', '');
		if (Configuration::get('PL_SHOW_CAPTCHA_TO_COMMENT') == 1)
		{
			if (Tools::getValue('plsubmitcomment') == 'true')
			{
				$checkForm = $this->checkForm(Tools::getValue('author_name'), Tools::getValue('author_email'), Tools::getValue('comment_content'));	
		
				// capcha
				if( $_SESSION['security_code'] == $_POST['security_code'] && !empty($_SESSION['security_code'] ) ) 
				{
					/* display message */
					if ($checkForm)
						$data = $this->addComment();
					else
						$data = 0;
					$smarty->assign('pl_blog_post_detail_display_message', $data);
					//$smarty->assign('pl_blog_post_detail_message', __PS_BASE_URI__.'/img/admin/ok2.png');
					/* -display message */
					
					unset($_SESSION['security_code']);
				} else 
				{
					$_POST['plsecurity_code_msg'] = 'Invalid Security Code';
				}
			}
		}
		else
		{
			/* display message */
			$checkForm = $this->checkForm(Tools::getValue('author_name'), Tools::getValue('author_email'), Tools::getValue('comment_content'));	
			if ($checkForm)
				$data = $this->addComment();
			else
				$data = 0;
				
			$smarty->assign('pl_blog_post_detail_display_message', $data);
			$smarty->assign('pl_blog_post_detail_message', __PS_BASE_URI__.'/img/admin/ok2.png');
			/* -display message */
		}		
		
		/* display post content */
		$id_pl_blog_post = Tools::getValue('plidp');
			
		$post = $this->getPostById($id_pl_blog_post);
		
		if ($post != null)
		{
			$smarty->assign('pl_blog_post_display_detail', 1);
			$count_comment = count($this->ents);
			$smarty->assign('pl_blog_post_detail_count_comment', $count_comment);
			$smarty->assign('pl_blog_post_detail', $post);
			
			if (Configuration::get('PL_TBEP_SHOW') == 'YES')
			{
				$smarty->assign('pl_display_tags', 1);
				$url_rewrite = Configuration::get('PS_REWRITING_SETTINGS');
				$home = $link->getPageLink('');		
				$pl_data_tags = $this->getTags($post['id_pl_blog_post']);
				
				$smarty->assign('url_rewrite', $url_rewrite);
				$smarty->assign('home', $home);
				$smarty->assign('pl_data_tags', $pl_data_tags);
				$smarty->assign('_PS_MODULE_DIR_', _PS_MODULE_DIR_);
			}
			else
			{
				$smarty->assign('pl_display_tags', 0);
			}
		}
		else
		{
			$smarty->assign('pl_blog_post_display_detail', 0);
		}
		$allow = array();
		//$allow['id_pl_blog_category'] = $post['id_pl_blog_category'];
		
		if ($post['post_allow_comment'] == 1)
			$allow['post_allow_comment'] = 1;
		/* -display post content */
		
		/* display comment */
		$smarty->assign('pl_blog_post_detail_count_comments', count($this->ents));
		
		if ($this->ents != null)
		{
			$smarty->assign('pl_blog_post_detail_display', 1);
			$smarty->assign('pl_blog_post_detail_comments', $this->ents);
		}
		else
		{
			$smarty->assign('pl_blog_post_detail_display', 0);
		}
		/* -display comment */
		
		// return category allow comment
//		$category_allow_comment = Db::getInstance()->getValue("
//			SELECT category_allow_comment
//			FROM "._DB_PREFIX_."pl_blog_category
//			WHERE id_pl_blog_category=".$allow['id_pl_blog_category']."
//		");
		//var_dump($this->allowComment());die();
		if (/*$category_allow_comment && */$allow['post_allow_comment'] && $this->allowComment()) 
		{
			$smarty->assign('pl_blog_post_detail_display_form', 1);
			if (Tools::getValue('pl_comment_error') == 'true')
				$smarty->assign('pl_comment_error', 1);
			else
				$smarty->assign('pl_comment_error', 0);
				
			if (Tools::getValue('plauthor_name_msg') != null)
				$smarty->assign('plauthor_name_msg', 1);
			else 
				$smarty->assign('plauthor_name_msg', 0);
						
			if (Tools::getValue('plauthor_email_msg') != null)
				$smarty->assign('plauthor_email_msg', 1);
			else 
				$smarty->assign('plauthor_email_msg', 0);
							
			if (Tools::getValue('plcomment_content_msg') != null)
				$smarty->assign('plcomment_content_msg', 1);
			else 
				$smarty->assign('plcomment_content_msg', 0);
				
			if (Tools::getValue('plsecurity_code_msg') != null)
				$smarty->assign('plsecurity_code_msg', 1);
			else 
				$smarty->assign('plsecurity_code_msg', 0);
				
			$smarty->assign('plauthor_name_msg_content', Tools::getValue('plauthor_name_msg'));
			$smarty->assign('plauthor_email_msg_content', Tools::getValue('plauthor_email_msg'));
			$smarty->assign('plcomment_content_msg_content', Tools::getValue('plcomment_content_msg'));
			$smarty->assign('plsecurity_code_msg_content', Tools::getValue('plsecurity_code_msg'));
			
			$smarty->assign('author_name', Tools::getValue('author_name'));
			$smarty->assign('author_email', Tools::getValue('author_email'));
			$smarty->assign('comment_content', Tools::getValue('comment_content'));
			
			/* display captcha */
			if (Configuration::get('PL_SHOW_CAPTCHA_TO_COMMENT') == 1)
			{
				$smarty->assign('pl_display_captcha', 1);
			}
			else
			{
				$smarty->assign('pl_display_captcha', 0);
			}
			$smarty->assign('pl_ps_base_uri', __PS_BASE_URI__);
			/* -display captcha */
		}
		else
		{
			$smarty->assign('pl_blog_post_detail_display_form', 0);
		}
		//print_r($this->ents);
		$smarty->display(_PS_MODULE_DIR_.'plblog/frontent/tpl/post-detail.tpl');
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
	
	public function getTags($id_pl_blog_post)
	{
		global $cookie;
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_post_tag a1
				LEFT JOIN '._DB_PREFIX_.'pl_blog_tags_lang a2
				ON a1.id_pl_tag = a2.id_pl_blog_tags
				WHERE a1.id_pl_blog_post = '.((int)$id_pl_blog_post).' AND a2.id_lang='.$cookie->id_lang;
		$data = Db::getInstance()->ExecuteS($sql);

		return $data;
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
		$id_pl_blog_post = (int) Tools::getValue('plidp');
		if ($id_pl_blog_post != null)
		{
			$post = $this->getMetaPostById($id_pl_blog_post);
			$meta_title = $post['post_meta_title'];
			$meta_description = $post['post_meta_description'];
			$meta_keywords = $post['post_meta_keywords'];
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
	
	function getPostById($id_pl_blog_post = null)
	{
		global $cookie;
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_post a
				LEFT JOIN '._DB_PREFIX_.'pl_blog_post_lang b
				ON (a.id_pl_blog_post= b.id_pl_blog_post)
				WHERE (a.post_status=1) AND (b.id_lang='.(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get("PS_LANG_DEFAULT")).') AND a.id_pl_blog_post='.$id_pl_blog_post.'
				';
				
		return Db::getInstance()->getRow($sql);
	}
	
	function getCommentBy($id_pl_blog_post = null)
	{
		global $cookie;
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_comment a
				LEFT JOIN '._DB_PREFIX_.'pl_blog_comment_lang b
				ON (a.id_pl_blog_comment=b.id_pl_blog_comment) 
				WHERE b.id_lang='.(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get("PS_LANG_DEFAULT")).' AND '.' (a.comment_status=2) AND (id_pl_blog_post='.$id_pl_blog_post.')';
		return (Db::getInstance()->ExecuteS($sql));
		//$db = Db::getInstance()->ExecuteS($sql);
		//var_dump($db);
	}
	
	function getMetaPostById($id_pl_blog_post)
	{
		global $cookie;
		return Db::getInstance()->getRow("
			SELECT * FROM "._DB_PREFIX_."pl_blog_post_lang WHERE id_lang=".(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT'))." AND id_pl_blog_post=".$id_pl_blog_post."
		");
	}
	
	function getDateCreate()
	{
		$date = getdate();
		return $date['year'].'/'.$date['mon'].'/'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'];
	}
	
	function isEmail($email)
	{
		return (empty($email) OR preg_match('/^[a-z0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z0-9!#$%&\'*+\/=?^`{}|~_-]*@[a-z0-9]+[._a-z0-9-]*\.[a-z0-9]+$/ui', $email));
	}
	
	function addComment()
	{
		if (Tools::getValue('plsubmitcomment') != 'true')
			return 0;
		$author_name = Tools::getValue('author_name');
		$author_email = Tools::getValue('author_email');
		$comment_content = pSQL($_REQUEST['comment_content'], true);
		
		$id_pl_blog_post = Tools::getValue('plidp');
		
		// insert table pl_blog_comment
		$sql = "
			INSERT INTO "._DB_PREFIX_."pl_blog_comment ( id_pl_blog_post, comment_date_create, comment_status, comment_author_email, comment_author_name)
			VALUES(".$id_pl_blog_post.",'".$this->getDateCreate()."', 1, '".$author_email."', '".$author_name."')
		";
		Db::getInstance()->ExecuteS($sql);
		
		// get id_pl_blog_comment
		$rs = Db::getInstance()->Executes('SELECT * FROM '._DB_PREFIX_.'pl_blog_comment ORDER BY id_pl_blog_comment DESC LIMIT 0,1');
		$row = $rs[0];
		$id_pl_blog_comment = (int) $row['id_pl_blog_comment'];
		
		// insert table pl_blog_comment_lang	
		$langs = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'lang');		
		foreach ($langs as $lang)
		{
			$id_lang = $lang['id_lang'];
			$sql = "
				INSERT INTO "._DB_PREFIX_."pl_blog_comment_lang(id_pl_blog_comment, id_lang, comment_content)
				VALUES(".$id_pl_blog_comment.",".$id_lang.", '".$comment_content."')
			";
			
			Db::getInstance()->ExecuteS($sql);
		}
		
		return 1;
	}
	
	function isName($name)
	{
		return preg_match('/^[^<>;=#{}]*$/u', $name);
	}
	
	function checkForm($author_name, $author_email, $comment_content)
	{
		$rs = true;
		if (!$this->isEmail($author_email))
		{
			$_POST['plauthor_email_msg'] = 'Invalid e-mail address';
			$rs = false;
		}			
		elseif (empty($author_email))
		{
			$_POST['plauthor_email_msg'] = 'Email address required';
			$rs = false;
		}
		
		if (!$this->isName($author_name) || $author_name == null) 
		{
			$_POST['plauthor_name_msg'] = 'Full name required';
			$rs = false;
		}
		
		if ( strlen($author_name) > 50 ) 
		{
			$_POST['plauthor_name_msg'] = 'Full name is too long (50 chars max)';
			$rs = false;
		}
		
		if ($comment_content == null)
		{
			$_POST['plcomment_content_msg'] = 'Comment required';
			$rs = false;
		}
		
		if ($rs == false)
			$_POST['pl_comment_error'] = 'true';
		return $rs;
	}
	
	function allowComment()	
	{
		$allowComment = Configuration::get('PL_ALLOW_COMMENTS_BY_GUESTS');
		if (!$allowComment AND !$cookie->id_customer) {
			return false;
		}
		return true;
	}
}