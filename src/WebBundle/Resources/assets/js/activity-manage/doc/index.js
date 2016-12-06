import FileChooser from '../../file-chooser/file-choose';
import {chooserUiOpen,chooserUiClose,showChooserType} from '../widget/chooser-ui.js';
let $mediaId = $('[name="mediaId"]');
let $select = $('#condition-select');
let fileChooser = new FileChooser();

showChooserType($mediaId);
inItStep2form();

function inItStep2form() {
  var $step2_form = $("#step2-form");
  var validator = $step2_form.data('validator');
  validator = $step2_form.validate({
      rules: {
        title:{
            required: true,
            maxlength: 50,
        },
        mediaId: 'required',
      },
      messages: {
        mediaId: {
          required:'请上传或选择%display%'
        }
      }
  });
}

function onConditionTimeType() {
  let $step3_form = $("#step3-form");
  let validator = $step3_form.validate({
    onkeyup: false,
    rules: {
      title: {
        required: true,
        maxlength: 50,
      },
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

if($select.children('option:selected').val() === 'time'){
  onConditionTimeType();
}

fileChooser.on('select', (file) => {
  chooserUiClose();
  $mediaId.val(file.id);
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