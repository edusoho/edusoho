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
            'user.service.registered'   => 'onUserCreate',
            'user.unlock'               => 'onUserCreate',
            'user.lock'                 => 'onUserDelete',
            'user.update'               => 'onUserUpdate',
            'user.change_nickname'      => 'onUserUpdate',

            'course.publish'            => 'onCourseCreate',
            'course.update'             => 'onCourseUpdate',
            'course.delete'             => 'onCourseDelete',
            'course.close'              => 'onCourseDelete',
            'course.join'               => 'onCourseJoin',
            'course.quit'               => 'onCourseQuit',
            // 'course.create'             => 'onCourseCreate',

            'course.lesson.publish'     => 'onCourseLessonCreate',
            'course.lesson.unpublish'   => 'onCourseLessonDelete',
            'course.lesson.update'      => 'onCourseLessonUpdate',
            'course.lesson.delete'      => 'onCourseLessonDelete',
            'course.lesson_start'       => 'onCourseLessonStart',
            'course.lesson_finish'       => 'onCourseLessonFinish',

            // 'classroom.create'          => 'onClassroomCreate',
            'classroom.join'            => 'onClassroomJoin',
            'classroom.quit'            => 'onClassroomQuit',

            'article.create'            => 'onArticleCreate',
            'article.publish'           => 'onArticleCreate',
            'article.update'            => 'onArticleUpdate',
            'article.trash'             => 'onArticleDelete',
            'article.unpublish'         => 'onArticleDelete',
            'article.delete'            => 'onArticleDelete',

            'thread.create'             => 'onThreadCreate',
            'thread.update'             => 'onThreadUpdate',
            'thread.delete'             => 'onThreadDelete',

            'announcement.create'       => 'onAnnouncementCreate',
            'testpaper.reviewed'        => 'onTestPaperReviewed',

            // 'testpaper.reviewed'        => 'onTestPaperReviewed',
            // -- 'course.lesson.publish'     => 'onLessonPublish',
            // -- 'course.join'               => 'onCourseJoin',
            // -- 'course.quit'               => 'onCourseQuit',
            // -- 'course.create'             => 'onCourseCreate',
            // -- 'course.publish'            => 'onCoursePublish',
            // -- 'course.lesson.delete'      => 'onCourseLessonDelete',
            // -- 'course.lesson.update'      => 'onCourseLessonUpdate',
            // -- 'course.close'              => 'onCourseClose',
            // 'announcement.create'       => 'onAnnouncementCreate',
            // -- 'classroom.join'            => 'onClassroomJoin',
            // -- 'classroom.quit'            => 'onClassroomQuit',
            // -- 'article.create'            => 'onArticleCreate',
            // 'discount.pass'             => 'onDiscountPass',
            'course.thread.post.create' => 'onCourseThreadPostCreate',
            'homework.check'            => 'onHomeworkCheck',
            'course.lesson_finish'      => 'onCourseLessonFinish',
            'course.lesson_start'       => 'onCourseLessonStart',
            'course.thread.create'      => 'onCourseThreadCreate',
            // -- 'course.lesson.create'      => 'onCourseLessonCreate',
            // -- 'profile.update'            => 'onProfileUpdate',
            // -- 'course.update'             => 'onCourseUpdate',

            'mobile.change'             => 'pushCloudData',
            // -- 'course.close'              => 'pushCloudData',
            // -- 'course.delete'             => 'pushCloudData',
            // -- 'course.lesson.unpublish'   => 'pushCloudData',
            // -- 'article.update'            => 'pushCloudData',
            // -- 'article.trash'             => 'pushCloudData',
            // -- 'article.delete'            => 'pushCloudData',
            // -- 'thread.update'             => 'pushCloudData',
            // -- 'thread.delete'             => 'pushCloudData',
            // -- 'thread.create'             => 'pushCloudData',
            'course.thread.update'      => 'pushCloudData',
            'course.thread.delete'      => 'pushCloudData',
        );
    }

    protected function pushCloud($eventName, array $data)
    {
        return $this->getCloudDataService()->push('school.'.$eventName, $data, time());
    }

    /**
     * User相关
     */
    public function onUserUpdate(ServiceEvent $event)
    {
        $context = $event->getSubject();
        if ($event->getName() == 'user.update') {
            $user = $context['user'];
        } else {
            $user = $context;
        }
        $profile = $this->getUserService()->getUserProfile($user['id']);

        $result = $this->pushCloud('user.update', $this->filterUser($user, $profile));
    }

    public function onUserCreate(ServiceEvent $event)
    {
        $user = $event->getSubject();
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $this->pushCloud('user.create', $this->filterUser($user, $profile));
    }

    public function onUserDelete(ServiceEvent $event)
    {
        $user = $event->getSubject();
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $this->pushCloud('user.delete', $this->filterUser($user, $profile));
    }

    protected function filterUser($user, $profile = array())
    {
        // id, nickname, title, roles, point, avatar(最大那个), about, updatedTime, createdTime
        $filtered = array();
        $filtered['id'] = $user['id'];
        $filtered['nickname'] = $user['nickname'];
        $filtered['title'] = $user['title'];

        if (!is_array($user['roles'])) {
            $user['roles'] = explode('|', $user['roles']);
        }
        
        $filtered['roles'] = in_array('ROLE_TEACHER', $user['roles']) ? 'teacher' : 'student';
        $filtered['point'] = $user['point'];
        $filtered['avatar'] = $this->getFileUrl($user['largeAvatar']);
        $filtered['about'] = empty($profile['about']) ? '' : $profile['about'];
        $filtered['updatedTime'] = $user['updatedTime'];
        $filtered['createdTime'] = $user['createdTime'];
        return $filtered;
    }

    /**
     * Course相关
     */
    public function onCourseCreate(ServiceEvent $event)
    {
        $course = $event->getSubject();

        if ($event->getName() == 'course.create') {
            //创建课程IM会话
            $currentUser = ServiceKernel::instance()->getCurrentUser();
            $message = array(
                'name' => $course['title'],
                'clients' => array(array(
                    'clientId' => $currentUser['id'],
                    'clientName' => $currentUser['nickname']
                ))
            );
            $result = CloudAPIFactory::create('root')->post('/im/me/conversation', $message);
            $course = $this->getCourseService()->updateCourse($course['id'], array('conversationId' => $result['no']));
        }

        $this->pushCloud('course.create', $this->filterCourse($course));
    }

    public function onCourseUpdate(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $course  = $context['course'];
        $this->pushCloud('course.update', $this->filterCourse($course));
    }

    public function onCourseDelete(ServiceEvent $event)
    {
        $course = $event->getSubject();

        $this->pushCloud('course.delete', $this->filterCourse($course));
    }

    public function onCourseJoin(ServiceEvent $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');
        $member = $event->getArgument('member');

        if (!empty($course['parentId'])) {
            return;
        }

        $member['course'] = $this->filterCourse($course);
        $member['user'] = $this->filterUser($this->getUserService()->getUser($userId));

        $this->pushCloud('course.join', $member);
    }

    public function onCourseQuit(ServiceEvent $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');
        $member = $event->getArgument('member');

        if (!empty($course['parentId'])) {
            return;
        }

        $member['course'] = $this->filterCourse($course);
        $member['user'] = $this->filterUser($this->getUserService()->getUser($userId));

        $this->pushCloud('course.quit', $member);
    }

    protected function filterCourse($course)
    {
        $course['smallPicture'] = $this->getFileUrl($course['smallPicture']);
        $course['middlePicture'] = $this->getFileUrl($course['middlePicture']);
        $course['largePicture'] = $this->getFileUrl($course['largePicture']);
        $course['about'] = $this->filterHtml($course['about']);
        return $course;
    }

    /**
     * CourseLesson相关
     */
    public function onCourseLessonCreate(ServiceEvent $event)
    {
        $lesson = $event->getSubject();

        $mobileSetting = $this->getSettingService()->get('mobile');

        if ((!isset($mobileSetting['enable']) || $mobileSetting['enable']) && $lesson['type'] == 'live') {
            $this->createJob($lesson);
        }

        $this->pushCloud('lesson.create', $lesson);
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

        $this->pushCloud('lesson.update', $lesson);
    }
    
    public function onCourseLessonDelete(ServiceEvent $event)
    {
        $context = $event->getSubject();
        if ($event->getName() == 'course.lesson.delete') {
            $lesson = $context['lesson'];
            $jobs = $this->getCrontabService()->findJobByTargetTypeAndTargetId('lesson', $lesson['id']);
            if ($jobs) {
                $this->deleteJob($jobs);
            }
        } else {
            $lesson = $context;
        }
        
        $this->pushCloud('lesson.delete', $lesson);
    }

    public function onCourseLessonStart(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $course = $event->getArgument('course');
        $learn  = $event->getArgument('learn');

        $learn['course'] = $this->filterCourse($course);
        $learn['lesson'] = $lesson;

        $this->pushCloud('lesson.start', $learn);
    }

    public function onCourseLessonFinish(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $course = $event->getArgument('course');
        $learn  = $event->getArgument('learn');

        $learn['course'] = $this->filterCourse($course);
        $learn['lesson'] = $lesson;

        $this->pushCloud('lesson.finish', $learn);
    }

    /**
     * Classroom相关
     */
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

    public function onClassroomJoin(ServiceEvent $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');
        $member = $event->getArgument('member');

        $member['classroom'] = $this->filterClassroom($classroom);
        $member['user'] = $this->filterUser($this->getUserService()->getUser($userId));

        $this->pushCloud('classroom.join', $member);
    }

    public function onClassroomQuit(ServiceEvent $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');
        $member = $event->getArgument('member');

        $member['classroom'] = $this->filterClassroom($classroom);
        $member['user'] = $this->filterUser($this->getUserService()->getUser($userId));

        $this->pushCloud('classroom.quit', $member);
    }

    protected function filterClassroom($classroom)
    {
        $classroom['smallPicture'] = $this->getFileUrl($classroom['smallPicture']);
        $classroom['middlePicture'] = $this->getFileUrl($classroom['middlePicture']);
        $classroom['largePicture'] = $this->getFileUrl($classroom['largePicture']);
        $classroom['about'] = $this->filterHtml($classroom['about']);
        return $classroom;
    }

    /**
     * Article相关
     */
    public function onArticleCreate(ServiceEvent $event)
    {
        $article = $event->getSubject();
        $schoolUtil = new MobileSchoolUtil();

        $articleApp = $schoolUtil->getArticleApp();
        $articleApp['avatar'] = $this->getAssetUrl($articleApp['avatar']);
        $article['app'] = $articleApp;

        $this->pushCloud('article.create', $this->filterArticle($article));
    }

    public function onArticleUpdate(ServiceEvent $event)
    {
        $article = $event->getSubject();
        $this->pushCloud('article.update', $this->filterArticle($article));
    }

    public function onArticleDelete(ServiceEvent $event)
    {
        $article = $event->getSubject();
        $this->pushCloud('article.delete', $this->filterArticle($article));
    }

    protected function filterArticle($article)
    {
        $article['thumb'] = $this->getFileUrl($article['thumb']);
        $article['originalThumb'] = $this->getFileUrl($article['originalThumb']);
        $article['picture'] = $this->getFileUrl($article['picture']);
        $article['body'] = $this->filterHtml($article['body']);
        return $article;
    }

    /**
     * Thread相关
     */
    public function onThreadCreate(ServiceEvent $event)
    {
        $thread = $event->getSubject();
        $this->pushCloud('thread.create', $thread);
    }

    public function onThreadUpdate(ServiceEvent $event)
    {
        $thread = $event->getSubject();
        $this->pushCloud('thread.update', $thread);
    }

    public function onThreadDelete(ServiceEvent $event)
    {
        $thread = $event->getSubject();
        $this->pushCloud('thread.delete', $thread);
    }

    /**
     * Announcement相关
     */
    public function onAnnouncementCreate(ServiceEvent $event)
    {
        $announcement = $event->getSubject();

        $target = $this->getTarget($announcement['targetType'], $announcement['targetId']);
        $announcement['target'] = $target;

        $this->pushCloud('announcement.create', $announcement);
    }

    /**
     * Testpaper相关
     */
    public function onTestPaperReviewed(ServiceEvent $event)
    {
        $testpaper = $event->getSubject();
        $result    = $event->getArgument('testpaperResult');

        $testpaper['target'] = explode('-', $testpaper['target']);
        $testpaperResultTarget = explode('-', $result['target']);
        if (empty($testpaperResultTarget[2])) {
            return;
        }

        $testpaper['target'] = $this->getTarget($testpaper['target'][0], $testpaper['target'][1]);
        $result['testpaper'] = $testpaper;

        $lesson = $this->getTarget('lesson', $testpaperResultTarget[2]);
        $result['target'] = $lesson;

        $resp = $this->pushCloud('testpaper.reviewed', $result);
        var_dump($resp);
        throw new \Exception("Error Processing Request", 1);
        
    }



    /**
     * 旧的事件发送机制
     */
    // public function onTestPaperReviewed(ServiceEvent $event)
    // {
    //     $testpaper = $event->getSubject();
    //     $result    = $event->getArgument('testpaperResult');

    //     $testpaper['target']       = explode('-', $testpaper['target']);
    //     $testpaperResult['target'] = explode('-', $result['target']);
    //     $lesson                    = $this->getCourseService()->getLesson($testpaperResult['target'][2]);
    //     $target                    = $this->getTarget($testpaper['target'][0], $testpaper['target'][1]);

    //     $from = array(
    //         'type'  => $target['type'],
    //         'id'    => $target['id'],
    //         'image' => $target['image']
    //     );

    //     $to = array('type' => 'user', 'id' => $result['userId']);

    //     $body = array(
    //         'type'     => 'testpaper.reviewed',
    //         'id'       => $result['id'],
    //         'lessonId' => $lesson['id']
    //     );

    //     $this->getCloudDataService()->push('edusoho.testpaper.reviewed', $testpaper, time());
    //     //$this->push($lesson['title'], $result['paperName'], $from, $to, $body);
    // }

    // @todo 跟峰哥确认，没有引用
    // public function onCourseLessonUnpublish(ServiceEvent $event)
    // {
    //     $lesson = $event->getSubject();
    //     $jobs   = $this->getCrontabService()->findJobByTargetTypeAndTargetId('lesson', $lesson['id']);

    //     if ($jobs) {
    //         $this->deleteJob($jobs);
    //     }
    // }

    // public function onAnnouncementCreate(ServiceEvent $event)
    // {
    //     $announcement = $event->getSubject();

    //     $target = $this->getTarget($announcement['targetType'], $announcement['targetId']);

    //     $from = array(
    //         'type'  => $target['type'],
    //         'id'    => $target['id'],
    //         'image' => $target['image']
    //     );

    //     $to = array(
    //         'type' => $target['type'],
    //         'id'   => $target['id']
    //     );

    //     $body = array(
    //         'id'   => $announcement['id'],
    //         'type' => 'announcement.create'
    //     );

    //     $this->getCloudDataService()->push('edusoho.announcement.create', $announcement, time());

    //     //$this->push($target['title'], $announcement['content'], $from, $to, $body);
    // }

    // public function onDiscountPass(ServiceEvent $event)
    // {
    //     $discount = $event->getSubject();

    //     $from = array('type' => 'global');
    //     $to   = array('type' => 'global');
    //     $body = array(
    //         'type' => 'discount.'.$discount['type']
    //     );
    //     $content;

    //     switch ($discount['type']) {
    //         case 'free':
    //             $content = "【限时免费】";
    //             break;
    //         case 'discount':
    //             $content = "【限时打折】";
    //             break;
    //         default:
    //             $content = "【全站打折】";
    //             break;
    //     }

    //     $this->push('打折活动', $content.$discount['name'], $from, $to, $body);
    // }

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

                $this->getCloudDataService()->push('edusho.course.thread.posy.create', $course, time());
                // $this->push($course['title'], $question['title'], $from, $to, $body);
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

        $this->getCloudDataService()->push('edusoho.homework.check', $homeworkResult, time());

        //$this->push($course['title'], $lesson['title'], $from, $to, $body);
    }

    // public function onCourseLessonFinish(ServiceEvent $event)
    // {
    //     $lesson = $event->getSubject();
    //     $course = $event->getArgument('course');
    //     $learn  = $event->getArgument('learn');

    //     $target = $this->getTarget('course', $learn['courseId']);
    //     $from   = array(
    //         'type'  => 'course',
    //         'id'    => $learn['courseId'],
    //         'image' => $target['image']
    //     );
    //     $to   = array('type' => 'user', 'id' => $learn['userId']);
    //     $body = array(
    //         'type'            => 'lesson.finish',
    //         'lessonId'        => $learn['lessonId'],
    //         'courseId'        => $learn['courseId'],
    //         'learnStartTime'  => $learn['startTime'],
    //         'learnFinishTime' => $learn['finishedTime']
    //     );

    //     $this->getCloudDataService()->push('edusoho.lesson.finish', $lesson, time());
    //     //$this->push($course['title'], $lesson['title'], $from, $to, $body);
    // }

    // public function onCourseLessonStart(ServiceEvent $event)
    // {
    //     $lesson = $event->getSubject();
    //     $course = $event->getArgument('course');
    //     $learn  = $event->getArgument('learn');
    //     $target = $this->getTarget('course', $learn['courseId']);
    //     $from   = array(
    //         'type'  => 'course',
    //         'id'    => $learn['courseId'],
    //         'image' => $target['image']
    //     );
    //     $to   = array('type' => 'user', 'id' => $learn['userId']);
    //     $body = array(
    //         'type'           => 'lesson.start',
    //         'lessonId'       => $learn['lessonId'],
    //         'courseId'       => $learn['courseId'],
    //         'learnStartTime' => $learn['startTime']
    //     );
    //     $this->getCloudDataService()->push('edusoho.course.lesson.start', $lesson, time());
    //     //$this->push($course['title'], $lesson['title'], $from, $to, $body);
    // }

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
                $this->getCloudDataService()->push('edusoho.course.thread.create', $thread, time());
                //$this->push($course['title'], $thread['title'], $from, $to, $body);
            }
        }
    }

    public function pushCloudData(ServiceEvent $event)
    {
        $data = $event->getSubject();
        $this->getCloudDataService()->push('edusoho.'.$event->getName(), $data, time());
    }

    // public function onProfileUpdate(ServiceEvent $event)
    // {
    //     $context = $event->getSubject();
    //     $user    = $context['user'];
    //     $this->getCloudDataService()->push('edusoho.profile.update', $user, time());
    // }

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

    protected function getTarget($type, $id)
    {
        $target = array('type' => $type, 'id' => $id);

        switch ($type) {
            case 'course':
                $course          = $this->getCourseService()->getCourse($id);
                $target['title'] = $course['title'];
                $target['image'] = $this->getFileUrl($course['smallPicture']);
                break;
            case 'lesson':
                $lesson          = $this->getCourseService()->getLesson($id);
                $target['title'] = $lesson['title'];
                break;
            case 'classroom':
                $classroom       = $this->getClassroomService()->getClassroom($id);
                $target['title'] = $classroom['title'];
                $target['image'] = $this->getFileUrl($classroom['smallPicture']);
                break;
            case 'global':
                $schoolUtil      = new MobileSchoolUtil();
                $schoolApp       = $schoolUtil->getAnnouncementApp();
                $target['title'] = '网校公告';
                $target['id']    = $schoolApp['id'];
                $target['image'] = $this->getFileUrl($schoolApp['avatar']);
                break;
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

    protected function filterHtml($text)
    {
        preg_match_all('/\<img.*?src\s*=\s*[\'\"](.*?)[\'\"]/i', $text, $matches);
        if (empty($matches)) {
            return $text;
        }

        foreach ($matches[1] as $url) {
            $text = str_replace($url, $this->getFileUrl($url), $text);
        }

        return $text;
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
