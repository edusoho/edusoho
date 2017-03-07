<?php   
namespace Topxia\Common;

use Topxia\Service\Common\ServiceKernel;

class ClassroomToolkit
{
    public static function buildMemberDeadline(array $expiryDate)
    {
        $deadline = $expiryDate['expiryValue'];

        if ($expiryDate['expiryMode'] == 'days') {
            $deadline = time() + $expiryDate['expiryValue'] * 24 * 60 * 60;
        }

        if ($expiryDate['expiryMode'] == 'date') {
            if (!is_int($deadline)) {
                $deadline = strtotime($deadline.' 23:59:59');
            }

            if ($deadline < time()) {
                throw new \Exception(self::getKernel()->trans('有效期的设置时间小于当前时间！'));
            }
        }

        return $deadline;
    }

    public static function isClassroomOverDue($classroom)
    {
        if ($classroom['expiryMode'] != 'date') {
            return false;
        }

        if ($classroom['expiryValue'] >= time()) {
            return false;
        }

        return true;
    }

    public static function isCopyCourseOverdue($course) 
    {
        if ($course['parentId'] == 0) {
            return false;
        }

        if ($course['expiryMode'] != 'date') {
            return false;
        }

        if ($course['expiryDay'] >= time()) {
            return false;
        }

        return true;
    }

    public static function hasAdminOrHeadTeacherRole($user, $fields)
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (!empty($fields['courseId'])) {
            $classroom = self::getClassroomService()->getClassroomByCourseId($fields['courseId']);
        }

        if (!empty($fields['classroom'])) {
            $classroom = $fields['classroom'];
        }

        $member = self::getClassroomService()->findMembersByUserIdAndClassroomIds($user['id'], array($classroom['id']));

        if (empty($member)) {
            return false;
        }

        if (array_intersect($member[$classroom['id']]['role'], array('headTeacher'))) {
            return true;
        }
    }

    protected static function getClassroomService()
    {
        return self::getKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected static function getKernel()
    {
        return ServiceKernel::instance();
    }
}