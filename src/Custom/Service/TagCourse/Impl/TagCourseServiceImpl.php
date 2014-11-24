<?php
namespace Custom\Service\TagCourse\Impl;

use Topxia\Service\Common\BaseService as BaseService;
use Custom\Service\TagCourse\TagCourseService;


class TagCourseServiceImpl extends BaseService implements TagCourseService
{
	
	public function getCourseStudentCountByTagIdAndCourseStatus($tagId,$status)
	{
		return $this->getCustomTagCourseDao()->getCourseStudentCountByTagIdAndCourseStatus($tagId,$status);
	}

	private function getCustomTagCourseDao()
    	{
       		return $this->createDao('Custom:TagCourse.TagCourseDao');
    	}

}
