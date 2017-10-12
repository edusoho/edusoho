webpackJsonp(["app/js/course-manage/question-marker/modal/index"],[
/* 0 */
/***/ (function(module, exports) {

	'use strict';
	
	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();
	
	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }
	
	var QuestionMarkerStats = function () {
	  function QuestionMarkerStats() {
	    _classCallCheck(this, QuestionMarkerStats);
	
	    this.init();
	  }
	
	  _createClass(QuestionMarkerStats, [{
	    key: 'init',
	    value: function init() {
	      var myChart = echarts.init(document.getElementById('main'));
	      var type = $('.popup-topic').data('type');
	      if (type.indexOf('single_choice') >= 0) {
	        myChart.setOption(this.getPeiOptions());
	      } else {
	        myChart.setOption(this.getBarOptions(type));
	      }
	
	      $('[data-toggle="tab"]').on('click', function () {
	        $(this).addClass('btn-primary').removeClass('btn-default').siblings().removeClass('btn-primary').addClass('btn-default');
	      });
	    }
	  }, {
	    key: 'getPeiOptions',
	    value: function getPeiOptions() {
	      var stats = this.getStats();
	      var legendData = [],
	          data = [];
	
	      $.each(stats, function (index, stat) {
	        var key = String.fromCharCode(index + 65);
	        legendData.push(key);
	
	        data.push({
	          'name': key,
	          'value': stat['pct']
	        });
	      });
	
	      return {
	        tooltip: {
	          trigger: 'item',
	          formatter: "{a} <br/>{b} : {c} ({d}%)"
	        },
	        color: ['#4653BE', '#72CC59', '#4DA8E6', '#F8AB60'],
	        legend: {
	          orient: 'vertical',
	          right: 'right',
	          top: 'center',
	          itemWidth: 8,
	          itemHeight: 8,
	          data: legendData
	        },
	        series: [{
	          name: '',
	          type: 'pie',
	          radius: '55%',
	          center: ['50%', '60%'],
	          labelLine: {
	            normal: {
	              show: false
	            }
	          },
	          label: {
	            normal: {
	              show: false,
	              position: 'center'
	            }
	
	          },
	          data: data
	        }]
	      };
	    }
	  }, {
	    key: 'getBarOptions',
	    value: function getBarOptions(questionType) {
	      var stats = this.getStats();
	
	      var xData = [],
	          seriesData = [],
	          seriesName = '选择率';
	
	      $.each(stats, function (index, stat) {
	
	        if (questionType === 'fill') {
	          xData.push('填空' + (index + 1));
	          seriesName = '正确率';
	        } else {
	          var key = String.fromCharCode(index + 65);
	          xData.push(key);
	        }
	
	        seriesData.push(stat['pct']);
	      });
	
	      return {
	        color: ['#5586db'],
	        tooltip: {
	          formatter: '{a}<br />{b}：{c}%'
	        },
	        xAxis: {
	          data: xData
	        },
	        yAxis: {
	          max: 100
	        },
	        series: [{
	          name: seriesName,
	          type: 'bar',
	          data: seriesData
	        }]
	      };
	    }
	  }, {
	    key: 'getStats',
	    value: function getStats() {
	      return $('#figure').data('stats');
	    }
	  }]);
	
	  return QuestionMarkerStats;
	}();
	
	new QuestionMarkerStats();

/***/ })
]);
//# sourceMappingURL=index.js.map