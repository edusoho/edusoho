<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\Course\CourseBaseController;
use Biz\Activity\Service\ActivityService;
use Biz\MultiClass\Service\MultiClassService;
use Symfony\Component\HttpFoundation\Request;

class MultiClassController extends CourseBaseController
{
    public function teachingAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $paginator = new Paginator(
            $request,
            $this->getMultiClassService()->countUserTeachMultiClass($user['id'], []),
            20
        );

        $multiClasses = $this->getMultiClassService()->searchUserTeachMultiClass(
            $user['id'],
            [],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($multiClasses, 'courseId'));
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($courses, 'courseSetId'));

        $currentTime = time();
        foreach ($courses as &$course) {
            $firstLive = $this->getActivityService()->search(['fromCourseId' => $course['id'], 'mediaType' => 'live'], ['startTime' => 'asc'], 0, 1);
            $lastLive = $this->getActivityService()->search(['fromCourseId' => $course['id'], 'mediaType' => 'live'], ['endTime' => 'desc'], 0, 1);
            if (empty($firstLive)) {
                $course['liveStatus'] = 'end';
                continue;
            }

            $course['liveStatus'] = 'live';
            if (current($firstLive)['startTime'] > $currentTime) {
                $course['liveStatus'] = 'not_start';
            }

            if (current($lastLive)['endTime'] < $currentTime) {
                $course['liveStatus'] = 'end';
            }
        }

        return $this->render(
            'my/teaching/multi-classes.html.twig',
            [
                'multiClasses' => $multiClasses,
                'courseSets' => $courseSets,
                'courses' => $courses,
                'paginator' => $paginator,
            ]
        );
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->createService('MultiClass:MultiClassService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }
}
