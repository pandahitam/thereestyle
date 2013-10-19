<?php

/**
  * PslideshowBlock class
  * @category classes
  *
  * @author AppSide 
  * @copyright AppSide
  * @version 1.0
  *
  */

class PslideshowBlock extends ObjectModel
{
	public $name;
	public $active;
	public $location;
	public $location_id;
	public $hook;
	public $hook_other;
	public $size_width;
	public $size_height;
	
	protected $fieldsRequired = array('name');
	protected $fieldsValidate = array('name' => 'isGenericName',
									  'active' => 'isBool');
	
	protected $fieldsSize = array('name' => 255);

	protected $table = 'pslideshow_block';
	protected $identifier = 'id_pslideshow_block';
	
	public $locationValues = array('anywhere','category','cms','product');
	
	public function getFields() { 
		
		if(trim($this->hook_other) != ''){
			$this->hook = $this->hook_other;
		}
		
		return array('id_pslideshow_block' => (int) $this->id,
					 'name' => pSQL($this->name),
					 'location' => pSQL($this->location),
					 'hook' => pSQL($this->hook),
					 'location_id' => pSQL($this->location_id),
					 'size_width' => pSQL($this->size_width),
					 'size_height' => pSQL($this->size_height),
					 'active' => (int) $this->active); 
	}
	
	public static function listSlideshows($active = false){
		 return Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'pslideshow_block WHERE 1=1 '.($active ? ' AND active = 1 ' : ''));
	}
	
	public function existsLocation($location,$active = true){
		
		if(!in_array($location,$this->locationValues)) $location = 'anywhere';
	
		$result = Db::getInstance()->ExecuteS("SELECT * FROM "._DB_PREFIX_."pslideshow_block s
												WHERE s.`id_pslideshow_block` = ".$this->id." 
												AND s.`location` = '".$location."'".
											($active ? " AND s.`active` = 1 " : ""));
		if($result) return true;
		return false;
	}
	
	public function getImages($id_lang = null,$active = false){
		
		if ($id_lang == NULL)
			$id_lang = Configuration::get('PS_LANG_DEFAULT');
			
		$query = 'SELECT * FROM `'._DB_PREFIX_.'pslideshow_img` img
				 	JOIN `'._DB_PREFIX_.'pslideshow_img_lang` l ON img.`id_pslideshow_img` = l.`id_pslideshow_img`
					WHERE img.`id_pslideshow_block` = '.$this->id.' 
					AND l.`id_lang` = '.(int)($id_lang).($active ? ' AND img.`active` = 1 ' : '').'
					ORDER BY img.`position`';
		
		return Db::getInstance()->ExecuteS($query);
	} 
	
	
	public static function getSlideshowsInHook($hookName,$active = 1){
		return Db::getInstance()->ExecuteS("SELECT * FROM "._DB_PREFIX_."pslideshow_block s
											 WHERE s.`hook` = '".$hookName."' ".($active ? " AND s.`active` = 1 " : ""));
	}
	
	public function unregisterHook($id_module){
		 if (!Validate::isHookName($this->hook)) return false;
		$row = Db::getInstance()->getRow('SELECT `id_hook`, `name` FROM `'._DB_PREFIX_.'hook` WHERE `name` = \''.pSQL($this->hook).'\'');

		if($row)
			return Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'hook_module` WHERE `id_module` = '.(int)($id_module).'
												AND `id_hook` = '.(int)($row['id_hook']));
	}

	public function checkHookHasSlideshow(){	
		$result = Db::getInstance()->ExecuteS("SELECT * FROM "._DB_PREFIX_."pslideshow_block s
												WHERE `hook` = '".trim($this->hook)."'
											    AND s.`id_pslideshow_block` != ".$this->id);
		if(count($result) > 0)return true;
		return false;
	}
	
}
