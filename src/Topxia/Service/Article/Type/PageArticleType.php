<?php
namespace Topxia\Service\Article\Type;

class PageArticleType extends ArticleType
{
	public function getBasicFields()
	{
		return array('title', 'categoryId','body', 'promoted','featured','sticky','tagIds','source','sourceUrl','picture', 'isFirstThumbnail','alias', 'template','editor');
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