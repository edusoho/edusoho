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
        list($teacherIds, $temp) = $this->mergeUserIdsAndCountTotalPrice($courses, array());
        foreach ($courses as $key => $course) {
            if($course['type'] == 'package') {
                list($raletions, $subcourses) = $this->getCourseService()->findSubcoursesByCourseId($course['id']);
                list($teacherIds, $totalPrice) = $this->mergeUserIdsAndCountTotalPrice($subcourses, $teacherIds);
                $course['subcourses'] = $subcourses;
                $course['costPrice'] = $totalPrice;
                $courses[$key] = $course;
            }
        }

        $teacherIds = array_values(array_unique($teacherIds));
        $users = $this->getUserService()->findUsersByIds($teacherIds);
        return array(
            'items' => $courses, 
            'extra' => array(
                'users' => $users
            )
        );
    }

    private function mergeUserIdsAndCountTotalPrice($courses, $userIds)
    {
        $totalPrice = 0;
        foreach ($courses as $course) {
            $userIds = array_merge($course['teacherIds'], $userIds);
            $totalPrice += floatval($course['price']);
        }
        return array($userIds, $totalPrice);
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}