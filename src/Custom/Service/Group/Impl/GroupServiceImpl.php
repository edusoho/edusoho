<?php
namespace Custom\Service\Group\Impl;

use Topxia\Service\Group\Impl\GroupServiceImpl as BaseService;
use Custom\Service\Group\GroupService;

class GroupServiceImpl extends BaseService implements GroupService
{
	
	public function recommendGroup($id, $number)
	{
		if (!is_numeric($number)) {
			throw $this->createAccessDeniedException('推荐小组序号只能为数字！');
		}

		$course = $this->getCourseDao()->updateCourse($id, array(
			'recommended' => 1,
			'recommendedSeq' => (int)$number,
			'recommendedTime' => time(),
		));

		$this->getLogService()->info('course', 'recommend', "推荐课程《{$course['title']}》(#{$course['id']}),序号为{$number}");

		return $course;
	}

	
}