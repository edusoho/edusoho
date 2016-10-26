define(function(require, exports, module) {
    require('echarts-debug');

    exports.run = function() {

        var liveTopChart = echarts.init(document.getElementById('liveTopChart'));
         var liveoption = {
            title: {
                text: ''
            },
            tooltip: {},
            legend: {
                data:['时间']
            },
            xAxis: {
                data: ["2016/02","2016/03","2016/04","2016/05","2016/05","2016/06"]
            },
            yAxis: {},
            series: [{
                name: '容量(G)',
                type: 'bar',
                data: [50, 220, 136, 110, 10, 90]
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
});