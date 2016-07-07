<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Service\Common\ServiceKernel;

class ThemeExtension extends \Twig_Extension
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(

        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('current_theme', array($this, 'getCurrentTheme'))
        );
    }

    public function getCurrentTheme()
    {
        $currentTheme = $this->getThemeService()->getCurrentThemeConfig();
        return $currentTheme;
    }

    private function createService($name)
    {
        return ServiceKernel::instance()->createService($name);
    }

    private function getThemeService()
    {
        return $this->createService('Theme.ThemeService');
    }

    public function getName()
    {
        return 'topxia_theme_twig';
    }
}
