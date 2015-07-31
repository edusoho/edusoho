<?php
namespace Topxia\Service\Question\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class QuestionEventSubscriber implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
    {
        return array(
            'question.create' => 'onQuestionCreate',
            'question.update' => 'onQuestionUpdate',
            'question.delete' => 'onQuestionDelete'
        );
    }

    public function onQuestionCreate(ServiceEvent $event)
    {
        $question = $event->getSubject();
        $courseId = explode('-',explode('/', $question['target'])[0])[1];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentId($courseId),'id');
        $num = count(explode('/',$question['target']));
        if($num > 1) {
            $lessonId = explode('-',explode('/', $question['target'])[1])[1];
            $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByParentId($lessonId),'id');
        }

        if($question['parentId']){
            $questionIds = ArrayToolkit::column($this->getQuestionService()->findQuestionsByPId($question['parentId']),'id');
            $parentQuestion = $this->getQuestionService()->getQuestion($question['parentId']);
        }
        $question['pId'] = $question['id'];
        unset($question['id']);
        if (!empty($courseIds)) {
            foreach ($courseIds as $key => $courseId) {
                
                if($question['parentId']){
                    $question['parentId'] = $questionIds[$key];
                    $this->getQuestionService()->editQuestion($questionIds[$key],array('subCount'=>$parentQuestion['subCount']));
                }

                if ($num > 1) {
                    $question['target'] = "course-".$courseId."/lesson-".$lessonIds[$key]."";
                } else {
                    $question['target'] = "course-".$courseId;
                }
                
                $this->getQuestionService()->addQuestion($question);
            }
        }
    }

    public function onQuestionUpdate(ServiceEvent $event)
    {
        $question = $event->getSubject();
        $questionIds = ArrayToolkit::column($this->getQuestionService()->findQuestionsByPId($question['parentId']),'id');
        unset($question['id'],$question['target'],$question['parentId'],$question['pId']);
        foreach ($questionIds as $value) {
            $this->getQuestionService()->editQuestion($value,$question);
        }

    }

    public function onQuestionDelete(ServiceEvent $event)
    {
         $questionId = $event->getSubject();
         $questionIds = ArrayToolkit::column($this->getQuestionService()->findQuestionsByPId($question['parentId']),'id');
        foreach ($questionIds as  $value) {
            $question = $this->getQuestionService()->getQuestion($value);
            $this->getQuestionService()->deleteQuestion($value);
            if ($question['subCount'] > 0) {
                $this->getQuestionService()->deleteQuestionsByParentId($value);
            }
            if ($question['parentId'] > 0) {
                $subCount = $this->getQuestionService()->findQuestionsCountByParentId($question['parentId']);
                $this->getQuestionService()->editQuestion($question['parentId'], array('subCount' => $subCount));
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