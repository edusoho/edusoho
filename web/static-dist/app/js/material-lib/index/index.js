webpackJsonp(["app/js/material-lib/index/index"],{

/***/ "d919d1055afe1010ecbe":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }
	
	var Select = function Select(element, type, options) {
	  var config = {};
	  /**
	   * type 类型
	   * element 对象
	   * opyions 配置项
	   */
	  if (type === 'remote') {
	    var _config;
	
	    config = (_config = {
	      ajax: {
	        url: $(element).data('url'),
	        dataType: 'json',
	        quietMillis: 100,
	        data: function data(term, page) {
	          return {
	            q: term,
	            page_limit: 10
	          };
	        },
	        results: function results(data) {
	          var results = [];
	          $.each(data, function (index, item) {
	            results.push({
	              id: item.name,
	              name: item.name
	            });
	          });
	          return {
	            results: results
	          };
	        }
	      },
	      initSelection: function initSelection(element, callback) {
	        var data = [];
	        $(element.val().split(",")).each(function () {
	          data.push({
	            id: this,
	            name: this
	          });
	        });
	        callback(data);
	      },
	      formatSelection: function formatSelection(item) {
	        return item.name;
	      },
	      formatResult: function formatResult(item) {
	        return item.name;
	      },
	
	      width: 400,
	      multiple: true,
	      placeholder: Translator.trans('validate.tag_required_hint')
	    }, _defineProperty(_config, 'multiple', true), _defineProperty(_config, 'createSearchChoice', function createSearchChoice() {
	      return null;
	    }), _defineProperty(_config, 'maximumSelectionSize', 20), _config);
	  }
	
	  $(element).select2(Object.assign(config, options));
	};
	
	exports["default"] = Select;

/***/ }),

/***/ "8e57c009b06dc22fb38f":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _messenger = __webpack_require__("06597b47670159844043");
	
	var _messenger2 = _interopRequireDefault(_messenger);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Cover = function () {
	  function Cover(options) {
	    _classCallCheck(this, Cover);
	
	    this.callback = options.callback;
	    this.element = options.element;
	    this.init();
	  }
	
	  _createClass(Cover, [{
	    key: 'init',
	    value: function init() {
	      this.initEvent();
	      this._initPlayer();
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      $('.js-img-set').on('click', function (event) {
	        _this.onClickChangePic(event);
	      });
	      $('.js-reset-btn').on('click', function (event) {
	        _this.onClickReset(event);
	      });
	      $('.js-set-default').on('click', function (event) {
	        _this.onClickDefault(event);
	      });
	      $('.js-set-select').on('click', function (event) {
	        _this.onClickSelect(event);
	      });
	      $('.js-screenshot-btn').on('click', function (event) {
	        _this.onClickScreenshot(event);
	      });
	      $('#cover-form').on('submit', function (event) {
	        _this.onSubmitCoverForm(event);
	      });
	    }
	  }, {
	    key: 'onClickChangePic',
	    value: function onClickChangePic(event) {
	      var $target = $(event.currentTarget);
	      var $coverTab = $target.closest('#cover-tab');
	      $coverTab.find('.js-cover-img').attr('src', $target.attr('src'));
	      $coverTab.find('#thumbNo').val($target.data('no'));
	    }
	  }, {
	    key: 'onClickReset',
	    value: function onClickReset() {
	      $('#thumbNo').val('');
	      $('.js-cover-img').attr('src', $('#orignalThumb').val());
	    }
	  }, {
	    key: 'onClickDefault',
	    value: function onClickDefault(event) {
	      this._changePane($(event.currentTarget));
	    }
	  }, {
	    key: 'onClickSelect',
	    value: function onClickSelect(event) {
	      this._changePane($(event.currentTarget));
	    }
	  }, {
	    key: 'onClickScreenshot',
	    value: function onClickScreenshot() {
	      var $target = $(event.currentTarget);
	      var self = this;
	      $target.button('loading');
	      $.ajax({
	        type: 'get',
	        url: $target.data('url'),
	        data: {
	          'second': self.second
	        }
	      }).done(function (resp) {
	        if (resp.status == 'success') {
	          self._successGeneratePic($target, resp);
	        } else if (resp.status == 'waiting') {
	          //轮询
	          self.intervalId = setInterval(function () {
	            $.get($target.data('url'), {
	              'second': self.second
	            }, function (resp) {
	              if (resp.status == 'success') {
	                self._successGeneratePic($target, resp);
	                clearInterval(self.intervalId);
	              }
	            });
	          }, 3000);
	        } else {
	          $target.button('reset');
	          (0, _notify2["default"])('danger', Translator.trans('meterial_lib.generate_screenshots_error_hint'));
	        }
	      }).fail(function () {
	        $target.button('reset');
	        (0, _notify2["default"])('danger', Translator.trans('meterial_lib.generate_screenshots_error_hint'));
	      });
	    }
	  }, {
	    key: 'onSubmitCoverForm',
	    value: function onSubmitCoverForm(event) {
	      var $target = $(event.currentTarget);
	      $target.find('#save-btn').button('loading');
	      if ($target.find('#thumbNo').val()) {
	        $.ajax({
	          type: 'POST',
	          url: $target.attr('action'),
	          data: $target.serialize()
	        }).done(function () {
	          (0, _notify2["default"])('success', Translator.trans('site.save_success_hint'));
	        }).fail(function () {
	          (0, _notify2["default"])('danger', Translator.trans('site.save_error_hint'));
	        }).always(function () {
	          $target.find('#save-btn').button('reset');
	        });
	      } else {
	        (0, _notify2["default"])('success', Translator.trans('site.save_success_hint'));
	        $target.find('#save-btn').button('reset');
	      }
	      event.preventDefault();
	    }
	  }, {
	    key: 'destroy',
	    value: function destroy() {
	      clearInterval(this.intervalId);
	    }
	  }, {
	    key: '_initPlayer',
	    value: function _initPlayer() {
	      var self = this;
	      if ($('#viewerIframe').length > 0) {
	        $('#viewerIframe');
	        var messenger = new _messenger2["default"]({
	          name: 'parent',
	          project: 'PlayerProject',
	          children: [document.getElementById('viewerIframe')],
	          type: 'parent'
	        });
	
	        // messenger.on("ready", function() {
	        //   self.player = window.frames["viewerIframe"].contentWindow.BalloonPlayer;
	        // });
	
	        messenger.on('timechange', function (data) {
	          self.second = Math.floor(data.currentTime);
	        });
	      }
	    }
	  }, {
	    key: '_successGeneratePic',
	    value: function _successGeneratePic($btn, resp) {
	      $btn.button('reset');
	      (0, _notify2["default"])('success', Translator.trans('meterial_lib.generate_screenshots_success_hint'));
	      var $coverTab = $btn.closest('#cover-tab');
	      $coverTab.find('.js-cover-img').attr('src', resp.url);
	      $coverTab.find('#thumbNo').val(resp.no);
	    }
	  }, {
	    key: '_changePane',
	    value: function _changePane($target) {
	      $target.closest('.nav').find('li.active').removeClass('active');
	      $target.addClass('active');
	
	      var $tabcontent = $('.tab-content-img');
	      $tabcontent.find('.tab-pane-img.active').removeClass('active');
	      $tabcontent.find($target.data('target')).addClass('active');
	    }
	  }]);
	
	  return Cover;
	}();
	
	exports["default"] = Cover;

/***/ }),

/***/ "e51bac35e7290fbcdec6":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _info = __webpack_require__("a0438f6657de6354719c");
	
	var _info2 = _interopRequireDefault(_info);
	
	var _cover = __webpack_require__("8e57c009b06dc22fb38f");
	
	var _cover2 = _interopRequireDefault(_cover);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var DetailWidget = function () {
	  function DetailWidget(options) {
	    _classCallCheck(this, DetailWidget);
	
	    this.callback = options.callback;
	    this.element = options.element;
	    this.init();
	  }
	
	  _createClass(DetailWidget, [{
	    key: 'init',
	    value: function init() {
	      this.initEvent();
	      if ($('#cover-tab').length > 0) {
	        this.cover = new _cover2["default"]({
	          element: $('#cover-tab')
	        });
	      };
	
	      this.info = new _info2["default"]({
	        element: $('#info-tab')
	      });
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      $('.js-back').on('click', function (event) {
	        _this.onClickBack(event);
	      });
	      $('.js-cover').on('click', function (event) {
	        _this.onClickCover(event);
	      });
	      $('.js-info').on('click', function (event) {
	        _this.onClickInfo(event);
	      });
	    }
	  }, {
	    key: 'onClickInfo',
	    value: function onClickInfo(event) {
	      var $target = $(event.currentTarget);
	      this._changePane($target);
	    }
	  }, {
	    key: 'onClickCover',
	    value: function onClickCover(event) {
	      var $target = $(event.currentTarget);
	      this._changePane($target);
	    }
	  }, {
	    key: 'onClickBack',
	    value: function onClickBack() {
	      this.back();
	    }
	  }, {
	    key: 'back',
	    value: function back() {
	      this.callback();
	      this.element.remove();
	      // this.info.destroy();
	      // this.cover && this.cover.destroy();
	      // this.destroy();
	      $('.panel-heading').html(Translator.trans('material_lib.content_title'));
	    }
	  }, {
	    key: '_changePane',
	    value: function _changePane($target) {
	      //change li
	      $target.closest('.nav').find('li.active').removeClass('active');
	      $target.addClass('active');
	
	      //change content
	      var $tabcontent = $target.closest('.content').find('.tab-content');
	      $tabcontent.find('.tab-pane.active').removeClass('active');
	      $tabcontent.find($target.data('target')).addClass('active');
	    }
	  }]);
	
	  return DetailWidget;
	}();
	
	exports["default"] = DetailWidget;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _detail = __webpack_require__("e51bac35e7290fbcdec6");
	
	var _detail2 = _interopRequireDefault(_detail);
	
	var _batchSelect = __webpack_require__("de585ca0d3c2d0205c51");
	
	var _batchSelect2 = _interopRequireDefault(_batchSelect);
	
	var _inputSelect = __webpack_require__("d919d1055afe1010ecbe");
	
	var _inputSelect2 = _interopRequireDefault(_inputSelect);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var MaterialWidget = function () {
	  function MaterialWidget(element) {
	    _classCallCheck(this, MaterialWidget);
	
	    this.model = 'normal';
	    this.renderUrl = $('#material-item-list').data('url');
	    this.attribute = 'mine';
	    this.element = $('#material-search-form');
	    this.init();
	  }
	
	  _createClass(MaterialWidget, [{
	    key: 'init',
	    value: function init() {
	      this.initEvent();
	      this._initHeader();
	      this._initSelect2();
	      this.initTagForm();
	      this.renderTable();
	
	      new _batchSelect2["default"](this.element);
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.element.on('click', '.js-search-btn', function (event) {
	        _this.submitForm(event);
	      });
	
	      this.element.on('click', '.js-source-btn', function (event) {
	        _this.onClickSourseBtn(event);
	      });
	
	      this.element.on('click', '.js-type-btn', function (event) {
	        _this.onClickTabs(event);
	      });
	
	      this.element.on('click', '.js-material-tag .label', function (event) {
	        _this.onClickTag(event);
	      });
	
	      this.element.on('click', '.js-delete-btn', function (event) {
	        _this.onClickDeleteBtn(event);
	      });
	
	      this.element.on('click', '.js-download-btn', function (event) {
	        _this.onClickDownloadBtn(event);
	      });
	
	      this.element.on('click', '.js-collect-btn', function (event) {
	        _this.onClickCollectBtn(event);
	      });
	
	      this.element.on('click', '.js-manage-batch-btn', function (event) {
	        _this.onClickManageBtn(event);
	      });
	
	      this.element.on('click', '.js-batch-delete-btn', function (event) {
	        _this.onClickDeleteBatchBtn(event);
	      });
	
	      this.element.on('click', '.js-batch-share-btn', function (event) {
	        _this.onClickShareBatchBtn(event);
	      });
	
	      this.element.on('click', '.js-batch-tag-btn', function (event) {
	        _this.onClickTagBatchBtn(event);
	      });
	
	      this.element.on('click', '.js-detail-btn', function (event) {
	        _this.onClickDetailBtn(event);
	      });
	
	      this.element.on('click', '.js-reconvert-btn', function (event) {
	        _this.onClickReconvertBtn(event);
	      });
	
	      this.element.on('change', '.js-process-status-select', function (event) {
	        _this.onClickProcessStatusBtn(event);
	      });
	
	      this.element.on('change', '.js-use-status-select', function (event) {
	        _this.onClickUseStatusBtn(event);
	      });
	
	      this.element.on('click', '.js-share-btn', function (event) {
	        _this.onClickShareBtn(event);
	      });
	
	      this.element.on('click', '.js-unshare-btn', function (event) {
	        _this.onClickUnshareBtn(event);
	      });
	
	      this.element.on('click', '.pagination li', function (event) {
	        _this.onClickPagination(event);
	      });
	    }
	  }, {
	    key: 'submitForm',
	    value: function submitForm(event) {
	      this.renderTable();
	      event.preventDefault();
	    }
	  }, {
	    key: 'onClickTabs',
	    value: function onClickTabs(event) {
	      var $target = $(event.currentTarget);
	      $target.closest('.js-material-tabs').find('.active').removeClass('active');
	      $target.addClass('active');
	      $target.closest('.js-material-tabs').find('[name=type]').val($target.data('value'));
	      this.renderTable();
	      event.preventDefault();
	    }
	    //标签选择
	
	  }, {
	    key: 'onClickTag',
	    value: function onClickTag(event) {
	      var $target = $(event.currentTarget);
	      var $container = $target.closest('.js-material-tag');
	      var $prev = $container.find('.label-primary');
	      if ($target.html() == $prev.html()) {
	        $target.removeClass('label-primary').addClass('label-default');
	        $container.find('[name=tagId]').val('');
	      } else {
	        $prev.removeClass('label-primary').addClass('label-default');
	        $target.addClass('label-primary').removeClass('label-default');
	        $container.find('[name=tagId]').val($target.data('id'));
	      }
	      this.renderTable();
	    }
	    // 下拉菜单编辑
	
	  }, {
	    key: 'onClickDetailBtn',
	    value: function onClickDetailBtn(event) {
	      if (!this.DetailBtnActive) {
	        var self = this;
	        var $target = $(event.currentTarget);
	        this.DetailBtnActive = true;
	        $.ajax({
	          type: 'GET',
	          url: $target.data('url')
	        }).done(function (resp) {
	          self.element.hide();
	          self.element.prev().hide();
	          self.element.parent().prev().html(Translator.trans('material_lib.detail.content_title'));
	          self.element.parent().append(resp);
	
	          if ($(".nav.nav-tabs").length > 0 && !navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i)) {
	            $(".nav.nav-tabs").lavaLamp();
	          }
	
	          (0, _inputSelect2["default"])('#tags', 'remote');
	
	          new _detail2["default"]({
	            element: $('#material-detail'),
	            callback: function callback() {
	              var $form = $('#material-search-form');
	              $form.show();
	              $form.prev().show();
	              self.renderTable();
	            }
	          });
	        }).fail(function () {
	          (0, _notify2["default"])('danger', Translator.trans('material_lib.have_no_permission_hint'));
	        }).always(function () {
	          self.DetailBtnActive = false;
	        });
	      }
	    }
	    // 下拉菜单删除
	
	  }, {
	    key: 'onClickDeleteBtn',
	    value: function onClickDeleteBtn(event) {
	      var self = this;
	      var $target = $(event.currentTarget);
	      var ids = [];
	      ids.push($target.data('id'));
	      $('#modal').html('');
	      $('#modal').load($target.data('url'), { ids: ids });
	      $('#modal').modal('show');
	    }
	    // 下拉菜单下载
	
	  }, {
	    key: 'onClickDownloadBtn',
	    value: function onClickDownloadBtn(event) {
	      var $target = $(event.currentTarget);
	      window.open($target.data('url'));
	    }
	  }, {
	    key: 'onClickSourseBtn',
	    value: function onClickSourseBtn(event) {
	      var $target = $(event.currentTarget);
	      $target.closest('ul').find('li.active').removeClass('active');
	      $target.parent().addClass('active');
	      $target.closest('ul').siblings('input[name="sourceFrom"]').val($target.parent().data('value'));
	
	      if ($target.closest('ul').siblings('input[name="sourceFrom"]').val() == 'my') {
	        this.attribute = 'mine';
	        $('#myShare').removeClass('hide');
	        $('#shareMaterials').removeClass('hide');
	        $('.js-manage-batch-btn').removeClass('hide');
	        $('.js-upload-file-btn').removeClass('hide');
	        var mode = this.model;
	        if (mode == "edit") {
	          $('#material-lib-batch-btn-bar').show();
	        }
	      } else {
	        this.attribute = 'others';
	        $('#myShare').addClass('hide');
	        $('#shareMaterials').addClass('hide');
	        $('.js-manage-batch-btn').addClass('hide');
	        $('.js-upload-file-btn').addClass('hide');
	        $('#material-lib-batch-btn-bar').hide();
	      }
	      this.renderTable();
	    }
	  }, {
	    key: 'onClickCollectBtn',
	    value: function onClickCollectBtn(event) {
	      var self = this;
	      var $target = $(event.currentTarget);
	      $.get($target.data('url'), function (data) {
	        if (data) {
	          $target.addClass("material-collection");
	          (0, _notify2["default"])('success', Translator.trans('site.collect_cuccess_hint'));
	        } else {
	          $target.removeClass("material-collection");
	          (0, _notify2["default"])('success', Translator.trans('site.uncollect_cuccess_hint'));
	        }
	      });
	    }
	  }, {
	    key: 'onClickManageBtn',
	    value: function onClickManageBtn(event) {
	      var self = this;
	      var mode = self.model;
	
	      if (mode == "normal") {
	        this.model = 'edit';
	        var $target = $(event.currentTarget);
	        $('#material-lib-batch-btn-bar').show();
	        $('#material-lib-items-panel').find('[data-role=batch-item]').show();
	        $('.materials-ul').addClass('batch-hidden');
	        $target.html(Translator.trans('meterial_lib.complete_manage'));
	      } else {
	        this.model = 'normal';
	        var _self = this;
	        var _$target = $(event.currentTarget);
	        $('#material-lib-batch-btn-bar').hide();
	        $('#material-lib-items-panel').find('[data-role=batch-item]').hide();
	        $('.materials-ul').removeClass('batch-hidden');
	        _$target.html(Translator.trans('meterial_lib.batch_manage'));
	      }
	    }
	  }, {
	    key: 'onClickDeleteBatchBtn',
	    value: function onClickDeleteBatchBtn(event) {
	      var self = this;
	      var $target = $(event.currentTarget);
	      var ids = [];
	      $('#material-lib-items-panel').find('[data-role=batch-item]:checked').each(function () {
	        ids.push(this.value);
	      });
	      if (ids == "") {
	        (0, _notify2["default"])('danger', Translator.trans('meterial_lib.select_resource_delete_hint'));
	        return;
	      }
	      $('#modal').html('');
	      $('#modal').load($target.data('url'), { ids: ids });
	      $('#modal').modal('show');
	    }
	  }, {
	    key: 'onClickShareBatchBtn',
	    value: function onClickShareBatchBtn(event) {
	      if (confirm(Translator.trans('meterial_lib.confirm_share_resource_hint'))) {
	        var $target = $(event.currentTarget);
	        var ids = [];
	        $('#material-lib-items-panel').find('[data-role=batch-item]:checked').each(function () {
	          ids.push(this.value);
	        });
	
	        this._fileShare(ids, $target.data('url'));
	        $('#material-lib-items-panel').find('[data-role=batch-item]').show();
	      }
	    }
	  }, {
	    key: 'onClickTagBatchBtn',
	    value: function onClickTagBatchBtn(event) {
	      var self = this;
	      var $target = $(event.currentTarget);
	      var ids = [];
	      this.element.find('[data-role=batch-item]:checked').each(function () {
	        ids.push(this.value);
	      });
	      if (ids == '') {
	        (0, _notify2["default"])('danger', Translator.trans('meterial_lib.select_resource_operate_hint'));
	        return;
	      }
	      $('#select-tag-items').val(ids);
	      $('#tag-modal').modal('show');
	    }
	  }, {
	    key: 'onClickProcessStatusBtn',
	    value: function onClickProcessStatusBtn(event) {
	      this.renderTable();
	    }
	  }, {
	    key: 'onClickUseStatusBtn',
	    value: function onClickUseStatusBtn(event) {
	      this.renderTable();
	    }
	  }, {
	    key: 'onClickShareBtn',
	    value: function onClickShareBtn(event) {
	      if (confirm(Translator.trans('meterial_lib.confirm_share_resource_hint'))) {
	        var $target = $(event.currentTarget);
	
	        var ids = [];
	        ids.push($target.data('fileId'));
	
	        this._fileShare(ids, $target.data('url'));
	      }
	    }
	  }, {
	    key: 'onClickUnshareBtn',
	    value: function onClickUnshareBtn(event) {
	      if (confirm(Translator.trans('meterial_lib.confirm_unshare_resource_hint'))) {
	        var self = this;
	        var $target = $(event.currentTarget);
	
	        $.post($target.data('url'), function (response) {
	          if (response) {
	            (0, _notify2["default"])('success', Translator.trans('meterial_lib.unshare_resource_success_hint'));
	            self.renderTable();
	          }
	        });
	      }
	    }
	  }, {
	    key: 'onClickPagination',
	    value: function onClickPagination(event) {
	      var $target = $(event.currentTarget);
	      this.element.find('.js-page').val($target.data('page'));
	      this.renderTable(true);
	      event.preventDefault();
	    }
	  }, {
	    key: 'onClickReconvertBtn',
	    value: function onClickReconvertBtn(event) {
	      var self = this;
	      var $target = $(event.currentTarget);
	      $target.button('loading');
	      $.ajax({
	        type: 'POST',
	        url: $target.data('url')
	      }).done(function (response) {
	        (0, _notify2["default"])('success', Translator.trans('subtitle.status.success'));
	        $target.parents(".materials-list").replaceWith($(response));
	      }).fail(function () {
	        (0, _notify2["default"])('danger', Translator.trans('subtitle.status.error'));
	      }).always(function () {
	        $target.button('reset');
	      });
	    }
	  }, {
	    key: 'renderTable',
	    value: function renderTable(isPaginator) {
	      isPaginator || this._resetPage();
	      var self = this;
	      var $table = $('#material-item-list');
	      this._loading();
	      $.ajax({
	        type: 'GET',
	        url: this.renderUrl,
	        data: this.element.serialize()
	      }).done(function (resp) {
	        $table.html(resp);
	        $('[data-toggle="tooltip"]').tooltip();
	        var mode = self.model;
	        var attribute = self.attribute;
	        if (mode == 'edit' && attribute == 'mine') {
	          $('#material-lib-batch-bar').show();
	          $('#material-lib-items-panel').find('[data-role=batch-item]').show();
	          $("[data-role=batch-select]").attr("checked", false);
	        } else if (mode == 'normal') {
	          $('#material-lib-batch-bar').hide();
	          $('#material-lib-items-panel').find('[data-role=batch-item]').hide();
	        }
	        var $temp = $table.find('.js-paginator');
	        self.element.find('[data-role=paginator]').html($temp.html());
	      }).fail(function () {
	        self._loaded_error();
	      });
	    }
	  }, {
	    key: '_loading',
	    value: function _loading() {
	      var loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">' + Translator.trans('site.loading') + '</div>';
	      var $table = $('#material-item-list');
	      $table.html(loading);
	    }
	  }, {
	    key: '_loaded_error',
	    value: function _loaded_error() {
	      var loading = '<div class="empty" colspan="10" style="color:#999;padding:80px;">' + Translator.trans('site.loading_error') + '</div>';
	      var $table = $('#material-item-list');
	      $table.html(loading);
	    }
	  }, {
	    key: '_resetPage',
	    value: function _resetPage() {
	      this.element.find('.js-page').val(1);
	    }
	  }, {
	    key: '_fileShare',
	    value: function _fileShare(ids, url) {
	      var self = this;
	      if (ids == "") {
	        (0, _notify2["default"])('danger', Translator.trans('meterial_lib.select_share_resource_hint'));
	        return;
	      }
	      $.post(url, { "ids": ids }, function (data) {
	        if (data) {
	          (0, _notify2["default"])('success', Translator.trans('meterial_lib.share_resource_success_hint'));
	          self.renderTable();
	        } else {
	          (0, _notify2["default"])('danger', Translator.trans('meterial_lib.share_resource_erroe_hint'));
	          self.renderTable();
	        }
	      });
	    }
	  }, {
	    key: '_initHeader',
	    value: function _initHeader() {
	      //init timepicker
	      var self = this;
	      $('#startDate').datetimepicker({
	        autoclose: true
	      }).on('changeDate', function () {
	        $('#endDate').datetimepicker('setStartDate', $('#startDate').val().substring(0, 16));
	        self.renderTable();
	      });
	
	      $('#startDate').datetimepicker('setEndDate', $('#endDate').val().substring(0, 16));
	
	      $('#endDate').datetimepicker({
	        autoclose: true
	      }).on('changeDate', function () {
	
	        $('#startDate').datetimepicker('setEndDate', $('#endDate').val().substring(0, 16));
	        self.renderTable();
	      });
	
	      $('#endDate').datetimepicker('setStartDate', $('#startDate').val().substring(0, 16));
	    }
	  }, {
	    key: '_initSelect2',
	    value: function _initSelect2() {
	      (0, _inputSelect2["default"])('#modal-tags', 'remote');
	    }
	  }, {
	    key: 'initTagForm',
	    value: function initTagForm(event) {
	      var $form = $('#tag-form');
	      var validator = $form.validate({
	        rules: {
	          tags: {
	            required: true
	          }
	        }
	      });
	    }
	  }]);
	
	  return MaterialWidget;
	}();
	
	var materialWidget = new MaterialWidget();
	
	$('#modal').on('click', '.file-delete-form-btn', function (event) {
	  var $form = $('#file-delete-form');
	
	  $(this).button('loading').addClass('disabled');
	  $.post($form.attr('action'), $form.serialize(), function (data) {
	    if (data) {
	      $('#modal').modal('hide');
	      (0, _notify2["default"])('success', Translator.trans('meterial_lib.delete_resource_success_hint'));
	      materialWidget.renderTable(true);
	    }
	    $('#material-lib-items-panel').find('[data-role=batch-item]').show();
	    $('#material-lib-items-panel').find('[data-role=batch-select]').attr('checked', false);
	  });
	});

/***/ }),

/***/ "a0438f6657de6354719c":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _inputSelect = __webpack_require__("d919d1055afe1010ecbe");
	
	var _inputSelect2 = _interopRequireDefault(_inputSelect);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Info = function () {
	  function Info(options) {
	    _classCallCheck(this, Info);
	
	    this.element = options.element;
	    this.callback = options.callback;
	    this.init();
	  }
	
	  _createClass(Info, [{
	    key: 'init',
	    value: function init() {
	      this.initEvent();
	      this._initTag();
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      $('#info-form').on('submit', function (event) {
	        _this.onSubmitInfoForm(event);
	      });
	    }
	  }, {
	    key: '_initTag',
	    value: function _initTag() {
	      (0, _inputSelect2["default"])('#infoTags', 'remote', {
	        width: 'off'
	      });
	    }
	  }, {
	    key: 'onSubmitInfoForm',
	    value: function onSubmitInfoForm(event) {
	      var $target = $(event.currentTarget);
	      $target.find('#info-save-btn').button('loading');
	      $.ajax({
	        type: 'POST',
	        url: $target.attr('action'),
	        data: $('#info-form').serialize()
	
	      }).done(function () {
	        (0, _notify2["default"])('success', Translator.trans('site.save_success_hint'));
	      }).fail(function () {
	        (0, _notify2["default"])('danger', Translator.trans('site.save_error_hint'));
	      }).always(function () {
	        $target.find('#info-save-btn').button('reset');
	      });
	
	      event.preventDefault();
	    }
	  }]);
	
	  return Info;
	}();
	
	exports["default"] = Info;

/***/ })

});
//# sourceMappingURL=index.js.map