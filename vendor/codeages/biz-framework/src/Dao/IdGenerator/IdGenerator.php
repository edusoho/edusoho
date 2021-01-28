<?php

namespace Codeages\Biz\Framework\Dao\IdGenerator;

interface IdGenerator
{
    /**
     * 生成一个新的ID
     * 
     * @param bool $encoded 是否编码
     * @return int|string
     */
    public function generate($encoded = true);

    /**
     * 编码ID，用于存储
     * 
     * @param int|string $id
     * @return int|string
     */
    public function encode($id);

    /**
     * 解码ID
     *
     * @param int|string $idRaw
     * @return int|string
     */
    public function decode($idRaw);
}