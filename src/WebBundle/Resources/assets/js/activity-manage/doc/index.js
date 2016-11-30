import FileChooser from '../../file-chooser/file-choose';

let onConditionTimeType = () => {
  let $step3_form = $("#step3-form");
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
  let $conditionsDetail = $("#condition-group");
  $conditionsDetail.removeClass('hidden');
};

let $select = $('#condition-select');

if($select.children('option:selected').val() === 'time'){
  onConditionTimeType();
}

let fileChooser = new FileChooser();

fileChooser.on('select', (file) => {
  $('.hidden-data').find('#mediaId').val(file.id);
});

$select.on('change', event => {
  let conditionsType = $(event.currentTarget).children('option:selected').val();

  let $conditionsDetail = $("#condition-group");
  if(conditionsType !== 'time'){
    $conditionsDetail.addClass('hidden');
  }else {
    onConditionTimeType();
  }
});