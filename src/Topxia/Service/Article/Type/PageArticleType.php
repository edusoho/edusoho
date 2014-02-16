<?php
namespace Topxia\Service\Article\Type;

class PageArticleType extends ArticleType
{
	public function getBasicFields()
	{
		return array('title', 'body', 'picture', 'alias', 'template','editor');
	}

	public function getAlias()
	{
		return 'page';
	}

	public function getName()
	{
		return '页面';
	}

}