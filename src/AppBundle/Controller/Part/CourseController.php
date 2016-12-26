<?php
namespace AppBundle\Controller\Part;

use AppBundle\Controller\BaseController;
use Topxia\Common\ArrayToolkit;


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
            if(!empty($member['classroomId'])) {
                $classroom = $this->getClassroomService()->getClassroom($member['classroomId']);
                $courseVip = empty($classroom['vipLevelId']) ? null : $this->getLevelService()->getLevel($classroom['vipLevelId']);
            }

            if ($courseVip) {
                $userVipStatus = $this->getVipService()->checkUserInMemberLevel($user['id'], $courseVip['id']);
            }
        }

        $nextLearnLesson = $member ? $this->getCourseService()->getUserNextLearnLesson($user['id'], $course['id']) : null;
        $learnProgress   = $member ? $this->calculateUserLearnProgress($course, $member) : null;

        $previewLesson = $this->getCourseService()->searchLessons(array('courseId' => $course['id'], 'type' => 'video', 'free' => 1), array('seq', 'ASC'), 0, 1);

        $breadcrumbs = $this->getCategoryService()->findCategoryBreadcrumbs($course['categoryId']);

        return $this->render('course/part/normal-header.html.twig', array(
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

        return $this->render('course/part/open-course-header.html.twig', array(
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

        return $this->render('course/part/normal-sidebar-teachers.html.twig', array(
            'course'   => $course,
            'teachers' => $teachers
        ));
    }

    public function studentsAction($course)
    {
        $course   = $this->getCourse($course);
        $members  = $this->getCourseMemberService()->findCourseStudents($course['id'], 0, 15);
        $students = $this->getUserService()->findUsersByIds(ArrayToolkit::column($members, 'userId'));

        return $this->render('course/part/normal-sidebar-students.html.twig', array(
            'course'   => $course,
            'students' => $students,
            'members'  => $members
        ));
    }

    public function belongClassroomsAction($course)
    {
        $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);

        return $this->render('course/part/normal-sidebar-belong-classrooms.html.twig', array(
            'course'     => $course,
            'classrooms' => !empty($classroom) && $classroom["status"] == "published" ? array($classroom) : array()
        ));
    }

    public function classroomInfoAction($courseId)
    {
        $classroom = $this->getClassroomService()->getClassroomByCourseId($courseId);
        $classroom = $this->getClassroomService()->getClassroom($classroom["classroomId"]);
        return $this->render('course/part/normal-header-classroom-info.html.twig', array(
            'classroom' => $classroom
        ));
    }

    public function recommendClassroomsAction($course)
    {
        $classroom               = $this->getClassroomService()->getClassroomByCourseId($course['id']);
        $classrooms = !empty($classroom) ? array($classroom) : array();
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

        return $this->render('course/part/normal-header-recommend-classrooms.html.twig', array(
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
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getDiscountService()
    {
        return $this->getBiz()->service('Discount:Discount.DiscountService');
    }

    protected function getLevelService()
    {
        return $this->getBiz()->service('Vip:Vip.LevelService');
    }

    protected function getVipService()
    {
        return $this->getBiz()->service('Vip:Vip.VipService');
    }

    protected function getCategoryService()
    {
        return $this->getBiz()->service('Taxonomy:CategoryService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
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
