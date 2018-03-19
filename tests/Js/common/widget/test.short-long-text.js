import { shortLongText } from '../../../../app/Resources/static-src/app/common/widget/short-long-text.js';
const jsdom = require('jsdom');
const { JSDOM } = jsdom;
const dom = new JSDOM('<!DOCTYPE html><html><body><div class="short-text">tesast</div></body></html>');
var window = dom.window;
global.window = window;
global.$ = require('jquery');

shortLongText($('body'));
var assert = require('chai').assert;
var expect = require('chai').expect;

describe('common:short-long-test', function(done) {
  it('short-text click event', function() {
    $('body').find('.short-text').trigger('click');
    assert.equal($('.short-text').css('display'), 'block');
    
    let test = function() {
      assert.equal($('.short-text').css('display'), 'none');
      done();
    };

    setTimeout(test, 1001);
  });
});