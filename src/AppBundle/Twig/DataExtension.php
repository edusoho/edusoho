<?php

namespace AppBundle\Twig;

use Biz\CloudPlatform\CloudAPIFactory;
use Biz\EduCloud\Service\ConsultService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;
use AppBundle\Common\ExtensionManager;

class DataExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct($container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFunctions()
    {
        $options = array('is_safe' => array('html'));

        return array(
            new \Twig_SimpleFunction('data', array($this, 'getData'), $options),
            new \Twig_SimpleFunction('datas', array($this, 'getDatas'), $options),
            new \Twig_SimpleFunction('datas_count', array($this, 'getDatasCount'), $options),
            new \Twig_SimpleFunction('service', array($this, 'callService'), $options),
            new \Twig_SimpleFunction('isOldSmsUser', array($this, 'getOldSmsUserStatus'), $options),
            new \Twig_SimpleFunction('cloudStatus', array($this, 'getCloudStatus'), $options),
            new \Twig_SimpleFunction('cloudConsultPath', array($this, 'getCloudConsultPath'), $options),
            new \Twig_SimpleFunction('cloud_info', array($this, 'getCloudInfo'), $options),
        );
    }

    public function getCloudInfo()
    {
        $api = CloudAPIFactory::create('leaf');

        return $api->get('/me');
    }

    public function getData($name, $arguments)
    {
        $datatag = ExtensionManager::instance()->getDataTag($name);

        return $datatag->getData($arguments);
    }

    public function getDatas($name, $conditions, $sort = null, $start = null, $limit = null)
    {
        $method = 'get'.ucfirst($name).'Datas';
        if (!method_exists($this, $method)) {
            throw new \RuntimeException($this->getServiceKernel()->trans('尚未定义批量获取"%name%"数据', array('%name%' => $name)));
        }

        return $this->{$method}($conditions, $sort, $start, $limit);
    }

    public function getDatasCount($name, $conditions)
    {
        $method = 'get'.ucfirst($name).'DatasdeCount';
        if (!method_exists($this, $method)) {
            throw new \RuntimeException($this->getServiceKernel()->trans('尚未定义获取"%name%"数据的记录条数', array('%name%' => $name)));
        }

        return $this->{$method}($conditions);
    }

    public function getOldSmsUserStatus()
    {
        return $this->getEduCloudService()->getOldSmsUserStatus();
    }

    /**
     * @deprecated  即将废弃，不要再使用
     */
    public function callService($name, $method, $arguments)
    {
        $service = $this->biz->service($name);
        $reflectionClass = new \ReflectionClass($service);

        return $reflectionClass->getMethod($method)->invokeArgs($service, $arguments);
    }

    public function getCloudStatus()
    {
        return $this->getEduCloudService()->isVisibleCloud();
    }

    public function getCloudConsultPath()
    {
        $cloudConsult = $this->getSettingService()->get('cloud_consult', array());
        if (empty($cloudConsult)) {
            return false;
        }

        $cloudConsultEnable = empty($cloudConsult['cloud_consult_code']) && $cloudConsult['cloud_consult_setting_enabled'] && $cloudConsult['cloud_consult_is_buy'];

        if (!$cloudConsultEnable) {
            return false;
        }

        return empty($cloudConsult['cloud_consult_js']) ? false : $cloudConsult['cloud_consult_js'];
    }

    private function getEduCloudService()
    {
        return $this->biz->service('CloudPlatform:EduCloudService');
    }

    /**
     * @return ConsultService
     */
    protected function getConsultService()
    {
        return $this->biz->service('EduCloud:MicroyanConsultService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    public function getName()
    {
        return 'topxia_data_twig';
    }
}
