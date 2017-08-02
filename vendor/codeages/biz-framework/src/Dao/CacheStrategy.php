<?php

namespace Codeages\Biz\Framework\Dao;

/**
 * Dao的缓存策略接口
 */
interface CacheStrategy
{
    /**
     * 在Dao的get/find/search/count系列方法调用之前调用，以获取缓存。
     *
     * @param string $dao       Dao
     * @param string $method    调用的Dao方法名
     * @param array  $arguments 调用的Dao参数
     *
     * @return mixed 缓存存在返回结果集，否则返回false
     */
    public function beforeQuery(GeneralDaoInterface $dao, $method, $arguments);

    /**
     * 在Dao的get/find/search/count系列方法调用之后调用，可以缓存结果。
     *
     * @param string $dao       Dao
     * @param string $method    调用Dao方法名
     * @param array  $arguments 调用Dao参数
     * @param mixed  $row       调用Dao后的结果
     */
    public function afterQuery(GeneralDaoInterface $dao, $method, $arguments, $data);

    /**
     * 在Dao的create系列方法调用之后调用，可以缓存结果集。
     *
     * @param string $dao       Dao
     * @param string $method    调用Dao方法名
     * @param array  $arguments 调用Dao参数
     * @param mixed  $row       调用Dao后的结果
     */
    public function afterCreate(GeneralDaoInterface $dao, $method, $arguments, $row);

    /**
     * 在Dao的update系列方法调用之后调用，可以缓存结果集。
     *
     * @param string $dao       Dao
     * @param string $method    调用Dao方法名
     * @param array  $arguments 调用Dao参数
     * @param mixed  $row       调用Dao后的结果
     */
    public function afterUpdate(GeneralDaoInterface $dao, $method, $arguments, $row);

    /**
     * 在Dao的wave系列方法调用之后调用，可以缓存结果集。
     *
     * @param string $dao       Dao
     * @param string $method    调用Dao方法名
     * @param array  $arguments 调用Dao参数
     * @param int    $row       受影响的行数
     */
    public function afterWave(GeneralDaoInterface $dao, $method, $arguments, $affected);

    /**
     * 在Dao的delete系列方法调用之后调用，可以删除缓存。
     *
     * @param string $dao       Dao
     * @param string $method    调用Dao方法名
     * @param array  $arguments 调用Dao参数
     */
    public function afterDelete(GeneralDaoInterface $dao, $method, $arguments);

    /**
     * 清空整张表的缓存
     *
     * @param GeneralDaoInterface $dao
     *
     * @return bool
     */
    public function flush(GeneralDaoInterface $dao);
}
