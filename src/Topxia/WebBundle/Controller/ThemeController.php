<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class ThemeController extends BaseController
{

	public function pendantAction($config=null)
    {
        if (isset($config['code'])) {
            return $this->render("TopxiaWebBundle:Default:{$config['code']}.html.twig",array(
                'config' => $config
            ));
        }
    }

    public function getCurrentConfigColorAction()
    {
        $config = $this->getThemeService()->getCurrentThemeConfig();
        return $this->render("TopxiaWebBundle:Default:color.html.twig", array(
            'color' => $config['config']['color']
        )); 
    }

    public function getCurrentConfigBottomAction()
    {
        $config = $this->getThemeService()->getCurrentThemeConfig();
        $config = $config['config']['bottom'];
        return $this->render("TopxiaWebBundle:Default:{$config}-bottom.html.twig"); 
    }

    private function getThemeService()
    {
        return $this->getServiceKernel()->createService('Theme.ThemeService');
    }
}