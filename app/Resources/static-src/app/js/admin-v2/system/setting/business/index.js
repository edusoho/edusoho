initUploadImg();

let validator = $('#business-form').validate({
  rules: {
    icp: {
      required: true,
      trim: true
    },
    icpUrl: {
      required: true,
      url: true
    },
    recordUrl: {
      url: true
    }
  },
  ajax: true,
  submitSuccess(data) {
    cd.message({type: 'success', message: Translator.trans('site.save_success_hint')});
    $('#save-business').button('reset');
  }
});

$('#save-business').on('click', (event) => {
  const $this = $(event.currentTarget);

  if (validator.form()) {
    $this.button('loading');
    $('#business-form').submit();
  }
});

$('.js-image-delete').on('click', function () {
  if (!confirm(Translator.trans('admin.site.delete_hint'))) return false;
  let $btn = $(this);
  let $recordContainer = $('.js-uploaded-image');
  $recordContainer.attr('src', '/assets/img/default/gif.png');
  $('#business-form').find('[name=recordPicture]').val('');
  $btn.addClass('hidden');
});

function initUploadImg() {
  cd.upload({
    el: '#recordPicture',
  }).on('success', (event, file, src) => {
    let $this = $(event.currentTarget);
    let $target = $($this.data('target'));

    let formData = new FormData();

    formData.append('token', $this.data('token'));
    formData.append('file', file);

    uploadImage(formData).then(function (data) {
      $target.attr('src', data.url);
      $('input[name="recordPicture"]').val(data.url);
      $this.closest('.cd-image-upload').find('.js-image-delete').removeClass('hidden');
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

