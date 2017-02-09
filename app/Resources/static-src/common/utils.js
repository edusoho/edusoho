let Browser = {};
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

export { Browser }