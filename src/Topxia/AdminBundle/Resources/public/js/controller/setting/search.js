define(function(require, exports, module) {

    var Notify = require('common/bootstrap-notify');
    require('echarts-debug');


    exports.run = function() {
        if($(".alert-warning").length>0){
            $(".search-button").hide();
        }

        $("[data-toggle='popover']").popover();
        
        //改版图表
        var searchChart = echarts.init(document.getElementById('searchChart'));
        var option = {
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
                name: '发送量(条)',
                type: 'line',
                data: [50, 220, 136, 110, 10, 90],
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