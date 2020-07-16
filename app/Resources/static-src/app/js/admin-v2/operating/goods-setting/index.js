initUploadImg();

let validator = $('#goods-setting-form').validate({
  rules: {},
  ajax: true,
  submitSuccess(data) {
    cd.message({type: 'success', message: Translator.trans('site.save_success_hint')});
    $('.js-setting-save-btn').button('reset');
  }
});

initLabelValidator();

$('.js-setting-save-btn').on('click', (event) => {
  const $this = $(event.currentTarget);

  if (validator.form()) {
    $this.button('loading');
    $('#goods-setting-form').submit();
  }
});

function initUploadImg() {
  cd.upload({
    el: '#cd-upload',
  }).on('success', (event, file, src) => {
    let $this = $(event.currentTarget);
    let $target = $($this.data('target'));

    let formData = new FormData();

    formData.append('token', $this.data('token'));
    formData.append('file', file);

    uploadImage(formData).then(function (data) {
      $target.attr('src', data.url);
      $('input[name="leading[qrcode]"]').val(data.uri);
    });
  }).on('error', (code) => {
    $el.val('');
    if (code === 'FILE_SIZE_LIMIT') {
      cd.message({
        type: 'danger',
        message: Translator.trans('uploader.size_2m_limit_hint')
      });
    } else if (code === 5006201) {
      cd.message({
        type: 'danger',
        message: Translator.trans('uploader.type_denied_limit_hint')
      });
    }
  });
}

function uploadImage(formData) {
  return new Promise(function (resolve, reject) {
    $.ajax({
      url: app.uploadUrl,
      type: 'POST',
      cache: false,
      data: formData,
      processData: false,
      contentType: false,
    }).done(function (data) {
      resolve(data);
    });
  });
};

function initLabelValidator() {
  validator.resetForm();

  if ($('input[name="leading_join_enabled"]:checked').val() == 1) {
    $('#leading-label').rules('add', {
      required: true,
      maxlength: 20,
    });
    $('#leading-description').rules('add', {
      required: true,
      maxlength: 40,
    });
    $('input[name="leading[qrcode]"]').rules('add', {
      required: true
    });
  } else {
    $('#leading-label').rules('remove');
    $('#leading-description').rules('remove');
    $('input[name="leading[qrcode]"]').rules('remove');
  }
}

$('input[name="leading_join_enabled"]').on('change', (event) => {
  initLabelValidator();
});


