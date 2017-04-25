import echarts from 'echarts';
let $container = $('#lesson-dashboard-container');
let myChart = echarts.init(document.getElementById('lesson-dashboard-container'));

// 指定图表的配置项和数据
let option = {
    tooltip : {
        trigger: 'axis',
        formatter: function(params) {
            var rate = 0;

            //求完成率
            var learningNum = parseInt(params[1].value);
            var learnedNum = parseInt(params[0].value);
            var totalNum = learnedNum + learningNum;
            if (totalNum > 0) {
                rate = ((learnedNum/totalNum) * 100).toFixed(1);
            } else {
                rate = 0;
            }

            var circle1 = '<span style="display:inline-block;margin-right:5px;'
                + 'border-radius:10px;width:9px;height:9px;background-color:' + params[0].color + '"></span>';
            var circle2 = '<span style="display:inline-block;margin-right:5px;'
                + 'border-radius:10px;width:9px;height:9px;background-color:' + params[1].color + '"></span>';
            var circle3 = '<span style="display:inline-block;margin-right:5px;'
                + 'border-radius:10px;width:9px;height:9px;background-color:#c23531' + '"></span>';

            var titles = $container.data('titles');
            var remarks = $container.data('remarks');
            var html = params[0].name + "：" + remarks[titles.indexOf(params[0].name)] + '</br>';
            var val1 = isNaN(learnedNum)? '-' : learnedNum;
            var val2 = isNaN(learningNum)? '-' : learningNum;
            html += circle1+params[0].seriesName+' : '+val1+'</br>';
            html += circle2+params[1].seriesName+' : '+val2+'</br>';
            html += circle3+Translator.trans('course_manage.lesson_dashboard.finish_rate')+': '+rate+'%';
            return html;
        },
        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
            type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
        }
    },
    legend: {
        data: [ Translator.trans('course_manage.lesson_dashboard.task_finished'), Translator.trans('course_manage.lesson_dashboard.task_learning')]
    },
    grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
    },
    xAxis:  {
        name: Translator.trans('course_manage.lesson_dashboard.student_num'),
        type: 'value',
        minInterval: 1
    },
    yAxis: [
        {
            name: Translator.trans('course_manage.lesson_dashboard.task'),
            type: 'category',
            axisLabel: {
                margin: 15
            },
            data: $container.data('titles')
        }
    ],
    series: [
        {
            name:  Translator.trans('course_manage.lesson_dashboard.task_finished'),
            type: 'bar',
            stack: Translator.trans('course_manage.lesson_dashboard.total_amount'),
            label: {
                normal: {
                    show: false,
                    position: 'insideRight'
                }
            },
            itemStyle: {
                normal: {
                    color: '#4CAF50'
                }
            },
            data: $container.data('finishedNum')
        },
        {
            name: Translator.trans('course_manage.lesson_dashboard.task_learning'),
            type: 'bar',
            stack: Translator.trans('course_manage.lesson_dashboard.total_amount'),
            label: {
                normal: {
                    show: false,
                    position: 'insideRight',
                    formatter: function (params) {
                        return params.value == 0 ? '':params.value;
                    }
                }
            },
            itemStyle: {
                normal: {
                    color: '#FFC108'
                }
            },
            data: $container.data('learnNum')
        }
    ]
};

// 使用刚指定的配置项和数据显示图表。
myChart.setOption(option);

resetContainerHeight();

function resetContainerHeight() {
  let height = calcContainerHeight($container);
  $container.height(height);
  myChart.resize();
}

function calcContainerHeight($container) {
  let length = $container.data('lesson-num');
  let maxHeight = 30 * length;

  if (maxHeight < 200) {
    return 200;
  } else {
    return maxHeight;
  }
}
