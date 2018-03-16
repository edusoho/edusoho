import { trim, dateFormat, numberConvertLetter } from '../../../app/Resources/static-src/app/common/unit.js';
console.log(trim);
var assert = require('chai').assert;
describe('common:unit when is_global is true', function() {
  it('function:trim', function() {
    assert.equal(trim(' a asdf '), 'aasdf');
  });
  it('function:trim when is_global is false', function() {
    assert.equal(trim(' a asdf ', false), 'a asdf');
  });
});