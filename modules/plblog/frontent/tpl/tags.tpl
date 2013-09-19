{l s='Tags' mod='plblog'}:&nbsp;
{$c = 1}
{foreach from=$pl_data_tags item=tag}
	{if ($c == 0)}
		|
	{/if}
	<a href="{$plTools->getTagLink($tag['id_pl_blog_tags'], $tag['tags_name'])}">{$tag['tags_name']}</a>
	{$c = 0}
{/foreach}
