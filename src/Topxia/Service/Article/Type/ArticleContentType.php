<?php
namespace Topxia\Service\Article\Type;

class ArticleContentType extends ArticleType
{
	public function getBasicFields()
	{
		return array('title', 'body', 'picture', 'categoryId', 'tagIds');
	}

	public function getAlias()
	{
		return 'article';
	}

	public function getName()
	{
		return '文章';
	}

}