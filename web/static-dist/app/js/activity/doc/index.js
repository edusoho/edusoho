webpackJsonp(["app/js/activity/doc/index"],{

/***/ "e3591734a7ec9a6a6c56":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _esSwfobject = __webpack_require__("c04c1b91e3806f24595a");
	
	var _esSwfobject2 = _interopRequireDefault(_esSwfobject);
	
	__webpack_require__("9a5c59a43068776403d1");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var DocPlayer = function () {
	  function DocPlayer(_ref) {
	    var element = _ref.element,
	        swfUrl = _ref.swfUrl,
	        pdfUrl = _ref.pdfUrl,
	        watermarkOptions = _ref.watermarkOptions,
	        canCopy = _ref.canCopy;
	
	    _classCallCheck(this, DocPlayer);
	
	    this.element = $(element);
	    this.swfUrl = swfUrl || '';
	    this.pdfUrl = pdfUrl || '';
	    this.swfPlayerWidth = '100%';
	    this.swfPlayerHeight = '100%';
	    this.swfPlayerUrl = '';
	    this.watermarkOptions = watermarkOptions || '';
	    this.canCopy = canCopy || false;
	    this.init();
	
	    console.log(watermarkOptions);
	  }
	
	  _createClass(DocPlayer, [{
	    key: 'init',
	    value: function init() {
	      if (this.isSupportHtml5() && !this.isIE9()) {
	        this.initPDFJSViewer();
	      } else {
	        this.initSwfViewer();
	      }
	      this.onFullScreen();
	    }
	  }, {
	    key: 'onFullScreen',
	    value: function onFullScreen(docPlayer) {
	      alert(1);
	      window.onmessage = function (e) {
	        alert(2);
	        console.log(e.data);
	        if (e == null || e == undefined) {
	          return;
	        }
	        var isPageFullScreen = e.data;
	        if (typeof isPageFullScreen != "boolean") {
	          return;
	        }
	        var docContent = $('#task-content-iframe', window.parent.document);
	        if (isPageFullScreen) {
	          docContent.removeClass('screen-full');
	          docContent.width('100%');
	        } else {
	          docContent.addClass('screen-full');
	          docContent.width(window.document.body.offsetWidth + "px");
	        }
	      };
	    }
	  }, {
	    key: 'isIE9',
	    value: function isIE9() {
	      return navigator.appVersion.indexOf("MSIE 9.") != -1;
	    }
	  }, {
	    key: 'isSupportHtml5',
	    value: function isSupportHtml5() {
	      return $.support.leadingWhitespace;
	    }
	  }, {
	    key: 'initPDFJSViewer',
	    value: function initPDFJSViewer() {
	      $("html").attr('dir', 'ltr');
	
	      var src = '//service-cdn.qiqiuyun.net/js-sdk/document-player/v7/viewer.html#' + this.pdfUrl;
	
	      if (!this.canCopy) {
	        src += '#false';
	      }
	
	      var $iframe = '<iframe id="doc-pdf-player" class="task-content-iframe" \n     src="' + src + '" style="width:100%;height:100%;border:0px" \n     allowfullscreen="" webkitallowfullscreen="">\n      </iframe>';
	      this.element.append($iframe);
	
	      this.addWatermark();
	    }
	  }, {
	    key: 'initSwfViewer',
	    value: function initSwfViewer() {
	      $.html('<div id="website"><p align="center" class="style1">' + Translator.trans('site.flash_not_install_hint') + '</p></div>');
	
	      var flashVars = {
	        doc_url: decodeURI(this.swfUrl.value)
	      };
	
	      var params = {
	        bgcolor: '#efefef',
	        allowFullScreen: true,
	        wmode: 'window',
	        allowNetworking: 'all',
	        allowscriptaccess: 'always',
	        autoPlay: false
	      };
	
	      var attributes = {
	        id: 'website'
	      };
	
	      _esSwfobject2["default"].embedSWF(this.swfPlayerUrl, 'website', this.swfPlayerWidth, this.swfPlayerHeight, "9.0.45", null, flashVars, params, attributes);
	
	      this.addWatermark();
	    }
	  }, {
	    key: 'addWatermark',
	    value: function addWatermark() {
	      this.watermarkOptions && this.element.WaterMark(this.watermarkOptions);
	    }
	  }]);
	
	  return DocPlayer;
	}();
	
	exports["default"] = DocPlayer;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _docPlayer = __webpack_require__("e3591734a7ec9a6a6c56");
	
	var _docPlayer2 = _interopRequireDefault(_docPlayer);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $element = $('#document-content');
	var watermarkUrl = $element.data('watermark-url');
	
	if (watermarkUrl) {
	  $.get(watermarkUrl, function (watermark) {
	    console.log(watermark);
	    initDocPlayer(watermark);
	  });
	} else {
	  initDocPlayer('');
	}
	
	function initDocPlayer(contents) {
	  var doc = new _docPlayer2["default"]({
	    element: $element,
	    swfUrl: $element.data('swf'),
	    pdfUrl: $element.data('pdf'),
	    watermarkOptions: {
	      contents: contents,
	      xPosition: 'center',
	      yPosition: 'center',
	      rotate: 45
	    },
	    canCopy: $element.data('disableCopy')
	  });
	}

/***/ })

});
//# sourceMappingURL=index.js.map