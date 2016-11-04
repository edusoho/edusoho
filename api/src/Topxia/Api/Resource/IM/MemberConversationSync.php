<?php

namespace Topxia\Api\Resource\IM;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
use Topxia\Api\Resource\BaseResource;
use Symfony\Component\HttpFoundation\Request;

/**
 * 更新用户在所有班级、课程下的会话
 */
class MemberConversationSync extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $setting = $this->getSettingservice()->get('app_im', array());
        if (empty($setting['enabled'])) {
            return $this->error('700008', '网站会话未启用');
        }

        $user = $this->getCurrentUser();

        //TODO
        //1. 同步用户的班级会话
        //2. 同步用户的课程会话
        $this->syncClassroomConversations($user);
        $this->syncCourseConversations($user);
        //返回信息如何约定？
        return true;
    }

    public function filter($res)
    {
        return $res;
    }

    protected function syncCourseConversations($user)
    {
        //1. a: get courses of user
        //2. c: get conversations of courses (targetType=course-push)(im_conversation)
        //3. if c not in a, new conv(im_conversation) & add user to conv(im_member); if a not in c, delete conv & delete user from conv;
        //4. b: get conversations of user with course-push (im_member),
        //5. compare a & b, if b not in a, del b; if a not in b, add user to b;
        //
        // check : 确保下面所有的操作都是同时操作【本地数据库】和【远程服务器】
        //
        //
        // refact :
        //    1. 下面的代码以及和syncClassroomConversations的代码相比，重复的太多，考虑抽取
        //    2. 保存本地库和保存到IM是同步的，应该抽提出来；
        //    3. 对xxx-push和xxx的操作是同步的，应该抽提出来；
        //

        //XXX 希望用户加入的课程不会超过1000，不然你懂得。
        $courses    = $this->getCourseService()->findUserLearnCourses($user['id'], 0, 1000);
        $coursesMap = ArrayToolkit::index($courses, 'id');
        return $this->syncTargetConversations($user, $coursesMap, 'course');

        // $coursesMap = ArrayToolkit::index($courses, 'id');
        // if (empty($coursesMap)) {
        //     return;
        // }

        // $courseConvs = $this->getConversationService()->searchConversations(array(
        //     'targetIds'   => ArrayToolkit::column($courses, 'id'),
        //     'targetTypes' => array('course-push')
        // ));
        // $courseConvsMap = ArrayToolkit::index($courseConvs, 'targetId');

        // foreach ($courseConvsMap as $convKey => $convVal) {
        //     if (!isset($coursesMap[$convKey])) {
        //         //local & cloud, course & member
        //         $conv = $this->getConversationService()->createConversation($coursesMap[$convKey]['title'], 'course-push', $convKey, array($user));
        //     }
        // }
        // foreach ($courses as $csKey => $csVal) {
        //     if (!isset($courseConvsMap[$csKey])) {
        //         //删除会话及会话下所有members
        //         $this->getConversationService()->removeConversation($courseConvsMap[$csKey]['convNo']);
        //     }
        // }

        // $userConvs    = $this->getConversationService()->findMembersByUserIdAndTargetType($user['id'], 'course-push');
        // $userConvsMap = ArrayToolkit::index($userConvs, 'targetId');
        // foreach ($courses as $csKey => $csVal) {
        //     if (!isset($userConvsMap[$csKey])) {
        //         //local & cloud   add member
        //         $this->getConversationService()->joinConversation($courseConvsMap[$ucKey]['convNo'], $user['id']);
        //     }
        // }
        // foreach ($userConvsMap as $ucKey => $ucVal) {
        //     if (!isset($courses[$ucKey])) {
        //         //local & cloud   delete member
        //         $this->getConversationService()->quitConversation($userConvsMap[$csKey]['convNo'], $user['id']);
        //     }
        // }

        // $userConvs2    = $this->getConversationService()->findMembersByUserIdAndTargetType($user['id'], 'course');
        // $userConvs2Map = ArrayToolkit::index($userConvs2, 'targetId');
        // foreach ($courses as $csKey => $csVal) {
        //     if (!isset($userConvs2Map[$csKey])) {
        //         //local & cloud   add member
        //         $this->getConversationService()->joinConversation($courseConvs2Map[$ucKey]['convNo'], $user['id']);
        //     }
        // }
        // foreach ($userConvs2Map as $ucKey => $ucVal) {
        //     if (!isset($courses[$ucKey])) {
        //         //local & cloud   delete member
        //         $this->getConversationService()->quitConversation($userConvs2Map[$csKey]['convNo'], $user['id']);
        //     }
        // }

        return true;
    }

    protected function syncClassroomConversations($user)
    {
        //1. a: get classrooms of user
        //2. get conversations of classrooms (targetType=classroom-push)(im_conversation)
        //3. if none, new conv(im_conversation) & add user to conv(im_member)
        //4. b: get conversations of user with classroom-push (im_member),
        //5. compare a & b, if b not in a, del b; if a not in b, add user to b;
        //
        // 代码参见 syncCourseConversations
        //
        $classroomIds = $this->getClassroomService()->findUserJoinedClassroomIds($user['id'], 0, 1000);
        return $this->syncTargetConversations($user, $classroomIds, 'classroom');

        return true;
    }

    protected function syncTargetConversations($user, $targetIds, $targetType)
    {
        if (empty($targetIds)) {
            return;
        }

        $courseConvs = $this->getConversationService()->searchConversations(array(
            'targetIds'   => $targetIds,
            'targetTypes' => array('course-push')
        ));
        $courseConvsMap = ArrayToolkit::index($courseConvs, 'targetId');

        foreach ($courseConvsMap as $convKey => $convVal) {
            if (!isset($targetsMap[$convKey])) {
                //local & cloud, course & member
                $conv = $this->getConversationService()->createConversation($targetsMap[$convKey]['title'], 'course-push', $convKey, array($user));
            }
        }
        foreach ($courses as $csKey => $csVal) {
            if (!isset($courseConvsMap[$csKey])) {
                //删除会话及会话下所有members
                $this->getConversationService()->removeConversation($courseConvsMap[$csKey]['convNo']);
            }
        }

        $userConvs    = $this->getConversationService()->findMembersByUserIdAndTargetType($user['id'], 'course-push');
        $userConvsMap = ArrayToolkit::index($userConvs, 'targetId');
        foreach ($courses as $csKey => $csVal) {
            if (!isset($userConvsMap[$csKey])) {
                //local & cloud   add member
                $this->getConversationService()->joinConversation($courseConvsMap[$ucKey]['convNo'], $user['id']);
            }
        }
        foreach ($userConvsMap as $ucKey => $ucVal) {
            if (!isset($courses[$ucKey])) {
                //local & cloud   delete member
                $this->getConversationService()->quitConversation($userConvsMap[$csKey]['convNo'], $user['id']);
            }
        }

        $userConvs2    = $this->getConversationService()->findMembersByUserIdAndTargetType($user['id'], 'course');
        $userConvs2Map = ArrayToolkit::index($userConvs2, 'targetId');
        foreach ($courses as $csKey => $csVal) {
            if (!isset($userConvs2Map[$csKey])) {
                //local & cloud   add member
                $this->getConversationService()->joinConversation($courseConvs2Map[$ucKey]['convNo'], $user['id']);
            }
        }
        foreach ($userConvs2Map as $ucKey => $ucVal) {
            if (!isset($courses[$ucKey])) {
                //local & cloud   delete member
                $this->getConversationService()->quitConversation($userConvs2Map[$csKey]['convNo'], $user['id']);
            }
        }

        return true;
    }

    protected function getConversationService()
    {
        return $this->getServiceKernel()->createService('IM.ConversationService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
