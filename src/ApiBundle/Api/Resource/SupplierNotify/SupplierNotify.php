<?php

namespace ApiBundle\Api\Resource\SupplierNotify;

use ApiBundle\Api\Annotation\Access;
use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\S2B2C\Service\SupplierNotifyService;

class SupplierNotify extends AbstractResource
{
    private $allowTypes = [
        'site_status_change' => 'onSiteStatusChange',
        'coop_mode_change' => 'onCoopModeChange',
        'merchant_domain_url_change' => 'onMerchantDomainUrlChange',
        'supplier_domain_url_change' => 'onSupplierDomainUrlChange',
        'site_logo_and_favicon_change' => 'onSupplierSiteLogoAndFaviconChange',
        'auth_node_change' => 'onMerchantAuthNodeChange', // setting => s2b2c => auth_node (array)
        'reset_brand' => 'onResetMerchantBrand',
    ];

    /**
     * @Access(roles="ROLE_ADMIN,ROLE_SUPER_ADMIN")
     * @ApiConf(isRequiredAuth=true)
     */
    public function add(ApiRequest $request)
    {
        $params = $request->request->get('body');

        if (!array_key_exists('notify_type', $params)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        if (!array_key_exists($params['notify_type'], $this->allowTypes)) {
            throw CommonException::ERROR_PARAMETER();
        }

        $func = $this->allowTypes[$params['notify_type']];

        return $this->getSupplierNotifyService()->$func($params);
    }

    /**
     * @return SupplierNotifyService
     */
    private function getSupplierNotifyService()
    {
        return $this->service('S2B2C:SupplierNotifyService');
    }
}
