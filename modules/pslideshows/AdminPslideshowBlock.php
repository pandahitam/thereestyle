<?php

/**
  * AdminPslideshowBlock tab for admin panel
  * @category admin
  *
  * @author AppSide
  * @version 1.0
  *
  */

// include_once(_PS_ADMIN_DIR_.'/../classes/AdminTab.php');
require_once(_PS_MODULE_DIR_."pslideshows/classes/PslideshowBlock.php");
require_once(_PS_MODULE_DIR_."pslideshows/pslideshows.php");
require_once(_PS_MODULE_DIR_."pslideshows/AdminPslideshowImg.php");

class AdminPslideshowBlock extends AdminTab
{	
	public function __construct()
	{
	 	$this->table = 'pslideshow_block';
	 	$this->className = 'PslideshowBlock';
	 	$this->lang = false;
	 	$this->edit = true;
	 	$this->view = false;
	 	$this->delete = true;
		
	 	$this->moduleConf = Pslideshows::getConf();
	 	
	 	$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		
	 	if($this->moduleConf['position_way'] == 'hook'){
	 		$locationField = 'hook';
	 	}else{
	 		$locationField = 'location_name';
	 		
	 		$this->_select = ' CASE WHEN a.location = "anywhere" THEN "Anywhere" 
							WHEN a.location = "category" THEN "Category pages"
							WHEN a.location = "product" THEN "Product pages"
							WHEN a.location = "cms" THEN "CMS pages"
							END AS "location_name", COUNT(img.id_pslideshow_img) AS nb_img ';
	 	}
	 	
		$this->fieldsDisplay = array(
			'id_pslideshow_block' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
			'name' => array('title' => $this->l('Name'), 'width' => 200),
			'active' => array('title' => $this->l('Active'), 'width' => 25, 'align' => 'center', 'icon' => array(1 => 'enabled.gif', 0 => 'forbbiden.gif', 'default' => 'unknown.gif'), 'search' => false, 'active' => 'status', 'type' => 'bool'),
			$locationField => array('title' => $this->l('Location'), 'width' => 100),
			'location_id' => array('title' => $this->l('Location ID\'s'), 'width' => 100),
			'nb_img' => array('title' => $this->l('Num. images'), 'width' => 100),
		);
	
		$this->_join .= ' LEFT JOIN '._DB_PREFIX_.'pslideshow_img as img ON img.id_pslideshow_block = a.id_pslideshow_block';
		$this->_group .= ' GROUP BY a.id_pslideshow_block';
		
		$this->fieldsDisplayPslideshow = array(
				'id_pslideshow_img' => array('title' => $this->l('ID image'), 'align' => 'center', 'width' => 25),
				'id_pslideshow_block' => array('title' => $this->l('ID slideshow'), 'align' => 'center', 'width' => 25),
				'active' => array('title' => $this->l('Active'), 'width' => 25, 'align' => 'center', 'icon' => array(1 => 'enabled.gif', 0 => 'forbbiden.gif', 'default' => 'unknown.gif'), 'search' => false, 'active' => 'status', 'type' => 'bool'),
				'title' => array('title' => $this->l('Title'), 'width' => 250),
				'link' => array('title' => $this->l('Link'), 'width' => 250),
				'image' => array('title' => $this->l('Image'), 'align' => 'center', 'image' => '../'.$this->moduleConf['img_save_path'].$defaultLanguage, 'orderby' => false, 'search' => false)
		);
		
		$this->_includeTabTitle[] = $this->l('All slideshows images');
		$this->_includeTab['PslideshowImg'] = array('fieldsDisplay' => $this->fieldsDisplayPslideshow);

		if(!is_dir(_PS_ROOT_DIR_.'/'.$this->moduleConf['img_save_path'])){
			$this->_errors[] = _PS_ROOT_DIR_.'/'.$this->moduleConf['img_save_path'].' '.Tools::displayError('must be created and writable');
		}elseif (!is_writable(_PS_ROOT_DIR_.'/'.$this->moduleConf['img_save_path'])){
			$this->_errors[] = _PS_ROOT_DIR_.'/'.$this->moduleConf['img_save_path'].' '.Tools::displayError('must be writable');
		}elseif (!is_writable(_PS_MODULE_DIR_.'pslideshows/cache')){
			$this->_errors[] = _PS_MODULE_DIR_.'pslideshows/cache '.$this->l('must be writable');
		}
	
		parent::__construct();
	}
	
	
	public function displayForm($isMainTab = true)
	{
		global $currentIndex, $cookie;
		
		parent::displayForm();
		
		if (!($obj = $this->loadObject(true))) return;
			
		$defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages();
			
		echo '<script type="text/javascript">
				$(document).ready(function(){
					$(".location_help").hide();
					$("#location_select").change(function(){ displayLocationHelp($(this).val()); });
					displayLocationHelp($("#location_select").val());
				});
			
				function displayLocationHelp(currentLocation){
					$(".location_help").hide();
					$("#location_id").hide();
					if($("#help_"+currentLocation).length > 0)
						$("#help_"+currentLocation).fadeIn();
					if(currentLocation != "anywhere"){
						$("#location_id").fadeIn();
					}
				}
			</script>
		
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'AndStay=1&token='.$this->token.'" method="post" enctype="multipart/form-data">
			'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/admin/cms.gif" />'.$this->l('Slideshow').'</legend>';
			
		// Name
		echo '<label>'.$this->l('Internal name').'</label>
			 <div class="margin-form">
				<input size="40" type="text" name="name" value="'.htmlentities($this->getFieldValue($obj,'name'),ENT_COMPAT, 'UTF-8').'" />
			</div>
		
		<div class="clear">&nbsp;</div>';
		
		if($this->moduleConf['position_way'] == 'tag'){
			
			$locationOptions = array('anywhere' => $this->l('Anywhere'),
									 'cms' => $this->l('CMS Pages'),
									 'category' => $this->l('Category page'),
									 'product' => $this->l('Product page')); 
			
			// Location
			echo '<label>'.$this->l('Location').' </label>
				<div class="margin-form">
					
					<div style="float:left;">
						<select id="location_select" name="location" style="width:150px;padding:2px;">';
						foreach($locationOptions as $value => $text){
							echo '<option value="'.$value.'" '.($this->getFieldValue($obj,'location') == $value ? 'selected' : '').' >&nbsp;'.$text.'&nbsp;&nbsp;</option>';
						}
				  	echo '</select>
				  	</div>
				  
				  	<div style="float:left; margin-left:10px;" id="location_id">
				  		'.$this->l('Only for ID\'s').' : <input size="25" type="text" name="location_id" value="'.htmlentities($this->getFieldValue($obj,'location_id'),ENT_COMPAT, 'UTF-8').'" /> 
				  		'.$this->l('Separated with a comma , ex : 5,7,9').'
				  	</div>
					<div class="clear"></div>
					
				 </div><div class="clear"></div>';
			
			echo '<div id="help_anywhere" class="location_help margin-form">
					<p class="hint" style="display: block; font-size: 11px; width:600px;">'.$this->l('Go to your theme directory').' : themes/'._THEME_NAME_.'/ <br />
					 '.$this->l('and copy the following tag in the template (.tpl) where you want to make appear the slideshow').' : 
					 <br /> <span style="font-size:13px; font-weight:bold;"> {displaySlideshow id='.($obj->id ? $obj->id : 'ID_SLIDESHOW').'} </span></p>
				  </div>';
			
			echo '<div id="help_category" class="margin-form location_help">
					 <p class="hint" style="display: block; font-size: 11px; width:600px;">'.$this->l('Go to your theme category page').' : themes/'._THEME_NAME_.'/category.tpl <br />
					 '.$this->l('and copy the following tag where you want to make appear the slideshow').' : 
					 <br /> <span style="font-size:13px; font-weight:bold;"> {displaySlideshow id='.($obj->id ? $obj->id : 'ID_SLIDESHOW').'} </span></p>
			</div>';
			
			echo '<div id="help_product" class="margin-form location_help">
					 <p class="hint" style="display: block; font-size: 11px; width:600px;">'.$this->l('Go to your theme product page').' : themes/'._THEME_NAME_.'/product.tpl <br />
					 '.$this->l('and copy the following tag where you want to make appear the slideshow').' : 
					 <br /> <span style="font-size:13px; font-weight:bold;"> {displaySlideshow id='.($obj->id ? $obj->id : 'ID_SLIDESHOW').'} </span></p>
			</div>';
			
			echo '<div id="help_cms" class="margin-form location_help">
					<p class="hint" style="display: block; font-size: 11px; width:600px;">'.$this->l('Go to your theme cms page').' : themes/'._THEME_NAME_.'/cms.tpl <br />
					 '.$this->l('and copy the following tag where you want to make appear the slideshow').' : 
					 <br /> <span style="font-size:13px; font-weight:bold;"> {displaySlideshow id='.($obj->id ? $obj->id : 'ID_SLIDESHOW').'} </span></p>
			</div>
			
			<div class="clear" style="height:1px;">&nbsp;</div>';
			
			$moduleToken = Tools::getAdminToken("AdminModules".intval(Tab::getIdFromClassName("AdminModules")).intval($cookie->id_employee));
			
			echo '<label> &nbsp; </label>
				  <div class="margin-form">
					<p><a href="index.php?tab=AdminModules&token='.$moduleToken.'&configure=pslideshows"> &raquo; '.$this->l('If you where unable to copy tag in template, go to module configuration and use positioning method "Prestashop hooks"').'</a></p>
				  </div>';
		
		}elseif($this->moduleConf['position_way'] == 'hook'){
			
			$hooks = array('rightColumn' => $this->l('Right column blocks'),
						   'leftColumn' => $this->l('Left column blocks'),
						   'home' => $this->l('Homepage content'),
						   'top' => $this->l('Top of pages'),
						   'footer'  => $this->l('Footer'),
						   'productfooter' => $this->l('Product footer'),
						   'customerAccount'  => $this->l('Customer account page display in front office'),
						   'createAccountForm' => $this->l('Customer account creation form'),
						   'createAccountTop' => $this->l('Block above the form for create an account'),
						   'myAccountBlock' => $this->l('My account block'),
						   'beforeCarrier' => $this->l('Before carrier list'),
						   'extraRight' => $this->l('Extra actions on the product page (right column)'),
						   'extraLeft' => $this->l('Extra actions on the product page (left column)'),
						   'shoppingCart' => $this->l('Shopping cart footer'),
						   'paymentTop' => $this->l('Top of payment page')
							); 
			
			$hookValue = $this->getFieldValue($obj,'hook');
			$hookOther = '';
			if(!empty($hookValue) && !array_key_exists($hookValue,$hooks)){
				$hookOther = $hookValue;
			}
		
			// Location
			echo '<label>'.$this->l('Hook').' </label>
			<div class="margin-form">
			
				<div style="float:left;">
					<select id="hook_select" name="hook" style="padding:2px;">
						<option value=""> ------------- </option>';
					foreach($hooks as $value => $text){
						echo '<option value="'.$value.'" '.($this->getFieldValue($obj,'hook') == $value ? 'selected' : '').' >&nbsp;'.$value.' - '.$text.'&nbsp;&nbsp;</option>';
					}
			  	echo '</select>
			  	</div>
			  	
		  		<div style="float:left; margin-left:10px;">
			  		'.$this->l('Other').' : &nbsp; hook<input size="10" type="text" name="hook_other" value="'.$hookOther.'" /> Ex : center
			  	</div>
				  	
			 </div>
			 <div class="clear">&nbsp;</div>';
		  	
		}
		
		echo '<label>'.$this->l('Status').' </label>
			<div class="margin-form">
				<input type="radio" name="active" id="active_on" value="1" '.((!$obj->id OR $this->getFieldValue($obj, 'active') == 1) ? 'checked="checked" ' : '').'/>
				<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="'.$this->l('Enabled').'" title="'.$this->l('Enabled').'" /></label>
				<input type="radio" name="active" id="active_off" value="0" '.($obj->id && $this->getFieldValue($obj, 'active') == 0 ? 'checked="checked" ' : '').'/>
				<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="'.$this->l('Disabled').'" title="'.$this->l('Disabled').'" /></label>			
			</div>
			<div class="clear">&nbsp;</div>

			<label>'.$this->l('Size').'</label>
			<div class="margin-form">
				&nbsp;&nbsp; '.$this->l('Width').' <input type="text" name="size_width" value="'.$this->getFieldValue($obj,'size_width').'" size="4" /> px
				&nbsp;&nbsp; '.$this->l('Height').' <input type="text" name="size_height" value="'.$this->getFieldValue($obj,'size_height').'" size="4" /> px
			</div>';
			
		// SUBMIT
		echo '<div class="margin-form space">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
		
			</fieldset>
		</form>
		<div class="clear space">&nbsp;</div>';
		
		if($obj->id){
			
			$slideshowImages = $obj->getImages((int)($cookie->id_lang));
			$nbImages = sizeof($slideshowImages);
			
			echo '<h2>'.$this->l('Slideshow images').' ('.$nbImages.')</h2>';
			
			echo '<p><a href="index.php?tab=AdminPslideshowBlock&addpslideshow_img&id_pslideshow_block='.$obj->id.'&token='.$this->token.'"> <img border="0" src="../img/admin/add.gif"> '.$this->l('Add new image in this slideshow').'</a><br /><br /></p>';
			
			if (!empty($slideshowImages) && $nbImages)
			{			
				echo '<table cellspacing="0" cellpadding="0" class="table widthfull">
						<tr>
							<th>ID</th>
							<th>'.$this->l('Title').'</th>
							<th>'.$this->l('Link').'</th>
							<th>'.$this->l('Active').'</th>
							<th>'.$this->l('Image').'</th>
							<th>'.$this->l('Position').'</th>
							<th>'.$this->l('Actions').'</th>
						</tr>';
				$irow = 0;
				foreach ($slideshowImages AS $k => $img)
				{
					echo '<tr class="'.($irow++ % 2 ? 'alt_row' : '').'">
					
						  <td>'.$img['id_pslideshow_img'].'</td>
						  <td>'.$img['title'].'</td>
						  <td>'.$img['link'].'</td>';
					echo '<td><a href="'.$currentIndex.'&id_pslideshow_block='.$obj->id.'&updatepslideshow_block&id_pslideshow_img='.(int) $img['id_pslideshow_img'].'&status&token='.$this->token.'">'.($img['active'] == '1' ? '<img src="../img/admin/enabled.gif" alt="Enabled" />' : '<img src="../img/admin/disabled.gif" alt="Disabled" />').'</a></td>';
					
					if(file_exists(_PS_ROOT_DIR_."/".$this->moduleConf['img_save_path'].(int)($cookie->id_lang)."/".$img['id_pslideshow_img'].".jpg")){
						//img in current lang	
						$img_src = __PS_BASE_URI__.$this->moduleConf['img_save_path'].(int)($cookie->id_lang).'/'.$img['id_pslideshow_img'].'.jpg';
					}elseif(file_exists(_PS_ROOT_DIR_."/".$this->moduleConf['img_save_path'].$defaultLanguage."/".$img['id_pslideshow_img'].".jpg")){
						//img in default lang
						$img_src = __PS_BASE_URI__.$this->moduleConf['img_save_path'].$defaultLanguage.'/'.$img['id_pslideshow_img'].'.jpg';	
					}
					
					echo '<td><img class="imgm" src="'.__PS_BASE_URI__.'modules/pslideshows/timthumb.php?src='.$img_src.'&h=45" alt="image" /></td>';
					
					echo '<td>';
						if($img['position'] != ($nbImages-1)){
							echo '<a href="'.$currentIndex.'&id_pslideshow_block='.$obj->id.'&updatepslideshow_block&id_pslideshow_img='.(int) $img['id_pslideshow_img'].'&way=1&position='.($img['position']+1).'&token='.$this->token.'"><img src="../img/admin/down.gif" alt="down" /></a>';
						}
						
						if($img['position'] != 0){
							echo '&nbsp;<a href="'.$currentIndex.'&id_pslideshow_block='.$obj->id.'&updatepslideshow_block&id_pslideshow_img='.(int) $img['id_pslideshow_img'].'&way=0&position='.($img['position']-1).'&token='.$this->token.'"><img src="../img/admin/up.gif" alt="up" /></a>';
						}
					echo '</td>';
					
					echo '<td>
							<a href="'.$currentIndex.'&id_pslideshow_block='.$obj->id.'&id_pslideshow_img='.(int) $img['id_pslideshow_img'].'&updatepslideshow_img&token='.$this->token.'"><img src="../img/admin/edit.gif" alt="view" /></a>
							<a href="'.$currentIndex.'&id_pslideshow_block='.$obj->id.'&updatepslideshow_block&id_pslideshow_img='.(int) $img['id_pslideshow_img'].'&deletepslideshow_img&token='.$this->token.'" onclick="if(!confirm(\''.addslashes($this->l('This action will delete definitely this image, are you sure ?')).'\')) return false;"><img src="../img/admin/delete.gif" alt="delete" /></a>
						</td>';
						
					echo '</tr>';
				}
				echo '</table>';
			
			}else
				echo $this->l('No images in this slideshow.').'<br /><br />';
		}	
	}
	
	public function getList($id_lang, $orderBy = NULL, $orderWay = NULL, $start = 0, $limit = NULL, $id_lang_shop = false)
	{	
		echo "<h2>".$this->l('Slideshows')."</h2>";
		parent::getList($id_lang,$orderBy,$orderWay,$start,$limit,$id_lang_shop);
	}
	
	public function postProcess()
	{	
		global $currentIndex, $cookie;
		
		if(Tools::getValue('submitAdd'.$this->table) && $this->moduleConf['position_way'] == 'hook'){
			
			$slideshow_id = Tools::getValue($this->identifier);
			
			if(Tools::getValue('hook_other') != ''){
				$hookName = Tools::getValue('hook_other');
			}elseif(Tools::getValue('hook') != ''){
				$hookName = Tools::getValue('hook');
			}
			$module = new Pslideshows();
			
			if($slideshow_id && is_numeric($slideshow_id)){
				$slideshowBlock = new PslideshowBlock($slideshow_id);
		
				if(!$slideshowBlock->checkHookHasSlideshow() && trim($slideshowBlock->hook != '')){
					$slideshowBlock->unregisterHook($module->id);
				}
			}
				
			$module->registerHook('header');
			
			if(isset($hookName))
				$module->registerHook($hookName);
		}
		
		if(Tools::getValue($this->identifier) && Tools::getValue('id_pslideshow_img')){
			
			$id_pslideshow_img = (int) Tools::getValue('id_pslideshow_img');
			$psImg = new PslideshowImg($id_pslideshow_img);
			
			if(isset($_GET['position'])){
				if($psImg->updatePosition((int)(Tools::getValue('way')), (int)(Tools::getValue('position'))))
					Tools::redirectAdmin($currentIndex.'&conf=4&'.$this->identifier.'='.Tools::getValue($this->identifier).'&updatepslideshow_block&token='.$this->token);
				else
					$this->_errors[] = Tools::displayError('Failed to update the position.');
			}elseif(isset($_GET['status'])){
				if($psImg->toggleStatus())
					Tools::redirectAdmin($currentIndex.'&conf=5&'.$this->identifier.'='.Tools::getValue($this->identifier).'&updatepslideshow_block&token='.$this->token);
				else
					$this->_errors[] = Tools::displayError('An error occurred while updating status.');
			
			}elseif(isset($_GET['delete']) || isset($_GET['deletepslideshow_img'])){
				
				$languages = Language::getLanguages();
				foreach ($languages as $lang){
					if(file_exists(_PS_ROOT_DIR_."/".$this->moduleConf['img_save_path'].$lang['id_lang']."/".$id_pslideshow_img.".jpg")){
						@unlink(_PS_ROOT_DIR_."/".$this->moduleConf['img_save_path'].$lang['id_lang']."/".$id_pslideshow_img.".jpg");
					}
				}
				
				if($psImg->delete())
					Tools::redirectAdmin($currentIndex.'&conf=1&'.$this->identifier.'='.Tools::getValue($this->identifier).'&updatepslideshow_block&token='.$this->token);
				else
					$this->_errors[] = Tools::displayError('An error occurred during deletion.');
			}
			
			parent::postProcess();
		
		}elseif(Tools::getValue('id_pslideshow_img') && isset($_GET['status'])){
			
				$id_pslideshow_img = (int) Tools::getValue('id_pslideshow_img');
				$psImg = new PslideshowImg($id_pslideshow_img);
				if($psImg->toggleStatus())
					Tools::redirectAdmin($currentIndex.'&conf=5&token='.$this->token);
				else
					$this->_errors[] = Tools::displayError('An error occurred while updating status.');
			
		}else{
			parent::postProcess();
		}
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
		$currentKey = '<{pslideshows}'._THEME_NAME_.'>adminpslideshowblock_'.md5($string2);
		$defaultKey = '<{pslideshows}prestashop>adminpslideshowblock_'.md5($string2);
		
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
