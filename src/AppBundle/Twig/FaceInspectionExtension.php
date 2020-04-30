<?php

namespace AppBundle\Twig;

use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\FaceInspection\Service\FaceInspectionService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FaceInspectionExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct($container ,Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('is_facein_open', array($this, 'isFaceInspectionOpen')),
            new \Twig_SimpleFunction('is_scene_facein_open', array($this, 'isSceneFaceInspectionOpen')),
        );
    }

    public function isFaceInspectionOpen()
    {
        $setting = $this->getSettingService()->get('cloud_facein', array());
        if (!empty($setting['enabled'])) {
            return true;
        }

        return false;
    }

    public function isSceneFaceInspectionOpen($sceneId)
    {
        $setting = $this->getSettingService()->get('cloud_facein', array());
        if (empty($setting['enabled'])) {
            return false;
        }

        $scene = $this->getAnswerSceneService()->get($sceneId);
        if ($scene['enable_facein'] != 1) {
            return false;
        }

        return true;
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return AnswerSceneService
     */
    private function getAnswerSceneService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerSceneService');
    }
}