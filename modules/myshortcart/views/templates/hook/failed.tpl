<br />
<br />
<p>
    {l s='Your order on' mod='myshortcart'} <span class="bold">{$shop_name}</span> {l s='is fail. Please check again or contact merchant' mod='myshortcart'}
    <br />
    <br />
    <span class="bold">{l s='Please Contact Myshortcart.' mod='myshortcart'}</span>
	<br />
    <br />
    <b>{l s='For any questions or for further information, please contact our' mod='myshortcart'} <a href="{$base_dir_ssl}contact-form.php">{l s='customer support' mod='myshortcart'}</a>.</b>
</p>
<form action="{$link->getModuleLink('myshortcart', 'validation', [], true)}" method="post">
<p class="cart_navigation">
        <input type=hidden name="O_STATUS" value="failed">
        <input type="submit" name="submit" value="{l s='Continue' mod='myshortcart'}" class="exclusive_large" />
</p>
</form>