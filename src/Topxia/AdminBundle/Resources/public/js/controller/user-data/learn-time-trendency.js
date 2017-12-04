define(function (require, exports, module) {
  var OverviewDateRangePicker = require('./date-range-picker');
  var Widget = require('widget');
  require('echarts');

  var LearnTimeTrendency = Widget.extend({
    setup: function() {
      this.$container = $('.js-learn-data-trendency');
      this.dateArr = [];
      this.learnTime = [];
      this.init();
    },

    init: function() {
      var self = this;
      console.log('222');
      this.dateRangePicker = new OverviewDateRangePicker('.js-user-data-chart');
      this.learnTimeTrendencyChart = echarts.init(document.getElementById('js-learn-data-trendency-chart'));
      this.dateArr = ["2017-11-01", "2017-11-26", "2017-11-27", "2017-11-28", "2017-11-29", "2017-11-30", "2017-12-01"];
      this.learnTime = [10, 12, 21, 54, 260, 830, 710];
      this.dateRangePicker.on('date-picked', function(data) {
        console.log('3333');
        console.log(data.startDate);
        console.log(data.endDate);
        var dateArr = [];
        var learnTime = [];
        for (var i = 0; i < 7; i++) {
          dateArr.push(data.startDate + i);
          learnTime.push(i);
        }
        // 先这么测试着
        this.dateArr = dateArr;
        this.learnTime = learnTime;
        console.log(this.dateArr);
        self.data(data.startDate, data.endDate, this.learnTime, this.dateArr);
      });
      this.data(this.dateRangePicker.getStartDate(), this.dateRangePicker.getEndDate(), this.learnTime, this.dateArr);
    },

    data: function(startDate, endDate, time, date) {
      this.learnTimeTrendencyChart.showLoading();
      var self = this;
      self.show(time, date);
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
            name: '学习时长',
            type: 'value',
          },
          series: [
            {
              name: '总时长',
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