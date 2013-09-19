<?php
require_once (dirname(__FILE__).'/TagsObject.php');
class AdminPlTags extends AdminTab
{
	function __construct()
	{
		$this->className = 'TagsObject';
		$this->table = 'pl_blog_tags';
		$this->lang = true;
		$this->edit = false;
		$this->delete = true;
				global $cookie;
				$this->_filter = 'AND tags_date_create IS NOT NULL';		
		$this->fieldsDisplay = array(
								'id_pl_blog_tags' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
								'tags_name' => array('title' => $this->l('Name'), 'width' => 200),
								'tags_date_create' => array('title' => $this->l('Date create'), 'width' =>100)					
								);
		parent::__construct();
	}
	function displayForm($isMainTab = true)
	{
		$this->loadJS_CSS();
		$home = __PS_BASE_URI__.substr($_SERVER['PHP_SELF'], strlen(__PS_BASE_URI__));
		global $currentIndex;
		parent::displayForm();
		$obj = $this->loadObject(true);
		$row = $obj->getTagsById();
		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend>'.$this->l('Category').'</legend>';
			
			// tags_name
			$this->displayRowMultiLang($this->l('Tag Name'), 'tags_name', 'text', $obj, null, null, null, false,  '<>;=#{}');
			
			// tags_description
			$this->displayRowMultiLang($this->l('Description'), 'tags_description', 'textarea', $obj,	null, 90, 10, false);
			
			// tags_date_create
			$this->displayDate('tags_date_create', $row['tags_date_create']);
			
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
	{		echo '<fieldset style="background-color:white !important; margin-bottom:25px !important;"><legend>'.$this->l('Manage Tags').'</legend>';
		parent::display();
		echo '</fieldset>';
	}
}