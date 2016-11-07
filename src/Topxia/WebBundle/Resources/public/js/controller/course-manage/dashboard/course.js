define(function(require, exports, module) {
    require('echarts');

    exports.run = function() {
        var $container = $('#course-dashboard-container');
        var myChart = echarts.init(document.getElementById('course-dashboard-container'));

        // 指定图表的配置项和数据
        option = {
            tooltip: {
                trigger: 'axis'
            },
            legend: {
                data: ['学员数', '完课数', '完课率']
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: '3%',
                containLabel: true
            },
            toolbox: {
                feature: {
                    saveAsImage: {}
                }
            },
            xAxis: {
                type: 'category',
                boundaryGap: false,
                data: $container.data('days')
            },
            yAxis: [
                {
                    name: '人数',
                    type: 'value',
                    minInterval: 1
                },
                {
                    name: '百分比',
                    type: 'value',
                    max: 100
                }
            ],
            series: [
                {
                    name:'学员数',
                    type:'line',
                    yAxisIndex: 0,
                    data:$container.data('studentNum')
                },
                {
                    name:'完课数',
                    type:'line',
                    yAxisIndex: 0,
                    data:$container.data('finishedNum')
                },
                {
                    name:'完课率',
                    type:'line',
                    yAxisIndex: 1,
                    data:$container.data('finishedRate')
                }
            ],
        };

        // 使用刚指定的配置项和数据显示图表。
        myChart.setOption(option);
        
    };

});