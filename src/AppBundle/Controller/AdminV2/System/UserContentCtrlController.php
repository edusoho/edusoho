<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Controller\AdminV2\BaseController;
use Symfony\Component\HttpFoundation\Request;

class UserContentCtrlController extends BaseController
{
    public function reviewAction(Request $request)
    {
        return $this->render('admin-v2/system/user-content-control/review.html.twig', [
            'setting' => $request,
        ]);
    }

    public function noteAction(Request $request)
    {
        return $this->render('admin-v2/system/user-content-control/note.html.twig', [
        ]);
    }

    public function threadAction(Request $request)
    {
        return $this->render('admin-v2/system/user-content-control/thread.html.twig', [
        ]);
    }

    public function privateMessageAction(Request $request)
    {
        return $this->render('admin-v2/system/user-content-control/private-message.html.twig', [
        ]);
    }
}
