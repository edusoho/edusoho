import ClassroomStatisticsDateRangePicker from './date-range-picker';

export default class ClassroomStudentTrend {
  constructor() {
    this.$container = $('#js-student-trendency');
    this.classroomId = this.$container.data('classroomId');
    this.dateArr = [];
    this.timestampArr = [];
    this.studentsIncreaseArr = [];
    this.tryViewsIncreaseArr = [];

    this.init();
  }

  init() {
    let self = this;
    this.dateRangePicker = new ClassroomStatisticsDateRangePicker('#js-student-trendency');
    this.studentTrendencyChart = echarts.init(document.getElementById('js-student-trendency-chart'));
    this.dateRangePicker.on('date-picked', function (data) {
      self.data(data.startDate, data.endDate);
    });
    this.data(this.dateRangePicker.getStartDate(), this.dateRangePicker.getEndDate());
  }

  data(startDate,endDate) {
    this.studentTrendencyChart.showLoading();
    let self = this;
    $.ajax({
      type: 'GET',
      beforeSend: function(request) {
        request.setRequestHeader('Accept', 'application/vnd.edusoho.v2+json');
        request.setRequestHeader('X-CSRF-Token', $('meta[name=csrf-token]').attr('content'));
      },
      data: {startDate: startDate, endDate: endDate},
      url: '/api/classroom/' + this.classroomId + '/report/student_trend',
      success: function(resp) {
        let dateArr = [],
          studentsIncreaseArr = [],
          auditorsIncreaseArr = [];
        for (let value of resp) {
          dateArr.push(value.date);
          studentsIncreaseArr.push(value.studentIncrease);
          auditorsIncreaseArr.push(value.auditorIncrease);
        }

        self.dateArr = dateArr;
        self.studentsIncreaseArr = studentsIncreaseArr;
        self.auditorsIncreaseArr = auditorsIncreaseArr;

        self.show();
      }
    });

  }

  show() {
    function formatter(params) {
      let html = params[0].name + '</br>';
      for (let i = 0; i < params.length; i++) {
        let circle = '<span style="display:inline-block;margin-right:5px;'
          + 'border-radius:10px;width:9px;height:9px;background-color:' + params[i].color + '"></span>'
          + params[i].seriesName + ' ' + params[i].value  + '</br>';
        html += circle;
      }
      return html;
    }

    let option = {
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
          {name:Translator.trans('classroom.manage.data_statistics.echarts.student_increase_num'), icon: 'circle', textStyle: {color:'#9b9b9b'}},
          {name:Translator.trans('classroom.manage.data_statistics.echarts.auditor_increase_num'), icon: 'circle', textStyle: {color:'#9b9b9b'}}
        ],
        itemWidth: 8,
        itemHeight: 8,
        right: '0'
      },
      grid: {
        left: '10',
        right: '15',
        top: '15%',
        bottom: '0',
        containLabel: true
      },
      xAxis: {
        type: 'category',
        boundaryGap: false,
        data: this.dateArr,
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
            color: '#9b9b9b',
          },
          margin: 15,
        },
        axisTick: {
          show: false
        },
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
            },
            margin: 15,
          },
          nameGap: 20,
        }
      ],
      series: [
        {
          name: Translator.trans('classroom.manage.data_statistics.echarts.student_increase_num'),
          type: 'line',
          showSymbol: false,
          smooth: true,
          itemStyle: {
            normal: {
              color: '#FD7C82'
            }
          },
          data: this.studentsIncreaseArr
        }
        ,
        {
          name:Translator.trans('classroom.manage.data_statistics.echarts.auditor_increase_num'),
          type:'line',
          showSymbol: false,
          smooth: true,
          itemStyle: {
            normal: {
              color: '#6A94FD'
            }
          },
          data:this.auditorsIncreaseArr
        }
      ],
    };
    this.studentTrendencyChart.hideLoading();
    console.log(option);
    this.studentTrendencyChart.setOption(option);
  }

  refresh() {

  }
}