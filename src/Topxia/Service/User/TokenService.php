<?php
namespace Topxia\Service\User;

interface TokenService
{
    /**
     * 生成一个一次性的Token
     * 
     * @param  string   $type   Token类型
     * @param  array    $args   生成Token的一些限制规则
     * 
     * @return array 生成的Token
     */
    public function makeToken($type, array $args = array());

    /**
     * 生成一个假的Token
     */
    public function makeFakeTokenString($length = 32);

    /**
     * 校验Token
     *
     * @param   string  $type Token类型
     * @param   string  $key  Token的值
     *
     * @return boolean 该Token值是否OK
     */
    public function verifyToken($type, $value);

    /**
     * 作废一个Token
     * 
     * @param  [type] $value 要摧毁的Token的值
     */
    public function destoryToken($value);
}