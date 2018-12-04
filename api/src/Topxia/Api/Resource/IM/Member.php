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
        $fields = $this->checkRequiredFields($requiredFields, $request->request->all());

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
        $user = $this->getCurrentUser();
        $course = $this->getCourseService()->getCourse($courseId);

        if ($convNo) {
            $convMember = $this->getConversationService()->getMemberByConvNoAndUserId($convNo, $user['id']);

            if ($convMember) {
                return array('convNo' => $convMember['convNo']);
            }

            $courseMember = $this->getCourseMemberService()->getCourseMember($courseId, $user['id']);
            if (!$courseMember) {
                return $this->error('4041901', '课程中没有该成员');
            }

            if ($this->getConversationService()->isImMemberFull($convNo, 500)) {
                return $this->error('5003310', '会话人数已满');
            }

            try {
                $convMember = $this->getConversationService()->joinConversation($convNo, $user['id']);

                return array('convNo' => $convMember['convNo']);
            } catch (\Exception $e) {
                return $this->error($e->getCode(), $e->getMessage());
            }
        } else {
            $courseMember = $this->getCourseMemberService()->getCourseMember($courseId, $user['id']);
            if (!$courseMember) {
                return $this->error('4041901', '课程中没有该成员');
            }

            $conversation = $this->getConversationService()->createConversation($course['title'], 'course', $course['id'], array($user));

            $res = array('convNo' => $conversation['no']);
        }

        return $res;
    }

    protected function entryClassroomConversation($classroomId, $convNo)
    {
        $user = $this->getCurrentUser();
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        if ($convNo) {
            $convMember = $this->getConversationService()->getMemberByConvNoAndUserId($convNo, $user['id']);

            if ($convMember) {
                return array('convNo' => $convMember['convNo']);
            }

            $classroomMember = $this->getClassroomService()->getClassroomMember($classroomId, $user['id']);
            if (!$classroomMember || in_array('auditor', $classroomMember['role'])) {
                return $this->error('4031821', '学员未加入班级');
            }

            if ($this->getConversationService()->isImMemberFull($convNo, 500)) {
                return $this->error('5003310', '会话人数已满');
            }

            try {
                $this->getConversationService()->joinConversation($convNo, $user['id']);

                return array('convNo' => $convNo);
            } catch (\Exception $e) {
                return $this->error($e->getCode(), $e->getMessage());
            }
        } else {
            $classroomMember = $this->getClassroomService()->getClassroomMember($classroomId, $user['id']);
            if (!$classroomMember || in_array('auditor', $classroomMember['role'])) {
                return $this->error('4031821', '学员未加入班级');
            }

            $conversation = $this->getConversationService()->createConversation($classroom['title'], 'classroom', $classroom['id'], array($user));
            $res = array('convNo' => $conversation['no']);
        }

        return $res;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM:ConversationService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course:MemberService');
    }
}
