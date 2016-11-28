define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require('echarts-debug');


    exports.run = function() {
        $("[data-toggle='popover']").popover();
        
        //改版图表
        var searchChart = document.getElementById('searchChart');
        if (searchChart) {
            var searchChart = echarts.init(searchChart);
            var items = app.arguments.items;
            var option = {
                title: {
                    text: ''
                },
                tooltip: {},
                legend: {
                    data:['时间']
                },
                xAxis: {
                    data: items.date
                },
                yAxis: {},
                series: [{
                    name: '发送量(条)',
                    type: 'line',
                    data: items.amount,
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
    }

})