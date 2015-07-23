<?php
namespace Topxia\Service\Question\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class QuestionEventSubscriber implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
    {
        return array(
            'question.create'=> 'onQuestionCreate'
        );
    }

    public function onQuestionCreate(ServiceEvent $event)
    {
        $question = $event->getSubject();
        $courseId = explode('-',explode('/', $question['target'])[0])[1];
        $courseIds = $this->getCourseService()->findCoursesByParentId($courseId);
        unset($question['id'],$question['target']);
        if (!empty($courseIds)) {
            foreach ($courseIds as $key => $value) {
                $question['target'] = "course-".$value;
                if (count(explode('/',$question['target'])) > 1) {
                    $lessonId = explode('-',explode('/', $question['target'])[1])[1];
                    $lessonIds = $this->getCourseService()->findLessonsByParentId($lesson['id']);
                    $question['target'] = "course-".$value."/lesson-".$lessonIds[$key]."";
                }
                $this->getQuestionService()->createQuestion($question);
            }
        }
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }
}