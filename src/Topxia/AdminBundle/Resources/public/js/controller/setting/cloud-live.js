define(function(require, exports, module) {
    require('echarts-debug');

    exports.run = function() {
        var liveTopChart = document.getElementById('liveTopChart');
        if (liveTopChart) {
            var liveTopChart = echarts.init(liveTopChart);
            var items = app.arguments.items;

             var liveoption = {
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
                    name: '人数',
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
            liveTopChart.setOption(liveoption);
        }
    }
});