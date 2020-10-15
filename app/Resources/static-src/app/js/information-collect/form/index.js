let $form = $('#infomation-collect-form');
let validator = $form.validate({
    rules: {
        'form[name]': {
            chinese_alphanumeric: true,
            byte_minlength: 4,
            byte_maxlength: 18,
            nickname: true,
        },
        'form[gender]': {
            maxlength: 1000,
        },
        'form[age]': {
            check_age: true
        },
        'form[idcard]': {
            idcardNumber: true
        },
        'form[phone]': {
            phone: true,
        },
        'form[email]': {
            email: true,
        },
        'form[wechat]': {
            check_wechat: true,
        },
        'form[qq]': {
            check_qq: true,
        },
        'form[weibo]': {
            check_weibo: true,
            minlength: 4,
            maxlength: 30,
        },
        'form[address_detail]': {
            minlength: 2,
            maxlength: 100,
        },
        'form[occupation]': {
            minlength: 2,
            maxlength: 40,
        },
        'form[company]': {
            minlength: 2,
            maxlength: 40,
        },
        'form[position]': {
            minlength: 2,
            maxlength: 40,
        },
        'form[school]': {
            minlength: 2,
            maxlength: 40,
        },
        'form[grade]': {
            minlength: 2,
            maxlength: 40,
        },
        'form[class]': {
            minlength: 2,
            maxlength: 40,
        },
        'form[country]': {
            minlength: 2,
            maxlength: 40,
        },
        'form[language]': {
            minlength: 2,
            maxlength: 40,
        },
        'form[interest]': {
            minlength: 2,
            maxlength: 100,
        },

    },
    messages: {
        'form[gender]': {
            required: Translator.trans('validate.required.message', { 'display': Translator.trans('user.fields.gender_label') }),
        },
    }
});

if ($('input[name="form[birthday]"]').length) {
    initDatePicker('input[name="form[birthday]"]');
}

$.validator.addMethod('check_age', function (value, element) {
    return this.optional(element) || /^[1-9]([0-9])?$/.test(value);
}, Translator.trans('information_collect.form.check_age_invalid'));

$.validator.addMethod('check_qq', function (value, element) {
    return this.optional(element) || /^[0-9]{5,10}$/.test(value);
}, Translator.trans('validate.valid_qq_input.message'));

$.validator.addMethod('check_wechat', function (value, element) {
    return this.optional(element) || /^[a-zA-Z]([-_a-zA-Z0-9])+$/.test(value);
}, Translator.trans('validate.valid_weixin_input.message'));

$.validator.addMethod('check_weibo', function (value, element) {
    return this.optional(element) || /^[A-Za-z0-9\u4e00-\u9fa5]+$/.test(value);
}, Translator.trans('information_collect.form.check_format_invalid'));

function initDatePicker($target) {
    let $picker = $($target);
    $picker.datetimepicker({
        format: 'yyyy-mm-dd',
        language: document.documentElement.lang,
        minView: 2,
        startView: 4,
        autoclose: true,
        datepicker: true,
        timepicker: false,
        endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000),
    }).on('hide', () => {
        this.validator.form();
    });
}