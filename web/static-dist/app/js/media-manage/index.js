webpackJsonp(["app/js/media-manage/index"],{

/***/ "16dca8fd088a3c871f3f":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _messenger = __webpack_require__("609d911a24b2b709511a");
	
	var _messenger2 = _interopRequireDefault(_messenger);
	
	var _utils = __webpack_require__("9181c6995ae8c5c94b7a");
	
	var Tool = _interopRequireWildcard(_utils);
	
	function _interopRequireWildcard(obj) { if (obj && obj.__esModule) { return obj; } else { var newObj = {}; if (obj != null) { for (var key in obj) { if (Object.prototype.hasOwnProperty.call(obj, key)) newObj[key] = obj[key]; } } newObj["default"] = obj; return newObj; } }
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	var $elem = $('.js-editbox');
	var $editbox_list = $('#editbox-lesson-list');
	var partnum = 6;
	var mediaLength = $elem.data('mediaLength');
	var parttime = mediaLength / partnum;
	
	for (var i = 0; i <= partnum; i++) {
	  var $new_scale_default = $('[data-role="scale-default"]').clone().css('left', getleft(parttime * i, mediaLength)).removeClass('hidden').removeAttr('data-role');
	  $new_scale_default.find('[data-role="scale-time"]').text(Tool.sec2Time(Math.round(parttime * i)));
	  $('[data-role="scale-default"]').before($new_scale_default);
	}
	
	_messenger2["default"].on("timechange", function (data) {
	  $('.scale-white').css('left', getleft(data.currentTime, mediaLength));
	});
	
	$('.scale-white').on('mousedown', function (event) {
	  var changeleft = false;
	  $(document).on('mousemove.playertime', function (event) {
	    window.getSelection ? window.getSelection().removeAllRanges() : document.selection.empty();
	    var left = event.pageX > $editbox_list.width() + 20 ? $editbox_list.width() + 20 : event.pageX && event.pageX <= 20 ? 20 : event.pageX;
	    $('.scale-white').css('left', left);
	    var times = gettime(left, mediaLength);
	    _messenger2["default"].sendToChild({ id: 'viewerIframe' }, 'setCurrentTime', { time: times });
	  }).on('mouseup.playertime', function (event) {
	    $(document).off('mousemove.playertime');
	    $(document).off('mousedown.playertime');
	    changeleft = true;
	    // messenger.sendToChild({ id: 'viewerIframe' }, 'setPlayerPlay');
	  });
	});
	
	function getleft(time, videoLength) {
	  var _width = $('#editbox-lesson-list').width();
	  var _totaltime = parseInt(videoLength);
	  var _left = time * _width / _totaltime;
	  return _left + 20;
	}
	
	function gettime(left, mediaLength) {
	  return Math.round((left - 20) * mediaLength / $('#editbox-lesson-list').width());
	}

/***/ }),

/***/ "631b083c4a39c1f7f104":
/***/ (function(module, exports) {

	module.exports = extend
	
	var hasOwnProperty = Object.prototype.hasOwnProperty;
	
	function extend() {
	    var target = {}
	
	    for (var i = 0; i < arguments.length; i++) {
	        var source = arguments[i]
	
	        for (var key in source) {
	            if (hasOwnProperty.call(source, key)) {
	                target[key] = source[key]
	            }
	        }
	    }
	
	    return target
	}


/***/ }),

/***/ "4e9151fb4a048110077b":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	__webpack_require__("b26ed6014df6ee9a0332");
	
	__webpack_require__("16dca8fd088a3c871f3f");
	
	var _subtitleSelect = __webpack_require__("90c40b9c35e074e923b0");
	
	var _subtitleSelect2 = _interopRequireDefault(_subtitleSelect);
	
	var _messenger = __webpack_require__("609d911a24b2b709511a");
	
	var _messenger2 = _interopRequireDefault(_messenger);
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	var _subtitle = __webpack_require__("d5971171c2b26114cfc1");
	
	var _subtitle2 = _interopRequireDefault(_subtitle);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var $textTrackDisplay = $('.text-track-overview');
	var $uploader = $('#uploader');
	
	var Manage = function () {
	  function Manage() {
	    _classCallCheck(this, Manage);
	
	    this.select = null;
	    this.init();
	  }
	
	  _createClass(Manage, [{
	    key: 'init',
	    value: function init() {
	      this.initTextDisplay();
	      this.initSelect();
	      this.initUploader();
	    }
	  }, {
	    key: 'initTextDisplay',
	    value: function initTextDisplay() {
	      var height = $('.manage-edit-body').height();
	      var tabHeight = $('.nav-tabs-edit').height();
	      var textTrackTitleHeight = $('.text-track-title').height();
	      var selectorHeight = $('#track-select').height();
	      $textTrackDisplay.height(height - tabHeight - textTrackTitleHeight - selectorHeight - 140).show();
	    }
	  }, {
	    key: 'initSelect',
	    value: function initSelect() {
	      var select = Object.create(_subtitleSelect2["default"]);
	      var $subtitleListElem = $('#track-select');
	      var subtitleList = $subtitleListElem.data('subtitleList');
	      var mediaId = $uploader.data('mediaId');
	      var _this = this;
	
	      select.init({
	        id: '#track-select',
	        optionsLimit: 4
	      });
	      select.on('valuechange', function (data) {
	        if (!data) {
	          $textTrackDisplay.html(Translator.trans('subtitle.no_subtitle_hint'));
	          return;
	        }
	        $.ajax({
	          url: data.url,
	          type: 'GET',
	          notSetHeader: true
	        }).done(_this.showSubtitleContent);
	      });
	      select.on('deleteoption', function (data) {
	        var url = '/media/' + mediaId + '/subtitle/' + data.id + '/delete';
	        $.post(url, function (data) {
	          if (data) {
	            (0, _notify2["default"])('success', Translator.trans('subtitle.delete_success_hint'));
	            $uploader.show();
	          }
	        });
	      });
	      select.on('optionlimit', function () {
	        $uploader.hide();
	      });
	      select.resetOptions(subtitleList);
	
	      this.select = select;
	    }
	  }, {
	    key: 'initUploader',
	    value: function initUploader() {
	      var select = this.select;
	
	      var videoNo = $uploader.data('mediaGlobalId');;
	      var mediaId = $uploader.data('mediaId');
	      var subtitleCreateUrl = $uploader.data('subtitleCreateUrl');
	
	      var uploader = new UploaderSDK({
	        initUrl: $uploader.data('initUrl'),
	        finishUrl: $uploader.data('finishUrl'),
	        id: 'uploader',
	        ui: 'simple',
	        multi: true,
	        accept: {
	          extensions: ['srt'],
	          mimeTypes: ['text/srt']
	        },
	        type: 'sub',
	        process: {
	          videoNo: videoNo
	        }
	      });
	
	      uploader.on('error', function (err) {
	        if (err.error === 'Q_TYPE_DENIED') {
	          (0, _notify2["default"])('danger', Translator.trans('subtitle.upload_srt_hint'));
	        }
	      });
	
	      uploader.on('file.finish', function (file) {
	        $.post(subtitleCreateUrl, {
	          "name": file.name,
	          "subtitleId": file.id,
	          "mediaId": mediaId
	        }).success(function (data) {
	          if (!data) {
	            return;
	          }
	          select.addOption(data);
	          (0, _notify2["default"])('success', Translator.trans('subtitle.upload_success_hint'));
	
	          setTimeout(function () {
	            var url = '/media/' + mediaId + '/subtitles';
	            $.get(url).done(function (data) {
	              if (data.subtitles) {
	                select.resetOptions(data.subtitles);
	              }
	            });
	          }, 5000);
	        }).error(function (data) {
	          (0, _notify2["default"])('danger', Translator.trans(data.responseJSON.error.message));
	        });
	      });
	    }
	  }, {
	    key: 'showSubtitleContent',
	    value: function showSubtitleContent(data) {
	      var captions = new _subtitle2["default"]();
	
	      try {
	        captions.parse(data);
	      } catch (e) {
	        (0, _notify2["default"])('danger', Translator.trans('subtitle.parse_error_hint'));
	        $textTrackDisplay.html(Translator.trans('subtitle.parse_error_hint'));
	        return;
	      }
	
	      var subtitleArray = captions.getSubtitles({
	        duration: true,
	        timeFormat: 'ms'
	      });
	
	      var html = '';
	      subtitleArray.map(function (cue) {
	        html += '<p>' + cue.text + '</p>';
	      });
	
	      $textTrackDisplay.html(html);
	
	      var $subtitleDom = $textTrackDisplay.find('p');
	
	      _messenger2["default"].on('timechange', function (data) {
	        setTimeout(function () {
	          var last = subtitleArray.find(function (cue, index) {
	            if (cue.start / 1000 > data.currentTime) {
	              return cue;
	            }
	          });
	
	          $subtitleDom.removeClass('active');
	          if (!last) {
	            return;
	          }
	          if (last.index > 1 && subtitleArray[last.index - 2].end > parseFloat(data.currentTime) * 1000) {
	            $subtitleDom.eq(last.index - 2).addClass('active');
	          }
	        }, 0);
	      });
	    }
	  }]);
	
	  return Manage;
	}();
	
	exports["default"] = Manage;

/***/ }),

/***/ "609d911a24b2b709511a":
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

/***/ "90c40b9c35e074e923b0":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	var convertStatus = function convertStatus(status) {
	  var statusMap = {
	    waiting: Translator.trans('subtitle.status.waiting'),
	    doing: Translator.trans('subtitle.status.doing'),
	    success: Translator.trans('subtitle.status.success'),
	    error: Translator.trans('subtitle.status.error'),
	    none: Translator.trans('subtitle.status.waiting')
	  };
	  return statusMap[status];
	};
	
	var Select = {
	  init: function init(options) {
	    this.$el = $(options.id);
	    this.options = [];
	    this.optionsLimit = options.optionsLimit || false;
	    this.eventManager = {};
	    this.initParent();
	    this.initEvent();
	  },
	  initParent: function initParent() {
	    var _self = this;
	    var $documentFragment = $(document.createDocumentFragment());
	    $documentFragment.append(this.templete());
	    this.$el.append($documentFragment);
	    this.$parentDom = $('.track-select-parent');
	    this.$list = $('.track-selcet-list');
	    this.$dataShow = this.$parentDom.find('.data-show');
	    this.$open = this.$parentDom.find('.track-selcet-open-arrow');
	    this.$close = this.$parentDom.find('.track-selcet-close-arrow');
	    this.$showBox = this.$parentDom.find('.track-select-show');
	  },
	  initEvent: function initEvent() {
	    var _self = this;
	    this.$parentDom.delegate('.track-selcet-open-arrow', 'click', this.handleOpen.bind(this)).delegate('.track-selcet-close-arrow', 'click', this.handleClose.bind(this)).delegate('.delete', 'click', this.handleDelete.bind(this)).delegate('.select-item', 'click', function () {
	      $(this).siblings().removeClass('active');
	      $(this).addClass('active');
	      var name = $(this).find('.value').html();
	      var url = $(this).find('.value').attr('url');
	      _self.setValue({ name: name, url: url });
	      _self.handleClose();
	    });
	    this.$showBox.on('click', this.toggle.bind(this));
	    this.on('valuechange', function () {
	      this.$dataShow.html(this.getValue().name);
	      this.$dataShow.attr('title', this.getValue().name);
	    });
	    this.on('listchange', function () {
	      if (this.optionsLimit && this.options.length >= this.optionsLimit) {
	        this.trigger('optionlimit');
	      }
	      this.$list.html(this.getOptionsStr());
	      this.setValue(this.getDefaultOption());
	    });
	    this.on('optionempty', this.handleOptionEmpty.bind(this));
	  },
	  templete: function templete() {
	    return '<div class="track-select-parent">\n              <div class="track-select-show">\n                <div class="data-show" title="' + this.getDefaultOption().name + '"></div>\n                <span class="track-selcet-open-arrow">\n                  <i class="es-icon es-icon-keyboardarrowdown"></i>\n                </span>\n                <span class="track-selcet-close-arrow" style="display:none;">\n                  <i class="es-icon es-icon-keyboardarrowup"></i>\n                </span>\n              </div>\n              <ul class="track-selcet-list" style="display:none;">\n                ' + this.getOptionsStr() + '\n              </ul>\n            </div>';
	  },
	  getDefaultOption: function getDefaultOption() {
	    if (this.options.length) {
	      return this.options[0];
	    } else {
	      this.open ? this.handleClose() : '';
	      return false;
	    }
	  },
	  getOptionsStr: function getOptionsStr() {
	    var _self = this;
	    if (!this.options.length) {
	      this.trigger('optionempty');
	    }
	    var optionsStr = '';
	    this.options.map(function (option, index) {
	      optionsStr += '<li class="select-item">\n                        <div class="value" title="' + option.name + '" url="' + option.url + '">\n                          ' + option.name + '\n                        </div>\n                        <span class="convertStatus convert-' + option.convertStatus + '">' + convertStatus(option.convertStatus) + '</span>\n                        <i class="es-icon es-icon-close01 delete" data-index="' + index + '"></i>\n                      </li>';
	    });
	    return optionsStr;
	  },
	  setValue: function setValue(value) {
	    if (!value) {
	      this.$dataShow.html(Translator.trans('subtitle.no_subtitle_hint'));
	      this.trigger('valuechange', false);
	      return;
	    }
	    this.value = value;
	    this.trigger('valuechange', this.value);
	  },
	  getValue: function getValue() {
	    return this.value || { name: Translator.trans('subtitle.no_subtitle_hint') };
	  },
	  toggle: function toggle() {
	    this.open ? this.handleClose() : this.handleOpen();
	  },
	  handleOpen: function handleOpen() {
	    if (!this.options.length) return;
	    this.open = true;
	    this.$open.hide();
	    this.$close.show();
	    this.$showBox.addClass('active');
	    this.$list.slideDown(200);
	  },
	  handleClose: function handleClose() {
	    this.open = false;
	    this.$close.hide();
	    this.$open.show();
	    this.$showBox.removeClass('active');
	    this.$list.slideUp(200);
	  },
	  handleDelete: function handleDelete(e) {
	    var el = e.target;
	    $(el).parent().remove();
	    this.trigger('deleteoption', this.options[$(el).data('index')]);
	    this.options.splice($(el).data('index'), 1);
	    this.trigger('listchange', this.options);
	    e.stopPropagation();
	  },
	  handleOptionEmpty: function handleOptionEmpty() {
	    this.value = '';
	    this.trigger('valuechange', false);
	  },
	  on: function on(event, fn) {
	    if (!this.eventManager[event]) {
	      this.eventManager[event] = [fn.bind(this)];
	    } else {
	      this.eventManager[event].push(fn.bind(this));
	    }
	  },
	  trigger: function trigger(event, data) {
	    if (this.eventManager[event]) {
	      this.eventManager[event].map(function (fn) {
	        fn(data);
	      });
	    }
	  },
	  resetOptions: function resetOptions(optionsArray) {
	    this.options = optionsArray;
	    this.trigger('listchange', this.options);
	  },
	  addOption: function addOption(option) {
	    if (!option.convertStatus) {
	      option.convertStatus = 'waiting';
	    }
	    this.options.push(option);
	    this.trigger('listchange');
	  }
	};
	
	exports["default"] = Select;

/***/ }),

/***/ "b26ed6014df6ee9a0332":
/***/ (function(module, exports) {

	'use strict';
	
	var videoHtml = $('#lesson-dashboard');
	var playerUrl = videoHtml.data("media-player-url");
	var html = '<iframe src=\'' + playerUrl + '\' name=\'viewerIframe\' id=\'viewerIframe\' width=\'100%\'allowfullscreen webkitallowfullscreen height=\'100%\' style=\'border:0px\'></iframe>';
	$("#lesson-video-content").html(html);

/***/ }),

/***/ "27d19b4547a9c8dcdebe":
/***/ (function(module, exports, __webpack_require__) {

	/**
	 * Module dependencies.
	 */
	
	var toMS = __webpack_require__("7ea730999b7f9bc57768")
	var toSrtTime = __webpack_require__("6d5fc844ee5e8f1afd1c")
	
	/**
	 * Add a new caption into the array of subtitles.
	 *
	 * @param {Array} subtitles
	 * @param {Object} caption
	 * @return {Array} subtitles
	 */
	
	module.exports = function add (subtitles, caption) {
	  if (!caption.start || !caption.end || !caption.text) {
	    throw new Error('Invalid caption data')
	  }
	
	  for (var prop in caption) {
	    if (!caption.hasOwnProperty(prop) || prop === 'text') {
	      continue
	    }
	
	    if (prop === 'start' || prop === 'end') {
	      if (/^(\d{2}):(\d{2}):(\d{2}),(\d{3})$/.test(caption[prop])) {
	        continue
	      }
	      if (/^\d+$/.test(caption[prop])) {
	        caption[prop] = toSrtTime(caption[prop])
	      } else {
	        throw new Error('Invalid caption time format')
	      }
	    }
	  }
	
	  subtitles.push({
	    index: subtitles.length + 1,
	    start: caption.start,
	    end: caption.end,
	    duration: toMS(caption.end) - toMS(caption.start),
	    text: caption.text
	  })
	
	  return subtitles
	}


/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _manage = __webpack_require__("4e9151fb4a048110077b");
	
	var _manage2 = _interopRequireDefault(_manage);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	new _manage2["default"]();

/***/ }),

/***/ "62dd28380e65e28b6966":
/***/ (function(module, exports, __webpack_require__) {

	/**
	 * Module dependencies.
	 */
	
	var toMS = __webpack_require__("7ea730999b7f9bc57768")
	
	/**
	 * Parse SRT string.
	 * @param {String} srt
	 * @return {Array} subtitles
	 */
	
	module.exports = function parse (srt) {
	  var subs = []
	  var index
	  var time
	  var text
	  var start
	  var end
	
	  if (!srt) {
	    throw new Error('No SRT to parse')
	  }
	
	  srt = srt.trim()
	  srt += '\n'
	  srt = srt
	    .replace(/\r\n/g, '\n')
	    .replace(/\n{3,}/g, '\n\n')
	    .split('\n')
	
	  srt.forEach(function (line) {
	    line = line.toString()
	
	    // if we don't have an index, so we should expect an index
	    if (!index) {
	      if (/^\d+$/.test(line)) {
	        index = parseInt(line)
	        return
	      }
	    }
	
	    // now we have to check for the time
	    if (!time) {
	      var match = line.match(/^(\d{2}:\d{2}:\d{2},\d{3}) --> (\d{2}:\d{2}:\d{2},\d{3})$/)
	      if (match) {
	        start = match[1]
	        end = match[2]
	        time = true
	        return
	      }
	    }
	
	    // now we get all the strings until we get an empty line
	    if (line.trim() === '') {
	      subs.push({
	        index: index,
	        start: start,
	        end: end,
	        duration: toMS(end) - toMS(start),
	        text: text || ''
	      })
	      index = time = start = end = text = null
	    } else {
	      if (!text) {
	        text = line
	      } else {
	        text += '\n' + line
	      }
	    }
	  })
	
	  return subs
	}


/***/ }),

/***/ "a354ef45a5643f3faa0a":
/***/ (function(module, exports, __webpack_require__) {

	/**
	 * Module dependencies.
	 */
	
	var toMS = __webpack_require__("7ea730999b7f9bc57768")
	var toSrtTime = __webpack_require__("6d5fc844ee5e8f1afd1c")
	
	/**
	 * Resync the subtitles.
	 * @param {Array} subtitles
	 * @param {Number} time
	 */
	
	module.exports = function resync (subtitles, time) {
	  if (!/(-|\+)?\d+/.test(time.toString())) {
	    throw new Error('Invalid time: ' + time + '.Expected a valid integer')
	  }
	
	  time = parseInt(time, 10)
	
	  return subtitles.map(function (caption) {
	    var start = toMS(caption.start)
	    var end = toMS(caption.end)
	
	    start = start + time
	    end = end + time
	
	    caption.start = start < 0
	      ? toSrtTime(0)
	      : toSrtTime(start)
	
	    caption.end = end < 0
	      ? toSrtTime(0)
	      : toSrtTime(end)
	
	    return caption
	  })
	}


/***/ }),

/***/ "4fbc8f4e49952771dc85":
/***/ (function(module, exports) {

	/**
	 * Stringify the given array of subtitles.
	 * @param {Array} subtitles
	 * @return {String} srt
	 */
	
	module.exports = function stringify (subtitles) {
	  var buffer = ''
	
	  subtitles.forEach(function (caption, index) {
	    if (index > 0) {
	      buffer += '\n'
	    }
	    buffer += caption.index
	    buffer += '\n'
	    buffer += caption.start + ' --> ' + caption.end
	    buffer += '\n'
	    buffer += caption.text
	    buffer += '\n'
	  })
	
	  return buffer
	}


/***/ }),

/***/ "7ea730999b7f9bc57768":
/***/ (function(module, exports) {

	/**
	 * Return the given SRT timestamp as milleseconds.
	 * @param {String} srtTime
	 * @returns {Number} milliseconds
	 */
	
	module.exports = function toMS (srtTime) {
	  var match = srtTime.match(/^(\d{2}):(\d{2}):(\d{2}),(\d{3})$/)
	
	  if (!match) {
	    throw new Error('Invalid SRT time format')
	  }
	
	  var hours = parseInt(match[1], 10)
	  var minutes = parseInt(match[2], 10)
	  var seconds = parseInt(match[3], 10)
	  var milliseconds = parseInt(match[4], 10)
	
	  hours *= 3600000
	  minutes *= 60000
	  seconds *= 1000
	
	  return hours + minutes + seconds + milliseconds
	}


/***/ }),

/***/ "6d5fc844ee5e8f1afd1c":
/***/ (function(module, exports) {

	/**
	 * Return the given milliseconds as SRT timestamp.
	 * @param {Integer} milliseconds
	 * @return {String} srtTimestamp
	 */
	
	module.exports = function toSrtTime (milliseconds) {
	  if (!/^\d+$/.test(milliseconds.toString())) {
	    throw new Error('Time should be an Integer value in milliseconds')
	  }
	
	  milliseconds = parseInt(milliseconds)
	
	  var date = new Date(0, 0, 0, 0, 0, 0, milliseconds)
	
	  var hours = date.getHours() < 10
	    ? '0' + date.getHours()
	    : date.getHours()
	
	  var minutes = date.getMinutes() < 10
	    ? '0' + date.getMinutes()
	    : date.getMinutes()
	
	  var seconds = date.getSeconds() < 10
	    ? '0' + date.getSeconds()
	    : date.getSeconds()
	
	  var ms = milliseconds - ((hours * 3600000) + (minutes * 60000) + (seconds * 1000))
	
	  if (ms < 100 && ms >= 10) {
	    ms = '0' + ms
	  } else if (ms < 10) {
	    ms = '00' + ms
	  }
	
	  var srtTime = hours + ':' + minutes + ':' + seconds + ',' + ms
	
	  return srtTime
	}


/***/ }),

/***/ "d5971171c2b26114cfc1":
/***/ (function(module, exports, __webpack_require__) {

	'use strict'
	
	/*!
	 * Subtitle.js
	 * Parse and manipulate SRT (SubRip)
	 * https://github.com/gsantiago/subtitle.js
	 *
	 * @version 0.1.5
	 * @author Guilherme Santiago
	*/
	
	/**
	 * Module dependencies.
	 */
	
	var toMS = __webpack_require__("7ea730999b7f9bc57768")
	var toSrtTime = __webpack_require__("6d5fc844ee5e8f1afd1c")
	var parse = __webpack_require__("62dd28380e65e28b6966")
	var stringify = __webpack_require__("4fbc8f4e49952771dc85")
	var resync = __webpack_require__("a354ef45a5643f3faa0a")
	var getSubtitles = __webpack_require__("558e05aa09312b06bb5c")
	var add = __webpack_require__("27d19b4547a9c8dcdebe")
	
	/**
	 * Export `Subtitle`.
	 */
	
	module.exports = Subtitle
	
	/**
	 * Subtitle constructor.
	 * @constructor
	 * @param {String} srt
	 */
	
	function Subtitle (srt) {
	  if (!(this instanceof Subtitle)) return new Subtitle(srt)
	
	  this._subtitles = []
	
	  if (srt) {
	    this.parse(srt)
	  }
	}
	
	/**
	 * Alias for `Subtitle.prototype`.
	 */
	
	var fn = Subtitle.prototype
	
	/**
	 * Parse the given SRT.
	 *
	 * @method
	 * @param {String} srt
	 */
	
	fn.parse = function _parse (srt) {
	  this._subtitles = parse(srt)
	}
	
	/**
	 * Add a caption.
	 * You have to pass an object with the following data:
	 * start - The start timestamp
	 * end - The end timestamp
	 * text - The caption text
	 *
	 * The timestamps support two patterns:
	 * The SRT pattern: '00:00:24,400'
	 * Or a positive integer representing milliseconds
	 *
	 * @public
	 * @param {Object} Caption data
	 */
	
	fn.add = function _add (caption) {
	  add(this._subtitles, caption)
	  return this
	}
	
	/**
	 * Return the subtitles.
	 *
	 * @param {Object} Options
	 * @returns {Array} Subtitles
	 */
	
	fn.getSubtitles = function _getSubtitles (options) {
	  return getSubtitles(this._subtitles, options)
	}
	
	/**
	 * Stringify the SRT.
	 *
	 * @returns {String} srt
	 */
	
	fn.stringify = function _stringify () {
	  return stringify(this._subtitles)
	}
	
	/**
	 * Resync the captions.
	 *
	 * @param {Integer} Time in milleseconds
	 */
	
	fn.resync = function _resync (time) {
	  this._subtitles = resync(this._subtitles, time)
	  return this
	}
	
	/**
	 * Convert the SRT time format to milliseconds
	 *
	 * @static
	 * @param {String} SRT time format
	 */
	
	Subtitle.toMS = toMS
	
	/**
	 * Convert milliseconds to SRT time format
	 *
	 * @static
	 * @param {Integer} Milleseconds
	 */
	
	Subtitle.toSrtTime = toSrtTime


/***/ }),

/***/ "558e05aa09312b06bb5c":
/***/ (function(module, exports, __webpack_require__) {

	/**
	 * Module dependencies.
	 */
	
	var extend = __webpack_require__("631b083c4a39c1f7f104")
	var toMS = __webpack_require__("7ea730999b7f9bc57768")
	
	/**
	 * Default options.
	 */
	
	var defaults = {
	  timeFormat: 'srt',
	  duration: false
	}
	
	/**
	 * Transform the given array of subtitles.
	 * @param {Array} subtitles
	 * @param {Object} options
	 * @return {Array} subtitles
	 */
	
	module.exports = function getSubtitles (subtitles, options) {
	  options = extend(defaults, options)
	
	  if (options.timeFormat === 'ms') {
	    subtitles = subtitles.map(function (caption) {
	      caption.start = toMS(caption.start)
	      caption.end = toMS(caption.end)
	      return caption
	    })
	  }
	
	  if (!options.duration) {
	    subtitles = subtitles.map(function (caption) {
	      delete caption.duration
	      return caption
	    })
	  }
	
	  return subtitles
	}


/***/ })

});
//# sourceMappingURL=index.js.map