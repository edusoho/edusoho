<?php

namespace Biz\S2B2C\Event;

use Biz\S2B2C\Service\CourseProductService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use QiQiuYun\SDK\Service\S2B2CService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseProductSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.create' => 'onCourseCreate',
            'course.update' => 'onCourseUpdate',
        );
    }

    public function onCourseUpdate(Event $event)
    {
        $course = $event->getSubject();
        if ($this->isSupplierCourse($course)) {
            $this->getS2B2CService()->changeProductSellingPrice($course['sourceCourseId'], 'course', $course['price']);
        }
    }

    public function onCourseCreate(Event $event)
    {
        $course = $event->getSubject();
        if ($this->isSupplierCourse($course)) {
            $this->getCourseProductService()->syncCourse($course['id']);
        }
    }

    protected function isSupplierCourse($course)
    {
        return !empty($course['sourceCourseId']);
    }

    /**
     * @return CourseProductService
     */
    protected function getCourseProductService()
    {
        return $this->getBiz()->service('S2B2C:CourseProductService');
    }

    /**
     * @return S2B2CService
     */
    protected function getS2B2CService()
    {
        return $this->getBiz()->offsetGet('qiQiuYunSdk.s2b2cService');
    }
}
