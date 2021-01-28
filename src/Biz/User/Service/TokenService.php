<?php

namespace Biz\User\Service;

interface TokenService
{
    /**
     * 生成一个一次性的Token.
     *
     * @param string $type Token类型
     * @param array  $args 生成Token的一些限制规则
     *
     * @return array 生成的Token
     */
    public function makeToken($type, array $args = array());

    /**
     * 生成一个假的Token.
     */
    public function makeFakeTokenString($length = 32);

    /**
     * 校验Token.
     *
     * @param string $type Token类型
     * @param string $key  Token的值
     * @param array  $data data的值(有值则更新)
     *
     * @return bool 该Token值是否OK
     */
    public function verifyToken($type, $value, array $data = array());

    /**
     * 作废一个Token.
     *
     * @param [type] $value 要摧毁的Token的值
     */
    public function destoryToken($value);

    public function deleteExpiredTokens($limit);

    public function findTokensByUserIdAndType($userId, $type);

    public function destroyTokensByUserId($userId);

    public function getTokenByType($type);

    public function deleteTokenByTypeAndUserId($type, $userId);
}
