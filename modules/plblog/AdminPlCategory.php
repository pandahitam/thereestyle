<?php
//require_once (dirname(__FILE__).'/../../classes/AdminTab.php');
require_once (dirname(__FILE__).'/CategoryObject.php');

class AdminPlCategory extends AdminTab
{
	function __construct()
	{
		global $cookie;
		
		$this->className = 'CategoryObject';
		$this->table = 'pl_blog_category';
		
		$this->lang = true;
		$this->edit = true;
		$this->delete = true;

		
		$this->_filter = 'AND category_parent IS NOT NULL';
		//$this->_filter = 'AND d.id_lang='.(($cookie->id_lang != null) ? $cookie->id_lang : Configuration::get('PS_LANG_DEFAULT'));
//		$this->_join = ' LEFT JOIN '._DB_PREFIX_.'pl_blog_category_lang d ON (d.id_pl_blog_category = a.category_parent) AND d.`id_lang` = '.(int)$cookie->id_lang.')';
		
		$this->fieldsDisplay = array(
								'id_pl_blog_category' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
								'category_name' => array('title' => $this->l('Name'), 'width' => 120),
								'category_description' => array('title' => $this->l('Description'), 'maxlength' => 190, 'width' =>200),
								'category_name' => array('title' => $this->l('Parent'), 'width' =>100),
								//'category_url' => array('title' => $this->l('URL'), 'width' =>80),
								'category_date_create' => array('title' => $this->l('Date create'), 'width' =>100),
								'category_status' => array('title' => $this->l('Status'), 'align' => 'center', 'active' => 'status', 'type' => 'bool'),
								);
		
		//$this->fieldImageSettings = array('name' => 'link_img', 'dir' => 'anh_upload_img');
		
		parent::__construct();
	}
	
	function displayForm($isMainTab = true)
	{
		$this->loadJS_CSS();
		//echo 
		$home = __PS_BASE_URI__.substr($_SERVER['PHP_SELF'], strlen(__PS_BASE_URI__));
		global $cookie;
                $currentIndex = self::$currentIndex;
		
		parent::displayForm();
		/* user post
		echo '<pre>';
		print_r($cookie->id_employee);
		*/
		
		if (!($obj = $this->loadObject(true)))
			return;
		
		//$this->_errors[] = Tools::displayError('An error occurred during deletion.');
		
		$this->displayErrors();
		
		$row = $obj->getCategoryById();
		
		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend>'.$this->l('Category').'</legend>';
			// parent
			//$this->displaySelection('Parent', 'category_parent', $row['category_parent'], $obj->getCategorys(), 'id_pl_blog_category', 'category_name', 200, null);
		echo '<label>'.$this->l('Parent:').' </label>
				<div class="margin-form">
					<select name="category_parent">
						<option value="1">Home</option>';
						$categories = $obj->getCategories();
						$obj->recurseCategory($categories, $categories[0][1], 1, $this->getFieldValue($obj, 'category_parent'));
		echo '		</select>
				</div>';
			
			
			
			// category_status
			$this->displayStatus('category_status', $row['category_status']);
			
			// category_title
			$this->displayRowMultiLang($this->l('Title'), 'category_name', 'text', $obj,	null, null, null, true, '<>;=#{}', $url_rewrite = true);
			
			// category_description
			$this->displayRowMultiLang($this->l('Description'), 'category_description', 'textarea', $obj,	null, 90, 10, false);
			
			// category allow comment
			$this->displayStatus('category_allow_comment', $row['category_allow_comment'], 'Allow comments');
			
			// url friendly
			$this->displayRowMultiLang($this->l('Friendly URL'), 'link_rewrite', 'text', $obj, null, null, null, false, '<>;=#{}', $link_rewrite = false, $str2url = true);
	
			// category_meta_title
			$this->displayRowMultiLang($this->l('Meta title'), 'category_meta_title', 'text', $obj, null, null, null, false, '<>;=#{}');
			
			// category_meta_description
			$this->displayRowMultiLang($title = $this->l('Meta description'), $name = 'category_meta_description', $type = 'text', $obj, $note = null, $cols = null, $rows = null, $sub = false, $str_help_box = '<>;=#{}');
			
			// category_meta_keywords
			$this->displayRowMultiLang($title = $this->l('Meta keywords'), $name = 'category_meta_keywords', $type = 'text', $obj, $note = null, $cols = null, $rows = null, $sub = false, $str_help_box = '<>;=#{}');
			
			// category_date_create
			$this->displayDate('category_date_create', $row['category_date_create']);
			
			// position
			echo '<input type="hidden" name="position" value="'.($row['position'] == null ? $obj->getPosition() : $row['position']).'" />';
			// buton submit
			echo'
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>		
			</fieldset>
                        <script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
        tinyMCE.init({
                mode : "specific_textareas",
		theme : "advanced",
		skin:"cirkuit",
		editor_selector : "rte",
		editor_deselector : "noEditor",
		plugins : "safari,pagebreak,style,table,advimage,advlink,inlinepopups,media,contextmenu,paste,fullscreen,xhtmlxtras,preview",
		// Theme options
		theme_advanced_buttons1 : "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,media,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "styleprops,|,cite,abbr,acronym,del,ins,attribs,pagebreak",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		content_css : pathCSS+"global.css",
		document_base_url : ad,
		width: "600",
		height: "auto",
		font_size_style_values : "8pt, 10pt, 12pt, 14pt, 18pt, 24pt, 36pt",
		elements : "nourlconvert,ajaxfilemanager",
		file_browser_callback : "ajaxfilemanager",
		entity_encoding: "raw",
		convert_urls : false,
		language : iso
				});
				id_language = Number('.$this->context->language->id.');
			</script>
		</form>
		<p class="clear"></p>';
	}
	
	function displayDate($name, $value)
	{
		$_date = '';
		if ( empty($value))
		{
			$_date .= '<input type="hidden" size="15" name="'.$name.'" value= "'.$this->getDate().'" />';
		}				
		else
		{
			$_date .= '<input type="hidden" size="15" name="'.$name.'"  value="'.$value.'" />';
		}
		
		echo $_date;
	}

	function displayStatus($name = null, $value = null, $title = null)
	{
		echo '
			<label>'.$this->l(($title == null ? 'Status' : $title)).' </label>
			<div class="margin-form">';
		if (Tools::isSubmit('addpl_blog_category'))
		{
			echo '<img src="../img/admin/enabled.gif" /><input type="radio" name="'.$name.'" value="1" checked="checked" /> &nbsp;&nbsp;';
			echo '<img src="../img/admin/disabled.gif" /><input type="radio" name="'.$name.'" value="0" /> ';
		}
		else
		{
			echo '<img src="../img/admin/enabled.gif" /><input type="radio" name="'.$name.'" value="1" '.($value == 1 ? 'checked="checked"' : '').' /> &nbsp;&nbsp;';
			echo '<img src="../img/admin/disabled.gif" /><input type="radio" name="'.$name.'" value="0"  '.($value == 0 ? 'checked="checked"' : '').' /> ';
		}
		echo '<sup>*</sup>
				<p class="clear"></p>
			</div>';
	}
	
	function displaySelection($title = null, $name = null, $selected = null, $rows = null, $value = null, $display_name = null, $width = null, $height = null)
	{
		echo '<label>'.$this->l($title).' </label>
				<div class="margin-form">';
				
		$_select = '<select name="'.$name.'" '.($width != null ? 'width="'.$width.'"' : '' ).' '.($height != null ? 'height="'.$height.'"': ' ').'>';
		for ($i = 0; $i < count($rows); $i++)
		{
			 $_row = $rows[$i];
			 $option = '<option '.($selected != null ? ($selected==$_row[$value] ? 'selected="selected"' : '') : '') . ' value="'.$_row[$value].'">'.$_row[$display_name].'</option>';
			 $_select .= $option;
		}
		
		$_select .= '</select>';
		
		echo $_select;
		
		echo '</div>';
	}
	
	public function displayRowMultiLang($title = null, $name = null, $type = null, $obj = null, $note = null, $cols = null, $rows = null, $sub = false, $str_help_box = null, $url_rewrite = false, $str2url = false)
	{
		$d_cols = null;
		$d_rows = null;
		$d_value = null;
		$sub_s = null;
		
		if (!empty($cols))
			$d_cols = 'cols='.$cols;
		if (!empty($rows))
			$d_rows = 'rows='.$rows;
		if (!empty($value))
			$d_value = 'value="'.$value.'"';
		if ($sub)
			$sub_s = '<sup>*</sup>';
			
		echo '<label>'.$this->l($title).' </label>
				<div class="margin-form translatable">';
		foreach ($this->_languages AS $language)
		{
			echo '
				<div class="lang_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">';
				if ($type != 'textarea' )
					echo '<input '.($str2url == true ? ' onchange="this.value=str2url(this.value)"' : '').' '.($url_rewrite == true ? 'onkeyup="copy2friendlyURL();"' : '').' value="'.htmlentities($this->getFieldValue($obj, $name, (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" type="'.$type.'" name="'.$name.'_'.$language['id_lang'].'" id="'.$name.'_'.$language['id_lang'].'" />';			
				else
					echo '<textarea class="rte" '.$d_cols.' '.$d_rows.' name="'.$name.'_'.$language['id_lang'].'">'.htmlentities($this->getFieldValue($obj, $name, (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>';
				echo $sub_s.($str_help_box != null ? '<span class="hint" name="help_box">'.$this->l('Invalid characters:').' '.$str_help_box.'<span class="hint-pointer">&nbsp;</span></span>' : '').'</div>';
		}
		echo '<p class="clear">'.$note.'</p>';
		echo '</div>';		
	}
	
	function displayRow($title = null, $name = null, $type = null, $value = null, $note = null, $cols = null, $rows = null, $sub = false, $str_help_box = null)
	{
		$d_cols = null;
		$d_rows = null;
		$d_value = null;
		$sub_s = null;
		
		if (!empty($cols))
			$d_cols = 'cols='.$cols;
		if (!empty($rows))
			$d_rows = 'rows='.$rows;
		if (!empty($value))
			$d_value = 'value="'.$value.'"';
		if ($sub)
			$sub_s = '<sup>*</sup>';
			
		echo '<label>'.$this->l($title).' </label>
				<div class="margin-form">';
				if ($type != 'textarea' )
					echo '<input '.$d_value.' type="'.$type.'" name="'.$name.'" id="'.$name.'" />';			
				else
					echo '<textarea '.$d_cols.' '.$d_rows.' name="'.$name.'">'.$value.'</textarea>';
				echo $sub_s.($str_help_box != null ? '<span class="hint" name="help_box">'.$this->l('Invalid characters:').' '.$str_help_box.'<span class="hint-pointer">&nbsp;</span></span>' : '').'</div>';
		echo '<p class="clear">'.$note.'</p>';
	}

	function getDate()
	{
		$date = getdate();
		return $date['year'].'/'.$date['mon'].'/'.$date['mday'].' '.$date['hours'].':'.$date['minutes'].':'.$date['seconds'];
	}
	
	function loadJS_CSS()
	{
		global $cookie;
		$iso = Language::getIsoById((int)($cookie->id_lang));
		$isoTinyMCE = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');
		$ad = dirname($_SERVER["PHP_SELF"]);
		echo '
			<script type="text/javascript">
			var iso = \''.$isoTinyMCE.'\' ;
			var pathCSS = \''._THEME_CSS_DIR_.'\' ;
			var ad = \''.$ad.'\' ;
			</script>
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tiny_mce/tiny_mce.js"></script>
			<script type="text/javascript" src="'.__PS_BASE_URI__.'js/tinymce.inc.js"></script>';
		?>
		<script type="text/javascript">
			function copy2friendlyURL()
			{
				$('#link_rewrite_' + id_language).val(str2url($('#category_name_' + id_language).val().replace(/^[0-9]+\./, ''), 'UTF-8'));
			}
		</script>
		<?php
	}
	
	function display()
	{
		echo '<fieldset style="background-color:white !important; margin-bottom:25px !important;"><legend>'.$this->l('Manage Category').'</legend>';
		parent::display();
		echo '</fieldset>';
	}
	
	public function displayList()
	{
		$currentIndex = self::$currentIndex;

		$this->displayTop();

		if ($this->edit AND (!isset($this->noAdd) OR !$this->noAdd))
			echo '<br /><a href="'.$currentIndex.'&add'.$this->table.'&token='.$this->token.'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add new category').'</a><br /><br />';
		
		if ($this->_list === false)
		{
			$this->displayWarning($this->l('Bad SQL query'));
			return false;
		}

		$this->displayListHeader();
		if (!sizeof($this->_list))
			echo '<tr><td class="center" colspan="'.(sizeof($this->fieldsDisplay) + 2).'">'.$this->l('No items found').'</td></tr>';

		$this->displayListContent();

		$this->displayListFooter();
	}
	
	
	public function getList($id_lang, $orderBy = NULL, $orderWay = NULL, $start = 0, $limit = NULL, $id_lang_shop = false)
	{
		global $cookie;
		parent::getList((int)($cookie->id_lang), !$cookie->__get($this->table.'Orderby') ? 'position' : NULL, !$cookie->__get($this->table.'Orderway') ? 'ASC' : NULL);	
	}
	
	public function displayListContent($token = NULL)
	{
		global $cookie;
                $currentIndex = self::$currentIndex;
		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

		$id_category = 1; // default categ

		$irow = 0;
		if ($this->_list AND isset($this->fieldsDisplay['position']))
		{
			$positions = array_map(create_function('$elem', 'return (int)($elem[\'position\']);'), $this->_list);
			sort($positions);
		}
		// echo '<pre>';
		// print_r($positions);
		if ($this->_list)
		{
			$isCms = false;
			if (preg_match('/cms/Ui', $this->identifier))
				$isCms = true;
			//$keyToGet = 'id_'.($isCms ? 'cms_' : '').'category'.(in_array($this->identifier, array('id_category', 'id_cms_category')) ? '_parent' : '');
			// foreach ($this->_list AS $i => $tr)
			// {
			for ($i = 0; $i < count($this->_list); $i++)
			{
				$tr = $this->_list[$i];
				
				$id = $tr[$this->identifier];
				echo '<tr'.(array_key_exists($this->identifier,$this->identifiersDnd) ? ' id="tr_'.(($id_category = (int)(Tools::getValue('id_'.($isCms ? 'cms_' : '').'category', '1'))) ? $id_category : '').'_'.$id.'_'.$tr['position'].'"' : '').($irow++ % 2 ? ' class="alt_row"' : '').' '.((isset($tr['color']) AND $this->colorOnBackground) ? 'style="background-color: '.$tr['color'].'"' : '').'>
							<td class="center">';
				if ($this->delete AND (!isset($this->_listSkipDelete) OR !in_array($id, $this->_listSkipDelete)))
					echo '<input type="checkbox" name="'.$this->table.'Box[]" value="'.$id.'" class="noborder" />';
				echo '</td>';
				foreach ($this->fieldsDisplay AS $key => $params)
				{
					$tmp = explode('!', $key);
					$key = isset($tmp[1]) ? $tmp[1] : $tmp[0];
					echo '
					<td '.(isset($params['position']) ? ' id="td_'.(isset($id_category) AND $id_category ? $id_category : 0).'_'.$id.'"' : '').' class="'.((!isset($this->noLink) OR !$this->noLink) ? 'pointer' : '').((isset($params['position']) AND $this->_orderBy == 'position')? ' dragHandle' : ''). (isset($params['align']) ? ' '.$params['align'] : '').'" ';
					if (!isset($params['position']) AND (!isset($this->noLink) OR !$this->noLink))
						echo ' onclick="document.location = \''.$currentIndex.'&'.$this->identifier.'='.$id.($this->view? '&view' : '&update').$this->table.'&token='.($token!=NULL ? $token : $this->token).'\'">'.(isset($params['prefix']) ? $params['prefix'] : '');
					else
						echo '>';
					if (isset($params['active']) AND isset($tr[$key]))
					    $this->_displayEnableLink($token, $id, $tr[$key], $params['active'], Tools::getValue('id_category'), Tools::getValue('id_product'));
					elseif (isset($params['activeVisu']) AND isset($tr[$key]))
						echo '<img src="../img/admin/'.($tr[$key] ? 'enabled.gif' : 'disabled.gif').'"
						alt="'.($tr[$key] ? $this->l('Enabled') : $this->l('Disabled')).'" title="'.($tr[$key] ? $this->l('Enabled') : $this->l('Disabled')).'" />';
					elseif (isset($params['position']))
					{
						if ($this->_orderBy == 'position' AND $this->_orderWay != 'DESC')
						{
							echo '<a '.(!($tr[$key] != $positions[sizeof($positions) - 1]) ? ' style="display: none;"' : '').' 
									href="'.$currentIndex.
									'&id_pl_blog_category='.$tr['id_pl_blog_category'].'&plposition='.(($tr[$key] != $positions[sizeof($positions) - 1]) ? $this->_list[$i+1]['id_pl_blog_category'] : '').'&token='.($token!=NULL ? $token : $this->token).'">
									<img src="../img/admin/'.($this->_orderWay == 'ASC' ? 'down' : 'up').'.gif"
									alt="'.$this->l('Down').'" title="'.$this->l('Down').'" /></a>';

							echo '<a '.(!($tr[$key] != $positions[0]) ? ' style="display: none;"' : '').' 
									href="'.$currentIndex.
									'&id_pl_blog_category='.$tr['id_pl_blog_category'].'&plposition='.(($tr[$key] != $positions[0]) ? $this->_list[$i-1]['id_pl_blog_category'] : '').'&token='.($token!=NULL ? $token : $this->token).'">
									<img src="../img/admin/'.($this->_orderWay == 'ASC' ? 'up' : 'down').'.gif"
									alt="'.$this->l('Up').'" title="'.$this->l('Up').'" /></a>';					
						}
						else
							echo (int)($tr[$key]);
					}
					elseif (isset($params['image']))
					{
						// item_id is the product id in a product image context, else it is the image id.
						$item_id = isset($params['image_id']) ? $tr[$params['image_id']] : $id;
						// If it's a product image
						if (isset($tr['id_image'])) 
						{
							$image = new Image((int)$tr['id_image']);
							$path_to_image = _PS_IMG_DIR_.$params['image'].'/'.$image->getExistingImgPath().'.'.$this->imageType;
						}else
							$path_to_image = _PS_IMG_DIR_.$params['image'].'/'.$item_id.(isset($tr['id_image']) ? '-'.(int)($tr['id_image']) : '').'.'.$this->imageType;
							
						echo cacheImage($path_to_image, $this->table.'_mini_'.$item_id.'.'.$this->imageType, 45, $this->imageType);
					}
					elseif (isset($params['icon']) AND (isset($params['icon'][$tr[$key]]) OR isset($params['icon']['default'])))
						echo '<img src="../img/admin/'.(isset($params['icon'][$tr[$key]]) ? $params['icon'][$tr[$key]] : $params['icon']['default'].'" alt="'.$tr[$key]).'" title="'.$tr[$key].'" />';
                    elseif (isset($params['price']))
						echo Tools::displayPrice($tr[$key], (isset($params['currency']) ? Currency::getCurrencyInstance((int)($tr['id_currency'])) : $currency), false);
					elseif (isset($params['float']))
						echo rtrim(rtrim($tr[$key], '0'), '.');
					elseif (isset($params['type']) AND $params['type'] == 'date')
						echo Tools::displayDate($tr[$key], $cookie->id_lang);
					elseif (isset($params['type']) AND $params['type'] == 'datetime')
						echo Tools::displayDate($tr[$key], $cookie->id_lang, true);
					elseif (isset($tr[$key]))
					{
						$echo = ($key == 'price' ? round($tr[$key], 2) : isset($params['maxlength']) ? Tools::substr($tr[$key], 0, $params['maxlength']).'...' : $tr[$key]);
						echo isset($params['callback']) ? call_user_func_array(array($this->className, $params['callback']), array($echo, $tr)) : $echo;
					}
					else
						echo '--';

					echo (isset($params['suffix']) ? $params['suffix'] : '').
					'</td>';
				}

				if ($this->edit OR $this->delete OR ($this->view AND $this->view !== 'noActionColumn'))
				{
					echo '<td class="center" style="white-space: nowrap;">';
					if ($this->view)
                        $this->_displayViewLink($token, $id);
					if ($this->edit)
					    $this->_displayEditLink($token, $id);
					if ($this->delete AND (!isset($this->_listSkipDelete) OR !in_array($id, $this->_listSkipDelete)))
					    $this->_displayDeleteLink($token, $id);
					if ($this->duplicate)
                        $this->_displayDuplicate($token, $id);
					echo '</td>';
				}
				echo '</tr>';
			}
		}
	}
	
	function postProcess()
	{
		if (isset($_GET['plposition']))
		{		
			$obj = $this->loadObject(true);
			$obj->updatePosition();	
		}
		
		parent::postProcess();
	}
	
	public function displayListFooter($token = NULL)
	{
		echo '</table>';
		if ($this->delete)
			echo '<p><input type="submit" class="button" name="submitDel'.$this->table.'" value="'.$this->l('Delete selection').'" onclick="return confirm(\''.$this->l('If you delete this category then all articles of this category will be deleted. Delete selected items?', __CLASS__, TRUE, FALSE).'\');" /></p>';
		echo '
				</td>
			</tr>
		</table>
		<input type="hidden" name="token" value="'.($token ? $token : $this->token).'" />
		</form>';
		if (isset($this->_includeTab) AND sizeof($this->_includeTab))
			echo '<br /><br />';
	}
	
	protected function _displayDeleteLink($token = NULL, $id)
	{
	    $currentIndex = self::$currentIndex;

		$_cacheLang['Delete'] = $this->l('Delete');
		$_cacheLang['DeleteItem'] = $this->l('If you delete this category then all articles of this category will be deleted. Delete item #', __CLASS__, TRUE, FALSE);

		echo '
			<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&delete'.$this->table.'&token='.($token!=NULL ? $token : $this->token).'" onclick="return confirm(\''.$_cacheLang['DeleteItem'].$id.' ?'.
    				(!is_null($this->specificConfirmDelete) ? '\r'.$this->specificConfirmDelete : '').'\');">
			<img src="../img/admin/delete.gif" alt="'.$_cacheLang['Delete'].'" title="'.$_cacheLang['Delete'].'" /></a>';
	}
}