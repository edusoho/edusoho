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
            'testpaper.items.create' => 'onTestpaperItemsCreate',
            'testpaper.items.update' => 'onTestpaperItemsUpdate'
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
        $parentId = $testpaper['id'];
        $courseId = explode('-',$testpaper['target'])[1];
        $courseIds = $this->getCourseService()->findCoursesByParentId($courseId);
        $testpaper['parentId'] = $testpaper['id'];

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

        $testpaper = $this->getTestpaperService()->getTestpaper($parentId);
        $fields = array("score"=>$testpaper['score'],"itemCount"=>$testpaper['itemCount'],"metas"=>json_encode($testpaper['metas']));
        $this->getTestpaperService()->updateTestpaperByParentId($testpaper['id'],$fields);
    }


    public function onTestpaperUpdate(ServiceEvent $event)
    {
        $testpaper = $event->getSubject();
        $parentId = $testpaper['id'];
        unset($testpaper['id'],$testpaper['target'],$testpaper['createdTime'],$testpaper['updatedTime'],$testpaper['metas'],$testpaper['parentId']);
        $this->getTestpaperService()->updateTestpaperByParentId($parentId,$testpaper);
    }

    public function onTestpaperItemsCreate(ServiceEvent $event)
    {
      $item = $event->getSubject();
      $item['pId'] = $item['id'];
      $testpaperIds = $this->getTestpaperService()->findTestpaperIdsByParentId($item['testId']);
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

        foreach ($items as $item) {
            $this->getTestpaperService()->updateTestpaperItemsByQuestionIdAndItem($item['questionId'],$item);
        }
        $fields = array("score"=>$testpaper['score'],"itemCount"=>$testpaper['itemCount'],"metas"=>json_encode($testpaper['metas']));
        $this->getTestpaperService()->updateTestpaperByParentId($testpaper['id'],$fields);
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
