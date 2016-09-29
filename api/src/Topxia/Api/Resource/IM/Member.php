<?php

namespace Topxia\Api\Resource\IM;

use Silex\Application;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;

class Member extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $requiredFields = array('targetId', 'targetType');
        $fields         = $this->checkRequiredFields($requiredFields, $request->request->all());

        $conversation = $this->getConversationService()->getConversationByTargetIdAndTargetType($fields['targetId'], $fields['targetType']);

        if ($conversation) {
            if ($this->getConversationService()->isImMemberFull($convNo)) {
                return $this->error('700008', '会话人数已满');
            }
        }

        $convNo = $conversation ? $conversation['no'] : '';
        if ($fields['targetType'] == 'course') {
            return $this->entryCourseConversation($fields['targetId'], $convNo);
        } elseif ($fields['targetType'] == 'classroom') {
            return $this->entryClassroomConversation($fields['targetId'], $convNo);
        }
    }

    public function filter($res)
    {
        return $res;
    }

    protected function entryCourseConversation($courseId, $convNo)
    {
        $user   = $this->getCurrentUser();
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            return $this->error('700001', '课程不存在');
        }
        if ($course && $course['status'] != 'published') {
            return $this->error('700002', '课程未发布');
        }

        $courseMember = $this->getCourseService()->getCourseMember($courseId, $user['id']);
        if (!$courseMember) {
            return $this->error('700003', '学员未加入课程');
        }

        if ($convNo) {
            $conversationMember = $this->getConversationService()->getMemberByConvNoAndUserId($convNo, $user['id']);

            if (!$conversationMember) {
                $res = $this->getConversationService()->addConversationMember($convNo, array($user));

                if ($res) {
                    $member = array(
                        'convNo'     => $convNo,
                        'targetId'   => $course['id'],
                        'targetType' => 'course',
                        'userId'     => $user['id']
                    );

                    $this->getConversationService()->addMember($member);
                } else {
                    return $this->error('700006', '学员进入会话失败');
                }
            }

            $res = array('convNo' => $convNo);
        } else {
            $conversation = $this->getConversationService()->createConversation($course['title'], 'course', $course['id'], array($user));
            $res          = array('convNo' => $conversation['no']);
        }

        return $res;
    }

    protected function entryClassroomConversation($classroomId, $convNo)
    {
        $user      = $this->getCurrentUser();
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        if (!$classroom) {
            return $this->error('700011', '班级不存在');
        }

        if ($classroom['status'] != 'published') {
            return $this->error('700012', '班级未发布');
        }

        $classroomMember = $this->getClassroomService()->getClassroomMember($classroomId, $user['id']);
        if (!$classroomMember || in_array('auditor', $classroomMember['role'])) {
            return $this->error('700013', '学员未加入班级');
        }

        if ($convNo) {
            $conversationMember = $this->getConversationService()->getMemberByConvNoAndUserId($convNo, $user['id']);

            if (!$conversationMember) {
                $res = $this->getConversationService()->addConversationMember($convNo, $user['id'], $user['nickname']);
                if ($res) {
                    $member = array(
                        'convNo'     => $convNo,
                        'targetId'   => $classroom['id'],
                        'targetType' => 'classroom',
                        'userId'     => $user['id']
                    );
                    $this->getConversationService()->addMember($member);
                } else {
                    return $this->error('700006', '学员进入会话失败');
                }
            }

            $res = array('convNo' => $convNo);
        } else {
            $conversation = $this->getConversationService()->createConversation($course['title'], 'course', $course['id'], array($user));
            $res          = array('convNo' => $conversation['no']);
        }

        return $res;
    }

    protected function createCourseConversation($course)
    {
        if (empty($course)) {
            return '';
        }

        $user = $this->getCurrentUser();

        if (empty($course['convNo'])) {
            $convNo = $this->getConversationService()->createCloudConversation($course['title'], $user['id'], $user['nickname']);

            if (!empty($convNo)) {
                $this->getCourseService()->updateCourse($course['id'], array('convNo' => $convNo));
            }

            return $convNo;
        }

        return $course['convNo'];
    }

    protected function createClassroomConversation($classroom)
    {
        if (empty($classroom)) {
            return '';
        }

        $user = $this->getCurrentUser();

        if (empty($classroom['convNo'])) {
            $convNo = $this->getConversationService()->createCloudConversation($classroomId['title'], $user['id'], $user['nickname']);

            if (!empty($convNo)) {
                $this->getClassroomService()->updateClassroom($classroom['id'], array('convNo' => $convNo));
            }

            return $convNo;
        }

        return $classroom['convNo'];
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
