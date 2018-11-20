<?php

namespace AppBundle\Controller\Callback\ESLive;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CourseWare extends ESLiveBase
{
    public function fetch(Request $request)
    {
        $token = $request->query->get('jwtToken');
        $context = $this->getJWTAuth()->valid($token);
        if (!$context) {
            throw new BadRequestHttpException('Token Error');
        }
        $start = $request->query->get('start', 0);
        $limit = $request->query->get('limit', 100);

        return true;
    }
}
