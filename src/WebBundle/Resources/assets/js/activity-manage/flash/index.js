import FileChooser from '../../file-chooser/file-choose';
import { chooserUiOpen, chooserUiClose, showChooserType } from '../widget/chooser-ui.js';
let $mediaId = $('[name="mediaId"]');

showChooserType($mediaId);
_inItStep2form();

function _inItStep2form() {
  var $form = $('#step2-form');
  var validator = $form.validate({
    rules: {
      title: {
        required: true,
        maxlength: 50,
      },
      mediaId: 'required',
    },
    messages: {
      mediaId: {
        required: '请上传或选择%display%'
      }
    }
  });

  $form.data('validator', validator);
}

let onConditionTimeType = () => {

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
        required: '请输入至少观看多少分钟',
      },
    }
  });

  $step3_form.data('validator', validator);
  let $conditionsDetail = $("#condition-group");
  $conditionsDetail.removeClass('hidden');
};

let $select = $('#condition-select');

if ($select.children('option:selected').val() === 'time') {
  onConditionTimeType();
}

let fileChooser = new FileChooser();

fileChooser.on('select', (file) => {
  chooserUiClose();
  $mediaId.val(file.id);
});

$select.on('change', event => {
  let conditionsType = $(event.currentTarget).children('option:selected').val();

  let $conditionsDetail = $("#condition-group");
  if (conditionsType !== 'time') {
    $conditionsDetail.addClass('hidden');
  } else {
    onConditionTimeType();
  }
});
