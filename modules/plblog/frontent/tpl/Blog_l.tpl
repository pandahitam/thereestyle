<!-- Lastest post -->{if $DISPLAY_LASTEST_POST != 'Disable'}	{if $DISPLAY_LASTEST_POST == 'Left'}		<div class="block pl_block">			<h4>{l s='Latest Posts' mod='plblog'}</h4>			<div class="block_content center">				<ul>				{foreach from=$pl_lastest_post item=post}					<li>						<a href="{$plTools->getPostLink($post['id_pl_blog_post'], $post['link_rewrite'], $post['id_pl_blog_category'])}">							<span>{$post['post_title']|truncate:40}</span>						</a>						<i>{$post['post_date_create']}</i>						{$pl_blog_post = $post['post_description']|strip_tags}						{$pl_blog_post|truncate:$PL_COLP_MAXIMUM}					</li>				{/foreach}				</ul>				{if $url_rewrite == 1}					<a class="button_large" href="{$home}blog/all-post.html">{l s='view all' mod='plblog'}</a>				{/if}				{if $url_rewrite == 0}					<a class="button_large" href="{$home}modules/plblog/frontent/all-post.php">{l s='view all' mod='plblog'}</a>				{/if}			</div>		</div>		{/if}{/if}<!-- /Lastest post --><!-- Blog categories -->{if $PL_DISPLAY_CATEGORY != 'Disable'}
	{if $PL_DISPLAY_CATEGORY == 'Left'}		<div id="categories_block_left" class="block pl_block_category">			<h4>{l s='Blog categories' mod='plblog'}</h4>			<div class="block_content">				<ul class="tree {if $pl_isDhtml}dhtml{/if}">				{foreach from=$pl_blockCategTree.children item=child name=pl_blockCategTree}					{if $smarty.foreach.pl_blockCategTree.last}						{include file="$pl_branche_tpl_path" node=$child last='true'}					{else}						{include file="$pl_branche_tpl_path" node=$child}					{/if}				{/foreach}				</ul>			</div>		</div>
	{/if}
{/if}<!-- /Blog categories --><!-- Blog tags -->
{if $PL_DISPLAY_TAG != 'Disable'}
	{if $PL_DISPLAY_TAG == 'Left'}
		<div class="block pl_tag">
			<h4>{l s='Blog tags' mod='plblog'}</h4>
			<div class="block_content center">
				<p class="block_content">
					{foreach from=$tags item=tag}
						{if $tag['id_pl_blog_tags'] != 1}
							<a href="{$plTools->getTagLink($tag['id_pl_blog_tags'], $tag['tags_name'])}">{$tag['tags_name']}</a>
						{/if}
					{/foreach}			
				</p>
			</div>
		</div>
	{/if}
{/if}<!-- /Blog tags -->