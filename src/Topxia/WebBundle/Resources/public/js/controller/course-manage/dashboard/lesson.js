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

                    var html = params[0].name + '</br>';
                    html += '完成人数 : '+params[0].value+'</br>';
                    html += '学习人数 : '+params[1].value+'</br>';
                    html += '完成率 : '+rate+'%';
                    return html;
                },
                axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                    type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
                }
            },
            legend: {
                data: ['完成人数', '学习人数']
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            xAxis:  {
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
                    name: '完成人数',
                    type: 'bar',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideRight'
                        }
                    },
                    itemStyle: {
                        normal: {
                            color: '#090'
                        }
                    },
                    data: $container.data('finishedNum')
                },
                {
                    name: '学习人数',
                    type: 'bar',
                    label: {
                        normal: {
                            show: true,
                            position: 'insideRight'
                        }
                    },
                    itemStyle: {
                        normal: {
                            color: '#668ed6'
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