<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class ThemeController extends BaseController
{
    public function pendantAction(Request $request)
    {
        // $config = $request->query->all();
        
        $config = (Object)array(
                    'id' => 'dafads',
                    'code' => 'the-dynamic',
                    'sort_type' => 'promoted',
                    'title' =>'bbbbb',
                    'categoryId' =>'3',
                    'count' => '15',
                    'free' => ''
                    );

        $view = $config->code;

        return $this->render("TopxiaWebBundle:Default:{$view}.html.twig",array(
            'config' => $config
        ));
    }
}