<?php

namespace AppBundle\Listener;

use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class OrgControllerListener
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onOrgController(FilterControllerEvent $event)
    {
        if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $urlPath = $request->getPathInfo();
        $this->_parseUrlAndProcess($urlPath);
    }

    private function _parseUrlAndProcess($url)
    {
        preg_match($this->makeRegex(), $url, $matches);

        if (!empty($matches[0])) {
            $match = $matches[0];
            $target = array_values(array_filter(explode('/', $match)));
            list($type, $id) = $target;
            $this->_process($type, $id);
        }
    }

    private function _process($type, $id)
    {
        list($service, $method) = $this->_serviceMapper[$type];
        $user = ServiceKernel::instance()->getCurrentUser();
        $object = ServiceKernel::instance()->createService($service)->$method($id);

        if (empty($object['orgCode']) || !$this->_existInSubOrg($user->currentOrgCode, $object['orgCode'])) {
            throw new NotFoundHttpException("{$type} #{$id} not in this org");
        }
    }

    private function _existInSubOrg($userOrgCode, $orgCode)
    {
        if ($userOrgCode == $orgCode) {
            return true;
        }

        return strpos($orgCode, $userOrgCode, 0) === 0;
    }

    private function makeRegex()
    {
        $str = implode('|', array_keys($this->_serviceMapper));
        $pattern = sprintf("/^\/(%s)\/\d+/", $str); // example: /^\/(course|classroom)\/\d+/
        return $pattern;
    }

    private $_serviceMapper = array(
        'user' => array('User.UserService', 'getUser'),
        'course' => array('Course.CourseService', 'getCourse'),
        'classroom' => array('Classroom:ClassroomService', 'getClassroom'),
        'article' => array('Article.ArticleService', 'getArticle'),
    );
}
