<?php

namespace AppBundle\Controller\Admin;

use AppBundle\System;
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

    public function changeAction(Request $request)
    {
        $themeUri = $request->query->get('uri');
        $theme = $this->getTheme($themeUri);
        $result = $this->getThemeService()->changeTheme($theme);

        return $this->createJsonResponse($result);
    }

    public function saveConfigAction(Request $request, $uri)
    {
        $config = $request->request->get('config');
        $currentData = $request->request->get('currentData');
        $this->getThemeService()->saveCurrentThemeConfig($config);

        if ($currentData) {
            return $this->render(
                'admin/theme/theme-edit-config-li.html.twig',
                array(
                    'pendant' => $currentData,
                    'uri' => $uri,
                )
            );
        }

        return $this->createJsonResponse(true);
    }

    public function confirmConfigAction(Request $request, $uri)
    {
        $this->getThemeService()->saveConfirmConfig();

        return $this->redirect($this->generateUrl('admin_setting_theme', array(), true));
    }

    public function manageIndexAction(Request $request, $uri)
    {
        if (!$this->getThemeService()->isAllowedConfig()) {
            return $this->redirect($this->generateUrl('admin_setting_theme'));
        }

        $this->getThemeService()->resetCurrentConfig();
        $themeConfig = $this->getThemeService()->getCurrentThemeConfig();

        return $this->render(
            'admin/theme/edit.html.twig',
            array(
                'themeConfig' => $themeConfig['config'],
                'allConfig' => $themeConfig['allConfig'],
                'themeUri' => $uri,
            )
        );
    }

    public function resetConfigAction(Request $request, $uri)
    {
        if (!$this->getThemeService()->isAllowedConfig()) {
            return $this->redirect($this->generateUrl('admin_setting_theme'));
        }

        $this->getThemeService()->resetConfig();
        $themeConfig = $this->getThemeService()->getCurrentThemeConfig();

        return $this->render(
            'admin/theme/edit.html.twig',
            array(
                'themeConfig' => $themeConfig['config'],
                'allConfig' => $themeConfig['allConfig'],
                'themeUri' => $uri,
            )
        );
    }

    public function showAction(Request $request, $uri)
    {
        $request->request->set('themeEditing', 1);
        return $this->forward('AppBundle:Default:index', array(
            'request' => $request,
        ));
    }

    public function themeConfigEditAction(Request $request, $uri)
    {
        $config = $request->query->get('config');

        //$code = "edit".$this->fiterCode($config['code']);

        return $this->edit($config['code'], $config);
    }

    protected function fiterCode($code)
    {
        $codes = explode('-', $code);
        $code = '';

        foreach ($codes as $value) {
            $code .= ucfirst($value);
        }

        return $code;
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

    private function edit($code, $config)
    {
        return $this->render(
            'admin/theme/edit-'.$code.'-modal.html.twig',
            array(
                'config' => $config,
            )
        );
    }

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

    protected function getNavigationService()
    {
        return $this->createService('Content:NavigationService');
    }
}
