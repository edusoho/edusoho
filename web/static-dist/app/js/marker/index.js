webpackJsonp(["app/js/marker/index"],{

/***/ "fe450c45f7142d95f6ed":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	var _utils = __webpack_require__("9181c6995ae8c5c94b7a");
	
	var Tool = _interopRequireWildcard(_utils);
	
	function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } else { var newObj = {}; if (obj != null) { for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) newObj[key] = obj[key]; } } newObj["default"] = obj; return newObj; } }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Drag = function () {
	  function Drag(_ref) {
	    var element = _ref.element,
	        _ref$initMarkerArry = _ref.initMarkerArry,
	        initMarkerArry = _ref$initMarkerArry === undefined ? [] : _ref$initMarkerArry,
	        _ref$_video_time = _ref._video_time,
	        _video_time = _ref$_video_time === undefined ? '18' : _ref$_video_time,
	        _ref$messenger = _ref.messenger,
	        messenger = _ref$messenger === undefined ? {} : _ref$messenger,
	        _ref$editbox = _ref.editbox,
	        editbox = _ref$editbox === undefined ? '.editbox' : _ref$editbox,
	        _ref$scalebox = _ref.scalebox,
	        scalebox = _ref$scalebox === undefined ? '.js-scalebox' : _ref$scalebox,
	        _ref$timepartnum = _ref.timepartnum,
	        timepartnum = _ref$timepartnum === undefined ? '6' : _ref$timepartnum,
	        _ref$markers_array = _ref.markers_array,
	        markers_array = _ref$markers_array === undefined ? new Array() : _ref$markers_array,
	        addScale = _ref.addScale,
	        mergeScale = _ref.mergeScale,
	        updateScale = _ref.updateScale,
	        deleteScale = _ref.deleteScale,
	        updateSeq = _ref.updateSeq;
	
	    _classCallCheck(this, Drag);
	
	    this.$element = $(element);
	    this.initMarkerArry = initMarkerArry;
	    this.markers_array = markers_array;
	    this._video_time = _video_time;
	    this.messenger = messenger;
	    this.editbox = editbox;
	    this.timepartnum = timepartnum;
	
	    this.courseId = this.$element.data("course-id");
	
	    this.addScale = addScale;
	    this.mergeScale = mergeScale;
	    this.updateScale = updateScale;
	    this.deleteScale = deleteScale;
	    this.updateSeq = updateSeq;
	
	    this.init();
	  }
	
	  _createClass(Drag, [{
	    key: 'init',
	    value: function init() {
	      this.initSortable();
	      this.initeditbox(false);
	      this.initMarker(this.initMarkerArry);
	      this.lisentresize();
	      this.initPlayer();
	
	      this.initEvent();
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('mousedown', '.gruop-lesson-list .drag', function (event) {
	        return _this.itemDraggable(event);
	      });
	      this.$element.on('click', '.lesson-list [data-role="question-remove"]', function (event) {
	        return _this.itemRmove(event);
	      });
	      this.$element.on('click', '#subject-lesson-list .item-lesson', function (event) {
	        return _this.stopEvent(event);
	      });
	      this.$element.on('mousedown', '.scale-blue', function (event) {
	        return _this.slideScale(event);
	      });
	      this.$element.on('mouseenter', '.scale-blue', function (event) {
	        return _this.hoverScale(event);
	      });
	      // this.$element.on('mousedown', '.scale-blue .item-lesson', event => this.previewQuestion(event));
	      this.$element.on('mousedown', '.js-question-preview', function (event) {
	        return _this.previewMouseDown(event);
	      });
	    }
	  }, {
	    key: 'initPlayer',
	    value: function initPlayer() {
	      var messenger = this.messenger;
	      var _self = this;
	      var changeleft = true;
	      var $editbox_list = $('#editbox-lesson-list');
	      messenger.on("timechange", function (data) {
	        if (changeleft) {
	          $('.scale-white').css('left', _self.getleft(data.currentTime));
	        }
	      });
	      $('.scale-white').on('mousedown', function (event) {
	        changeleft = false;
	        $(document).on('mousemove.playertime', function (event) {
	          window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
	          var left = event.pageX > $editbox_list.width() + 20 ? $editbox_list.width() + 20 : event.pageX && event.pageX <= 20 ? 20 : event.pageX;
	          $('.scale-white').css('left', left);
	          var times = _self.gettime(left);
	          messenger.sendToChild({ id: 'viewerIframe' }, 'setCurrentTime', { time: times });
	        }).on('mouseup.playertime', function (event) {
	          $(document).off('mousemove.playertime');
	          $(document).off('mousedown.playertime');
	          changeleft = true;
	          // messenger.sendToChild({ id: 'viewerIframe' }, 'setPlayerPlay');
	        });
	      });
	    }
	  }, {
	    key: 'initSortable',
	    value: function initSortable() {
	      var _obj = this;
	      $("#subject-lesson-list").sortable({
	        group: 'no-drop',
	        drop: false,
	        delay: 500,
	        handle: '.drag',
	        onDrop: function onDrop($item, container, _super) {
	          if ($item.hasClass('item-lesson')) {
	            _super($item, container);
	            var $_scale = $item.closest('.scale.blue');
	            if ($_scale.find('.lesson-list .item-lesson').length > 0) {
	              _obj.sortList($_scale.find('.lesson-list'));
	              _obj.addScale($_scale, $_scale.find('.time').html(), $_scale.css("left"), $_scale.find('.lesson-list').children().length);
	            }
	          }
	        }
	      });
	
	      $("#editbox-lesson-list").sortable({
	        group: 'no-drop',
	        drag: false
	      });
	    }
	  }, {
	    key: 'sortList',
	    value: function sortList($list) {
	      var num = 1;
	      $list.find('.item-lesson').each(function () {
	        $(this).find('[data-role="sqe-number"]').text(num);
	        num++;
	      });
	    }
	  }, {
	    key: 'addScale',
	    value: function addScale($marker, time, seq, markers_array) {
	      var $marker_item = $marker.find('li' + ':last');
	      var markerJson = {
	        "id": $marker.attr('id'),
	        "second": time,
	        "questionMarkers": [{
	          "id": $marker_item.attr('id'),
	          "seq": seq,
	          "questionId": $marker_item.attr('question-id')
	        }]
	      };
	      $.extend(this.addScale(markerJson, $marker, markers_array));
	    }
	  }, {
	    key: 'initeditbox',
	    value: function initeditbox(isresize) {
	      var _self = this,
	          $_editbox = $(_self.editbox);
	      if (isresize) {
	        $_editbox.find('.scale.scale-default:visible').each(function () {
	          $(this).css('left', _self.getleft(Tool.time2Sec($(this).find('[data-role="scale-time"]').text())));
	        });
	        $_editbox.find('.scale.scale-blue:visible').each(function () {
	          $(this).css('left', _self.getleft(Tool.time2Sec($(this).find('[data-role="scale-blue-time"]').text())));
	        });
	      } else {
	        var _partnum = _self.timepartnum;
	        var _parttime = _self._video_time / _partnum;
	        for (var i = 0; i <= _partnum; i++) {
	          var $new_scale_default = $('[data-role="scale-default"]').clone().css('left', _self.getleft(_parttime * i)).removeClass('hidden').removeAttr('data-role');
	
	          $new_scale_default.find('[data-role="scale-time"]').text(Tool.sec2Time(Math.round(_parttime * i)));
	          $('[data-role="scale-default"]').before($new_scale_default);
	        }
	      }
	    }
	  }, {
	    key: 'initMarker',
	    value: function initMarker(initMarkerArry) {
	      if (initMarkerArry.length > 0) {
	        var $scale_blue = $('[data-role="scale-blue"]');
	        for (var i = 0; i < initMarkerArry.length; i++) {
	          var $new_scale_blue = $scale_blue.clone().css('left', this.getleft(initMarkerArry[i].second)).removeAttr('data-role').removeClass('hidden').attr('id', initMarkerArry[i].id);
	
	          var $scale_blue_time = $new_scale_blue.find('[data-role="scale-blue-time"]').text(Tool.sec2Time(initMarkerArry[i].second));
	          var questionMarkers = initMarkerArry[i].questionMarkers;
	          var $scale_blue_item = $new_scale_blue.find('[data-role="scale-blue-item"]');
	
	          for (var j = 0; j < questionMarkers.length; j++) {
	            var $new_scale_blue_item = $scale_blue_item.clone().removeAttr('data-role').attr({ 'question-id': questionMarkers[j].questionId, 'id': questionMarkers[j].id });
	
	            console.log('new_scale_blue_item', $new_scale_blue_item);
	
	            $new_scale_blue_item.data('url', '/course/' + this.courseId + '/question/' + questionMarkers[j].questionId + '/marker/preview').find('[data-role="sqe-number"]').text(j + 1).end().find('[data-role="question-type"]').text('单选题').end().find('[data-role="question-info"]').text(questionMarkers[j].stem.replace(/<.*?>/ig, ""));
	
	            $scale_blue_item.before($new_scale_blue_item);
	          }
	
	          $scale_blue.after($new_scale_blue);
	          $scale_blue_item.remove();
	          this.markers_array.push({ id: initMarkerArry[i].id, time: initMarkerArry[i].second });
	        }
	        this.newSortList($(this.scalebox).find('[data-role="scale-blue-list"]'));
	      }
	    }
	  }, {
	    key: 'lisentresize',
	    value: function lisentresize() {
	      var _self = this;
	      $(window).resize(function () {
	        _self.initeditbox(true);
	      });
	    }
	  }, {
	    key: 'getleft',
	    value: function getleft(time) {
	      var _width = $('#editbox-lesson-list').width();
	      var _totaltime = parseInt(this._video_time);
	      var _left = time * _width / _totaltime;
	      return _left + 20;
	    }
	  }, {
	    key: 'newSortList',
	    value: function newSortList($list) {
	      var _self = this;
	      $list.sortable({
	        delay: 500,
	        itemSelector: '.item-lesson',
	        onDrop: function onDrop($item, container, _super) {
	          _super($item, container);
	          _self.maskShow(false);
	          var $scale_blue = $item.closest('.scale-blue');
	          var markerJson = {
	            "id": '',
	            "questionMarkers": []
	          };
	          markerJson.id = $scale_blue.attr('id');
	          _self.sortList($scale_blue.find('[data-role="scale-blue-list"]'));
	
	          $scale_blue.find("li").each(function () {
	            var questionMarkers = {
	              'id': $(this).attr('id'),
	              'seq': $(this).find('[data-role="sqe-number"]').html()
	            };
	            markerJson.questionMarkers.push(questionMarkers);
	          });
	          _self._updateSeq($scale_blue, markerJson);
	          $scale_blue.removeClass('moveing');
	        },
	        serialize: function serialize(parent, children, isContainer) {
	          return isContainer ? children : parent.attr('id');
	        },
	        isValidTarget: function isValidTarget($item, container) {
	          _self.maskShow(true);
	          $item.closest('.scale-blue').addClass('moveing');
	          return true;
	        }
	      });
	    }
	  }, {
	    key: 'maskShow',
	    value: function maskShow(show) {
	      show ? $('[data-role="player-mask"]').removeClass('hidden') : $('[data-role="player-mask"]').addClass('hidden');
	    }
	  }, {
	    key: 'gettime',
	    value: function gettime(left) {
	      return Math.round((left - 20) * this._video_time / $('#editbox-lesson-list').width());
	    }
	  }, {
	    key: 'itemDraggable',
	    value: function itemDraggable(e) {
	      var _self = this,
	          marker_array = [],
	          $merge_marker = null,
	          _mover_left = null,
	          _move_time = null;
	
	      var $dragingitem = $(e.currentTarget),
	          $editbox_list = $('#editbox-lesson-list'),
	          $scale_red = $('[data-role="scale-red"]'),
	          $scale_red_details = $scale_red.find('[data-role="scale-red-details"]'),
	          $dragingitemcopy = $dragingitem.clone().removeClass('drag').addClass('disdragg');
	
	      $dragingitem.after($dragingitemcopy);
	      _self.maskShow(true);
	
	      //查询现有的时间刻度：数组保存现有的所有时间刻度 markers_array
	      $(document).on('mousemove.dragitem', function (event) {
	        if ($editbox_list.find('.placeholder').length <= 0) {
	          $scale_red.addClass('hidden');
	          $editbox_list.removeClass('highlight');
	          return;
	        }
	        //显示红线
	        $editbox_list.addClass('highlight');
	
	        _mover_left = event.pageX > $editbox_list.width() + 20 ? $editbox_list.width() + 20 : event.pageX && event.pageX <= 20 ? 20 : event.pageX;
	        _move_time = _self.gettime(_mover_left);
	        $scale_red_details.text(Tool.sec2Time(_move_time));
	        $scale_red.removeClass('hidden').css('left', _mover_left);
	
	        //markers_array靠近的元素提示合并,
	        if (_self.markers_array.length > 0) {
	          $('.scale-blue').removeClass('highlight');
	          marker_array = [];
	          $merge_marker = null;
	          for (var i in _self.markers_array) {
	            if (Math.abs(_self.markers_array[i].time - _move_time) <= 5) {
	              marker_array = [{
	                id: _self.markers_array[i].id,
	                time: _self.markers_array[i].time
	              }];
	              //靠近的元素刻度线高亮条件ID
	              $merge_marker = $('.scale-blue[id=' + _self.markers_array[i].id + ']').addClass('highlight');
	              return;
	            }
	          }
	        }
	      }).on('mouseup.dragitem', function (event) {
	        $(document).off('mousemove.dragitem');
	        $(document).off('mouseup.dragitem');
	        _self.maskShow(false);
	        $scale_red.addClass('hidden');
	        $editbox_list.removeClass('highlight');
	        //未拖动
	        var $moveeditem = $editbox_list.find('.item-lesson');
	        if ($moveeditem.length <= 0) {
	          $scale_red.addClass('hidden');
	          $editbox_list.removeClass('highlight');
	          $dragingitemcopy.remove();
	          return;
	        }
	        //标记已存在，新增题目
	        if (marker_array.length > 0) {
	          var $list = $merge_marker.find('[data-role="scale-blue-list"]');
	          $moveeditem.appendTo($list).find('[data-role="sqe-number"]').text($list.children().length);
	          _self._addScale($merge_marker, marker_array[0].time, $list.children().length, _self.markers_array);
	          $merge_marker.removeClass('highlight');
	          _self.newSortList($list);
	        } else {
	          //新增标记和题目
	          var $scale_blue = $('[data-role="scale-blue"]'),
	              $new_scale_blue = $scale_blue.clone().css('left', _mover_left).removeAttr('data-role'),
	              $scale_blue_list = $new_scale_blue.find('[data-role="scale-blue-list"]'),
	              $scale_blue_time = $new_scale_blue.find('[data-role="scale-blue-time"]').text(Tool.sec2Time(_move_time));
	          $scale_blue_list.children().remove();
	          $scale_blue_list.append($moveeditem);
	          $scale_blue.after($new_scale_blue);
	          _self._addScale($new_scale_blue, _move_time, 1, _self.markers_array);
	        }
	      });
	    }
	  }, {
	    key: 'itemRmove',
	    value: function itemRmove(e) {
	      e.stopPropagation();
	      var $this = $(e.currentTarget);
	      var $list = $this.closest('[data-role="scale-blue-list"]'),
	          $marker_question = $this.closest('li'),
	          $marker = $this.closest('.scale-blue');
	      this._deleteScale($marker, $marker_question, $list.children().length, this.markers_array);
	    }
	  }, {
	    key: 'stopEvent',
	    value: function stopEvent(e) {
	      e.stopPropagation();
	    }
	  }, {
	    key: 'slideScale',
	    value: function slideScale(e) {
	      var _self = this,
	          marker_array = [],
	          $merge_marker = null,
	          _mover_left = null,
	          _move_time = null;
	
	      var $moveitem = $(e.currentTarget),
	          $editbox_list = $('#editbox-lesson-list'),
	          _oldleft = $moveitem.css('left');
	      _self.maskShow(true);
	      $('.marker-manage').addClass('slideing');
	      $moveitem.addClass('moveing');
	      $(document).on('mousemove.slide', function (event) {
	        window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
	        _mover_left = event.pageX > $editbox_list.width() + 20 ? $editbox_list.width() + 20 : event.pageX && event.pageX <= 20 ? 20 : event.pageX;
	        _move_time = Math.round((_mover_left - 20) * _self._video_time / $editbox_list.width());
	        $moveitem.css('left', _mover_left);
	        $moveitem.find('[data-role="scale-blue-time"]').text(Tool.sec2Time(_move_time));
	
	        if (_self.markers_array.length > 0) {
	          $('.scale-blue').removeClass('highlight');
	          marker_array = [];
	          $merge_marker = null;
	          for (var i in _self.markers_array) {
	            if (Math.abs(_self.markers_array[i].time - _move_time) <= 5 && $moveitem.attr('id') != _self.markers_array[i].id) {
	              marker_array = [{
	                id: _self.markers_array[i].id,
	                time: _self.markers_array[i].time
	              }];
	              //靠近的元素刻度线高亮条件ID
	              $merge_marker = $('.scale-blue[id=' + _self.markers_array[i].id + ']').addClass('highlight');
	              return;
	            }
	          }
	        }
	      }).on('mouseup.slide', function (event) {
	        $(document).off('mousemove.slide');
	        $(document).off('mouseup.slide');
	        _self.maskShow(false);
	        $moveitem.removeClass('moveing');
	        $('.marker-manage').removeClass('slideing');
	        if (marker_array.length > 0) {
	          var $list = $merge_marker.find('[data-role="scale-blue-list"]');
	          $list.append($moveitem.find('[data-role="scale-blue-list"]').children());
	          _self.sortList($list);
	          $merge_marker.removeClass('highlight');
	          _self._mergeScale($moveitem, $merge_marker, _self.markers_array);
	        } else {
	          //新增
	          _self._updateScale($moveitem, _move_time);
	        }
	      });
	    }
	  }, {
	    key: 'hoverScale',
	    value: function hoverScale(e) {
	      var $this = $(e.currentTarget);
	      if ($this.offset().left - 20 < 110) {
	        $this.find('.scale-details').css('margin-left', '-' + ($this.offset().left - 20) + 'px');
	      } else {
	        $this.find('.scale-details').css('margin-left', '-110px');
	      }
	    }
	
	    // previewQuestion(e) {
	    //   e.stopPropagation();
	    //   let $this = $(e.currentTarget), url = $this.data('url');
	    //   if (url) {
	    //     let imgUrl = app.config.loading_img_path;
	    //     let $target = $($this.data('target'));
	    //     let $loadingImg = "<img src='" + imgUrl + "' class='modal-loading' style='z-index:1041;width:60px;height:60px;position:absolute;top:50%;left:50%;margin-left:-30px;margin-top:-30px;'/>";
	    //     $target.html($loadingImg);
	    //     $target.load(url);
	    //   }
	    // }
	
	  }, {
	    key: 'previewMouseDown',
	    value: function previewMouseDown(e) {
	      //阻止默认事件，父层的拖动
	      e.stopPropagation();
	    }
	  }, {
	    key: '_addScale',
	    value: function _addScale($marker, time, seq, markers_array) {
	      var $marker_item = $marker.find('li' + ':last');
	      var markerJson = {
	        "id": $marker.attr('id'),
	        "second": time,
	        "questionMarkers": [{
	          "id": $marker_item.attr('id'),
	          "seq": seq,
	          "questionId": $marker_item.attr('question-id')
	        }]
	      };
	      $.extend(this.addScale(markerJson, $marker, markers_array));
	    }
	  }, {
	    key: '_mergeScale',
	    value: function _mergeScale($marker, $merg_marker, markers_array) {
	      // 合并时后台去处理顺序，被合并数按序号依次增加
	      var markerJson = {
	        "id": $marker.attr('id'),
	        "merg_id": $merg_marker.attr('id')
	      };
	      $.extend(this.mergeScale(markerJson, $marker, $merg_marker, markers_array));
	    }
	  }, {
	    key: '_updateScale',
	    value: function _updateScale($marker, time) {
	      var markerJson = {
	        "id": $marker.attr('id'),
	        "second": time
	      };
	      $.extend(this.updateScale(markerJson, $marker));
	    }
	  }, {
	    key: '_deleteScale',
	    value: function _deleteScale($marker, $marker_question, marker_questions_num, markers_array) {
	      console.log('id', $marker, $marker.attr('id'));
	      var markerJson = {
	        "id": $marker.attr('id'),
	        "questionMarkers": [{
	          "id": $marker_question.attr('id'),
	          "seq": $marker_question.find('[data-role="sqe-number"]').html(),
	          "questionId": $marker_question.attr('question-id')
	        }]
	      };
	      $.extend(this.deleteScale(markerJson, $marker, $marker_question, marker_questions_num, markers_array));
	    }
	  }, {
	    key: '_updateSeq',
	    value: function _updateSeq($scale, markerJson) {
	      $.extend(this.updateSeq($scale, markerJson));
	    }
	  }]);
	
	  return Drag;
	}();
	
	exports["default"] = Drag;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _manage = __webpack_require__("4fc7bc177a8be8d19796");
	
	var _manage2 = _interopRequireDefault(_manage);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _manage2["default"]({
	  formSelect: '.js-mark-form',
	  markerSelect: '.js-marker-manage-content'
	});

/***/ }),

/***/ "4fc7bc177a8be8d19796":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	__webpack_require__("db9fa60e685f75e2d7f6");
	
	var _messenger = __webpack_require__("82e9514f4e98661ef32b");
	
	var _messenger2 = _interopRequireDefault(_messenger);
	
	var _drag = __webpack_require__("fe450c45f7142d95f6ed");
	
	var _drag2 = _interopRequireDefault(_drag);
	
	var _jsCookie = __webpack_require__("fe53252afd7b6c35cb73");
	
	var _jsCookie2 = _interopRequireDefault(_jsCookie);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var drag = function drag(initMarkerArry, mediaLength, messenger) {
	  var drag = new _drag2["default"]({
	    element: '#task-dashboard',
	    initMarkerArry: initMarkerArry,
	    _video_time: mediaLength,
	    messenger: messenger,
	    addScale: function addScale(markerJson, $marker, markers_array) {
	      var url = $('.js-pane-question-content').data('queston-marker-add-url');
	      var param = {
	        markerId: markerJson.id,
	        second: markerJson.second,
	        questionId: markerJson.questionMarkers[0].questionId,
	        seq: markerJson.questionMarkers[0].seq
	      };
	      $.post(url, param, function (data) {
	        if (data.id == undefined) {
	          return;
	        }
	        //新增时间刻度
	        if (markerJson.id == undefined) {
	          $marker.attr('id', data.markerId);
	          markers_array.push({ id: data.markerId, time: markerJson.second });
	          //排序
	        }
	        $marker.removeClass('hidden');
	        $marker.find('.item-lesson[question-id=' + markerJson.questionMarkers[0].questionId + ']').attr('id', data.id);
	      });
	      return markerJson;
	    },
	    mergeScale: function mergeScale(markerJson, $marker, $merg_emarker, markers_array) {
	      var url = $('.js-pane-question-content').data('marker-merge-url');
	      $.post(url, {
	        sourceMarkerId: markerJson.id,
	        targetMarkerId: markerJson.merg_id
	      }, function (data) {
	        $marker.remove();
	        for (var i in markers_array) {
	          if (markers_array[i].id == markerJson.id) {
	            markers_array.splice(i, 1);
	            break;
	          }
	        }
	      });
	      return markerJson;
	    },
	    updateScale: function updateScale(markerJson, $marker) {
	      var url = $('.js-pane-question-content').data('marker-update-url');
	      var param = {
	        id: markerJson.id,
	        second: markerJson.second
	      };
	      if (markerJson.second) {
	        $.post(url, param, function (data) {});
	      } else {
	        console.log('do not need upgrade scale...');
	      }
	      return markerJson;
	    },
	    deleteScale: function deleteScale(markerJson, $marker, $marker_question, marker_questions_num, markers_array) {
	      var url = $('.js-pane-question-content').data('queston-marker-delete-url');
	      $.post(url, {
	        questionId: markerJson.questionMarkers[0].id
	      }, function (data) {
	        $marker_question.remove();
	        console.log(markerJson.questionMarkers[0].questionId, 'questionId');
	        $('#subject-lesson-list').find('.item-lesson[question-id=' + markerJson.questionMarkers[0].questionId + ']').removeClass('disdragg').addClass('drag');
	        if ($marker.find('[data-role="scale-blue-list"]').children().length <= 0) {
	          $marker.remove();
	          for (var i in markers_array) {
	            if (markers_array[i].id == $marker.attr('id')) {
	              markers_array.splice(i, 1);
	              break;
	            }
	          }
	        } else {
	          //剩余排序
	          console.log('drag', drag);
	          drag.sortList($marker.find('[data-role="scale-blue-list"]'));
	        }
	      });
	    },
	    updateSeq: function updateSeq($scale, markerJson) {
	      if (markerJson == undefined || markerJson.questionMarkers == undefined || markerJson.questionMarkers.length == 0) {
	        return;
	      }
	
	      var url = $('.js-pane-question-content').data('queston-marker-sort-url');
	      var param = new Array();
	
	      for (var i = 0; i < markerJson.questionMarkers.length; i++) {
	        param.push(markerJson.questionMarkers[i].id);
	      }
	
	      $.post(url, { questionIds: param });
	    }
	  });
	
	  return drag;
	};
	
	var Manage = function () {
	  function Manage(options) {
	    _classCallCheck(this, Manage);
	
	    this.$form = $(options.formSelect);
	    this.$marker = $(options.markerSelect);
	    this.init();
	  }
	
	  _createClass(Manage, [{
	    key: 'init',
	    value: function init() {
	      this.initData();
	      this.initEvent();
	    }
	  }, {
	    key: 'initData',
	    value: function initData() {
	      var _this = this;
	
	      var count = parseInt((document.body.clientHeight - 350) / 50) > 0 ? parseInt((document.body.clientHeight - 350) / 50) : 1;
	
	      $.post(this.$form.attr('action'), this.$form.serialize() + '&pageSize=' + count, function (response) {
	        $('#subject-lesson-list').html(response);
	        $('[data-toggle="popover"]').popover();
	        if (!_jsCookie2["default"].get("MARK-MANGE-GUIDE")) {
	          _this.initIntro();
	        } else {
	          _this.initDrag();
	          $('#step-1').removeClass('introhelp-icon-help');
	        }
	        _jsCookie2["default"].set("MARK-MANGE-GUIDE", 'true', { expires: 360, path: "/" });
	        _this.$form.data('pageSize', count);
	      });
	    }
	  }, {
	    key: 'initIntro',
	    value: function initIntro() {
	      $('.js-introhelp-overlay').removeClass('hidden');
	      $('.show-introhelp').addClass('show');
	
	      var $img = $('.js-introhelp-img img'),
	          img = document.createElement('img'),
	          imgheight = $(window).height() - $img.offset().top - 80;
	
	      img.src = $img.attr('src');
	      var left = imgheight * img.width / img.height / 2 + 50;
	      $img.height(imgheight);
	      $('.js-introhelp-img').css('margin-left', '-' + left + 'px');
	    }
	  }, {
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this2 = this;
	
	      this.$marker.on('click', '.js-question-preview', function (event) {
	        return _this2.onQuestionPreview(event);
	      });
	      this.$marker.on('click', '.js-more-questions', function (event) {
	        return _this2.onMoreQuestion(event);
	      });
	      this.$marker.on('click', '.js-close-introhelp', function (event) {
	        return _this2.onCloseHelp(event);
	      });
	      this.$marker.on('click', '#mark-form-submit', function (event) {
	        return _this2.onFormSubmit(event);
	      });
	      this.$marker.on('change', '#mark-form-target', function (event) {
	        return _this2.onChangeSelect(event);
	      });
	      this.$marker.on('keydown', '#mark-form-keyword', function (event) {
	        return _this2.onFormAutoSubmit(event);
	      });
	    }
	  }, {
	    key: 'onFormAutoSubmit',
	    value: function onFormAutoSubmit(event) {
	      if (event.keyCode == 13) {
	        event.preventDefault();
	        this.onFormSubmit(event);
	      }
	    }
	  }, {
	    key: 'onFormSubmit',
	    value: function onFormSubmit(e) {
	      var validator = this.$form.validate();
	
	      if (validator.form()) {
	        var count = this.$form.data('pageSize');
	        $.post(this.$form.attr('action'), this.$form.serialize() + '&pageSize=' + count, function (response) {
	          $('#subject-lesson-list').html(response);
	        });
	      }
	    }
	  }, {
	    key: 'onChangeSelect',
	    value: function onChangeSelect(e) {
	      this.onFormSubmit(e);
	    }
	  }, {
	    key: 'onQuestionPreview',
	    value: function onQuestionPreview(e) {
	      $.get($(e.currentTarget).data('url'), function (response) {
	        $('.modal').modal('show');
	        $('.modal').html(response);
	      });
	    }
	  }, {
	    key: 'onMoreQuestion',
	    value: function onMoreQuestion(e) {
	      var target = $('select[name=target]');
	      var $this = $(e.currentTarget).hide().parent().addClass('loading'),
	          $list = $('#subject-lesson-list').css('max-height', $('#subject-lesson-list').height()),
	          getpage = parseInt($this.data('current-page')) + 1,
	          lastpage = $this.data('last-page');
	
	      $.post($this.data('url') + getpage, { 'target': target.val(), 'pageSize': this.$form.data('pageSize') }, function (response) {
	        $this.remove();
	        $list.append(response).animate({ scrollTop: 40 * ($list.find('.item-lesson').length + 1) });
	        if (getpage == lastpage) {
	          $('.js-more-questions').parent().remove();
	        }
	      });
	    }
	  }, {
	    key: 'onCloseHelp',
	    value: function onCloseHelp(e) {
	      var $this = $(e.currentTarget);
	      $this.closest('.show-introhelp').removeClass('show-introhelp');
	      if ($('.show-introhelp').height() <= 0) {
	        $('.js-introhelp-overlay').addClass('hidden');
	        this.initDrag();
	      }
	    }
	  }, {
	    key: 'initDrag',
	    value: function initDrag() {
	      var initMarkerArry = [];
	      var mediaLength = 30;
	
	      $.ajax({
	        type: "get",
	        url: $('.js-pane-question-content').data('marker-metas-url'),
	        cache: false,
	        async: false,
	        success: function success(data) {
	          initMarkerArry = data.markersMeta;
	          mediaLength = data.videoTime;
	        }
	      });
	      drag(initMarkerArry, mediaLength, _messenger2["default"]);
	    }
	  }]);
	
	  return Manage;
	}();
	
	exports["default"] = Manage;

/***/ }),

/***/ "82e9514f4e98661ef32b":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _messenger = __webpack_require__("06597b47670159844043");
	
	var _messenger2 = _interopRequireDefault(_messenger);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var messenger = new _messenger2["default"]({
	  name: 'parent',
	  project: 'PlayerProject',
	  children: [document.getElementById('viewerIframe')],
	  type: 'parent'
	});
	
	exports["default"] = messenger;

/***/ }),

/***/ "db9fa60e685f75e2d7f6":
/***/ (function(module, exports) {

	'use strict';
	
	var videoHtml = $('#task-dashboard');
	var playerUrl = videoHtml.data("media-player");
	var html = '<iframe src=\'' + playerUrl + '\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
	$("#task-video-content").html(html);

/***/ })

});
//# sourceMappingURL=index.js.map