const Browser = {};
let userAgent = navigator.userAgent.toLowerCase();
let s;
/* eslint-disable */
(s = userAgent.match(/rv:([\d.]+)\) like gecko/)) ? Browser.ie = s[1] :
  (s = userAgent.match(/msie ([\d.]+)/)) ? Browser.ie = s[1] :
    (s = userAgent.match(/firefox\/([\d.]+)/)) ? Browser.firefox = s[1] :
      (s = userAgent.match(/chrome\/([\d.]+)/)) ? Browser.chrome = s[1] :
        (s = userAgent.match(/opera.([\d.]+)/)) ? Browser.opera = s[1] :
          (s = userAgent.match(/version\/([\d.]+).*safari/)) ? Browser.safari = s[1] : 0;
/* eslint-enable */

if (Browser.ie) console.info('IE: ' + Browser.ie);
if (Browser.firefox) console.info('Firefox: ' + Browser.firefox);
if (Browser.chrome) console.info('Chrome: ' + Browser.chrome);
if (Browser.opera) console.info('Opera: ' + Browser.opera);
if (Browser.safari) console.info('Safari: ' + Browser.safari);

const isMobileDevice = () => {
  return navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i);
};

const delHtmlTag = (str) => {
  return str.replace(/<[^>]+>/g, '').replace(/&nbsp;/ig, '');
}

const initTooltips = () => {
  $('[data-toggle="tooltip"]').tooltip({
    html: true,
  });
}

const initPopover = () => {
  $('[data-toggle="popover"]').popover({
    html: true,
  });
}


export {
  Browser,
  isMobileDevice,
  delHtmlTag,
  initTooltips,
};