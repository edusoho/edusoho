define(function (require, exports, module) {
    "use strict";
    require('echarts');

    var initEcharts = function (el, options) {
        var obj = echarts.init(el);
        obj.setOption(options);
        return obj;
    };

    exports.run = function () {
        var $lineCharts = $('#line-charts');
        var chartsData = $lineCharts.data('chartsData');
        var options = {
            title: {
                text: '观看统计'
            },
            tooltip: {
                trigger: 'axis'
            },
            xAxis: {
                type: 'category',
                splitLine: {
                    show: false
                },
                boundaryGap: false,
                data: chartsData.date
            },
            yAxis: {
                type: 'value'
            },
            series: [{
                name: '观看数',
                type: 'line',
                data: chartsData.watchNum,
                itemStyle: {
                    normal: {
                        color: '#0b62a4',
                        borderWidth: 5
                    }
                },
                lineStyle:{
                    normal:{
                        color: '#0b62a4',
                        width: 3
                    }
                }
            }]
        };
        var lineCharts = initEcharts($lineCharts.get(0), options);
        $('.js-watch-type').on('click', function () {
            var $self = $(this);
            $('#refererlog-search-form').find('[name=type]').val($self.data('value'));
            $('#refererlog-search-form').submit();
        });
    }
});