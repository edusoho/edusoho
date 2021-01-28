class QuestionMarkerStats {
  constructor() {
    this.init();
  }

  init() {
    let myChart = echarts.init(document.getElementById('main'));
    let type = $('.popup-topic').data('type');
    if (type.indexOf('single_choice') >= 0) {
      myChart.setOption(this.getPeiOptions());
    } else {
      myChart.setOption(this.getBarOptions(type));
    }

    $('[data-toggle="tab"]').on('click', function () {
      $(this).addClass('btn-primary').removeClass('btn-default')
        .siblings().removeClass('btn-primary').addClass('btn-default');
    });
  }

  getPeiOptions() {
    let stats = this.getStats();
    let legendData = [], data = [];

    $.each(stats, function(index, stat) {
      let key = index;
      legendData.push(key);

      data.push({
        'name': key,
        'value': stat['pct']
      });
    });

    return {
      tooltip: {
        trigger: 'item',
        formatter: '{a} <br/>{b} : {c} ({d}%)'
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

  getBarOptions(questionType) {
    let stats = this.getStats();

    let xData = [],
      seriesData = [],
      seriesName = Translator.trans('course.question_marker.selection_rate');

    $.each(stats, function(index, stat) {

      if (questionType === 'fill') {
        xData.push(Translator.trans('course.question_marker.gap_filling')+(index+1));
        seriesName = Translator.trans('course.question_marker.correct_rate');
      } else if (questionType === 'determine') {
        let key = String.fromCharCode(index+65);
        xData.push(key);
      } else {
        xData.push(index);
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

  getStats() {
    return $('#figure').data('stats');
  }
}

new QuestionMarkerStats();