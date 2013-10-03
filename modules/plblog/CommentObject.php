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
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'pl_blog_comment',
		'primary' => 'id_pl_blog_comment',
		'multilang' => true,
		'fields' => array(
			'id_pl_blog_post' => 		array('type' => self::TYPE_INT, 'validate' => 'isInt'),
			'comment_author_name' => 	array('type' => self::TYPE_STRING, 'validate' => 'isCatalogName', 'size' => 200),
			'comment_author_email' => 	array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'size' => 200),
			'comment_status' => 		array('type' => self::TYPE_INT, 'validate' => 'isInt'),

			// Lang fields
			'comment_content' => 		array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => true, 'size' => 7000),
		),
	);	
	
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