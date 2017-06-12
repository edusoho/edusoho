class QuestionMarkerStats {
  constructor() {
    this.init();
  }

  init() {
    let myChart = echarts.init(document.getElementById('main'));
    let type = $('.popup-topic').data('type');
    if (type.indexOf('choice') >= 0) {
      myChart.setOption(this.getPeiOptions());
    } else {
      myChart.setOption(this.getBarOptions());
    }

    $('[data-toggle="tab"]').on('click', function () {
      $(this).addClass('btn-primary').removeClass('btn-default')
        .siblings().removeClass('btn-primary').addClass('btn-default');
    });
  }

  getPeiCount() {
     let option = this.getOption(),
      count = $('#figure').data('count'),
      arr = [];
    count.forEach(function(val, key) {
      arr.push({
        'name': option[key],
        'value': count[key]
      })
    });
    return arr;
  }

  getOption() {
    let $content = $('#figure'),
      option = $content.data('option'),
      arr = [];
    if (option.toString('10').indexOf(',') > 0) {
      arr = option.split(',');
    } else {
      arr = option.toString('10').split('');
    }
    return arr;
  }

  getPeiOptions() {
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
        data: this.getOption()
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
        data: this.getPeiCount()
      }]
    };
  }

  getBarOptions() {
    return {
      tooltip: {},
      xAxis: {
        data: this.getOption()
      },
      yAxis: {},
      series: [{
        name: '销量',
        type: 'bar',
        data: $('#figure').data('count')
      }]
    };
  }
}

new QuestionMarkerStats();