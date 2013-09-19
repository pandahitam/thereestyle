<!-- load javascript, css -->
<!-- TinyMCE -->
<script type="text/javascript" src="{$pl_blog_post_detail_path_tinymce}"></script>
<script type="text/javascript">
	tinyMCE.init({
		mode : "textareas",
		theme : "simple",
		width:"340"
	});
</script>
<!-- /TinyMCE -->

<script type="text/javascript">
	function submitform()
	{
	  document.pladdcomment.submit();
	}
</script>


<!-- display message -->
{if $pl_blog_post_detail_display_message == 1}
	<div class="plconf">
		<img src="{$pl_blog_post_detail_message}">
		{l s='Comment posted. Awaiting moderator validation.' mod='plblog'}
		
	</div>
{/if}
<!-- /display message -->

<!-- display post -->
{if $pl_blog_post_display_detail == 1}
	<div id="plpost">
		<!--<h2>{$pl_blog_post_detail['post_title']|truncate:60}</h2>-->
		<h1>{$pl_blog_post_detail['post_title']|truncate:60}</h1>
		<div class="pl_info_post">
			<div class="plcount_comment">{$pl_blog_post_detail_count_comment}{if $pl_blog_post_detail_count_comment > 1} {l s='Comments' mod='plblog'}{/if}{if $pl_blog_post_detail_count_comment <= 1} {l s='Comment' mod='plblog'}{/if}</div>		
			<div class="plauthor">{$pl_blog_post_detail['post_date_create']}</div>		
			<div class="plclear">&nbsp;</div>
		</div>
		<div class="plpost_content"><p>{$pl_blog_post_detail['post_description']}</p></div>
		<div class="plclear">&nbsp;</div>
	</div>
{/if}
<!-- /display post -->

<!-- display tags -->
{if $pl_display_tags == 1}
	<div class="pl_post_tag">
		{include file="{$_PS_MODULE_DIR_}plblog/frontent/tpl/tags.tpl"}
	</div>
{/if}
<!-- /display tags -->

<!-- display comment -->
<div class="pl_list_comment">
	<div class="pltitle_comment">{$pl_blog_post_detail_count_comments}{if $pl_blog_post_detail_count_comments <= 1} {l s='Comment' mod='plblog'}{/if}{if $pl_blog_post_detail_count_comments > 1} {l s='Comments' mod='plblog'}{/if}</div>
	{if $pl_blog_post_detail_display == 1}
		{foreach from=$pl_blog_post_detail_comments item=comment}
			<div class="plcomment">
				<div class="pluser_name">{$comment['comment_author_name']}</div>
				<div class="pldate_create">{$comment['comment_date_create']}</div>
				<div class="plcomment_content">{$comment['comment_content']}</div>
			</div>
		{/foreach}
	{/if}
</div>
<!-- /display comment -->

<!-- display form -->

{if $pl_blog_post_detail_display_form == 1}
	<div class="pl_comment_form">
		<form action="" name="pladdcomment" method="post" class="std">
			<fieldset>
				<h3>{l s='Send a comment' mod='plblog'}</h3>
				{if $pl_comment_error == 1}
					<div class="error" style="background-color:#FAE2E3;width:96%;border:1px #EC9B9B solid">
						<img src="{$pl_ps_base_uri}img/admin/error2.png">errors
						<ol>
							{if $plauthor_name_msg == 1}<li>{$plauthor_name_msg_content}</li>{/if}
							{if $plauthor_email_msg == 1}<li>{$plauthor_email_msg_content}</li>{/if}
							{if $plcomment_content_msg == 1}<li>{$plcomment_content_msg_content}</li>{/if}
							{if $plsecurity_code_msg == 1}<li>{$plsecurity_code_msg_content}</li>{/if}
						</ol>
					</div>
				{/if}
				<p class="text">
					<label for="name">{l s='Full Name' mod='plblog'} <em class="pl_requie">*</em></label>
					<input class="plinput" type="text" name="author_name" value="{if $pl_blog_post_detail_display_message == 0}{$author_name}{/if}"/>
				</p>
				<p class="text">
					<label for="email">{l s='Email' mod='plblog'} <em class="pl_requie">*</em></label>
					<input class="plinput" type="text" name="author_email" value="{if $pl_blog_post_detail_display_message == 0}{$author_email}{/if}"/> 
				</p>
				<p class="textarea">
					<label for="comment">{l s='Comment' mod='plblog'} <em class="pl_requie">*</em></label>
					<textarea class ="rte" id="elm1" name="comment_content" cols="20" rows="15">{if $pl_blog_post_detail_display_message == 0}{$comment_content}{/if}</textarea>
				</p>
				{if $pl_display_captcha == 1}
					<p class="text">
						<label>{l s='Security Code' mod='plblog'} <em class="pl_requie">*</em></label>
						<input class="plinput-text" name="security_code" type="text" size="10"/>&nbsp;&nbsp;
						<img class="plimages" border="0px" src="{$pl_ps_base_uri}modules/plblog/CaptchaSecurityImages.php?width=65&height=20&characters=4" />
					</p>
				{/if}
				<p class="submit">
					<a href="javascript:submitform()" class="exclusive_large">
						{l s='Send' mod='plblog'}
					</a>
				</p>
				<div class="plclear">&nbsp;</div>	
				<input type="hidden" name="plsubmitcomment" value="true" />
			</fieldset>
		</form>
	</div>
{/if}
<!-- /display form -->