webpackJsonp(["app/js/activity/video/preview/index"],{

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _preview = __webpack_require__("1dc4cd4f0f1db3e6ec67");
	
	var _preview2 = _interopRequireDefault(_preview);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var videoplay = new _preview2["default"]('#video-content');
	videoplay.play();

/***/ }),

/***/ "1dc4cd4f0f1db3e6ec67":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
		value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _esSwfobject = __webpack_require__("c04c1b91e3806f24595a");
	
	var _esSwfobject2 = _interopRequireDefault(_esSwfobject);
	
	var _messenger = __webpack_require__("06597b47670159844043");
	
	var _messenger2 = _interopRequireDefault(_messenger);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var VideoPlay = function () {
		function VideoPlay(container) {
			_classCallCheck(this, VideoPlay);
	
			this.player = {};
			this.container = container;
		}
	
		_createClass(VideoPlay, [{
			key: 'play',
			value: function play() {
				if ($('#swf-player').length) {
					this._playerSwf();
				} else {
					this._playVideo();
				}
			}
		}, {
			key: '_playerSwf',
			value: function _playerSwf() {
				var swf_dom = 'swf-player';
				_esSwfobject2["default"].embedSWF($('#' + swf_dom).data('url'), swf_dom, '100%', '100%', "9.0.0", null, null, {
					wmode: 'opaque',
					allowFullScreen: 'true'
				});
			}
		}, {
			key: '_playVideo',
			value: function _playVideo() {
				var messenger = new _messenger2["default"]({
					name: 'partner',
					project: 'PlayerProject',
					children: [],
					type: 'parent'
				});
	
				messenger.on("ended", function () {
					$('#task-preview-player').html($('.js-time-limit-dev').html());
				});
			}
		}]);
	
		return VideoPlay;
	}();
	
	exports["default"] = VideoPlay;

/***/ })

});
//# sourceMappingURL=index.js.map