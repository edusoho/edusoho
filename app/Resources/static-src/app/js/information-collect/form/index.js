import notify from 'common/notify';

let $form = $('#infomation-collect-form');
let validator = $form.validate({
    rules: {
        'name': {
            chinese_alphanumeric: true,
            byte_minlength: 4,
            byte_maxlength: 18,
            nickname: true,
        },
        'gender': {
            maxlength: 1000,
        },
        'age': {
            check_age: true
        },
        'idcard': {
            idcardNumber: true
        },
        'phone': {
            phone: true,
        },
        'email': {
            email: true,
        },
        'wechat': {
            check_wechat: true,
        },
        'qq': {
            check_qq: true,
        },
        'weibo': {
            check_weibo: true,
            minlength: 4,
            maxlength: 30,
        },
        'address_detail': {
            minlength: 2,
            maxlength: 100,
        },
        'occupation': {
            minlength: 2,
            maxlength: 40,
        },
        'company': {
            minlength: 2,
            maxlength: 40,
        },
        'position': {
            minlength: 2,
            maxlength: 40,
        },
        'school': {
            minlength: 2,
            maxlength: 40,
        },
        'grade': {
            minlength: 2,
            maxlength: 40,
        },
        'class': {
            minlength: 2,
            maxlength: 40,
        },
        'country': {
            minlength: 2,
            maxlength: 40,
        },
        'language': {
            minlength: 2,
            maxlength: 40,
        },
        'interest': {
            minlength: 2,
            maxlength: 100,
        },

    },
    messages: {
        'gender': {
            required: Translator.trans('validate.required.message', { 'display': Translator.trans('user.fields.gender_label') }),
        },
    }
});

if ($('input[name="birthday"]').length) {
    initDatePicker('input[name="birthday"]');
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

if ($('input[name="province_city_area"]').length) {
    initProvinceOptionsAndSelect();

    cd.select({
        el: '#city',
        type: 'single'
    }).on('change', (value, text) => {
        $('#area').hasClass('hidden') ? $('#area').removeClass('hidden') : '';
        $('input[name="area"]').attr('value', text);
        initAreaSelectOptions(value);
    });

    cd.select({
        el: '#area',
        type: 'single'
    }).on('change', (value, text) => {
        $('input[name="province_city_area"]').val(JSON.stringify([
            window.arealist.province_list[$('input[name="province"]').val()],
            window.arealist.city_list[$('input[name="city"]').val()],
            window.arealist.county_list[$('input[name="area"]').val()]
        ]));
        validator.form();
    });
}


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
        validator.form();
    });
}

function initProvinceOptionsAndSelect() {
    appendSelectOption($('.province-select-options'), window.arealist.province_list);

    cd.select({
        el: '#province',
        type: 'single'
    }).on('change', (value, text) => {
        $('#city').hasClass('hidden') ? $('#city').removeClass('hidden') : '';
        initCitySelectOptions(value);
        initAreaSelectOptions();
    });
}

function initCitySelectOptions(provinceIndex) {
    $('.city-select-options').empty();
    $('input[name="province_city_area"]').attr('value', '');
    $('input[name="city"]').attr('value', '');
    $('input[name="city"]').siblings('.select-value').html('<span class="text-muted"> ' + '请选择' + '</span>');
    appendSelectOption($('.city-select-options'), window.arealist.city_list, provinceIndex, provinceIndex + 10000);
}

function initAreaSelectOptions(cityIndex = 0) {
    $('.area-select-options').empty();
    $('input[name="province_city_area"]').attr('value', '');
    $('input[name="area"]').attr('value', '');
    $('input[name="area"]').siblings('.select-value').html('<span class="text-muted"> ' + '请选择' + '</span>');
    if (!cityIndex) {
        return;
    }
    appendSelectOption($('.area-select-options'), window.arealist.county_list, cityIndex, cityIndex + 100);
}

function appendSelectOption($target, options, sliceStartIndex = 0, sliceEndIndex = 0) {
    $.each(options, function (index, value) {
        if (sliceEndIndex > 0 && index >= sliceEndIndex) {
            return;
        }
        if (index > sliceStartIndex) {
            $target.append('<li data-value="' + index + '">' + value + '</li>');
        }
    });
}

$('.js-btn-save').on('click', (event) => {

    if (!validator.form()) {
        return;
    }
    let formData = {};
    $.each($form.serializeArray(), function (index, item) {
        if (item.name.indexOf('[') >= 0) {
            formData[item.name.slice(item.name.indexOf('[') + 1, item.name.indexOf(']'))] = item.value;
        } else {
            formData[item.name] = item.value;
        }
    });

    $.ajax({
        type: "POST",
        data: formData,
        beforeSend: function (request) {
            request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
            request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
        },
        url: '/api/information_collect_form',
        success: function (resp) {
            notify('success', Translator.trans('site.save_success_hint'));
            window.location.href = $('.js-btn-save').data('goto');
        }
    });
});