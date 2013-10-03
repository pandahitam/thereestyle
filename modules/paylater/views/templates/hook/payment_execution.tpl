{capture name=path}{l s='Order Summary' mod='paylater'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='paylater'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.'}</p>
{else}

<form action="{$this_path_ssl}validation.php" method="post">
	<p>
		{l s='Here is a short summary of your order:' mod='paylater'}
	</p>
	<p>
		- {l s='The total amount of your order is' mod='paylater'}
		<span id="amount" class="price">{displayPrice price=$total}</span>
		{if $use_taxes == 1}
			{l s='(tax incl.)' mod='paylater'}
		{/if}
	</p>
	<p>
		<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
	</p>
	<br>
	<p>
		{l s='Our customer service will check availability of the items, then contact you for further payment information using phone/email.' mod='paylater'}
	</p>
	<p>
		<b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='paylater'}.</b>
	</p>
	<p class="cart_navigation">
		{*
		<a href="{$link->getPageLink('order.php', true)}?step=3" class="button_large hideOnSubmit">{l s='Other payment methods' mod='paylater'}</a>
		*}
		<input type="submit" name="submit" value="{l s='I confirm my order' mod='paylater'}" class="button_large hideOnSubmit" />
	</p>
</form>
{/if}
