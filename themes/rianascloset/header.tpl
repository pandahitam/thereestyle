{*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 14008 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang_iso}">
	<head>
		<title>{$meta_title|escape:'htmlall':'UTF-8'}</title>
{if isset($meta_description) AND $meta_description}
		<meta name="description" content="{$meta_description|escape:html:'UTF-8'}" />
{/if}
{if isset($meta_keywords) AND $meta_keywords}
		<meta name="keywords" content="{$meta_keywords|escape:html:'UTF-8'}" />
{/if}
		<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
		<meta name="generator" content="PrestaShop" />
		<meta name="robots" content="{if isset($nobots)}no{/if}index,follow" />
		<link rel="icon" type="image/vnd.microsoft.icon" href="{$img_ps_dir}favicon.ico?{$img_update_time}" />
		<link rel="shortcut icon" type="image/x-icon" href="{$img_ps_dir}favicon.ico?{$img_update_time}" />
		<script type="text/javascript">
			var baseDir = '{$content_dir}';
			var static_token = '{$static_token}';
			var token = '{$token}';
			var priceDisplayPrecision = {$priceDisplayPrecision*$currency->decimals};
			var priceDisplayMethod = {$priceDisplay};
			var roundMode = {$roundMode};
		</script>
{if isset($css_files)}
	{foreach from=$css_files key=css_uri item=media}
	<link href="{$css_uri}" rel="stylesheet" type="text/css" media="{$media}" />
	{/foreach}
{/if}
{if isset($js_files)}
	{foreach from=$js_files item=js_uri}
	<script type="text/javascript" src="{$js_uri}"></script>
	{/foreach}
{/if}
		
	<script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
	</head>
	
	<body {if $page_name}id="{$page_name|escape:'htmlall':'UTF-8'}"{/if}>
	{if !$content_only}
		{if isset($restricted_country_mode) && $restricted_country_mode}
		<div id="restricted-country">
			<p>{l s='You cannot place a new order from your country.'} <span class="bold">{$geolocation_country}</span></p>
		</div>
		{/if}
		<!-- Header -->
		<div id="header">
			<div id="page">
				<a id="header_logo" href="{$base_dir}index.html" title="{$shop_name|escape:'htmlall':'UTF-8'}">
					{*<img class="logo" src="{$img_ps_dir}logo.jpg?{$img_update_time}" alt="{$shop_name|escape:'htmlall':'UTF-8'}" {if $logo_image_width}width="{$logo_image_width}"{/if} {if $logo_image_height}height="{$logo_image_height}" {/if} />*}
					{*change by rocanowi 20120801*}
					The Ree Style
				</a>
				<div id="header_right">
				{*add by rocanowi - utk search, pilih bahasa, dan pilih currency*}
					<div id="search_block_top">
						<form id="searchbox" action="http://www.rianascloset.com/search.php" method="get">
							<p>
							<label for="search_query_top"></label>
							<input type="hidden" value="position" name="orderby">
							<input type="hidden" value="desc" name="orderway">
							<input id="search_query_top" class="search_query ac_input" type="text" value="" name="search_query" autocomplete="off" placeholder="Search something">
							<input class="button" type="submit" value="Go" name="submit_search" style="margin-left: 160px;position: absolute;">
							</p>
						</form>
					</div>
				</div>
				
	
				{$HOOK_TOP}
				{$HOOK_HEADER}
	
				
			</div>
		</div>
			
		<div id="page">
			<div id="headershadow"></div>
			<div id="headerbottom"></div>		
			<div class="clear"></div>
			
			<div id="columns">
				<!-- Left -->
				{if $page_name != 'cms'}
				<div id="left_column" class="column">
					{$HOOK_LEFT_COLUMN}
				</div>
				{/if}

				<!-- Center -->
				<div id="center_column" style=" {if $page_name == 'main' || $page_name == 'order-opc' || $page_name == 'details' || $page_name == 'cms'}width: 980px;{/if}">
	{/if}