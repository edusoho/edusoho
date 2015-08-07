<?php
namespace Custom\WebBundle\Controller\Part;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;

use Topxia\WebBundle\Controller\Part\CourseController as BaseCourseController;

class CourseController extends BaseCourseController
{
    public function headerAction($course, $member)
    {
        if (($course['discountId'] > 0)&&($this->isPluginInstalled("Discount"))){
            $course['discountObj'] = $this->getDiscountService()->getDiscount($course['discountId']);
        }

        $hasFavorited = $this->getCourseService()->hasFavoritedCourse($course['id']);


        $user = $this->getCurrentUser();
        $userVipStatus = $courseVip = null;
        if ($this->isPluginInstalled('Vip') && $this->setting('vip.enabled')) {
            $courseVip = $course['vipLevelId'] > 0 ? $this->getLevelService()->getLevel($course['vipLevelId']) : null;
            if ($courseVip) {
                $userVipStatus = $this->getVipService()->checkUserInMemberLevel($user['id'], $courseVip['id']);
            }
        }

        $nextLearnLesson = $member ? $this->getCourseService()->getUserNextLearnLesson($user['id'], $course['id']) : null;
        $learnProgress = $member ? $this->calculateUserLearnProgress($course, $member) : null;

        $previewLesson = $this->getCourseService()->searchLessons(array('courseId' => $course['id'], 'type' => 'video', 'free' => 1), array('seq', 'ASC'), 0, 1);

        return $this->render('CustomWebBundle:Course:Part/normal-header.html.twig', array(
            'course' => $course,
            'member' => $member,
            'hasFavorited' => $hasFavorited,
            'courseVip' => $courseVip,
            'userVipStatus' => $userVipStatus,
            'nextLearnLesson' => $nextLearnLesson,
            'learnProgress' => $learnProgress,
            'previewLesson' => empty($previewLesson) ? null : $previewLesson[0],
        ));
    }

    public function otherPeriodsAction($course){
        $course = $this->getCourse($course);
        $otherPeriods = $this->getCourseService()->findOtherPeriods($course['id']);
        
        return $this->render('CustomWebBundle:Course:Part/normal-sidebar-other-periods.html.twig', array(
            'course' => $course,
            'otherPeriods' => $otherPeriods,
        ));
    }

}

