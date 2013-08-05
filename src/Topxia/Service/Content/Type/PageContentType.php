<?php
namespace Topxia\Service\Content\Type;

class PageContentType extends ContentType
{
	public function getBasicFields()
	{
		return array('title', 'body', 'picture', 'alias', 'template');
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