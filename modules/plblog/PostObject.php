<?php
class PostObject extends ObjectModel
{
	public $id_pl_blog_post;
	public $id_pl_blog_category;
	public $id_pl_blog_tags;
	public $post_date_create;
	public $post_allow_comment;
	public $post_status;
	
	public $post_title;
	public $post_description;
	public $post_meta_title;
	public $post_meta_description;
	public $post_meta_keywords;
	public $link_rewrite;
	
	protected $fieldsValidate = array(
		'id_pl_blog_category' => 'isInt',
		'post_allow_comment' => 'isInt',
		'post_status' => 'isBool'
	);
		
	protected $fieldsRequiredLang = array('post_title', 'post_description', 'link_rewrite');
		
	protected $fieldsSizeLang = array(
		'post_title' => 100,
		'post_meta_title' => 500,
		'post_meta_description' => 1000,
		'post_meta_keywords' => 1000,
		'link_rewrite' => 1000
	);
	
	protected $fieldsValidateLang = array(
		'post_title' => 'isCatalogName',
		'post_description' => 'isString',
		'post_meta_title' => 'isCatalogName',
		'post_meta_description' => 'isCatalogName',
		'post_meta_keywords' => 'isCatalogName',
		'link_rewrite' => 'isString'
	);
	
	protected $table = 'pl_blog_post';
	
	protected $identifier = 'id_pl_blog_post';
		
	public function getFields()
	{
		parent::validateFields();
			
		$fields['id_pl_blog_post'] = pSQL($this->id_pl_blog_post);
		$fields['id_pl_blog_category'] = pSQL($this->id_pl_blog_category);
		//$fields['id_pl_blog_tags'] = pSQL($this->id_pl_blog_tags);
		$fields['post_date_create'] = pSQL($this->post_date_create);
		$fields['post_allow_comment'] = pSQL($this->post_allow_comment);
		$fields['post_status'] = pSQL($this->post_status);
		
		return $fields;
	}
		
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array(
			'post_title', 
			'post_description',
			'post_meta_title',
			'post_meta_description',
			'post_meta_keywords',
			'link_rewrite'
		));
	}
	
	public function getPostById($id_lang = false)
	{				if ($this->id == null)						return null;
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_post a
				LEFT JOIN '._DB_PREFIX_.'pl_blog_post_lang b
				ON a.id_pl_blog_post=b.id_pl_blog_post
				WHERE b.id_lang='.Configuration::get('PS_LANG_DEFAULT').' AND a.id_pl_blog_post='.$this->id;
		$data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
			
		return $data;
	}
	
	public function getCategorys()
	{
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_category a
				LEFT JOIN '._DB_PREFIX_.'pl_blog_category_lang b
				ON a.id_pl_blog_category=b.id_pl_blog_category
				WHERE a.category_status=1 AND b.id_lang='.Configuration::get('PS_LANG_DEFAULT');
		$data = Db::getInstance()->ExecuteS($sql);
		
		return $data;	
	}
	
	public function getTags($id_lang)
	{
		global $cookie;
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_post_tag a1
				LEFT JOIN '._DB_PREFIX_.'pl_blog_tags_lang a2
				ON a1.id_pl_tag = a2.id_pl_blog_tags
				WHERE a1.id_pl_blog_post = '.((int)$this->id).' AND a2.id_lang='.$id_lang;
		$data = Db::getInstance()->ExecuteS($sql);
		
		$tags = '';
		$c = true;
		foreach ($data as $tag)
		{
			if ($c)
				$tags = $tag['tags_name'];
			else
				$tags .= ', '.$tag['tags_name'];
			$c = false;
		}
		return $tags;	
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
		SET `post_status` = !`post_status`
		WHERE `'.pSQL($this->identifier).'` = '.(int)($this->id));
	}
	
	public function add($autodate = true, $nullValues = false)
	{
		$result = parent::add($autodate, $nullValues);
		
		$this->addPostTag();
		
		return $result;
	}
	
	public function isCatalogName($name)
	{
		return preg_match('/^[^<>;=#{}]*$/u', $name);
	}
	
	public 	function getDate()
	{
		$date = getdate();
		return $date['year'].'/'.$date['mon'].'/'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'];
	}
	
	public function addPost1Tag($tags = null, $id_lang = 0, $id_pl_blog_post = null)
	{
		if (!$this->isCatalogName($tags)) return;
		if ($tags == null) return;
		$tags = str_replace('  ', '', $tags);
		
		if ($tags == null) return;
		//echo $tags.'<br/>'; return;
		$l_tags = explode(',', $tags);
		// echo '<pre>';
		// print_r($l_tags);
		$c_l_tags = count($l_tags);
		if ($c_l_tags < 1) return;
		// return id_pl_blog_post
		if ($id_pl_blog_post == null)
		{
			$rows = Db::getInstance()->ExecuteS("SELECT id_pl_blog_post FROM "._DB_PREFIX_."pl_blog_post ORDER BY id_pl_blog_post DESC LIMIT 0, 1");
			$row = $rows[0];
			$id_pl_blog_post = $row['id_pl_blog_post'];
		}		
		
		// return date
		$date = $this->getDate();
		// add tag
		foreach ($l_tags as $tag_name)
		{
			if ($tag_name[0] == ' ')
				$tag_name = substr($tag_name, 1);
			//if ($tag_name == '') continue;
			// check exists
			$id_pl_tag = Db::getInstance()->getValue('SELECT id_pl_blog_tags FROM '._DB_PREFIX_.'pl_blog_tags_lang WHERE tags_name="'.$tag_name.'"');
			if ($id_pl_tag == null) $tag_exist = false;
			else $tag_exist = true;
			
			if (!$tag_exist) 
			{			
				// add table tags
				Db::getInstance()->ExecuteS("INSERT INTO "._DB_PREFIX_."pl_blog_tags(tags_date_create) VALUES('".$date."')");
				
				// return id_pl_blog_tags have just insert
				$rows = Db::getInstance()->ExecuteS('SELECT id_pl_blog_tags FROM '._DB_PREFIX_.'pl_blog_tags ORDER BY id_pl_blog_tags DESC LIMIT 0,1');
				$row = $rows[0];
				$id_pl_tag = $row['id_pl_blog_tags'];
				
				// add table tags_lang
				Db::getInstance()->ExecuteS("
					INSERT INTO "._DB_PREFIX_."pl_blog_tags_lang(id_pl_blog_tags, id_lang, tags_name)
					VALUES(".$id_pl_tag.",".$id_lang.",'".$tag_name."')
				");
			}
			// add table post_tag
			Db::getInstance()->ExecuteS("
				INSERT INTO "._DB_PREFIX_."pl_blog_post_tag(id_pl_blog_post, id_pl_tag)
				VALUES(".$id_pl_blog_post.",".$id_pl_tag.")
			");
		}
	}
	
	public function addPostTag($id_pl_blog_post = null)
	{
		global $cookie;
		
		$_lang = Db::getInstance()->ExecuteS("
			SELECT id_lang FROM "._DB_PREFIX_."lang
		");

		foreach ($_lang as $lang)
		{
			$tags = Tools::getValue('id_pl_blog_tags_'.$lang['id_lang']);
			if ($tags == null) continue;
			$this->addPost1Tag($tags, $lang['id_lang'], $id_pl_blog_post);
		}
	}
	
	public function update($nullValues = false)
	{
		$result = parent::update($nullValues);
		
		// update post_tag
		
		$id_pl_blog_post = Tools::getValue('id_pl_blog_post');
		Db::getInstance()->ExecuteS("DELETE FROM "._DB_PREFIX_."pl_blog_post_tag WHERE id_pl_blog_post=".$id_pl_blog_post);
		
		$this->addPostTag($id_pl_blog_post);
		
		return $result;
	}
}