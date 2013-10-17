<?php

/**
  * AdminPslideshowImg tab for admin panel
  * @category admin
  *
  * @author Appside
  * @version 1.0
  *
  */

// include_once(_PS_ADMIN_DIR_.'/../classes/AdminTab.php');
require_once(_PS_MODULE_DIR_."pslideshows/classes/PslideshowImg.php");
require_once(_PS_MODULE_DIR_."pslideshows/classes/PslideshowBlock.php");
require_once(_PS_MODULE_DIR_."pslideshows/pslideshows.php");

class AdminPslideshowImg extends AdminTab
{
	protected $moduleConf;
	protected $maxImageSize = 200000;
	
	public function __construct()
	{
	 	$this->table = 'pslideshow_img';
	 	$this->className = 'PslideshowImg';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->view = false;
	 	$this->delete = true;
		
		$this->moduleConf = Pslideshows::getConf();
		
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
				
		$languages = Language::getLanguages();
		foreach ($languages as $l){
			$this->fieldImageSettings[] = array('name' => 'img_'.$l['id_lang'], 'dir' => '../'.$this->moduleConf['img_save_path'].$l['id_lang']);
		}
		
		$this->fieldsDisplay = array(
			'id_pslideshow_img' => array('title' => $this->l('ID image'), 'align' => 'center', 'width' => 25),
			'id_pslideshow_block' => array('title' => $this->l('ID slideshow'), 'align' => 'center', 'width' => 25),
			'active' => array('title' => $this->l('Active'), 'width' => 25, 'align' => 'center', 'icon' => array(1 => 'enabled.gif', 0 => 'forbbiden.gif', 'default' => 'unknown.gif'), 'search' => false, 'active' => 'status', 'type' => 'bool'),
			'title' => array('title' => $this->l('Title'), 'width' => 300),
			'image' => array('title' => $this->l('Image'), 'align' => 'center', 'image' => '../'.$this->moduleConf['img_save_path'].$defaultLanguage, 'orderby' => false, 'search' => false)
		);
		
		parent::__construct();
	}
	
	public function displayForm($isMainTab = true)
	{
		global $currentIndex, $cookie;
		
		if (!($obj = $this->loadObject(true)))
		return;
		
		$defaultLanguage = intval($cookie->id_lang);
		$languages = Language::getLanguages();
		$divLangName = 'title¤link¤img';
			
		echo '
		<script type="text/javascript">
			id_language = Number('.$defaultLanguage.');
			
			function deleteImg(id_lang){
				$("#displayImg_"+id_lang).remove(); 
				$("#uploadImg_"+id_lang).css("display","block"); 
				var newInput = $("<input type=\'hidden\' name=\'delImg[]\' value=\'"+id_lang+"\' />");
				$("#slideForm").append(newInput);	
				return false;
			}
		</script>';
		
		$pslideshowBlockToken = Tools::getAdminToken("AdminPslideshowBlock".intval(Tab::getIdFromClassName("AdminPslideshowBlock")).intval($cookie->id_employee));
		
		if(Tools::getValue('id_pslideshow_block')){
			$block_id = Tools::getValue('id_pslideshow_block');
		}
		
		if(isset($block_id))
			echo '<a href="index.php?tab=AdminPslideshowBlock&updatepslideshow_block&id_pslideshow_block='.$block_id.'&token='.$pslideshowBlockToken.'"><img src="../img/admin/arrow2.gif"> Back to slideshow</a><br /><br />';
		
		echo '<form id="slideForm" action="'.$currentIndex.'&submitAdd'.$this->table.'AndStay=1&token='.$this->token.'" method="post" enctype="multipart/form-data">
				'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
				<fieldset>
				<legend><img src="../img/admin/cms.gif" />'.$this->l('Slideshow image').'</legend>';
			
		// META TITLE
		echo '	<label>'.$this->l('Title').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '	<div id="title_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">
						<input size="50" type="text" name="title_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'title', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" />
						<p>'.$this->l('Title to display in overlay or image ALT attribute').'</p>
					</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'title');
		echo '	</div><div class="clear space">&nbsp;</div>';

				// IMG1
		echo '	<label>'.$this->l('Image').' </label>
				<div class="margin-form">';
				foreach ($languages as $language){
					echo '	<div id="img_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').'; float: left;">';

					$uploadDisplay = 'none';
					if(!file_exists(_PS_ROOT_DIR_."/".$this->moduleConf['img_save_path'].$language['id_lang']."/".$obj->id.".jpg")){
						$uploadDisplay = '';
					}
					
					echo '<div id="uploadImg_'.$language['id_lang'].'" style="display:'.$uploadDisplay.'">
							<input  type="file" name="img_'.$language['id_lang'].'" /> .jpg .png .gif  <sup>*</sup>
						</div>';
	
					if(file_exists(_PS_ROOT_DIR_."/".$this->moduleConf['img_save_path'].$language['id_lang']."/".$obj->id.".jpg")){
						echo '<div id="displayImg_'.$language['id_lang'].'">
								<img src="'.__PS_BASE_URI__.'modules/pslideshows/timthumb.php?src='.__PS_BASE_URI__.$this->moduleConf['img_save_path'].$language['id_lang'].'/'.$obj->id.'.jpg&w=300&t='.time().'" />
								<a onclick="deleteImg('.$language['id_lang'].'); return false;" href="'.$currentIndex.'&token='.$this->token.'&id_pslideshow_img='.$obj->id.'&updateimage_block&delimg=1&id_lang='.$language['id_lang'].'"><img src="'.__PS_BASE_URI__.'img/admin/delete.gif" alt="delete" /></a>
							</div>';
					}
					echo '</div>';
				}
				$this->displayFlags($languages, $defaultLanguage, $divLangName, 'img');
		echo '</div><div class="clear space">&nbsp;</div>';
				
		// LINK
		echo '	<label>'.$this->l('Link').' </label>
				<div class="margin-form">';
		foreach ($languages as $language)
			echo '	<div id="link_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
						<input type="text" size="50" id="link_'.$language['id_lang'].'" name="link_'.$language['id_lang'].'" value="'.htmlentities(stripslashes($this->getFieldValue($obj, 'link', $language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /> 
						<p>Ex : http://www.google.com</p>
					</div>';
		$this->displayFlags($languages, $defaultLanguage, $divLangName, 'link');
		echo '</div>
		<div class="clear"></div>';
		
		echo '<label>'.$this->l('Open link in a new page').' </label>
			<div class="margin-form">
				<input type="radio" name="target_blank" id="target_blank_on" value="1" '.(($obj->id && $this->getFieldValue($obj, 'target_blank') == 1) ? 'checked="checked" ' : '').'/>
				<label class="t" for="target_blank_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
				<input type="radio" name="target_blank" id="target_blank_off" value="0" '.((!$obj->id OR $this->getFieldValue($obj,'target_blank') == 0) ? 'checked="checked" ' : '').'/>
				<label class="t" for="target_blank_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>			
			</div>
			<div class="clear">&nbsp;</div>';
		
		echo '<label>'.$this->l('Status').' </label>
			<div class="margin-form">
				<input type="radio" name="active" id="active_on" value="1" '.((!$obj->id OR $this->getFieldValue($obj, 'active') == 1) ? 'checked="checked" ' : '').'/>
				<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
				<input type="radio" name="active" id="active_off" value="0" '.($obj->id && $this->getFieldValue($obj, 'active') == 0 ? 'checked="checked" ' : '').'/>
				<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>			
			</div>
			<div class="clear">&nbsp;</div>';
		
		
			echo '<label>'.$this->l('Slideshow').' </label>
					<div class="margin-form">';
			
		$slideshowBlocks = PslideshowBlock::listSlideshows();
		if(count($slideshowBlocks)){
		 
						echo '<select name="id_pslideshow_block">';
							foreach($slideshowBlocks as $s){
								echo '<option value="'.$s['id_pslideshow_block'].'" '.($this->getFieldValue($obj, 'id_pslideshow_block') == $s['id_pslideshow_block'] ? 'selected' : '').'> &nbsp; '.$s['name'].' &nbsp; </option>';
							}
				echo '</select> <p>'.$this->l('Slideshow where image will appear.').'</p>';
		}else{
			echo '<p>'.$this->l('There is no slideshows for the moment.').'</p>';
		}
		
		echo '</div>
				<div class="clear">&nbsp;</div>';
		
		// SUBMIT
		echo '	<div class="margin-form space">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
	
	public function postProcess()
	{
		global $currentIndex, $cookie;
		
		$defaultLanguage = new Language(intval(Configuration::get('PS_LANG_DEFAULT')));
		
		if (Tools::getValue('delImg')){
			$delImgArray = Tools::getValue('delImg');
			$id_image = Tools::getValue('id_pslideshow_img');
			
			if(is_array($delImgArray) && count($delImgArray) > 0){
				
				if(in_array($defaultLanguage->id,$delImgArray) && (!isset($_FILES['img_'.$defaultLanguage->id]) || empty($_FILES['img_'.$defaultLanguage->id]['name']))){
					$this->_errors[] = $this->l('the field').' <b> Image </b> '.$this->l('is required at least in').' '.$defaultLanguage->name;
				}else{
					foreach($delImgArray as $lang){
						if(file_exists(_PS_ROOT_DIR_."/".$this->moduleConf['img_save_path'].$lang."/".$id_image.".jpg")){
							unlink(_PS_ROOT_DIR_."/".$this->moduleConf['img_save_path'].$lang."/".$id_image.".jpg");
						}
					}
				}
			}
		}elseif(isset($_GET['delete'.$this->table]) || Tools::getValue('submitDel'.$this->table)){
		
				$imgblock_items = array();
				if(Tools::getValue('submitDel'.$this->table)){
					$imgblock_items = $_POST[$this->table.'Box'];
				}elseif(isset($_GET['delete'.$this->table])){
					$imgblock_items[0] = Tools::getValue($this->identifier);
				}
				
				foreach($imgblock_items as $val){
					$id_image = $val;
					$languages = Language::getLanguages();
					foreach ($languages as $language){
						if(file_exists(_PS_ROOT_DIR_."/".$this->moduleConf['img_save_path'].$language['id_lang']."/".$id_image.".jpg")){
							@unlink(_PS_ROOT_DIR_."/".$this->moduleConf['img_save_path'].$language['id_lang']."/".$id_image.".jpg");
						}
					}
				}
		}elseif(isset($_GET['status'])){	
			echo "trest"; exit();
			if($object->toggleStatus())
					Tools::redirectAdmin($currentIndex.'&conf=5&'.$this->identifier.'='.Tools::getValue($this->identifier).'&updatepslideshow_block&token='.$this->token);
				else
					$this->_errors[] = Tools::displayError('An error occurred while updating status.');
	
		}elseif (Tools::getValue('position')){
			if ($this->tabAccess['edit'] !== '1')
				$this->_errors[] = Tools::displayError('You do not have permission to edit here.');
			elseif (!Validate::isLoadedObject($object = $this->loadObject()))
				$this->_errors[] = Tools::displayError('An error occurred while updating status for object.').' <b>'.$this->table.'</b> '.Tools::displayError('(cannot load object)');
			elseif (!$object->updatePosition((int)(Tools::getValue('way')), (int)(Tools::getValue('position'))))
				$this->_errors[] = Tools::displayError('Failed to update the position.');
			else
				Tools::redirectAdmin($currentIndex.'&'.$this->table.'Orderby=position&'.$this->table.'Orderway=asc&conf=4'.(($id_category = (int)(Tools::getValue('id_cms_category'))) ? ('&id_cms_category='.$id_category) : '').'&token='.Tools::getAdminTokenLite('AdminCMSContent'));
		}

		if(!Tools::getValue('id_pslideshow_img') && Tools::getValue('submitAddpslideshow_img') && (!isset($_FILES['img_'.$defaultLanguage->id]) || empty($_FILES['img_'.$defaultLanguage->id]['name'])))
			$this->_errors[] = $this->l('the field').' <b> Image </b> '.$this->l('is required at least in').' '.$defaultLanguage->name;
		
		return parent::postProcess(true);
	}
	
	
	protected function postImage($id)
	{
		$languages = Language::getLanguages();
		foreach ($languages as $l){
			if(!is_dir(_PS_ROOT_DIR_."/".$this->moduleConf['img_save_path'].$l['id_lang']."/")){
				 mkdir(_PS_ROOT_DIR_."/".$this->moduleConf['img_save_path'].$l['id_lang'],0777);
			}
		}	
		return parent::postImage($id);
	}
	
	protected function l($string, $class = __CLASS__, $addslashes = FALSE, $htmlentities = TRUE)
	{		
		global $_MODULES, $_MODULE, $cookie;
		$id_lang = (!isset($cookie) OR !is_object($cookie)) ? intval(Configuration::get('PS_LANG_DEFAULT')) : intval($cookie->id_lang);
		$file = _PS_MODULE_DIR_.'pslideshows/'.Language::getIsoById($id_lang).'.php';
		if (file_exists($file) AND include_once($file))
			$_MODULES = !empty($_MODULES) ? array_merge($_MODULES, $_MODULE) : $_MODULE;
		if (!is_array($_MODULES))
			return (str_replace('"', '&quot;', $string));
		$source = Tools::strtolower(get_class($this));
		$string2 = str_replace('\'', '\\\'', $string);
		$currentKey = '<{pslideshows}'._THEME_NAME_.'>adminpslideshowimg_'.md5($string2);
		$defaultKey = '<{pslideshows}prestashop>adminpslideshowimg_'.md5($string2);
		
		$_MODULES = array_change_key_case($_MODULES,CASE_LOWER);
		
		if (key_exists($currentKey, $_MODULES)){
			$ret = stripslashes($_MODULES[$currentKey]);	
		}elseif (key_exists($defaultKey, $_MODULES))
			$ret = stripslashes($_MODULES[$defaultKey]);
		else{
			$ret = $string;
			return parent::l($string);
		}
		return str_replace('"', '&quot;', $ret);
	}
	
}

?>
