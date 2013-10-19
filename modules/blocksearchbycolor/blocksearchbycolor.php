<?php
/*2012 Web 4 Infinity
www.web4infinity.com
*/

if (!defined('_PS_VERSION_'))
	exit;

class blocksearchbycolor extends Module
{
	function __construct()
	{
		$this->name = 'blocksearchbycolor';
		$this->tab = 'Search by color';
		$this->version = 1.0;
		$this->author = 'Web 4 Infinity';
		$this->module_key = '1d2e3b8c4162bd3a61d6fa7a7538b883';
		$this->need_instance = 0;

		parent::__construct();

		$this->displayName = $this->l('Search by color');
		$this->description = $this->l('Search the store by clicking a color box.');
	}

	public function install()
	{
		if (!parent::install() OR !$this->registerHook('top')
				OR !$this->registerHook('leftColumn')
				OR !$this->registerHook('rightColumn')
				OR !$this->registerHook('header')
			)
			return false;
		return true;
	}

		function hookRightColumn($params)
	{
		global $smarty;
		$attcolorsgroup = 'SELECT * FROM '._DB_PREFIX_.'attribute_group WHERE is_color_group=1';
		$resultsgroup = Db::getInstance()->ExecuteS($attcolorsgroup);
		$groupatt = array();
		foreach ($resultsgroup as $group){
		array_push($groupatt, $group['id_attribute_group']);
		}

		$attcolors = 'SELECT * FROM '._DB_PREFIX_.'attribute WHERE id_attribute_group in ('.implode(',',$groupatt).')';
$colors = array();
$names = array();
if ($results = Db::getInstance()->ExecuteS($attcolors))

	foreach ($results as $row){
		$attnames = 'SELECT * FROM '._DB_PREFIX_.'attribute_lang WHERE id_attribute= '.$row['id_attribute'];
		if ($attname = Db::getInstance()->getRow($attnames))
		//echo $row['color'].' :: '.$attname['name'].'<br />';
		array_push($colors, $row['color']);
		array_push($names, $attname['name']);
		$smarty->assign('attcolor', $colors);
		$smarty->assign('attname', $names);
	}

		return $this->display(__FILE__, 'blocksearchbycolor.tpl');
	}
	public function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}
	public function uninstall()
 	{
 	 	if (!parent::uninstall())
 	 		return false;
 	}

	public function hookHeader($params)
	{
		$this->context->controller->addCSS(($this->_path).'blocksearchbycolor.css', 'all');
	}
}


