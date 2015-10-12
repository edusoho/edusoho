<?php
namespace Topxia\Service\Testpaper\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\WebBundle\Util\TargetHelper;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\ArrayToolkit;

class TestpaperEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'testpaper.finish' => 'onTestpaperFinish',
            'testpaper.create' => 'onTestpaperCreate',
            'testpaper.update' => 'onTestpaperUpdate',
            'testpaper.publish'=> 'onTestpaperPublish',
            'testpaper.close'=>'onTestpaperClose',
            'testpaper.delete' => 'onTestpaperDelete',
            'testpaper.item.create' => 'onTestpaperItemCreate',
            'testpaper.item.update' => 'onTestpaperItemUpdate',
            'testpaper.item.delete' => 'onTestpaperItemDelete'
        );
    }

    public function onTestpaperFinish(ServiceEvent $event)
    {
        $testpaper = $event->getSubject();
        $testpaperResult = $event->getArgument('testpaperResult');
        //TODO need to use targetHelper class for consistency
        $target = explode('-', $testpaper['target']);
        $course = $this->getCourseService()->getCourse($target[1]);
        $private = $course['status'] == 'published' ? 0 :1;
        if($course['parentId']){ 
            $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']); 
            $classroom = $this->getClassroomService()->getClassroom($classroom['classroomId']);
            if(array_key_exists('showable',$classroom) && $classroom['showable']== 1) {
                $private = 0;
            }else{
                $private = 1;
            }
        }
        $this->getStatusService()->publishStatus(array(
            'type' => 'finished_testpaper',
            'objectType' => 'testpaper',
            'objectId' => $testpaper['id'],
            'private' => $private,
            'properties' => array(
                'testpaper' => $this->simplifyTestpaper($testpaper),
                'result' => $this->simplifyTestpaperResult($testpaperResult),
            )
        ));
    }

    public function onTestpaperCreate(ServiceEvent $event)
    {
        $testpaper = $event->getSubject();
        $testpaperTarget = explode('-',$testpaper['target']);
        $courseId = $testpaperTarget[1];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        if($courseIds){
           $testpaper['pId'] = $testpaper['id'];
           unset($testpaper['id']);
           foreach ($courseIds as  $courseId) {
                $testpaper['target'] = "course-".$courseId;
                $addtestpaper = $this->getTestpaperService()->addTestpaper($testpaper);
            } 
        }
    }


    public function onTestpaperUpdate(ServiceEvent $event)
    {
        $testpaper = $event->getSubject();
        $testpaperTarget = explode('-',$testpaper['target']);
        $courseId = $testpaperTarget[1];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        
        if($courseIds){
            $lockedTarget = '';
            foreach ($courseIds as $courseId) {
                    $lockedTarget .= "'course-".$courseId."',";
            }
            $lockedTarget = "(".trim($lockedTarget,',').")";
            $testpaperIds = ArrayToolkit::column($this->getTestpaperService()->findTestpapersByPIdAndLockedTarget($testpaper['id'],$lockedTarget),'id');
            unset($testpaper['id'],$testpaper['target'],$testpaper['createdTime'],$testpaper['updatedTime'],$testpaper['pId']);
            foreach ($testpaperIds as $testpaperId) {
               $this->getTestpaperService()->editTestpaper($testpaperId,$testpaper);
            }  
        }
    }

    public function onTestpaperPublish(ServiceEvent $event)
    {
        $testpaper = $event->getSubject();
        $testpaperTarget = explode('-',$testpaper['target']);
        $courseId = $testpaperTarget[1];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        if($courseIds){
            $lockedTarget = '';
            foreach ($courseIds as $courseId) {
                    $lockedTarget .= "'course-".$courseId."',";
            }
            $lockedTarget = "(".trim($lockedTarget,',').")";
            $testpaperIds = ArrayToolkit::column($this->getTestpaperService()->findTestpapersByPIdAndLockedTarget($testpaper['id'],$lockedTarget),'id');
            foreach ($testpaperIds as $testpaperId) {
               $this->getTestpaperService()->publishTestpaper($testpaperId);
            }  
        }
    }

    public function onTestpaperClose(ServiceEvent $event)
    {
        $testpaper = $event->getSubject();
        $testpaperTarget = explode('-',$testpaper['target']);
        $courseId = $testpaperTarget[1];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        if($courseIds){
            $lockedTarget = '';
            foreach ($courseIds as $courseId) {
                    $lockedTarget .= "'course-".$courseId."',";
            }
            $lockedTarget = "(".trim($lockedTarget,',').")";
            $testpaperIds = ArrayToolkit::column($this->getTestpaperService()->findTestpapersByPIdAndLockedTarget($testpaper['id'],$lockedTarget),'id');
            foreach ($testpaperIds as $testpaperId) {
               $this->getTestpaperService()->closeTestpaper($testpaperId);
            }  
        }

    }

    public function onTestpaperDelete(ServiceEvent $event)
    {
       $testpaper = $event->getSubject();
       $testpaperId = $testpaper['id'];
       $testpaperTarget = explode('-',$testpaper['target']);
       $courseId = $testpaperTarget[1];
       $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
       if($courseIds){
            $lockedTarget = '';
            foreach ($courseIds as $courseId) {
                    $lockedTarget .= "'course-".$courseId."',";
            }
            $lockedTarget = "(".trim($lockedTarget,',').")";
            $testpaperIds = ArrayToolkit::column($this->getTestpaperService()->findTestpapersByPIdAndLockedTarget($testpaperId,$lockedTarget),'id');
            foreach ($testpaperIds as $testpaperId) {
              $this->getTestpaperService()->deleteTestpaper($testpaperId);
            }
       }
    }

    public function onTestpaperItemCreate(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $item = $context['item'];
        $testpaper = $context['testpaper'];
        $testpaperTarget = explode('-',$testpaper['target']);
        $courseId = $testpaperTarget[1];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        if($courseIds){
            $lockedTarget = '';
            foreach ($courseIds as $courseId) {
                $lockedTarget .= "'course-".$courseId."',";
            }
            $lockedTarget = "(".trim($lockedTarget,',').")";
            $testpaperIds = ArrayToolkit::column($this->getTestpaperService()->findTestpapersByPIdAndLockedTarget($testpaper['id'],$lockedTarget),'id');
            $item['pId'] = $item['id'];
            unset($item['id']);
            foreach ($testpaperIds as $testpaperId) {
                $item['testId'] = $testpaperId;
                $this->getTestpaperService()->createTestpaperItem($item);
            }
        }
    }

    public function onTestpaperItemUpdate(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $item = $context['item'];
        $testpaper = $context['testpaper'];
        $testpaperTarget = explode('-',$testpaper['target']);
        $courseId = $testpaperTarget[1];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');
        if($courseIds){
            $lockedTarget = '';
            foreach ($courseIds as $courseId) {
                $lockedTarget .= "'course-".$courseId."',";
            }
            $lockedTarget = "(".trim($lockedTarget,',').")";
            $testpaperIds = ArrayToolkit::column($this->getTestpaperService()->findTestpapersByPIdAndLockedTarget($testpaper['id'],$lockedTarget),'id');
            $testpaperItemIds = ArrayToolkit::column($this->getTestpaperService()->findTestpaperItemsByPIdAndLockedTestIds($item['id'],$testpaperIds),'id');
            $fields = array('seq'=>$item['seq'],'questionId'=>$item['questionId'],'questionType'=>$item['questionType'],'score'=>$item['score'],'missScore'=>$item['missScore']);
            foreach ($testpaperItemIds as $testpaperItemId) {
                $this->getTestpaperService()->editTestpaperItem($testpaperItemId, $fields);
            }
        }
    }

    public function onTestpaperItemDelete(ServiceEvent $event)
    {
        $context = $event->getSubject();
        $item = $context['item'];
        $testpaper = $context['testpaper'];
        
        $testpaperTarget = explode('-',$testpaper['target']);
        $courseId = $testpaperTarget[1];
        $courseIds = ArrayToolkit::column($this->getCourseService()->findCoursesByParentIdAndLocked($courseId,1),'id');        
    
        if($courseIds){
            $lockedTarget = ''; 
            foreach ($courseIds as $courseId) {
                $lockedTarget .= "'course-".$courseId."',";
            }       
            $lockedTarget = "(".trim($lockedTarget,',').")";
            $testpaperIds = ArrayToolkit::column($this->getTestpaperService()->findTestpapersByPIdAndLockedTarget($testpaper['id'],$lockedTarget),'id');  
            //重新生题,删除试卷已存在的题目
            if(!$item){
                foreach ($testpaperIds as $testpaperId) {
                    $this->getTestpaperService()->deleteTestpaperItemByTestId($testpaperId); 
                }
                return;  
            }
            $testpaperItemIds = ArrayToolkit::column($this->getTestpaperService()->findTestpaperItemsByPIdAndLockedTestIds($item['id'],$testpaperIds),'id');
            foreach ($testpaperItemIds as $testpaperItemId) {
                $this->getTestpaperService()->deleteTestpaperItem($testpaperItemId);
            }
        }  
    }

    protected function simplifyTestpaper($testpaper)
    {
        return array(
            'id' => $testpaper['id'],
            'name' => $testpaper['name'],
            'description' => StringToolkit::plain($testpaper['description'], 100),
            'score' => $testpaper['score'],
            'passedScore' => $testpaper['passedScore'],
            'itemCount' => $testpaper['itemCount'],
        );
    }

    protected function simplifyTestpaperResult($testpaperResult)
    {
        return array(
            'id' => $testpaperResult['id'],
            'score' => $testpaperResult['score'],
            'objectiveScore' => $testpaperResult['objectiveScore'],
            'subjectiveScore' => $testpaperResult['subjectiveScore'],
            'teacherSay' => StringToolkit::plain($testpaperResult['teacherSay'], 100),
            'passedStatus' => $testpaperResult['passedStatus'],
        );
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getStatusService()
    {
        return ServiceKernel::instance()->createService('User.StatusService');
    }

    protected function getTestpaperService()
    {
        return ServiceKernel::instance()->createService('Testpaper.TestpaperService');
    }

    private function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }
}
