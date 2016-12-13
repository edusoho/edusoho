import ReactDOM from 'react-dom';
import React from 'react';
import MultiInput from '../../../common/widget/multi-input';
import sortList from 'common/sortable';

class Marketing {
  constructor() {
    this.init();
  }

  init() {
    let $form = $('#course-marketing-form');

    ReactDOM.render( <MultiInput items = {$("#course-services").data("init-value")} fieldName={$("#course-services").data('field-name')} sortable={true}/>,
      document.getElementById('course-services')
    );

    let validator = $form.validate({
      onkeyup: false,
      rules: {
        price: {
          required: '#chargeMode:checked',
          currency: true
        },
        tryLookLength: {
          required: '#enableTryLook:checked',
          digits: true
        },
        tryLookLimit: {
          required: '#enableTryLook:checked',
          digits: true
        }
      },
      messages: {
        price: {
          required: Translator.trans('请输入价格'),
          currency: Translator.trans('请输入价格，最多两位小数')
        },
        tryLookLength: Translator.trans('请输入试看时长'),
        tryLookLimit: Translator.trans('请输入视频观看时长限制')
      }
    });

    $.validator.addMethod(
      "currency",
      function(value, element, params) {
        return this.optional(element) || /^\d{0,8}(\.\d{0,2})?$/.test(value);
      },
      Translator.trans('请输入价格，最多两位小数')
    );

    $('input[name="isFree"]').on('change', function(event) {
      $('.js-is-free').toggle($('input[name="isFree"]:checked').val() == 0 ? 'show' : 'hide');
    });
    $('input[name="tryLookable"]').on('change', function(event) {
      $('.js-enable-try-look').toggle($('input[name="tryLookable"]:checked').val() == 0 ? 'show' : 'hide');
    });

    $('#course-submit').click(function(evt) {
      if (validator.form()) {
        $(evt.currentTarget).button('loading');
        $form.submit();
      }
    });
  }
}


new Marketing();
