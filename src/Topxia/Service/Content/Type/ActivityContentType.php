<?php
namespace Topxia\Service\Content\Type;

class ActivityContentType extends ContentType
{
	public function getBasicFields()
	{
		return array('title', 'body', 'picture', 'categoryId', 'tagIds');
	}

	public function getExtendedFields()
	{
		return array(
			'field1' => 'startTime',
			'field2' => 'endTime',
			'field3' => 'location'
		);
	}

	public function getAlias()
	{
		return 'activity';
	}

	public function getName()
	{
		return '活动';
	}

}