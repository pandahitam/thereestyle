<!-- MODULE blockshopbyprice -->
<div  class="block">
	<h4>{l s='Shop by Price' mod='blockshopbyprice'}</h4>
         <div class="block_content">
             <ul class="products">
                  {section name=nr loop=$pricerange}
                   <li>
                   {if $pricerange[nr].maxprice == "NoMax"}
                        <a href="{$base_dir}modules/blockshopbyprice/shopbyprice.php?id_category={$id_category}&minprice={$pricerange[nr].minprice}"/>{l s='more' mod='blockshopbyprice'} {$currencysign}{$pricerange[nr].minprice}</a>
                   {else}
                        <a href="{$base_dir}modules/blockshopbyprice/shopbyprice.php?id_category={$id_category}&minprice={$pricerange[nr].minprice}&maxprice={$pricerange[nr].maxprice}"/>{$currencysign}{$pricerange[nr].minprice} - {$pricerange[nr].maxprice}</a>
                   {/if}
                   </li>
                  {/section}
             </ul>
        </div>
 </div>
<!-- /MODULE blockshopbyprice-->