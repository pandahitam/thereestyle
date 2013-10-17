<?php

/**
  * PslideshowImg class
  * PslideshowImg management
  * @category classes
  *
  * @author AppSide 
  * @copyright AppSide
  * @version 1.0
  *
  */

class PslideshowImg extends ObjectModel
{
	public $title;
	public $link;

	public $active;
	public $position;
	public $target_blank;
	public $id_pslideshow_block;
	
 	protected $fieldsRequiredLang = array();
	protected $fieldsSizeLang = array('title' => 255,'link' => 255);
	protected $fieldsValidateLang = array('title' => 'isGenericName','link' => 'isCleanHtml' );

	protected $fieldsRequired = array();
	protected $fieldsValidate = array('active' => 'isBool');
	protected $fieldsSize = array();

	protected $table = 'pslideshow_img';
	protected $identifier = 'id_pslideshow_img';
	
	public function getFields() { 
		return array('id_pslideshow_img' => (int) $this->id,
					 'id_pslideshow_block' => (int) $this->id_pslideshow_block,
					 'position' => (int) $this->position,
					 'target_blank' => (int) $this->target_blank,
					 'active' => (int) $this->active); 
	}
	
	public function getTranslationsFieldsChild(){
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('title','link'));
	}
	
	public function add($autodate = true, $nullValues = false)
	{ 
		$this->position = PslideshowImg::getLastPosition((int)(Tools::getValue('id_pslideshow_block')));
		return parent::add($autodate, true); 
	}
	
	public function update($nullValues = false)
	{
		if (parent::update($nullValues))
			return $this->cleanPositions($this->id_pslideshow_block);
		return false;
	}
	
	public function delete()
	{
	 	if (parent::delete())
			return $this->cleanPositions($this->id_pslideshow_block);
		return false;
	}
		
	public static function listImages($id_lang,$active = true)
	{
		if (empty($id_lang))
			$id_lang = (int)Configuration::get('PS_LANG_DEFAULT');
			
		$query = 'SELECT * FROM  '._DB_PREFIX_.$this->table.' img
				JOIN '._DB_PREFIX_.$this->table.'_lang l ON (img.id_pslideshow_img = l.id_pslideshow_img)
				WHERE l.id_lang = '.intval($id_lang).($active ? ' AND img.active = 1 ' : '').' 
				ORDER BY img.`position`';
		
		return Db::getInstance()->ExecuteS($query);
	}
	
	public function updatePosition($way, $position)
	{
		
		if (!$res = Db::getInstance()->ExecuteS('
			SELECT img.`id_pslideshow_img`, img.`position`, img.`id_pslideshow_block` 
			FROM `'._DB_PREFIX_.'pslideshow_img` img
			WHERE img.`id_pslideshow_block` = '.(int)(Tools::getValue('id_pslideshow_block', 1)).' 
			ORDER BY img.`position` ASC'))
			return false;
		
		foreach ($res AS $img)
			if ((int)($img['id_pslideshow_img']) == (int)($this->id))
				$movedImg = $img;
		
		if (!isset($movedImg) || !isset($position))
			return false;
				
		// < and > statements rather than BETWEEN operator
		// since BETWEEN is treated differently according to databases
		return (Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'pslideshow_img`
			SET `position`= `position` '.($way ? '- 1' : '+ 1').'
			WHERE `position` 
			'.($way 
				? '> '.(int)($movedImg['position']).' AND `position` <= '.(int)($position)
				: '< '.(int)($movedImg['position']).' AND `position` >= '.(int)($position)).'
			AND `id_pslideshow_block`='.(int)($movedImg['id_pslideshow_block']))
		AND Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'pslideshow_img`
			SET `position` = '.(int)($position).'
			WHERE `id_pslideshow_img` = '.(int)($movedImg['id_pslideshow_img']).'
			AND `id_pslideshow_block`='.(int)($movedImg['id_pslideshow_block'])));
	}
	
	static public function cleanPositions($id_category)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_pslideshow_img`
		FROM `'._DB_PREFIX_.'pslideshow_img`
		WHERE `id_pslideshow_block` = '.(int)($id_category).'
		ORDER BY `position`');
		$sizeof = sizeof($result);
		
		for ($i = 0; $i < $sizeof; ++$i){
				$sql = 'UPDATE `'._DB_PREFIX_.'pslideshow_img` SET `position` = '.(int)($i).'
				WHERE `id_pslideshow_block` = '.(int)($id_category).' 
				AND `id_pslideshow_img` = '.(int)($result[$i]['id_pslideshow_img']);
				Db::getInstance()->Execute($sql);
		}
		return true;
	}
	
	static public function getLastPosition($id_category)
	{
		return (Db::getInstance()->getValue('SELECT MAX(position)+1 
												FROM `'._DB_PREFIX_.'pslideshow_img` 
												WHERE `id_pslideshow_block` = '.(int)($id_category)));
	}
	
		
}

?>
