<?php
class PlTools
{
	function __construct()
	{
	}
	
	public function getCurrentURL()
	{
		$url = 'http';
		
		if ($_SERVER["HTTPS"] == "on") 
			$url .= "s";
			
		$url .= "://";
		if ($_SERVER["SERVER_PORT"] != "80")
			$url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		else
			$url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		
		if (Tools::getValue('n'))
			$url = $url.(!strstr($url, '?') ? '?' : '&amp;').'n='.(int)(Tools::getValue('n'));
		return $url;
	}
	
	public function getFriendlyUrlCategory($id_pl_blog_category)
	{
		return Db::getInstance()->getValue("
			SELECT link_rewrite FROM
			"._DB_PREFIX_."pl_blog_category_lang
			WHERE id_pl_blog_category = ".$id_pl_blog_category."
		");
	}
	
	public function getPaginationLinkBlog()
	{
		if (Configuration::get("PS_REWRITING_SETTINGS") == 0)
		{
			//return $this->getCurrentURL();	
		
			global $link;
            if (Tools::getValue('plidc'))
			{
				//$url = $link->getPageLinkOld('blog/'.Tools::getValue('plcn').'.'.Tools::getValue('plidc').'.html');
				$url = $link->getPageLinkOld('').'modules/plblog/frontent/list-post.php?plcn='.Tools::getValue('plcn').'&plidc='.Tools::getValue('plidc');
				if (Tools::getValue('n'))
					$url = $url.(!strstr($url, '?') ? '?' : '&amp;').'n='.(int)(Tools::getValue('n'));
				return $url;
			}
			elseif (Tools::getValue('plidt'))
			{	
				//$url = $link->getPageLinkOld('').'blog/'.Tools::getValue('plidt').'.'.Tools::getValue('pltn').'.html';
				$url = $link->getPageLinkOld('').'modules/plblog/frontent/list-tags.php?plidt='.Tools::getValue('plidt').'&pltn='.Tools::getValue('pltn');
				if (Tools::getValue('n'))
					$url = $url.(!strstr($url, '?') ? '?' : '&amp;').'n='.(int)(Tools::getValue('n'));
				return $url;
			}
			else
			{
				$url = $link->getPageLinkOld('').'modules/plblog/frontent/all-post.php';;
				if (Tools::getValue('n'))
					$url = $url.(!strstr($url, '?') ? '?' : '&amp;').'n='.(int)(Tools::getValue('n'));
				return $url;				
			}
		}
		else
		{
			global $link;
            if (Tools::getValue('plidc'))
			{
				$url = $link->getPageLinkOld('blog/'.Tools::getValue('plidc').'_'.$this->getFriendlyUrlCategory(Tools::getValue('plidc')).'.html');
				if (Tools::getValue('n'))
					$url = $url.(!strstr($url, '?') ? '?' : '&amp;').'n='.(int)(Tools::getValue('n'));
				return $url;
			}
			elseif (Tools::getValue('plidt'))
			{
				$url = $link->getPageLinkOld('').'blog/'.Tools::getValue('plidt').'.'.Tools::getValue('pltn').'.html';
				if (Tools::getValue('n'))
					$url = $url.(!strstr($url, '?') ? '?' : '&amp;').'n='.(int)(Tools::getValue('n'));
				return $url;
			}
			else
			{
				$url = $link->getPageLinkOld('').'blog/all-post.html';
				if (Tools::getValue('n'))
					$url = $url.(!strstr($url, '?') ? '?' : '&amp;').'n='.(int)(Tools::getValue('n'));
				return $url;				
			}
		}
	}
	
	public function getCategoryNameRewrite($id_pl_blog_category)
	{
		global $cookie;
		return Db::getInstance()->getValue('
			SELECT link_rewrite FROM '._DB_PREFIX_.'pl_blog_category_lang
			WHERE id_lang = '.$cookie->id_lang.' AND id_pl_blog_category = '.$id_pl_blog_category.'
		');
	}
	
	public function getPostLink($id_pl_blog_post, $link_rewrite, $id_pl_blog_category)
	{
		global $link;
		if (Configuration::get('PS_REWRITING_SETTINGS') == 1)
		{
			$_link = "blog/".$this->getCategoryNameRewrite($id_pl_blog_category).'/'.$id_pl_blog_post.'-'.$link_rewrite.".html";
			$l = $link->getPageLinkOld($_link);
		}
		else
		{
			$_link = 'modules/plblog/frontent/details.php?plcn='.$this->getCategoryNameRewrite($id_pl_blog_category).'&plidp='.$id_pl_blog_post.'&plpn='.$link_rewrite;
			$l = $link->getPageLinkOld($_link);
		}
		
		return $l;
	}
	
	public function getCategoryLink($id_pl_blog_category, $link_rewrite)
	{
		global $link;
		if (Configuration::get("PS_REWRITING_SETTINGS") == 0)
		{
			$url = $link->getPageLinkOld('').'modules/plblog/frontent/list-post.php?plidc='.$id_pl_blog_category.'&plcn='.$link_rewrite;
			return $url;
		}
		else
		{
			$url = $link->getPageLinkOld('blog/'.$id_pl_blog_category.'_'.$link_rewrite.'.html');
			return $url;
		}
	}
	
	public function getTagLink($id_pl_blog_tags, $tags_name)
	{
		global $link;
		if (Configuration::get("PS_REWRITING_SETTINGS") == 0)
		{
			$url = $link->getPageLinkOld('').'modules/plblog/frontent/list-tags.php?pltn='.$this->stringOnLink($tags_name).'&plidt='.$id_pl_blog_tags;
			return $url;
		}
		else
		{
			$url = $link->getPageLinkOld('blog/tag/'.$this->stringOnLink($tags_name).'_'.$id_pl_blog_tags.'.html');
			return $url;
		}	
	}
	
	public function stringOnLink($str)
	{
		$str = preg_replace("[^a-z^A-Z^0-9^ ^-]", "", $str);
		$str = strtolower($str);
		$str = preg_replace('/\s+/', " ", $str);
		$str = trim($str);
		$str = str_replace(" ", "-", $str);
		return $str;
	}	
	
	public function getValue($key)
	{
		return Tools::getValue($key);
	}
	
	public 	function substr($str, $start = 0, $end = null)
	{
		if ($end == null)
			$end = strlen($str);
		$str = substr(strip_tags($str), $start, $end);
		return $str;
	}
	
	function getCountComment($id_pl_blog_post = null)
	{
		$sql = 'SELECT * FROM '._DB_PREFIX_.'pl_blog_comment a
		WHERE a.comment_status=2 AND a.id_pl_blog_post='.$id_pl_blog_post;
		
		return count(Db::getInstance()->ExecuteS($sql));
	}
}
