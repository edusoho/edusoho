import EsWebUploader from 'common/es-webuploader.js';

let editor = CKEDITOR.replace('profile_about', {
  toolbar: 'Simple',
  fileSingleSizeLimit: app.fileSingleSizeLimit,
  filebrowserImageUploadUrl: $('#profile_about').data('imageUploadUrl')
});

let uploader = new EsWebUploader({
  element: '#upload-picture-btn',
  onUploadSuccess: function(file, response) {
    let url = $('#upload-picture-btn').data('gotoUrl');
    $.get(url, function(html) {
      $('#modal').modal('show').html(html);
    });
  }
});

let validator = $('#user-profile-form').validate({
  rules: {
    'profile[about]': 'required',
    'profile[title]': {
      required: true,
      chinese_limit: 24
    },
    'profile_avatar': 'required'
  },
  ajax: true,
  submitSuccess(data) {
    cd.message({type: 'success', message: Translator.trans('settings.user_profile.save_success_hint')});
    
    setTimeout(function() {
      window.location.reload();
    }, 1000);
  }
});

$('#profile-save-btn').on('click', (event) => {
  const $this = $(event.currentTarget);

  if (editor.updateElement() && validator.form()) {
    $this.button('loading');
    $('#user-profile-form').submit();
  }
});

