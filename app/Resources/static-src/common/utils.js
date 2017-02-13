const Browser = {};
let userAgent = navigator.userAgent.toLowerCase();
let s;
(s = userAgent.match(/rv:([\d.]+)\) like gecko/)) ? Browser.ie = s[1] :
(s = userAgent.match(/msie ([\d.]+)/)) ? Browser.ie = s[1] :
(s = userAgent.match(/firefox\/([\d.]+)/)) ? Browser.firefox = s[1] :
(s = userAgent.match(/chrome\/([\d.]+)/)) ? Browser.chrome = s[1] :
(s = userAgent.match(/opera.([\d.]+)/)) ? Browser.opera = s[1] :
(s = userAgent.match(/version\/([\d.]+).*safari/)) ? Browser.safari = s[1] : 0;

if (Browser.ie) console.log('IE: ' + Browser.ie);
if (Browser.firefox) console.log('Firefox: ' + Browser.firefox);
if (Browser.chrome) console.log('Chrome: ' + Browser.chrome);
if (Browser.opera) console.log('Opera: ' + Browser.opera);
if (Browser.safari) console.log('Safari: ' + Browser.safari);


Browser.ie10 = /MSIE\s+10.0/i.test(navigator.userAgent)
                && (() => {"use strict";return this === undefined;})();
Browser.ie11 = (/Trident\/7\./).test(navigator.userAgent);
Browser.edge = /Edge\/13./i.test(navigator.userAgent);


const isMobileDevice = ()=> {
   return navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i);
}

export { Browser,isMobileDevice }


