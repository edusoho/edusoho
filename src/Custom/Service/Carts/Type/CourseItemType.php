<?php
namespace Custom\Service\Carts\Type;

use Topxia\Common\ArrayToolkit;

class CourseItemType extends AbstractCartItemType
{

    public function getItemsAndExtra($courseIds, $extraParams)
    {
        if(empty($courseIds)) {
            return array(
                'items' => array(), 
                'extra' => array()
            );
        }

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $teacherIds = $this->mergeUserIds($courses, array());
        foreach ($courses as $key => $course) {
            if($course['type'] == 'package') {
                list($raletions, $subcourses) = $this->getCourseService()->findSubcoursesByCourseId($course['id']);
                $course['subcourses'] = $subcourses;
                $teacherIds = $this->mergeUserIds($subcourses, $teacherIds);
                $courses[$key] = $course;
            }
        }

        $users = $this->getUserService()->findUsersByIds(array_unique($teacherIds));
        return array(
            'items' => $courses, 
            'extra' => array(
                'users' => $users
            )
        );
    }

    private function mergeUserIds($courses, $userIds)
    {
        foreach ($courses as $course) {
            $userIds = array_merge($course['teacherIds'], $userIds);
        }
        return $userIds;
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}