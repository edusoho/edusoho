<?php
namespace Topxia\Service\Notification;

use Topxia\Api\Util\MobileSchoolUtil;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PushMessageEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'testpaper.reviewed'        => 'onTestPaperReviewed',
            'course.lesson.publish'     => 'onLessonPubilsh',
            'course.join'               => 'onCourseJoin',
            'course.quit'               => 'onCourseQuit',
            'course.create'             => 'onCourseCreate',
            'course.publish'            => 'onCoursePublish',
            'course.lesson.delete'      => 'onCourseLessonDelete',
            'course.lesson.update'      => 'onCourseLessonUpdate',
            'announcement.create'       => 'onAnnouncementCreate',
            'classroom.create'          => 'onClassroomCreate',
            'classroom.join'            => 'onClassroomJoin',
            'classroom.quit'            => 'onClassroomQuit',
            'classroom.put_course'      => 'onClassroomPutCourse',
            'article.create'            => 'onArticleCreate',
            'course.thread.post.create' => 'onCourseThreadPostCreate',
            'homework.check'            => 'onHomeworkCheck',
            'course.lesson_finish'      => 'onCourseLessonFinish',
            'course.lesson_start'       => 'onCourseLessonStart',
            'course.thread.create'      => 'onCourseThreadCreate',
            'user.register'             => 'onUserRegister',
            'user.profile.update'       => 'onUserProfileUpdate',
            'user.email.verify'         => 'onUserEmailVerify',
            'user.nickname.update'      => 'onUserNicknameUpdate',
            'user.email.update'         => 'onUserEmialUpdate',
            'user.avatar.update'        => 'onUserAvatarUpdate',
            'user.password.update'      => 'onUserPasswordUpdate',
            'user.paypassword.update'   => 'onUserPayPasswordUpdate',
            'user.mobile.update'        => 'onUserMobileUpdate',
            'user.securequestion.add'   => 'onUserSecurequestionAdd',
            'user.account.setup'        => 'onUserAccountSetup',
            'user.roles.change'         => 'onUserRolesChange',
            'user.unbind'               => 'onUserUnbind',
            'user.binduser'             => 'onUserBinduser',
            'user.lock'                 => 'onUserLock',
            'user.unlock'               => 'onUserUnlock',
            'user.promote'              => 'onUserPromote',
            'user.cancelpromote'        => 'onUserCancelPromote',
            'user.follow'               => 'onUserFollow',
            'user.unfollow'             => 'onUserUnfollow',
            'user.apply.approval'       => 'onUserApplyApproval',
            'user.pass.approval'        => 'onUserPassApproval',
            'user.reject.approval'      => 'onUserRejectApproval',
            'user.create.invitecode'    => 'onUserCreateInvitecode',
            'course.update'             => 'onCourseUpdate',
            'course.pitcture.update'    => 'onCoursePictureUpdate',
            'course.recommend'          => 'onCourseRecommend'
        );
    }

    public function onCourseCreate(ServiceEvent $event)
    {
        $course = $event->getSubject();

        $currentUser = ServiceKernel::instance()->getCurrentUser();
        $message     = array(
            'name'    => $course['title'],
            'clients' => array(array(
                'clientId'   => $currentUser['id'],
                'clientName' => $currentUser['nickname']
            ))
        );

        $result = CloudAPIFactory::create('root')->post('/im/me/conversation', $message);
        $this->getCourseService()->updateCourse($course['id'], array('conversationId' => $result['no']));
        $this->pushCloudData('course', 'create', 'new', $course['id'], $course['createdTime']);
    }

    public function onClassroomCreate(ServiceEvent $event)
    {
        $classroom = $event->getSubject();

        $currentUser = ServiceKernel::instance()->getCurrentUser();
        $message     = array(
            'name'    => $classroom['title'],
            'clients' => array(array('clientId' => $currentUser['id'], 'clientName' => $currentUser['nickname']))
        );

        $result = CloudAPIFactory::create('root')->post('/im/me/conversation', $message);
        $this->getClassroomService()->updateClassroom($classroom['id'], array('conversationId' => $result['no']));
    }

    public function onTestPaperReviewed(ServiceEvent $event)
    {
        $testpaper = $event->getSubject();
        $result    = $event->getArgument('testpaperResult');

        $testpaper['target']       = explode('-', $testpaper['target']);
        $testpaperResult['target'] = explode('-', $result['target']);
        $lesson                    = $this->getCourseService()->getLesson($testpaperResult['target'][2]);
        $target                    = $this->getTarget($testpaper['target'][0], $testpaper['target'][1]);

        $from = array(
            'type'  => $target['type'],
            'id'    => $target['id'],
            'image' => $target['image']
        );

        $to = array('type' => 'user', 'id' => $result['userId']);

        $body = array(
            'type'     => 'testpaper.reviewed',
            'id'       => $result['id'],
            'lessonId' => $lesson['id']
        );

        $this->push($lesson['title'], $result['paperName'], $from, $to, $body);
    }

    public function onLessonPubilsh(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $course = $this->getCourseService()->getCourse($lesson['courseId']);
        $from   = array(
            'type'  => 'course',
            'id'    => $course['id'],
            'image' => $this->getFileUrl($course['smallPicture'])
        );

        $to = array('type' => 'course', 'id' => $course['id']);

        $body = array('type' => 'lesson.publish', 'id' => $lesson['id'], 'lessonType' => $lesson['type']);

        $this->push($course['title'], $lesson['title'], $from, $to, $body);

        $mobileSetting = $this->getSettingService()->get('mobile');

        if ((!isset($mobileSetting['enable']) || $mobileSetting['enable']) && $lesson['type'] == 'live') {
            $this->createJob($lesson);
        }
    }

    public function onCourseLessonUpdate(ServiceEvent $event)
    {
        $context       = $event->getSubject();
        $argument      = $context['argument'];
        $lesson        = $context['lesson'];
        $mobileSetting = $this->getSettingService()->get('mobile');

        if ($lesson['type'] == 'live' && isset($argument['startTime']) && $argument['startTime'] != $lesson['fields']['startTime'] && (!isset($mobileSetting['enable']) || $mobileSetting['enable'])) {
            $jobs = $this->getCrontabService()->findJobByTargetTypeAndTargetId('lesson', $lesson['id']);

            if ($jobs) {
                $this->deleteJob($jobs);
            }

            if ($lesson['status'] == 'published') {
                $this->createJob($lesson);
            }
        }
    }

    public function onCourseLessonDelete(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $lesson  = $context['lesson'];
        $jobs    = $this->getCrontabService()->findJobByTargetTypeAndTargetId('lesson', $lesson['id']);

        if ($jobs) {
            $this->deleteJob($jobs);
        }
    }

    public function onCoursePublish(ServiceEvent $event)
    {
        $course = $event->getSubject();

        $from = array(
            'type'  => 'course',
            'id'    => $course['id'],
            'image' => $this->getFileUrl($course['smallPicture'])
        );

        $to = array('type' => 'course', 'id' => $course['id']);

        $body = array('type' => 'course.open');

        return $this->push($course['title'], '课程已发布!', $from, $to, $body);
    }

    public function onAnnouncementCreate(ServiceEvent $event)
    {
        $announcement = $event->getSubject();

        $target = $this->getTarget($announcement['targetType'], $announcement['targetId']);

        $from = array(
            'type'  => $target['type'],
            'id'    => $target['id'],
            'image' => $target['image']
        );

        $to = array(
            'type' => $target['type'],
            'id'   => $target['id']
        );

        $body = array(
            'id'   => $announcement['id'],
            'type' => 'announcement.create'
        );

        $this->push($target['title'], $announcement['content'], $from, $to, $body);
    }

    public function onClassroomJoin(ServiceEvent $event)
    {
        $classroom = $event->getSubject();
        $userId    = $event->getArgument('userId');

        if ($classroom['conversationId']) {
            $this->addGroupMember('classroom', $classroom['conversationId'], $classroom['createdTime'], $userId);
        }

        $from = array(
            'type'  => 'classroom',
            'id'    => $classroom['id'],
            'image' => $this->getFileUrl($classroom['smallPicture'])
        );

        $to = array(
            'type' => 'classroom',
            'id'   => $classroom['id']
        );

        $body = array('type' => 'classroom.join', 'userId' => $userId);

        $this->push($classroom['title'], '班级有新成员加入', $from, $to, $body);
    }

    public function onClassroomQuit(ServiceEvent $event)
    {
        $classroom = $event->getSubject();
        $userId    = $event->getArgument('userId');

        if ($classroom['conversationId']) {
            $this->deleteGroupMember('course', $classroom['conversationId'], $classroom['createdTime'], $userId);
        }
    }

    public function onCourseJoin(ServiceEvent $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');

        if (!$course['parentId'] && $course['conversationId']) {
            $this->addGroupMember('course', $course['conversationId'], $course['createdTime'], $userId);
        }
    }

    public function onCourseQuit(ServiceEvent $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');

        if (!$course['parentId'] && $course['conversationId']) {
            $this->deleteGroupMember('course', $course['conversationId'], $course['createdTime'], $userId);
        }
    }

    public function onClassroomPutCourse(ServiceEvent $event)
    {
        $classroomCourse = $event->getSubject();

        $classroom = $this->getClassroomService()->getClassroom($classroomCourse['classroomId']);

        $from = array(
            'type'  => 'classroom',
            'id'    => $classroom['id'],
            'image' => $this->getFileUrl($classroom['smallPicture'])
        );

        $to = array(
            'type' => 'classroom',
            'id'   => $classroom['id']
        );

        $course = $this->getCourseService()->getCourse($classroomCourse['courseId']);

        $body = array(
            'type'    => 'classroom.put_course',
            'id'      => $course['id'],
            'title'   => $course['title'],
            'image'   => $this->getFileUrl($course['smallPicture']),
            'content' => $course['about']
        );

        $this->push($classroom['title'], '班级有新课程加入！', $from, $to, $body);
    }

    public function onArticleCreate(ServiceEvent $event)
    {
        $article    = $event->getSubject();
        $schoolUtil = new MobileSchoolUtil();
        $articleApp = $schoolUtil->getArticleApp();
        $from       = array(
            'id'    => $articleApp['id'],
            'type'  => $articleApp['code'],
            'image' => $this->getAssetUrl($articleApp['avatar'])
        );

        $to = array(
            'type' => 'global'
        );

        $body = array(
            'type'    => 'news.create',
            'id'      => $article['id'],
            'title'   => $article['title'],
            'image'   => $this->getFileUrl($article['thumb']),
            'content' => $this->plainText($article['body'], 50)
        );

        $this->push('资讯', $article['title'], $from, $to, $body);
    }

    public function onCourseThreadPostCreate(ServiceEvent $event)
    {
        $post     = $event->getSubject();
        $course   = $this->getCourseService()->getCourse($post['courseId']);
        $question = $this->getThreadService()->getThread($post['courseId'], $post['threadId']);

        foreach ($course['teacherIds'] as $teacherId) {
            if ($teacherId == $post['userId'] && $question['type'] == 'question') {
                $target = $this->getTarget('course', $post['courseId']);
                $from   = array(
                    'type'  => 'course',
                    'id'    => $post['courseId'],
                    'image' => $target['image']
                );
                $to   = array('type' => 'user', 'id' => $question['userId']);
                $body = array(
                    'type'                => 'question.answered',
                    'threadId'            => $question['id'],
                    'courseId'            => $question['courseId'],
                    'lessonId'            => $question['lessonId'],
                    'questionCreatedTime' => $question['createdTime'],
                    'questionTitle'       => $question['title'],
                    'postContent'         => $post['content']
                );
                $this->push($course['title'], $question['title'], $from, $to, $body);
            }
        }
    }

    public function onHomeworkCheck(ServiceEvent $event)
    {
        $homeworkResult = $event->getSubject();

        $course = $this->getCourseService()->getCourse($homeworkResult['courseId']);
        $lesson = $this->getCourseService()->getLesson($homeworkResult['lessonId']);
        $target = $this->getTarget('course', $homeworkResult['courseId']);
        $from   = array(
            'type'  => 'course',
            'id'    => $homeworkResult['courseId'],
            'image' => $target['image']
        );
        $to   = array('type' => 'user', 'id' => $homeworkResult['userId']);
        $body = array(
            'type'             => 'homework.reviewed',
            'homeworkId'       => $homeworkResult['homeworkId'],
            'homeworkResultId' => $homeworkResult['id'],
            'lessonId'         => $homeworkResult['lessonId'],
            'courseId'         => $homeworkResult['courseId'],
            'teacherSay'       => $homeworkResult['teacherSay']
        );

        $this->push($course['title'], $lesson['title'], $from, $to, $body);
    }

    public function onCourseLessonFinish(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $course = $event->getArgument('course');
        $learn  = $event->getArgument('learn');

        $target = $this->getTarget('course', $learn['courseId']);
        $from   = array(
            'type'  => 'course',
            'id'    => $learn['courseId'],
            'image' => $target['image']
        );
        $to   = array('type' => 'user', 'id' => $learn['userId']);
        $body = array(
            'type'            => 'lesson.finish',
            'lessonId'        => $learn['lessonId'],
            'courseId'        => $learn['courseId'],
            'learnStartTime'  => $learn['startTime'],
            'learnFinishTime' => $learn['finishedTime']
        );
        $this->push($course['title'], $lesson['title'], $from, $to, $body);
    }

    public function onCourseLessonStart(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $course = $event->getArgument('course');
        $learn  = $event->getArgument('learn');
        $target = $this->getTarget('course', $learn['courseId']);
        $from   = array(
            'type'  => 'course',
            'id'    => $learn['courseId'],
            'image' => $target['image']
        );
        $to   = array('type' => 'user', 'id' => $learn['userId']);
        $body = array(
            'type'           => 'lesson.start',
            'lessonId'       => $learn['lessonId'],
            'courseId'       => $learn['courseId'],
            'learnStartTime' => $learn['startTime']
        );
        $this->push($course['title'], $lesson['title'], $from, $to, $body);
    }

    public function onCourseThreadCreate(ServiceEvent $event)
    {
        $thread = $event->getSubject();
        $course = $this->getCourseService()->getCourse($thread['courseId']);

        if ($thread['type'] == 'question') {
            $target = $this->getTarget('course', $thread['courseId']);
            $from   = array(
                'type'  => 'course',
                'id'    => $thread['courseId'],
                'image' => $target['image']
            );
            $to   = array('type' => 'user');
            $body = array(
                'type'                => 'question.created',
                'threadId'            => $thread['id'],
                'courseId'            => $thread['courseId'],
                'lessonId'            => $thread['lessonId'],
                'questionCreatedTime' => $thread['createdTime'],
                'questionTitle'       => $thread['title']
            );

            foreach ($course['teacherIds'] as $teacherId) {
                $to['id'] = $teacherId;
                $this->push($course['title'], $thread['title'], $from, $to, $body);
            }
        }
    }

    public function onUserRegister(ServiceEvernt $event)
    {
        $user = $event->getSubject();
        $this->getCloudDataService()->push('edusoho.user.register', array(
            'type'     => 'new',
            'category' => 'user',
            'id'       => $user['id']
        ), $user['createdTime']);
    }

    public function onUserProfileUpdate(Service $event)
    {
        $userId = $event->getSubject();
        $this->pushCloudData('user', 'profile', 'update', $userId, time());
    }

    public function onUserEmailVerify(Service $event)
    {
        $user = $event->getSubject();
        $this->getCloudDataService()->push('edusoho.user.email.verify', array(
            'type'     => 'update',
            'category' => 'user',
            'id'       => $user['id']
        ), time());
    }

    public function onUserNicknameUpdate(Service $event)
    {
        $user = $event->getSubject();
        $this->pushCloudData('user', 'nickname', 'update', $user['id'], time());
    }

    public function onUserEmialUpdate(Service $event)
    {
        $user = $event->getSubject();
        $this->pushCloudData('user', 'email', 'update', $user['id'], time());
    }

    public function onUserAvatarUpdate(Service $event)
    {
        $user = $event->getSubject();
        $this->pushCloudData('user', 'avater', 'update', $user['id'], time());
    }

    public function onUserPasswordUpdate(Service $event)
    {
        $user = $event->getSubject();
        $this->pushCloudData('user', 'password', 'update', $user['id'], time());
    }

    public function onUserPayPasswordUpdate()
    {
        $user = $event->getSubject();
        $this->pushCloudData('user', 'paypassword', 'update', $user['id'], time());
    }

    public function onUserMobileUpdate(Service $event)
    {
        $user = $event->getSubject();
        $this->pushCloudData('user', 'mobile', 'update', $user['id'], time());
    }

    public function onUserSecurequestionAdd(Service $event)
    {
        $userId = $event->getSubject();
        $this->pushCloudData('user', 'securequestion', 'new', $userId, time());
    }

    public function onUserAccountSetup(Service $event)
    {
        $userId = $event->getSubject();
        $this->getCloudDataService()->push('edusoho.user.account.setup', array(
            'type'     => 'update',
            'category' => 'user',
            'id'       => $userId
        ), time());
    }

    public function onUserRolesChange(Service $event)
    {
        $userId = $event->getSubject();
        $this->pushCloudData('user', 'roles', 'update', $userId, time());
    }

    public function onUserUnbind(Service $event)
    {
        $user = $event->getSubject();
        $this->pushCloudData('user', 'bind', 'delete', $user['id'], time());
    }

    public function onUserBinduser(Service $event)
    {
        $userId = $event->getSubject();
        $this->pushCloudData('user', 'bind', 'new', $userId, time());
    }

    public function onUserLock(Service $event)
    {
        $userId = $event->getSubject();
        $this->pushCloudData('user', 'lock', 'update', $userId, time());
    }

    public function onUserUnlock(Service $event)
    {
        $userId = $event->getSubject();
        $this->pushCloudData('user', 'unlock', 'update', $userId, time());
    }

    public function onUserPromote(Service $event)
    {
        $userId = $event->getSubject();
        $this->pushCloudData('user', 'promote', 'update', $userId, time());
    }

    public function onUserCancelPromote(Service $event)
    {
        $userId = $event->getSubject();
        $this->pushCloudData('user', 'cancelpromote', 'update', $userId, time());
    }

    public function onUserFollow(Service $event)
    {
        $userId = $event->getSubject();
        $this->pushCloudData('user', 'follow', 'update', $userId, time());
    }

    public function onUserUnfollow(Service $event)
    {
        $userId = $event->getSubject();
        $this->pushCloudData('user', 'unfollow', 'update', $userId, time());
    }

    public function onUserApplyApproval(Service $event)
    {
        $userId = $event->getSubject();
        $this->pushCloudData('user', 'applyapproval', 'new', $userId, time());
    }

    public function onUserPassApproval(Service $event)
    {
        $userId = $event->getSubject();
        $this->pushCloudData('user', 'passapproval', 'update', $userId, time());
    }

    public function onUserRejectApproval(Service $event)
    {
        $userId = $event->getSubject();
        $this->pushCloudData('user', 'rejectapproval', 'update', $userId, time());
    }

    public function onUserCreateInvitecode(Service $event)
    {
        $userId = $event->getSubject();
        $this->pushCloudData('user', 'invitecode', 'update', $userId, time());
    }

    public function onCourseUpdate(Service $event)
    {
        $context = $event->getSubject();
        $course  = $context['course'];
        $this->pushCloudData('course', 'course', 'update', $course['id'], time());
    }

    public function onCoursePictureUpdate(Service $event)
    {
        $context = $event->getSubject();
        $course  = $context['course'];
        $this->puchCloudData('course', 'coursepicture', 'update', $course['id'], time());
    }

    public function onCourseRecommend(Service $event)
    {
        $courseId = $event->getSubject();
        $this->pushCloudData('course', 'recommend', 'update', $courseId, time());
    }

    protected function push($title, $content, $from, $to, $body)
    {
        $message = array(
            'title'   => $title,
            'content' => $content,
            'custom'  => array(
                'from' => $from,
                'to'   => $to,
                'body' => $body
            )
        );

        $result = CloudAPIFactory::create('tui')->post('/message/send', $message);
    }

    protected function addGroupMember($grouType, $conversationId, $timestamp, $memberId)
    {
        $user   = $this->getUserService()->getUser($memberId);
        $result = $this->getCloudDataService()->push('edusoho.'.$grouType.'.join', array(
            'conversationId' => $conversationId,
            'memberId'       => $memberId,
            'memberName'     => $user['nickname']
        ), $timestamp);
    }

    protected function deleteGroupMember($grouType, $conversationId, $timestamp, $memberId)
    {
        $result = $this->getCloudDataService()->push('edusoho.'.$grouType.'.quit', array(
            'conversationId' => $conversationId,
            'memberId'       => $memberId
        ), $timestamp);
    }

    protected function pushCloudData($category, $fieldType, $paramType, $userId, $timestamp)
    {
        $result = $this->getCloudDataService()->push('edusoho.'.$category.".".$fieldType.".".$paramType, array(
            'type'     => $paramType,
            'category' => $category,
            'id'       => $userId
        ), $timestamp);
    }

    protected function getTarget($type, $id)
    {
        $target = array('type' => $type, 'id' => $id);

        switch ($type) {
            case 'course':
                ;
                $course          = $this->getCourseService()->getCourse($id);
                $target['title'] = $course['title'];
                $target['image'] = $this->getFileUrl($course['smallPicture']);
                break;
            case 'classroom':
                ;
                $classroom       = $this->getClassroomService()->getClassroom($id);
                $target['title'] = $classroom['title'];
                $target['image'] = $this->getFileUrl($classroom['smallPicture']);
            case 'global':
                ;
                $schoolUtil      = new MobileSchoolUtil();
                $schoolApp       = $schoolUtil->getAnnouncementApp();
                $target['title'] = '网校公告';
                $target['id']    = $schoolApp['id'];
                $target['image'] = $this->getFileUrl($schoolApp['avatar']);
            default:
                # code...
                break;
        }

        return $target;
    }

    protected function getFileUrl($path)
    {
        if (empty($path)) {
            return $path;
        }

        $path = str_replace('public://', '', $path);
        $path = str_replace('files/', '', $path);
        $path = "http://{$_SERVER['HTTP_HOST']}/files/{$path}";
        return $path;
    }

    protected function getAssetUrl($path)
    {
        if (empty($path)) {
            return '';
        }

        $path = "http://{$_SERVER['HTTP_HOST']}/assets/{$path}";
        return $path;
    }

    protected function plainText($text, $count)
    {
        return mb_substr($text, 0, $count, 'utf-8');
    }

    protected function createJob($lesson)
    {
        if ($lesson['startTime'] >= (time() + 60 * 60)) {
            $startJob = array(
                'name'       => "PushNotificationOneHourJob",
                'cycle'      => 'once',
                'time'       => $lesson['startTime'] - 60 * 60,
                'jobClass'   => 'Topxia\\Service\\Notification\\Job\\PushNotificationOneHourJob',
                'targetType' => 'lesson',
                'targetId'   => $lesson['id']
            );
            $startJob = $this->getCrontabService()->createJob($startJob);
        }
    }

    protected function deleteJob($jobs)
    {
        foreach ($jobs as $key => $job) {
            if ($job['name'] == 'PushNotificationOneHourJob') {
                $this->getCrontabService()->deleteJob($job['id']);
            }
        }
    }

    protected function getThreadService()
    {
        return ServiceKernel::instance()->createService('Course.ThreadService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getTestpaperService()
    {
        return ServiceKernel::instance()->createService('Testpaper.TestpaperService');
    }

    protected function getCloudDataService()
    {
        return ServiceKernel::instance()->createService('CloudData.CloudDataService');
    }

    protected function getCrontabService()
    {
        return ServiceKernel::instance()->createService('Crontab.CrontabService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}
