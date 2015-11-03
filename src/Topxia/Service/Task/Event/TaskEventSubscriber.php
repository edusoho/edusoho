<?php
namespace Topxia\Service\Task\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class TaskEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            //'task.finished' => 'onFinished',

            'course.lesson_finish' => 'onLessonFinished',
            //'homework.finish' => 'onHomeworkFinished',
            //'homework.check' => 'onHomeworkCheck',
            'testpaper.reviewed' => 'onTestPaperFinished',
            'testpaper.finish' => 'onTestPaperFinished',
        );
    }

    public function onLessonFinished(ServiceEvent $event)
    {
        $lesson = $event->getSubject();

        $this->_finishTask('studyplan', $lesson);
    }

    public function onFinished(ServiceEvent $event)
    {
        $targetObject = $event->getSubject();
        $taskType = $event->getArgument('taskType');

        $this->_finishTask($taskType, $targetObject);
    }

    public function onHomeworkFinished(ServiceEvent $event)
    {
        $homework = $event->getSubject();
        $homeworkResult = $event->getArgument('homeworkResult');
        $targetObject = array('id'=>$homework['id'], 'type'=>'homework','passedStatus'=>$homeworkResult['passedStatus'],'userId'=>$homeworkResult['userId']);

        $this->_finishTask('studyplan', $targetObject);
    }

    public function onHomeworkCheck(ServiceEvent $event)
    {
        $homeworkResult = $event->getSubject();
        $targetObject = array('id'=>$homeworkResult['homeworkId'], 'type'=>'homework','passedStatus'=>$homeworkResult['passedStatus'],'userId'=>$homeworkResult['userId']);

        $this->_finishTask('studyplan', $targetObject);
    }

    public function onTestPaperFinished(ServiceEvent $event)
    {
        $testpaper = $event->getSubject();
        $testpaperResult = $event->getArgument('testpaperResult');
        $target = explode('-', $testpaperResult['target']);
        
        if (isset($target[3])) {
            $lesson = $this->getCourseService()->getLesson($target[3]);

            $targetObject = array('id'=>$lesson['id'], 'type'=>'testpaper', 'passedStatus'=>$testpaperResult['passedStatus'],'userId'=>$testpaperResult['userId']);
            $this->_finishTask('studyplan',$targetObject);
        }

    }

    private function _finishTask($taskType, $targetObject)
    {
        $this->getTaskService()->finishTask($targetObject, $taskType);
    }

    protected function getTaskService()
    {
        return ServiceKernel::instance()->createService('Task.TaskService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

}
