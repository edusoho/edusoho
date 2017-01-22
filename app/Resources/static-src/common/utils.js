const Browser = {};

Browser.isMozilla = (typeof document.implementation != 'undefined') 
  && (typeof document.implementation.createDocument != 'undefined')
  && (typeof HTMLDocument != 'undefined');
Browser.isIE = window.ActiveXObject ? true : false;
Browser.isFirefox = (navigator.userAgent.toLowerCase().indexOf("firefox") != -1);
Browser.isSafari = (navigator.userAgent.toLowerCase().indexOf("safari") != -1);
Browser.isOpera = (navigator.userAgent.toLowerCase().indexOf("opera") != -1);
Browser.isOpera = (navigator.userAgent.toLowerCase().indexOf("opera") != -1);
Browser.isChrome = (navigator.userAgent.toLowerCase().indexOf("chrome") != -1);
export { Browser }