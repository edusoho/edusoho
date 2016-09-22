<?php
namespace Custom\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\MyTeachingController as BaseMyTeachingController;

class MyTeachingController extends BaseMyTeachingController
{
    public function orgCoursesAction(Request $request, $filter)
    {
        $user = $this->getCurrentUser();
        if (in_array('ROLE_CENTER_ADMIN', $user->getRoles()) and !$user->isSuperAdmin()) {
            $orgParentIds = explode('.', substr($user['orgCode'], 0, strlen($user['orgCode'])-1));
            $orgChildren = $this->getOrgService()->findOrgsByPrefixOrgCode($user['orgCode']);
            $orgChildIds = ArrayToolkit::column($orgChildren, 'id');
            $orgIds = array_unique(array_merge($orgParentIds, $orgChildIds));
            
            $conditions = array();
            $conditions['orgIds'] = $orgIds;
            if ($filter == 'normal' || $filter == 'live') {
                $conditions["parentId"] = 0;
                $conditions["type"]     = $filter;
            }

            if ($filter == 'classroom') {
                $conditions["parentId_GT"] = 0;
            }

            $courseCount = $this->getCourseService()->searchCourseCount($conditions);

            $paginator = new Paginator(
                $request,
                $courseCount,
                10
            );

            $courses = $this->getCourseService()->searchCourses(
                $conditions,
                array('orgId', 'ASC'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );

            $classrooms = array();

            if ($filter == 'classroom') {
                $classrooms = $this->getClassroomService()->findClassroomsByCoursesIds(ArrayToolkit::column($courses, 'id'));
                $classrooms = ArrayToolkit::index($classrooms, 'courseId');

                foreach ($classrooms as $key => $classroom) {
                    $classroomInfo                      = $this->getClassroomService()->getClassroom($classroom['classroomId']);
                    $classrooms[$key]['classroomTitle'] = $classroomInfo['title'];
                }
            }

            $courseSetting = $this->getSettingService()->get('course', array());

            return $this->render('CustomWebBundle:MyTeaching:teaching.html.twig', array(
                'courses'    => $courses,
                'classrooms' => $classrooms,
                'paginator'  => $paginator,
                'filter'     => $filter
            ));

        } else {
            return parent::coursesAction($request, $filter);
        }
    }

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Custom:Testpaper.TestpaperService');
    }

    protected function getOrgService()
    {
        return $this->getServiceKernel()->createService('Org:Org.OrgService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('Custom:User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Custom:Course.CourseService');
    }
}