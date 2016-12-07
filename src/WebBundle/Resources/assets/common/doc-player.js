import swfobject from 'es-swfobject';

(function($) {
  $.fn.WaterMark = function(options) {
    var settings = $.extend({
      'duringTime': 5 * 60 * 1000,
      'interval': 10 * 60 * 1000,
      'isAlwaysShow': true,
      'xPosition': 'center',
      'yPosition': 'top',
      'isUseRandomPos': false,
      'opacity': 0.8,
      'rotate': 45,
      'style': {},
      'contents': ''
    }, options);

    var showTimer;
    var $thiz = $(this);
    var minTopOffset = 40;
    var minLeftOffset = 15;
    var topOffset = minTopOffset;
    var leftOffset = minLeftOffset;
    var $watermarkDiv = null;

    function genereateDiv() {
      var IEversion = getInternetExplorerVersion();
      $watermarkDiv = $('<div id="waterMark" class="watermark"></div>');
      var rotate = 'rotate(' + settings.rotate + 'deg)';
      $watermarkDiv.addClass('active');
      $watermarkDiv.css({
        opacity: settings.opacity,
        '-webkit-transform': rotate,
        '-moz-transform': rotate,
        '-ms-transform': rotate,
        '-o-transform': rotate,
        'transform': rotate,
        'filter': "progid:DXImageTransform.Microsoft.Matrix(M11=0.70710678, M12=0.70710678, M21=-0.70710678, M22=0.70710678, sizingMethod='auto expand')"
      });
      $watermarkDiv.css(settings.style);
      if (IEversion >= 8 && IEversion < 9) {
        $watermarkDiv.css({
          'height': 60,
          'filter': "progid:DXImageTransform.Microsoft.Matrix(M11=0.70710678, M12=0.70710678, M21=-0.70710678, M22=0.70710678, sizingMethod='auto expand')progid:DXImageTransform.Microsoft.Alpha(opacity=" + (parseFloat(settings.opacity) * 100) + ")"
        });
      }
      $watermarkDiv.html(settings.contents);
      return $watermarkDiv;
    }

    function alwaysShow() {
      displayWaterMark();
    }

    function displayWaterMark() {
      getOffset();
      $watermarkDiv.css({
        'top': topOffset,
        'left': leftOffset
      });
      $watermarkDiv.show();
    }

    function timeingShow() {
      displayWaterMark();
      showTimer = setInterval(function() {
        displayWaterMark();
        setTimeout(function() {
          $watermarkDiv.hide();
        }, settings.duringTime);

      }, settings.interval);
    }

    function getOffset() {
      if (settings.isUseRandomPos) {
        setOffsetRandom();
      } else {
        setOffsetByPosition();
      }
    }

    function setOffsetRandom() {
      var maxTopOffset = ($thiz.height() - $watermarkDiv.height() - minTopOffset);
      var maxLeftOffset = ($thiz.width() - $watermarkDiv.width() - minLeftOffset);

      topOffset = Math.random() * maxTopOffset + minTopOffset;
      leftOffset = Math.random() * maxLeftOffset;
    }

    function setOffsetByPosition() {
      if (settings.xPosition == "left") {
        leftOffset = minLeftOffset;
      }
      if (settings.xPosition == "center") {
        leftOffset = ($thiz.width() - $watermarkDiv.width()) / 2;
      }
      if (settings.xPosition == "right") {
        leftOffset = ($thiz.width() - $watermarkDiv.width()) - minLeftOffset;
      }
      if (settings.yPosition == "top") {
        topOffset = minTopOffset;
      }
      if (settings.yPosition == "center") {
        topOffset = ($thiz.height() - $watermarkDiv.height()) / 2 + minTopOffset;
      }
      if (settings.yPosition == "bottom") {
        topOffset = ($thiz.height() - $watermarkDiv.height() - minTopOffset);
      }
    }

    function startShow() {
      if (settings.isAlwaysShow) {
        alwaysShow();
      } else {
        timeingShow();

      }
    }

    function getInternetExplorerVersion() {
      var rv = -1; // Return value assumes failure.
      if (navigator.appName == 'Microsoft Internet Explorer') {
        var ua = navigator.userAgent;
        var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
        if (re.exec(ua) != null)
          rv = parseFloat(RegExp.$1);
      }
      return rv;
    }

    function init() {
      $thiz.append(genereateDiv());

      startShow();
    }

    init();
  };
})($);

class DocPlayer {
  constructor({ element, swfUrl, pdfUrl, watermarkOptions, canCopy }) {
    this.element = $(element);
    this.swfUrl = swfUrl || '';
    this.pdfUrl = pdfUrl || '';
    this.swfPlayerWidth = '100%';
    this.swfPlayerHeight = '100%';
    this.swfPlayerUrl = '';
    this.watermarkOptions = watermarkOptions || '';
    this.canCopy = canCopy || false;
    this.init();

  }

  init() {
    if (this.isSupportHtml5() && !this.isIE9()) {
      this.initPDFJSViewer();
    } else {
      this.initSwfViewer();
    }
  }

  isIE9() {
    return navigator.appVersion.indexOf("MSIE 9.") != -1;
  }

  isSupportHtml5() {
    return $.support.leadingWhitespace;
  }

  initPDFJSViewer() {
    $("html").attr('dir', 'ltr');

    let src = 'http://opencdn.edusoho.net/pdf.js/v3/viewer.html#' + this.pdfUrl;

    if (!this.canCopy) {
      src += '#false';
    }

    let iframe = document.createElement('iframe');
    iframe.style.height = '100%';
    iframe.allowfullscreen = true;
    iframe.style.width = '100%';
    iframe.id = 'doc-pdf-player';
    iframe.src = src;
    this.element.get(0).appendChild(iframe);
    this.addWatermark();
  }

  initSwfViewer() {
    $.html(`<div id="website"><p align="center" class="style1">${Translator.trans('您还没有安装flash播放器 请点击')}<a href="http://www.adobe.com/go/getflashplayer">${Translator.trans('这里')}</a>${Translator.trans('安装')}</p></div>`);

    let flashVars = {
      doc_url: decodeURI(this.swfUrl.value)
    };

    let params = {
      bgcolor: '#efefef',
      allowFullScreen: true,
      wmode: 'window',
      allowNetworking: 'all',
      allowscriptaccess: 'always',
      autoPlay: false
    };

    let attributes = {
      id: 'website'
    };

    swfobject.embedSWF(
      this.swfPlayerUrl,
      'website',
      this.swfPlayerWidth,
      this.swfPlayerHeight,
      "9.0.45",
      null,
      flashVars,
      params,
      attributes
    );

    this.addWatermark();
  }

  addWatermark() {
    this.watermarkOptions && this.element.WaterMark(this.watermarkOptions);
  }
}

export default DocPlayer;
