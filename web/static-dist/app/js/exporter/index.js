webpackJsonp(["app/js/exporter/index"],[
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Export = function () {
	    function Export($exprtBtns) {
	        _classCallCheck(this, Export);
	
	        this.$exportBtns = $exprtBtns;
	        this.$modal = $('#modal');
	        this.exportDataEvent();
	    }
	
	    _createClass(Export, [{
	        key: 'exportDataEvent',
	        value: function exportDataEvent() {
	            var self = this;
	            self.$exportBtns.on('click', function () {
	                self.$exportBtn = $(this);
	                var $form = $(self.$exportBtn.data('targetForm'));
	                var formData = $form.length > 0 ? $form.serialize() : '';
	                var preUrl = self.$exportBtn.data('preUrl') + '?' + formData;
	                var tryUrl = self.$exportBtn.data('tryUrl') + '?' + formData;
	                var can = self.tryExport(tryUrl);
	                if (!can) {
	                    return false;
	                }
	
	                self.$exportBtn.button('loading');
	                var urls = { 'preUrl': preUrl, 'url': self.$exportBtn.data('url') };
	                self.showProgress();
	
	                self.exportData(0, '', urls);
	            });
	        }
	    }, {
	        key: 'tryExport',
	        value: function tryExport(tryUrl) {
	            var can = true;
	            var self = this;
	            $.ajax({
	                type: "get",
	                url: tryUrl,
	                async: false,
	                success: function success(response) {
	                    if (!response.success) {
	                        self.notifyError(Translator.trans(response.message, response.parameters));
	                        can = false;
	                    }
	                }
	            });
	
	            return can;
	        }
	    }, {
	        key: 'finish',
	        value: function finish() {
	            var self = this;
	            self.$modal.find('#progress-bar').width('100%').parent().removeClass('active');
	            var $title = self.$modal.find('.modal-title');
	            setTimeout(function () {
	                (0, _notify2["default"])('success', $title.data('success'));
	                self.$modal.modal('hide');
	            }, 500);
	        }
	    }, {
	        key: 'showProgress',
	        value: function showProgress() {
	            var progressHtml = $('#export-modal').html();
	            this.$modal.html(progressHtml);
	            this.$modal.modal({ backdrop: 'static', keyboard: false });
	        }
	    }, {
	        key: 'download',
	        value: function download(urls, fileName) {
	            if (urls.url && fileName) {
	                window.location.href = urls.url + '?fileName=' + fileName;
	                return true;
	            }
	
	            return false;
	        }
	    }, {
	        key: 'notifyError',
	        value: function notifyError(message) {
	            this.$modal.modal('hide');
	            (0, _notify2["default"])('warning', message);
	        }
	    }, {
	        key: 'exportData',
	        value: function exportData(start, fileName, urls) {
	            var self = this;
	            var data = {
	                'start': start,
	                'fileName': fileName
	            };
	
	            $.get(urls.preUrl, data, function (response) {
	                if (!response.success) {
	                    console.log(response);
	
	                    (0, _notify2["default"])('danger', Translator.trans(response.message));
	                    return;
	                }
	
	                if (response.status === 'continue') {
	                    var process = response.start * 100 / response.count + '%';
	                    self.$modal.find('#progress-bar').width(process);
	                    self.exportData(response.start, response.fileName, urls);
	                } else {
	                    self.$exportBtn.button('reset');
	                    self.download(urls, response.fileName) ? self.finish() : self.notifyError('unexpected error, try again');;
	                }
	            }).error(function (e) {
	                (0, _notify2["default"])('danger', e.responseJSON.error.message);
	            });
	        }
	    }]);
	
	    return Export;
	}();
	
	;
	
	new Export($('.js-export-btn'));

/***/ })
]);
//# sourceMappingURL=index.js.map