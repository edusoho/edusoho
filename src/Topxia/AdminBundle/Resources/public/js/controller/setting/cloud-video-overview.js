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
                data:[Translator.trans('site.time')]
            },
            xAxis: {
                data: spaceItems.date
            },
            yAxis: {},
            series: [{
                name: Translator.trans('site.capacity'),
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
            data:[Translator.trans('site.time')]
        },
        xAxis: {
            data: flowItems.date
        },
        yAxis: {},
        series: [{
            name: Translator.trans('site.capacity'),
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