<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\OpenCourse\Service\OpenCourseService;
use Symfony\Component\HttpFoundation\Request;

class OpenCourseController extends BaseController
{
    public function teachingAction(Request $request, $filter)
    {
        $user = $this->getCurrentUser();

        $tab = $request->query->get('tab', 'publish');
        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是教师，不能查看此页面! ');
        }

        $conditions = $this->_createSearchConditions($filter, $tab);

        $paginator = new Paginator(
            $request,
            $this->getOpenCourseService()->countCourses($conditions),
            10
        );

        $openCourses = $this->getOpenCourseService()->searchCourses(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('my/teaching/open-course.html.twig', [
            'courses' => $openCourses,
            'paginator' => $paginator,
            'filter' => $filter,
            'tab' => $tab,
        ]);
    }

    private function _createSearchConditions($filter, $tab)
    {
        $user = $this->getCurrentUser();

        $status = ['publish' => 'published', 'unPublish' => 'draft', 'closed' => 'closed'];
        $conditions = [
            'type' => $filter,
            'status' => $status[$tab],
        ];

        if ($user->isAdmin()) {
            $conditions['userId'] = $user['id'];
            $openCourses = $this->getOpenCourseService()->searchCourses($conditions, [], 0, PHP_INT_MAX, ['id']);
            $conditions['courseIds'] = array_column($openCourses, 'id');
            unset($conditions['userId']);
        }
        $members = $this->getOpenCourseService()->searchMembers(
            ['userId' => $user['id'], 'role' => 'teacher'],
            [],
            0,
            PHP_INT_MAX,
            ['courseId']
        );
        $conditions['courseIds'] = $conditions['courseIds'] ?? [];
        $conditions['courseIds'] = array_merge($conditions['courseIds'], array_column($members, 'courseId')) ?: [-1];

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
