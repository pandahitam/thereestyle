<?php
class PlBlog extends Module
{
	private $tabParentClass = null;
	
	private $tabClassBlog = 'AdminPlBlog';
	private $tabNameBlog = null;
	//private $idTabParentBlog = null;
	private $_html_categories = '';
	private $psVersion = false;
	
	function __construct()
	{
		$this->name = 'plblog';
		$this->displayName = $this->l('Blog');
		$this->description = $this->l('Free Blog Module');
		$this->version = '1.1.8';
		$this->author = 'PrestaLive';
		$this->tab = 'front_office_features';
		
		parent::__construct();
		
		/* Check version less than 1.4.5.1 */
		/*$version_mask = explode('.', _PS_VERSION_, 3);
	   	if ($version_mask[1] < 4 OR ($version_mask[1] = 4 AND $version_mask[2] < 5))
	   		$this->psVersion = true;*/
	}
	
	function initTab()
	{
		$this->tabParentClass = 'AdminTools';
		
		$this->tabClassBlog = 'AdminPlBlog';
		$this->tabNameBlog = 'Blog';
		//$this->idTabParentBlog = $this->getIdTabFromClassName($this->tabParentClass);
	}
	
	function existsTab($tabClass)
	{
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT id_tab AS id 
		FROM `'._DB_PREFIX_.'tab` t 
		WHERE LOWER(t.`class_name`) = \''.pSQL($tabClass).'\'');
		if (count($result) == 0) 
			return false;
		return true;
	}

	function installConfiguration()
	{
		Configuration::updateValue('PL_DISPLAY_CATEGORY', 'Left');
		Configuration::updateValue('PL_DISPLAY_TAG', 'Left');
		Configuration::updateValue('PL_NEWS_CATEGORIES_TO_SHOW', 10);
		Configuration::updateValue('PL_SHOW_CAPTCHA_TO_COMMENT', '1');				
		Configuration::updateValue('PL_ALLOW_COMMENTS_BY_GUESTS', '1');
		Configuration::updateValue('PL_LASTEST_POST', 'Left');
		Configuration::updateValue('PL_OP_LASTEST_POST', 5);
		Configuration::updateValue('PL_TBEP_SHOW', 'YES');
		Configuration::updateValue('PL_COLP_MAXIMUM', 150);
		Configuration::updateValue('PL_B_SUMMARY_CHARACTER_COUNT', 250);
	}

	function uninstallConfiguration()
	{
		Configuration::deleteByName('PL_DISPLAY_CATEGORY');
		Configuration::deleteByName('PL_DISPLAY_TAG');
		Configuration::deleteByName('PL_NEWS_CATEGORIES_TO_SHOW');
		Configuration::deleteByName('PL_SHOW_CAPTCHA_TO_COMMENT');				
		Configuration::deleteByName('PL_ALLOW_COMMENTS_BY_GUESTS');
		Configuration::deleteByName('PL_LASTEST_POST');
		Configuration::deleteByName('PL_OP_LASTEST_POST');
		Configuration::deleteByName('PL_COLP_MAXIMUM');
		Configuration::deleteByName('PL_B_SUMMARY_CHARACTER_COUNT');
	}
	
	function install()
	{
		if (!parent::install())
			return false;
		
		$this->installConfiguration();
		
		
		/*Install Tab*/
		if (!$this->existsTab($this->tabClassBlog))
		{
			$this->initTab();
			if(!$this->addTab($this->tabNameBlog, $this->tabClassBlog, 9))
				return false;
			
			/*Init database*/
			if (!$this->installDb())
				return false;				
		}
		
		$this->registerHook('leftColumn');
		$this->registerHook('rightColumn');
		$this->registerHook('header');
		
		$this->installRewriteURL();
		
		return true;
	}
	
	function uninstall()
	{
		if (!parent::uninstall())
			return false;
			
		$this->uninstallRewriteURL();
		
		$this->uninstallConfiguration();
		
		if(!$this->removeTab($this->tabClassBlog))
			return false;

		if(!$this->uninstallDb())
			return false;
		
		return true;
	}
	
	function installRewriteURL()
	{		
		$link_list_post = 'modules/plblog/frontent/list-post.php?plidc=$1&plcn=$2&';
		$link_list_post_rewrite = 'blog/([0-9]*)_([a-zA-Z0-9-]*)\.html';
		
		$link_details = 'modules/plblog/frontent/details.php?plcn=$1&plidp=$2&plpn=$3&';
		$link_details_rewrite = 'blog/([a-zA-Z0-9-]*)/([0-9]*)-([a-zA-Z0-9-]*)\.html';
		
		$link_tags = 'modules/plblog/frontent/list-tags.php?pltn=$1&plidt=$2&';
		$link_tags_rewrite = 'blog/tag/([a-zA-Z0-9-]*)_([0-9]*)\.html';
		
		$link_all_post = 'modules/plblog/frontent/all-post';
		$link_all_post_rewrite = 'blog/all-post.html';
		
		// rewrite url list-post.php	
		$sql = "INSERT INTO "._DB_PREFIX_."meta (id_meta ,page)
				VALUES (NULL , '".$link_list_post."');
				";
		Db::getInstance()->ExecuteS($sql);
		
		// rewrite url details.php
		$sql = "INSERT INTO "._DB_PREFIX_."meta (id_meta , page)
				VALUES (NULL , '".$link_details."');";
		Db::getInstance()->ExecuteS($sql);	
		
		// rewrite url list-tags.php
		$sql = "INSERT INTO "._DB_PREFIX_."meta (id_meta, page)
				VALUES(NULL, '".$link_tags."');
				";
		Db::getInstance()->ExecuteS($sql);
		
		// rewrite url all-post.html.php
		$sql = "INSERT INTO "._DB_PREFIX_."meta (id_meta, page)
				VALUES(NULL, '".$link_all_post."');
				";
		Db::getInstance()->ExecuteS($sql);
		
		//$langs = Language::getLanguages();
		$langs = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'lang');
		foreach ($langs as $lang)
		{
			$id_lang = $lang['id_lang'];
			
			// list-post
			$id_meta = $this->getIdMetaByPage($link_list_post);
			$sql = "INSERT INTO "._DB_PREFIX_."meta_lang (id_meta ,	id_lang ,title , url_rewrite)
					VALUES (".$id_meta.", ".$id_lang.", 'List post' , '".$link_list_post_rewrite."');";
			Db::getInstance()->ExecuteS($sql);	

			// details
			$id_meta = $this->getIdMetaByPage($link_details);
			$sql = "INSERT INTO "._DB_PREFIX_."meta_lang (id_meta ,	id_lang ,title ,url_rewrite	)
					VALUES (".$id_meta.", ".$id_lang.", 'Post detail' , '".$link_details_rewrite."');";
			Db::getInstance()->ExecuteS($sql);				
			
			// tags
			$id_meta = $this->getIdMetaByPage($link_tags);
			$sql = "INSERT INTO "._DB_PREFIX_."meta_lang (id_meta, id_lang, title, url_rewrite )
					VALUES (".$id_meta.", ".$id_lang.", 'List tags', '".$link_tags_rewrite."');";
			Db::getInstance()->ExecuteS($sql);
			
			// all-post.html
			$id_meta = $this->getIdMetaByPage($link_all_post);
			$sql = "INSERT INTO "._DB_PREFIX_."meta_lang (id_meta, id_lang, title, url_rewrite )
					VALUES (".$id_meta.", ".$id_lang.", 'All post', '".$link_all_post_rewrite."');";
			Db::getInstance()->ExecuteS($sql);			
		}
	}
	
	function getIdMetaByPage($page)
	{
		$sql = "SELECT id_meta FROM "._DB_PREFIX_."meta WHERE page='".$page."'";
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
		return $row['id_meta'];
	}
	
	function uninstallRewriteURL()
	{	
		$link_list_post = 'modules/plblog/frontent/list-post.php?plidc=$1&plcn=$2&';
		$link_list_post_rewrite = 'blog/([0-9]*)_([a-zA-Z0-9-]*)\.html';
		
		$link_details = 'modules/plblog/frontent/details.php?plcn=$1&plidp=$2&plpn=$3&';
		$link_details_rewrite = 'blog/([a-zA-Z0-9-]*)/([0-9]*)-([a-zA-Z0-9-]*)\.html';
		
		$link_tags = 'modules/plblog/frontent/list-tags.php?pltn=$1&plidt=$2&';
		$link_tags_rewrite = 'blog/tag/([a-zA-Z0-9-]*)_([0-9]*)\.html';
		
		$link_all_post = 'modules/plblog/frontent/all-post';
		$link_all_post_rewrite = 'blog/all-post.html';
		
		$sql = "DELETE FROM "._DB_PREFIX_."meta WHERE page='".$link_list_post."'";
		Db::getInstance()->ExecuteS($sql);
		
		$sql = "DELETE FROM "._DB_PREFIX_."meta_lang WHERE url_rewrite='".$link_list_post_rewrite."'";
		Db::getInstance()->ExecuteS($sql);
		
		$sql = "DELETE FROM "._DB_PREFIX_."meta WHERE page='".$link_tags."'";
		Db::getInstance()->ExecuteS($sql);		
		
		$sql = "DELETE FROM "._DB_PREFIX_."meta_lang WHERE url_rewrite='".$link_tags_rewrite."'";
		Db::getInstance()->ExecuteS($sql);		
		
		$sql = "DELETE FROM "._DB_PREFIX_."meta WHERE page='".$link_details."'";
		Db::getInstance()->ExecuteS($sql);
		
		$sql = "DELETE FROM "._DB_PREFIX_."meta_lang WHERE url_rewrite='".$link_details_rewrite."'";
		Db::getInstance()->ExecuteS($sql);		
		
		$sql = "DELETE FROM "._DB_PREFIX_."meta WHERE page='".$link_all_post."'";
		Db::getInstance()->ExecuteS($sql);
		
		$sql = "DELETE FROM "._DB_PREFIX_."meta_lang WHERE url_rewrite='".$link_all_post_rewrite."'";
		Db::getInstance()->ExecuteS($sql);		
	}
	
	private function getIDTabParent($tabClass)
	{
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT id_tab AS id 
		FROM `'._DB_PREFIX_.'tab` t 
		WHERE LOWER(t.`class_name`) = \''.pSQL($tabClass).'\'');
		//echo print_r($result).'----'.$result['id'];
		return $result['id'];
	}

	function installDb()
	{
		/* Table category and category_lang*/
		// Table category
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_category';
		Db::getInstance()->ExecuteS($sql);
		
		$sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'pl_blog_category(
				id_pl_blog_category int(11) NOT NULL AUTO_INCREMENT,
				category_url nvarchar(1000) NULL,
				category_parent int(11) NULL,
				category_date_create datetime NULL,
				category_status boolean NULL,
				position int(11) NULL,
				category_allow_comment bool NULL,
				PRIMARY KEY (id_pl_blog_category)
				)';
		Db::getInstance()->ExecuteS($sql);
		
		$sql =' INSERT INTO '._DB_PREFIX_.'pl_blog_category(category_status, position) VALUES(1, 0) ';
		Db::getInstance()->ExecuteS($sql);

		// Table category_lang
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_category_lang';
		Db::getInstance()->ExecuteS($sql);
		
		$sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'pl_blog_category_lang(
				id_pl_blog_category int(11) NOT NULL AUTO_INCREMENT,
				id_lang int(11) NOT NULL,
				category_name nvarchar(500) NULL,
				category_description nvarchar(2000) NULL,
				category_meta_title nvarchar(500) NULL,
				category_meta_description nvarchar(1000) NULL,
				category_meta_keywords nvarchar(1000) NULL,
				link_rewrite nvarchar(1000) NULL,
				PRIMARY KEY(id_pl_blog_category, id_lang)
				)';
		Db::getInstance()->ExecuteS($sql);
		
		$langs = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'lang');
		foreach ($langs as $lang)
		{
			$sql = ' INSERT INTO '._DB_PREFIX_.'pl_blog_category_lang(id_pl_blog_category, id_lang, category_name) VALUES(1, '.$lang['id_lang'].',"") ';
			Db::getInstance()->ExecuteS($sql);
		}
		
		/*Table post and post_lang*/
		// Table post
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_post';
		Db::getInstance()->ExecuteS($sql);
		
		$sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'pl_blog_post(
				id_pl_blog_post int(11) NOT NULL AUTO_INCREMENT,
				id_pl_blog_category int(11) NULL,
				id_pl_blog_tags int(11) NULL,
				post_date_create datetime NULL,
				post_allow_comment boolean NULL,
				post_status boolean NULL,
				PRIMARY KEY(id_pl_blog_post)
				)';
		Db::getInstance()->ExecuteS($sql);
		
		// Table post_lang
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_post_lang';
		Db::getInstance()->ExecuteS($sql);
		
		$sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'pl_blog_post_lang(
				id_pl_blog_post int(11) NOT NULL AUTO_INCREMENT,
				id_lang int(11) NOT NULL,
				post_title nvarchar(2000) NULL,
				post_description text NULL,
				post_meta_title nvarchar(500) NULL,
				post_meta_description nvarchar(1000) NULL,
				post_meta_keywords nvarchar(1000) NULL,
				link_rewrite nvarchar(1000) NULL,
				PRIMARY KEY(id_pl_blog_post, id_lang)
				)';
		Db::getInstance()->ExecuteS($sql);
		
		/* Table post_tag */
		$sql = "CREATE TABLE IF NOT EXISTS "._DB_PREFIX_."pl_blog_post_tag(
				id_pl_blog_post int(11) NOT NULL,
				id_pl_tag int(11) NOT NULL,
				PRIMARY KEY (id_pl_blog_post, id_pl_tag)
				)";
		Db::getInstance()->ExecuteS($sql);
		
		/*Table comment and comment_lang*/
		// Table comment
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_comment';
		Db::getInstance()->ExecuteS($sql);
		
		$sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'pl_blog_comment(
				id_pl_blog_comment int(11) NOT NULL AUTO_INCREMENT,
				id_pl_blog_post int(11) NULL,
				comment_author_name nvarchar(200) NULL,
				comment_author_email nvarchar(200) NULL,
				comment_date_create datetime NULL,
				comment_status int(11) NULL,
				PRIMARY KEY(id_pl_blog_comment)
				)';
		Db::getInstance()->ExecuteS($sql);		
		
		// Table comment_lang
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_comment_lang';
		Db::getInstance()->ExecuteS($sql);
		
		$sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'pl_blog_comment_lang(
				id_pl_blog_comment int(11) NOT NULL AUTO_INCREMENT,
				id_lang int(11) NOT NULL,
				comment_content nvarchar(7000) NULL,
				PRIMARY KEY(id_pl_blog_comment, id_lang)
				)';
		Db::getInstance()->ExecuteS($sql);		
		
		/* Table comment_status */
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_comment_status';
		Db::getInstance()->ExecuteS($sql);
		
		$sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'pl_blog_comment_status(
					id_pl_blog_comment_status int(11) NOT NULL AUTO_INCREMENT,
					name nvarchar(10) NOT NULL,
					PRIMARY KEY (id_pl_blog_comment_status)
					)';
		Db::getInstance()->ExecuteS($sql);
		
		$comment_status_name = array('Pending', 'Approved' , 'Spam' );
		for ($i = 0; $i < count($comment_status_name); $i++)
		{
			$sql = 'INSERT INTO '._DB_PREFIX_.'pl_blog_comment_status(name) VALUES("'.$comment_status_name[$i].'")';
			Db::getInstance()->ExecuteS($sql);
		}
		
		/* Table tags and tags_lang */
		// Table tags
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_tags';
		Db::getInstance()->ExecuteS($sql);
		
		$sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'pl_blog_tags(
				id_pl_blog_tags int(11) NOT NULL AUTO_INCREMENT,
				tags_url nvarchar(1000) NULL,
				tags_date_create datetime NULL,
				PRIMARY KEY(id_pl_blog_tags)
				)';
		Db::getInstance()->ExecuteS($sql);		
		
		$sql = "INSERT INTO "._DB_PREFIX_."pl_blog_tags (tags_url)
				VALUES('')
			   ";
		Db::getInstance()->ExecuteS($sql);
		
		// Table tags_lang
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_tags_lang';
		Db::getInstance()->ExecuteS($sql);
		
		$sql = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'pl_blog_tags_lang(
				id_pl_blog_tags int(11) NOT NULL AUTO_INCREMENT,
				id_lang int(11) NOT NULL,
				tags_name nvarchar(500) NULL,
				tags_description nvarchar(7000) NULL,
				PRIMARY KEY(id_pl_blog_tags, id_lang)
				)';
		Db::getInstance()->ExecuteS($sql);	
		
		$langs = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'lang');
		foreach ($langs as $lang)
		{
			$id_lang = $lang['id_lang'];
			$sql = "INSERT INTO "._DB_PREFIX_."pl_blog_tags_lang (id_pl_blog_tags, id_lang, tags_name)
					VALUES(1, ".$id_lang.", '')
				   ";
			Db::getInstance()->ExecuteS($sql);
		}
		
		return true;
	}
	
	function uninstallDb()
	{
		// delete table faqcat
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_category';
		Db::getInstance()->ExecuteS($sql);
		
		// delete table faqcat_lang
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_category_lang';
		Db::getInstance()->ExecuteS($sql);		
		
		// delete table post
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_post';
		Db::getInstance()->ExecuteS($sql);
		
		// delete table post_lang
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_post_lang';
		Db::getInstance()->ExecuteS($sql);
		
		// delete table post_tag
		$sql = "DROP TABLE IF EXISTS "._DB_PREFIX_."pl_blog_post_tag";
		Db::getInstance()->ExecuteS($sql);
		
		// delete table comment
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_comment';
		Db::getInstance()->ExecuteS($sql);
		
		// delete table comment_lang
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_comment_lang';
		Db::getInstance()->ExecuteS($sql);
		
		// delete table tags
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_tags';
		Db::getInstance()->ExecuteS($sql);
		
		// delete table comment_lang
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_tags_lang';
		Db::getInstance()->ExecuteS($sql);
		
		// delete table comment_status
		$sql = 'DROP TABLE IF EXISTS '._DB_PREFIX_.'pl_blog_comment_status';
		Db::getInstance()->ExecuteS($sql);
		
		return true;
	}

	private function addTab($tabName, $tabClass , $id_parent)
	{
		$tab = new Tab();	
		$langs = Language::getLanguages();
		foreach ($langs as $lang) {
			$tab->name[$lang['id_lang']] = $tabName;
		}
		$tab->class_name = $tabClass;
		$tab->module = $this->name;
		$tab->id_parent = $id_parent;
		if(!$tab->save())
			return false;
			
		return true;
	}
  
  private function removeTab($tabClass)
  {
    $idTab = Tab::getIdFromClassName($tabClass);
    if($idTab != 0)
    {
      $tab = new Tab($idTab);
      $tab->delete();
      return true;
    }
    return false;
  }
	
	
	function getIdTabFromClassName($tabName)
	{
		$sql = 'SELECT id_tab FROM '._DB_PREFIX_.'tab WHERE class_name="'.$tabName.'"';
		$tab = Db::getInstance()->getRow($sql);
		return intval($tab['id_tab']);
	}
	public function hookHeader($params)
	{
		global $smarty,$cookie;
        $smarty->assign('this_path', $this->_path);
        return $this->display(__FILE__, '/frontent/tpl/plblogheader.tpl');
	}
	function hookRightColumn($params)
	{	
		global $smarty, $link;
		
		$smarty->assign('PL_DISPLAY_CATEGORY', Configuration::get('PL_DISPLAY_CATEGORY'));
		$smarty->assign('PL_DISPLAY_TAG', Configuration::get('PL_DISPLAY_TAG'));
		$smarty->assign('PL_COLP_MAXIMUM', Configuration::get('PL_COLP_MAXIMUM'));
		$data =  $this->getCategories();

		$_index_data = array();
		$_index_data[0] = 0;
		for ($i = 1; $i < count($data); $i++)
			$_index_data[$i] = 1;

		for ($i = 1; $i < count($data); $i++)
		{
			$row = $data[$i];
			if ($_index_data[$i] == 1)
			{
				if ($row['category_parent'] == 1)
				{
					$_index_data[$i] = 0;
					
					$url_rewrite = Configuration::get('PS_REWRITING_SETTINGS');
					if ($url_rewrite == 1)
						$this->_html_categories .= '<a href="'.$link->getPageLink('blog/'.$this->str_url($row['category_name']).'.'.$row['id_pl_blog_category'].'.html').'">'.$row['category_name'].'</a><br/>';
					else
						$this->_html_categories .= '<a href="'.$link->getPageLink('modules/plblog/frontent/list-post.php?plcn='.$this->str_url($row['category_name'])).'&plidc='.$row['id_pl_blog_category'].'">'.$row['category_name'].'</a><br/>';
					
					$_index_data = $this->getHtmlCategories($data, $_index_data, $row, '');
				}
			}
		}
		
		$smarty->assign('_html_categories', $this->_html_categories);			
		
		$smarty->assign('home', $link->getPageLink(''));
		$smarty->assign('url_rewrite', Configuration::get('PS_REWRITING_SETTINGS'));
		
		$tags = $this->getTags();
		$smarty->assign('tags', $tags);
		$smarty->assign('count_tags', (count($tags) >= 1 ? 1 : 0));
		
		// lastest post
		require_once _PS_MODULE_DIR_.'plblog/frontent/tools/PlTools.php';
		$plTools = new PlTools();		
		$smarty->assign('plTools', $plTools);
		
		$DISPLAY_LASTEST_POST = Configuration::get('PL_LASTEST_POST');
		$smarty->assign('DISPLAY_LASTEST_POST', $DISPLAY_LASTEST_POST);
		$pl_lastest_post = $this->getLastestPost();
		$smarty->assign('pl_lastest_post', $pl_lastest_post);
		
		
		// tree
		global $cookie;
		$maxdepth = Configuration::get('BLOCK_CATEG_MAX_DEPTH');
		$result = $this->getCategories();
		$resultParents = array();
		$resultIds = array();

		foreach ($result as &$row)
		{
			// $resultParents[$row['id_parent']][] = &$row;
			// $resultIds[$row['id_category']] = &$row;
			$resultParents[$row['category_parent']][] = &$row;
			$resultIds[$row['id_pl_blog_category']] = &$row;
		}

		$blockCategTree = $this->getTree($resultParents, $resultIds, Configuration::get('BLOCK_CATEG_MAX_DEPTH'));
		unset($resultParents);
		unset($resultIds);
		$isDhtml = (Configuration::get('BLOCK_CATEG_DHTML') == 1 ? true : false);
		
			if (Tools::getValue('plidc'))
			{
				//$cookie->last_visited_category = $id_category;
				$smarty->assign('pl_currentCategoryId', Tools::getValue('plidc'));
			}
			
		$smarty->assign('pl_blockCategTree', $blockCategTree);

		if (file_exists(_PS_THEME_DIR_.'modules/plblog/frontent/tpl/blockcategories.tpl'))
			$smarty->assign('pl_branche_tpl_path', _PS_THEME_DIR_.'modules/blockcategories/category-tree-branch.tpl');
		else
			$smarty->assign('pl_branche_tpl_path', _PS_MODULE_DIR_.'plblog/frontent/tpl/category-tree-branch.tpl');
		$smarty->assign('pl_isDhtml', $isDhtml);
		return $this->display(__FILE__, 'frontent/tpl/Blog_r.tpl');
	}
	function hookLeftColumn($params)
	{
		global $smarty, $link;
		
		$smarty->assign('PL_DISPLAY_CATEGORY', Configuration::get('PL_DISPLAY_CATEGORY'));
		$smarty->assign('PL_DISPLAY_TAG', Configuration::get('PL_DISPLAY_TAG'));
		$smarty->assign('PL_COLP_MAXIMUM', Configuration::get('PL_COLP_MAXIMUM'));
		$data =  $this->getCategories();

		$_index_data = array();
		$_index_data[0] = 0;
		for ($i = 1; $i < count($data); $i++)
			$_index_data[$i] = 1;

		for ($i = 1; $i < count($data); $i++)
		{
			$row = $data[$i];
			if ($_index_data[$i] == 1)
			{
				if ($row['category_parent'] == 1)
				{
					$_index_data[$i] = 0;
					
					$url_rewrite = Configuration::get('PS_REWRITING_SETTINGS');
					if ($url_rewrite == 1)
						$this->_html_categories .= '<a href="'.$link->getPageLink('blog/'.$this->str_url($row['category_name']).'.'.$row['id_pl_blog_category'].'.html').'">'.$row['category_name'].'</a><br/>';
					else
						$this->_html_categories .= '<a href="'.$link->getPageLink('modules/plblog/frontent/list-post.php?plcn='.$this->str_url($row['category_name'])).'&plidc='.$row['id_pl_blog_category'].'">'.$row['category_name'].'</a><br/>';
					
					$_index_data = $this->getHtmlCategories($data, $_index_data, $row, '');
				}
			}
		}
		
		$smarty->assign('_html_categories', $this->_html_categories);			
		
		$smarty->assign('home', $link->getPageLink(''));
		$smarty->assign('url_rewrite', Configuration::get('PS_REWRITING_SETTINGS'));
		
		$tags = $this->getTags();
		$smarty->assign('tags', $tags);
		$smarty->assign('count_tags', (count($tags) >= 1 ? 1 : 0));
		
		// lastest post
		require_once _PS_MODULE_DIR_.'plblog/frontent/tools/PlTools.php';
		$plTools = new PlTools();		
		$smarty->assign('plTools', $plTools);
		
		$DISPLAY_LASTEST_POST = Configuration::get('PL_LASTEST_POST');
		$smarty->assign('DISPLAY_LASTEST_POST', $DISPLAY_LASTEST_POST);
		$pl_lastest_post = $this->getLastestPost();
		$smarty->assign('pl_lastest_post', $pl_lastest_post);
		
		
		// tree
		global $cookie;
		$maxdepth = Configuration::get('BLOCK_CATEG_MAX_DEPTH');
		$result = $this->getCategories();
		$resultParents = array();
		$resultIds = array();

		foreach ($result as &$row)
		{
			// $resultParents[$row['id_parent']][] = &$row;
			// $resultIds[$row['id_category']] = &$row;
			$resultParents[$row['category_parent']][] = &$row;
			$resultIds[$row['id_pl_blog_category']] = &$row;
		}

		$blockCategTree = $this->getTree($resultParents, $resultIds, Configuration::get('BLOCK_CATEG_MAX_DEPTH'));
		unset($resultParents);
		unset($resultIds);
		$isDhtml = (Configuration::get('BLOCK_CATEG_DHTML') == 1 ? true : false);
		
			if (Tools::getValue('plidc'))
			{
				//$cookie->last_visited_category = $id_category;
				$smarty->assign('pl_currentCategoryId', Tools::getValue('plidc'));
			}
			
		$smarty->assign('pl_blockCategTree', $blockCategTree);

		if (file_exists(_PS_THEME_DIR_.'modules/plblog/frontent/tpl/blockcategories.tpl'))
			$smarty->assign('pl_branche_tpl_path', _PS_THEME_DIR_.'modules/blockcategories/category-tree-branch.tpl');
		else
			$smarty->assign('pl_branche_tpl_path', _PS_MODULE_DIR_.'plblog/frontent/tpl/category-tree-branch.tpl');
		$smarty->assign('pl_isDhtml', $isDhtml);
			
		return $this->display(__FILE__, 'frontent/tpl/Blog_l.tpl');
	}
	
	public function getTree($resultParents, $resultIds, $maxDepth, $id_category = 1, $currentDepth = 0)
	{
		global $link;

		$children = array();
		if (isset($resultParents[$id_category]) AND sizeof($resultParents[$id_category]) AND ($maxDepth == 0 OR $currentDepth < $maxDepth))
			foreach ($resultParents[$id_category] as $subcat)
				$children[] = $this->getTree($resultParents, $resultIds, $maxDepth, $subcat['id_pl_blog_category'], $currentDepth + 1);
		if (!isset($resultIds[$id_category]))
			return false;
		return array('id' => $id_category, 'link' => $this->getCategoryLink($id_category, $resultIds[$id_category]['link_rewrite']),
					 'name' => $resultIds[$id_category]['category_name'], 'desc'=> $resultIds[$id_category]['category_description'],
					 'children' => $children);
	}
	
	public function getCategoryLink($id_pl_blog_category, $link_rewrite)
	{
		global $link;
		if (Configuration::get("PS_REWRITING_SETTINGS") == 0)
		{
			$url = $link->getPageLink('').'modules/plblog/frontent/list-post.php?plidc='.$id_pl_blog_category.'&plcn='.$link_rewrite;
			return $url;
		}
		else
		{
			$url = $link->getPageLink('blog/'.$id_pl_blog_category.'_'.$link_rewrite.'.html');
			return $url;
		}
	}
	
	function getLastestPost()
	{
		global $cookie;
		
		// PL_BL_NUMS_ROW_LP - numbers of row lastest post for display
		$post_length = Configuration::get('PL_OP_LASTEST_POST');
		// if ($post_length == null)
			// $post_length = 2;
		
		return Db::getInstance()->ExecuteS("
			SELECT * FROM "._DB_PREFIX_."pl_blog_post_lang a1
			INNER JOIN "._DB_PREFIX_."pl_blog_post a2
			ON a1.id_pl_blog_post=a2.id_pl_blog_post
			WHERE (a2.id_pl_blog_category != 1) AND a1.id_lang = ".$cookie->id_lang."
			ORDER BY a2.post_date_create DESC
			LIMIT 0, ".$post_length."
		");
	}
	
	function replaceTitleLink($title)
	{
		return str_replace(' ', '-', str_replace('  ', ' ', $title));
	}
	
	
	
	function displayCategories()
	{
		global $smarty, $link;
		$data =  $this->getCategories();

		$_index_data = array();
		$_index_data[0] = 0;
		for ($i = 1; $i < count($data); $i++)
			$_index_data[$i] = 1;
			
		for ($i = 1; $i < count($data); $i++)
		{
			$row = $data[$i];
			if ($_index_data[$i] == 1)
			{
				if ($row['category_parent'] == 1)
				{
					$_index_data[$i] = 0;
					
					$url_rewrite = Configuration::get('PS_REWRITING_SETTINGS');
					if ($url_rewrite == 1)
						$this->_html_categories .= '<a href="'.$link->getPageLink('list-post/page-0/'.$row['id_pl_blog_category'].'-'.$this->str_url($row['category_name']).'.html">'.$row['category_name']).'</a><br/>';
					else
						$this->_html_categories .= '<a href="'.$link->getPageLink('modules/plblog/frontent/list-post.php?plpage=0&plidc='.$row['id_pl_blog_category'].'&plcn='.$this->str_url($row['category_name'])).'">'.$row['category_name'].'</a><br/>';
					
					$_index_data = $this->getHtmlCategories($data, $_index_data, $row, '');
				}
			}
		}
		
		$smarty->assign('_html_categories', $this->_html_categories);		
	}
	
	function getHtmlCategories($data, $_index_data, $row, $html_categories = '', $_vt = 1)
	{	
		global $link;
		
		for ($j = 0; $j < count($data); $j++)
		{
			$category = $data[$j];
			if ($_index_data[$j] == 1)
			{					
				if ($category['category_parent'] == $row['id_pl_blog_category'])
				{
					$_index_data[$j] = 0;	
					
					$url_rewrite = Configuration::get('PS_REWRITING_SETTINGS');
					$this->_html_categories .= $this->sp($_vt);
					if ($url_rewrite == 1)
						$this->_html_categories .= '<a href="'.$link->getPageLink('blog/'.$this->str_url($category['category_name']).'.'.$category['id_pl_blog_category'].'.html').'">'.$category['category_name'].'</a><br/>';
					else
						$this->_html_categories .= '<a href="'.$link->getPageLink('modules/plblog/frontent/list-post.php?plcn='.$this->str_url($category['category_name'])).'&plidc='.$category['id_pl_blog_category'].'">'.($category['category_name']).'</a><br/>';
														
					$_index_data = $this->getHtmlCategories($data, $_index_data, $category, $html, ($_vt+1));
				}
			}
		}
		
		return $_index_data;
	}
	
	function str_url($str)
	{
		return strtolower(str_replace(' ', '-', str_replace('  ', ' ', $str))); 
	}
	
	function getTags()
	{
		global $cookie;
		$sql = 'SELECT * 
			FROM '._DB_PREFIX_.'pl_blog_tags a
			LEFT JOIN '._DB_PREFIX_.'pl_blog_tags_lang b
			ON (a.id_pl_blog_tags=b.id_pl_blog_tags)
			WHERE b.id_lang='.(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT'));
		
		return Db::getInstance()->ExecuteS($sql);
	}
	
	public function getCategories()
	{
		global $cookie;
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_category a
				LEFT JOIN '._DB_PREFIX_.'pl_blog_category_lang b
				ON a.id_pl_blog_category=b.id_pl_blog_category
				WHERE a.category_status=1 AND b.id_lang='.(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT')).'
				ORDER BY position ASC
				';

		$data = Db::getInstance()->ExecuteS($sql);
		
		return $data;
	}
	
	function sp($_vt = 1)
	{
		$rs = '';
		for ($i = 0; $i < $_vt; $i++)
			$rs .= '&nbsp;&nbsp;&nbsp;';
		return $rs;
	}
		
	/* setting */
	public function getContent()
	{	
		$msg = '';
		if (Tools::getValue('submitConfiguration'))
		{
			$PL_NEWS_CATEGORIES_TO_SHOW = Tools::getValue('PL_NEWS_CATEGORIES_TO_SHOW');
			$PL_SHOW_CAPTCHA_TO_COMMENT = Tools::getValue('PL_SHOW_CAPTCHA_TO_COMMENT');
			$PL_HOOK_BLOG_CATEGORY = Tools::getValue('PL_HOOK_BLOG_CATEGORY');
			$PL_HOOK_BLOG_TAG = Tools::getValue('PL_HOOK_BLOG_TAG');
			$PL_ALLOW_COMMENTS_BY_GUESTS = Tools::getValue('PL_ALLOW_COMMENTS_BY_GUESTS');
			$PL_DISPLAY_CATEGORY = Tools::getValue('PL_DISPLAY_CATEGORY');
			$PL_DISPLAY_TAG = Tools::getValue('PL_DISPLAY_TAG');
			$PL_OP_LASTEST_POST = Tools::getValue('PL_OP_LASTEST_POST');
			$PL_LASTEST_POST = Tools::getValue('PL_LASTEST_POST');
			$PL_TBEP_SHOW = Tools::getValue('PL_TBEP_SHOW');
			$PL_COLP_MAXIMUM = Tools::getValue('PL_COLP_MAXIMUM');
			$PL_B_SUMMARY_CHARACTER_COUNT = Tools::getValue('PL_B_SUMMARY_CHARACTER_COUNT');
			
			Configuration::updateValue('PL_SHOW_CAPTCHA_TO_COMMENT', $PL_SHOW_CAPTCHA_TO_COMMENT);		
			Configuration::updateValue('PL_HOOK_BLOG_CATEGORY', $PL_HOOK_BLOG_CATEGORY);		
			Configuration::updateValue('PL_HOOK_BLOG_TAG', $PL_HOOK_BLOG_TAG);	
			Configuration::updateValue('PL_ALLOW_COMMENTS_BY_GUESTS', $PL_ALLOW_COMMENTS_BY_GUESTS);		
			Configuration::updateValue('PL_DISPLAY_CATEGORY', $PL_DISPLAY_CATEGORY);
			Configuration::updateValue('PL_DISPLAY_TAG', $PL_DISPLAY_TAG);
			Configuration::updateValue('PL_LASTEST_POST', $PL_LASTEST_POST);
			Configuration::updateValue('PL_TBEP_SHOW', $PL_TBEP_SHOW);
			
			$rs_setting = true;
			if ($this->isInt($PL_NEWS_CATEGORIES_TO_SHOW))
			{
				Configuration::updateValue('PL_NEWS_CATEGORIES_TO_SHOW', $PL_NEWS_CATEGORIES_TO_SHOW);
			}
			else
			{
				$rs_setting = false;
				$_POST['plcategory_to_show_msg'] = 'invalid numbers';			
			}
			if ($this->isInt($PL_OP_LASTEST_POST))
			{
				Configuration::updateValue('PL_OP_LASTEST_POST', $PL_OP_LASTEST_POST);
			}
			else
			{
				$rs_setting = false;
				$_POST['pl_op_lastest_post_msg'] = 'invalid numbers';			
			}
			
			if ($this->isInt($PL_COLP_MAXIMUM))
			{
				Configuration::updateValue('PL_COLP_MAXIMUM', $PL_COLP_MAXIMUM);
			}
			else
			{
				$rs_setting = false;
				$_POST['pl_colp_maximum_msg'] = 'invalid numbers';				
			}
			
			if ($this->isInt($PL_B_SUMMARY_CHARACTER_COUNT)) {
				Configuration::updateValue('PL_B_SUMMARY_CHARACTER_COUNT', $PL_B_SUMMARY_CHARACTER_COUNT);
			} else {
				$rs_setting = false;
				$_POST['pl_b_summary_character_count'] = 'invalid numbers';				
			}
			
			if (!$rs_setting)
				$msg = '
					<div class="clear">&nbsp;</div>
					<h2>Easy FAQ</h2>
					<div class="conf confirm" style="background-color:#a7625e !important;color:white !important;"><img src="../img/admin/error.png" alt="Confirmation" />Settings false!</div>
				';
			else
				$msg = '
					<div class="clear">&nbsp;</div>
					<h2>Easy FAQ</h2>
					<div class="conf confirm"><img src="../img/admin/ok.gif" alt="Confirmation" />Settings updated</div>
				';			
		}
		
		return $msg.$this->displayFormConfiguration();
	}
	
	public function isInt($value)
	{
		return ((string)(int)$value === (string)$value OR $value === false);
	}
	
	public function displayFormConfiguration()
	{
		$_form = '
			<h3>'.$this->l('If you have any problem, email us at').' <font color="#0A3AEB">support@prestalive.com</font>. </h3>
			 <h3>'.$this->l('You can visit here').' <th><a href="http://www.prestalive.com" style="color: #0A3AEB"> www.PrestaLive.com </a> </th>'.$this->l('for more modules.').' </h3>
				
			<form name="submitConfiguration" method="post" action="'.$_SERVER['REQUEST_URI'].'">
	
				<fieldset>
				    
					<legend>'.$this->l('Blog Setting').'</legend>
					<label>'.$this->l('Use Captcha to Comment').'</label>
					<div class="margin-form">
						<input id="display_on" type="radio" value="1" '.(Configuration::get('PL_SHOW_CAPTCHA_TO_COMMENT') == '1' ? "checked='checked'" : '').' name="PL_SHOW_CAPTCHA_TO_COMMENT" />
						<label class="t" for="display_on">
							<img title="Enabled" alt="Enabled" src="../img/admin/enabled.gif">
						</label>
						<label class="t" for="PL_SHOW_CAPTCHA_TO_COMMENT">YES</label>
						<input id="display_off" type="radio" '.(Configuration::get('PL_SHOW_CAPTCHA_TO_COMMENT') == '0' ? "checked='checked'" : '').' value="0" name="PL_SHOW_CAPTCHA_TO_COMMENT">
						<label class="t" for="display_off">
							<img title="Disabled" alt="Disabled" src="../img/admin/disabled.gif">
						</label>
						<label class="t" for="PL_SHOW_CAPTCHA_TO_COMMENT">NO</label>
						<p>'.$this->l('Require registered users to enter captcha code').'</p>
					</div>
					
					<label>'.$this->l('Allow Unregistered Users To Comment').'</label>
					<div class="margin-form">
						<input id="display_on" type="radio" value="1" '.(Configuration::get('PL_ALLOW_COMMENTS_BY_GUESTS') == '1' ? "checked='checked'" : '').' name="PL_ALLOW_COMMENTS_BY_GUESTS" />
						<label class="t" for="display_on">
							<img title="Enabled" alt="Enabled" src="../img/admin/enabled.gif">
						</label>
						<input id="display_off" type="radio" '.(Configuration::get('PL_ALLOW_COMMENTS_BY_GUESTS') == '0' ? "checked='checked'" : '').' value="0" name="PL_ALLOW_COMMENTS_BY_GUESTS">
						<label class="t" for="display_off">
							<img title="Disabled" alt="Disabled" src="../img/admin/disabled.gif">
						</label>
						<p>'.$this->l('Allow or disallow the ability to post comments for unregistered users').'</p>
					</div>
					
					<label>'.$this->l('Summary Character Count').'</label>
					<div class="margin-form">
						<input type="text" size="8" value="'.(Configuration::get('PL_B_SUMMARY_CHARACTER_COUNT')).'" name="PL_B_SUMMARY_CHARACTER_COUNT" />
						<div style="color:red;">&nbsp;&nbsp;'.Tools::getValue('pl_b_summary_character_count').'</div>
					</div>
				</fieldset>
				<br />	
				<fieldset>
					<legend>'.$this->l('Category, Post, Tags').'</legend>					
					<label>'.$this->l('Display Category').'</label>
					<div class="margin-form">
						<select name="PL_DISPLAY_CATEGORY">
							<option value="Left" '.(Configuration::get('PL_DISPLAY_CATEGORY') == 'Left' ? 'selected="selected"' : '').' >&nbsp;&nbsp;Left&nbsp;</option>
							<option value="Right" '.(Configuration::get('PL_DISPLAY_CATEGORY') == 'Right' ? 'selected="selected"' : '').' >&nbsp;&nbsp;Right&nbsp;</option>
							<option value="Disable" '.(Configuration::get('PL_DISPLAY_CATEGORY') == 'Disable' ? 'selected="selected"' : '').' >&nbsp;&nbsp;Disable&nbsp;</option>
						</select>
						<p>'.$this->l('Choose hook for display blog category').'</p>
					</div>
	
					<label>'.$this->l('Display Tag').'</label>
					<div class="margin-form">
						<select name="PL_DISPLAY_TAG">
							<option value="Left" '.(Configuration::get('PL_DISPLAY_TAG') == 'Left' ? 'selected="selected"' : '').' >&nbsp;&nbsp;Left&nbsp;</option>
							<option value="Right" '.(Configuration::get('PL_DISPLAY_TAG') == 'Right' ? 'selected="selected"' : '').' >&nbsp;&nbsp;Right&nbsp;</option>
							<option value="Disable" '.(Configuration::get('PL_DISPLAY_TAG') == 'Disable' ? 'selected="selected"' : '').' >&nbsp;&nbsp;Disable&nbsp;</option>
						</select>
						<p>'.$this->l('Choose hook for display blog tag').'</p>
					</div>
					
					<label>'.$this->l('Show Lastest Post').'</label>
					<div class="margin-form">
						<select name="PL_LASTEST_POST">
							<option value="Left" '.(Configuration::get('PL_LASTEST_POST') == 'Left' ? 'selected="selected"' : '').' >&nbsp;&nbsp;Left&nbsp;</option>
							<option value="Right" '.(Configuration::get('PL_LASTEST_POST') == 'Right' ? 'selected="selected"' : '').' >&nbsp;&nbsp;Right&nbsp;</option>
							<option value="Disable" '.(Configuration::get('PL_LASTEST_POST') == 'Disable' ? 'selected="selected"' : '').' >&nbsp;&nbsp;Disable&nbsp;</option>
						</select>
						<p>'.$this->l('Choose hook for display lastest post').'</p>
					</div>
					
					<label>'.$this->l('Numbers of Lastest Post').'</label>
					<div class="margin-form">
						<input type="text" size="8" value="'.(Configuration::get('PL_OP_LASTEST_POST')).'" name="PL_OP_LASTEST_POST" />
						<div style="color:red;">&nbsp;&nbsp;'.Tools::getValue('pl_op_lastest_post_msg').'</div>
						<p>'.$this->l('ID').'Specify the amount of lastest post to be displayed in lastest post block</p>
					</div>

					<label>'.$this->l('Allow Show Tags Below Each Post').'</label>
					<div class="margin-form">
						<select name="PL_TBEP_SHOW">
							<option value="YES" '.(Configuration::get('PL_TBEP_SHOW') == 'YES' ? 'selected="selected"' : '').' >&nbsp;&nbsp;YES&nbsp;</option>
							<option value="NO" '.(Configuration::get('PL_TBEP_SHOW') == 'NO' ? 'selected="selected"' : '').' >&nbsp;&nbsp;NO&nbsp;</option>
						</select>
						<p>'.$this->l('Choose status to Allow show tags below each post').'</p>
					</div>
					
					<label>'.$this->l('Post Character Count').'</label>
					<div class="margin-form">
						<input type="text" size="8" value="'.(Configuration::get('PL_COLP_MAXIMUM')).'" name="PL_COLP_MAXIMUM" />
						<div style="color:red;">&nbsp;&nbsp;'.Tools::getValue('pl_colp_maximum').'</div>
					</div>
					
					<center><input style="width:100px !important;" type="submit" name="submitConfiguration" value="Save" class="button" /></center>
				</fieldset>
			</form>
		';
		
		return $_form;
	}
}