<?php
namespace Topxia\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;

class PointEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'user.learnedLesson' => 'learnedLesson',
            'user.signed' => 'signed',
            'user.shareNote' => 'shareNote',
            'user.accomplishTest' => 'accomplishTest',
            'user.noteByLiked' => 'noteByLiked'
        );
    }

    public function learnedLesson(ServiceEvent $event)
    {
    	$param = $event->getSubject();
    	$pointSetting = $this->getSettingService()->get('point', array());
    	
    	$param['number'] = $pointSetting['accomplishLesson'];
        if($param['lessonType'] = 'testpaper') {
            return true;
        }
    	if($param['type'] == 'add') {
    		$param['description'] = '完成学习课时';
            $param['action'] = 'finish_lesson';
            $this->getUserService()->increasePoint($param['userId'], $param['number'], $param['action'], $param['description']);
    	} else if($param['type'] == 'decrease') {
    		$param['description'] = '取消学习课时';
            $param['action'] = 'cancel_lesson';
            $this->getUserService()->decreasePoint($param['userId'], $param['number'], $param['action'], $param['description']);
    	}
    }

    public function signed(ServiceEvent $event)
    {
    	$user = $this->getCurrentUser();
    	$pointSetting = $this->getSettingService()->get('point', array());
    	$this->getUserService()->increasePoint($user['id'], $pointSetting['accomplishSign'], 'finish_sign', '完成签到');
    }

    public function accomplishTest(ServiceEvent $event)
    {
        $param = $event->getSubject();
        $pointSetting = $this->getSettingService()->get('point', array());
    	$testResults = $this->getTestPaperService()->
            findAllTestpaperResultsByTestIdAndStatusAndUserId($param['testPaperId'], $param['userId'], array('finished'));

        if(count($testResults) < 2) {
            $this->getUserService()->increasePoint($param['userId'], $pointSetting['accomplishTest'], 'finish_test', '完成测试');
        }
    }

    public function shareNote(ServiceEvent $event)
    {
        $param = $event->getSubject();
        $pointSetting = $this->getSettingService()->get('point', array());

        $number = $pointSetting['shareNote'];
        if($param['type'] == 'add') {
            $this->getUserService()->increasePoint($param['userId'], $number, 'share_note', '分享笔记');
        } else if($param['type'] == 'decrease') {
             $this->getUserService()->decreasePoint($param['userId'], $number, 'cancel_share_note', '取消分享笔记');
        }
       
    }
    
    public function noteByLiked(ServiceEvent $event)
    {
        $param = $event->getSubject();
        $pointSetting = $this->getSettingService()->get('point', array());

        $number = $pointSetting['noteByLiked'];
        if($param['type'] == 'add') {
            $this->getUserService()->increasePoint($param['userId'], $number, 'note_by_liked', '笔记被赞');
        } else if($param['type'] == 'decrease') {
             $this->getUserService()->decreasePoint($param['userId'], $number, 'note_cancel_liked', '笔记被取消赞');
        }
    }

    public function getCurrentUser()
    {
    	return $this->getUserService()->getCurrentUser();
    }

    public function getUserService()
    {
    	return ServiceKernel::instance()->createService('User.UserService');
    }

    public function getSettingService()
    {
    	return ServiceKernel::instance()->createService('System.SettingService');
    }

    public function getTestPaperService()
    {
        return ServiceKernel::instance()->createService('Testpaper.TestpaperService');
    }
}