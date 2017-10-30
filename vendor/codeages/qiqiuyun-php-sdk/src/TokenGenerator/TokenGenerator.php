<?php

namespace QiQiuYun\SDK\TokenGenerator;

interface TokenGenerator
{
    /**
     * 生成资源播放 Token
     *
     * @param string $resNo    资源编号
     * @param int    $lifetime Token 的有效时长
     * @param bool   $once     Token是否一次性
     *
     * @return string 资源播放Token
     */
    public function generatePlayToken($resNo, $lifetime = 600, $once = true);
}
