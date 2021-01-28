import notify from 'common/notify';
import dataURLToBlob from 'dataurl-to-blob';

class CaptureInit{
  constructor() {
    let self = this;
    if(this._checkIEDevice()){
      $('#capture-btn').addClass('hidden');
      notify('danger', '云监考暂不支持该浏览器，请选择其他浏览器');
    }else{
      self._initEvent();
    }
  }

  //判断IE浏览器
  _checkIEDevice(){
    let userAgent = navigator.userAgent;
    let isIE = userAgent.indexOf('compatible') > -1 && userAgent.indexOf("MSIE") > -1; //判断是否IE<11浏览器
    let isEdge = userAgent.indexOf('Edge') > -1 && !isIE; //判断是否IE的Edge浏览器
    let isIE11 = userAgent.indexOf('Trident') > -1 && userAgent.indexOf("rv:11.0") > -1;
    return  isIE || isEdge || isIE11;
  }

  _initEvent(){
    let $captureBtn = $('#capture-btn');
    let self = this;
    $captureBtn.addClass('disabled');
    $captureBtn.html('人像采集正在初始化...');
    //自动检测是否初始化成功
    setTimeout(function () {
      if($('.js-loading-container').length >0){
        console.log('人像采集初始化失败');
        notify('danger', Translator.trans('人像采集初始化失败！'));
        $('.js-btn-group').html(`<button id="reset-btn" class="btn btn-primary">重试</button>`);
      }
    }, 15000);
    $(document).ready(() => {
      console.log('云监考初始化成功.');
      $captureBtn.removeClass('disabled');
      $('.js-loading-container').remove();
      $captureBtn.html('开始采集');
      self._initCapture()
    });

    $('.js-btn-group').on('click', '#reset-btn', function (event) {
      window.location.reload();
    });
  }

  _initCapture() {
    let time = 0;

    window.esCaptureSdk.on('capture_real_face.started', function () {
      notify('success', Translator.trans('人像采集已启动,请面对摄像头。'));
      console.log('人像采集已启动,请面对摄像头。');
    });

    //采集成功
    window.esCaptureSdk.on('capture_real_face.captured', function (data) {
      console.log('人像采集成功。');
      time++;
      let img = new Image(480);
      img.src = data.capture;
      $('#real-face-imgs').html(img);

      if (time === 3) {
        let img = new Image(480);
        img.src = data.capture;
        $('#real-face-capture').append(img);
        let params = new FormData();
        params.append('picture',dataURLToBlob(data.capture));

        $.ajax({
          url: $('#capture-btn').data('url'),
          type: 'POST',
          contentType: false,
          processData: false,
          data: params,
          success: function (response) {
            if (response) {
              $('#capture-btn').addClass('hidden');
              $('#capture-finish-btn').removeClass('hidden');
              notify('success', Translator.trans('恭喜！您已成功完成图像采集!'));
            } else {
              notify('danger', Translator.trans('采集失败！请刷新页面重新采集'));
            }
          }
        });
      }
    });

    window.esCaptureSdk.on('capture_real_face.finished', function () {
      console.log("人像采集结束");
    });

    $('#capture-btn').on('click', function () {
      console.log('人像采集启动中, 请稍等...');
      $('.js-tip-title').remove();
      $('input[name=token]').attr('disabled', true);
      $('#capture-btn').attr('disabled', true);
      notify('success', Translator.trans('人像采集启动中, 请稍等...'));
      window.esCaptureSdk.setToken($('input[name=token]').val());
      window.esCaptureSdk.captureRealFaces();
    });
  }
}

new CaptureInit();