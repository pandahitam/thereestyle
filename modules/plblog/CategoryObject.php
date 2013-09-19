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
	
	protected $fieldsSize = array('category_url' => 1000);
	
	protected $fieldsValidate = array(
		'category_url' => 'isString',
		'category_parent' => 'isInt',
		'category_date_create' => 'isString',
		'category_status' => 'isBool',
		'category_allow_comment' => 'isBool'
	);
		
	protected $fieldsRequiredLang = array('link_rewrite');
		
	protected $fieldsSizeLang = array(
		'category_name' => 100,
		'category_description' => 2000,
		'category_meta_title' => 500,
		'category_meta_description' => 1000,
		'category_meta_keywords' => 1000,
		'link_rewrite' => 1000
	);
	
	protected $fieldsValidateLang = array(
		'category_name' => 'isCatalogName',
		'category_description' => 'isString',
		'category_meta_title' => 'isCatalogName',
		'category_meta_description' => 'isCatalogName',
		'category_meta_keywords' => 'isCatalogName',
		'link_rewrite' => 'isCatalogName'
	);
	
	protected $table = 'pl_blog_category';
	
	protected $identifier = 'id_pl_blog_category';
		
	public function getFields()
	{
		parent::validateFields();
			
		$fields['category_url'] = pSQL($this->category_url);
		$fields['category_parent'] = pSQL($this->category_parent);
		$fields['category_date_create'] = pSQL($this->category_date_create);
		$fields['category_status'] = pSQL($this->category_status);
		$fields['position'] = ((int) $this->position);
		$fields['category_allow_comment'] = pSQL($this->category_allow_comment);
		
		return $fields;
	}
		
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array(
			'category_name', 
			'category_description',
			'category_meta_title',
			'category_meta_description',
			'category_meta_keywords',
			'link_rewrite'
		));
	}
		
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
		
	protected function makeTranslationFields(&$fields, &$fieldsArray, $id_language)
	{
		global $cookie;
		
		$fields[$id_language]['id_lang'] = $id_language;
		$fields[$id_language][$this->identifier] = (int)($this->id);
		foreach ($fieldsArray as $field)
		{
			/* Check fields validity */
			if (!Validate::isTableOrIdentifier($field))
				die(Tools::displayError());

			/* Copy the field, or the default language field if it's both required and empty */
			if ((!$this->id_lang AND isset($this->{$field}[$id_language]) AND !empty($this->{$field}[$id_language])) 
			OR ($this->id_lang AND isset($this->$field) AND !empty($this->$field)))
				$fields[$id_language][$field] = $this->id_lang ? pSQL($this->$field, true) : pSQL($this->{$field}[$id_language], true);
			elseif (in_array($field, $this->fieldsRequiredLang))
				$fields[$id_language][$field] = $this->id_lang ? pSQL($this->$field, true) : pSQL($this->{$field}[(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT'))], true);
			else
				$fields[$id_language][$field] = '';
		}
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
		
		//echo '<script type="text/javascript">alert("'.$id_plposition.' and '.$id_pl_blog_category_position.'");</script>';
		
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