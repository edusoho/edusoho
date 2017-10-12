webpackJsonp(["app/js/classroom/index"],{

/***/ "584608d4ce1895020bac":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };
	
	var buyBtn = exports.buyBtn = function buyBtn($element) {
	  $element.on('click', function (event) {
	    $.post($(event.currentTarget).data('url'), function (resp) {
	      if ((typeof resp === 'undefined' ? 'undefined' : _typeof(resp)) === 'object') {
	        window.location.href = resp.url;
	      } else {
	        $('#modal').modal('show').html(resp);
	      }
	    });
	  });
	};

/***/ }),

/***/ "421cf737aed7dbab3295":
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _notify = __webpack_require__("b334fd7e4c5a19234db2");
	
	var _notify2 = _interopRequireDefault(_notify);
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	$(".cancel-refund").on('click', function () {
	  if (!confirm(Translator.trans('classroom.cancel_refund_hint'))) {
	    return false;
	  }
	
	  $.post($(this).data('url'), function () {
	    (0, _notify2["default"])('success', Translator.trans('退款申请已取消成功！'));
	    window.location.reload();
	  });
	});

/***/ }),

/***/ "bbe1f1e10924ccc8bdb1":
/***/ (function(module, exports) {

	"use strict";
	
	$("body").on("click", ".es-qrcode", function () {
	  var $this = $(this);
	  if ($this.hasClass('open')) {
	    $this.removeClass('open');
	  } else {
	    $.ajax({
	      type: "post",
	      url: $this.data("url"),
	      dataType: "json",
	      success: function success(data) {
	        $this.find(".qrcode-popover img").attr("src", data.img);
	        $this.addClass('open');
	      }
	    });
	  }
	});

/***/ }),

/***/ "6a20cd61187c3c5ca840":
/***/ (function(module, exports) {

	'use strict';
	
	Object.defineProperty(exports, "__esModule", {
	  value: true
	});
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var Sing = function () {
	  function Sing($element) {
	    _classCallCheck(this, Sing);
	
	    this.$element = $element;
	    this.selectedDate = null;
	    this.inited = false;
	    this.daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
	    this.signedRecordsUrl = null;
	    this.signUrl = null;
	    this.initEvent();
	    this.setup();
	  }
	
	  _createClass(Sing, [{
	    key: 'initEvent',
	    value: function initEvent() {
	      var _this = this;
	
	      this.$element.on('click', '[data-role=sign]', function () {
	        return _this.sign();
	      });
	      this.$element.on('mouseenter', '[data-role="signed"]', function () {
	        return _this.signedIn();
	      });
	      this.$element.on('mouseleave', '[data-role="signed"]', function () {
	        return _this.signedOut(event);
	      });
	      this.$element.on('mouseenter', '.sign_main', function () {
	        return _this.keep();
	      });
	      this.$element.on('mouseleave', '.sign_main', function () {
	        return _this.remove();
	      });
	      this.$element.on('click', '[data-role=previous]', function () {
	        return _this.previousMonth();
	      });
	      this.$element.on('click', '[data-role=next]', function () {
	        return _this.nextMonth();
	      });
	    }
	  }, {
	    key: 'setup',
	    value: function setup() {
	      this.selectedDate = this.$element.find('#title-month').data('time');
	      var signedRecordsUrl = this.$element.data('records');
	      var signUrl = this.$element.data('signurl');
	      this.signedRecordsUrl = signedRecordsUrl;
	      this.signUrl = signUrl;
	    }
	  }, {
	    key: 'keep',
	    value: function keep() {
	      this.$element.find('.sign_main').addClass('keepShow');
	    }
	  }, {
	    key: 'remove',
	    value: function remove() {
	      this.$element.find('.sign_main').removeClass('keepShow');
	      this.hiddenSignTable();
	    }
	  }, {
	    key: 'getDaysInMonth',
	    value: function getDaysInMonth(month, year) {
	      if (month == 1 && year % 4 == 0 && (year % 100 != 0 || year % 400 == 0)) {
	        return 29;
	      } else {
	        return this.daysInMonth[month];
	      }
	    }
	  }, {
	    key: 'getWeekByDate',
	    value: function getWeekByDate(year, month, day) {
	      return new Date(year + '/' + month + '/' + day).getDay();
	    }
	  }, {
	    key: 'sign',
	    value: function sign() {
	      var _this2 = this;
	
	      var today = new Date().getDate();
	      $.ajax({
	        url: this.signUrl,
	        dataType: 'json',
	        success: function success(data) {
	
	          $('#sign').html('<div  class="sign-area" data-role="signed" onclick="return false;" >' + '<a class="btn-signin after" >' + Translator.trans('classroom.member_signed') + '<br>' + Translator.trans('classroom.sign_keep_days', { 'keepDays': data.keepDays }) + '</a></div>');
	          _this2.showSignTable();
	          _this2.initTable(true);
	          _this2.$element.find('.d-' + today).addClass('signed_anime_day');
	          // window.location.reload();
	        },
	        error: function error(xhr) {}
	      });
	    }
	  }, {
	    key: 'signedIn',
	    value: function signedIn() {
	      if (!this.inited) {
	        this.initTable();
	      }
	      this.showSignTable();
	    }
	  }, {
	    key: 'signedOut',
	    value: function signedOut(e) {
	      var _this3 = this;
	
	      this.$element.find('.sign_main').removeClass('keepShow');
	      setTimeout(function () {
	        if (_this3.$element.find('.sign_main').hasClass('keepShow')) {
	          return;
	        } else {
	          _this3.hiddenSignTable();
	        }
	      }, 1000);
	    }
	  }, {
	    key: 'showSignTable',
	    value: function showSignTable() {
	      this.$element.find('.sign_main').addClass('keepShow');
	      this.$element.find('.sign_main').attr('style', 'display:block');
	    }
	  }, {
	    key: 'hiddenSignTable',
	    value: function hiddenSignTable() {
	      this.$element.find('.sign_main').removeClass('keepShow');
	      this.$element.find('.sign_main').attr('style', 'display:none');
	    }
	  }, {
	    key: 'initTable',
	    value: function initTable(signedToday) {
	      var _this4 = this;
	
	      var selectedDate = this.selectedDate;
	      selectedDate = selectedDate.split('/');
	      var year = parseInt(selectedDate[0]);
	      var month = parseInt(selectedDate[1]);
	      var days = this.getDaysInMonth(month - 1, year);
	      var $tbody = this.$element.find('tbody');
	      var newtr = "<tr><td class='t-1-0 '></td><td class='t-1-1 '></td><td class='t-1-2 '></td><td class='t-1-3 '></td><td class='t-1-4 '></td><td class='t-1-5 '></td><td class='t-1-6 '></td></tr>";
	
	      var url = this.signedRecordsUrl + '?startDay=' + year + '-' + month + '-1' + '&endDay=' + year + '-' + month + '-' + days;
	
	      $tbody.append(newtr);
	      var row = 1;
	      var today = new Date().getDate();
	      for (var day = 1; day <= days; day++) {
	        var week = this.getWeekByDate(year, month, day);
	        $tbody.find(".t-" + row + '-' + week).html(day);
	        $tbody.find(".t-" + row + '-' + week).addClass('d-' + day);
	
	        if (week == 6 && day != days) {
	          row++;
	          newtr = '<tr><td class="day t-' + row + '-0 "></td><td class="day t-' + row + '-1 "></td><td class="day t-' + row + '-2 "></td><td class="day t-' + row + '-3 "></td><td class="day t-' + row + '-4 "></td><td class="day t-' + row + '-5 "></td><td class="day t-' + row + '-6 "></td></tr>';
	          $tbody.append(newtr);
	        }
	      }
	
	      $.ajax({
	        url: url,
	        dataType: 'json',
	        async: true, //(默认: true) 默认设置下，所有请求均为异步请求。如果需要发送同步请求，请将此选项设置为 false。注意，同步请求将锁住浏览器，用户其它操作必须等待请求完成才可以执行。
	        success: function success(data) {
	          for (var i = 0; i < data.records.length; i++) {
	            var day = parseInt(data.records[i]['day']);
	            $tbody.find(".d-" + day).addClass('signed_day').attr('title', Translator.trans('classroom.sign_rank_hint', { 'time': data.records[i]['time'], 'rank': data.records[i]['rank'] }));
	          }
	          _this4.$element.find('.today-rank').html(data.todayRank);
	          _this4.$element.find('.signed-number').html(data.signedNum);
	          _this4.$element.find('.keep-days').html(data.keepDays);
	        }
	      });
	
	      this.inited = true;
	      if (signedToday) {
	        var $signbtn = this.$element.find('[data-role=sign]');
	        $signbtn.data('role', 'signed');
	        $signbtn.on('mouseenter', function () {
	          this.signedIn();
	        });
	        $signbtn.on('mouseleave', function () {
	          this.signedOut();
	        });
	        $signbtn.on('click', false);
	        $signbtn.addClass('sign-btn');
	        $signbtn.find('.sign-text').html(Translator.trans('classroom.member_signed'));
	      }
	    }
	  }, {
	    key: 'previousMonth',
	    value: function previousMonth() {
	      var currentDate = this.selectedDate;
	      currentDate = currentDate.split('/');
	      var currentYear = parseInt(currentDate[0]);
	      var currentMonth = parseInt(currentDate[1]);
	      var nextMonth = 0;
	      var nextYear = currentYear;
	      if (currentMonth == 1) {
	        nextMonth = 12;
	        nextYear = currentYear - 1;
	      } else {
	        nextMonth = currentMonth - 1;
	      }
	      nextMonth = nextMonth < 10 ? '0' + nextMonth : nextMonth;
	      this.selectedDate = nextYear + '/' + nextMonth;
	      this.$element.find('tbody').html('');
	      this.$element.find('[data-role=next]').removeClass('disabled-next');
	      this.$element.find('#title-month').html(nextYear + Translator.trans('site.date.year') + nextMonth + Translator.trans('site.date.month'));
	      this.initTable();
	    }
	  }, {
	    key: 'nextMonth',
	    value: function nextMonth() {
	      var currentDate = this.selectedDate;
	      currentDate = currentDate.split('/');
	      var currentYear = parseInt(currentDate[0]);
	      var currentMonth = parseInt(currentDate[1]);
	      var nextMonth = 0;
	      var nextYear = currentYear;
	      if (currentMonth == new Date().getMonth() + 1 && currentYear == new Date().getFullYear()) {
	        return;
	      } else if (currentMonth == 12) {
	        nextMonth = 1;
	        nextYear = currentYear + 1;
	      } else {
	        nextMonth = currentMonth + 1;
	      }
	      if (nextMonth == new Date().getMonth() + 1 && currentYear == new Date().getFullYear()) {
	        this.$element.find('[data-role=next]').addClass('disabled-next');
	      }
	      nextMonth = nextMonth < 10 ? '0' + nextMonth : nextMonth;
	      this.selectedDate = nextYear + '/' + nextMonth;
	      this.$element.find('tbody').html('');
	      this.$element.find('#title-month').html(nextYear + Translator.trans('site.date.year') + nextMonth + Translator.trans('site.date.month'));
	      this.initTable();
	    }
	  }]);
	
	  return Sing;
	}();
	
	exports["default"] = Sing;

/***/ }),

/***/ 0:
/***/ (function(module, exports, __webpack_require__) {

	'use strict';
	
	var _sign = __webpack_require__("6a20cd61187c3c5ca840");
	
	var _sign2 = _interopRequireDefault(_sign);
	
	__webpack_require__("bbe1f1e10924ccc8bdb1");
	
	__webpack_require__("421cf737aed7dbab3295");
	
	var _btnUtil = __webpack_require__("584608d4ce1895020bac");
	
	function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { "default": obj }; }
	
	(0, _btnUtil.buyBtn)($('.js-classroom-buy-btn'));
	
	if ($('#classroom-sign').length > 0) {
	  var userSign = new _sign2["default"]($('#classroom-sign'));
	}
	
	if ($('.icon-vip').length > 0) {
	  $(".icon-vip").popover({
	    trigger: 'manual',
	    placement: 'auto top',
	    html: 'true',
	    container: 'body',
	    animation: false
	  }).on("mouseenter", function () {
	    var _this = $(this);
	    _this.popover("show");
	  }).on("mouseleave", function () {
	    var _this = $(this);
	    setTimeout(function () {
	      if (!$(".popover:hover").length) {
	        _this.popover("hide");
	      }
	    }, 100);
	  });
	}

/***/ })

});
//# sourceMappingURL=index.js.map