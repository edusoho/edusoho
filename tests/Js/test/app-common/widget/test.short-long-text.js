
let { getRootPath, init } = require('../../../util/init.js');
import decache from 'decache';
const { shortLongText } = require(getRootPath() + '/app/Resources/static-src/app/common/widget/short-long-text.js');
var assert = require('chai').assert;

describe('common:short-long-test', function(done) {
  before(function() {
    init('<div class="short-text">tesast</div>');
    shortLongText($('body'));
  });
  after(function() {
    decache('../../../util/init.js');
  });
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