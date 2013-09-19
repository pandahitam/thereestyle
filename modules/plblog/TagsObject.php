<?php
class TagsObject extends ObjectModel
{
	public $id;
	public $tags_url;
	public $tags_date_create;
	
	public $tags_name;
	public $tags_description;
	
	protected $fieldsSize = array('tags_url' => 1000);
	
	protected $fieldsValidate = array(
		'tags_url' => 'isString',
		'tags_date_create' => 'isString'
	);
		
	protected $fieldsSizeLang = array(
		'tags_name' => 100,
		'tags_description' => 7000
	);
	
	protected $fieldsValidateLang = array(
		'tags_name' => 'isCatalogName',
		'tags_description' => 'isString'
	);
	
	protected $table = 'pl_blog_tags';
	
	protected $identifier = 'id_pl_blog_tags';
		
	public function getFields()
	{
		parent::validateFields();

		$fields['tags_url'] = pSQL($this->tags_url);
		$fields['tags_date_create'] = pSQL($this->tags_date_create);
		
		return $fields;
	}
		
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('tags_name' ,'tags_description'));
	}

	public function getTagsById($id_lang = false)
	{		if ($this->id == null)						return null;			
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_tags a
				LEFT JOIN '._DB_PREFIX_.'pl_blog_tags_lang b
				ON a.id_pl_blog_tags=b.id_pl_blog_tags
				WHERE b.id_lang='.Configuration::get('PS_LANG_DEFAULT').' AND a.id_pl_blog_tags='.$this->id;
		$data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
			
		return $data;
	}
		
	protected function makeTranslationFields(&$fields, &$fieldsArray, $id_language)
	{
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
				$fields[$id_language][$field] = $this->id_lang ? pSQL($this->$field, true) : pSQL($this->{$field}[Configuration::get('PS_LANG_DEFAULT')], true);
			else
				$fields[$id_language][$field] = '';
		}
	}
}