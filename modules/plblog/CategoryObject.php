<?php
class CategoryObject extends ObjectModel
{
	public $id_pl_blog_category;
	public $category_url;
	public $category_parent;
	public $category_date_create;
	public $category_status;
	public $position;
	public $category_allow_comment;
	
	public $category_name;
	public $category_description;
	public $category_meta_title;
	public $category_meta_description;
	public $category_meta_keywords;
	public $link_rewrite;
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'pl_blog_category',
		'primary' => 'id_pl_blog_category',
		'multilang' => true,
		'fields' => array(
			'category_url' => 			array('type' => self::TYPE_STRING, 'validate' => 'isString'),
			'category_parent' => 		array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
			'category_status' => 		array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'position' => 				array('type' => self::TYPE_INT),
			'category_allow_comment' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			'category_date_create' => 	array('type' => self::TYPE_STRING, 'validate' => 'isString'),

			// Lang fields
			'category_name' => 				array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'size' => 100),
/*			'category_description' => 		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 2000), */
			'category_description' => 		array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString', 'size' => 2000),
			'category_meta_title' => 		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 500),
			'category_meta_description' => 	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 1000),
			'category_meta_keywords' => 	array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 1000),
			'link_rewrite' => 				array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'required' => true, 'size' => 1000),
		),
	);	
			
	public function getCategoryById($id_lang = false)
	{	
		global $cookie;
		
		if ($this->id == null)						return null;			
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_category a
				LEFT JOIN '._DB_PREFIX_.'pl_blog_category_lang b
				ON a.id_pl_blog_category=b.id_pl_blog_category
				WHERE b.id_lang='.(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT')).' AND a.id_pl_blog_category='.$this->id;
		$data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
			
		return $data;
	}
	
	public function getCategories()
	{
		global $cookie;
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_category a
				LEFT JOIN '._DB_PREFIX_.'pl_blog_category_lang b
				ON a.id_pl_blog_category=b.id_pl_blog_category
				WHERE a.category_status=1 AND b.id_lang='.(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT'));
		$data = Db::getInstance()->ExecuteS($sql);
		
		$categories = array();
		
		foreach ($data as $row)
		{
			if(is_null($row['category_parent'])) {
				$row['category_parent'] = 0;
			}
			$categories[$row['category_parent']][$row['id_pl_blog_category']] = $row;
		}
		return $categories;	
	}
	
	function recurseCategory($categories, $current, $id_category = 1, $id_selected = 1, $_vt = 0)
	{
		global $currentIndex;
		if ($id_category != 1)
			echo '<option value="'.$id_category.'"'.(($id_selected == $id_category) ? ' selected="selected"' : '').'>'.
		$this->sp($_vt).$current['category_name'].'</option>';
		if (isset($categories[$id_category]))
			foreach ($categories[$id_category] AS $key => $row)
				$this->recurseCategory($categories, $categories[$id_category][$key], $key, $id_selected, ($_vt+1));
	}
	
	function sp($_vt = 0)
	{
		$rs = '';
		for ($i = 0; $i < $_vt; $i++)
			$rs .= '&nbsp;&nbsp;&nbsp;';
		return $rs;
	}
		
	public function toggleStatus()
	{
	 	if (!Validate::isTableOrIdentifier($this->identifier) OR !Validate::isTableOrIdentifier($this->table))
	 		die(Tools::displayError());
			
		/* Change status to active/inactive */
		return Db::getInstance()->Execute('
		UPDATE `'.pSQL(_DB_PREFIX_.$this->table).'`
		SET `category_status` = !`category_status`
		WHERE `'.pSQL($this->identifier).'` = '.(int)($this->id));
	}
	
	public function updatePosition()
	{
		$id_pl_blog_category = (int)Tools::getValue('id_pl_blog_category');
		$plposition = (int)Tools::getValue('plposition');
		
		$row_id_pl_blog_category = Db::getInstance()->getRow('
			SELECT position
			FROM '._DB_PREFIX_.'pl_blog_category 
			WHERE id_pl_blog_category='.$id_pl_blog_category.'
		');
		$id_pl_blog_category_position = $row_id_pl_blog_category['position'];
		
		$row_plposition = Db::getInstance()->getRow('
			SELECT position
			FROM '._DB_PREFIX_.'pl_blog_category 
			WHERE id_pl_blog_category='.$plposition.'			
		');
		$id_plposition = $row_plposition['position'];
		
		/*echo '<script type="text/javascript">alert("'.$id_plposition.' and '.$id_pl_blog_category_position.'");</script>'; */
		
		Db::getInstance()->ExecuteS('
			UPDATE '._DB_PREFIX_.'pl_blog_category
			SET position='.$id_pl_blog_category_position.'
			WHERE id_pl_blog_category='.$plposition.'
		');
		
		Db::getInstance()->ExecuteS('
			UPDATE '._DB_PREFIX_.'pl_blog_category
			SET position='.$id_plposition.'
			WHERE id_pl_blog_category='.$id_pl_blog_category.'			
		');
	}	
	
	function getPosition()
	{
		$sql = '
			SELECT position
			FROM '._DB_PREFIX_.'pl_blog_category
			ORDER BY position DESC
			LIMIT 0,1
		';
		
		$rs = Db::getInstance()->ExecuteS($sql);
		$row = $rs[0];
		
		return (int)$row['position'] + 1;
	}
}