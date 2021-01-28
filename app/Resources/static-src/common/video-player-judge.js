const supportsNativeHls = () => {
  const video = document.createElement('video');

  // HLS manifests can go by many mime-types
  const canPlay = [
    // Apple santioned
    'application/vnd.apple.mpegurl',
    // Apple sanctioned for backwards compatibility
    'audio/mpegurl',
    // Very common
    'audio/x-mpegurl',
    // Very common
    'application/x-mpegurl',
    // Included for completeness
    'video/x-mpegurl',
    'video/mpegurl',
    'application/mpegurl',
  ];

  return canPlay.some(canItPlay => (/maybe|probably/i).test(video.canPlayType(canItPlay)));
};

const isIE = function() {
  const Browser = {};
  Browser.ie9 = /MSIE\s+9.0/i.test(navigator.userAgent);
  Browser.ie10 = /MSIE\s+10.0/i.test(navigator.userAgent);
  Browser.ie11 = (/Trident\/7\./).test(navigator.userAgent);
  Browser.edge = /Edge\/13./i.test(navigator.userAgent);
  if (Browser.ie9 || Browser.ie10 || Browser.ie11 || Browser.edge) {
    return true;
  }
};

export const getSupportedPlayer = () => {
  if (supportsNativeHls()) {
    return 'native';
  } else if (window.MediaSource) {
    return 'hls';
  } else if (isIE()) {
    return 'flash';
  }
  return false;
};

