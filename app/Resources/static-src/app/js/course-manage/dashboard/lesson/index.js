let $container = $('#lesson-dashboard-container');
let myChart = echarts.init(document.getElementById('lesson-dashboard-container'));
let taskRates = $container.data('finishedRate');
let finishedNum = $container.data('finishedNum');
let learnNum = $container.data('learnNum');
let split = 1;
for(let i in finishedNum) {
    let sum = finishedNum[i] + learnNum[i];
    (split<sum) && (split = sum);
}
split = split > 5 ? 5: split;
// 指定图表的配置项和数据
let option = {
    tooltip : {
        trigger: 'axis',
        formatter: function(params) {
            console.log(params);
            
            let titles = $container.data('titles');
            let remarks = $container.data('remarks');
            let chartTitle = remarks[titles.indexOf(params[0].name)];
            if (!chartTitle) {
                return '<div>无数据</div>';
            }

            let html = params[0].name + "：" + chartTitle + '</br>';

            let rateIndex = params[0].dataIndex;
            for (let i = 0; i < params.length; i++) {
                let value = parseInt(params[i].value);
                value =  isNaN(value)? '-' : value;
                let circle = '<span style="display:inline-block;margin-right:5px;'
                    + 'border-radius:10px;width:9px;height:9px;background-color:' + params[i].color + '"></span>'
                    + params[i].seriesName+' : '+value+'</br>';
                html += circle;
            };

            var circle3 = '<span style="display:inline-block;margin-right:5px;'
                + 'border-radius:10px;width:9px;height:9px;background-color:#c23531' + '"></span>';
            html += circle3+'完成率 : '+taskRates[rateIndex]+'%';

            return html;
        },
        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
            type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
        }
    },
    legend: {
        data: ['已学完', '学习中']
    },
    grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
    },
    xAxis:  {
        name: '人',
        type: 'value',
        minInterval: 1,
        splitNumber: split,
        min: 0,
    },
    yAxis: [
        {
            name: '任务',
            type: 'category',
            axisLabel: {
                margin: 15
            },
            data: $container.data('titles')
        }
    ],
    series: [
        {
            name: '已学完',
            type: 'bar',
            stack: '总量',
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
            data: finishedNum
        },
        {
            name: '学习中',
            type: 'bar',
            stack: '总量',
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
            data: learnNum
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
