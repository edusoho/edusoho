define(function(require, exports, module) {
    require('echarts');

    exports.run = function() {
        var $container = $('#lesson-dashboard-container');
        var myChart = echarts.init(document.getElementById('lesson-dashboard-container'));

        // 指定图表的配置项和数据
        option = {
            tooltip : {
                trigger: 'axis',
                formatter: function(params) {
                    var rate = 0;

                    //求完成率
                    if (params[1].value > 0) {
                        rate = (params[0].value/params[1].value).toFixed(3) * 100;
                    }

                    var circle1 = '<span style="display:inline-block;margin-right:5px;'
                        + 'border-radius:10px;width:9px;height:9px;background-color:' + params[0].color + '"></span>';
                    var circle2 = '<span style="display:inline-block;margin-right:5px;'
                        + 'border-radius:10px;width:9px;height:9px;background-color:' + params[1].color + '"></span>';
                    var circle3 = '<span style="display:inline-block;margin-right:5px;'
                        + 'border-radius:10px;width:9px;height:9px;background-color:#c23531' + '"></span>';

                    var html = params[0].name + '</br>';
                    html += circle1+params[0].seriesName+' : '+params[0].value+'</br>';
                    html += circle2+params[1].seriesName+' : '+params[1].value+'</br>';
                    html += circle3+'完成率 : '+rate+'%';
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
                minInterval: 1
            },
            yAxis: [
                {
                    name: '课时',
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
                            show: true,
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
                    name: '学习中',
                    type: 'bar',
                    stack: '总量',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideRight'
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

        function resetContainerHeight(){
            var height = calcContainerHeight($container);
            $container.height(height);
            myChart.resize();
        }

        function calcContainerHeight($container)
        {
            var length = $container.data('lesson-num');
            var maxHeight = 30 * length;

            if (maxHeight < 400) {
                return 400;
            } else {
                return maxHeight;
            }
        }
    };

});