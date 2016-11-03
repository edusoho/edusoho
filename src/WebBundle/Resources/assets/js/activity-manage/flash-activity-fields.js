$('#condition-select').on('change', event => {
  "use strict";
  let conditionsType = $(event.currentTarget).children('option:selected').val();

  let $conditionsDetail = $("#condition-group");
  if(conditionsType !== 'detail'){
    $conditionsDetail.addClass('hidden');
    return;
  }

  var $step3_form = $("#step3-form");
  let validator = $step3_form.validate({
    onkeyup: false,
    rules: {
      finishDetail: {
        required: true,
        digits: true
      },
    },
    messages: {
      finishDetail: {
        required: "请输入完成条件",
        digits: "完成条件必须为数字"
      }
    }
  });
  $step3_form.data('validator', validator);
  $conditionsDetail.removeClass('hidden');
});
