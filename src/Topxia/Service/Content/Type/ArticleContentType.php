<?php
namespace Topxia\Service\Content\Type;

class ArticleContentType extends ContentType
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
		return $this->getKernel()->trans('文章');
	}

}