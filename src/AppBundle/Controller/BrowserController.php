<?php

namespace AppBundle\Controller;

class BrowserController extends BaseController
{
    public function upgradeAction()
    {
        return $this->render('browser/upgrade.html.twig');
    }
}
