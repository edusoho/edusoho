<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;

class ThemeController extends BaseController
{
    public function indexAction(Request $request)
    {
        $currentTheme = $this->setting('theme', array('uri' => 'default'));

        $themes = $this->getThemes();

        return $this->render('TopxiaAdminBundle:Theme:index.html.twig', array(
            'themes'       => $themes,
            'currentTheme' => $currentTheme
        ));
    }

    public function changeAction(Request $request)
    {
        $themeUri = $request->query->get('uri');

        $theme = $this->getTheme($themeUri);

        if (empty($theme)) {
            return $this->createJsonResponse(false);
        }

        $this->getSettingService()->set('theme', $theme);

        return $this->createJsonResponse(true);
    }

    public function manageIndexAction(Request $request, $uri)
    {
        // if (!$this->getThemeService()->isAllowedConfig()) {
        //     return $this->redirect($this->generateUrl('admin_setting_theme'));
        // }
        $themeConfig = $this->getThemeService()->getCurrentThemeConfig();

        return $this->render('TopxiaAdminBundle:Theme:edit.html.twig', array(
            'themeConfig' => $themeConfig['config'],
            'allConfig'   => $themeConfig['allConfig'],
            'themeUri'    => $uri
        ));
    }

    public function resetConfigAction(Request $request, $uri)
    {
        //var_dump($uri);
        //exit();
        $this->getThemeService()->resetConfig();
        return $this->redirect($this->generateUrl('admin_theme_manage', array('uri' => $uri), true));
    }

    protected function getTheme($uri)
    {
        if (empty($uri)) {
            return;
        }

        $dir = $this->container->getParameter('kernel.root_dir').'/../web/themes';

        $metaPath = $dir.'/'.$uri.'/theme.json';

        if (!file_exists($metaPath)) {
            return;
        }

        $theme = json_decode(file_get_contents($metaPath), true);

        if (empty($theme)) {
            return;
        }

        $theme['uri'] = $uri;

        return $theme;
    }

    protected function getThemes()
    {
        $themes = array();

        $dir    = $this->container->getParameter('kernel.root_dir').'/../web/themes';
        $finder = new Finder();

        foreach ($finder->directories()->in($dir)->depth('== 0') as $directory) {
            $theme = $this->getTheme($directory->getBasename());

            if ($theme) {
                $themes[] = $theme;
            }
        }

        return $themes;
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getThemeService()
    {
        return $this->getServiceKernel()->createService('Theme.ThemeService');
    }
}
