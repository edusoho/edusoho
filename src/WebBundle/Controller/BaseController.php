<?php
/**
 * User: retamia
 * Date: 2016/10/19
 * Time: 12:02
 */

namespace WebBundle\Controller;


use Codeages\Biz\Framework\Service\BaseService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Topxia\Common\Exception\AccessDeniedException;
use Topxia\Common\Exception\ResourceNotFoundException;

class BaseController extends Controller
{
    protected function getBiz()
    {
        return $this->get('biz');
    }

    public function getUser()
    {
        $biz = $this->getBiz();
        return $biz['user'];
    }

    protected function createJsonResponse($data = null, $status = 200, $headers = array())
    {
        return new JsonResponse($data, $status, $headers);
    }

    protected function createResourceNotFoundException($resourceType, $resourceId, $message='')
    {
        return new ResourceNotFoundException($resourceType, $resourceId, $message);
    }

    /**
     * @param string $alias
     * @return BaseService
     */
    protected function createService($alias)
    {
        $biz = $this->getBiz();
        return $biz->service($alias);
    }
}