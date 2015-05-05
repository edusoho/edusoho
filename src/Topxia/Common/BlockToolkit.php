<?php
namespace Topxia\Common;

use Topxia\Service\Common\ServiceKernel;

class BlockToolkit {

    public static function init($code, $jsonFile) 
    {
        if (file_exists($jsonFile)) {
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

    public static function updateCarousel($code)
    {
        $blockService = ServiceKernel::instance()->createService('Content.BlockService');
        $block = $blockService->getBlockByCode($code);
        $data = $block['data'];
        $content = $block['content'];
        preg_match_all('/< *img[^>]*src *= *["\']?([^"\']*)/i', $content, $imgMatchs);
        preg_match_all('/< *img[^>]*alt *= *["\']?([^"\']*)/i', $content, $altMatchs);
        preg_match_all('/< *a[^>]*href *= *["\']?([^"\']*)/i', $content, $linkMatchs);
        preg_match_all('/< *a[^>]*target *= *["\']?([^"\']*)/i', $content, $targetMatchs);
        foreach ($data['carousel']['items'] as $key => $imglink) {
            if (!empty($imgMatchs[1][$key])) {
                $imglink['src'] = $imgMatchs[1][$key];
            }

            if (!empty($altMatchs[1][$key])) {
                $imglink['alt'] = $altMatchs[1][$key];
            }

            if (!empty($linkMatchs[1][$key])) {
                $imglink['href'] = $linkMatchs[1][$key];
            }

            if (!empty($targetMatchs[1][$key])) {
                $imglink['target'] = $targetMatchs[1][$key];
            }

            $data['carousel']['items'][$key] = $imglink;
        }

        $blockService->updateBlock($block['id'], array(
            'data' => $data,
        ));
    } 

}