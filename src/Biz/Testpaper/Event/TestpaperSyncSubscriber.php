<?php
/**
 * Created by PhpStorm.
 * User: malianbo
 * Date: 17/2/5
 * Time: 16:33
 */

namespace Biz\Testpaper\Event;

use Biz\Testpaper\Dao\TestpaperDao;
use Biz\Testpaper\Dao\TestpaperItemDao;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Event\CourseSyncSubscriber;

class TestpaperSyncSubscriber extends CourseSyncSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            'testpaper.create'       => 'onTestpaperCreate',
            'testpaper.update'       => 'onTestpaperUpdate',
            'testpaper.delete'       => 'onTestpaperDelete',

            'testpaper.item.create'  => 'onTestpaperItemCreate',
            'testpaper.item.update'  => 'onTestpaperItemUpdate',
            'testpaper.item.delete'  => 'onTestpaperItemDelete'
        );
    }

    public function onTestpaperCreate(Event $event)
    {
        $testpaper = $event->getSubject();
        if($testpaper['copyId'] > 0){
            return;
        }
        $copiedCourseSets = $this->getCourseSetDao()->findCourseSetsByParentIdAndLocked($testpaper['courseSetId'], 1);
        if(empty($copiedCourseSets)){
            return;
        }
//        $this->getTestpaperDao()->findTestpapersByCopyIdAndLockedTarget()
    }

    public function onTestpaperUpdate(Event $event)
    {

    }

    public function onTestpaperDelete(Event $event)
    {

    }

    public function onTestpaperItemCreate(Event $event)
    {

    }

    public function onTestpaperItemUpdate(Event $event)
    {

    }

    public function onTestpaperItemDelete(Event $event)
    {

    }

    /**
     * @return TestpaperDao
     */
    protected function getTestpaperDao()
    {
        return $this->getBiz()->dao('Testpaper:TestpaperDao');
    }

    /**
     * @return TestpaperItemDao
     */
    protected function getTestpaperItemDao()
    {
        return $this->getBiz()->dao('Testpaper:TestpaperItemDao');
    }
}