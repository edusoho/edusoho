define(function(require, exports, module) {
    require('echarts');

    exports.run = function() {
        var $container = $('#course-dashboard-container');
        var myChart = echarts.init(document.getElementById('course-dashboard-container'));

        // 指定图表的配置项和数据
        option = {
            tooltip: {
                trigger: 'axis',
                formatter: formatter
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

        function formatter(params)
        {
            var html = params[0].name + '</br>';
            var circle1 = '<span style="display:inline-block;margin-right:5px;'
                + 'border-radius:10px;width:9px;height:9px;background-color:' + params[0].color + '"></span>';
            var circle2 = '<span style="display:inline-block;margin-right:5px;'
                + 'border-radius:10px;width:9px;height:9px;background-color:' + params[1].color + '"></span>';
            var circle3 = '<span style="display:inline-block;margin-right:5px;'
                + 'border-radius:10px;width:9px;height:9px;background-color:' + params[2].color + '"></span>';
            html += circle1+params[0].seriesName+' : '+params[0].value+'</br>';
            html += circle2+params[1].seriesName+' : '+params[1].value+'</br>';
            html += circle3+params[2].seriesName+' : '+params[2].value+'%</br>';

            changeSummary(params[0].dataIndex);
            return html;
        }

        function changeSummary(index)
        {
            var studentNumArr = $container.data('studentNum'),
                finishedNumArr = $container.data('finishedNum'),
                finishedRateArr = $container.data('finishedRate'),
                askNumArr = $container.data('askNum'),
                noteNumArr = $container.data('noteNum'),
                discussionNumArr = $container.data('discussionNum');
            $('.js-student-num').html(studentNumArr[index]);
            $('.js-finished-num').html(finishedNumArr[index]);
            $('.js-finished-rate').html(finishedRateArr[index]+'%');
            $('.js-ask-num').html(askNumArr[index]);
            $('.js-note-num').html(noteNumArr[index]);
            $('.js-discussion-num').html(discussionNumArr[index]);
        }

        // 使用刚指定的配置项和数据显示图表。
        myChart.setOption(option);
    };

});