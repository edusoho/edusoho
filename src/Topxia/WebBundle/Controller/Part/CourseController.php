<?php
namespace Topxia\WebBundle\Controller\Part;

use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;

class CourseController extends BaseController
{
    public function headerAction($course, $member)
    {
        if (($course['discountId'] > 0) && ($this->isPluginInstalled("Discount"))) {
            $course['discountObj'] = $this->getDiscountService()->getDiscount($course['discountId']);
        }

        $hasFavorited = $this->getCourseService()->hasFavoritedCourse($course['id']);

        $user          = $this->getCurrentUser();
        $userVipStatus = $courseVip = null;

        if ($this->isPluginInstalled('Vip') && $this->setting('vip.enabled')) {
            $courseVip = $course['vipLevelId'] > 0 ? $this->getLevelService()->getLevel($course['vipLevelId']) : null;

            if ($courseVip) {
                $userVipStatus = $this->getVipService()->checkUserInMemberLevel($user['id'], $courseVip['id']);
            }
        }

        $nextLearnLesson = $member ? $this->getCourseService()->getUserNextLearnLesson($user['id'], $course['id']) : null;
        $learnProgress   = $member ? $this->calculateUserLearnProgress($course, $member) : null;

        $previewLesson = $this->getCourseService()->searchLessons(array('courseId' => $course['id'], 'type' => 'video', 'free' => 1), array('seq', 'ASC'), 0, 1);

        $breadcrumbs = $this->getCategoryService()->findCategoryBreadcrumbs($course['categoryId']);

        return $this->render('TopxiaWebBundle:Course:Part/normal-header.html.twig', array(
            'course'          => $course,
            'member'          => $member,
            'hasFavorited'    => $hasFavorited,
            'courseVip'       => $courseVip,
            'userVipStatus'   => $userVipStatus,
            'nextLearnLesson' => $nextLearnLesson,
            'learnProgress'   => $learnProgress,
            'previewLesson'   => empty($previewLesson) ? null : $previewLesson[0],
            'breadcrumbs'     => $breadcrumbs
        ));
    }

    public function opeCourseHeaderAction($course)
    {
        $breadcrumbs = $this->getCategoryService()->findCategoryBreadcrumbs($course['categoryId']);

        return $this->render('TopxiaWebBundle:Course:Part/open-course-header.html.twig', array(
            'course'      => $course,
            'breadcrumbs' => $breadcrumbs
        ));
    }

    public function teachersAction($course)
    {
        $course         = $this->getCourse($course);
        $teachersNoSort = $this->getUserService()->findUsersByIds($course['teacherIds']);

        $teachers = array();

        foreach ($course['teacherIds'] as $key => $teacherId) {
            $teachers[$teacherId] = $teachersNoSort[$teacherId];
        }

        return $this->render('TopxiaWebBundle:Course:Part/normal-sidebar-teachers.html.twig', array(
            'course'   => $course,
            'teachers' => $teachers
        ));
    }

    public function studentsAction($course)
    {
        $course   = $this->getCourse($course);
        $members  = $this->getCourseService()->findCourseStudents($course['id'], 0, 15);
        $students = $this->getUserService()->findUsersByIds(ArrayToolkit::column($members, 'userId'));

        return $this->render('TopxiaWebBundle:Course:Part/normal-sidebar-students.html.twig', array(
            'course'   => $course,
            'students' => $students,
            'members'  => $members
        ));
    }

    public function belongClassroomsAction($course)
    {
        $classrooms = $this->getClassroomService()->findClassroomsByCourseId($course['id']);

        foreach ($classrooms as $key => $classroom) {
            if ($classroom["status"] != "published") {
                unset($classrooms[$key]);
            }
        }

        return $this->render('TopxiaWebBundle:Course:Part/normal-sidebar-belong-classrooms.html.twig', array(
            'course'     => $course,
            'classrooms' => $classrooms
        ));
    }

    public function classroomInfoAction($courseId)
    {
        $classroom = $this->getClassroomService()->findClassroomByCourseId($courseId);
        $classroom = $this->getClassroomService()->getClassroom($classroom["classroomId"]);
        return $this->render('TopxiaWebBundle:Course/Part:normal-header-classroom-info.html.twig', array(
            'classroom' => $classroom
        ));
    }

    public function recommendClassroomsAction($course)
    {
        $classrooms               = array();
        $classrooms               = array_merge($classrooms, array_values($this->getClassroomService()->findClassroomsByCourseId($course['id'])));
        $belongCourseClassroomIds = ArrayToolkit::column($classrooms, 'id');
        $conditions               = array(
            'categoryIds' => array($course['categoryId']),
            'showable'    => 1
        );

        if ($course['categoryId'] > 0) {
            $classrooms = array_merge($classrooms, $this->getClassroomService()->searchClassrooms($conditions, array('recommendedSeq', 'ASC'), 0, 8));
        }

        $conditions = array(
            'recommended' => 1,
            'showable'    => 1,
            'status'      => 'published'
        );

        $classrooms = array_merge($classrooms, $this->getClassroomService()->searchClassrooms($conditions, array('recommendedSeq', 'ASC'), 0, 11));

        $recommends = array();

        foreach ($classrooms as $key => $classroom) {
            if (isset($recommends[$classroom['id']])) {
                continue;
            }

            if (count($recommends) >= 8) {
                break;
            }

            if (in_array($classroom['id'], $belongCourseClassroomIds)) {
                $classroom['belogCourse'] = true;
            }

            if ($classroom['status'] == 'published') {
                $recommends[$classroom['id']] = $classroom;
            }
        }

        return $this->render('TopxiaWebBundle:Course/Part:normal-header-recommend-classrooms.html.twig', array(
            'classrooms' => $recommends
        ));
    }

    protected function getCourse($course)
    {
        if (is_array($course)) {
            return $course;
        }

        $courseId = (int) $course;
        return $this->getCourseService()->getCourse($courseId);
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getDiscountService()
    {
        return $this->getServiceKernel()->createService('Discount:Discount.DiscountService');
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function calculateUserLearnProgress($course, $member)
    {
        if ($course['lessonNum'] == 0) {
            return array('percent' => '0%', 'number' => 0, 'total' => 0);
        }

        $percent = intval($member['learnedNum'] / $course['lessonNum'] * 100).'%';

        return array(
            'percent' => $percent,
            'number'  => $member['learnedNum'],
            'total'   => $course['lessonNum']
        );
    }
}
