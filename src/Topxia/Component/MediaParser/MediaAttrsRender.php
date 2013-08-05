<?php
namespace Topxia\Component\MediaParser;

class MediaAttrsRender
{
	public static function render($media)
	{
		$attrs = [];
		if (empty($media) or empty($media['id']) or empty($media['uuid'])) {
			return json_encode($attrs);
		}

		if (stripos($media['uuid'], 'YoukuVideo') !== false) {
			$attrs['swf_url'] = $media['swf_url'];
			$attrs['apple_url'] = "http://v.youku.com/player/getM3U8/vid/{$media['id']}/ts/" . time() . '/v.m3u8';
		} else if (stripos($media['uuid'], 'QQVideo') !== false) {
			$attrs['swf_url'] = $media['swf_url'];
			$attrs['mp4_url'] = "http://video.store.qq.com/{$media['id']}.mp4";
		} else {
			if (!empty($media['swf_url'])) {
				$attrs['swf_url'] = $media['swf_url'];
			}

			if (!empty($media['mp4_url'])) {
				$attrs['mp4_url'] = $media['mp4_url'];
			}

			if (!empty($media['apple_url'])) {
				$attrs['apple_url'] = $media['apple_url'];
			}
		}

		return json_encode($attrs);
	}

}