define(function(require, exports, module) {
    require('echarts-debug');
    exports.run = function() {

        //改版图表
        var smsSendChart = echarts.init(document.getElementById('smsSendChart'));
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
                type: 'bar',
                data: items.count
            }],
            color:['#428BCA'],
            grid:{
                show:true,
                borderColor:'#fff',
                backgroundColor:'#fff'
            }
        };
        smsSendChart.setOption(option);
    }

});