<?php

namespace AppBundle\Twig;

use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SearchExtension extends \Twig_Extension
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

    public function getFilters()
    {
        return array();
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('show_search_placeholder', array($this, 'showSearchPlaceholder')),
        );
    }

    public function showSearchPlaceholder()
    {
        $cloudSearchSetting = $this->getSettingService()->get('cloud_search', array());

        $types = !empty($cloudSearchSetting['type']) ? $cloudSearchSetting['type'] : array();
        $placeholderArray = array();
        if (!empty($types) && is_array($types)) {
            foreach ($types as $type => $value) {
                if ($value) {
                    if ('classroom' == $type) {
                        $classroomSetting = $this->getSettingService()->get('classroom', array());
                        $placeholderArray[] = empty($classroomSetting['name']) ? $this->container->get('translator')->trans('cloud_search.'.$type) : $classroomSetting['name'];
                    } else {
                        $placeholderArray[] = $this->container->get('translator')->trans('cloud_search.'.$type);
                    }
                }
            }
        }

        return implode('、 ', $placeholderArray);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    public function getName()
    {
        return 'topxia_search_twig';
    }
}
