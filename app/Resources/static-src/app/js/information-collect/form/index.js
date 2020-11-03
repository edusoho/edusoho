import notify from 'common/notify';
import Api from 'common/api';

let $form = $('#infomation-collect-form');
let validator = $form.validate({
    rules: {
        'name': {
            chinese_alphanumeric: true,
            minlength: 2,
            maxlength: 36,
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
            check_email: true,
            minlength: 6,
            maxlength: 64,
        },
        'wechat': {
            check_wechat: true,
            minlength: 6,
            maxlength: 20,
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

$.validator.addMethod('check_email', function (value, element) {
    return this.optional(element) || /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(value);
}, Translator.trans('information_collect.form.check_format_invalid'));

if ($('input[name="province_city_area"]').length) {
    initProvinceOptions();
    cd.select({
        el: '#province',
        type: 'single'
    }).on('change', (value, text) => {
        $('#city').hasClass('hidden') ? $('#city').removeClass('hidden') : '';
        initCitySelectOptions(value);
        initAreaSelectOptions();
    });

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
        startDate: new Date('1900-01-01'),
        endDate: new Date()
    });
}

function initProvinceOptions() {
    appendSelectOption($('.province-select-options'), window.arealist.province_list);

    if ($('input[name="province"]').val()) {
        let provinceIndex = 0;
        $.each(window.arealist.province_list, function (index, value) {
            if (value == $('input[name="province"]').val()) {
                provinceIndex = index;
                return;
            }
        });
        initCitySelectOptions(provinceIndex, $('input[name="city"]').val());
    }

    if ($('input[name="city"]').val()) {
        let cityIndex = 0;
        $.each(window.arealist.city_list, function (index, value) {
            if (value == $('input[name="city"]').val()) {
                cityIndex = index;
                return;
            }
        });
        initAreaSelectOptions(cityIndex, $('input[name="area"]').val());
    }
}

function initCitySelectOptions(provinceIndex, selectedValue = '') {
    $('.city-select-options').empty();
    $('input[name="province_city_area"]').attr('value', '');
    $('input[name="city"]').attr('value', selectedValue);
    if (selectedValue) {
        $('input[name="city"]').siblings('.select-value').html('<span> ' + selectedValue + '</span>');
    } else {
        $('input[name="city"]').siblings('.select-value').html('<span class="text-muted"> ' + Translator.trans('site.choose_hint') + '</span>');
    }
    appendSelectOption($('.city-select-options'), window.arealist.city_list, parseInt(provinceIndex), parseInt(provinceIndex) + 10000);
}

function initAreaSelectOptions(cityIndex, selectedValue = '') {
    $('.area-select-options').empty();
    $('input[name="province_city_area"]').attr('value', '');
    $('input[name="area"]').attr('value', selectedValue);
    $('input[name="area"]').siblings('.select-value').html('<span class="text-muted"> ' + selectedValue + '</span>');
    if (selectedValue) {
        $('input[name="area"]').siblings('.select-value').html('<span> ' + selectedValue + '</span>');
        $('input[name="province_city_area"]').val(JSON.stringify([$('input[name="province"]').val(), $('input[name="city"]').val(), selectedValue]));
    } else {
        $('input[name="area"]').siblings('.select-value').html('<span class="text-muted"> ' + Translator.trans('site.choose_hint') + '</span>');
    }
    appendSelectOption($('.area-select-options'), window.arealist.county_list, parseInt(cityIndex), parseInt(cityIndex) + 100);
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

    Api.informationCollect.submitEvent({
        data: $form.serialize(),
    }).then((resp) => {
        notify('success', Translator.trans('site.save_success_hint'));
        submitAfter();
    }).catch(() => {
        notify('danger', Translator.trans('site.save_error_hint'));
    });
});

$("#skip").click(function () {
    submitAfter();
});

function submitAfter() {
    if ('1' == $('input[name="in_order"]').val()) {
        $('input[name="informationCollectIsSubmited"]').val('1');
        $('.order-center-information-collect-content').removeClass('error');
        $("#modal").modal('hide');
    } else {
        window.location.href = $('.js-btn-save').data('goto');
    }
}

if ($('.js-delete-content-btn').length) {
    $('.js-delete-content-btn').on('click', (event) => {
        $($(event.currentTarget).data('target')).val('');
    });
}