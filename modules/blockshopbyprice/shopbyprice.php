<?php
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../header.php');

require_once(dirname(__FILE__).'/../../product-sort.php');

$errors = array();

function getminPrice()
{
    $minPrice = floatval(Tools::getValue("minprice"));
    return Tools::convertPrice($minPrice, NULL, false);
}
 
function getmaxPrice()
{
    $maxPrice = Tools::getValue("maxprice", false);
    if ($maxPrice)
    {
        $maxPrice = floatval($maxPrice);
        return Tools::convertPrice($maxPrice, NULL, false);
    }

    return false;
}

function getQuery()
{
    if (getmaxPrice())
    {
        $query = 'AND p.`price` BETWEEN '.getminPrice().' and '.getmaxPrice().'';
    }
     else
    {
        $query = 'AND p.`price` >= '.getminPrice().'';
    }

    return $query;
}

function productsbyPrice($id_lang, $pageNumber = 0, $nbProducts = 10, $orderBy = NULL, $orderWay = NULL, $id_category = 1)
{
        global $cookie;

        if (empty($orderBy) || $orderBy == 'position') $orderBy = 'date_add';
        if (empty($orderWay)) $orderWay = 'DESC';
        if ($orderBy == 'id_product' OR $orderBy == 'price' OR $orderBy == 'date_add')
                $orderByPrefix = 'p';
        elseif ($orderBy == 'name')
                $orderByPrefix = 'pl';
        if (!Validate::isOrderBy($orderBy) OR !Validate::isOrderWay($orderWay))
                die(Tools::displayError());

        // add category filter
        $category_filter = '';
        if ($id_category > 1)
        {
            $category_filter = ' AND cg.`id_category`='.$id_category;
        }

        $result = Db::getInstance()->ExecuteS('
        SELECT SQL_CALC_FOUND_ROWS 
            p.*, pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, p.`ean13`,
                i.`id_image`, il.`legend`, t.`rate`, m.`name` AS manufacturer_name

        FROM `'._DB_PREFIX_.'product` p
        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.intval($id_lang).')
        LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
        LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.intval($id_lang).')

        LEFT JOIN `'._DB_PREFIX_.'tax_rule` tr ON (p.`id_tax_rules_group` = tr.`id_tax_rules_group`
            AND tr.`id_country` = '.intval(Country::getDefaultCountryId()).'
	    AND tr.`id_state` = 0)
	LEFT JOIN `'._DB_PREFIX_.'tax` t ON (t.`id_tax` = tr.`id_tax`)
	LEFT JOIN `'._DB_PREFIX_.'tax_lang` tl ON (t.`id_tax` = tl.`id_tax` AND tl.`id_lang` = '.intval($id_lang).')

        LEFT JOIN `'._DB_PREFIX_.'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
        WHERE p.`active` = 1
        '.getQuery().'
        AND p.`id_product` IN (
                SELECT cp.`id_product`
                FROM `'._DB_PREFIX_.'category_group` cg
                LEFT JOIN `'._DB_PREFIX_.'category_product` cp ON (cp.`id_category` = cg.`id_category`)
                WHERE cg.`id_group` '.(!$cookie->id_customer ?  '= 1' : 'IN (SELECT id_group FROM '._DB_PREFIX_.'customer_group WHERE id_customer = '.intval($cookie->id_customer).')').'
                    '.$category_filter.'
        )
        ORDER BY '.(isset($orderByPrefix) ? pSQL($orderByPrefix).'.' : '').'`'.pSQL($orderBy).'` '.pSQL($orderWay).'
        LIMIT '.intval($pageNumber * $nbProducts).', '.intval($nbProducts));

        $numProd = Db::getInstance()->getValue('SELECT FOUND_ROWS()');

        if ($orderBy == 'price')
                Tools::orderbyPrice($result, $orderWay);
        if (!$result)
                return false;

        return array(
            'list'  => Product::getProductsProperties(intval($id_lang), $result),
            'num'   => intval($numProd)
            );
}

if ( ! Tools::getValue("minprice"))
{
    $smarty->display(_PS_ROOT_DIR_.'/modules/blockshopbyprice/shopbyprice-nomin.tpl');
}
else
{
    global $link, $cookie;
    
    // find category
    $id_category = (int)(Tools::getValue('id_category', 1));

    // dirty hack to get page number and number of items on page
    $controller->pagination();
    $result = productsbyPrice(intval($cookie->id_lang), intval($controller->p) - 1, intval($controller->n), $controller->orderBy, $controller->orderWay, $id_category);
    $controller->pagination($result['num']);

    // another dirty hack to have module working with url rewriting
    $requestNb = $link->getPaginationLink(false, false, true, false, false, true);
    $requestPage = $link->getPaginationLink(false, false, false, false, true, false);

    if (Configuration::get('PS_REWRITING_SETTINGS') AND $pos = strpos($requestPage, '?'))
    {
        $requestPage = substr_replace($requestPage, "/modules/blockshopbyprice/shopbyprice.php", 0, $pos);
    }

    $smarty->assign(array(
        'products' => $result['list'],
        'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
        'requestNb' => $requestNb,
        'requestPage' => $requestPage
        ));

    $smarty->display(_PS_ROOT_DIR_.'/modules/blockshopbyprice/shopbyprice.tpl');

}

require_once(dirname(__FILE__).'/../../footer.php');

?>
