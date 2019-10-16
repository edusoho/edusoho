
const $contentBody = $('.js-parent-content');
const isIOS = !!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
if (isIOS) {
  $contentBody.addClass('iframe-scroll');
}
const $iframe = $contentBody.find('iframe');
if ($iframe.length !== 0) {
  $iframe.attr('scrolling', 'no');
} 
