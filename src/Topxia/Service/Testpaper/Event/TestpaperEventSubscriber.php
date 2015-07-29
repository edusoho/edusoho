<?php
namespace Topxia\Service\Testpaper\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Common\StringToolkit;
use Topxia\WebBundle\Util\TargetHelper;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class TestpaperEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return array(
            'testpaper.finish' => 'onTestpaperFinish',
            'testpaper.create' => 'onTestpaperCreate',
            'testpaper.update' => 'onTestpaperUpdate',
            'testpaper.delete' => 'onTestpaperDelete',
            'testpaper.items.create' => 'onTestpaperItemsCreate',
            'testpaper.items.update' => 'onTestpaperItemsUpdate',
            'testpaper.items.delete' => 'onTestpaperItemsDelete'
        );
    }

    public function onTestpaperFinish(ServiceEvent $event)
    {
        $testpaper = $event->getSubject();
        $testpaperResult = $event->getArgument('testpaperResult');
        //TODO need to use targetHelper class for consistency
        $target = explode('-', $testpaper['target']);
        $course = $this->getCourseService()->getCourse($target[1]);
        $this->getStatusService()->publishStatus(array(
            'type' => 'finished_testpaper',
            'objectType' => 'testpaper',
            'objectId' => $testpaper['id'],
            'private' => $course['status'] == 'published' ? 0 : 1,
            'properties' => array(
                'testpaper' => $this->simplifyTestpaper($testpaper),
                'result' => $this->simplifyTestpaperResult($testpaperResult),
            )
        ));
    }

    public function onTestpaperCreate(ServiceEvent $event)
    {
        $testpaper = $event->getSubject();
        $items = $event->getArgument('items');
        $pId = $testpaper['id'];
        $courseId = explode('-',$testpaper['target'])[1];
        $courseIds = $this->getCourseService()->findCoursesByParentId($courseId);
        $testpaper['pId'] = $testpaper['id'];

        unset($testpaper['id']);        
        foreach ($courseIds as  $value) {
            $testpaper['target'] = "course-".$value;
            $testpaperId = $this->getTestpaperService()->addTestpaper($testpaper);
            foreach($items as $k=>$item)
            {
                $item['pId'] = $item['id'];
                unset($item['id']);
                $item['testId'] = $testpaperId['id'];
                $this->getTestpaperService()->createTestpaperItem($item);
            }
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($pId);
        $fields = array("score"=>$testpaper['score'],"itemCount"=>$testpaper['itemCount'],"metas"=>json_encode($testpaper['metas']));
        $this->getTestpaperService()->updateTestpaperByPid($testpaper['id'],$fields);
    }


    public function onTestpaperUpdate(ServiceEvent $event)
    {
        $testpaper = $event->getSubject();
        $pId = $testpaper['id'];
        unset($testpaper['id'],$testpaper['target'],$testpaper['createdTime'],$testpaper['updatedTime'],$testpaper['metas'],$testpaper['pId']);
        $this->getTestpaperService()->updateTestpaperByPid($pId,$testpaper);
    }

    public function onTestpaperDelete(ServiceEvent $event)
    {
       $testpaperId = $event->getSubject();
       $testpaperIds = $this->getTestpaperService()->findTestpaperIdsByPid($testpaperId);
       $this->getTestpaperService()->deleteTestpaperByPid($testpaperId);
       foreach ($testpaperIds as $value) {
          $this->getTestpaperService()->deleteTestpaperItemByTestId($value);
       }
    }

    public function onTestpaperItemsCreate(ServiceEvent $event)
    {
      $item = $event->getSubject();
      $item['pId'] = $item['id'];
      $testpaperIds = $this->getTestpaperService()->findTestpaperIdsByPid($item['testId']);
      unset($item['id']);
      foreach ($testpaperIds as $testpaperId) {
        $item['testId'] = $testpaperId;
        $this->getTestpaperService()->createTestpaperItem($item);
      }
    }

    public function onTestpaperItemsUpdate(ServiceEvent $event)
    {
        $testpaper = $event->getSubject();
        $items = $event->getArgument('items');
        //判断是否是一维数组
        if(array_key_exists('id', $items)){
            $this->getTestpaperService()->updateTestpaperItemsByPidItem($items['id'],array('seq'=>$items['seq']));
            return;
        }

        //重新生成题目
        $testpaperIds = $this->getTestpaperService()->findTestpaperIdsByPid($items[0]['testId']);
        foreach ($testpaperIds as $value) {
            $this->getTestpaperService()->deleteTestpaperItemByTestId($value);
            foreach ($items as $item) {
                $item['pId'] = $item['id'];
                unset($item['id']);
                $item['testId'] = $value;
                $this->getTestpaperService()->createTestpaperItem($item);
            }
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaper['id']);
        $fields = array("score"=>$testpaper['score'],"itemCount"=>$testpaper['itemCount'],"metas"=>json_encode($testpaper['metas']));
        $this->getTestpaperService()->updateTestpaperByPid($testpaper['id'],$fields);
    }

    public function onTestpaperItemsDelete(ServiceEvent $event)
    {
        $item = $event->getSubject();
        $this->getTestpaperService()->deleteTestpaperItemByPId($item['id']);
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
}
