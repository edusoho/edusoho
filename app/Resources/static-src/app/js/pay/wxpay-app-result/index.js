import UAParser from 'ua-parser-js';

let $uapraser = new UAParser(navigator.userAgent);
let browser = $uapraser.getBrowser();
let os = $uapraser.getOS();

if (os.name === 'iOS' && browser.name === 'Mobile Safari') {
  $('#openApp').html(os.name + browser.name);
  $('#openApp')[0].click();
}
