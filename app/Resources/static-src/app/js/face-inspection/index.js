import notify from 'common/notify';
import dataURLToBlob from 'dataurl-to-blob';
import {checkBrowserCompatibility} from './util';

class CaptureInit{
  constructor() {
    this.$sdk = null;
    this.bestFaceResult = null;
    this._initSdk();
    this.capture();
  }

  _initSdk() {
    var comp = checkBrowserCompatibility();
    if (comp.ok === false) {
      notify('danger', comp.message);
      return ;
    }

    var element = document.getElementById('inspection-collect-video');
    this.sdk = new InspectionSDKV2(element, {
      apiServer: "//inspection-service.edusoho.net",
      token: $("input[name=token]").val(),
    });
  }

  capture() {
    let self = this;
    let collectIndex = 1;
    let bestCollect = null;

    self.sdk.on("collect-ready", function () {
      $("#inspection-collect-btn").removeAttr("disabled");
      $("#inspection-collect-outline").show();
      $("#inspection-collect-starting").hide();

      self.sdk.loadAllModels();
    });

    self.sdk.on("collect-open-error", function (error) {
      console.log("error", error);
      self.showErrorMessage(error.message);
      $("#inspection-collect-btn").attr("disabled", true);
    });

    self.sdk.on("collect-result", function (result) {
      if (bestCollect === null || bestCollect.score < result.score) {
        bestCollect = result;
      }

      setTimeout(() => {
        $("#inspection-collect-actions").find(".btn").attr("disabled", true);
        if (result.error) {
          self.showErrorMessage(result.message);
          $("#inspection-collect-btn").removeAttr("disabled").text("采集").show();
          self.bootCollectFace();
        } else {
          self.setCollectResult(collectIndex, result.face);

          if (collectIndex < 3) {
            self.showErrorMessage(`已成功采集 ${collectIndex} 张，请继续采集下一张。`);
            $("#inspection-collect-btn").removeAttr("disabled").text("采集").show();
            self.bootCollectFace();
          } else {
            self.showErrorMessage('采集完成，正在上传头像，请稍等...');
            setTimeout(() => {
                self.uploadImg(bestCollect);
            }, 2000);
          }
          collectIndex ++;
        }
      }, 1000); // 采集第2、3张时速度会非常快，界面会一闪而过，这里减慢1秒，消除影响。
    });

    $("#inspection-collect-btn").on("click", async function () {
      self.showErrorMessage("正在采集，请稍等...");
      $("#inspection-collect-btn").attr("disabled", true).text("采集中...");

      let faceImgEl = document.getElementById("inspection-collect-image");
      self.sdk.collectFace(faceImgEl, collectIndex < 3 ? false : true);
    });

    self.bootCollectFace();
  }

  uploadImg(bestCollect) {
    let self = this;
    let params = new FormData();
    params.append('picture', dataURLToBlob(bestCollect.face));
    $.ajax({
      url: $(".js-upload-url").data('uploadUrl'),
      type: 'POST',
      contentType: false,
      processData: false,
      data: params,
      success: function (response) {
        if (response) {
          $('#inspection-collect-btn').addClass('hidden');
          self.showErrorMessage(Translator.trans('恭喜！您已成功完成图像采集!'));
          self.bestFaceResult = bestCollect;
          $("#inspection-collect-finish-btn").removeAttr('disabled').show();
        } else {
          self.showErrorMessage(Translator.trans('采集失败！请刷新页面重新采集'));
        }
      }
    });
  }

  bootCollectFace() {
    $("#inspection-collect-starting").text("正在启动采集引擎，请稍等...");
    $("#inspection-collect-actions").find(".btn").hide();
    $("#inspection-collect-btn").attr("disabled", true).show();
    $("#inspection-collect-image").hide();

    this.sdk.bootCollectFace();
  }

  setCollectResult(index, imgData) {
    let resultImg = document.createElement("img");
    resultImg.src = imgData;
    $("#inspection-collect-result-" + index).empty().append(resultImg);
  }

  showErrorMessage(message) {
    $('#alert-box').html(message);
  }
}

new CaptureInit();