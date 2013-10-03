{capture name=path}{l s='Shop By Price' mod='blockshopbyprice'}{/capture}
{include file="$tpl_dir/breadcrumb.tpl"}

<h2>{l s='Shop By Price' mod='blockshopbyprice'}</h2>

{if $products}
	{include file="product-sort.tpl"}
	{include file="$tpl_dir/product-list.tpl" products=$products}
        {include file="pagination.tpl"}

{else}
	<p class="warning">{l s='No Products in that price range.' mod='blockshopbyprice'}</p>
{/if}
