<div class="breadcrumb">
    <a href="{$base_dir}" title="{l s='return to' mod='plblog'} {l s='Home' mod='plblog'}">{l s='Home' mod='plblog'}</a>
    {$length = $pl_path|@count}
    {if $length > 0}
        {$i = 0}
        {foreach from=$pl_path item=path}
            {$i = $i + 1}
            {if $i < $length}
                <span class="navigation-pipe">&bull;</span>
                <a title="{$path['name']}" href="{$base_dir}blog/all-post.html">{$path['name']}</a>
            {/if}
            {if $i >= $length}
                <span class="navigation-pipe">&bull;</span>	
                {$path['name']}
            {/if}
        {/foreach}
    {/if}
</div>
