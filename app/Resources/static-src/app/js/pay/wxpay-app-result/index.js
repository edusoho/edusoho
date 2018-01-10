import UAParser from 'ua-parser-js';

let $uapraser = new UAParser(navigator.userAgent);
let browser = $uapraser.getBrowser();
let os = $uapraser.getOS();
alert("kuozhi://" + $('#jsWebViewPayResult').data('goto'));
if (os.name === 'iOS' && browser.name === '[Mobile] Safari') {
  window.location="kuozhi://" + $('#jsWebViewPayResult').data('goto');
}
