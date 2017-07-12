let $form = $('#reward-point-exchange-form');
let $modal = $form.parents('.modal');
import notify from 'common/notify';

let validator = $form.validate({
  rules: {
    telephone: {
      phone: true
    },
    email: {
      email: true
    },
    address: {
      byte_maxlength: 60,
      chinese_alphanumeric: true,
      required: {
        depends () {
        $(this).val($.trim($(this).val()));
        return true;
        }
      },
    },
  }
});

$('.js-exchange-product').click((event) => {
  if (validator.form()) {
    $(event.currentTarget).button('loadding');

    $.post($form.prop('action'), $form.serialize(), function(result) {
      if (result.success) {
        notify('success', result.message);
        $modal.modal('hide');
      } else {
        notify('warning', result.message);
      }
    }).error(function(){
      notify('danger', Translator.trans('兑换失败'));
    });
  }
});