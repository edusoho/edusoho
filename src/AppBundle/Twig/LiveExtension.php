<?php

namespace AppBundle\Twig;

use Biz\CloudPlatform\Client\CloudAPIIOException;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LiveExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(ContainerInterface $container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('live_can_record', array($this, 'canRecord')),
            new \Twig_SimpleFunction('is_live_finished', array($this, 'isLiveFinished')),
        );
    }

    public function canRecord($liveId)
    {
        $client = new EdusohoLiveClient();

        try {
            return (bool) $client->isAvailableRecord($liveId);
        } catch (CloudAPIIOException $cloudAPIIOException) {
            return false;
        }
    }

    public function isLiveFinished($mediaId, $type)
    {
        if ($type == 'openCourse') {
            return $this->getOpenCourseService()->isLiveFinished($mediaId);
        } else {
            return $this->getLiveCourseService()->isLiveFinished($mediaId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'live';
    }

    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    protected function getLiveCourseService()
    {
        return $this->biz->service('OpenCourse:LiveCourseService');
    }
}
