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
        $context = $event->getSubject();
        $question = $context['question'];
        $argument = $context['argument'];
        
        $questionTarget = explode('/', $question['target']);
        $questionCourseTarget = explode('-',$questionTarget[0]);
        $courseId = $questionCourseTarget[1];

        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        if($courseIds){
            $num = count(explode('/',$question['target']));
            if($num > 1) {
                $questionLessonTarget = explode('-',$questionTarget[1]);
                $lessonId = $questionLessonTarget[1];
                $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($lessonId,$courseIds),'id');
            }

            //材料题
            if($argument['parentId']){
                $lockedTarget= '';
                foreach ($courseIds as $key => $courseId) {
                    if ($num > 1) {
                        $lockedTarget .= "'course-".$courseId."/lesson-".$lessonIds[$key]."',";
                   } else {
                        $lockedTarget .= "'course-".$courseId."',";
                    }
                }
                $lockedTarget = "(".trim($lockedTarget,',').")";
                $questionIds = ArrayToolkit::column($this->getQuestionService()->findQuestionsByCopyIdAndLockedTarget($question['parentId'],$lockedTarget),'id');
            }

            $argument['copyId'] = $question['id'];
            foreach ($courseIds as $key => $courseId) {
                if($argument['parentId']) {
                    $argument['parentId'] = $questionIds[$key];
                }
                if ($num > 1) {
                    $argument['target'] = "course-".$courseId."/lesson-".$lessonIds[$key]."";
                } else {
                    $argument['target'] = "course-".$courseId;
                }
                $this->getQuestionService()->createQuestion($argument);
            }
        }
    }

    public function onQuestionUpdate(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $question = $context['question'];
        $argument = $context['argument'];
        $copyId = $question['id'];
        $questionOldTarget = explode('/', $argument['question']['target']);
        $questionOldCourseTarget = explode('-',$questionOldTarget[0]);
        $courseId = $questionOldCourseTarget[1];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        if($courseIds){
            //获取修改从属于
            $num = count(explode('/',$question['target']));
            if($num > 1) {
                $questionTarget = explode('/', $question['target']);
                $questionLessonTarget = explode('-',$questionTarget[1]);
                $lessonId = $questionLessonTarget[1];
                $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($lessonId,$courseIds),'id');
            }
            //获取要修改从属于
            $oldNum = count(explode('/',$argument['question']['target']));
            if($oldNum > 1){
                $questionOldLessonTarget = explode('-',$questionOldTarget[1]);
                $oldLessonId = $questionOldLessonTarget[1];
                $oldLessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($lessonId,$courseIds),'id');
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
            $questionIds = ArrayToolkit::column($this->getQuestionService()->findQuestionsByCopyIdAndLockedTarget($question['id'],$lockedTarget),'id');
            foreach ($questionIds as $key => $questionId) {
                if ($num > 1) {
                    $argument['fields']['target'] = "course-".$courseIds[$key]."/lesson-".$lessonIds[$key]."";
                } else {
                    $argument['fields']['target'] = "course-".$courseIds[$key];
                }

                $this->getQuestionService()->updateQuestion($questionId,$argument['fields']);
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
                $lessonIds = ArrayToolkit::column($this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($lessonId,$courseIds),'id');
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
            $questionIds = ArrayToolkit::column($this->getQuestionService()->findQuestionsByCopyIdAndLockedTarget($question['id'],$lockedTarget),'id');
            foreach ($questionIds as  $questionId) {
                $this->getQuestionService()->deleteQuestion($questionId);
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