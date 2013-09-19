<?php
class CommentObject extends ObjectModel
{
	public $id_pl_blog_comment;
	public $id_pl_blog_post;
	public $comment_author_name;
	public $comment_author_email;
	public $comment_date_create;
	public $comment_status;
	public $comment_content;	
	protected $fieldsSize = array('comment_author_name' => 200, 'comment_author_email' => 200);
	protected $fieldsValidate = array(
		'id_pl_blog_post' => 'isInt',
		'comment_author_name' => 'isCatalogName',
		'comment_author_email' => 'isEmail',
		'comment_status' => 'isInt'
	);
	protected $fieldsRequiredLang = array('comment_content');
	protected $fieldsSizeLang = array('comment_content' => 7000);
	protected $fieldsValidateLang = array('comment_content' => 'isString');
	protected $table = 'pl_blog_comment';
	protected $identifier = 'id_pl_blog_comment';
	public function getFields()
	{
		parent::validateFields();
		$fields['id_pl_blog_post'] = pSQL($this->id_pl_blog_post);
		$fields['comment_author_name'] = pSQL($this->comment_author_name);
		$fields['comment_author_email'] = pSQL($this->comment_author_email);
		$fields['comment_date_create'] = pSQL($this->comment_date_create);
		$fields['comment_status'] = pSQL($this->comment_status);	
		return $fields;
	}	
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('comment_content'));
	}

	public function getCommentById($id_lang = false)
	{		global $cookie;
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_comment a
				LEFT JOIN '._DB_PREFIX_.'pl_blog_comment_lang b
				ON a.id_pl_blog_comment=b.id_pl_blog_comment
				WHERE b.id_lang='.(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT')).' AND a.id_pl_blog_comment='.$this->id;
		$data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
		return $data;
	}
	public function getPostNames()
	{		global $cookie;
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_post_lang a
				WHERE a.id_lang='.(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT'));
		return Db::getInstance()->ExecuteS($sql);
	}
	public function getCommentStatus()
	{
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_comment_status';
		return Db::getInstance()->ExecuteS($sql);
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
	public function toggleStatus()
	{
	 	if (!Validate::isTableOrIdentifier($this->identifier) OR !Validate::isTableOrIdentifier($this->table))
	 		die(Tools::displayError());
		/* Change status to active/inactive */
		return Db::getInstance()->Execute('
		UPDATE `'.pSQL(_DB_PREFIX_.$this->table).'`
		SET `comment_status` = !`comment_status`
		WHERE `'.pSQL($this->identifier).'` = '.(int)($this->id));
	}
}