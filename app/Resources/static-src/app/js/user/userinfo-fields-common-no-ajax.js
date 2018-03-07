import ParentUserInfo from './userinfo-fields-common';

export default class UserInfoFieldsItemValidate extends ParentUserInfo {
  constructor(options) {
    super(options);
  }

  createValidator() {
    this.validator = this.$element.validate({
      currentDom: '#form-submit-btn',
      rules: {
        email: {
          required: true,
          email: true,
          remote: {
            url: $('#email').data('url'),
            type: 'get',
            data: {
              'value': function () {
                return $('#email').val();
              }
            }
          }
        },
        mobile: {
          required: true,
          phone: true,
          remote: {
            url: $('#mobile').data('url'),
            type: 'get',
            data: {
              'value': function () {
                return $('#mobile').val();
              }
            }
          }
        },
        truename: {
          required: true,
          chinese_alphanumeric: true,
          minlength: 2,
          maxlength: 36,
        },
        qq: {
          required: true,
          qq: true,
        },
        idcard: {
          required: true,
          idcardNumber: true,
        },
        gender: {
          required: true,
        },
        company: {
          required: true,
        },
        job: {
          required: true,
        },
        weibo: {
          required: true,
          url: true,
        },
        weixin: {
          required: true,
        }
      },
      messages: {
        gender: {
          required: Translator.trans('site.choose_gender_hint'),
        },
        mobile: {
          phone: Translator.trans('validate.phone.message'),
        }
      }
    });
    this.getCustomFields();
  }

}
