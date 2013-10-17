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

		{if !$content_only}
				</div>

<!-- Right -->
				<div id="right_column" class="column">
					{$HOOK_RIGHT_COLUMN}
				</div>
			</div>

<!-- Footer -->
			{*<div id="footer2"></div>*}
			<div id="footer">
				<div id="topfooter" class=" clearfix">
					<div class="span-1">
						<ul>
								<li><a href="{$link->getCMSLink('6')}">About The Ree Style</a></li>
								<li><a href="{$link->getPageLink('Contact')}">Contact Us </a></li>
								<li><a href="{$link->getCMSLink('9')}">Press</a></li>
								<li><a href="{$link->getCMSLink('10')}">Request Catalog</a></li>
								<li><a href="{$link->getCMSLink('11')}">Membership Cards</a></li>
						</ul>  
					</div>
					<div class="span-2">
						<ul>
							   <li><a href="{$link->getCMSLink('12')}">Help & FAQ</a></li>
							   <li><a href="{$link->getCMSLink('13')}">How To Order</a></li>
							   <li><a href="{$link->getCMSLink('14')}">Shipping Policy</a></li>
							   <li><a href="{$link->getCMSLink('19')}">Size Guide</a></li>
							   <li><a href="{$link->getCMSLink('20')}">Return & Exchange</a></li>
						   
						</ul>  
					</div>
					<div class="span-3">
						<ul>
							<li><a href="{$link->getCMSLink('21')}">Become Reseller</a></li>
							<li><a href="{$link->getCMSLink('22')}">Free Shipping</a></li>
							<li><a href="{$link->getCMSLink('23')}">Careers</a></li>
							<li><a href="{$link->getCMSLink('24')}">Sitemap</a></li>    
						</ul>  
					</div>
					<div class="newsletter">
						Sign Up For Our Newsletter
						{$HOOK_FOOTER}
					</div>
					
					<div id="socmed">
						<ul>
							<li><a href="http://www.facebook.com"><img src="http://www.rianascloset.com/themes/rianascloset/img/facebook.png"></a></li>
							<li><a href="http://www.twitter.com"><img src="http://www.rianascloset.com/themes/rianascloset/img/twitter.png"></a></li>
							<li><a href="http://www.youtube.com"><img src="http://www.rianascloset.com/themes/rianascloset/img/youtube.png"></a></li>
						</ul>
					</div>
				</div>
			</div>
        </div>
        <div id="bottomfooter">
	        <div id="footertag">
	       <b>Need Help?</b> +6221-1234567   <b>E-mail</b>:ask@thereestyle.com
	        <div id="copyright">Copyright 2012 The Ree Style All Right Reserved</div>
	        </div>
	    </div>
	{/if}
	<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>
	</body>
</html>