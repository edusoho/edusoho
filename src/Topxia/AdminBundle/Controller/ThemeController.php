<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\Finder\Finder;

class ThemeController extends BaseController
{
    public function indexAction (Request $request)
    {
        $currentTheme = $this->setting('theme', array('uri' => 'default'));

        $themes = $this->getThemes();
        return $this->render('TopxiaAdminBundle:Theme:index.html.twig', array(
            'themes' => $themes,
            'currentTheme' => $currentTheme,
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

    private function getTheme($uri)
    {
        if (empty($uri)) {
            return null;
        }

        $dir = $this->container->getParameter('kernel.root_dir'). '/../web/themes';

        $metaPath = $dir . '/' . $uri . '/theme.json';

        if (!file_exists($metaPath)) {
            return null;
        }

        $theme = json_decode(file_get_contents($metaPath), true);
        if (empty($theme)) {
            return null;
        }

        $theme['uri'] = $uri;

        return $theme;
    }

    private function getThemes()
    {
        $themes = array();

        $dir = $this->container->getParameter('kernel.root_dir'). '/../web/themes';
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
}