<?php
if (!defined('_PS_VERSION_'))
	exit;

class Pslideshows extends Module
{	
	private $_html = '';
	
	private static $instance = 1;
	
	private static $pref = null;
	public static $default_values = array('position_way' => 'tag',
										  'show_title' => 1,
										  'show_pager' => 1,
										  'show_arrows' => 1,
										  'random_start' => 0,
										  'effect' => 'horizontal',
										  'transition_speed' => 800,
										  'transition_pause' => 4000,
										  'img_save_path' => 'img/pslideshows/');

	public function __construct()
	{
		$this->name = 'pslideshows';
 	 	$this->version = '1.1';
		
		if(floatval(_PS_VERSION_) >= 1.4){
 	 		$this->tab = 'front_office_features';
 	 	}else{
 	 		$this->tab = 'Slideshows';
 	 	}
 	 	
		parent::__construct();
		
		$this->displayName = $this->l('Slideshows anywhere');
		$this->description = $this->l('Display slideshows in your website');
		
		$this->checkServerConf();
	}
	
	public function install()
	{
	 	if(!parent::install()) return false;
	 	
	 	Configuration::deleteByName('PSLIDESHOW_CONF');
	 	
		if(!Configuration::updateValue('PSLIDESHOW_CONF',base64_encode(serialize(self::$default_values)))) return false;
		
		/**** create admin tab ****/
	
		$langs = Language::getLanguages();

		$tab = new Tab();
		$tab->class_name = "AdminPslideshowBlock";
		$tab->module = $this->name;
		$tab->id_parent = 124;
		foreach($langs as $l) $tab->name[$l['id_lang']] = "Slideshows";
		$id = $tab->add();
		
		$tab = new Tab();
		$tab->class_name = "AdminPslideshowImg";
		$tab->module = $this->name;
		$tab->id_parent = 124;
		foreach($langs as $l) $tab->name[$l['id_lang']] = "All images";
		$id = $tab->add();
		
		$conf = $this->getConf();
		
		/**** create icon ****/
		@copy(_PS_ROOT_DIR_."/modules/pslideshows/AdminPslideshowBlock.gif",_PS_ROOT_DIR_."/img/t/AdminPslideshowBlock.gif");
		
		/**** create img directories ****/
		if(@mkdir(_PS_ROOT_DIR_.'/'.$conf['img_save_path'])){
			foreach($langs as $l) @mkdir(_PS_ROOT_DIR_.'/'.$conf['img_save_path'].'/'.$l['id_lang'],0777);
		}
		
		/**** create tables ****/
		
		if(!defined('_MYSQL_ENGINE_')){
			define(_MYSQL_ENGINE_,'MyISAM');
		}

		$query1 = "CREATE TABLE `"._DB_PREFIX_."pslideshow_img`(
					`id_pslideshow_img` int(11) unsigned NOT NULL auto_increment,
					`id_pslideshow_block` int(11) unsigned DEFAULT NULL,
					`active` tinyint(1) unsigned NOT NULL DEFAULT '0',
					`position` int(10) unsigned NOT NULL,
					`target_blank` tinyint(1) unsigned NOT NULL DEFAULT '0',
					PRIMARY KEY  (`id_pslideshow_img`),
					KEY `id_pslideshow_block` (`id_pslideshow_block`)
				) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";
					
		$query2 = " CREATE TABLE `"._DB_PREFIX_."pslideshow_img_lang`(
					`id_lang` int(11) unsigned NOT NULL,
					`id_pslideshow_img` int(11) unsigned NOT NULL,
					`title` varchar(255) character set utf8 NULL,
					`link` varchar(255) character set utf8 NULL,
					UNIQUE KEY `pslideshow_img_lang_index` (`id_lang`,`id_pslideshow_img`)
				) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";
		
		$query3 = "CREATE TABLE `"._DB_PREFIX_."pslideshow_block`(
					`id_pslideshow_block` int(11) unsigned NOT NULL auto_increment,
					`name` varchar(255) NULL,
					`location` enum('anywhere','category','product','cms') DEFAULT NULL,
					`hook` varchar(128) DEFAULT NULL,
					`hook_other` varchar(128) DEFAULT NULL,
					`location_id` text,
					`size_width` varchar(8) character set utf8 NULL,
					`size_height` varchar(8) character set utf8 NULL,
					`active` tinyint(1) unsigned NOT NULL default '0',
					PRIMARY KEY  (`id_pslideshow_block`)
				) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8;";
		
		if (!Db::getInstance()->execute($query1)) return false;
		if (!Db::getInstance()->execute($query2)) return false;
		if (!Db::getInstance()->execute($query3)) return false;
		
		Db::getInstance()->execute("INSERT INTO `"._DB_PREFIX_."pslideshow_block` (`id_pslideshow_block`, `name`, `location`, `location_id`, `size_width`, `size_height`, `active`) 
									VALUES (1, 'My Slideshow 1', 'anywhere', '', '550', '300', 1)");
				
		/**** register in hooks ****/
		if(!$this->registerHook('header')) return false;
		
		return true;
	}
	
	public function uninstall()
	{
		if (!parent::uninstall()) return false;
		
		$tab_id = Tab::getIdFromClassName("AdminPslideshowImg");
		if($tab_id){
			$tab = new Tab($tab_id);
			$tab->delete();
		}
		
		$tab_id = Tab::getIdFromClassName("AdminPslideshowBlock");
		if($tab_id){
			$tab = new Tab($tab_id);
			$tab->delete();
		}
		
		@unlink(_PS_ROOT_DIR_."/img/t/AdminPslideshowBlock.gif");
				
		Db::getInstance()->execute('DROP TABLE '._DB_PREFIX_.'pslideshow_img');
		Db::getInstance()->execute('DROP TABLE '._DB_PREFIX_.'pslideshow_img_lang');
		Db::getInstance()->execute('DROP TABLE '._DB_PREFIX_.'pslideshow_block');
		
		if (!Configuration::deleteByName('PSLIDESHOW_CONF') OR !parent::uninstall()) return false;
		
		return true;
	}
	
	public function hookHeader($params)
	{
		global $smarty, $cookie, $css_files, $js_files;
		
		$css_files[_MODULE_DIR_.$this->name.'/jquery.bxSlider/bx_styles.css'] = 'screen';
		$js_files[] = _MODULE_DIR_.$this->name.'/jquery.bxSlider/jquery.bxSlider.min.js';
		
		if (method_exists($smarty, 'register_function')) { 
			$smarty->register_function('displaySlideshow', array(__CLASS__, 'displaySlideshow'));	
		}elseif(method_exists($smarty->register, 'registerPlugin')){
			if(!isset($smarty->registered_plugins['function']['displaySlideshow']))
				$smarty->registerPlugin("function", "displaySlideshow", array(__CLASS__, 'displaySlideshow'));
		}
		$smarty->registerPlugin("function", "displaySlideshow", array(__CLASS__, 'displaySlideshow'));
	}
	
	public function __call($hookName, $arguments){
		
		global $cookie, $smarty;
		
		$conf = self::getConf();
		
		if($conf['position_way'] != 'hook') return false;
		
		if(!Validate::isHookName($hookName)) return false;
		
		require_once(_PS_MODULE_DIR_."pslideshows/classes/PslideshowBlock.php");
		require_once(_PS_MODULE_DIR_."pslideshows/classes/PslideshowImg.php");
		
		$hookName = str_replace('hook','',$hookName);
		$slideshows = PslideshowBlock::getSlideshowsInHook($hookName);
		
		$html = $conf['position_way'];
		
		if(!count($slideshows)) return false;
		
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		
		foreach($slideshows as $s){
			
			$slideshow = new PslideshowBlock($s['id_pslideshow_block']);
			if(!$slideshow->active) continue;
			
			$images = $slideshow->getImages($cookie->id_lang,true);
		
			if(!count($images)) continue;
			
			$i = 0;
			foreach ($images as $img){
				$img_src = '';
				if(file_exists(_PS_ROOT_DIR_."/".$conf['img_save_path'].(int)($cookie->id_lang)."/".$img['id_pslideshow_img'].".jpg")){
					$img_src = _PS_BASE_URL_.__PS_BASE_URI__.$conf['img_save_path'].(int)($cookie->id_lang).'/'.$img['id_pslideshow_img'].'.jpg';
				}elseif(file_exists(_PS_ROOT_DIR_."/".$conf['img_save_path'].$defaultLanguage."/".$img['id_pslideshow_img'].".jpg")){
					$img_src = _PS_BASE_URL_.__PS_BASE_URI__.$conf['img_save_path'].$defaultLanguage.'/'.$img['id_pslideshow_img'].'.jpg';	
				}
				$images[$i]['src'] = $img_src;
				$i++;
			}
			
			$width = !empty($slideshow->size_width) ? $slideshow->size_width : 550;
			$height = !empty($slideshow->size_height) ? $slideshow->size_height : 300;
			
			$smarty->assign(array('slideshowId' => $slideshow->id.'_'.self::$instance,
								  'slideshowImages' => $images, 
								  'slideshowConf' => $conf, 
								  'slideshowWidth' => $width, 
								  'slideshowHeight' => $height));
			
			self::$instance++;
			$html .= Module::display(__FILE__, 'slideshow.tpl');
		}
		
		return $html;
	}
	
	public static function displaySlideshow($params,&$smarty){
		
		global $cookie, $smarty;
		
		$html = '';
		$conf = self::getConf();
		
		if($conf['position_way'] != 'tag') return $html;
		
		$pathinfo = pathinfo(__FILE__);
		// $page_name = basename($_SERVER['PHP_SELF'], '.'.$pathinfo['extension']);
		$page_name = Context::getContext()->controller->php_self;
		$locationPages = array('product','category','cms');
		
		if(!isset($params['id']) || !is_numeric($params['id'])) return $html;
		
		$id_slideshow = (int) $params['id'];
		unset($params['id']);
		
		$conf = array_merge($conf,$params);
	
		require_once(_PS_MODULE_DIR_."pslideshows/classes/PslideshowBlock.php");
		
		$slideshow = new PslideshowBlock($id_slideshow);
		
		if(!Validate::isLoadedObject($slideshow)) return $html;
		
		if(!$slideshow->active) return $html;
		
		if($slideshow->location == 'anywhere' || !in_array($page_name,$locationPages)){
			$location = 'anywhere';
		}else{
			$location = $page_name;
		}
		
		if(!$slideshow->existsLocation($location)) return $html;
		
		if(!empty($slideshow->location_id)){
			
			$slideshowCategories = explode(',',$slideshow->location_id);
			$slideshowCategories = array_map('trim',$slideshowCategories);
			$current_id = null;
			 
			if($location == 'category'){
				$current_id = (int) Tools::getValue('id_category');
			}elseif($location == 'product'){
				$current_id = (int) Tools::getValue('id_product');	
			}elseif($location == 'cms'){
				$current_id = (int) Tools::getValue('id_cms');	
			}
			
			// if(!is_null($current_id) && !in_array($current_id,$slideshowCategories)) return $html;
		}
		
		$images = $slideshow->getImages($cookie->id_lang,true);
		// $html = count($images);
		if(!count($images)) return $html;
		
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		
		$i = 0;
		foreach ($images as $img){
			$img_src = '';
			if(file_exists(_PS_ROOT_DIR_."/".$conf['img_save_path'].(int)($cookie->id_lang)."/".$img['id_pslideshow_img'].".jpg")){
				$img_src = _PS_BASE_URL_.__PS_BASE_URI__.$conf['img_save_path'].(int)($cookie->id_lang).'/'.$img['id_pslideshow_img'].'.jpg';
			}elseif(file_exists(_PS_ROOT_DIR_."/".$conf['img_save_path'].$defaultLanguage."/".$img['id_pslideshow_img'].".jpg")){
				$img_src = _PS_BASE_URL_.__PS_BASE_URI__.$conf['img_save_path'].$defaultLanguage.'/'.$img['id_pslideshow_img'].'.jpg';	
			}
			$images[$i]['src'] = $img_src;
			$i++;
		}
		
		if(isset($params['width'])){
			$width = $params['width'];
		}else{
			$width = $slideshow->size_width != '' ? $slideshow->size_width : 550;
		}
		
		if(isset($params['height'])){
			$height = $params['height'];
		}else{
			$height = $slideshow->size_height != '' ? $slideshow->size_height : 300;
		}
		
		$smarty->assign(array('slideshowId' => $slideshow->id.'_'.self::$instance,
							  'slideshowImages' => $images, 
							  'slideshowConf' => $conf, 
							  'slideshowWidth' => $width, 
							  'slideshowHeight' => $height));
		
		self::$instance++;
		// return 'asd';
		return Module::getInstanceByName('pslideshows')->display(__FILE__, 'slideshow.tpl');
	} 
	
	private function _displayForm()
	{
		$values = !empty($_POST['submitSlideshow']) ? Tools::getValue('pref') : array_merge(self::$default_values,self::getConf());
		
		$this->_html .='<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
					<fieldset>';
									
		$this->_html .= '

					<label>'.$this->l('Slideshows Positioning method').'</label> 
					<div class="margin-form">
						<select name="pref[position_way]">
							<option value="tag" '.($values['position_way'] == "tag" ? "selected" : "").'>'.$this->l('copying tag in template').' &nbsp;</option>
							<option value="hook" '.($values['position_way'] == "hook" ? "selected" : "").'>'.$this->l('using prestashop hooks').' &nbsp;</option>
						</select>
					</div>
				
					<br /><div class="clear"></div>
					
					<label>'.$this->l('Transition Effect').'</label> 
					<div class="margin-form">
						<select name="pref[effect]">
							<option value="horizontal" '.($values['effect'] == "horizontal"? "selected" : "").'>'.$this->l('Slide horizontal').' &nbsp;</option>
							<option value="vertical" '.($values['effect'] == "vertical" ? "selected" : "").'>'.$this->l('Slide vertical').' &nbsp;</option>
							<option value="fade" '.($values['effect'] == "fade" ? "selected" : "").'>'.$this->l('Fade').' &nbsp;</option>
						</select>
					</div>
				
					<br /><div class="clear"></div>
				
					<label>'.$this->l('Transition interval').'</label> 
					<div class="margin-form">
					<input type="text" name="pref[transition_pause]" value="'.$values['transition_pause'].'" size="3"  /> '.$this->l('Milliseconds').'
					</div>	
					
					<br /><div class="clear"></div>
					
					<label>'.$this->l('Transition speed').'</label> 
					<div class="margin-form">
					<input type="text" name="pref[transition_speed]" value="'.$values['transition_speed'].'" size="3"  /> '.$this->l('Milliseconds').'
					</div>	
					
					<br /><div class="clear"></div>
					
					<label>'.$this->l('Display title overlay').'</label>					
					<div class="margin-form">
						<input type="radio" name="pref[show_title]" id="show_title_on" value="1" '.(isset($values['show_title']) && $values['show_title'] == '1' ? 'checked' : '').'/>
						<label class="t" for="show_title_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
						<input type="radio" name="pref[show_title]" id="show_title_off" value="0" '.(isset($values['show_title']) && $values['show_title'] == '0' ? 'checked' : '').'/>
						<label class="t" for="show_title_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>			
					</div>
					
					<br /><div class="clear"></div>
					
					<label>'.$this->l('Display pager').'</label>
					<div class="margin-form">
						<input type="radio" name="pref[show_pager]" id="show_pager_on" value="1" '.(isset($values['show_pager']) && $values['show_pager'] == '1' ? 'checked' : '').'/>
						<label class="t" for="show_pager_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
						<input type="radio" name="pref[show_pager]" id="show_pager_off" value="0" '.(isset($values['show_pager']) && $values['show_pager'] == '0' ? 'checked' : '').'/>
						<label class="t" for="show_pager_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>			
					</div>
					
					<br /><div class="clear"></div>
					
					<label>'.$this->l('Display arrows').'</label>
					<div class="margin-form">
						<input type="radio" name="pref[show_arrows]" id="show_arrows_on" value="1" '.(isset($values['show_arrows']) && $values['show_arrows'] == '1' ? 'checked' : '').'/>
						<label class="t" for="show_arrows_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
						<input type="radio" name="pref[show_arrows]" id="show_arrows_off" value="0" '.(isset($values['show_arrows']) && $values['show_arrows'] == '0' ? 'checked' : '').'/>
						<label class="t" for="show_arrows_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>			
					</div>
					
					<br /><div class="clear"></div>
					
					<label>'.$this->l('Random start').' </label>
					<div class="margin-form">
						<input type="radio" name="pref[random_start]" id="random_start_on" value="1" '.(isset($values['random_start']) && $values['random_start'] == '1' ? 'checked' : '').'/>
						<label class="t" for="random_start_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
						<input type="radio" name="pref[random_start]" id="random_start_off" value="0" '.(isset($values['random_start']) && $values['random_start'] == '0' ? 'checked' : '').'/>
						<label class="t" for="random_start_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>			
					</div>
					
					<input class="button" name="submitPslideshow" value="'.$this->l('Update settings').'" type="submit" />
					
				</fieldset>
			</form>';
	}
	
	public function getContent()
	{
		$this->checkServerConf();
		if($this->warning != ''){
			$this->_html .= '<div style="width:680px;" class="warning bold">'.$this->warning.'</div>';
		}
		
		$this->_html = '<h2>'.$this->l('Slideshow anywhere settings').'</h2>';
		$this->_html .= '<p>'.$this->l('If you want to add slideshows, you must go to the "Slideshows" sub tab on the navigation menu').'</p>';
		if (!empty($_POST))
		{
			$this->_postValidation();
			if (!isset($this->_postErrors) || !sizeof($this->_postErrors)){
				$this->_postProcess();
			}else{
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error">'. $err .'</div>';
			}
		}else{
			$this->_html .= '<br />';
		}
		$this->_displayForm();
		return $this->_html;
	}
	
	private function _postValidation()
	{
		if (isset($_POST['submitPslideshow']))
		{
			if (empty($_POST['pref']['transition_pause']) || !is_numeric($_POST['pref']['transition_pause']))
				$this->_postErrors[] = $this->l('Numeric transition interval is required.');
				
			if (empty($_POST['pref']['transition_speed']) || !is_numeric($_POST['pref']['transition_speed']))
				$this->_postErrors[] = $this->l('Numeric transition speed is required.');
		}
	}
	
	private function _postProcess()
	{
		if (Tools::isSubmit('submitPslideshow'))
		{
			$pref = $_POST['pref'];
			$old_values = self::getConf();
	
			$new_values = array_merge(self::$default_values,$pref);
			Configuration::updateValue('PSLIDESHOW_CONF',base64_encode(serialize($new_values)));			
		}
		
		$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" /> '.$this->l('Settings updated').'</div>';
	}
	
	public static function getConf(){
		if(is_null(self::$pref)){
			$config = Configuration::get('PSLIDESHOW_CONF');
			$options = self::$default_values;

			if($config) $options = array_merge($options,unserialize(base64_decode($config)));
			self::$pref = $options;
		}
		return self::$pref;
	}
	
	public function checkServerConf()
	{
		$pref = self::getConf();
		
		if(!is_dir(_PS_ROOT_DIR_.'/'.$pref['img_save_path'])){
			$this->warning = _PS_ROOT_DIR_.'/'.$pref['img_save_path'].' '.$this->l('must be created and writable');
			
		}elseif (!is_writable(_PS_ROOT_DIR_.'/'.$pref['img_save_path'])){
			$this->warning = _PS_ROOT_DIR_.'/'.$pref['img_save_path'].' '.$this->l('must be writable');
			
		}elseif (!is_writable(_PS_MODULE_DIR_.$this->name.'/cache')){
			$this->warning = _PS_MODULE_DIR_.$this->name.'/cache '.$this->l('must be writable');
		}
	}
}