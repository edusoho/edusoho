<?php

namespace AppBundle\Controller\Callback;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Controller\BaseController;

class IndexController extends BaseController
{
    public function indexAction(Request $request)
    {
        $resource = $request->query->get('res', null);
        $resourceInstance = $this->get('callback.resource_factory')->create($resource);
        $method = strtolower($request->getMethod());
        if (!in_array($method, array('post', 'get'))) {
            throw new \InvalidArgumentException(sprintf('unsupported method: %s', $method));
        }

        return new JsonResponse($resourceInstance->$method($request));
    }

    protected function nextCursorPaging($currentCursor, $currentStart, $currentLimit, $currentRows)
    {
        $end = end($currentRows);
        if (empty($end)) {
            return array(
                'cursor' => $currentCursor + 1,
                'start' => 0,
                'limit' => $currentLimit,
                'eof' => true,
            );
        }

        if (count($currentRows) < $currentLimit) {
            return array(
                'cursor' => $end['updatedTime'] + 1,
                'start' => 0,
                'limit' => $currentLimit,
                'eof' => true,
            );
        }

        if ($end['updatedTime'] != $currentCursor) {
            $next = array(
                'cursor' => $end['updatedTime'],
                'start' => 0,
                'limit' => $currentLimit,
                'eof' => false,
            );
        } else {
            $next = array(
                'cursor' => $currentCursor,
                'start' => $currentStart + $currentLimit,
                'limit' => $currentLimit,
                'eof' => false,
            );
        }

        return $next;
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->getBiz()->service('Thread:ThreadService');
    }
}