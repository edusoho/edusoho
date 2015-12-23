<?php
namespace Mooc\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Mooc\Service\Course\CourseScoreService;

class CourseScoreServiceImpl extends BaseService implements CourseScoreService
{
    public function getUserScoreByUserIdAndCourseId($userId, $courseId)
    {
        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw $this->createNotFoundException("用户不存在!");
        }

        return $this->getCourseScoreDao()->getUserScoreByUserIdAndCourseId($user['id'], $courseId);
    }

    public function addUserCourseScore($fields)
    {
        $this->checkCourseAndUser($fields['courseId'], $fields['userId']);

        $fields = $this->filterFields($fields);

        $fields['createdTime'] = time();
        return $this->getCourseScoreDao()->addUserCourseScore($fields);
    }

    public function findAllMemberScore($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createServiceException('课程不存在,无法获取成员成绩！');
        }

        return $this->getCourseScoreDao()->findAllMemberScore($courseId);
    }

    public function findUserScoreByIdsAndCourseId($userIds, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createServiceException('课程不存在,无法获取成员成绩！');
        }

        return $this->getCourseScoreDao()->findUserScoreByIdsAndCourseId($userIds, $courseId);
    }

    public function getCoursePassStudentCount($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createServiceException('课程不存在！');
        }

        $courseScoreSetting = $this->getScoreSettingByCourseId($courseId);

        $conditions = array(
            'courseId'      => $courseId,
            'standardScore' => $courseScoreSetting['standardScore']
        );

        return $this->getCourseScoreDao()->searchMemberScoreCount($conditions);
    }

    public function searchMemberScoreCount($conditions)
    {
        return $this->getCourseScoreDao()->searchMemberScoreCount($conditions);
    }

    public function searchMemberScore($conditions, $orderBy, $start, $limit)
    {
        return $this->getCourseScoreDao()->searchMemberScore($conditions, $orderBy, $start, $limit);
    }

    public function findUsersScoreBySqlJoinUser($fields)
    {
        return $this->getCourseScoreDao()->findUsersScoreBySqlJoinUser($fields);
    }

    public function updateUserCourseScore($id, $fields)
    {
        $userCourseScore         = $this->getCourseScoreDao()->getUserCourseScore($id);
        $fields['courseScoreId'] = $userCourseScore['id'];
        $fields['courseId']      = $userCourseScore['courseId'];
        $fields                  = $this->filterFields($fields);

        return $this->getCourseScoreDao()->updateUserCourseScore($id, $fields);
    }

    /**
     * 设置课程评分
     *
     */
    public function addScoreSetting($scoreSetting)
    {
        if (!isset($scoreSetting['courseId'])) {
            throw $this->createServiceException('课程不存在，无法设置课程评分！');
        }

        if (!isset($scoreSetting['expectPublishTime'])) {
            throw $this->createServiceException('成绩发布预告时间，无法更新设置课程评分！');
        }

        $course = $this->getcourseService()->getCourse($scoreSetting['courseId']);

        if (empty($course)) {
            throw $this->createServiceException('课程不存在，无法设置课程评分！');
        }

        $scoreSetting['expectPublishTime'] = strtotime($scoreSetting['expectPublishTime']);
        $scoreSetting['createdTime']       = time();
        $scoreSetting                      = $this->getCourseScoreSettingDao()->addScoreSetting($scoreSetting);
        $this->dispatchEvent("scoreSetting.add", $scoreSetting);
        return $scoreSetting;
    }

    public function updateScoreSetting($courseId, $fields)
    {
        $course = $this->getcourseService()->getCourse($courseId);

        $scoreSetting = $this->getScoreSettingByCourseId($courseId);

        if (empty($course) || empty($scoreSetting)) {
            throw $this->createServiceException('课程不存在，无法更新设置课程评分！');
        }

        if (!isset($scoreSetting['expectPublishTime'])) {
            throw $this->createServiceException('成绩发布预告时间，无法更新设置课程评分！');
        }

        $fields['expectPublishTime'] = strtotime($fields['expectPublishTime']);
        $scoreSetting                = $this->getCourseScoreSettingDao()->updateScoreSetting($courseId, $fields);
        $this->dispatchEvent("scoreSetting.update", $scoreSetting);

        return $scoreSetting;
    }

    public function getScoreSettingByCourseId($courseId)
    {
        return $this->getCourseScoreSettingDao()->getScoreSettingByCourseId($courseId);
    }

    public function deleteCourseScoreByCourseId($courseId)
    {
        $this->getCourseScoreSettingDao()->deleteSettingByCourseId($courseId);
        $this->getCourseScoreDao()->deleteScoresByCourseId($courseId);
    }

    private function checkCourseAndUser($courseId, $userId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException("课程不存在,不能添加学员成绩!");
        }

        $user = $this->getUserService()->getUser($userId);

        if (empty($user)) {
            throw $this->createNotFoundException("用户不存在!");
        }

        $member = $this->getCourseService()->getCourseMember($courseId, $userId);

        if (empty($member)) {
            throw $this->createNotFoundException("用户不是课程学员!");
        }
    }

    private function filterFields($fields)
    {
        $userCourseScore = array();

        if (!empty($fields['courseScoreId'])) {
            $userCourseScore = $this->getCourseScoreDao()->getUserCourseScore($fields['courseScoreId']);
        }

        $courseScoreSetting = $this->getScoreSettingByCourseId($fields['courseId']);
        $fields             = ArrayToolkit::filter($fields, array(
            'courseId'         => 0,
            'userId'           => 0,
            'totalScore'       => 0.0,
            'examScore'        => 0.0,
            'homeworkScore'    => 0.0,
            'otherScore'       => 0.0,
            'importOtherScore' => 0.0
        ));

        $fields['totalScore'] = 0.0;

        if (isset($fields['importOtherScore']) && !empty($fields['importOtherScore'])) {
            $fields['otherScore'] = $fields['importOtherScore'] * ($courseScoreSetting['otherWeight'] / 100);
        }

        if (isset($fields['examScore']) && !empty($fields['examScore'])) {
            $fields['totalScore'] += $fields['examScore'];
        } else {
            $examScore = $userCourseScore ? $userCourseScore['examScore'] : 0;
            $fields['totalScore'] += $examScore;
        }

        if (isset($fields['homeworkScore']) && !empty($fields['homeworkScore'])) {
            $fields['totalScore'] += $fields['homeworkScore'];
        } else {
            $homeworkScore = $userCourseScore ? $userCourseScore['homeworkScore'] : 0;
            $fields['totalScore'] += $homeworkScore;
        }

        if (isset($fields['otherScore']) && !empty($fields['otherScore'])) {
            $fields['totalScore'] += $fields['otherScore'];
        } else {
            $otherScore = $userCourseScore ? $userCourseScore['otherScore'] : 0;
            $fields['totalScore'] += $otherScore;
        }

        return $fields;
    }

    public function findScoreSettingsByCourseIds($courseIds)
    {
        return $this->getCourseScoreSettingDao()->findScoreSettingsByCourseIds($courseIds);
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }

    protected function getCourseScoreDao()
    {
        return $this->createDao('Mooc:Course.CourseScoreDao');
    }

    protected function getCourseScoreSettingDao()
    {
        return $this->createDao('Mooc:Course.CourseScoreSettingDao');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }
}
