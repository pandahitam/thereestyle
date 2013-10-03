<?php
class TagsObject extends ObjectModel
{
	public $id;
	public $tags_url;
	public $tags_date_create;
	
	public $tags_name;
	public $tags_description;

	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'pl_blog_tags',
		'primary' => 'id_pl_blog_tags',
		'multilang' => true,
		'fields' => array(
			'tags_url' => 			array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 1000),
			'tags_date_create' => 	array('type' => self::TYPE_STRING, 'validate' => 'isString'),

			// Lang fields
			'tags_name' => 				array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'size' => 100),
			'tags_description' => 		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 7000),
		),
	);	

	public function getTagsById($id_lang = false)
	{		if ($this->id == null)						return null;			
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_tags a
				LEFT JOIN '._DB_PREFIX_.'pl_blog_tags_lang b
				ON a.id_pl_blog_tags=b.id_pl_blog_tags
				WHERE b.id_lang='.Configuration::get('PS_LANG_DEFAULT').' AND a.id_pl_blog_tags='.$this->id;
		$data = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
			
		return $data;
	}
		
}