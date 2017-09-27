<?php

namespace Codeages\Biz\Framework\Token\Service;

interface TokenService
{
    /**
     * 生成令牌
     *
     * @param string $place    令牌使用场景
     * @param array  $lifetime 令牌的有效时长（秒），`0`表示永久有效
     * @param int    $times    令牌可被校验的次数，超过该次数则校验失败
     * @param mixed  $data     令牌所属的业务数据
     *
     * @throws GenerateException 当生成令牌的Key已存在时抛出异常
     *
     * @return array 令牌对象
     */
    public function generate($place, $lifetime, $times = 0, $data = null);

    /**
     * 校验令牌
     *
     * @param string $place 令牌使用场景
     * @param string $key   令牌的Key
     *
     * @return bool 校验通过，返回`true`，否则返回`false`
     */
    public function verify($place, $key);

    /**
     * 删除令牌
     *
     * @param string $place 令牌使用场景
     * @param string $key   令牌的Key
     */
    public function destroy($place, $value);

    /**
     * 清除过期令牌
     */
    public function gc();
}
