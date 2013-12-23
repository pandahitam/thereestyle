{capture name=path}{l s='Shipping' mod='myshorcart'}{/capture}

<h2>{l s='Payment' mod='myshortcart'}</h2>

{assign var='current_step' value='payment'}

{if isset($nbProducts) && $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.'}</p>
{else}

<h3>{l s='Order Details' mod='myshortcart'}</h3>

<form name="order" action="{$msc_url}" method="post">
    <table border="0">
        <tr>
            <td>{l s='You have chosen to pay by Credit Card/DOKU Wallet/Virtual Account.' mod='myshortcart'}
                {l s=' Here is a short summary of your order:' mod='myshortcart'}
                <br/><br/>
            </td>
        </tr>
        <tr>
            <td>{l s='The total amount of your order is : ' mod='myshortcart'} {number_format($msc_amount, 2, ',', '.')}</td>
        </tr>
        <tr>
            <td>
                <br/><br/>
                <b>{l s='Please click \'Submit Order\' to proceed to payment page' mod='checkout'}.</b>
            </td>
        </tr>
    </table>

    <input type=hidden name="BASKET" value="{$basket}">
    <input type=hidden name="STOREID" value="{$msc_storeid}">
    <input type=hidden name="TRANSIDMERCHANT" value="{$msc_invoice}">
    <input type=hidden name="AMOUNT" value="{$msc_amount}">
    <input type=hidden name="URL" value="{$url}">
    <input type=hidden name="WORDS" value="{$msc_words}">
    <input type=hidden name="CNAME" value="{$c_name}">
    <input type=hidden name="CEMAIL" value="{$c_email}">
    <input type=hidden name="CWPHONE" value="{$c_phone}">
    <input type=hidden name="CHPHONE" value="{$c_phone}">
    <input type=hidden name="CMPHONE" value="{$c_mphone}"> 
    <input type=hidden name="CCAPHONE" value="{$c_phone}"> 
    <input type=hidden name="CADDRESS" value="{$c_address}"> 
    <input type=hidden name="CZIPCODE" value="{$c_zipcode}"> 
    <input type=hidden name="BIRTHDATE" value="0000-00-00">

    <p class="cart_navigation">
        <input type="submit" name="submit" value="{l s='Submit Order' mod='myshortcart'}" class="exclusive_large" />
    </p>
</form>

{/if}