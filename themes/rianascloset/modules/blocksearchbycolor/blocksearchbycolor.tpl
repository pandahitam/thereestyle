{*
* 2012 Web 4 Infinity
* www.web4infinity.com
*}
  
<!-- MODULE Block new products carousel -->
<div id="search-by-color-block" class="block">
<h4>Search by color</h4>
<div class="block_content">
<ul id="block_color_search">
{foreach from=$attcolor item='color' key=j}
<li><a href="search?orderby=position&orderway=desc&search_query={$attname[$j]}&submit_search=Go" title="{$attname[$j]}" style="background-color:{$color}" class="searchcolorbox"></a></li>
{/foreach}
</ul>
</div>
</div>
<!-- /MODULE Block new products -->