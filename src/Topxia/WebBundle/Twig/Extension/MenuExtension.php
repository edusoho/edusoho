<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Service\Common\ServiceKernel;
use Topxia\WebBundle\Util\Permission;

class MenuExtension extends \Twig_Extension
{
    protected $menuUtil = null;

    public function getFilters ()
    {
        return array(
        );
    }

    public function getFunctions()
    {
        return array(
            'menus' => new \Twig_Function_Method($this, 'menus'),
            'html_title' => new \Twig_Function_Method($this, 'htmlTitle'),
            'page_title' => new \Twig_Function_Method($this, 'pageTitle'),
        );
    }

    public function htmlTitle($code)
    {
        return $this->createMenuUtil()->getTitle($code);
    }

    public function pageTitle($code)
    {
        return $this->createMenuUtil()->getTitle2($code);
    }

    public function menus($parent = null, $group = null)
    {
        return $this->createMenuUtil()->getPermissions($parent, $group);
    }

    private function createMenuUtil()
    {
        if (!$this->menuUtil) {
            $this->menuUtil = new Permission();
        }

        return $this->menuUtil;
    }

    public function getName ()
    {
        return 'topxia_menu_twig';
    }

}


