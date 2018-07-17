const { trim, dateFormat, numberConvertLetter } = require('test-src/app/common/unit.js');

const assert = require('chai').assert;
describe('app/common/unit:trim', function() {
  it('is_global is true', function() {
    assert.equal(trim(' a asdf '), 'aasdf');
  });
  it('when is_global is false', function() {
    assert.equal(trim(' a asdf ', false), 'a asdf');
  });
});


