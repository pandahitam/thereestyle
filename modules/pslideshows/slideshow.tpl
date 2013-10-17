
{if $slideshowImages|@count > 0 && $slideshowImages}
<script type="text/javascript">

$(document).ready(function(){ldelim}
	$('#pslideshow_{$slideshowId} .pslideshow').bxSlider({ldelim}
		 pager:{$slideshowConf.show_pager},
		 controls:{$slideshowConf.show_arrows},
		 pause:{$slideshowConf.transition_pause},
		 speed:{$slideshowConf.transition_speed},
		 infiniteLoop:true,
		 auto:true,
		 prevText: '',
		 nextText: '',
		 randomStart:{$slideshowConf.random_start},
		 mode:'{$slideshowConf.effect}'
	{rdelim});	
{rdelim});

</script>

<style type="text/css">
	#pslideshow_{$slideshowId} {ldelim} width:{$slideshowWidth}px;  {rdelim}
	#pslideshow_{$slideshowId} .pslideshow ul li {ldelim} width:{$slideshowWidth}px; height:{$slideshowHeight}px; 	{rdelim}
</style>

<div class="clear"></div>

<div id="pslideshow_{$slideshowId}">
	<ul class="pslideshow">
		{foreach from=$slideshowImages item=img name=slideshowImages}			
		<li>
			{if $img.link != ''}<a title="{$img.title}" href="{$img.link}" {if $img.target_blank == 1}target="_blank"{/if}>{/if}
			<img src="{$img.src}" width="{$slideshowWidth}" height="{$slideshowHeight}" alt="{$img.title}" />
			{if $img.link != ''}</a>{/if}
			
			{if $slideshowConf.show_title && $img.title != ''}
			<div class="overlay">                 
				<h3>{if $img.link != ''}<a title="{$img.title}" href="{$img.link}" {if $img.target_blank == 1}target="_blank"{/if}>{/if}{$img.title}{if $img.link != ''}</a>{/if}</h3>
			</div>
			{/if}
		</li>
		{/foreach}		
	</ul>
</div>
<div class="clear"></div>
{/if}