<br />
<br />
<p>
     {l s='Your order on' mod='myshortcart'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='myshortcart'}
	<br />
    <br />
	{l s='You have chosen the <b>MyShortCart Credit Card</b> Transaction method.' mod='myshortcart'}
	<br />
    <br />
    <span class="bold">{l s='Your order will be sent very soon.' mod='myshortcart'}</span>
    <br />
    <br />
    <b>{l s='For any questions or for further information, please contact our' mod='myshortcart'} <a href="{$base_dir_ssl}contact-form.php">{l s='customer support' mod='myshortcart'}</a>.</b>
    
</p>

<form name="autocontinue" action="{$link->getModuleLink('myshortcart', 'validation', [], true)}" id="autocontinue" method="post">
<p class="cart_navigation">
        <input type=hidden name="O_STATUS" value="succes">
        <input type="submit" name="submit" value="{l s='Continue' mod='myshortcart'}" class="exclusive_large" />
</p>