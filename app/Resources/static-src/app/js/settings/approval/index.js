import notify from 'common/notify';

$('#approval-form').validate({
  rules: {
    idcard: 'required idcardNumber',
    truename: {
      required: true,
      chinese: true,
      maxlength: 25,
      minlength: 2
    },
    faceImg: 'required isImage limitSize',
    backImg: 'required isImage limitSize'
  },
  messages: {
    faceImg: {
      required: Translator.trans('user.fields.idcard_front_placeholder')
    },
    backImg: {
      required: Translator.trans('user.fields.idcard_back_placeholder')
    }
  }
});

cd.upload({
  el: '.js-upload-input',
  type: 'normal',
  success(event, $target) {
    $target.addClass('done');
    if (!$target.find('.mask').length) {
      let html = '<div class="mask"></div>';
      $target.append(html);
    }
  },
  error(code) {
    if (code === 'FILE_SIZE_LIMIT') {
      notify('danger', Translator.trans('uploader.size_2m_limit_hint'));
    } else if (code === 'FLIE_TYPE_LIMIT') {
      notify('danger', Translator.trans('uploader.type_denied_limit_hint'));
    }
  }
});
