
let { getRootPath, init } = require('../../../util/init.js');
import decache from 'decache';
const assert = require('chai').assert;
const sinon = require('sinon');
import Api from 'common/api';
let ajax = require(getRootPath() + '/app/Resources/static-src/common/api/ajax.js').default;

describe('common:ajax', function() {
  before(function() {
    init('');
  });

  it('ajax:promise = true', function() {
    let ajaxStub = sinon.stub($, 'ajax').returns('test');
    ajax({
      beforeSend: '',
    }).then(res => {
      assert.equal(res, 'test');
    });
    let expectedParams = {
      async: true,
      beforeSend: '',
      dataType: 'json',
      promise: true,
      type: 'GET',
      url: null,
    };
    ajaxStub.restore();
    sinon.assert.calledWith(ajaxStub, expectedParams);
  });

  it('ajax:promise = false', function() {
    let ajaxStub = sinon.stub($, 'ajax').returns('test1');
    let result = ajax({
      beforeSend: '',
      promise: false,
    });
    assert.equal(result, 'test1');

    let expectedParams = {
      async: true,
      beforeSend: '',
      dataType: 'json',
      promise: false,
      type: 'GET',
      url: null,
    };
    ajaxStub.restore();
    sinon.assert.calledWith(ajaxStub, expectedParams);
  });

});