
let { init } = require('test/util/init.js');
import decache from 'decache';
const assert = require('chai').assert;
const sinon = require('sinon');
import Api from 'common/api';
let ajax = require('test-src/common/api/ajax.js').default;

describe('common:ajax', function() {
  before(function() {
    init('');
  });

  it('ajax:promise = true', function() {
    let ajaxStub = sinon.stub($, 'ajax').returns('test');
    ajax({
    }).then(res => {
      assert.equal(res, 'test');
    });
    let expectedParams = {
      async: true,
      dataType: 'json',
      promise: true,
      type: 'GET',
      url: null,
    };
    ajaxStub.restore();
    sinon.assert.calledWithMatch(ajaxStub, expectedParams);
  });

  it('ajax:promise = false', function() {
    let ajaxStub = sinon.stub($, 'ajax').returns('test1');
    let result = ajax({
      promise: false,
    });
    assert.equal(result, 'test1');

    let expectedParams = {
      async: true,
      dataType: 'json',
      promise: false,
      type: 'GET',
      url: null,
    };
    ajaxStub.restore();
    sinon.assert.calledWithMatch(ajaxStub, expectedParams);
  });

});