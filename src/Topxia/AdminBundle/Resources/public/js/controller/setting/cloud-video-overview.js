define(function(require, exports, module) {
    require('echarts');
    exports.run = function() {
        var spaceItemChart = echarts.init(document.getElementById('spaceItemChart'));
        var spaceItems = app.arguments.spaceItems;
        var option = {
            title: {
                text: ''
            },
            tooltip: {},
            legend: {
                data:['时间']
            },
            xAxis: {
                data: spaceItems.date
            },
            yAxis: {},
            series: [{
                name: '容量(G)',
                type: 'bar',
                data: spaceItems.amount
            }],
            color:['#428BCA'],
            grid:{
                show:true,
                borderColor:'#fff',
                backgroundColor:'#fff'
            }
        };
        spaceItemChart.setOption(option);
        
     var flowItemChart = echarts.init(document.getElementById('flowItemChart'));
     var flowItems = app.arguments.flowItems;
     var option = {
        title: {
            text: ''
        },
        tooltip: {},
        legend: {
            data:['时间']
        },
        xAxis: {
            data: flowItems.date
        },
        yAxis: {},
        series: [{
            name: '容量(G)',
            type: 'bar',
            data: flowItems.amount
        }],
        color:['#428BCA'],
        grid:{
            show:true,
            borderColor:'#fff',
            backgroundColor:'#fff'
        }
    };
    flowItemChart.setOption(option);
    }
})