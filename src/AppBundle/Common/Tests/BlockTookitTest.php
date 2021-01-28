<?php

namespace AppBundle\Common\Tests;

use Biz\BaseTestCase;

class BlockTookitTest extends BaseTestCase
{
    public function testUpdateCarousel()
    {
        $content = '

         <a href="/page/advantage"><img src="/files/default/2015/04-11/112830e24e8d363209.jpg?5.2.4"></a>
        <a href="/page/advantage"><img src="/files/default/2015/04-11/1122019a6951892474.jpg?5.2.4"></a>
        <a href="/page/advantage"><img src="/files/default/2015/04-11/1128353cd37b311439.jpg?5.2.4"></a>
        <img src="/files/default/2015/02-26/1733448c7d09508424.jpg?4.9.4">
        <img src="/files/default/2015/02-26/173351fe1494062061.jpg?4.9.4">
        <img src="/files/default/2015/02-26/173356466d93820923.jpg?4.9.4">
        <img src="/files/default/2015/02-26/17340199e797153342.jpg?4.9.4">


        ';
        $data = json_decode('{"carousel":[{"src":"\/themes\/graceful\/img\/slide-1.png","alt":"\u8f6e\u64ad\u56fe1\u7684\u63cf\u8ff0","href":"#","target":"_self"},{"src":"\/themes\/graceful\/img\/slide-2.png","alt":"\u8f6e\u64ad\u56fe2\u7684\u63cf\u8ff0","href":"#","target":"_self"},{"src":"\/themes\/graceful\/img\/slide-3.png","alt":"\u8f6e\u64ad\u56fe3\u7684\u63cf\u8ff0","href":"#","target":"_self"},{"src":"\/themes\/graceful\/img\/slide-1.png","alt":"\u8f6e\u64ad\u56fe4\u7684\u63cf\u8ff0","href":"#","target":"_self"},{"src":"\/themes\/graceful\/img\/slide-2.png","alt":"\u8f6e\u64ad\u56fe5\u7684\u63cf\u8ff0","href":"#","target":"_self"}]}', true);
        preg_match_all('/< *img[^>]*src *= *["\']?([^"\']*)/is', $content, $imgMatchs);
        preg_match_all('/< *img[^>]*alt *= *["\']?([^"\']*)/is', $content, $altMatchs);
        preg_match_all('/< *a[^>]*href *= *["\']?([^"\']*)/is', $content, $linkMatchs);
        preg_match_all('/< *a[^>]*target *= *["\']?([^"\']*)/is', $content, $targetMatchs);
        if ($content) {
            foreach ($data['carousel'] as $key => &$imglink) {
                $unset = true;
                if (!empty($imgMatchs[1][$key])) {
                    $unset = false;
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

                if ($unset) {
                    unset($data['carousel'][$key]);
                }
            }
        } else {
            $data = null;
        }

        $this->assertEquals(5, count($data['carousel']));
    }

    public function testNullCarouselContent()
    {
        $content = '     ';
        $data = json_decode('{"carousel":[{"src":"\/themes\/graceful\/img\/slide-1.png","alt":"\u8f6e\u64ad\u56fe1\u7684\u63cf\u8ff0","href":"#","target":"_self"},{"src":"\/themes\/graceful\/img\/slide-2.png","alt":"\u8f6e\u64ad\u56fe2\u7684\u63cf\u8ff0","href":"#","target":"_self"},{"src":"\/themes\/graceful\/img\/slide-3.png","alt":"\u8f6e\u64ad\u56fe3\u7684\u63cf\u8ff0","href":"#","target":"_self"},{"src":"\/themes\/graceful\/img\/slide-1.png","alt":"\u8f6e\u64ad\u56fe4\u7684\u63cf\u8ff0","href":"#","target":"_self"},{"src":"\/themes\/graceful\/img\/slide-2.png","alt":"\u8f6e\u64ad\u56fe5\u7684\u63cf\u8ff0","href":"#","target":"_self"}]}', true);

        if (trim($content)) {
            preg_match_all('/< *img[^>]*src *= *["\']?([^"\']*)/is', $content, $imgMatchs);
            preg_match_all('/< *img[^>]*alt *= *["\']?([^"\']*)/is', $content, $altMatchs);
            preg_match_all('/< *a[^>]*href *= *["\']?([^"\']*)/is', $content, $linkMatchs);
            preg_match_all('/< *a[^>]*target *= *["\']?([^"\']*)/is', $content, $targetMatchs);
            foreach ($data['carousel'] as $key => &$imglink) {
                $unset = true;
                if (!empty($imgMatchs[1][$key])) {
                    $unset = false;
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

                if ($unset) {
                    unset($data['carousel'][$key]);
                }
            }
        } else {
            $data = null;
        }

        $this->assertNull($data);
    }

    public function testNullContentLinks()
    {
        $content = ' ';
        $data = json_decode('{"firstColumnText":[{"value":"\u65b0\u624b\u6307\u5357"}],"firstColumnLinks":[{"value":"\u8d2d\u4e70\u6d41\u7a0b","href":"#","target":"_self"},{"value":"\u5982\u4f55\u5b66\u4e60","href":"#","target":"_self"},{"value":"\u4f1a\u5458\u5236\u5ea6","href":"#","target":"_self"},{"value":"\u5e38\u89c1\u95ee\u9898","href":"#","target":"_self"}],"secondColumnText":[{"value":"\u6211\u662f\u8001\u5e08"}],"secondColumnLinks":[{"value":"\u5982\u4f55\u53d1\u5e03\u8bfe\u7a0b","href":"#","target":"_self"},{"value":"\u5982\u4f55\u4e0a\u4f20\u8bfe\u4ef6","href":"#","target":"_self"},{"value":"\u5982\u4f55\u5f55\u5165\u9898\u76ee","href":"#","target":"_self"},{"value":"\u5982\u4f55\u521b\u5efa\u76f4\u64ad\u8bfe","href":"#","target":"_self"}],"thirdColumnText":[{"value":"\u6211\u662f\u5b66\u751f"}],"thirdColumnLinks":[{"value":"\u5982\u4f55\u63d0\u95ee","href":"#","target":"_self"},{"value":"\u5982\u4f55\u505a\u7b14\u8bb0","href":"#","target":"_self"},{"value":"\u5982\u4f55\u53c2\u52a0\u8003\u8bd5","href":"#","target":"_self"},{"value":"\u5982\u4f55\u67e5\u770b\u8d44\u6599","href":"#","target":"_self"}],"fourthColumnText":[{"value":"\u8d26\u6237\u7ba1\u7406"}],"fourthColumnLinks":[{"value":"\u4fee\u6539\u90ae\u7bb1","href":"#","target":"_self"},{"value":"\u627e\u56de\u5bc6\u7801","href":"#","target":"_self"},{"value":"\u8bbe\u7f6e\u5934\u50cf","href":"#","target":"_self"},{"value":"\u5b9e\u540d\u8ba4\u8bc1","href":"#","target":"_self"}],"fifthColumnText":[{"value":"\u5173\u4e8e\u6211\u4eec"}],"fifthColumnLinks":[{"value":"\u5173\u4e8e\u6211\u4eec","href":"#","target":"_self"},{"value":"\u8054\u7cfb\u6211\u4eec","href":"#","target":"_self"},{"value":"\u65b0\u6d6a\u5fae\u535a","href":"#","target":"_self"},{"value":"\u817e\u8baf\u5fae\u535a","href":"#","target":"_self"}]}', true);

        $index = 0;
        $index2 = 0;
        $content = trim($content);
        if (!empty($content)) {
            preg_match_all('/< *dt.*?>(.*?)<\/dt>/is', $content, $textMatchs);
            preg_match_all('/< *dl.*?>.*?<\/dl>/is', $content, $dlMatchs);
            foreach ($data as $key => &$object) {
                if (in_array($key, array('firstColumnText', 'secondColumnText', 'thirdColumnText', 'fourthColumnText', 'fifthColumnText'))) {
                    $object[0]['value'] = $textMatchs[1][$index];
                    ++$index;
                }

                if (in_array($key, array('firstColumnLinks', 'secondColumnLinks', 'thirdColumnLinks', 'fourthColumnLinks', 'fifthColumnLinks'))
                        && !empty($dlMatchs[0][$index2])) {
                    $dl = $dlMatchs[0][$index2];
                    ++$index2;
                    preg_match_all('/< *a[^>]*href *= *["\']?([^"\']*)/i', $dl, $hrefMatchs);
                    preg_match_all('/< *a[^>]*target *= *["\']?([^"\']*)/i', $dl, $targetMatchs);
                    preg_match_all('/< *a.*?>(.*?)<\/a>/i', $dl, $valuetMatchs);
                    foreach ($object as $i => &$item) {
                        $unset = true;
                        if (!empty($hrefMatchs[1][$i])) {
                            $item['href'] = $hrefMatchs[1][$i];
                        }

                        if (!empty($targetMatchs[1][$i])) {
                            $item['target'] = $targetMatchs[1][$i];
                        }

                        if (!empty($valuetMatchs[1][$i])) {
                            $unset = false;
                            $item['value'] = $valuetMatchs[1][$i];
                        }
                        if ($unset) {
                            unset($object[$i]);
                            $unset = true;
                        }
                    }
                }
            }
        } else {
            $data = null;
        }

        $this->assertNull($data);
    }
}
