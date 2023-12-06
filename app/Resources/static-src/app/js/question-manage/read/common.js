import { initTooltips } from 'common/utils';
import 'store';

const registerEvent = function ($importBox) {
  let fileName;
  let $form = $('#import-step-form');

  let $uploadBtn = $('#upload-btn');
  let $inputFile = $('#form_file');
  let $uploadFileBox = $('#upload-file-box');
  let uploadFileBoxEl = document.getElementById('upload-file-box');
  let $oldTemplate = $('#old-template-btn');
  let $step1View = $('.js-step1-view');
  let $step2View = $('.js-step2-view');
  let $step3View = $('.js-step3-view');
  let $step2Btns = $('.js-step2-btn');
  let $step3Btns = $('.js-step3-btn');
  const $jsUploadHotSpot = $('.js-upload-hot-spot')
  const $importRuleBtn = $('.js-import-rule-btn')
  const $importQuestionTips = $('.js-question-import-tips')
  const $uploadProgress = $('.js-upload-progress')
  const $tutorialLink = $('.js-tutorial-link')
  const $docxLink = $('.js-DOCX-link')
  const $xlsxLink = $('.js-XLSX-link')
  const $uploadImg = $('.js-uploda-img')
  const $uploadSuccessfulImg = $('.js-upload-successful-img')
  const $uploadSuccessfulText = $('.js-upload-successful-text')
  const $modalGuideTitle = $('.js-import-modal-guide-title')
  const $modalTitle = $('.js-import-modal-title')
  const $modalGuideInfo = $('.js-guide-import-info')
  const $modalContent = $('.js-content')
  const $modalGuideBtn = $('.js-next-tip-btn')

  $jsUploadHotSpot.on('click', () => {
    $inputFile.click()
  })

  $inputFile.on('change', e => {
    let fileList = e.currentTarget.files;

    if (fileList.length === 0) {
      return false;
    }

    fileName = fileList[0].name;
    let arr = fileName.split('.');
    arr.pop();
    fileName = arr.join('.');
    readFile(new FormData($form[0]));
  });
  // 拖拽上传
  $uploadFileBox.on({
    dragleave: function (e) { // 拖出
      e.preventDefault();
      e.stopPropagation();
    },
    drop: function (e) { // 拖进后释放
      e.preventDefault();
      e.stopPropagation();
    },
    dragenter: function (e) {    //拖进
      e.preventDefault();
      e.stopPropagation();
    },
    dragover: function (e) {    //拖着不动
      e.preventDefault();
      e.stopPropagation();
    }
  });

  uploadFileBoxEl.addEventListener('dragenter', function (e) {
    $uploadFileBox.toggleClass('bg-primary-light');
  }, false);

  uploadFileBoxEl.addEventListener('dragleave', function (e) {
    $uploadFileBox.toggleClass('bg-primary-light');
  }, false);

  uploadFileBoxEl.addEventListener('drop', function (e) {
    $uploadFileBox.removeClass('bg-primary-light');
    const fileList = e.dataTransfer.files;

    if (fileList.length === 0) {
      return false;
    }

    fileName = fileList[0].name;
    let arr = fileName.split('.');
    arr.pop();
    fileName = arr.join('.');
    let data = new FormData();
    data.append('importFile', fileList[0]);

    readFile(data);
  }, false);

  // 读取文件
  function readFile(data) {
    if (!data) {
      return false;
    }

    let url = $form.attr('action');
    let type = $form.attr('method');

    $importQuestionTips.hide()
    $jsUploadHotSpot.off('click');
    $jsUploadHotSpot.addClass('modal-body-lodaing')
    $uploadProgress.removeClass('hidden')
    $tutorialLink.addClass('a-not-click')
    $docxLink.addClass('a-not-click')
    $xlsxLink.addClass('a-not-click')
    $importRuleBtn.attr('disabled', true)
    $importRuleBtn.addClass('import-btn-disabled')

    $.ajax({
      type: type,
      url: url,
      data: data,
      cache: false,
      processData: false,
      contentType: false,
      success: function (res) {
        if (res.success === true) {
          readSuccessCallBack(res);
        } else {
          readErrorCallBack(res);
        }
      },
      error: function (err) {
        $inputFile.val('');
        err = err.responseJSON.error;
        console.log('Read error:', err);
      }
    });
  }

  function readSuccessCallBack(res) {
    let pending = false;
    let interval = setInterval(() => {
      if (pending) {
        return;
      }
      pending = true;
      $.ajax({
        type: 'get',
        url: res.progressUrl,
        success: resp => {
          pending = false;

          if (resp.status === 'failed') {
            clearInterval(interval);
            readErrorCallBack(resp.errorHtml);
            return;
          }
          $uploadProgress.attr('value', resp.progress);

          if (resp.status === 'finished') {
            $uploadImg.addClass('hidden');
            $uploadSuccessfulImg.removeClass('hidden');
            $uploadProgress.addClass('hidden');
            $uploadSuccessfulText.removeClass('hidden');
            clearInterval(interval);

            setTimeout(() => {
              window.location.href = res.url;
            }, 1000);
          }
        },
        error: err => {
          clearInterval(interval);
          $inputFile.val('');
          err = err.responseJSON.error;
          console.log('Read error:', err);
        }
      });
    }, 1000);
  }

  // 读取失败回调
  function readErrorCallBack(res) {
    $uploadBtn.button('reset');
    $oldTemplate.addClass('hidden');
    $step1View.addClass('hidden');
    $step3View.html(res).removeClass('hidden');
    $step3Btns.removeClass('hidden');
    $importRuleBtn.hide();
  }

  $oldTemplate.click(function () {
    if ('1' == $(this).data('need-upgrade')) {
      $('#modal').modal('hide');
      cd.confirm({
        title: Translator.trans('site.tips'),
        content: '<div class="cd-pb24 cd-dark-major">' + Translator.trans('course.question_manage.upgrade_tips') + '</div>',
        okText: Translator.trans('site.close'),
        cancelText: Translator.trans('site.confirm'),
        className: '',
      }).on('ok', () => {
        $('#modal').modal('show');
      }).on('cancel', () => {
        $('#modal').modal('show');
      });
      return;
    }
    $.ajax({
      type: 'get',
      url: $form.data('plumberUrl'),
    }).done(function (resp) {
      let $modal = $('#modal');
      $modal.html(resp);
    });
  });

  // 重新上传
  $('#re-import-btn').click(function () {
    $oldTemplate.removeClass('hidden');
    $step1View.removeClass('hidden');
    $step2View.addClass('hidden');
    $step3View.addClass('hidden');
    $step2Btns.addClass('hidden');
    $step3Btns.addClass('hidden');
    $inputFile.val('');
    $uploadProgress.attr('value', 0);
    $importRuleBtn.show();
    $importQuestionTips.show();
    $uploadProgress.addClass('hidden')
    $jsUploadHotSpot.removeClass('modal-body-lodaing')
    $tutorialLink.removeClass('a-not-click')
    $docxLink.removeClass('a-not-click')
    $xlsxLink.removeClass('a-not-click')
    $importRuleBtn.attr('disabled', false)
    $importRuleBtn.removeClass('import-btn-disabled')
    $uploadImg.removeClass('hidden')
    $jsUploadHotSpot.on('click', () => {
      $inputFile.click()
    })
  });

  $('[data-toggle="popover"]').popover();
  console.log(store);
  if (!store.get('QUESTION_IMPORT_DUIDE')) {
    $modalGuideTitle.removeClass('hidden')
    $modalTitle.addClass('hidden')
    $modalGuideInfo.removeClass('hidden')
    $modalContent.addClass('hidden')
    $modalGuideBtn.removeClass('hidden')
    $importRuleBtn.addClass('hidden')

    store.set('QUESTION_IMPORT_DUIDE', true);
  }

  $modalGuideBtn.on('click', ()=> {
    $modalGuideTitle.addClass('hidden')
    $modalTitle.removeClass('hidden')
    $modalGuideInfo.addClass('hidden')
    $modalContent.removeClass('hidden')
    $modalGuideBtn.addClass('hidden')
    $importRuleBtn.removeClass('hidden')
  })
};
initTooltips()
export {
  registerEvent
}