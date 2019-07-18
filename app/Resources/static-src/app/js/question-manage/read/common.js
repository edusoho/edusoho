const registerEvent = function($importBox) {
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
    dragleave: function(e) { // 拖出
      e.preventDefault();
      e.stopPropagation();
    },
    drop: function(e) { // 拖进后释放
      e.preventDefault();
      e.stopPropagation();
    },
    dragenter: function(e) {    //拖进
      e.preventDefault();
      e.stopPropagation();
    },
    dragover: function(e) {    //拖着不动
      e.preventDefault();
      e.stopPropagation();
    }
  });

  uploadFileBoxEl.addEventListener('dragenter', function(e) {
    $uploadFileBox.toggleClass('bg-primary-light');
  }, false);

  uploadFileBoxEl.addEventListener('dragleave', function(e) {
    $uploadFileBox.toggleClass('bg-primary-light');
  }, false);

  uploadFileBoxEl.addEventListener('drop', function(e) {
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
    if(!data) {
      return false;
    }

    let url = $form.attr('action');
    let type = $form.attr('method');

    $uploadBtn.button('loading');
    $.ajax({
      type: type,
      url: url,
      data: data,
      cache: false,
      processData: false,
      contentType: false,
      success: function(res) {
        $uploadBtn.button('reset');
        if (res.success === true) {
          readSuccessCallBack(res);
        } else {
          readErrorCallBack(res);
        }
      },
      error: function(err) {
        $uploadBtn.button('reset');
        $inputFile.val('');
        err = err.responseJSON.error;
        console.log('Read error:', err);
      }
    });
  }

  function readSuccessCallBack(res) {
    window.location.href = res.url;
  }

  // 读取失败回调
  function readErrorCallBack(res) {
    $oldTemplate.addClass('hidden');
    $step1View.addClass('hidden');
    $step3View.html(res).removeClass('hidden');
    $step3Btns.removeClass('hidden');
  }

  $oldTemplate.click(function() {
    let url = $form.attr('action');
    url = url.replace(/read/g, 'plumber');

    $.ajax({
      type: 'get',
      url: url,
    }).done(function(resp) {
      let $modal = $('#modal');
      $modal.html(resp);
    });
  });

  // 重新上传
  $('#re-import-btn').click(function() {
    $oldTemplate.removeClass('hidden');
    $step1View.removeClass('hidden');
    $step2View.addClass('hidden');
    $step3View.addClass('hidden');
    $step2Btns.addClass('hidden');
    $step3Btns.addClass('hidden');
    $inputFile.val('');
  });
};

export {
  registerEvent
}