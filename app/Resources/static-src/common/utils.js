const Browser = {};
let userAgent = navigator.userAgent.toLowerCase();
let s;
(s = userAgent.match(/rv:([\d.]+)\) like gecko/)) ? Browser.ie = s[1] :
(s = userAgent.match(/msie ([\d.]+)/)) ? Browser.ie = s[1] :
(s = userAgent.match(/firefox\/([\d.]+)/)) ? Browser.firefox = s[1] :
(s = userAgent.match(/chrome\/([\d.]+)/)) ? Browser.chrome = s[1] :
(s = userAgent.match(/opera.([\d.]+)/)) ? Browser.opera = s[1] :
(s = userAgent.match(/version\/([\d.]+).*safari/)) ? Browser.safari = s[1] : 0;

Browser.ie10 = /MSIE\s+10.0/i.test(navigator.userAgent)
                && (() => {"use strict";return this === undefined;})();
Browser.ie11 = (/Trident\/7\./).test(navigator.userAgent);
Browser.edge = /Edge\/13./i.test(navigator.userAgent);


const isMobileDevice = ()=> {
   return navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i);
}

const isLogin = (() => { return $("meta[name='is-login']").attr("content") == 1 })();

export { Browser, isMobileDevice, isLogin }


