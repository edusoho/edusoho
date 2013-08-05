<?php
namespace Topxia\WebBundle\Security\Voter;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Topxia\Service\Common\ServiceKernel;

class ClientIpVoter implements VoterInterface
{
    public function __construct(ContainerInterface $container)
    {
        $this->container     = $container;
    }

    public function supportsAttribute($attribute)
    {
        return true;
    }

    public function supportsClass($class)
    {
        return true;
    }

    function vote(TokenInterface $token, $object, array $attributes)
    {
        // $blacklistedIp = ServiceKernel::instance()->createService('Util.SimpleStorageService')->get('blacklist_ip');

        // if (empty($blacklistedIp) or !is_array($blacklistedIp)) {
        //     return VoterInterface::ACCESS_ABSTAIN;
        // }

        // $request = $this->container->get('request');
        // if (in_array($request->getClientIp(), $blacklistedIp)) {
        //     $this->container->get('session')->setFlash('notice', '您的IP已被列入黑名单，访问被拒绝，如有疑问请联系管理员！');
        //     return VoterInterface::ACCESS_DENIED;
        // }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}