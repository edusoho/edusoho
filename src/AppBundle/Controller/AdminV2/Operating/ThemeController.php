<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Controller\AdminV2\BaseController;
use AppBundle\System;
use Biz\System\Service\SettingService;
use Biz\Theme\Service\ThemeService;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;

class ThemeController extends BaseController
{
    public function indexAction(Request $request)
    {
        $currentTheme = $this->setting('theme', array('uri' => 'default'));

        $themes = $this->getThemes();

        return $this->render(
            'admin/theme/index.html.twig',
            array(
                'themes' => $themes,
                'currentTheme' => $currentTheme,
            )
        );
    }

    protected function getThemes()
    {
        $themes = array();

        $dir = $this->container->getParameter('kernel.root_dir').'/../web/themes';
        $finder = new Finder();

        foreach ($finder->directories()->in($dir)->depth('== 0') as $directory) {
            $theme = $this->getTheme($directory->getBasename());

            if ($theme) {
                $themes[] = $theme;
            }
        }

        return $themes;
    }

    protected function getTheme($uri)
    {
        if (empty($uri)) {
            return;
        }

        $dir = $this->container->getParameter('kernel.root_dir').'/../web/themes';

        $metaPath = $dir.'/'.$uri.'/theme.json';

        if (!is_link($metaPath) && !is_file($metaPath)) {
            return;
        }

        $theme = json_decode(file_get_contents($metaPath), true);

        if (empty($theme)) {
            return;
        }

        $theme['uri'] = $uri;

        return $theme;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return ThemeService
     */
    protected function getThemeService()
    {
        return $this->createService('Theme:ThemeService');
    }
}
