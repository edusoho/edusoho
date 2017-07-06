let taskChart = echarts.init(document.getElementById('task-data-chart'));
let option = {
    tooltip : {
        trigger: 'axis',
        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
            type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
        }
    },
    legend: {
        data: ['已完成', '学习中','未开始']
    },
    grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
    },
    xAxis:  {
        type: 'value',
        show: false,
    },
    yAxis: {
        data: ['任务1','任务2','任务3','任务4','任务5','任务6','任务7'],
        axisLine: {
            show: false
        },
        axisTick:{
            show: false
        }
    },
    series: [
        {
            name: '已完成',
            type: 'bar',
            stack: '总量',
            label: {
                normal: {
                    show: true,
                    position: 'insideRight'
                }
            },
            data: [2, 4, 5, 5, 1, 6, 1],
            itemStyle: {
                normal: {
                    color: '#92D178'
                }
            },
        },
        {
            name: '学习中',
            type: 'bar',
            stack: '总量',
            label: {
                normal: {
                    show: true,
                    position: 'insideRight'
                }
            },
            data:[0, 4, 5, 1, 1, 6, 3],
            itemStyle: {
                normal: {
                    color: '#FECF7D'
                }
            },
        },
        {
            name: '未开始',
            type: 'bar',
            stack: '总量',
            label: {
                normal: {
                    show: true,
                    position: 'insideRight'
                }
            },
            data: [2, 4, 2, 6, 10, 0, 7],
            itemStyle: {
                normal: {
                    color: '#D3D3D3'
                }
            },
        },
    ]
};
taskChart.setOption(option);
let url = $('.js-date-change-url').data('url');
let $timeSlectBtn = $('.is-date-change');
let $tabChangBtn = $('.js-tab-change');
let ajax = false;

$timeSlectBtn.on('click', function() {
  let type = $(this).data('type');
  let time = $(this).data('time');
  $.post(url, {
    type: type,
    time: time
  }).done(() => {
    console.log('success');
  }).fail(() => {
    console.log('error');
  })
});

$tabChangBtn.on('click', function() {
  $(this).parents('.course-statictics-content').find('.js-chart-change').toggle();
  if (ajax == false) {
    $.post(url).done(() => {
      ajax = true;
    }).fail(() => {
      console.log('fail');
    })
  }
})
