
let { init } = require('test/util/init.js');
const { shortLongText } = require('test-src/app/common/widget/short-long-text.js');
const assert = require('chai').assert;

describe('app/common/widget/short-long-text:shortLongText', function() {
  before(function() {
    init('<div class="short-text">tesast</div>');
    shortLongText($('body'));
  });

  it('short-text click event', function() {
    $('body').find('.short-text').trigger('click');
    assert.equal($('.short-text').css('display'), 'block');
    
    let test = function() {
      assert.equal($('.short-text').css('display'), 'none');
    };

    setTimeout(test, 1001);
  });
});