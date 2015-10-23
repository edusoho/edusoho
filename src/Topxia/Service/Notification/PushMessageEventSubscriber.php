<?php
namespace Topxia\Service\Notification;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Topxia\Api\Util\MobileSchoolUtil;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class PushMessageEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'testpaper.reviewed' => 'onTestPaperReviewed',
            'course.lesson.pubilsh' => 'onLessonPubilsh',
            'course.publish' => 'onCoursePublish',
            'course.close' => 'onCourseClose',
            'announcement.create' => 'onAnnouncementCreate',
            'classroom.join' => 'onClassroomJoin',
            'classroom.quit' => 'onClassroomQuit',
            'classroom.put_course' => 'onClassroomPutCourse',
            'article.create' => 'onArticleCreate',
            'discount.pass' => 'onDiscountPass',
            'course.join' => 'onCourseJoin',
            'course.quit' => 'onCourseQuit',
        );
    }

    public function onTestPaperReviewed(ServiceEvent $event)
    {
        $result = $event->getSubject();

        $testpaper = $this->getTestpaperService()->getTestpaper($result['testId']);
        $testpaper['target'] = explode('-', $testpaper['target']);

        $target = $this->getTarget($testpaper['target'][0], $testpaper['target'][1]);

        $from = array(
            'type' => $target['type'],
            'id' => $target['id'],
            'image' => $target['image'],
        );

        $to = array('type' => 'user', 'id' => $result['userId']);

        $body = array(
            'type' => 'testpaper.reviewed',
            'id' => $result['id']
        );

        $this->push($target['title'], $result['paperName'], $from, $to, $body);
    }

    public function onLessonPubilsh(ServiceEvent $event)
    {
        $lesson = $event->getSubject();
        $course = $this->getCourseService()->getCourse($lesson['courseId']);
        $from = array(
            'type' => 'course',
            'id' => $course['id'],
            'image' => $this->getFileUrl($course['smallPicture']),
        );

        $to = array('type' => 'course', 'id' => $course['id']);

        $body = array('type' => 'lesson.publish','id' => $lesson['id'], 'lessonType' => $lesson['type']);

        return $this->push($course['title'], $lesson['title'], $from, $to, $body);
    }

    public function onCoursePublish(ServiceEvent $event)
    {
        $course = $event->getSubject();

        $from = array(
            'type' => 'course',
            'id' => $course['id'],
            'image' => $this->getFileUrl($course['smallPicture']),
        );

        $to = array('type' => 'course', 'id' => $course['id']);

        $body = array('type' => 'course.open');

        return $this->push($course['title'], '课程被关闭!', $from, $to, $body);

    }

    public function onCourseClose(ServiceEvent $event)
    {
        $course = $event->getSubject();

        $from = array(
            'type' => 'course',
            'id' => $course['id'],
            'image' => $this->getFileUrl($course['smallPicture']),
        );

        $to = array('type' => 'course', 'id' => $course['id']);

        $body = array('type' => 'course.close');

        return $this->push($course['title'], '课程被关闭!', $from, $to, $body);

    }

    public function onAnnouncementCreate(ServiceEvent $event)
    {
        $announcement = $event->getSubject();

        $target = $this->getTarget($announcement['targetType'], $announcement['targetId']);

        $from = array(
            'type' => $target['type'],
            'id' => $target['id'],
            'image' => $target['image'],
        );

        $to = array(
            'type' => $target['type'],
            'id' => $target['id'],
        );

        $body = array(
            'id' => $announcement['id'],
            'type' => 'announcement.create'
        );

        $this->push($target['title'], $announcement['content'], $from, $to, $body);
    }

    public function onClassroomJoin(ServiceEvent $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');

        $this->addGroupMember('classroom', $classroom['id'], $userId);

        $from = array(
            'type' => 'classroom',
            'id' => $classroom['id'],
            'image' => $this->getFileUrl($classroom['smallPicture']),
        );

        $to = array(
            'type' => 'classroom',
            'id' => $classroom['id'],
        );

        $body = array('type' => 'classroom.join', 'userId' => $userId);

        $this->push($classroom['title'], '班级有新成员加入', $from, $to, $body);
    }

    public function onClassroomQuit(ServiceEvent $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');
        $this->deleteGroupMember('classroom', $classroom['id'], $userId);
    }

    public function onCourseJoin(ServiceEvent $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');
        $this->addGroupMember('course', $course['id'], $userId);
    }

    public function onCourseQuit(ServiceEvent $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');
        $this->deleteGroupMember('course', $course['id'], $userId);
    }

    public function onClassroomPutCourse(ServiceEvent $event)
    {
        $classroomCourse = $event->getSubject();

        $classroom = $this->getClassroomService()->getClassroom($classroomCourse['classroomId']);

        $from = array(
            'type' => 'classroom',
            'id' => $classroom['id'],
            'image' => $this->getFileUrl($classroom['smallPicture']),
        );

        $to = array(
            'type' => 'classroom',
            'id' => $classroom['id'],
        );

        $course = $this->getCourseService()->getCourse($classroomCourse['courseId']);

        $body = array(
            'type' => 'classroom.put_course',
            'id' => $course['id'],
            'title' => $course['title'],
            'image' => $this->getFileUrl($course['smallPicture']),
            'content' => $course['about']
        );

        $this->push($classroom['title'], '班级有新课程加入！', $from, $to, $body);

    }

    public function onArticleCreate(ServiceEvent $event)
    {
        $article = $event->getSubject();
        $schoolUtil = new MobileSchoolUtil();
        $articleApp = $schoolUtil->getArticleApp();
        $from = array(
            'id' => $articleApp['id'],
            'type' => $articleApp['code'],
            'image' => $this->getAssetUrl($articleApp['avatar'])
        );

        $to = array(
            'type' => 'global',
        );

        $body = array(
            'type' => 'news.create',
            'id' => $article['id'],
            'title' => $article['title'],
            'image' => $this->getFileUrl($article['thumb']),
            'content' => $this->plainText($article['body'], 50),
        );

        $this->push('资讯', $article['title'], $from, $to, $body);
    }

    public function onDiscountPass(ServiceEvent $event)
    {
        $discount = $event->getSubject();

        $from = array('type' => 'global');
        $to = array('type' => 'global');
        $body = array(
            'type' => 'discount.'.$discount['type']
        );
        $content;
        switch ($discount['type']) {
            case 'free':
                $content = "【限时免费】";
                break;
            case 'discount':
                $content = "【限时打折】";
                break;
            default:
                $content = "【全站打折】";
                break;
        }

        $this->push('打折活动', $content.$discount['name'], $from, $to, $body);
    }

    protected function push($title, $content, $from, $to, $body)
    {
        $message = array(
            'title' => $title,
            'content' => $content,
            'custom' => array(
                'from' => $from,
                'to' => $to,
                'body' => $body,
            )
        );

        $result = CloudAPIFactory::create('tui')->post('/message/send', $message);
    }

    protected function addGroupMember($grouType, $groupId, $memberId)
    {
        $uri = "/groups/%s-%s/members";
        $result = CloudAPIFactory::create('tui')->post(sprintf($uri, $grouType, $groupId), array(
                'memberId' => $memberId,
            ));
    }

    protected function deleteGroupMember($grouType, $groupId, $memberId)
    {
        $uri = "/groups/%s-%s/members/%s";
        $result = CloudAPIFactory::create('tui')->delete(sprintf($uri, $grouType, $groupId, $memberId));
    }

    protected function getTarget($type, $id)
    {
        $target = array('type' => $type, 'id' => $id);
        switch ($type) {
            case 'course':
                $course = $this->getCourseService()->getCourse($id);
                $target['title'] = $course['title'];
                $target['image'] = $this->getFileUrl($course['smallPicture']);
                break;
            case 'classroom':
                $classroom = $this->getClassroomService()->getClassroom($id);
                $target['title'] = $classroom['title'];
                $target['image'] = $this->getFileUrl($classroom['smallPicture']);
            case 'global':
                $schoolUtil = new MobileSchoolUtil();
                $schoolApp = $schoolUtil->getAnnouncementApp();
                $target['title'] = '网校公告';
                $target['id'] = $schoolApp['id'];
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

    protected function plainText($text)
    {
        return $text;
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

}
