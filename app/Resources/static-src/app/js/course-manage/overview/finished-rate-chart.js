export default class FinishedRateChart {
  constructor(id, studentNum) {
    this.id = id;
    this.studentNum = studentNum;

    this.dateArr = [];
    this.finishedRateArr = [];
    this.finishedNumArr = [];

    this.chart = echarts.init(document.getElementById(this.id));
  }

  show(startDate, endDate){

    let self = this;
    $.ajax({
      type: "GET",
      beforeSend: function(request) {
        request.setRequestHeader("Accept", 'application/vnd.edusoho.v2+json');
        request.setRequestHeader("X-CSRF-Token", $('meta[name=csrf-token]').attr('content'));
      },
      data: {startDate: startDate, endDate: endDate},
      url: "/api/course/1/report/completion_rate_trend",
      success: function(resp) {

        for (let value of resp) {
          self.dateArr.push(value.date);
          self.finishedRateArr.push(Math.floor(value.finishedRate));
          self.finishedNumArr.push(value.finishedNum);
        }

        self.chart.setOption(self.getOption());

      }
    });
  }

  getOption() {

    function formatter(params) {
      let html = params[0].name + '</br>';
      for (let i = 0; i < params.length; i++) {
        let circle = '<span style="display:inline-block;margin-right:5px;'
          + 'border-radius:10px;width:9px;height:9px;background-color:' + params[i].color + '"></span>'
          + params[i].seriesName + ' : ' + params[i].value + (i === 1 ? '%' : '') + '</br>';
        html += circle;
      };
      return html;
    }

    return  {
      tooltip: {
        trigger: 'axis',
        formatter: formatter
      },
      legend: {
        data: [Translator.trans('course_manage.course_dashboard.finish_num'), Translator.trans('course_manage.course_dashboard.finish_rate')]
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
        data: this.dateArr,
        splitLine: {
          show: false
        },
        axisLine: {
          lineStyle: {
            color: '#616161'
          }
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
              type: 'dashed'
            }
          },
          axisLine: {
            show: false
          },
          axisTick: {
            show: false
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
              color: '#7ecf51'
            }
          },
          data:this.finishedNumArr
        },
        {
          name:Translator.trans('course_manage.course_dashboard.finish_rate'),
          type:'line',
          yAxisIndex: 1,
          showSymbol: false,
          smooth: true,
          itemStyle: {
            normal: {
              color: '#61a5e8'
            }
          },
          data:this.finishedRateArr
        }
      ],
    };

  }
}