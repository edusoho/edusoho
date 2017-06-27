<?php
use Topxia\Service\Common\ServiceKernel;
use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;


function filter($data, $type)
{
    $class = 'Topxia\\Api\\Filter\\' .  ucfirst($type) . 'Filter';
    $filter = new $class();
    return $filter->filter($data);
}

function filters($datas, $type)
{
    $class = 'Topxia\\Api\\Filter\\' .  ucfirst($type) . 'Filter';
    $filter = new $class();
    return $filter->filters($datas);
}

function convert($data, $type)
{
    $class = 'Topxia\\Api\\Convert\\' .  ucfirst($type) . 'Convert';
    $convert = new $class();
    return $convert->convert($data);
}

function setCurrentUser($user)
{
    $currentUser = new CurrentUser();
    if (empty($user)) {
        $user = array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' =>  '',
            'roles' => array(),
        );
    }
    $currentUser->fromArray($user);
    ServiceKernel::instance()->setCurrentUser($currentUser);
}

function getCurrentUser()
{
    return ServiceKernel::instance()->getCurrentUser();
}

function createAccessDeniedException($message = 'Access Denied', $code = 0)
{
    return new AccessDeniedException($message, null, $code);
}

function createNotFoundException($message = 'Not Found', $code = 0)
{
    return new NotFoundException($message, $code);
}

function convertAbsoluteUrl($html)
{
    $host = ServiceKernel::instance()->getEnvVariable('schemeAndHost');
    $html = preg_replace_callback('/src=[\'\"]\/(.*?)[\'\"]/', function($matches) use ($host) {
        return "src=\"{$host}/{$matches[1]}\"";
    }, $html);

    return $html;
}