
import process from 'process';
import decache from 'decache';

const getRootPath = function(){
  return process.cwd();
};

const init = function(html = '')
{
  let { JSDOM } = require('jsdom');
  let dom = new JSDOM(`<!DOCTYPE html><html><body>${html}</body></html>`);
  global.window = dom.window;
  global.$ = require('jquery');
};

export {
  init,
  getRootPath
};



