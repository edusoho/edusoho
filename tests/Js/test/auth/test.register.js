import * as utilInit from '../../util/init.js';
import SmsSender from 'app/common/widget/sms-sender';
import Register from 'test-src/app/js/auth/register/register.js';
const assert = require('chai').assert;
const sinon = require('sinon');


describe('register:initValidator', function() {
  it('register:initValidator', function() {
    let validateStub = sinon.stub($.fn, 'validate');

    let initValidator = Register.prototype.initValidator;
    initValidator();
    let expectedParams = {
      rules: {
        nickname: {
          required: true,
          byte_minlength: 4,
          byte_maxlength: 18,
          nickname: true,
          chinese_alphanumeric: true,
          es_remote: {
            type: 'get',
          }
        },
        password: {
          minlength: 5,
          maxlength: 20,
        }
      },
    };
    sinon.assert.calledWith(validateStub, expectedParams);
    validateStub.restore();
  });
});

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

describe('register:initDate', function() {
  it('register:initDate', function() {
    let stub = sinon.stub($.fn, 'datetimepicker');
    let initDate = Register.prototype.initDate;
    initDate();
    sinon.assert.calledWith(stub, {
      autoclose: true,
      format: 'yyyy-mm-dd',
      minView: 'month',
      language: window.document.documentElement.lang
    });
    stub.restore();
  });
});

describe('register:initCaptchaCode', function() {
  beforeEach(function() {
    utilInit.init(`
      <a id="getcode_num" data-url="test"></a>
    `);
  });
  it('register:initCaptchaCode', function() {
    let mockObject = {
      initCaptchaCodeRule: function(){}
    };
    let stub = sinon.stub(mockObject, 'initCaptchaCodeRule');
    let randomStub = sinon.stub(Math, 'random').returns('12349123');
    let initCaptchaCode = Register.prototype.initCaptchaCode;

    initCaptchaCode.apply(mockObject);
    $('#getcode_num').trigger('click');
    assert.equal('test?12349123', $('#getcode_num').attr('src'));
    stub.restore();
    randomStub.restore();
    sinon.assert.calledOnce(stub);
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
      initCaptchaCodeRule: function(){}
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
      initCaptchaCodeRule: function(){}
    };
    let initCaptchaCodeRuleStub = sinon.stub(mockObject, 'initCaptchaCodeRule');
    emSmsCodeValidate.apply(mockObject, [12341]);
    sinon.assert.calledOnce(initCaptchaCodeRuleStub);
    initCaptchaCodeRuleStub.restore();
  });
});

describe('register:initUserTermsRule', function() {
  it('register:initUserTermsRule', function() {
    utilInit.init(`
      <div id="user_terms">
      </div>
    `);
    $.fn.rules = function() {};
    let stub = sinon.stub($.fn, 'rules');
    let initUserTermsRule = Register.prototype.initUserTermsRule;
    initUserTermsRule();
    sinon.assert.calledOnce(stub);
    sinon.assert.calledWith(stub, 'add', {
      required: true,
      messages: {
        required: 'validate.user_terms.message'
      }
    });
  });
});

describe('register:constructor', function() {
  it('register:constructor', function() {
    utilInit.init();
    let register = new Register();
  });
});


describe('register:initRegisterTypeRule', function() {
  it('register:initRegisterTypeRule if email exsit', function() {
    utilInit.init(`
      <input name="email" />
    `);
    $.fn.rules = function() {};
    let stub = sinon.stub($.fn, 'rules');
    let initRegisterTypeRule = Register.prototype.initRegisterTypeRule;
    initRegisterTypeRule();
    sinon.assert.calledOnce(stub);
    sinon.assert.calledWith(stub, 'add', {
      required: true,
      email: true,
      es_remote: {
        type: 'get'
      },
      messages: {
        required: 'validate.valid_email_input.message',
      }
    });
  });

  it('register:initRegisterTypeRule if emailOrMobile exsit', function() {
    utilInit.init(`
      <input name="emailOrMobile" />
    `);
    $.fn.rules = function() {};
    let stub = sinon.stub($.fn, 'rules');
    let initRegisterTypeRule = Register.prototype.initRegisterTypeRule;
    initRegisterTypeRule();
    sinon.assert.calledOnce(stub);
    sinon.assert.calledWithMatch(stub, 'add',{
      required: true,
      email_or_mobile_check: true,
      messages: {
        required: 'validate.phone_and_email_input.message'
      },
    });
  });
});

describe('register:initUserTermsRule', function() {
  it('register:initUserTermsRule', function() {
    utilInit.init(`
      <div class="invitecode">
      </div>
    `);
    $.fn.rules = function() {};
    let stub = sinon.stub($.fn, 'rules');
    let initInviteCodeRule = Register.prototype.initInviteCodeRule;
    initInviteCodeRule();
    sinon.assert.calledOnce(stub);
    sinon.assert.calledWith(stub, 'add', {
      required: false,
      reg_inviteCode: true,
      es_remote: {
        type: 'get'
      }
    });
  });
});

describe('register:initInviteCodeRule', function() {
  it('register:initInviteCodeRule', function() {
    utilInit.init(`
      <input class="captcha_code" />
    `);
    $.fn.rules = function() {};
    let stub = sinon.stub($.fn, 'rules');
    let initCaptchaCodeRule = Register.prototype.initCaptchaCodeRule;
    initCaptchaCodeRule();
    sinon.assert.calledOnce(stub);
    sinon.assert.calledWithMatch(stub, 'add', {
      required: true,
      alphanumeric: true,
      es_remote: {
        type: 'get'
      },
    });
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

// describe('register:initMobileMsgVeriCodeSendBtn', function() {
//   it('register:initMobileMsgVeriCodeSendBtn', function() {
//     utilInit.init(`
//       <div class="js-sms-send-btn"></div>
//       <input name="verifiedMobile" />
//       <div id="js-time-left"></div>
//     `);
//     let stub = sinon.spy(() => sinon.createStubInstance(SmsSender));
    
//     //let stub = sinon.spy(SmsSender.prototype, 'constructor');
//     let initMobileMsgVeriCodeSendBtn = Register.prototype.initMobileMsgVeriCodeSendBtn;
//     initMobileMsgVeriCodeSendBtn();
//     $('.js-sms-send-btn').trigger('click');
//     //sinon.assert.calledOnce(stub);
//     // sinon.assert.calledWithMatch(stub, {
//     //   element: '.js-sms-send',
//     //   url: $('.js-sms-send').data('smsUrl'),
//     //   smsType: 'sms_registration',
//     //   dataTo: 'verifiedMobile',
//     //   captcha: false,
//     // });
//     stub.restore();
//   });
// });
