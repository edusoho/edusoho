import UAParser from 'ua-parser-js';

let $uapraser = new UAParser(navigator.userAgent);
let browser = $uapraser.getBrowser();
let os = $uapraser.getOS();

alert(os.name + browser.name);

if (os.name === 'iOS' && browser.name === '[Mobile] Safari') {
  document.getElementById("openApp").click();
}
