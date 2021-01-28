import * as utilInit from '../../util/init.js';
import SmsSender from 'app/common/widget/sms-sender';
import Register from 'test-src/app/js/auth/register/register.js';
const assert = require('chai').assert;
const sinon = require('sinon');

// describe('register:initValidator', function() {
//   it('register:initValidator', function() {
//     let validateStub = sinon.stub($.fn, 'validate');

//     let initValidator = Register.prototype.initValidator;
//     initValidator();
//     let expectedParams = {
//       rules: {
//         nickname: {
//           required: true,
//           byte_minlength: 4,
//           byte_maxlength: 18,
//           nickname: true,
//           chinese_alphanumeric: true,
//           es_remote: {
//             type: 'get',
//           }
//         },
//         password: {
//           minlength: 5,
//           maxlength: 20,
//         }
//       },
//     };
//     sinon.assert.calledWith(validateStub, expectedParams);
//     validateStub.restore();
//   });
// });

describe('register:inEventMobile', function() {
  beforeEach(function() {
    utilInit.init(`
      <form id="register-form">
      <input id="register_emailOrMobile" value="1" />
      <input id="register_mobile" value="2" />
      </form>
    `);
  });

  it('register:inEventMobile register_mobile', function() {
    let mockObject = {
      emSmsCodeValidate: function(){}
    };
    let emSmsCodeValidateStub = sinon.stub(mockObject, 'emSmsCodeValidate');
    let inEventMobile = Register.prototype.inEventMobile;

    inEventMobile.apply(mockObject);
    $('#register_mobile').trigger('blur');
    emSmsCodeValidateStub.restore();
    sinon.assert.calledWith(emSmsCodeValidateStub, '2');
  });

  it('register:inEventMobile register_emailOrMobile', function() {
    let mockObject = {
      emSmsCodeValidate: function(){}
    };
    let emSmsCodeValidateStub = sinon.stub(mockObject, 'emSmsCodeValidate');
    let inEventMobile = Register.prototype.inEventMobile;

    inEventMobile.apply(mockObject);
    $('#register_emailOrMobile').trigger('blur');
    emSmsCodeValidateStub.restore();
    sinon.assert.calledWith(emSmsCodeValidateStub, '1');
  });
});

describe('register:initDragCaptchaCodeRule', function() {
  beforeEach(function() {
    utilInit.init(`
      <div class="js-drag-img"></div>
      <input type="hidden" name="drag_captcha_token" value="test" />
    `);
  });
  it('register:initDragCaptchaCodeRule', function() {
    $.fn.rules = function(){};
    let stub = sinon.stub($.fn, 'rules');
    let initDragCaptchaCodeRule = Register.prototype.initDragCaptchaCodeRule;
    initDragCaptchaCodeRule();
    stub.restore();
    sinon.assert.calledOnce(stub);
    sinon.assert.calledWith(stub, 'add', {
      required: true,
      messages: {
        required: 'auth.register.drag_captcha_tips'
      }
    });
  });
});


describe('register:emSmsCodeValidate', function() {
  beforeEach(function() {
    utilInit.init(`
      <input name="captcha_code" />
      <input name="sms_code" />
    `);
  });

  it('register:emSmsCodeValidate isMobile', function() {
    let emSmsCodeValidate = Register.prototype.emSmsCodeValidate;
    let mockObject = {
      initSmsCodeRule: function(){},
    };
    let initSmsCodeRuleStub = sinon.stub(mockObject, 'initSmsCodeRule');
    emSmsCodeValidate.apply(mockObject, [13967340627]);
    sinon.assert.calledOnce(initSmsCodeRuleStub);
    initSmsCodeRuleStub.restore();
  });

  it('register:emSmsCodeValidate isNotMobile', function() {
    let emSmsCodeValidate = Register.prototype.emSmsCodeValidate;
    let mockObject = {
      initSmsCodeRule: function(){},
      initDragCaptchaCodeRule: function(){},
    };
    let initDragCaptchaCodeRuleStub = sinon.stub(mockObject, 'initDragCaptchaCodeRule');
    emSmsCodeValidate.apply(mockObject, [12341]);
    sinon.assert.calledOnce(initDragCaptchaCodeRuleStub);
    initDragCaptchaCodeRuleStub.restore();
  });
});

describe('register:constructor', function() {
  it('register:constructor', function() {
    utilInit.init();
    let register = new Register();
  });
});

describe('register:initSmsCodeRule', function() {
  it('register:initSmsCodeRule', function() {
    utilInit.init(`
      <input class="sms_code" />
    `);
    $.fn.rules = function() {};
    let stub = sinon.stub($.fn, 'rules');
    let initSmsCodeRule = Register.prototype.initSmsCodeRule;
    initSmsCodeRule();
    sinon.assert.calledOnce(stub);
    sinon.assert.calledWithMatch(stub, 'add', {
      required: true,
      unsigned_integer: true,
      rangelength: [6, 6],
      es_remote: {
        type: 'get',
      },
      messages: {
        rangelength: 'validate.sms_code.message'
      }
    });
  });
});