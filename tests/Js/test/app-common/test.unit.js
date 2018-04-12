import { getRootPath } from '../../util/init.js';
const { trim, dateFormat, numberConvertLetter } = require(getRootPath() + '/app/Resources/static-src/app/common/unit.js');

const assert = require('chai').assert;
describe('common:unit', function() {
  it('function:trim when is_global is true', function() {
    assert.equal(trim(' a asdf '), 'aasdasdfasff');
  });
  it('function:trim when is_global is false', function() {
    assert.equal(trim(' a asdf ', false), 'a asdf');
  });
});


