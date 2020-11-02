export default class Create {
  constructor($element) {
    this.$element = $element;
    this.event();
  }

  event() {
    this.validator = this.$element.validate({
      rules: {
        targetId: {
          required: {
            depends() {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
        },
        templateId: {
          required: {
            depends() {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
        },
        code: {
          maxlength: 6,
          certificate_code: true,
          es_remote: true,
          required: {
            depends() {
              $(this).val($.trim($(this).val()));
              return true;
            }
          },
        },
        expiryDay: {
          deadline_check: true,
        },
      },
      messages: {
        targetId: {
          required: Translator.trans('admin_v2.certificate.target_id.check'),
        },
        templateId: {
          required: Translator.trans('admin_v2.certificate.template_id.check'),
        },
        code: {
          es_remote: Translator.trans('admin_v2.certificate.code.exist'),
        },
      }
    });

    $('#create-certificate').on('click', (e) => {
      if (this.validator.form()) {
        $('#create-certificate').button('loading').addClass('disabled');
        this.$element.submit();
      }
    });

    $('.js-auto-issue').on('click', function (e) {
      let $input = $(this).find('.es-switch__input');
      let ToggleVal = $input.val() == $input.data('open') ? $input.data('close') : $input.data('open');
      $input.val(ToggleVal);
      $(this).toggleClass('is-active');
      if ($input.val() == 1) {
        $('.js-auto-send').removeClass('hidden');
        $('.js-close-auto').addClass('hidden');
      } else {
        $('.js-close-auto').removeClass('hidden');
        $('.js-auto-send').addClass('hidden');
      }
    });

    $('#create-certificate-back').on('click',()=>{
      $('#create-certificate-back').button('loading').addClass('disabled');
      $('[name="back"]').val(1);
      $('#certificate-form')[0].submit();
    });
  }
}

jQuery.validator.addMethod('certificate_code', function (value, element, params) {
  return this.optional(element) || /^[a-zA-Z0-9]+$/i.test(value);
}, jQuery.validator.format(Translator.trans('admin_v2.certificate.code.check')));

jQuery.validator.addMethod('deadline_check', function () {
  let value = $('[name = expiryDay]').val();
  if (!value || ((/^\+?[0-9][0-9]*$/.test(value) && value < 6001 && value > 0))) {
    return true;
  }
  return false;
}, Translator.trans('admin_v2.certificate.expiry_day.check'));

new Create($('#certificate-form'));
