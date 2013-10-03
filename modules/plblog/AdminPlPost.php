<?php
//require_once (dirname(__FILE__).'/../../classes/AdminTab.php');
require_once (dirname(__FILE__).'/PostObject.php');
require_once (dirname(__FILE__).'/CategoryObject.php');

class AdminPlPost extends AdminTab
{
	function __construct()
	{
		$this->className = 'PostObject';
		$this->table = 'pl_blog_post';
		
		$this->lang = true;
		$this->edit = true;
		$this->delete = true;
		
		 $this->_select = 'd.category_name';
		 $this->_join = ' LEFT JOIN '._DB_PREFIX_.'pl_blog_category_lang d ON (d.id_pl_blog_category = a.id_pl_blog_category)';

		$this->fieldsDisplay = array(
								'id_pl_blog_post' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
								'post_title' => array('title' => $this->l('Title'), 'width' => 100),
								'post_description' => array('title' => $this->l('Content'), 'maxlength' => 190, 'width' =>200),
								'category_name' => array('title' => $this->l('Category'), 'width' =>100),	
								'post_status' => array('title' => $this->l('Status'), 'align' => 'center', 'active' => 'status', 'type' => 'bool'),
								);
		parent::__construct();
	}

	function displayForm($isMainTab = true)
	{
		$this->loadJS_CSS();
		//echo 
		$home = __PS_BASE_URI__.substr($_SERVER['PHP_SELF'], strlen(__PS_BASE_URI__));
		$currentIndex = self::$currentIndex;
		parent::displayForm();
		
		$this->displayErrors();
		
		$obj = $this->loadObject(true);
		
		$row = $obj->getPostById();
		
		
		echo '
		<form name="plfrm_a" action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend>'.$this->l('Post').'</legend>';
		
		$obj_category = new CategoryObject(true);
		
		echo '<label>'.$this->l('Category').' </label>
				<div class="margin-form">
					<select name="id_pl_blog_category">
					<option value="1">Home&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>';
					
					$categories = $obj_category->getCategories();
					$obj_category->recurseCategory($categories, $categories[0][1], 1, $this->getFieldValue($obj, 'id_pl_blog_category'));
		echo '
					</select>
				<a href="'.$currentIndex.'&addpl_blog_category&token='.$this->token.'" title="new category">
					<img border="0" src="../img/admin/add.gif">
					New category
				</a>
				</div>';
		// echo ($description = '&nbsp;&nbsp;<a href="'.$currentIndex.'&addpl_blog_category&token='.$this->token.'">New Category</a>');
			
			// post_status
			$this->displayStatus('Status', 'post_status', $row['post_status']);
			
			// post_title
			$this->displayRowMultiLang($this->l('Title'), 'post_title', 'text', $obj,	null, null, null, true,  '<>;=#{}', $url_rewrite = true);
			
			// post_description
			$this->displayRowMultiLang($this->l('Content'), 'post_description', 'textarea', $obj,	null, 90, 10, true);
			
			// url friendly
			$this->displayRowMultiLang($this->l('Friendly URL'), 'link_rewrite', 'text', $obj, null, null, null, false, '<>;=#{}', $link_rewrite = false, $str2url = true);
	
			// post meta title
			//$this->displayRow($this->l('Meta title'), 'post_meta_title', 'text', htmlentities($row['post_meta_title']),	null, null, null, false,  '<>;=#{}');
			$this->displayRowMultiLang($title = $this->l('Meta title'), $name = 'post_meta_title', $type = 'text', $obj, $note = null, $cols = null, $rows = null, $sub = false, $str_help_box = '<>;=#{}');
			
			// post meta description
			//$this->displayRow($this->l('Meta description'), 'post_meta_description', 'text', htmlentities($row['post_meta_description']),	null, null, null, false,  '<>;=#{}');
			$this->displayRowMultiLang($title = $this->l('Meta description'), $name = 'post_meta_description', $type = 'text', $obj, $note = null, $cols = null, $rows = null, $sub = false, $str_help_box = '<>;=#{}');
			
			// post meta keywords
			//$this->displayRow($this->l('Meta keywords'), 'post_meta_keywords', 'text', htmlentities($row['post_meta_keywords']),	null, null, null, false,  '<>;=#{}');
			$this->displayRowMultiLang($title = $this->l('Meta keywords'), $name = 'post_meta_keywords', $type = 'text', $obj, $note = null, $cols = null, $rows = null, $sub = false, $str_help_box = '<>;=#{}');
			
			// tags name
			$this->displayRowMultiLangTag($title = $this->l('Tags'), $name = 'id_pl_blog_tags', $type = 'text', $obj, $note = "Tags separated by commas (e.g., dvd, dvd player, hifi)", $cols = null, $rows = null, $sub = false, $str_help_box = '<>;=#{}');
					
			// post_allow_comment
			$this->displayStatus($this->l('Allow comments'), 'post_allow_comment', $row['post_allow_comment']);
			
			// category_date_create
			$this->displayDate('post_date_create', $row['post_date_create']);
			
			// buton submit
		/*echo'
				<label>'.$this->l('Image:').' </label>
				<div class="margin-form">';
		echo		$this->displayImage($obj->id, dirname(__FILE__).'/images/'.$obj->id.'.jpg', 350, NULL, NULL, true);
		echo '	<br /><input type="file" name="logo" />
					<p>'.$this->l('Upload image from your computer').'</p>
				</div>';*/
		echo '	<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small">'.$this->l('').'</div>
				';			
			echo'</fieldset>                            
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
		</form>';
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

	function displayStatus($title, $name = null, $value = null, $default = false)
	{
		echo '
			<label>'.$this->l($title).' </label>
			<div class="margin-form">';
		if (Tools::isSubmit('addpl_blog_post'))
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

	public function displayRowMultiLangTag($title = null, $name = null, $type = null, $obj = null, $note = null, $cols = null, $rows = null, $sub = false, $str_help_box = null, $url_rewrite = false, $str2url = false)
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
					echo '<input '.($str2url == true ? ' onchange="this.value=str2url(this.value)"' : '').' '.($url_rewrite == true ? 'onkeyup="copy2friendlyURL();"' : '').' type="'.$type.'" name="'.$name.'_'.$language['id_lang'].'" id="'.$name.'_'.$language['id_lang'].'" '.(is_string($obj) ? ' value="'.$obj.'" ' : ' value="'.htmlentities($obj->getTags((int)($language['id_lang']))).'" ').'/>';			
				else
					echo '<textarea class="rte autoload_rte " '.$d_cols.' '.$d_rows.' name="'.$name.'_'.$language['id_lang'].'">'.htmlentities($obj->getTags((int)($language['id_lang']))).'</textarea>';
				echo $sub_s.($str_help_box != null ? '<span class="hint" name="help_box">'.$this->l('Invalid characters:').' '.$str_help_box.'<span class="hint-pointer">&nbsp;</span></span>' : '').'</div>';
		}
		echo '<p class="clear">'.$note.'</p>';
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
					echo '<input '.($str2url == true ? ' onchange="this.value=str2url(this.value)"' : '').' '.($url_rewrite == true ? 'onkeyup="copy2friendlyURL();"' : '').' type="'.$type.'" name="'.$name.'_'.$language['id_lang'].'" id="'.$name.'_'.$language['id_lang'].'" '.(is_string($obj) ? ' value="'.$obj.'" ' : ' value="'.htmlentities($this->getFieldValue($obj, $name, (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" ').'/>';			
				else
					echo '<textarea class="rte autoload_rte" '.$d_cols.' '.$d_rows.' name="'.$name.'_'.$language['id_lang'].'">'.htmlentities($this->getFieldValue($obj, $name, (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>';
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
	
	public function getList($id_lang, $orderBy = NULL, $orderWay = NULL, $start = 0, $limit = NULL, $id_lang_shop = false)
	{
		//parent::getList($id_lang, $this->_orderBy, $this->_orderWay, $start, $limit);
		
		// init
		$orderBy = $this->_orderBy;
		$orderWay = $this->_orderWay;
		// where id_lang
		$this->_where = 'd.`id_lang`='.$id_lang;
		
		global $cookie;

		/* Manage default params values */
		if (empty($limit))
			$limit = ((!isset($cookie->{$this->table.'_pagination'})) ? $this->_pagination[1] : $limit = $cookie->{$this->table.'_pagination'});

		if (!Validate::isTableOrIdentifier($this->table))
			die (Tools::displayError('Table name is invalid:').' "'.$this->table.'"');

		if (empty($orderBy))
			$orderBy = $cookie->__get($this->table.'Orderby') ? $cookie->__get($this->table.'Orderby') : $this->_defaultOrderBy;
		if (empty($orderWay))
			$orderWay = $cookie->__get($this->table.'Orderway') ? $cookie->__get($this->table.'Orderway') : 'ASC';

		$limit = (int)(Tools::getValue('pagination', $limit));
		$cookie->{$this->table.'_pagination'} = $limit;

		/* Check params validity */
		if (!Validate::isOrderBy($orderBy) OR !Validate::isOrderWay($orderWay)
			OR !is_numeric($start) OR !is_numeric($limit)
			OR !Validate::isUnsignedId($id_lang))
			die(Tools::displayError('get list params is not valid'));

		/* Determine offset from current page */
		if ((isset($_POST['submitFilter'.$this->table]) OR
		isset($_POST['submitFilter'.$this->table.'_x']) OR
		isset($_POST['submitFilter'.$this->table.'_y'])) AND
		!empty($_POST['submitFilter'.$this->table]) AND
		is_numeric($_POST['submitFilter'.$this->table]))
			$start = (int)($_POST['submitFilter'.$this->table] - 1) * $limit;

		/* Cache */
		$this->_lang = (int)($id_lang);
		$this->_orderBy = $orderBy;
		$this->_orderWay = Tools::strtoupper($orderWay);

		/* SQL table : orders, but class name is Order */
		$sqlTable = $this->table == 'order' ? 'orders' : $this->table;
		
		/* Query in order to get results with all fields */
		$sql = 'SELECT SQL_CALC_FOUND_ROWS
			'.($this->_tmpTableFilter ? ' * FROM (SELECT ' : '').'
			'.($this->lang ? 'b.*, ' : '').'a.*'.(isset($this->_select) ? ', '.$this->_select.' ' : '').'
			FROM `'._DB_PREFIX_.$sqlTable.'` a
			'.($this->lang ? 'LEFT JOIN `'._DB_PREFIX_.$this->table.'_lang` b ON (b.`'.$this->identifier.'` = a.`'.$this->identifier.'` AND b.`id_lang` = '.(int)($id_lang).')' : '').'
			'.(isset($this->_join) ? $this->_join.' ' : '').'
			'.(isset($this->_where) ? 'WHERE '.$this->_where.' ' : '').($this->deleted ? 'AND a.`deleted` = 0 ' : '').(isset($this->_filter) ? $this->_filter : '').'
			'.(isset($this->_group) ? $this->_group.' ' : '').'
			'.((isset($this->_filterHaving) || isset($this->_having)) ? 'HAVING ' : '').(isset($this->_filterHaving) ? ltrim($this->_filterHaving, ' AND ') : '').(isset($this->_having) ? $this->_having.' ' : '').'
			ORDER BY '.(($orderBy == $this->identifier) ? 'a.' : '').'`'.pSQL($orderBy).'` '.pSQL($orderWay).
			($this->_tmpTableFilter ? ') tmpTable WHERE 1'.$this->_tmpTableFilter : '').'
			LIMIT '.(int)($start).','.(int)($limit);
		$this->_list = Db::getInstance()->ExecuteS($sql);
		$this->_listTotal = Db::getInstance()->getValue('SELECT FOUND_ROWS() AS `'._DB_PREFIX_.$this->table.'`');
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
				$('#link_rewrite_' + id_language).val(str2url($('#post_title_' + id_language).val().replace(/^[0-9]+\./, ''), 'UTF-8'));
			}
		</script>
		<?php
	}
	
	function display()
	{
		echo '<fieldset style="background-color:white !important; margin-bottom:25px !important;"><legend>'.$this->l('Manage Post').'</legend>';
		parent::display();
		echo '</fieldset>';
	}
	
	public function displayList()
	{
		$currentIndex = self::$currentIndex;

		$this->displayTop();

		if ($this->edit AND (!isset($this->noAdd) OR !$this->noAdd))
			echo '<br /><a href="'.$currentIndex.'&add'.$this->table.'&token='.$this->token.'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add new post').'</a><br /><br />';
		
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
	
	public function displayListContent($token = NULL)
	{
		/* Display results in a table
		 *
		 * align  : determine value alignment
		 * prefix : displayed before value
		 * suffix : displayed after value
		 * image  : object image
		 * icon   : icon determined by values
		 * active : allow to toggle status
		 */

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
		if ($this->_list)
		{
			$isCms = false;
			if (preg_match('/cms/Ui', $this->identifier))
				$isCms = true;
			$keyToGet = 'id_'.($isCms ? 'cms_' : '').'category'.(in_array($this->identifier, array('id_category', 'id_cms_category')) ? '_parent' : '');
			foreach ($this->_list AS $tr)
			{
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
							echo '<a'.(!($tr[$key] != $positions[sizeof($positions) - 1]) ? ' style="display: none;"' : '').' href="'.$currentIndex.
									'&'.$keyToGet.'='.(int)($id_category).'&'.$this->identifiersDnd[$this->identifier].'='.$id.'
									&way=1&position='.(int)($tr['position'] + 1).'&token='.($token!=NULL ? $token : $this->token).'">
									<img src="../img/admin/'.($this->_orderWay == 'ASC' ? 'down' : 'up').'.gif"
									alt="'.$this->l('Down').'" title="'.$this->l('Down').'" /></a>';

							echo '<a'.(!($tr[$key] != $positions[0]) ? ' style="display: none;"' : '').' href="'.$currentIndex.
									'&'.$keyToGet.'='.(int)($id_category).'&'.$this->identifiersDnd[$this->identifier].'='.$id.'
									&way=0&position='.(int)($tr['position'] - 1).'&token='.($token!=NULL ? $token : $this->token).'">
									<img src="../img/admin/'.($this->_orderWay == 'ASC' ? 'up' : 'down').'.gif"
									alt="'.$this->l('Up').'" title="'.$this->l('Up').'" /></a>';						}
						else
							echo (int)($tr[$key] + 1);
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
						echo Tools::displayDate($tr[$key], (int)$cookie->id_lang);
					elseif (isset($params['type']) AND $params['type'] == 'datetime')
						echo Tools::displayDate($tr[$key], (int)$cookie->id_lang, true);
					elseif (isset($tr[$key]))
					{
						if ($key == 'post_description')
							echo substr(strip_tags($tr[$key]), 0, 120);
						else
						{
							$echo = ($key == 'price' ? round($tr[$key], 2) : isset($params['maxlength']) ? Tools::substr($tr[$key], 0, $params['maxlength']).'...' : $tr[$key]);
							echo isset($params['callback']) ? call_user_func_array(array($this->className, $params['callback']), array($echo, $tr)) : $echo;
						}
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
		if (Tools::getValue('deleteImage') == 1)
		{
			$this->deleteImage(Tools::getValue('id_pl_blog_post'));
		}
		if (
		       (
			       (Tools::getValue('id_pl_blog_category') == null) || 
				   ((int) Tools::getValue('id_pl_blog_category') < 1)
				)
				&& Tools::isSubmit('submitAdd'.$this->table))
		{
			$currentIndex = self::$currentIndex;
			$urlRedirect = $currentIndex.'&addpl_blog_post&token='.$this->token;
			
			echo '
				<script language="javascript">
					var rs = alert("Category is null. Please make new Category!");
					window.location.href = "'.$urlRedirect.'";
				</script>
			';
		}

		parent::postProcess();
	}
	
	public function displayListFooter($token = NULL)
	{
		echo '</table>';
		if ($this->delete)
			echo '<p><input type="submit" class="button" name="submitDel'.$this->table.'" value="'.$this->l('Delete selection').'" onclick="return confirm(\''.$this->l('If you delete this post then all comment of this post will be deleted. Delete selected items?', __CLASS__, TRUE, FALSE).'\');" /></p>';
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
		$_cacheLang['DeleteItem'] = $this->l('If you delete this post then all comment of this post will be deleted. Delete item #', __CLASS__, TRUE, FALSE);

		echo '
			<a href="'.$currentIndex.'&'.$this->identifier.'='.$id.'&delete'.$this->table.'&token='.($token!=NULL ? $token : $this->token).'" onclick="return confirm(\''.$_cacheLang['DeleteItem'].$id.' ?'.
    				(!is_null($this->specificConfirmDelete) ? '\r'.$this->specificConfirmDelete : '').'\');">
			<img src="../img/admin/delete.gif" alt="'.$_cacheLang['Delete'].'" title="'.$_cacheLang['Delete'].'" /></a>';
	}

	public function deleteImage($id_image)
	{
		$image = dirname(__FILE__) . '/images/'.$id_image.'.jpg';
		if (file_exists($image))
			unlink($image);
		return true;
	}

	public function afterImageUpload()
	{
		/* Generate image with differents size */
		if (($id_pl_blog_post = (int)(Tools::getValue('id_pl_blog_post'))) AND isset($_FILES) AND count($_FILES) AND file_exists(dirname(__FILE__) . '/images/'.$id_image.'.jpg'))
		{
			$imagesTypes = ImageType::getImagesTypes('suppliers');
			foreach ($imagesTypes AS $k => $imageType)
			{
				$file = dirname(__FILE__) . '/images/'.$id_image.'.jpg';
				imageResize($file, _PS_SUPP_IMG_DIR_.$id_pl_blog_post.'-'.stripslashes($imageType['name']).'.jpg', (int)($imageType['width']), (int)($imageType['height']));
			}
		}
	}
}
