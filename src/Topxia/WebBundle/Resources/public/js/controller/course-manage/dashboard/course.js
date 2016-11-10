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
                data: ['学员数', '完成数', '完课率']
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
                data: $container.data('days')
            },
            yAxis: [
                {
                    name: '人数',
                    type: 'value',
                    minInterval: 1
                },
                {
                    name: '完课率',
                    type: 'value',
                    max: 100
                }
            ],
            series: [
                {
                    name:'学员数',
                    type:'line',
                    yAxisIndex: 0,
                    itemStyle: {
                        normal: {
                            color: '#ffc107'
                        }
                    },
                    data:$container.data('studentNum')
                },
                {
                    name:'完成数',
                    type:'line',
                    yAxisIndex: 0,
                    itemStyle: {
                        normal: {
                            color: '#4caf50'
                        }
                    },
                    data:$container.data('finishedNum')
                },
                {
                    name:'完课率',
                    type:'line',
                    yAxisIndex: 1,
                    itemStyle: {
                        normal: {
                            color: '#2196f3'
                        }
                    },
                    data:$container.data('finishedRate')
                }
            ],
        };

        // 使用刚指定的配置项和数据显示图表。
        myChart.setOption(option);

        $('.finisher-lesson-popover').popover({
            html: true,
            trigger: 'hover',
            placement: 'bottom',
            template: '<div class="popover tata-popover tata-popover-lg" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
            content: function() {
                var html = $(this).siblings('.popover-content').html();
                return html;
            }
        });
    };
});