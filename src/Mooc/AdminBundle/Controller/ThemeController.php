<?php

namespace Mooc\AdminBundle\Controller;

use Topxia\AdminBundle\Controller\ThemeController as ThemeBaseController;

class ThemeController extends ThemeBaseController
{
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

        if (empty($theme['themeType']) || $theme['themeType'] != 'mooc') {
            return;
        }

        $theme['uri'] = $uri;

        return $theme;
    }
}
