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

    public function saveConfigAction(Request $request)
    {
        $config = $request->request->get('config');
        $currentData = $request->request->get('currentData');

        $config = $this->getThemeService()->saveCurrentThemeConfig($config);

        return $this->render('TopxiaAdminBundle:Theme:theme-edit-config-li.html.twig', array(
            'pendant' => $currentData
        ));
    }

    public function confirmConfigAction(Request $request)
    {
        $this->getThemeService()->saveConfirmConfig();
        return $this->redirect($this->generateUrl('homepage'));
    }

    public function resetConfigAction(Request $request)
    {
        $this->getThemeService()->resetConfig();
        return $this->redirect($this->generateUrl('admin_edit_theme'));
    }

    public function editAction (Request $request)
    {
        $themeConfig = $this->getThemeService()->getCurrentThemeConfig();

        return $this->render('TopxiaAdminBundle:Theme:edit.html.twig', array(
            'themeConfig' => $themeConfig['config'],
            'allConfig' => $themeConfig['allConfig']
        ));
    }

    public function showAction(Request $request)
    {
        $themeConfig = $this->getThemeService()->getCurrentThemeConfig();

        return $this->render('TopxiaWebBundle:Default:index.html.twig', array(
            'themeConfig' => $themeConfig['config'],
            'allConfig' => $themeConfig['allConfig']
        ));
    }

    public function themeConfigEditAction(Request $request)
    {
        $code = $request->query->get('code');

        $code = "edit" . $this->fiterCode($code);

        return $this->$code();
    }

    private function fiterCode($code)
    {
        $codes = explode('-', $code);
        $code = '';
        foreach ($codes as $value) {
            $code .= ucfirst($value);
        }
        return $code;
    }

    private function editRecommendCourse ()
    {
        return $this->render('TopxiaAdminBundle:Theme:edit-left-courses-modal.html.twig');
    }

    private function editCategoryCourse ()
    {
        return $this->render('TopxiaAdminBundle:Theme:edit-left-courses-modal.html.twig');
    }

    private function editLiveCourse ()
    {
        return $this->render('TopxiaAdminBundle:Theme:edit-left-courses-modal.html.twig');
    }

    private function editRecommendTopic ()
    {
        return $this->render('TopxiaAdminBundle:Theme:edit-left-threads-modal.html.twig');
    }

    private function editInformation ()
    {
        return $this->render('TopxiaAdminBundle:Theme:edit-left-articles-modal.html.twig');
    }

    private function editLecturers ()
    {
        return $this->render('TopxiaAdminBundle:Theme:edit-left-teachers-modal.html.twig');
    }

    private function editSidebarLiveCourse ()
    {
        return $this->render('TopxiaAdminBundle:Theme:edit-right-live-courses-modal.html.twig');
    }

    private function editPayCourse ()
    {
        return $this->render('TopxiaAdminBundle:Theme:edit-right-popular-courses-modal.html.twig');
    }

    private function editPopularTags ()
    {
        return $this->render('TopxiaAdminBundle:Theme:edit-right-tags-modal.html.twig');
    }

    private function editLatestReviews ()
    {
        return $this->render('TopxiaAdminBundle:Theme:edit-right-reviews-modal.html.twig');
    }

    private function editPromotedTeacherBlock ()
    {
        return $this->render('TopxiaAdminBundle:Theme:edit-right-teacher-modal.html.twig');
    }

    private function editTheDynamic ()
    {
        return $this->render('TopxiaAdminBundle:Theme:edit-right-articles-modal.html.twig');
    }

    private function editTheLearningDynamics ()
    {
        return $this->render('TopxiaAdminBundle:Theme:edit-right-learns-modal.html.twig');
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

    private function getThemeService()
    {
        return $this->getServiceKernel()->createService('Theme.ThemeService');
    }
}