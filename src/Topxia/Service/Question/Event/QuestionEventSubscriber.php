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
        $num = count(explode('/',$question['target']));
        if($num > 1) {
            $lessonId = explode('-',explode('/', $question['target'])[1])[1];
            $lessonIds = $this->getCourseService()->findLessonsByParentId($lessonId);
        }

        if($question['parentId']){
            $questionIds = $this->getQuestionService()->findQuestionsByPId($question['parentId']);
        }
        $question['pId'] = $question['id'];
        unset($question['id']);
        if (!empty($courseIds)) {
            foreach ($courseIds as $key => $value) {
                
                if($questionIds){
                    $question['parentId'] = $questionIds[$key];
                }

                if ($num > 1) {
                    $question['target'] = "course-".$value."/lesson-".$lessonIds[$key]."";
                } else {
                    $question['target'] = "course-".$value;
                }
                $this->getQuestionService()->addQuestion($question);
            }
        }
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getQuestionService()
    {
        return ServiceKernel::instance()->createService('Question.QuestionService');
    }
}