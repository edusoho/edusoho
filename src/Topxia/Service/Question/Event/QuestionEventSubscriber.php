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
            'question.create' => 'onQuestionCreate',
            'question.update' => 'onQuestionUpdate'
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
            $parentQuestion = $this->getQuestionService()->getQuestion($question['parentId']);
        }
        $question['pId'] = $question['id'];
        unset($question['id']);
        if (!empty($courseIds)) {
            foreach ($courseIds as $key => $value) {
                
                if($question['parentId']){
                    $question['parentId'] = $questionIds[$key];
                    $this->getQuestionService()->editQuestion($questionIds[$key],array('subCount'=>$parentQuestion['subCount']));
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

    public function onQuestionUpdate(ServiceEvent $event)
    {
        $question = $event->getSubject();
        $questionIds = $this->getQuestionService()->findQuestionsByPId($question['id']);
        unset($question['id'],$question['target'],$question['parentId'],$question['pId']);
        foreach ($questionIds as $key => $value) {
            $this->getQuestionService()->editQuestion($questionIds[$key],$question);
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