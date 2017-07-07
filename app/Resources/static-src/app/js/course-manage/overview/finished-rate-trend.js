import CourseOverviewDateRangePicker from './date-range-picker';

export default class FinishedRateTrend {

  constructor() {

    this.$container = $('#finished-rate-trend');
    this.courseId = this.$container.data('courseId');
    this.dateArr = [];
    this.timestampArr = [];
    this.finishedRateArr = [];
    this.finishedNumArr = [];

    this.initChart();

    this.initDateRangePicker();

  }

  initChart() {
    this.chart = echarts.init(document.getElementById('finished-rate-chart'));
    this.chart.setOption(this.getOption());
  }

  initDateRangePicker() {
    let self = this;
    this.dateRangePicker = new CourseOverviewDateRangePicker('#finished-rate-trend');
    this.dateRangePicker.on('date-picked', function (data) {
      self.show(data.startDate, data.endDate);
    });

    this.show(this.dateRangePicker.getStartDate(), this.dateRangePicker.getEndDate());
  }

  show(startDate, endDate) {

    this.chart.showLoading();

    let self = this;
    $.ajax({
      type: "GET",
      beforeSend: function(request) {
        request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
        request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
      },
      data: {startDate: startDate, endDate: endDate},
      url: '/api/course/' + this.courseId + '/report/completion_rate_trend',
      success: function(resp) {

        let dateArr = [],
          finishedRateArr = [],
          finishedNumArr = [];
        for (let value of resp) {
          dateArr.push(value.date);
          finishedRateArr.push(Math.floor(value.finishedRate));
          finishedNumArr.push(value.finishedNum);
        }

        self.dateArr = dateArr;
        self.finishedRateArr = finishedRateArr;
        self.finishedNumArr = finishedNumArr;

        self.refreshChart();

      }
    });
  }

  refreshChart() {
    this.chart.setOption({
      xAxis: {
        data: this.dateArr
      },
      series: [
        {data: this.finishedNumArr},
        {data: this.finishedRateArr},
      ]
    });

    this.chart.hideLoading();
  }

  getOption() {

    function formatter(params) {
      let html = params[0].name + '</br>';
      for (let i = 0; i < params.length; i++) {
        let circle = '<span style="display:inline-block;margin-right:5px;'
          + 'border-radius:10px;width:9px;height:9px;background-color:' + params[i].color + '"></span>'
          + params[i].seriesName + ' ' + params[i].value + (i === 1 ? '%' : '') + '</br>';
        html += circle;
      };
      return html;
    }

    return  {
      tooltip: {
        trigger: 'axis',
        formatter: formatter,
        backgroundColor: '#ffffff',
        borderColor: '#f5f5f5',
        borderWidth: 1,
        textStyle: {
          color: '#9b9b9b'
        },
        padding: 15
      },
      legend: {
        data: [
          {name:Translator.trans('course_manage.course_dashboard.finish_num'), icon: 'circle', textStyle: {color:'#9b9b9b'}},
          {name:Translator.trans('course_manage.course_dashboard.finish_rate'), icon: 'circle', textStyle: {color:'#9b9b9b'}}
          ],
        right: '10%'
      },
      grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
      },
      xAxis: {
        type: 'category',
        boundaryGap: false,
        data: [],
        splitLine: {
          lineStyle: {
            color: '#f5f5f5'
          }
        },
        axisLine: {
          lineStyle: {
            color: '#f5f5f5'
          }
        },
        axisLabel: {
          textStyle: {
            color: '#9b9b9b'
          }
        },
        axisTick: {
          show: false
        }
      },
      yAxis: [
        {
          name: Translator.trans('course_manage.course_overview.person_unit'),
          type: 'value',
          minInterval: 1,
          boundaryGap: ['0%', '20%'],
          splitLine: {
            lineStyle: {
              color: '#f5f5f5'
            }
          },
          axisLine: {
            show: false
          },
          axisTick: {
            show: false
          },
          axisLabel: {
            textStyle: {
              color: '#9b9b9b'
            }
          }
        },
        {
          show: false,
          type: 'value',
          minInterval: 1,
          max: 100,
          axisLabel: {
            formatter: '{value}%'
          },
          min: 0
        }
      ],
      series: [
        {
          name:Translator.trans('course_manage.course_dashboard.finish_num'),
          type:'line',
          yAxisIndex: 0,
          showSymbol: false,
          smooth: true,
          itemStyle: {
            normal: {
              color: '#92D178'
            }
          },
          data: []
        },
        {
          name:Translator.trans('course_manage.course_dashboard.finish_rate'),
          type:'line',
          yAxisIndex: 1,
          showSymbol: false,
          smooth: true,
          itemStyle: {
            normal: {
              color: '#FECF7D'
            }
          },
          data: []
        }
      ],
    };

  }
}