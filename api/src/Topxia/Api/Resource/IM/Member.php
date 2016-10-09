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

        $conversation = $this->getConversationService()->getConversationByTarget($fields['targetId'], $fields['targetType']);

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

        if ($convNo) {
            $conversationMember = $this->getConversationService()->getMemberByConvNoAndUserId($convNo, $user['id']);

            if (!$conversationMember) {
                $courseMember = $this->getCourseService()->getCourseMember($courseId, $user['id']);
                if (!$courseMember) {
                    return $this->error('700003', '学员未加入课程');
                }

                if ($this->getConversationService()->isImMemberFull($convNo, 500)) {
                    return $this->error('700008', '会话人数已满');
                }

                return $this->joinCoversationMember($convNo, $course['id'], 'course', $user);
            }

            $res = array('convNo' => $convNo);
        } else {
            $courseMember = $this->getCourseService()->getCourseMember($courseId, $user['id']);
            if (!$courseMember) {
                return $this->error('700003', '学员未加入课程');
            }

            $conversation = $this->getConversationService()->createConversation($course['title'], 'course', $course['id'], array($user));

            $res = array('convNo' => $conversation['no']);
        }

        return $res;
    }

    protected function entryClassroomConversation($classroomId, $convNo)
    {
        $user      = $this->getCurrentUser();
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        if ($convNo) {
            $conversationMember = $this->getConversationService()->getMemberByConvNoAndUserId($convNo, $user['id']);

            if (!$conversationMember) {
                $classroomMember = $this->getClassroomService()->getClassroomMember($classroomId, $user['id']);
                if (!$classroomMember || in_array('auditor', $classroomMember['role'])) {
                    return $this->error('700013', '学员未加入班级');
                }

                if ($this->getConversationService()->isImMemberFull($convNo, 500)) {
                    return $this->error('700008', '会话人数已满');
                }

                return $this->joinCoversationMember($convNo, $classroom['id'], 'classroom', $user);
            }

            $res = array('convNo' => $convNo);
        } else {
            $classroomMember = $this->getClassroomService()->getClassroomMember($classroomId, $user['id']);
            if (!$classroomMember || in_array('auditor', $classroomMember['role'])) {
                return $this->error('700013', '学员未加入班级');
            }

            $conversation = $this->getConversationService()->createConversation($classroom['title'], 'classroom', $classroom['id'], array($user));
            $res          = array('convNo' => $conversation['no']);
        }

        return $res;
    }

    protected function joinCoversationMember($convNo, $targetId, $targetType, $user)
    {
        $res = $this->getConversationService()->addConversationMember($convNo, array($user));

        if ($res) {
            $member = array(
                'convNo'     => $convNo,
                'targetId'   => $targetId,
                'targetType' => $targetType,
                'userId'     => $user['id']
            );

            $this->getConversationService()->addMember($member);

            return array('convNo' => $convNo);
        } else {
            return $this->error('700006', '学员进入会话失败');
        }
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
