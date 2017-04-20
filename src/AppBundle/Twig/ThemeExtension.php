<?php

namespace AppBundle\Twig;

use Codeages\Biz\Framework\Context\Biz;

class ThemeExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct($container, Biz $biz)
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
            new \Twig_SimpleFunction('current_theme', array($this, 'getCurrentTheme')),
        );
    }

    public function getCurrentTheme()
    {
        $currentTheme = $this->getThemeService()->getCurrentThemeConfig();

        return $currentTheme;
    }

    private function getThemeService()
    {
        return $this->biz->service('Theme:ThemeService');
    }

    public function getName()
    {
        return 'topxia_theme_twig';
    }
}
