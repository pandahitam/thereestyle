<?php

class Blockshopbyprice extends Module
{
	private $_html;

	function __construct()
 	{
 	 	$this->name = 'blockshopbyprice';
 	 	$this->tab = 'Blocks';
		$this->displayName = $this->l('Shop by price block');
		$this->description = $this->l('Adds a block listing products by price');
		$this->confirmUninstall = $this->l('Uninstalling this will delete any price ranges you set up in this module. It might be worth making a note of them before uninstalling. Pressing OK will uninstall the module or pressing Cancel will leave it installed');
		$this->version = '1.44';
        parent::__construct();
 	}

    public function install()
	{
		if (!parent::install()
			OR !$this->createtbl() //calls function to create price range table
			OR !$this->registerHook('leftColumn'))
			return false;
		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall()
			OR !Db::getInstance()->Execute('DROP TABLE `'._DB_PREFIX_.'shopbyprice`;'))
			return false;
		return true;
	}

	function hookRightColumn($params)
	{
		$db = Db::getInstance(); // create and object to represent the database
		$results = $db->ExecuteS("SELECT `minprice`, `maxprice` FROM `"._DB_PREFIX_."shopbyprice` ORDER BY `displayorder`"); //retrieve price ranges
		// while ($row = mysql_fetch_assoc($result))
			// $results[] = $row;
		if (empty($results)){$results = 0;} ; // if no results then set $results to 0s
		global $cookie;
		global $smarty;
		
		$currency = Currency::getCurrency(intval($cookie->id_currency));

		// find category
		$id_category = (int)(Tools::getValue('id_category', 1));

		// var_dump($results);
		// die;
		
		$smarty->assign(array(
			'currencysign' => $currency["sign"],
			'pricerange' => $results,
			'id_category'  => $id_category
			));

		return $this->display(__FILE__, 'blockshopbyprice.tpl');
	}

	function hookLeftColumn($params)
	{
		return $this->hookRightColumn($params);
	}

        function createtbl()
	{
		     /**Function called by install -
		     * creates the "shopbyprice" table required for storing price ranges
		     */

                    $db = Db::getInstance();
            	    $query = "CREATE TABLE `"._DB_PREFIX_."shopbyprice` (
					`id_range` INT NOT NULL AUTO_INCREMENT,
                                        `minprice` VARCHAR( 10 ) NOT NULL ,
                                        `maxprice` VARCHAR( 10 ) NOT NULL,
										`displayorder` INT (2) NOT NULL DEFAULT 99,
                                        PRIMARY KEY(`id_range`)
                                        )";
    		    $db->Execute($query);
		    return true;
	}

  	public function getContent()
        {

          if (isset($_POST['Delete']) OR isset($_POST['Add']))
         {
             $this->_postProcess();
         }
		 
          $this->_html = $this->_html.'<h2>'.$this->displayName.' v.'.$this->version.'</h2><h3><a style="color:red;margin:5px;" target="_blank" href="http://www.techietips.net/prestashop-shop-by-price-module.html">'.$this->l('>> Read Documentation on this module <<').'</a></h3>';
		  $this->_Priceranges();
          return $this->_html;

        }
      
        public function _Priceranges()
        {
			$priceranges = Db::getInstance()->ExecuteS('
				SELECT * FROM `'._DB_PREFIX_.'shopbyprice`
				ORDER BY displayorder ASC
			');

         $this->_html .='
         <script language = "javascript">
         function CheckAll()
         {
             for (var i=0;i<document.form1.elements.length;i++)
             {
                 var x = document.form1.elements[i];
                 if (x.name == "moderate[]")
                 {
                     x.checked = document.form1.selall.checked;
                 }
             }
         }
         </script>';

			$this->_html .=
		'<form action="'.$_SERVER['REQUEST_URI'].'" method="post" name="form1">
			<fieldset>
			<legend>'.$this->l('Price Ranges').'</legend>
			<table>
                          <tr>
                            <th style="color:blue" >'.$this->l('Min Price').'</th>
                            <th style="color:blue">'.$this->l('Max Price').'</th>
							<th style="color:blue">'.$this->l('Display Order').'</th>
                           </tr>';
               foreach ($priceranges as $row)
               {
                 $this->_html .=
                         '<tr>
                             <td style="min-width:100px" >'.$row['minprice'].'</td>
                             <td style="min-width:100px" >'.$row['maxprice'].'</td>
							 <td style="min-width:100px" >'.$row['displayorder'].'</td>
                             <td><INPUT TYPE=checkbox VALUE="'.$row['id_range'].'" NAME="moderate[]"></td>
                          </tr>';
               }
		$this->_html .=
                        '</table>
                         <hr>
                         <input class="button" name="Delete" value="'.$this->l('Delete Selected').'" type="submit" type="submit" style="width: 200px;"/>
			</fieldset>
                        <br>
                       <fieldset>
                       <legend>'.$this->l('Add Price Range').'</legend>
                       <hr style="height:1px; dotted #ccc" width="400" align="left" />
                       <label for="minprice">'.$this->l('Enter Minimum Price').'</label>
                       <input type="text" name="minprice">
                      <hr style="height:1px; dotted #ccc" width="400" align="left" />
                       <label for="maxprice" >'.$this->l('Enter Maximum Price').'</label>
                       <input type="text" name="maxprice" value="'.$this->l('No Maximum').'">
                       <hr style="height:1px; dotted #ccc" width="400" align="left" />
					   <label for="displayorder" >'.$this->l('Enter Display Order').'</label>
                       <input type="text" name="displayorder" value="99">
                       <hr style="height:1px; dotted #ccc" width="400" align="left" />
                      <input class="button" name="Add" value="'.$this->l('Add This Price Range').'" type="submit" type="submit" style="width: 200px;"/>
           </fieldset>
		</form>';
        }

            private function _postProcess()
           {

           if (isset($_POST['Delete']) && ($_POST['moderate']))
             {
                 foreach($_POST['moderate'] as $check => $val)
                 {
                     $deleted=Db::getInstance()->Execute('
                     DELETE FROM `'._DB_PREFIX_.'shopbyprice`
                     WHERE id_range =  "'.($val).'"
                     ');
                 }
                $this->_html .= $this->displayConfirmation($this->l('The selected shop price range has been deleted'));
               }

              if (isset($_POST['Add'])  )
              {
                $maxprice = $this->chkMaxprice();
                $minprice = $this->chkMinprice();
                $displayorder = $this->chkdisplayOrder();
                if (!$minprice == false){
                  
                    $db = Db::getInstance(); // create an object to represent the database
                    $result = $db->Execute('
                    INSERT INTO `'._DB_PREFIX_.'shopbyprice`
                    (`minprice`,`maxprice`,`displayorder`)
                    VALUES
                    ("'.$minprice.'"
                    ,"'.$maxprice.'"
					,"'.$displayorder.'"
                    )'); //write price ranges

                }
              }

             return $this->_html;
           }

         function chkMinprice()  // function to validate the min price
         {
           if (isset($_POST['minprice']))
           {
               $minprice = $_POST['minprice'];
               if (!is_numeric ($minprice))
                   {
                       $this->_html .= $this->displayError($this->l('Please Set a Minimum Price - it must be a number e.g 0 and above'));
                       return false;
                   }
               else
               return $_POST['minprice'];

           }
               else
               $this->_html .= $this->displayError($this->l('Please Set a Minimum Price') );
               return false;
         }

         function chkMaxprice()  // function to validate the max price
         {
             if (isset($_POST['maxprice']))
             {
              $maxprice = $_POST['maxprice'];
              if (is_numeric($maxprice))
                  {
                   return $maxprice;
                  }
              }
            $maxprice = "NoMax";  // if it fails validation then just set no maximum
            return $maxprice;
         }
	 
		 function chkdisplayOrder()  // function to validate the display Order
         {
			if (isset($_POST['displayorder']))
			{
				$maxprice = $_POST['displayorder'];
				if (is_numeric($displayorder))
				{
                   return $displayorder;
				}
			}
            $displayorder = "99"; // if it fails validation - then just add the display order as 99
            return $maxprice;
		}
}