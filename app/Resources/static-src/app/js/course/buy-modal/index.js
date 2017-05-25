import UserInfoFieldsItemValidate from 'app/js/user/userinfo-fields-common.js'
let $form = $('#course-buy-form');
let $modal = $form.closest('.modal');

let userInfoFieldsItemValidatenew = new UserInfoFieldsItemValidate($form);

console.log(userInfoFieldsItemValidatenew);

$('#show-coupon-input').on('click', function () {
  let $form = $(this).parents('form');
  if ($(this).data('status') == 'hide') {
    $form.find('.coupon-input-group').removeClass('hide');
    $form.find('#show-coupon').addClass('hide');
    $form.find('#hide-coupon').removeClass('hide');
    $(this).data('status', 'show');
  } else if ($(this).data('status') == 'show') {
    $form.find('.coupon-input-group').addClass('hide');
    $form.find('#show-coupon').removeClass('hide');
    $form.find('#hide-coupon').addClass('hide');
    $(this).data('status', 'hide');
  }
});

$("input[role='payTypeChoices']").on('click', function () {

  $("#password").prop("type", "password");

  if ($(this).val() == "chargeCoin") {
    $("#screct").show();

    $('[name="password"]').rules('add', {
      required: true,
      remote: true,
    })
    if (parseFloat($("#leftMoney").html()) < parseFloat($("#neededMoney").html())) {
      $("#notify").show();
      $modal.find('[type=submit]').addClass('disabled');
    }
  } else if ($(this).val() == "zhiFuBao") {
    $('[name="password"]').rules('remove');
    $("#screct").hide();
    $("#notify").hide();
    $modal.find('[type=submit]').removeClass('disabled');
  }
})

$('.btn-use-coupon').on('click', function () {

  coupon_code = $('[name=coupon]').val();

  $.post($(this).data('url'), { code: coupon_code }, function (response) {
    if (response.useable == 'yes') {

      let html = '<span class="control-text"><strong class="money">'
        + response.afterAmount
        + '</strong><span class="text-muted">' + Translator.trans('元') + '</span> - <span class="text-muted">' + Translator.trans('已优惠') + '</span><strong>'
        + response.decreaseAmount
        + '</strong><span class="text-muted">' + Translator.trans('元') + '</span></span>';

      $('.money-text').html(html);
      if (response.afterAmount === '0.00') {
        $('#course-pay').text(Translator.trans('去学习'));
      }

      $('.coupon-error').html('');
      $('[name=coupon]').attr("readonly", true);
      $('.btn-use-coupon').addClass('disabled');
    } else {
      let message = '<span class="text-danger">' + response.message + '</span>';
      $('.coupon-error').html(message).show();
      $('[name=coupon]').val('');
    }
  });
});

$('#course-pay').click(() => {
  if (userInfoFieldsItemValidatenew.validator.form()) {
    $()
  }
})

