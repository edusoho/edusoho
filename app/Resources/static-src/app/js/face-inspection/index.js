import dataURLToBlob from 'dataurl-to-blob';
import {checkBrowserCompatibility} from './util';
import notify from 'common/notify';

class CaptureInit{
  constructor() {
    this.sdk = null;
    this._initSdk();
    if (this.sdk) {
      this.capture();
    }
  }

  _initSdk() {
    var comp = checkBrowserCompatibility();
    if (comp.ok === false) {
      this.showErrorMessage(comp.message);
      return ;
    }

    this.sdk = new window.ExamSupervisorSDK({
      apiServer: '//exam-supervisor-service.edusoho.net',
      token: $('input[name=token]').val(),
    });
  }

  capture() {
    let self = this;

    self.sdk.on('collect-open-error', function (error) {
      console.log('error', error);
      self.showErrorMessage(error.message);
      $('#inspection-collect-btn').attr('disabled', true);
    });

    self.sdk.bootCollectFace('inspection-collect-video', result => this.faceCaptured(result));
  }

  faceCaptured(result) {
    let self = this;
    console.log(result);
    return new Promise(() => {
      console.log('uploading......');
      self.uploadImg(result.face);
    });
  }

  uploadImg(face) {
    let params = new FormData();
    params.append('picture', dataURLToBlob(face));
    $.ajax({
      url: $('.js-upload-url').data('uploadUrl'),
      type: 'POST',
      contentType: false,
      processData: false,
      data: params,
      success: function (response) {
        if (response) {
          notify('success', Translator.trans('恭喜！您已成功完成图像采集!'));
          $('#inspection-collect-finish-btn').show();
          $('.js-upload-url').find('button').hide();
        } else {
          notify('danger', Translator.trans('采集失败！请刷新页面重新采集'));
        }
      }
    });
  }

  showErrorMessage(message) {
    $('#alert-box').html(message);
  }
}

new CaptureInit();