<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class TeacherController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = array(
            'roles'  => 'ROLE_TEACHER',
            'locked' => 0
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchUserCount($conditions),
            20
        );
        $conditions['promoted'] = 1;
        $promotedCount          = $this->getUserService()->searchUserCount($conditions);
        $currentPage            = $request->query->get('page') ? $request->query->get('page') : 1;
        $promotedPage           = intval($promotedCount / 20);
        $promotedLeft           = $promotedCount % 20;

        if ($currentPage <= $promotedPage) {
            $teachers = $this->getUserService()->searchUsers(
                $conditions,
                array('promotedSeq', 'ASC'),
                ($currentPage - 1) * 20,
                20
            );
        } elseif (($promotedPage + 1) == $currentPage) {
            $teachers = $this->getUserService()->searchUsers(
                $conditions,
                array('promotedSeq', 'ASC'),
                ($currentPage - 1) * 20,
                20
            );
            $conditions['promoted'] = 0;
            $teachersTemp           = $this->getUserService()->searchUsers(
                $conditions,
                array('createdTime', 'DESC'),
                0,
                20 - $promotedLeft
            );
            $teachers = array_merge($teachers, $teachersTemp);
        } else {
            $conditions['promoted'] = 0;
            $teachers               = $this->getUserService()->searchUsers(
                $conditions,
                array('createdTime', 'DESC'),
                (20 - $promotedLeft) + ($currentPage - $promotedPage - 2) * 20,
                20
            );
        }

        $user         = $this->getCurrentUser();
        $teacherIds   = ArrayToolkit::column($teachers, 'id');
        $profiles     = $this->getUserService()->findUserProfilesByIds($teacherIds);
        $myFollowings = $this->getUserService()->filterFollowingIds($user['id'], $teacherIds);
        return $this->render('TopxiaWebBundle:Teacher:index.html.twig', array(
            'teachers'     => $teachers,
            'profiles'     => $profiles,
            'paginator'    => $paginator,
            'Myfollowings' => $myFollowings
        ));
    }

    public function searchAction($request, $keyword)
    {
        $conditions = array(
            'roles'  => 'ROLE_TEACHER',
            'locked' => 0
        );

        if (!empty($keyword)) {
            $conditions['nickname'] = $keyword;
        }

        $teachers = $this->getUserService()->searchUsers($conditions, array(
            'nickname',
            'ASC'
        ), 0, 1000);

        return $this->createJsonResponse($teachers);
    }
}
