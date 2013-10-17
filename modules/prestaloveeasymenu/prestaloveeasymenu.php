<?php
//include _PS_MODULE_DIR_.'prestaloveeasymenu/prestaloveeasymenu.class.php';
class prestaloveeasymenu extends Module
{
  private $_menu = '';
  private $_html = '';

  public function __construct()
  {
    $this->name = 'prestaloveeasymenu';
	$this->tab = 'front_office_features';
	$this->version = 1.2;
	$this->author = 'PrestaLove';
	$this->need_instance = 0;
		
    parent::__construct();
    $this->displayName = $this->l('PrestaLove Easy Menu');
    $this->description = $this->l('Add a new menu on your shop.');
  }

  public function install()
  {
    if(!parent::install() ||
		!$this->registerHook('header') ||
		!$this->registerHook('top') ||
		!$this->installDB())
      return false;
    return true;
  }

  public function installDb()
  {
    Db::getInstance()->ExecuteS('
    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'pl_menu` (
      `id_pl_menu` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
      `name` VARCHAR( 128 ) NOT NULL ,
      `link` VARCHAR( 128 ) NOT NULL ,
      `parent` INT UNSIGNED NOT NULL ,
      `target` VARCHAR( 50 ) NOT NULL ,
      `publish` TINYINT( 1 ) NOT NULL ,
      `order` INT( 1 ) NOT NULL
    ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;');
   Db::getInstance()->ExecuteS('
    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'pl_menu_lang` (
    `id_pl_menu` INT NOT NULL ,
    `id_lang` INT NOT NULL ,
    `name` VARCHAR( 128 ) NOT NULL ,
    INDEX ( `id_pl_menu` , `id_lang` )
    ) ENGINE = MYISAM CHARACTER SET utf8 COLLATE utf8_general_ci;');
    return true;
  }

  public function uninstall()
  {
    if(!parent::uninstall() || 
       !$this->uninstallDB())
    	return false;
    return true;
  }

  private function uninstallDb()
  {
    Db::getInstance()->ExecuteS('DROP TABLE `'._DB_PREFIX_.'pl_menu`');
    Db::getInstance()->ExecuteS('DROP TABLE `'._DB_PREFIX_.'pl_menu_lang`');
    return true;
  }

	public function getContent()
	{
    	global $cookie;
   
		
		$this->_html .= '<h2>'.$this->l('PrestaLove Easy Menu').'</h2>';
		
    	// Add/Edit content 
		$defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
		$languages = Language::getLanguages(false);
		$iso = Language::getIsoById((int)($cookie->id_lang));
		$divLangName = 'name_label';
		
    	if(Tools::isSubmit('submitBlockPLMenuLinks'))
    	{
      		if(Tools::getValue('name') == '')
      		{
        		$this->_html .= $this->displayError($this->l('Unable to add this menu link, please insert name and link'));
      		}
      		else
      		{
        		$this->add(Tools::getValue('name'), Tools::getValue('link'), Tools::getValue('parent', 0), Tools::getValue('target'), Tools::getValue('order', 0), Tools::getValue('publish', 1));
        	$this->_html .= $this->displayConfirmation($this->l('The menu link has been added'));
      		}
    	}
    	if(Tools::isSubmit('submitBlockPLMenuRemove'))
    	{
      		$id_pl_menu = Tools::getValue('id_pl_menu', 0);
      		$this->remove($id_pl_menu);
      
      		$this->_html .= $this->displayConfirmation($this->l('The link has been removed'));
    	}
		
		if(Tools::isSubmit('submitBlockPLMenuEdit'))
    	{
      		$id_pl_menu = Tools::getValue('id_pl_menu', 0);
      		$pl_menu = $this->get($id_pl_menu, $defaultLanguage);
    	}
	
		if(Tools::isSubmit('submitBlockPLMenuEdited'))
    	{
      		$pl_menu = $this->edit(Tools::getValue('id_pl_menu_edit', 0), Tools::getValue('name'), Tools::getValue('link'), Tools::getValue('parent', 0), Tools::getValue('target'), Tools::getValue('order', 0), Tools::getValue('publish', 1), Tools::getValue('parent_old', 0));
      
      		$this->_html .= $this->displayConfirmation($this->l('The link has been edited'));
    	}
       	
   		$this->_html .= '
   		<fieldset>
      		<legend><img src="../img/admin/add.gif" alt="" title="" />'.$this->l('Add/Edit ST Menu Link').'</legend>
      			<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="form">
  					<label>'.$this->l('Name').'</label>
        			<div class="margin-form">';
					foreach ($languages as $language)
					{
						$this->_html .= '
						<div id="name_label_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $defaultLanguage ? 'block' : 'none').';float: left;">
							<input type="text" name="name['.$language['id_lang'].']" id="name_'.$language['id_lang'].'" size="70" value="';
						if($pl_menu[0]['id_pl_menu']) {
							$langName = $this->getName($language['id_lang'],$pl_menu[0]['id_pl_menu']);						
							$this->_html .= $langName[0]['name'];
						}
						$this->_html .= '" />
						</div>';
					 }
					$this->_html .= $this->displayFlags($languages, $defaultLanguage, $divLangName, 'name_label', true);

        			$this->_html .= '</div>';
					$this->_html .= '<p class="clear"> </p>
  					<label>'.$this->l('Link').'</label>
  			<div class="margin-form">
          <input type="text" name="link" value="'.$pl_menu[0]['link'].'" size="70" />
  			</div>
			<label>'.$this->l('Parent').'</label>
  			<div class="margin-form">
				<select name="parent">
					<option value=0> Root </option>';
					
				$this->_html .= $this->displaySelectboxMenu($pl_menu[0]['parent']);
				
				$this->_html .= '</select>
  			</div>
			<label>'.$this->l('Target').'</label>
  			<div class="margin-form">
          <select name="target">
		    <option>none</option>
		    <option value="_blank"';
			if($pl_menu[0]['target']=='_blank') {
				$this->_html .= ' SELECTED';
			}
			$this->_html .= '>_blank</option>
		    <option value="_parent"';
			if($pl_menu[0]['target']=='_parent') {
				$this->_html .= ' SELECTED';
			}
			$this->_html .= '>_parent</option>
		    <option value="_self"';
			if($pl_menu[0]['target']=='_self') {
				$this->_html .= ' SELECTED';
			}
			$this->_html .= '>_self</option>
		    <option value="_top"';
			if($pl_menu[0]['target']=='_top') {
				$this->_html .= ' SELECTED ';
			}
			$this->_html .= '>_top</option>
		  </select>
  			</div>
  			<label>'.$this->l('Publish').'</label>
  			<div class="margin-form">';
			if($pl_menu[0]['publish'] or !$pl_menu[0]['id_pl_menu']) {
          		$this->_html .= '<input type="radio" name="publish" value="1" checked /><img title="Enabled" alt="Enabled" src="../img/admin/enabled.gif"/>
          			<input type="radio" name="publish" value="0" /><img title="Disabled" alt="Disabled" src="../img/admin/disabled.gif"/>';
			}
			else {
          		$this->_html .= '<input type="radio" name="publish" value="1" /><img title="Enabled" alt="Enabled" src="../img/admin/enabled.gif"/>
          			<input type="radio" name="publish" value="0" checked /><img title="Disabled" alt="Disabled" src="../img/admin/disabled.gif"/>';
			}
			$this->_html .= '
  			</div>
			<label>'.$this->l('Order').'</label>
  			<div class="margin-form">
          <input type="text" name="order" value="'.$pl_menu[0]['order'].'" size="10" />
  			</div>
        <p class="center"> ';
		if(!$pl_menu[0]['id_pl_menu']) {
            $this->_html .= '
			<input type="submit" name="submitBlockPLMenuLinks" value="'.$this->l('  Add  ').'" class="button" /> ';
		}
		else {
		    $this->_html .= '<input type="hidden" name="id_pl_menu_edit" value="'.$pl_menu[0]['id_pl_menu'].'" /><input type="hidden" name="parent_old" value="'.$pl_menu[0]['parent'].'" /><input type="submit" name="submitBlockPLMenuEdited" value="'.$this->l('  Edit  ').'" class="button" /> ';
		}
	$this->_html .= '
        </p>
  		</form>
    </fieldset><br />';
	
    $this->_html .= '
    <fieldset>
      <legend><img src="../img/admin/details.gif" alt="details" title="details" />'.$this->l('List Menu Links').'</legend>
      <table style="width:100%;" class="table" id="pl-menu-tablednd">
        <thead>
          <tr>
            <th align="center">'.$this->l('Id').'</th>
            <th>'.$this->l('Name').'</th>
            <th>'.$this->l('Link').'</th>
            <th>'.$this->l('Target').'</th>
            <th align="center">'.$this->l('Order').'</th>
            <th align="center">'.$this->l('Publish').'</th>
            <th align="center">'.$this->l('Action').'</th>
          </tr>
        </thead>
        <tbody>';
          $this->_html .=  $this->displayTableMenu();
        $this->_html .= '</tbody>
      </table>
  	</fieldset>';
	

    echo $this->_html;
	
  }
  
  private function gets($id_lang, $id_pl_menu = null, $publish = null)
  {
    return Db::getInstance()->ExecuteS('
    SELECT l.id_pl_menu, l.target, l.link, ll.name, l.parent, l.order, l.publish
    FROM '._DB_PREFIX_.'pl_menu l 
    LEFT JOIN '._DB_PREFIX_.'pl_menu_lang ll ON (l.id_pl_menu = ll.id_pl_menu AND ll.id_lang = "'.$id_lang.'") WHERE 1=1
    '.((!is_null($id_pl_menu)) ? 'AND l.id_pl_menu = "'.$id_pl_menu.'"' : '').'
	'.((!is_null($publish)) ? 'AND l.publish = "'.$publish.'"' : '').'	
	Order by `parent`, `order`
    ');
  }

  private function get($id_pl_menu, $id_lang)
  {
    return self::gets($id_lang, $id_pl_menu);
  }
  
  private function getsListmenus($id_lang, $parent = 0)
  {
    $menus = array();
  	$pl_menuLinks = Db::getInstance()->ExecuteS('
    SELECT l.id_pl_menu, l.target, l.link, ll.name, l.parent, l.order, l.publish
    FROM '._DB_PREFIX_.'pl_menu l 
    LEFT JOIN '._DB_PREFIX_.'pl_menu_lang ll ON (l.id_pl_menu = ll.id_pl_menu AND ll.id_lang = "'.$id_lang.'") WHERE l.parent = "'. $parent .'" Order by `order`');
	
	$i = 0;
	foreach($pl_menuLinks as $pl_menuLink) {
	  $menus[$i] = $pl_menuLink;
	  $menus[$i]['subitems'] = self::getsListmenus($id_lang, $pl_menuLink['id_pl_menu']);
	  $i++;
	}
	
	
	return $menus;
  }
  
  private function getName($id_lang, $id_pl_menu)
  {
    return Db::getInstance()->ExecuteS('
    SELECT ll.name
    FROM '._DB_PREFIX_.'pl_menu l 
    LEFT JOIN '._DB_PREFIX_.'pl_menu_lang ll ON (l.id_pl_menu = ll.id_pl_menu AND ll.id_lang = "'.$id_lang.'") WHERE 1=1
    '.((!is_null($id_pl_menu)) ? 'AND l.id_pl_menu = "'.$id_pl_menu.'"' : '').'
    ');
  }
  
  private function add($name, $link, $parent = 0, $target, $order = 0, $publish = 1)
  {
    if(!is_array($name))
      return false;

	// Add data on pl_menu table
    Db::getInstance()->autoExecute(
      _DB_PREFIX_.'pl_menu',
      array(
        'link'=>$link,
		'parent'=> (int)$parent,
        'target'=> $target,
		'publish'=> (int)$publish,
		'order'=> (int)$order
      ),
      'INSERT'
    );

	
	// Insert ID
    $id_pl_menu = Db::getInstance()->Insert_ID();
	
	// Add data on pl_menu_lang table
    foreach($name as $id_lang=>$name)
    {
      Db::getInstance()->autoExecute(
        _DB_PREFIX_.'pl_menu_lang',
        array(
          'id_pl_menu'=>$id_pl_menu,
          'id_lang'=>$id_lang,
          'name'=>$name
        ),
        'INSERT'
      );
    }
		
  }
  
  
  private function edit($id_pl_menu, $name, $link, $parent = 0, $target, $order = 0, $publish = 1, $parent_old)
  {
    if(!is_array($name))
      return false;
	  
	// Update data on pl_menu table
	$queryUpdate = 'UPDATE `'._DB_PREFIX_.'pl_menu`
			SET `link` = "'. $link .'",
			`parent` = '. intval($parent) .',
			`target` = "'. $target .'",
			`publish` = '. intval($publish) .',
			`order` = '. intval($order) .'
			WHERE `id_pl_menu` = '.intval($id_pl_menu);
			
	Db::getInstance()->Execute($queryUpdate);
	
	
	// Check reference parent
	$parent_parent = Db::getInstance()->ExecuteS('SELECT `parent` FROM `'._DB_PREFIX_.'pl_menu` WHERE `id_pl_menu` = '.$parent);
	
	if($parent_parent[0]['parent'] == $id_pl_menu) {
		Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'pl_menu`
			SET `parent` = 0 WHERE `id_pl_menu` = '.intval($parent));
	}
	
	// Update data on pl_menu_lang table
    foreach($name as $id_lang=>$name)
    {
	  $queryUpdateName = 'UPDATE `'._DB_PREFIX_.'pl_menu_lang`
			SET `name` = "'. $name .'"
			WHERE `id_lang` = '.intval($id_lang) .' AND `id_pl_menu` = '.intval($id_pl_menu);

	  Db::getInstance()->Execute($queryUpdateName);
    }
  }
  
  
  
  
  private function remove($id_pl_menu)
  {
    Db::getInstance()->delete(_DB_PREFIX_.'pl_menu', "id_pl_menu = '{$id_pl_menu}'"); 
    Db::getInstance()->delete(_DB_PREFIX_.'pl_menu_lang', "id_pl_menu = '{$id_pl_menu}'");
	Db::getInstance()->Execute('UPDATE `'._DB_PREFIX_.'pl_menu`
			SET `parent` = 0 WHERE `parent` = '.intval($id_pl_menu));
  }
 	private function displaySelectboxMenu($defaultValue = 0, $parent = 0, $pretext = '')
	{
    	global $cookie;
  		$listOption = '';
		$pretext .= '-';
  		$pl_menulinks = $this->getsListMenus($cookie->id_lang, $parent);
		foreach ($pl_menulinks as $pl_menulink) {
			$listOption .= '<option value="'.$pl_menulink['id_pl_menu'].'"';
			
			// Select default value
			if($defaultValue == $pl_menulink['id_pl_menu']) {
				$listOption .= ' SELECTED ';
			}
			
			$listOption .= '> '. $pretext . $pl_menulink['name'].' </option>';
			if($pl_menulink['subitems'])
				$listOption .= $this->displaySelectboxMenu($defaultValue, $pl_menulink['id_pl_menu'], $pretext);
		}
		return $listOption;
	}
	
	private function displayTableMenu($parent = 0, $pretext = '')
	{
    	global $cookie;
  		$listField = '';
		if($parent) $pretext = '&nbsp;&nbsp;&nbsp;&nbsp;' . $pretext . '--';
  		$pl_menulinks = $this->getsListMenus($cookie->id_lang, $parent);

		foreach ($pl_menulinks as $pl_menulink) {
			$listField .= '<tr id="rowid-'.$pl_menulink['id_pl_menu'].'">';
			$listField .= '<td align="center">'. $pl_menulink['id_pl_menu'].'</td>';
			$listField .= '<td>'. $pretext . $pl_menulink['name'].'</td>';
			$listField .= '<td>'. $pl_menulink['link'].'</td>';
			$listField .= '<td>'. $pl_menulink['target'].'</td>';
			$listField .= '<td align="center">'. $pl_menulink['order'].'</td>';
			$listField .= '<td align="center">';
				if($pl_menulink['publish']) $listField .= '<img title="Enabled" alt="Enabled" src="../img/admin/enabled.gif"/>';
				else $listField .= '<img title="Disabled" alt="Disabled" src="../img/admin/disabled.gif"/>';
			$listField .= '</td>';
			$listField .= '<td align="center"> <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
                <input type="hidden" name="id_pl_menu" value="'.$pl_menulink['id_pl_menu'].'" />
				<button name="submitBlockPLMenuEdit" class="button"><img src="../img/admin/edit.gif" alt="Edit" title="Edit" /></button>
				<button name="submitBlockPLMenuRemove" class="button" onclick="javascript:return confirm(\''.$this->l('Are you sure you want to remove this link?').'\');" ><img src="../img/admin/delete.gif" alt="Delete" title="Delete" /></button>
          			
          		</form></td>';
			$listField .= '</tr>';
			if($pl_menulink['subitems'])
				$listField .= $this->displayTableMenu($pl_menulink['id_pl_menu'], $pretext);
			
		}
		return $listField;
	}
  	
	private function displayListMenu($parent = 0, $class = 'pl-easy-menu')
	{
    	global $cookie;
		
		// Get current link
		$curent_link = str_replace(__PS_BASE_URI__, '', $_SERVER["REQUEST_URI"]);
		
  		$listMenus = '<ul id="pl-easy-menu-'. $parent .'" class="pl-easy-menu '. $class .'">';
  		$pl_menulinks = $this->getsListMenus($cookie->id_lang, $parent);
		
		$countLink = 0;
		
		foreach ($pl_menulinks as $pl_menulink) {
			$countLink++;
			$li_class = '';
			$curentclass = '';
			
			// Add class for <li> tag
			if ($countLink==1) $li_class .= ' first ';
			elseif ($countLink==count($pl_menulinks)) {$li_class .= ' last ';$style_class .= 'background:none';}
			else $li_class .= '';$style_class .= '';

			// Add active class
			if(($pl_menulink['link']==$curent_link) or ($curent_link==NULL and $pl_menulink['link']=='index.php')) {
				$li_class .= ' li-current ';
				$curentclass .= 'class="selected active"';
			}
			
			// Add item
			$listMenus .= '<li class="'. $li_class .'" style="'.$style_class.'">';
			$target = '';
			if ($pl_menulink['target'] != 'none')
				$target = 'target = "'.$pl_menulink['target'] .'"';
			$listMenus .= '<a href="'. (substr_compare($pl_menulink['link'], 'http://', 0, 7) ? __PS_BASE_URI__ : '') . $pl_menulink['link'] .'" '.$target.' title="'. $pl_menulink['name'] .'" '. $curentclass .'>'. $pl_menulink['name'];
			$listMenus .= '</a>';
			
			// Add submenu
			if($pl_menulink['subitems'])
				$listMenus .= $this->displayListMenu($pl_menulink['id_pl_menu']);
						
			$listMenus .= '</li>';
		}
		
		$listMenus .= '</ul>';
		
		return $listMenus;
	}
	
	public function hookTop($param)
	{
		global $smarty;
		$this->_menu = $this->displayListMenu(0, 'sf-menu');
		$smarty->assign('MENU', $this->_menu);
    	return $this->display(__FILE__, 'prestaloveeasymenu.tpl');
	}
	
	public function hookLeftColumn($params)
	{
		global $smarty;
		$this->_menu = $this->displayListMenu(0, 'sf-menu sf-vertical');
		$smarty->assign('MENU', $this->_menu);
    	return $this->display(__FILE__, 'prestaloveeasymenu.tpl');
	}
	
	public function hookRightColumn($params)
	{
		return $this->hookLeftColumn($params);
	}
	
	public function hookHeader($params)
	{
		return $this->display(__FILE__, 'prestaloveeasymenu-header.tpl');
	}
}
?>