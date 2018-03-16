<?php

namespace Codeages\Biz\Framework\Dao\IdGenerator;

use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\Codec\OrderedTimeCodec;


/**
 * 针对MySQL Innodb优化的按时间序递增的ID生成器
 * 
 * @see https://www.percona.com/blog/2014/12/19/store-uuid-optimized-way/
 * @see https://github.com/ramsey/uuid/pull/118
 */
class OrderedTimeUUIDGenerator implements IdGenerator
{
    /**
     * @var Ramsey\Uuid\UuidFactoryInterface
     */
    protected $uuidFactory;

    public function __construct()
    {
        $factory = new UuidFactory();
        $codec = new OrderedTimeCodec($factory->getUuidBuilder());
        $factory->setCodec($codec);

        $this->uuidFactory = $factory;
    }
    /**
     * @inheritDoc
     */
    public function generate($encoded = true)
    {
        if ($encoded) {
            return $this->uuidFactory->uuid1()->getBytes();
        }

        return $this->uuidFactory->uuid1()->toString();
    }

    /**
     * @inheritDoc
     */
    public function encode($id)
    {
        return $this->uuidFactory->fromString($id)->getBytes();
    }

    /**
     * @inheritDoc
     */
    public function decode($idRaw)
    {
        return $this->uuidFactory->fromBytes($idRaw)->toString();
    }
}