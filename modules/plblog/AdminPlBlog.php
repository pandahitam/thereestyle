<?php
require_once dirname(__FILE__).'/AdminPlCategory.php';
require_once dirname(__FILE__).'/AdminPlPost.php';
require_once dirname(__FILE__).'/AdminPlComment.php';
require_once dirname(__FILE__).'/AdminPlTags.php';
class AdminPlBlog extends AdminTab
{
	private $adminCategory;
	private $adminPost;
	private $adminComment;
	private $adminTags;
	function __construct()
	{
		$this->adminCategory = new AdminPlCategory();
		$this->adminPost = new AdminPlPost();
		$this->adminComment = new AdminPlComment();
		$this->adminTags = new AdminPlTags();
				$this->table = array('pl_blog_category', 'pl_blog_comment', 'pl_blog_post', 'pl_blog_tags');
				parent::__construct();				$this->adminCategory->token = $this->token;		$this->adminPost->token = $this->token;		$this->adminComment->token = $this->token;		$this->adminTags->token = $this->token;		
	}
	/**	 * Display errors	 * @view AdminTab::displayErrors()	 */		     	public function displayErrors()	{		parent::displayErrors();		$this->adminCategory->displayErrors();		$this->adminPost->displayErrors();		$this->adminComment->displayErrors();		$this->adminTags->displayErrors();	}	public function viewAccess($disable = false)	{		$result = parent::viewAccess($disable);		$this->adminCategory->tabAccess = $this->tabAccess; 		$this->adminPost->tabAccess = $this->tabAccess;		$this->adminComment->tabAccess = $this->tabAccess; 		$this->adminPost->adminComment = $this->tabAccess;		return $result;	}
	function display()
	{		
					
		// action
		if (Tools::isSubmit('addpl_blog_category') || Tools::isSubmit('submitAddpl_blog_category') || Tools::isSubmit('updatepl_blog_category'))
		{
			$this->adminCategory->displayForm();
		}
		elseif (Tools::isSubmit('addpl_blog_post') || Tools::isSubmit('submitAddpl_blog_post') ||	Tools::isSubmit('updatepl_blog_post'))
		{
			$this->adminPost->displayForm();
		}
		elseif (Tools::isSubmit('addpl_blog_comment') || Tools::isSubmit('submitAddpl_blog_comment') ||	Tools::isSubmit('updatepl_blog_comment'))
		{
			$this->adminComment->displayForm();
		}
		elseif (Tools::isSubmit('addpl_blog_tags') || Tools::isSubmit('submitAddpl_blog_tags') ||	Tools::isSubmit('updatepl_blog_tags'))
		{
			$this->adminTags->displayForm();
		}	
		else
		{
			$this->adminCategory->display();
			$this->clear();
			$this->adminPost->display();
			$this->clear();
			$this->adminComment->display();
			$this->clear();
			$this->adminTags->display();
			$this->clear();
		}
	}
	function clear()
	{
		echo '<div style="height:10px;">&nbsp;</div>';
	}
	function postProcess()
	{
		// access
		$this->adminCategory->tabAccess = $this->tabAccess;
		$this->adminPost->tabAccess = $this->tabAccess;
		$this->adminComment->tabAccess = $this->tabAccess;
		$this->adminTags->tabAccess = $this->tabAccess;
//		// action//
		if (Tools::isSubmit('submitFilterpl_blog_category') ||  Tools::isSubmit('addpl_blog_category') || Tools::isSubmit('submitAddpl_blog_category') || Tools::isSubmit('updatepl_blog_category') || Tools::isSubmit('deletepl_blog_category') || Tools::isSubmit('statuspl_blog_category') || isset($_GET['plposition']))
		{
			$this->adminCategory->postProcess($this->token);
		}
		elseif (Tools::isSubmit('submitFilterpl_blog_post') || Tools::isSubmit('addpl_blog_post') || Tools::isSubmit('submitAddpl_blog_post') ||	Tools::isSubmit('updatepl_blog_post') || Tools::isSubmit('deletepl_blog_post') || Tools::isSubmit('statuspl_blog_post'))
		{
			$this->adminPost->postProcess($this->token);
		}
		elseif (Tools::isSubmit('submitFilterpl_blog_comment') || Tools::isSubmit('addpl_blog_comment') || Tools::isSubmit('submitAddpl_blog_comment') ||	Tools::isSubmit('updatepl_blog_comment') || Tools::isSubmit('deletepl_blog_comment') || Tools::isSubmit('statuspl_blog_comment'))
		{
			$this->adminComment->postProcess($this->token);
		}
		elseif (Tools::isSubmit('submitFilterpl_blog_tags') || Tools::isSubmit('addpl_blog_tags') || Tools::isSubmit('submitAddpl_blog_tags') ||	Tools::isSubmit('updatepl_blog_tags') || Tools::isSubmit('deletepl_blog_tags') || Tools::isSubmit('statuspl_blog_tags'))
		{
			$this->adminTags->postProcess($this->token);
		}
	}
}