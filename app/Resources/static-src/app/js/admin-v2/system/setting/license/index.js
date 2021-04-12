initUploadImg();
initPermit();
permitSettingAction();
let validator = $('#license-form').validate({
  rules: {
    license_url: {
      url: true
    }
  },
  ajax: true,
  submitSuccess(data) {
    console.log(data);
    cd.message({type: 'success', message: Translator.trans('site.save_success_hint')});
    $('#save_license').button('reset');
  }
});

$('#save_license').on('click', (event) => {
  const $this = $(event.currentTarget);
  if (validator.form()) {
    $this.button('loading');
    $('#license-form').submit();
  }
});

cd.upload({
  el: '#license-picture',
}).on('success', (event, file, src) => {
  let $this = $(event.currentTarget);
  let $target = $($this.data('target'));

  let formData = new FormData();

  formData.append('token', $this.data('token'));
  formData.append('file', file);

  uploadImage(formData).then(function (data) {
    $target.attr('src', data.url);
    $('input[name="license_picture"]').val(data.url);
    $('.js-image-delete').removeClass('hidden');
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

$('.js-image-delete').on('click', function () {
  if (!confirm(Translator.trans('admin.site.delete_hint'))) return false;
  let $btn = $(this);
  let $recordContainer = $btn.closest('.cd-image-upload').find('.js-uploaded-image');
  $recordContainer.attr('src', '/assets/img/default/gif.png');
  $('[name="license_picture"]').val('');
  $btn.addClass('hidden');
});

function initUploadImg() {
  cd.upload({
    el: '#license_picture',
  }).on('success', (event, file, src) => {
    let $this = $(event.currentTarget);
    let $target = $($this.data('target'));

    let formData = new FormData();

    formData.append('token', $this.data('token'));
    formData.append('file', file);

    uploadImage(formData).then(function (data) {
      $target.attr('src', data.url);
      $('input[name="license_picture"]').val(data.url);
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

function initPermitsUploadImg($index=0) {
  cd.upload({
    el: '#permits'+$index,
  }).on('success', (event, file, src) => {
    let $this = $(event.currentTarget);
    let $target = $($this.data('target'));

    let formData = new FormData();

    formData.append('token', $this.data('token'));
    formData.append('file', file);

    uploadImage(formData).then(function (data) {
      $target.attr('src', data.url);
      $('input[name="permits['+$index+'][picture]"]').val(data.url);
      $('#js-image-delete-'+$index).removeClass('hidden');
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
}
function initPermit($permitIndex=0) {
  let $form = $('#license-form');
  let permitIndex = $('.setting_permit').length - 1;
  $('.setting_permit').last().find('.addSettingPermitBtn').show();
}

function removePermitPicture($removeBtnArray, $permitIndex) {
  $($removeBtnArray[$permitIndex]).on('click', function () {

    let $btn = $(this);
    let $recordContainer = $btn.closest('.cd-image-upload').find('.js-uploaded-image');
    if (!confirm(Translator.trans('admin.site.delete_hint'))) return false;

    $('input[name="permits['+$permitIndex+'][picture]"]').val('');
    $recordContainer.attr('src', '/assets/img/default/gif.png');
    $btn.addClass('hidden');
    cd.message({type: 'success', message: Translator.trans('admin.setting.delete_permit_picture_success_hint')});
  }).error(function () {
    cd.message({type: 'danger', message: Translator.trans('admin.setting.delete_permit_picture_fail_hint')});
  });
}

function removeSettingPermit($removeSettingPermitBtnArray, $permitIndex) {
  $($removeSettingPermitBtnArray[$permitIndex]).on('click', function () {
    let $settingPermit = '#settingPermit_' + $permitIndex;
    let delConfirm = confirm(Translator.trans('admin.setting.delete_permit_setting_delete_confirom'));
    if (delConfirm) {
      $($settingPermit).remove();
      $('.setting_permit').last().find('.addSettingPermitBtn').show();
    }
  });
}

function addSettingPermit($addSettingPermitBtnArray, $permitIndex) {
  $($addSettingPermitBtnArray[$permitIndex]).on('click', function () {
    $(this).hide();
    if ($('.setting_permit').length < 10) {
      appendPermit('#settingPermit_', ++$permitIndex);
    } else {
      cd.message({type: 'danger', message: Translator.trans('admin.setting.delete_permit_setting_max_number')});
    }
  });
}

function permitSettingElementAction($permitIndex) {
  let uploaderArray = [];
  let removeBtnArray = [];
  let removeSettingPermitBtnArray = [];
  let addSettingPermitBtnArray = [];
  if ($permitIndex === 0) {
    $('#removeSettingPermitBtn_0').hide();
  }
  initPermitsUploadImg($permitIndex);

  removeBtnArray[$permitIndex] = '#js-image-delete-' + $permitIndex;
  removeSettingPermitBtnArray[$permitIndex] = '#removeSettingPermitBtn_' + $permitIndex;
  addSettingPermitBtnArray[$permitIndex] = '#addSettingPermitBtn_' + $permitIndex;
  removePermitPicture(removeBtnArray, $permitIndex);
  removeSettingPermit(removeSettingPermitBtnArray, $permitIndex);
  addSettingPermit(addSettingPermitBtnArray, $permitIndex);
}

function permitSettingAction() {
  let permitIndex = $('.setting_permit').length - 1;
  for (let i = 0; i <= permitIndex; i++) {
    permitSettingElementAction(i);
  }
}

function permitSettingLastAction() {
  let permitIndex = $('.setting_permit').last().attr('id').replace(/[^0-9]/ig, '');
  permitSettingElementAction(permitIndex);
}

function resetElementId($id, $permitNum) {
  let lastSettingPermit = $('.setting_permit').last();
  lastSettingPermit.find($id).attr('id', function () {
    $id = ($id + '_' + $permitNum).replace(/\./ , '');
    return $id;
  });
}

function appendPermit($id, $permitNum) {
  let permitArea = $('#permit_area');
  let fromPermitDiv = $('.setting_permit').prop('outerHTML');

  let permitsName = 'permits' + '[' + $permitNum + ']' + '[name]';
  let permitsRecordNumber = 'permits' + '[' + $permitNum + ']' + '[record_number]';
  let permitsPicture = 'permits' + '[' + $permitNum + ']' + '[picture]';

  permitArea.append(fromPermitDiv);

  let lastSettingPermit = $('.setting_permit').last();
  lastSettingPermit.find('.permit_picture_remove').hide();
  lastSettingPermit.find('.removeSettingPermitBtn').show();
  lastSettingPermit.find('.addSettingPermitBtn').show();

  lastSettingPermit.find('.permit_name').attr('name', permitsName);
  lastSettingPermit.find('.permit_record_number').attr('name', permitsRecordNumber);
  lastSettingPermit.find('.permit_picture').attr('name', permitsPicture);

  lastSettingPermit.attr('id', function () {
    let id = 'settingPermit_' + $permitNum;
    return id;
  });

  resetElementId('.permit_picture', $permitNum);
  resetElementId('.cd-image-upload', $permitNum);
  resetElementId('.removeSettingPermitBtn', $permitNum);
  resetElementId('.addSettingPermitBtn', $permitNum);

  lastSettingPermit.find('.permit_name').attr('value', '');
  lastSettingPermit.find('.permit_record_number').attr('value', '');
  lastSettingPermit.find('.permit_picture').attr('value', '');
  lastSettingPermit.find('img').attr('src', '/assets/img/default/gif.png');
  lastSettingPermit.find('.cd-hide').attr('id', 'permits'+$permitNum);
  lastSettingPermit.find('.cd-hide').attr('data-target', '#permits-qrcode'+$permitNum);
  lastSettingPermit.find('.cd-avatar-square').attr('id', 'permits-qrcode'+$permitNum);
  lastSettingPermit.find('.image-delete-tip').attr('id', 'js-image-delete-'+$permitNum);

  permitSettingLastAction();
}

