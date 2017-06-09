<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;

class rewardPointController extends BaseController
{
    public function rewardPointlistAction(Request $request)
    {
        $conditions = $request->query->all();
        if (isset($conditions['keywordType'])) {
            $conditions = $this->manageConditions($conditions);
        }
        $count = $this->getCourseService()->countCourses($conditions);
        $paginator = new Paginator(
            $request,
            $count,
            20
        );

        $courses = $this->getCourseService()->searchCourses(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/reward-point/list.html.twig', array(
            'courses' => $courses,
            'paginator' => $paginator,
        ));
    }

    public function updateRewardPointAction(Request $request)
    {
        $conditions = $request->query->all();
        if (isset($conditions)) {
            $course = $this->getCourseService()->getCourse($conditions['id']);
            if (isset($conditions['taskRewardPoint'])) {
                $course['taskRewardPoint'] = $conditions['taskRewardPoint'];
            }
            if (isset($conditions['rewardPoint'])) {
                $course['rewardPoint'] = $conditions['rewardPoint'];
            }
        }
        $course = $this->getCourseService()->updateCourseMarketing($conditions['id'], $course);

        if ($course) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => $this->getServiceKernel()->trans('请输入大于等于0的整数。'));
        }

        return $this->createJsonResponse($response);
    }

    protected function manageConditions($conditions)
    {
        if (isset($conditions) && $conditions['keywordType'] == 'courseSetId') {
            $conditions['courseSetId'] = $conditions['keyword'];
        }
        if (isset($conditions) && $conditions['keywordType'] == 'courseTitle') {
            $conditions['titleLike'] = $conditions['keyword'];
        }
        if (isset($conditions) && $conditions['keywordType'] == 'courseSetTitle') {
            $courseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($conditions['keyword']);
            $courseSetIds = ArrayToolkit::column($courseSets, 'id');
            $conditions['courseSetIds'] = $courseSetIds;
        }

        return $conditions;
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
