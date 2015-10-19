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

            'course.lesson_finish' => 'onFinished',
            'homework.finish' => 'onHomeworkFinished',
            'homework.check' => 'onFinished',
            'testpaper.reviewed' => 'onTestPaperFinished',
            'testpaper.finish' => 'onTestPaperFinished',
        );
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
        $targetObject = array('id'=>$homework['id'], 'type'=>'homework','passedStatus'=>$homeworkResult['passedStatus']);

        $this->_finishTask('studyPlan', $targetObject);
    }

    public function onTestPaperFinished(ServiceEvent $event)
    {
        $testpaper = $event->getSubject();
        $testpaperResult = $event->getArgument('testpaperResult');
        $target = explode('-', $testpaperResult['target']);
        
        if (isset($target[3])) {
            $lesson = $this->getCourseService()->getLesson($target[3]);

            $targetObject = array('id'=>$lesson['id'], 'type'=>'testpaper', 'passedStatus'=>$testpaperResult['passedStatus']);
            $this->_finishTask('studyPlan',$targetObject);
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
