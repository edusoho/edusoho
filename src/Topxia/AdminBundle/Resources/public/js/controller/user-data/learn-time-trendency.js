define(function (require, exports, module) {
  var OverviewDateRangePicker = require('./date-range-picker');
  var Widget = require('widget');
  require('echarts');

  var LearnTimeTrendency = Widget.extend({
    setup: function() {
      this.$container = $('.js-learn-data-trendency');
      this.dateArr = [];
      this.learnTime = [];
      this.totalTime = 0;
      this.dateRangePicker = new OverviewDateRangePicker();
      this.init();
    },

    init: function() {
      var self = this;
      this.learnTimeTrendencyChart = echarts.init(document.getElementById('js-learn-data-trendency-chart'));
      this.showData({startDate: this.dateRangePicker.getStartDate(),endDate:this.dateRangePicker.getEndDate()});
      self.dateRangePicker.on('date-picked', function(data) {
        self.showData(data);
      });
    },

    showData: function(data) {
      this.learnTimeTrendencyChart.showLoading();
      var self = this;
      $.ajax({
        type: "GET",
        data: {startTime: data.startDate, endTime: data.endDate},
        url: self.$container.data('url'),
        success: function(resp) {

          var dateArr = [],
            totalTime = 0,
            learnTime = [];
          for (var value of resp) {
            dateArr.push(value.date);
            learnTime.push(parseInt(value.learnedTime/60));
            totalTime += parseInt(value.learnedTime/60);
          }
          self.dateArr = dateArr;
          self.learnTime = learnTime;
          self.totalTime = totalTime;
          self.show(self.learnTime, self.dateArr);
        }
      });
    },

    show: function(time, data) {
      var option = {
          tooltip: {
            trigger: 'axis'
          },
          grid: {
            left: '3%',
            right: '6%',
            bottom: '3%',
            containLabel: true
          },
          toolbox: {
            feature: {
              saveAsImage: {}
            }
          },
          xAxis: {
            type: 'category',
            boundaryGap: false,
            // 日期
            data: data
          },
          yAxis: {
            name: '学习时长/分钟',
            type: 'value',
            min: 0,
            splitNumber: 1,
          },
          series: [
            {
              name: '时长',
              type: 'line',
              smooth: true,
              itemStyle: {
                normal: {
                  areaStyle: {
                    type: 'default'
                  }
                }
              },
              // 学习时间
              data: time
            }
          ],
          color: ['#46C37B', '#428BCA']
      };
      this.learnTimeTrendencyChart.hideLoading();
      this.learnTimeTrendencyChart.setOption(option);
    }
  })

  module.exports = LearnTimeTrendency;
})