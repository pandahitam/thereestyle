
<!-- display title -->
<h1>{$pl_category_name|truncate:60}</h1>
<!-- /display title -->

<!-- display post list -->
{if $pl_postes_empty == 0}
	{foreach from=$pl_post_list item=post}
		{$l = $plTools->getPostLink($post['id_pl_blog_post'], $post['link_rewrite'], $post['id_pl_blog_category'])}
		{$count_comment = $plTools->getCountComment($post['id_pl_blog_post'])}
		{*$post_content = $plTools->substr($post['post_description'], 0, 1000)*}	
			<!-- post -->
			<div id="plpost">
				<div class="pltitle">
					<h2>{$post['post_title']}</h2>
				</div>
				<div class="pl_info_post">
					<div class="plcount_comment">{$count_comment}{if $count_comment > 1} {l s='Comments' mod='plblog'}{/if}{if $count_comment <= 1} {l s='Comment' mod='plblog'}{/if}</div>		
					<div class="plauthor">{$post['post_date_create']}</div>		
					<div class="plclear">&nbsp;</div>
				</div>
				<div class="plpost_content"><p>{$post['post_description']|strip_tags|truncate:{$pl_b_summary_character_count}}</p></div>
				<div class="plread_more"><a href="{$l}">{l s='Read more' mod='plblog'}</a></div>
			</div>
			<!-- /post -->
	{/foreach}
{/if}
{if $pl_postes_empty == 1}
{l s='There are no posts in this category' mod='plblog'}
	
{/if}
<!-- /display post list -->