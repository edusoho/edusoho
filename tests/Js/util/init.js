
import process from 'process';
import decache from 'decache';

const getRootPath = function(){
  return process.cwd();
};

const init = function(html = '')
{
  let { JSDOM } = require('jsdom');
  let dom = new JSDOM(`<!DOCTYPE html><html><body>${html}</body></html>`,{
    url: 'http://demo.edusoho.com/',
    referrer: 'http://demo.edusoho.com/',
    contentType: 'text/html',
    userAgent: 'Mellblomenator/9000',
    includeNodeLocations: true,
  });
  global.window = dom.window;
  global.$ = require('jquery');
};

export {
  init,
  getRootPath
};



