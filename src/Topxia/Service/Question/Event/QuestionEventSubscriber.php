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
        $questionTarget = explode('/', $question['target']);
        $questionCourseTarget = explode('-',$questionTarget[0]);
        $courseId = $questionCourseTarget[1];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        if($courseIds){
            $num = count(explode('/',$question['target']));
            if($num > 1) {
                $questionLessonTarget = explode('-',$questionTarget[1]);
                $lessonId = $questionLessonTarget[1];
                $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByParentIdAndLockedCourseIds($lessonId,$courseIds),'id');
            }
            //材料题
            if($question['parentId']){
                $lockedTarget= '';
                foreach ($courseIds as $key => $courseId) {
                    if ($num > 1) {
                        $lockedTarget .= "'course-".$courseId."/lesson-".$lessonIds[$key]."',";
                    } else {
                        $lockedTarget .= "'course-".$courseId."',";
                    }
                }
                $lockedTarget = "(".trim($lockedTarget,',').")";
                $questionIds = ArrayToolkit::column($this->getQuestionService()->findQuestionsByPIdAndLockedTarget($question['parentId'],$lockedTarget ),'id');
                $parentQuestion = $this->getQuestionService()->getQuestion($question['parentId']);
                foreach ($questionIds as $questionId) {
                    $this->getQuestionService()->editQuestion($questionId,array('subCount'=>$parentQuestion['subCount']));
                }
            }
            $question['pId'] = $question['id'];
            unset($question['id']);
            foreach ($courseIds as $key => $courseId) {
                if($question['parentId']) {
                    $question['parentId'] = $questionIds[$key];
                }
                if ($num > 1) {
                    $question['target'] = "course-".$courseId."/lesson-".$lessonIds[$key]."";
                } else {
                    $question['target'] = "course-".$courseId;
                }
                $question['createdTime'] = time();
                $question['updatedTime'] = time();
                $this->getQuestionService()->addQuestion($question);
            }
        }
    }

    public function onQuestionUpdate(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $question = $context['question'];
        $oldTarget = $context['oldTarget'];
        $pId = $question['id'];
        $questionOldTarget = explode('/', $oldTarget);
        $questionOldCourseTarget = explode('-',$questionOldTarget[0]);
        $courseId = $questionOldCourseTarget[1];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        if($courseIds){
            $num = count(explode('/',$question['target']));
            if($num > 1) {
                $questionTarget = explode('/', $question['target']);
                $questionLessonTarget = explode('-',$questionTarget[1]);
                $lessonId = $questionLessonTarget[1];
                $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByParentIdAndLockedCourseIds($lessonId,$courseIds),'id');
            }
            $oldNum = count(explode('/',$oldTarget));
            if($oldNum > 1){
                $questionOldLessonTarget = explode('-',$questionOldTarget[1]);
                $oldLessonId = $questionOldLessonTarget[1];
                $oldLessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByParentIdAndLockedCourseIds($lessonId,$courseIds),'id');
            }
            $lockedTarget = '';
            foreach ($courseIds as $key => $courseId) {
                if ($oldNum > 1) {
                    $lockedTarget .= "'course-".$courseId."/lesson-".$oldLessonIds[$key]."',";
                } else {
                    $lockedTarget .= "'course-".$courseId."',";
                }
            }
            $lockedTarget = "(".trim($lockedTarget,',').")";
            $questionIds = ArrayToolkit::column($this->getQuestionService()->findQuestionsByPIdAndLockedTarget($question['id'],$lockedTarget),'id');
            unset($question['id'],$question['parentId'],$question['pId'],$question['createdTime']); 
            foreach ($questionIds as $key => $questionId) {
                if ($num > 1) {
                    $question['target'] = "course-".$courseIds[$key]."/lesson-".$lessonIds[$key]."";
                } else {
                    $question['target'] = "course-".$courseIds[$key];
                }

                $this->getQuestionService()->editQuestion($questionId,$question);
            }
        }   
    }

    public function onQuestionDelete(ServiceEvent $event)
    {
        $question = $event->getSubject();
        $questionTarget = explode('/', $question['target']);
        $questionCourseTarget = explode('-',$questionTarget[0]);
        $courseId = $questionCourseTarget[1];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        if($courseIds){
            $num = count(explode('/',$question['target']));
            if($num > 1) {
                $questionLessonTarget = explode('-',$questionTarget[1]);
                $lessonId = $questionLessonTarget[1];
                $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByParentIdAndLockedCourseIds($lessonId,$courseIds),'id');
            }
            $lockedTarget= '';
            foreach ($courseIds as $key => $courseId) {
                if ($num > 1) {
                    $lockedTarget .= "'course-".$courseId."/lesson-".$lessonIds[$key]."',";
                } else {
                    $lockedTarget .= "'course-".$courseId."',";
                }
            }
            $lockedTarget = "(".trim($lockedTarget,',').")";
            $questionIds = ArrayToolkit::column($this->getQuestionService()->findQuestionsByPIdAndLockedTarget($question['id'],$lockedTarget),'id');
            foreach ($questionIds as  $questionId) {
                $question = $this->getQuestionService()->getQuestion($questionId);
                $this->getQuestionService()->deleteQuestion($questionId);
                if ($question['subCount'] > 0) {
                    $this->getQuestionService()->deleteQuestionsByParentId($questionId);
                }
                if ($question['parentId'] > 0) {
                    $subCount = $this->getQuestionService()->findQuestionsCountByParentId($question['parentId']);
                    $this->getQuestionService()->editQuestion($question['parentId'], array('subCount' => $subCount));
                }
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