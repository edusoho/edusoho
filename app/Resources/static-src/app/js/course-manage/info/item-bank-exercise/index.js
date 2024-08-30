import Intro from '../intro';
import Expiry from 'app/js/course-manage/expiry/expiry';
import {Dropdown, Menu, Form, FormItem ,Switch } from '@codeages/design-vue';
import ContractSetting from './contract-setting.vue';
import Vue from 'common/vue';
import Axios from 'axios';
import qs from 'qs';

const axios = Axios.create({
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/vnd.edusoho.v2+json',
    'Content-Type': 'application/x-www-form-urlencoded',
    'X-CSRF-Token': $('meta[name=csrf-token]').attr('content'),
  },
});

Vue.prototype.$axios = axios;
Vue.prototype.$qs = qs;

Vue.use(Dropdown);
Vue.use(Menu);
Vue.filter('trans', function (value, params) {
  if (!value) return '';
  return Translator.trans(value, params);
});


new Vue({
  el: '#contract-setting',
  render: createElement => createElement(ContractSetting, {
    props: {
      exercise: $('#contract-setting').data('exercise')
    },
  }),
});

class ExerciseInfo {
  constructor() {
    this.initValidator();
    this.checkBoxChange();
    this.initDatetimepicker();
    this.taskPriceSetting();
    this.setIntroPosition();
    this.expiry = new Expiry();
  }

  setIntroPosition() {
    const space = 44;
    const introRight = $('.js-course-manage-info').offset().left + space;
    window.onload = () => {
      $('.js-plan-intro').css('right', `${introRight}px`).removeClass('hidden');
    };
  }

  initDatetimepicker() {
    this.initDatePicker('#expiryStartDate');
    this.initDatePicker('#expiryEndDate');
    this.initDatePicker('#deadline');
  }

  initValidator() {
    let $form = $('#course-info-form');
    this.validator = $form.validate({
      currentDom: '#course-submit',
      ajax: true,
      groups: {
        date: 'expiryStartDate expiryEndDate'
      },
      rules: {
        expiryDays: {
          required: () => {
            return $('input[name="expiryMode"]:checked').val() != 'date';
          },
          digits: true,
          max_year: true
        },
        price: {
          required: true,
          positive_price: true,
          min: parseFloat($('#js-course-info').data('minPrice')),
        },
        expiryStartDate: {
          required: () => {
            return $('input[name="expiryMode"]:checked').val() == 'date';
          },
          date: true,
          before_date: '#expiryEndDate'
        },
        expiryEndDate: {
          required: () => {
            return $('input[name="expiryMode"]:checked').val() == 'date';
          },
          date: true,
          after_date: '#expiryStartDate'
        },
      },
      messages: {
        price: Translator.trans($('#js-course-info').data('hintMessage')),
        expiryDays: {
          required: Translator.trans('course.manage.deadline_end_date_error_hint'),
        },
        expiryStartDate: {
          required: Translator.trans('course.manage.expiry_start_date_error_hint'),
          before: Translator.trans('course.manage.expiry_days_error_hint')
        },
        expiryEndDate: {
          required: Translator.trans('course.manage.expiry_end_date_error_hint'),
          after: Translator.trans('course.manage.expiry_start_date_error_hint')
        }
      },
      submitSuccess: (data) => {
        cd.message({ type: 'success', message: Translator.trans('site.save_success_hint') });
        window.location.reload();
      }
    });
    $.validator.addMethod(
      'before',
      function (value, element, params) {
        if ($('input[name="expiryMode"]:checked').val() !== 'date') {
          return true;
        }
        return !value || $(params).val() > value;
      },
      Translator.trans('course.manage.expiry_end_date_error_hint')
    );

    $.validator.addMethod(
      'after',
      function (value, element, params) {
        if ($('input[name="expiryMode"]:checked').val() !== 'date') {
          return true;
        }
        return !value || $(params).val() < value;
      },
      Translator.trans('course.manage.expiry_start_date_error_hint')
    );

    $.validator.addMethod('max_year', function (value, element) {
      return this.optional(element) || value <= 7300;
    }, Translator.trans('course.manage.max_year_error_hint'));

    this.saveForm();
  }

  saveForm() {
    $('#course-submit').on('click', (event) => {
      // 阻止表单的默认提交行为
      event.preventDefault();

      // 访问Vue组件的数据
      const formData = window.marketingForm;

      // 将formData中的数据添加到表单中
      const $form = $('#course-info-form');

      // 清除之前添加的隐藏字段，以免重复添加
      $form.find('input[name^="marketingForm"]').remove();

      // 遍历formData并添加隐藏字段
      Object.keys(formData).forEach(key => {
        const value = formData[key];
        $('<input>')
          .attr('type', 'hidden')
          .attr('name', `${key}`)
          .attr('value', value)
          .appendTo($form);
      });
      this.expiry.commonExpiryMode();
      if (this.validator.form()) {
        $('#course-info-form').submit();
      }
    });
  }

  initDatePicker($id) {
    let $picker = $($id);
    $picker.datetimepicker({
      format: 'yyyy-mm-dd',
      language: document.documentElement.lang,
      minView: 2, //month
      autoclose: true,
      endDate: new Date(Date.now() + 86400 * 365 * 10 * 1000)
    }).on('hide', () => {
      this.validator && this.validator.element($picker);
    });
    $picker.datetimepicker('setStartDate', new Date());
  }

  taskPriceSetting() {
    const $priceItem = $('.js-task-price-setting');
    $priceItem.on('click', 'li', (event) => {
      const $li = $(event.currentTarget);
      $li.toggleClass('open');
      const $input = $li.find('input');
      $input.prop('checked', !$input.is(':checked'));
    });

    $priceItem.on('click', 'input', (event) => {
      event.stopPropagation();
      const $input = $(event.target);
      $input.closest('li').toggleClass('open');
    });
  }


  checkBoxChange() {
    $('input[name="expiryDays"]').on('blur', (event) => {
      this.validator.element($(event.target));
    });

  }
}

new ExerciseInfo();

setTimeout(function() {
  new Intro();
}, 500);