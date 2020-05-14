<?php

namespace Biz\S2B2C\Event;

use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use QiQiuYun\SDK\Service\S2B2CService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseProductSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'course.update' => 'onCourseUpdate',
        ];
    }

    public function onCourseUpdate(Event $event)
    {
        $course = $event->getSubject();
        if ($this->isSupplierCourse($course)) {
            $courseProduct = $this->getS2b2cProductService()->getByTypeAndLocalResourceId('course', $course['id']);
            $this->getS2B2CService()->changeProductSellingPrice($courseProduct['remoteProductId'], 'course', $course['price']);
        }
    }

    protected function isSupplierCourse($course)
    {
        return 'supplier' === $course['platform'];
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

    /**
     * @return ProductService
     */
    protected function getS2b2cProductService()
    {
        return $this->getBiz()->service('S2B2C:ProductService');
    }
}
