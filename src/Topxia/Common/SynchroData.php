<?php
namespace Topxia\Common;

use Topxia\Service\Common\ServiceKernel;

class SynchroData
{
	public static function synchroCourse($parentId)
    {
       if (empty($parentId)) {
			throw new \RuntimeException("同步课程数据参数缺少");
		}

		$courseService = ServiceKernel::instance()->createService('Course.CourseService');

		$fields = $courseService->getCourse($parentId);
		unset($fields['id'],$fields['parentId']);
		$courseService->updateCourseByParentIdAndFields($parentId,$fields);
    }
}