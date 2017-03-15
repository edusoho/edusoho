<?php

namespace AppBundle\Controller\My;

use AppBundle\Controller\BaseController;
use Biz\OpenCourse\Service\OpenCourseService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;

class OpenCourseController extends BaseController
{
    public function teachingAction(Request $request, $filter)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是教师，不能查看此页面! ');
        }

        $conditions = $this->_createSearchConditions($filter);

        $paginator = new Paginator(
            $request,
            $this->getOpenCourseService()->countCourses($conditions),
            10
        );

        $openCourses = $this->getOpenCourseService()->searchCourses(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('my/teaching/open-course.html.twig', array(
            'courses' => $openCourses,
            'paginator' => $paginator,
            'filter' => $filter,
        ));
    }

    private function _createSearchConditions($filter)
    {
        $user = $this->getCurrentUser();

        $conditions = array(
            'type' => $filter,
        );

        if ($user->isAdmin()) {
            $conditions['userId'] = $user['id'];
        } else {
            $conditions['courseIds'] = array(-1);
            $members = $this->getOpenCourseService()->searchMembers(
                array('userId' => $user['id'], 'role' => 'teacher'),
                array('createdTime' => 'ASC'),
                0,
                999
            );

            if ($members) {
                foreach ($members as $key => $member) {
                    $conditions['courseIds'][] = $member['courseId'];
                }
            }
        }

        return $conditions;
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->getBiz()->service('OpenCourse:OpenCourseService');
    }
}
