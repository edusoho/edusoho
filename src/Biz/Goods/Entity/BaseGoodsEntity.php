<?php

namespace Biz\Goods\Entity;

use Biz\Goods\Service\GoodsService;
use Biz\Product\ProductException;
use Biz\Product\Service\ProductService;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\PluginBundle\System\PluginConfigurationManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Topxia\Service\Common\ServiceKernel;

/**
 * Class BaseGoodsEntity
 */
abstract class BaseGoodsEntity
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    abstract public function getTarget($goods);

    abstract public function hitTarget($goods);

    abstract public function getSpecsByTargetId($targetId);

    abstract public function canManageTarget($goods);

    abstract public function getManageUrl($goods);

    abstract public function fetchTargets($goodses);

    abstract public function fetchSpecs($targets);

    abstract public function isSpecsMember($goods, $specs, $userId);

    abstract public function isSpecsTeacher($goods, $specs, $userId);

    abstract public function isSpecsStudent($goods, $specs, $userId);

    abstract public function getVipInfo($goods, $specs, $userId);

    abstract public function canVipFreeJoin($goods, $specs, $userId);

    abstract public function getSpecsTeacherIds($goods, $specs);

    abstract public function buySpecsAccess($goods, $specs);

    abstract public function hasCertificate($goods, $specs);

    /**
     * @param $productId
     *
     * @return mixed
     */
    protected function getProduct($productId)
    {
        $product = $this->getProductService()->getProduct($productId);
        if (empty($product)) {
            throw ProductException::NOTFOUND_PRODUCT();
        }

        return $product;
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->biz->service('Product:ProductService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->biz->service('Goods:GoodsService');
    }

    protected function isPluginInstalled($code)
    {
        $pluginManager = new PluginConfigurationManager(ServiceKernel::instance()->getParameter('kernel.root_dir'));

        return $pluginManager->isPluginInstalled($code);
    }

    protected function generateUrl($name, $parameters, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        global $kernel;

        return $kernel->getContainer()->get('router')->generate($name, $parameters, $referenceType);
    }
}
