define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require('echarts');


    exports.run = function() {
        $("[data-toggle='popover']").popover();
        
        //改版图表
        var searchChart = echarts.init(document.getElementById('searchChart'));
        var chartData = app.arguments.chartData;
        var option = {
            title: {
                text: ''
            },
            tooltip: {},
            legend: {
                data:[Translator.trans('site.time')]
            },
            xAxis: {
                data: chartData.date
            },
            yAxis: {
                minInterval: 1
            },
            series: [{
                name: chartData.unit,
                type: 'line',
                data: chartData.count,
                areaStyle: {
                    normal: {
                        color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                            offset: 0,
                            color: '#428BCA'
                        }, {
                            offset: 1,
                            color: '#7ec2fc'
                        }])
                    }
                },
            }],
            color:['#428BCA'],
            grid:{
                show:true,
                borderColor:'#fff',
                backgroundColor:'#fff'
            }
        };
        searchChart.setOption(option);
    }

})