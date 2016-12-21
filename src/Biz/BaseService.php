<?php

namespace Biz;

use Biz\Util\HTMLPurifierFactory;
use Monolog\Logger;
use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Event\Event;
use Topxia\Service\Common\ServiceKernel;
use Codeages\Biz\Framework\Dao\GeneralDaoInterface;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class BaseService extends \Codeages\Biz\Framework\Service\BaseService
{
    private $lock = null;

    /**
     * @param  $alias
     * @return GeneralDaoInterface
     */
    protected function createDao($alias)
    {
        return $this->biz->dao($alias);
    }

    /**
     * @return CurrentUser
     */
    public function getCurrentUser()
    {
        return $this->biz['user'];
    }

    /**
     * @param  $alias
     * @return BaseService
     */
    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }

    private function getDispatcher()
    {
        return $this->biz['dispatcher'];
    }

    protected function dispatchEvent($eventName, $subject)
    {
        if ($subject instanceof Event) {
            $event = $subject;
        } else {
            $event = new Event($subject);
        }

        return $this->getDispatcher()->dispatch($eventName, $event);
    }

    protected function beginTransaction()
    {
        $this->biz['db']->beginTransaction();
    }

    protected function commit()
    {
        $this->biz['db']->commit();
    }

    protected function rollback()
    {
        $this->biz['db']->rollback();
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->biz['logger'];
    }

    /**
     * @param  string                  $message
     * @return AccessDeniedException
     */
    protected function createAccessDeniedException($message = '')
    {
        return new AccessDeniedException($message);
    }

    /**
     * @param  string                     $message
     * @return InvalidArgumentException
     */
    protected function createInvalidArgumentException($message = '')
    {
        return new InvalidArgumentException($message);
    }

    /**
     * @param  string              $message
     * @return NotFoundException
     */
    protected function createNotFoundException($message = '')
    {
        return new NotFoundException($message);
    }

    /**
     * @param  string             $message
     * @return ServiceException
     */
    protected function createServiceException($message = '')
    {
        return new ServiceException($message);
    }

    protected function fillOrgId($fields)
    {
        $magic = $this->biz->service('System:SettingService')->get('magic');

        if (isset($magic['enable_org']) && $magic['enable_org']) {
            if (!empty($fields['orgCode'])) {
                $org = ServiceKernel::instance()->createService('Org:OrgService')->getOrgByOrgCode($fields['orgCode']);
                if (empty($org)) {
                    throw $this->createNotFoundException('组织机构不存在,更新失败');
                }
                $fields['orgId']   = $org['id'];
                $fields['orgCode'] = $org['orgCode'];
            } else {
                unset($fields['orgCode']);
            }
        } else {
            unset($fields['orgCode']);
        }
        return $fields;
    }

    protected function purifyHtml($html, $trusted = false)
    {
        if (empty($html)) {
            return '';
        }

        $config = array(
            'cacheDir' => ServiceKernel::instance()->getParameter('kernel.cache_dir').'/htmlpurifier'
        );

        $factory  = new HTMLPurifierFactory($config);
        $purifier = $factory->create($trusted);

        return $purifier->purify($html);
    }

    protected function getLock()
    {
        if (!$this->lock) {
            $this->lock = new Lock($this->biz);
        }

        return $this->lock;
    }
}
