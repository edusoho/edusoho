<?php
namespace Topxia\Service\Content\Type;

use Topxia\Service\Common\ServiceKernel;

class PageContentType extends ContentType
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
		return $this->getKernel()->trans('页面');
	}

	protected function getKernel()
	{
    return  ServiceKernel::instance();
  }

}