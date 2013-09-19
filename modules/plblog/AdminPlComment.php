<?php
require_once (dirname(__FILE__).'/../../classes/AdminTab.php');
require_once (dirname(__FILE__).'/CommentObject.php');
class AdminPlComment extends AdminTab
{
	function __construct()
	{
		$this->className = 'CommentObject';
		$this->table = 'pl_blog_comment';
		$this->lang = true;
		$this->edit = true;
		$this->delete = true;
		$this->_select = 'd.post_title, g.name, substring(b.comment_content, 1, 120) as content';
		$this->_join = ' LEFT JOIN '._DB_PREFIX_.'pl_blog_post_lang d ON (d.id_pl_blog_post = a.id_pl_blog_post)
						 LEFT JOIN '._DB_PREFIX_.'pl_blog_comment_status g ON (g.id_pl_blog_comment_status = a.comment_status)
						';
		$this->fieldsDisplay = array(
								'id_pl_blog_comment' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
								'post_title' => array('title' => $this->l('Title'), 'width' => 220),							
								'comment_author_name' => array('title' => $this->l('Author\'s name'), 'width' =>100),
								'comment_author_email' => array('title' => $this->l('Author\'s email'), 'width' =>100),
								'name' => array('title' => $this->l('Status'), 'align' => 'center', 'width' => 25),
								);
		parent::__construct();
	}
	
	function displayForm($isMainTab = true)
	{
		$this->loadJS_CSS();
		$home = __PS_BASE_URI__.substr($_SERVER['PHP_SELF'], strlen(__PS_BASE_URI__));
		global $currentIndex;
		parent::displayForm();
		
		$this->displayErrors();
		$obj = $this->loadObject(true);
		$row = $obj->getCommentById();
		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend>'.$this->l('Comment').'</legend>';
			// parent
			$this->displaySelection($this->l('Post title'), 'id_pl_blog_post', $row['id_pl_blog_post'], $obj->getPostNames(), 'id_pl_blog_post', 'post_title', 200, null);
			// comment_status
			$this->displaySelection($this->l('Status'), 'comment_status', $row['comment_status'], $obj->getCommentStatus(), 'id_pl_blog_comment_status', 'name', 200, null);
			// comment_author_name
			$this->displayRow($this->l('Author\'s name'), 'comment_author_name', 'text', htmlentities($row['comment_author_name']),	null, null, null, true,  '<>;=#{}');
			// comment_author_email
			$this->displayRow($this->l("Author\'s email"), 'comment_author_email', 'text', htmlentities($row['comment_author_email']),	null, null, null, true,  '<>;=#{}');
			// comment_content
			$this->displayRowMultiLang($this->l('Content'), 'comment_content', 'textarea', $obj, null, 90, 10, true);
			// comment_date_create
			$this->displayDate('comment_date_create', $row['comment_date_create']);
			// buton submit
			echo'
				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small">'.$this->l('').'</div>
				';			
			echo'</fieldset>
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

	function displayStatus($name = null, $value = null)
	{
		echo '
			<label>'.$this->l('Status').' </label>
			<div class="margin-form">';
		echo '<img src="../img/admin/enabled.gif" /><input type="radio" name="'.$name.'" value="1" '.($value == 1 ? 'checked="checked"' : '').' /> &nbsp;&nbsp;';
		echo '<img src="../img/admin/disabled.gif" /><input type="radio" name="'.$name.'" value="0"  '.($value == 0 ? 'checked="checked"' : '').' /> ';
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
			//$selected = ($selected == null ? 1 : ($selected == 1) ? 2 : $selected);
			 $option = '<option '.($selected != null ? ($selected==$_row[$value] ? 'selected="selected"' : '') : '') . ' value="'.$_row[$value].'">'.$_row[$display_name].'</option>';
			 $_select .= $option;
		}
		
		$_select .= '</select>';
		
		echo $_select;
		
		echo '</div>';
	}
	
	public function displayRowMultiLang($title = null, $name = null, $type = null, $obj = null, $note = null, $cols = null, $rows = null, $sub = false, $str_help_box = null)
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
					echo '<input value="'.htmlentities($this->getFieldValue($obj, $name, (int)($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" type="'.$type.'" name="'.$name.'_'.$language['id_lang'].'" id="'.$name.'_'.$language['id_lang'].'" />';			
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
	
	public function getList($id_lang, $orderBy = NULL, $orderWay = NULL, $start = 0, $limit = NULL)
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
	}
	
	function display()
	{
		echo '<fieldset style="background-color:white !important; margin-bottom:25px !important;"><legend>'.$this->l('Manage Comment').'</legend>';
		parent::display();
		echo '</fieldset>';
	}
	
	public function displayList()
	{
		global $currentIndex;

		$this->displayTop();

		//if ($this->edit AND (!isset($this->noAdd) OR !$this->noAdd))
		//	echo '<br /><a href="'.$currentIndex.'&add'.$this->table.'&token='.$this->token.'"><img src="../img/admin/add.gif" border="0" /> '.$this->l('Add new').'</a><br /><br />';
		/* Append when we get a syntax error in SQL query */
		if ($this->_list === false)
		{
			$this->displayWarning($this->l('Bad SQL query'));
			return false;
		}

		/* Display list header (filtering, pagination and column names) */
		$this->displayListHeader();
		if (!sizeof($this->_list))
			echo '<tr><td class="center" colspan="'.(sizeof($this->fieldsDisplay) + 2).'">'.$this->l('No items found').'</td></tr>';

		/* Show the content of the table */
		$this->displayListContent();

		/* Close list table and submit button */
		$this->displayListFooter();
	}
}