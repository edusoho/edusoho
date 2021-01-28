
let { init } = require('test/util/init.js');
const { shortLongText } = require('test-src/app/common/widget/short-long-text.js');
const assert = require('chai').assert;
const sinon = require('sinon');
let clock;

describe('app/common/widget/short-long-text:shortLongText', function() {
  before(function() {
    init('<div class="short-text">tesast</div>');
    shortLongText($('body'));
  });

  it('short-text click event', function() {
    clock = sinon.useFakeTimers(); 
    $('body').find('.short-text').trigger('click');
    assert.equal($('.short-text').css('display'), 'block');
    clock.tick(1000);
    assert.equal($('.short-text').css('display'), 'none');
    clock.restore();
  });
});