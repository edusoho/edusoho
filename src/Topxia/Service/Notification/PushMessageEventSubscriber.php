<?php
namespace Topxia\Service\Notification;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\CloudPlatform\CloudAPIFactory;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class PushMessageEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'testpaper.reviewed' => 'onTestPaperReviewed',
            'course.lesson.create' => 'onLessonCreate',
            'course.publish' => 'onCoursePublish',
            'course.close' => 'onCourseClose',
            'announcement.create' => 'onAnnouncementCreate',
            'classroom.join' => 'onClassroomJoin',
            'classroom.put_course' => 'onClassroomPutCourse',
            'article.create' => 'onArticleCreate',
            'discount.start' => 'onDiscountStart',
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
            'image' => $this->getFileUrl($target['image']),
        );

        $to = array('type' => 'user', 'id' => $result['userId']);

        $body = array(
            'type' => 'testpaper.reviewed',
            'resultId' => $result['id'],
            'testpaperId' => $result['testId'],
            'score' => $result['score'],
            'teacherSay' => $result['teacherSay'],
        );

        $this->push($target['title'], "试卷《{$result['paperName']}》批阅完成！", $from, $to, $body);
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
            'type' => 'announcement.create'
        );

        return $this->push($target['title'], $announcement['content'], $from, $to, $body);
    }

    public function onClassroomJoin(ServiceEvent $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');

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
            'summary' => $course['about'],
        );

        $this->push($classroom['title'], '班级有新课程加入！', $from, $to, $body);

    }

    public function onArticleCreate(ServiceEvent $event)
    {
        $article = $event->getSubject();

        $from = array(
            'type' => 'news',
        );

        $to = array(
            'type' => 'global',
        );

        $body = array(
            'type' => 'news.create',
            'id' => $article['id'],
            'title' => $article['title'],
            'image' => $this->getFileUrl($article['thumb']),
            'summary' => $this->plainText($article['body'], 50),
        );

        $this->push('资讯', '网校有新资讯!', $from, $to, $body);
    }

    public function onDiscountStart(ServiceEvent $event)
    {
        $discount = $event->getSubject();

        $from = array('type' => 'school');
        $to = array('type' => 'school');
        $body = array('type' => 'discount.start');

        $this->push('公告', $discount['name'], $from, $to, $body);
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

         CloudAPIFactory::create('tui')->post('/message/send', $message);

        file_put_contents('/tmp/push_message', json_encode($message, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE). "\n\n", FILE_APPEND);
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
                $target['title'] = '网校公告';
                $target['image'] = '';
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
        $path = "http://{$_SERVER['HTTP_HOST']}/files/{$path}";
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
