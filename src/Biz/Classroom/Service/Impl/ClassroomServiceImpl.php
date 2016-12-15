<?php


namespace Biz\Classroom\Service\Impl;


use Biz\BaseService;
use Biz\Classroom\Dao\ClassroomCourseDao;
use Biz\Classroom\Dao\ClassroomDao;
use Biz\Classroom\Dao\ClassroomMemberDao;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Taxonomy\Service\CategoryService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\UserService;

class ClassroomServiceImpl extends BaseService implements ClassroomService
{
    public function searchMembers($conditions, $orderBy, $start, $limit)
    {
        $conditions = $this->_prepareConditions($conditions);
        return $this->getClassroomMemberDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function findClassroomsByIds(array $ids)
    {
        return ArrayToolkit::index($this->getClassroomDao()->findByIds($ids), 'id');
    }

    public function findActiveCoursesByClassroomId($classroomId)
    {
        $classroomCourses = $this->getClassroomCourseDao()->findActiveCoursesByClassroomId($classroomId);
        $courseIds        = ArrayToolkit::column($classroomCourses, 'courseId');

        if (empty($courseIds)) {
            return array();
        }

        $courses       = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses       = ArrayToolkit::index($courses, 'id');
        $sortedCourses = array();

        foreach ($classroomCourses as $key => $classroomCourse) {
            $sortedCourses[$key]                        = $courses[$classroomCourse['courseId']];
            $sortedCourses[$key]['classroom_course_id'] = $classroomCourse['id'];
        }

        return $sortedCourses;
    }

    public function findMembersByUserIdAndClassroomIds($userId, $classroomIds)
    {
        $members = $this->getClassroomMemberDao()->findByUserIdAndClassroomIds($userId, $classroomIds);
        if (empty($members)) {
            return array();
        }
        return ArrayToolkit::index($members, 'classroomId');
    }


    protected function _prepareConditions($conditions)
    {
        if (isset($conditions['role'])) {
            $conditions['role'] = "%{$conditions['role']}%";
        }

        if (isset($conditions['roles'])) {
            $roles = "";

            foreach ($conditions['roles'] as $role) {
                $roles .= "|".$role;
            }

            $roles = $roles."|";

            foreach ($conditions['roles'] as $key => $role) {
                $conditions['roles'][$key] = "|".$role."|";
            }

            $conditions['roles'][] = $roles;
        }

        if (isset($conditions['nickname'])) {
            $user                 = $this->getUserService()->getUserByNickname($conditions['nickname']);
            $conditions['userId'] = $user ? $user['id'] : -1;
            unset($conditions['nickname']);
        }

        if (isset($conditions['categoryId'])) {
            $childrenIds               = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
            $conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
            unset($conditions['categoryId']);
        }

        return $conditions;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->biz->service('Taxonomy:CategoryService');
    }

    /**
     * @return ClassroomMemberDao
     */
    protected function getClassroomMemberDao()
    {
        return $this->createDao('Classroom:ClassroomMemberDao');
    }

    /**
     * @return ClassroomCourseDao
     */
    protected function getClassroomCourseDao()
    {
        return $this->createDao('Classroom:ClassroomCourseDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    /**
     * @return ClassroomDao
     */
    protected function getClassroomDao()
    {
        return $this->createDao('Classroom:ClassroomDao');
    }
}
