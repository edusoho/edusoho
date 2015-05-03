<?php
namespace Topxia\Common;

use Topxia\Service\Common\ServiceKernel;

class BlockToolkit {

    public static function init($code, $jsonFile) 
    {
        if (!file_exists($jsonFile)) {
            throw new \RuntimeException("插件编辑区元信息文件{$blockMeta}不存在！");
        }

        $blockMeta = json_decode(file_get_contents($jsonFile), true);
        if (empty($blockMeta)) {
            throw new \RuntimeException("插件元信息文件{$blockMeta}格式不符合JSON规范，解析失败，请检查元信息文件格式");
        }

        $blockService = ServiceKernel::instance()->createService('Content.BlockService');
        foreach ($blockMeta as $key => $meta) {
            $block = $blockService->getBlockByCode($key);
            $default = empty($meta['default']) ? null : $meta['default'];
            if (empty($block)) {
                $block = array(
                    'code' => $key,
                    'category' => $code,
                    'meta' => $meta,
                    'data' => $default,
                    'templateName' => $meta['templateName'],
                    'title' => $meta['title']
                );
                $blockService->createBlock($block);
            } else {
                $blockService->updateBlock($block['id'], array(
                    'category' => $code,
                    'meta' => $meta,
                    'data' => $default,
                    'templateName' => $meta['templateName'],
                    'title' => $meta['title']
                ));
            }
          
        }
    }

}