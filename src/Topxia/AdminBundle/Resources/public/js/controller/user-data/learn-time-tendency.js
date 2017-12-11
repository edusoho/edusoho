define(function (require, exports, module) {
  var OverviewDateRangePicker = require('./date-range-picker');
  var Widget = require('widget');
  require('echarts');

  var LearnTimeTendency = Widget.extend({
    setup: function() {
      this.$container = $('.js-learn-data-tendency');
      this.dateArr = [];
      this.learnTime = [];
      this.totalTime = 0;
      this.learnTimeTendencyChart = null;
      this.dateRangePicker = new OverviewDateRangePicker();
      this.init();
    },

    init: function() {
      var self = this;
      this.learnTimeTendencyChart = echarts.init(document.getElementById('learn-data-tendency-chart'));
      this.showData({startDate: this.dateRangePicker.getStartDate(),endDate:this.dateRangePicker.getEndDate()});
      self.dateRangePicker.on('date-picked', function(data) {
        self.showData(data);
      });
    },

    showData: function(data) {
      this.learnTimeTendencyChart.showLoading('default', { maskColor: '#fcfcfc' });
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
          totalTime = (Math.floor(totalTime/60) + Translator.trans('site.date.hour') + (totalTime%60) + Translator.trans('site.date.minute') );
          self.totalTime = totalTime;
          self.show(self.learnTime, self.dateArr);
        }
      });
    },

    show: function(time, data) {
      var option = {
          title: {
            text: Translator.trans('admin.user.statistics.data.learn_total_time') +': ' + this.totalTime,
            x: "center",
            y: "6.5%",
            textStyle: {
              fontSize: 14,
              fontWeight: '500',
            },
          },
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
            data: data
          },
          yAxis: {
            name: Translator.trans('admin.user.statistics.data.learn_time'),
            type: 'value',
            min: 0,
            splitNumber: 1,
          },
          series: [
            {
              name: Translator.trans('admin.user.statistics.data.time'),
              type: 'line',
              smooth: true,
              itemStyle: {
                normal: {
                  areaStyle: {
                    type: 'default'
                  }
                }
              },
              data: time
            }
          ],
          color: ['#46C37B', '#428BCA']
      };
      this.learnTimeTendencyChart.hideLoading();
      this.learnTimeTendencyChart.setOption(option);
    }
  })

  module.exports = LearnTimeTendency;
})