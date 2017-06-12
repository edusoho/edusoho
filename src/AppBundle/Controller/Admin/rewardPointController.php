<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;

class rewardPointController extends BaseController
{
    public function indexAction(Request $request)
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

    public function updateAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();
            if (isset($fields)) {
                $course = $this->getCourseService()->getCourse($fields['id']);
                if (isset($fields['taskRewardPoint'])) {
                    if (!preg_match('/^\+?[0-9][0-9]*$/', $fields['taskRewardPoint'])) {
                        return $this->createJsonResponse(array('success' => false, 'message' => '请输入非负整数'));
                    }
                    $course['taskRewardPoint'] = $fields['taskRewardPoint'];
                }
                if (isset($fields['rewardPoint'])) {
                    if (!preg_match('/^\+?[0-9][0-9]*$/', $fields['rewardPoint'])) {
                        return $this->createJsonResponse(array('success' => false, 'message' => '请输入非负整数'));
                    }
                    $course['rewardPoint'] = $fields['rewardPoint'];
                }
            }
            $this->getCourseService()->updateCourseMarketing($fields['id'], $course);

            return $this->createJsonResponse(array('success' => true));
        }
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
